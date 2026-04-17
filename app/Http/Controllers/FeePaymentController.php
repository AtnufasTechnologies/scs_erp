<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplicationPaymentLog;
use App\Models\FailedTransaction;
use App\Models\FailedTransactionLog;
use App\Models\FeesStructure;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\LateFee;
use App\Models\PaymentGatewayType;
use App\Models\StudentMaster;
use App\Models\StudentPayment;
use App\Models\StudentLateFeeExemption;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Easebuzz\PayWithEasebuzzLaravel\Lib\EasebuzzLib\Easebuzz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BatchMaster;
use App\Models\FeeHead;
use App\Models\CollegeBankAccount;

class FeePaymentController extends Controller
{
    function index(Request $request)
    {
        // ---- Base Query ----
        $query = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure.feeHeads',
            'stdfeestructure',
            'stdfeestructure.programspivot',
            'feepayment'
        ]);

        // ---- Filters ----

        if ($request->roll_no) {
            $searchValues = preg_split('/\s+/', $request->roll_no, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->orWhere('roll_no', 'LIKE', "%$value%");
                    $q->orWhere('first_name', 'LIKE', "%$value%");
                }
            });
        }

        if ($request->filter_batch) {
            $query->where('batch', $request->filter_batch);
        }

        if ($request->filter_pgr) {
            $query->where('programme', $request->filter_pgr);
        }

        // ---- PAGINATION ----
        $data = $query->paginate(36)->withQueryString(); // <<<<<< THIS IS THE KEY


        // ---- TRANSFORM EACH RECORD USING through() ----
        $students = $data->through(function ($student) {

            $applicableFS = FeesStructure::with(['feeHeads.head.bankmaster'])
                ->where('batch_id', $student->batch)
                ->whereHas('programspivot', function ($q) use ($student) {
                    $q->where('std_program_id', $student->programme);
                })
                ->whereIn('std_current_year', range(1, $student->current_year))
                ->get();
            $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount'); // 100

            $fsWithStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay) {

                $payment = $student->feepayment
                    ->where('fee_structure_id', $fs->id)
                    ->where('student_id', $student->id)
                    ->whereIn('status', 'success')
                    ->first();

                $totalAmount = $fs->feeHeads->sum('amount');

                // ---- LATE FEE CALCULATION ----
                $lateDays = 0;
                $lateFee = 0;

                if (!$payment) {
                    $dueDate = Carbon::parse($fs->due_date)->timezone('asia/kolkata');

                    $today = Carbon::today()->timezone('asia/kolkata');

                    if ($today->gt($dueDate)) {

                        $lateDays = $dueDate->diffInDays($today);
                        $lateFee = $lateDays * $lateFeePerDay;
                    }
                }

                $bankAccounts = $fs->feeHeads
                    ->map(fn($h) => ($h->head && $h->head->bankmaster) ? [
                        'acc_label' => $h->head->bankmaster->acc_label,
                        'acc_name'  => $h->head->bankmaster->acc_name,
                        'acc_no'    => $h->head->bankmaster->acc_no,
                        'bank_name' => $h->head->bankmaster->bank_name,
                        'branch'    => $h->head->bankmaster->branch,
                    ] : null)
                    ->filter()
                    ->unique('acc_no')
                    ->values()
                    ->toArray();

                return [
                    'paymentinfo' => $payment,
                    'fee_structure_id' => $fs->id,
                    'quarter' => $fs->quarter_title,
                    'year' => $fs->std_current_year,
                    'total_amount' => $totalAmount,
                    'late_days' => $lateDays,
                    'late_fee' => $lateFee,
                    'payable_amount' => $totalAmount + $lateFee,
                    'paid' => $payment ? true : false,
                    'paid_amount' => $payment->amount ?? 0,
                    'status' => $payment ? 'success' : ($lateFee > 0 ? 'late' : 'due'),
                    'bank_accounts' => $bankAccounts,
                ];
            });


            return [
                'studentinfo' => [
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'rollno' => $student->roll_no,
                    'dob' => $student->dob,
                    'gender' => $student->gender == 1 ? 'male' : 'female',
                    'mobile' => $student->mobile_no,
                    'email' => $student->mail_id
                ],
                'batch' => $student->batchmaster->batch_name ?? '',
                'programgroup' => $student->programgroup->program_code ?? '',
                'programinfo' => $student->programgroup->programInfo->name ?? '',
                'current_year' => $student->current_year,
                'fee_status' => $fsWithStatus
            ];
        });

        // ---- Return view ----
        return view('admin.accounts.fee-payment-records', [
            'data' => $students
        ]);
    }


    public function manualFeePayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'fee_structure_id' => 'required',
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
            'gateway_type_id' => 'required',
            'transaction_ref' => 'required',
        ]);

        /** Payment Gateway Logic
         * 1 Easebuzz
         * 2 Billdesk
         * 3 Cash Offline
         * 4 Offline
         */
        //generate Invoice #

        $feeStructureRecord  = FeesStructure::find($request->fee_structure_id);
        $paymentTitle = $feeStructureRecord->quarter_title;
        $studentId = $request->student_id;

        if ($request->gateway_type_id == 1) {
            $invoice =  StaticController::generateInvoiceId('EZ' . $studentId);
        }

        if ($request->gateway_type_id == 2) {

            $invoice =  StaticController::generateInvoiceId('BD' . $studentId);
        }

        if ($request->gateway_type_id == 3) {
            $invoice =  StaticController::generateInvoiceId('CA' . $studentId);
        }

        if ($request->gateway_type_id == 4) {
            $invoice =  StaticController::generateInvoiceId('OF' . $studentId);
        }


        $rec = new StudentPayment();
        $rec->invoice_id = $invoice;
        $rec->student_id = $request->student_id;
        $rec->fee_structure_id = $request->fee_structure_id;
        $rec->status = 'success';
        $rec->amount = $request->amount;
        $rec->transaction_date = $request->transaction_date;
        $rec->gateway_type_id = $request->gateway_type_id;
        $rec->message = "Manual Entry from Accounts Office";
        $rec->save();
        return redirect()->back()->with('success', 'Payment updated successfully!');
    }





    public function generateInvoice($rollno)
    {
        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'programGroup.feeprogpivot.feeStructure',
            'programGroup.programInfo',
            'feepayment' // your payment table
        ])->where('roll_no', $rollno)->firstOrFail();

        $paidInvoices = [];
        $totalPaid = 0;

        foreach ($student->programGroup->feeprogpivot as $pivot) {

            foreach ($pivot->feeStructure as $fee) {

                // check if payment done
                $payment = $student->feepayment
                    ->where('fee_structure_id', $fee->id)
                    ->where('status', 'success')
                    ->first();

                if ($payment) {
                    $amount = FeeStructureHasHead::where('fee_structure_id', $fee->id)->sum('amount');

                    $paidInvoices[] = [
                        'quarter' => $fee->quarter_title,
                        'payable_amount' => $amount,
                        'status' => 'PAID',
                        'paid_on' => $payment->transaction_date ?? 'N/A',
                        'inv_id' => $payment->invoice_id ?? 'N/A',

                    ];

                    $totalPaid += $amount;
                }
            }
        }

        return view('pdf.fee-invoice', [
            'student' => $student,
            'paidInvoices' => $paidInvoices,
            'total_paid' => $totalPaid,
            'invoice_no' => "INV-" . now()->format('Ymd') . "-" . $student->id,
        ]);
    }


    function generateFeeReciept($studentId, $feeId)
    {
        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'programGroup.feeprogpivot.feeStructure',
            'feepayment',
            'programGroup.programInfo'

        ])->findOrFail($studentId);

        // Find the selected fee structure
        $fee = $student->programGroup
            ->feeprogpivot
            ->pluck('feeStructure')
            ->flatten()
            ->where('id', $feeId)
            ->first();

        if (!$fee) {
            abort(404, "Fee structure not found");
        }

        // Find the payment for this fee
        $payment = $student->feepayment
            ->where('fee_structure_id', $feeId)
            ->where('status', 'success')
            ->first();

        if (!$payment) {
            abort(404, "No successful payment found for this fee");
        }

        // Fetch late fee if present
        $lateFee = $payment->late_fee_amount ?? 0;
        $lateDays = $payment->late_days ?? 0;

        // Check if a fixed late fee exemption was applied
        $fixedLateFee = null;
        $exemption = StudentLateFeeExemption::where('student_id', $studentId)
            ->where('fee_structure_id', $feeId)
            ->where('is_active', true)
            ->first();
        if ($exemption && !is_null($exemption->fixed_late_fee)) {
            $fixedLateFee = (float)$exemption->fixed_late_fee;
        }

        // Pass fixedLateFee to the receipt view if you want to display it
        // Example: return view('includes.success-page', [..., 'fixedLateFee' => $fixedLateFee]);
        // For now, just pass to showSuccessPage as a second argument (if you update that method/view)
        return $this->showSuccessPage($payment->invoice_id, $fixedLateFee);
    }

    function studentValidation()
    {
        return view('student.fee-payment');
    }


    // public function studentFeeStatusOld(Request $request)
    // {
    //     $request->validate([
    //         'rollno' => 'required'
    //     ]);
    //     $roll = trim($request->rollno);

    //     // ---- FETCH STUDENT ----
    //     $student = StudentMaster::with([
    //         'batchmaster',
    //         'programgroup.programInfo',
    //         'stdfeestructure',
    //         'stdfeestructure.programspivot',
    //         'feepayment',
    //         'feepayment.feestructuremaster.feeHeads',
    //     ])
    //         ->where('roll_no', $roll)
    //         ->firstOrFail();

    //     // ---- FETCH APPLICABLE FEE STRUCTURES (QUARTERS) ----
    //     $applicableFS = FeesStructure::with('feeHeads')
    //         ->where('batch_id', $student->batch)
    //         ->whereHas('programspivot', function ($q) use ($student) {
    //             $q->where('std_program_id', $student->programme);
    //         })
    //         ->whereIn('std_current_year', range(1, $student->current_year))
    //         ->where('is_payable', 1)
    //         ->orderBy('std_current_year')
    //         ->get();

    //     // ---- PREPARE FEE STATUS (QUARTER-WISE) ----
    //     $feeStatus = $applicableFS->map(function ($fs) use ($student) {

    //         // Check if SUCCESS payment exists for this quarter
    //         $successPayment = $student->feepayment
    //             ->where('fee_structure_id', $fs->id)
    //             ->where('student_id', $student->id)
    //             ->where('status', 'success')
    //             ->first();

    //         // Get latest payment attempt (success / failed / pending)
    //         $latestPayment = $student->feepayment
    //             ->where('fee_structure_id', $fs->id)
    //             ->where('student_id', $student->id)
    //             ->sortByDesc('created_at')
    //             ->first();

    //         return [
    //             'fee_structure_id'   => $fs->id,
    //             'fee_structure_name' => $fs->quarter_title,
    //             'year'               => $fs->std_current_year,
    //             'quarter'            => $fs->quarter_no,

    //             'total_amount'       => $fs->feeHeads->sum('amount'),

    //             // CORE LOGIC
    //             'paid'               => $successPayment ? true : false,
    //             'paid_amount'        => $successPayment->amount ?? 0,
    //             'status'             => $successPayment ? 'PAID' : 'NOT PAID',

    //             // Optional debug / UI info
    //             'last_attempt_status' => $latestPayment->status ?? null,
    //             'paymentinfo'        => $latestPayment
    //         ];
    //     });

    //     // ---- OPTIONAL: SHOW ONLY UNPAID QUARTERS ----
    //     $feeStatus = $feeStatus
    //         ->filter(fn($item) => $item['paid'] === false)
    //         ->values();

    //     // ---- FINAL RESPONSE ----
    //     $studentData = [
    //         'studentinfo' => [
    //             'id'       => $student->id,
    //             'fullname' => $student->fullname,
    //             'rollno'   => $student->roll_no,
    //             'mobile'   => $student->mobile_no,
    //             'email'    => $student->mail_id,
    //         ],
    //         'programinfo'  => $student->programgroup->programInfo->name ?? '',
    //         'batch'        => $student->batchmaster->batch_name ?? '',
    //         'current_year' => $student->current_year,
    //         'feesinfo'     => $feeStatus
    //     ];

    //     return view('student.gateway-selection', [
    //         'data' => $studentData
    //     ]);
    // }


    public function studentFeeStatus(Request $request)
    {
        $request->validate([
            'rollno' => 'required'
        ]);

        $roll = trim($request->rollno);

        // ---- FETCH LATE FEE (ONCE) ----
        $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount'); // 100

        // ---- FETCH STUDENT ----
        $student = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure',
            'stdfeestructure.programspivot',
            'feepayment',
            'feepayment.feestructuremaster.feeHeads',
        ])
            ->where('roll_no', $roll)
            ->firstOrFail();

        // ---- FETCH EXEMPTIONS FOR THIS STUDENT ----
        $exemptions = StudentLateFeeExemption::where('student_id', $student->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('fee_structure_id');

        // Check if student has blanket exemption (fee_structure_id = null)
        $hasBlanketExemption = $exemptions->contains(function ($exemption) {
            return is_null($exemption->fee_structure_id);
        });

        // ---- FETCH APPLICABLE FEE STRUCTURES ----
        $applicableFS = FeesStructure::with('feeHeads')
            ->where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->programme);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year')
            ->get();
        // ---- PREPARE FEE STATUS ----
        $feeStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $exemptions, $hasBlanketExemption) {
            // Success payment
            $successPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->first();

            // Latest attempt
            $latestPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->sortByDesc('created_at')
                ->first();

            $baseAmount = $fs->feeHeads->sum('amount');

            // ---- LATE FEE LOGIC WITH EXEMPTION CHECK ----
            $lateDays = 0;
            $lateFee = 0;
            $isExempted = false;

            if (!$successPayment && $fs->due_date) {
                $dueDate = Carbon::parse($fs->due_date);
                $today   = Carbon::today();

                if ($today->gt($dueDate)) {
                    $lateDays = $dueDate->diffInDays($today);

                    // ---- CHECK EXEMPTION ----
                    $isExempted = $hasBlanketExemption || $exemptions->has($fs->id);
                    if ($isExempted) {
                        $exemption = $hasBlanketExemption
                            ? $exemptions->first(function ($e) {
                                return is_null($e->fee_structure_id);
                            })
                            : $exemptions->get($fs->id);
                        if ($exemption && !is_null($exemption->fixed_late_fee)) {
                            $lateFee = (float)$exemption->fixed_late_fee;
                        } else {
                            $lateFee = 0;
                        }
                    } else {
                        if (!$isExempted) {
                            $lateFee  = $lateDays * $lateFeePerDay;
                        }
                    }
                }
            }

            return [
                'fee_structure_id'   => $fs->id,
                'fee_structure_name' => $fs->quarter_title,
                'year'               => $fs->std_current_year,
                'quarter'            => $fs->quarter_no,
                'is_payable'         => $fs->is_payable,
                'base_amount'        => $baseAmount,
                'late_days'          => $lateDays,
                'late_fee'           => $lateFee,
                'is_late_fee_exempted' => $isExempted,
                'total_payable'      => $baseAmount + $lateFee,

                // CORE PAYMENT INFO
                'paid'               => $successPayment ? true : false,
                'paid_amount'        => $successPayment->amount ?? 0,
                'status'             => $successPayment
                    ? 'PAID'
                    : ($isExempted && $lateDays > 0 ? 'DUE (Late Fee Exempted)' : ($lateFee > 0 ? 'LATE' : 'DUE')),

                // UI / Debug
                'last_attempt_status' => $latestPayment->status ?? null,
                'paymentinfo'         => $latestPayment
            ];
        });

        // ---- FILTER OUT: PAID FEES & FEES WITH ACTIVE EXEMPTIONS ----
        $feeStatus = $feeStatus
            ->filter(fn($item) => $item['paid'] === false)
            ->values();

        // ---- RETURN JSON RESPONSE ----
        // return response()->json($feeStatus);
        // ---- FINAL RESPONSE FOR VIEW (IF NEEDED) ----
        $studentData = [
            'studentinfo' => [
                'id'       => $student->id,
                'fullname' => $student->fullname,
                'rollno'   => $student->roll_no,
                'mobile'   => $student->mobile_no,
                'email'    => $student->mail_id,
            ],
            'programinfo'  => $student->programgroup->programInfo->name ?? '',
            'batch'        => $student->batchmaster->batch_name ?? '',
            'current_year' => $student->current_year,
            'feesinfo'     => $feeStatus
        ];
        return view('student.gateway-selection', [
            'data' => $studentData
        ]);
    }


    //student fee payment
    // public function createOrderOld(Request $request)
    // {
    //     $request->validate([
    //         'fee_structure_id' => 'required|array|min:1',
    //         'gateway' => 'required'
    //     ]);

    //     $studentId = $request->studentId;
    //     $feeStructureIds = $request->fee_structure_id;
    //     $gateway = $request->gateway;

    //     $payMaster = PaymentGatewayType::where('title', $gateway)->firstOrFail();
    //     $paymentGatewayId = $payMaster->id;

    //     // Generate UNIQUE Invoice
    //     $prefix = $gateway === 'easebuzz' ? 'EZ' : 'BL';
    //     $invoice = StaticController::generateInvoiceId($prefix . $studentId);

    //     /** Remove previous initiated payments for same fees */
    //     StudentPayment::where('student_id', $studentId)
    //         ->whereIn('fee_structure_id', $feeStructureIds)
    //         ->where('status', 'initiated')
    //         ->delete();

    //     /** Insert new payment rows */
    //     foreach ($feeStructureIds as $feeId) {

    //         $amount = FeeStructureHasHead::where('fee_structure_id', $feeId)->sum('amount');

    //         $rec = new StudentPayment();
    //         $rec->invoice_id = $invoice;
    //         $rec->student_id = $studentId;
    //         $rec->fee_structure_id = $feeId;
    //         $rec->status = 'intiated';
    //         $rec->amount = $amount;
    //         $rec->transaction_date = Carbon::now();
    //         $rec->gateway_type_id = $paymentGatewayId;
    //         $rec->save();
    //     }

    //     /** Calculate FINAL payable amount */
    //     $payableAmount = StudentPayment::where('invoice_id', $invoice)
    //         ->where('student_id', $studentId)
    //         ->sum('amount');

    //     /** SPLIT PAYMENT (MULTIPLE FEES SAFE) */
    //     $splitData = FeeStructureHasHead::whereIn('fee_structure_id', $feeStructureIds)
    //         ->with('head.bankmaster:id,acc_label')
    //         ->get();

    //     $split = [];
    //     foreach ($splitData as $item) {
    //         $label = $item->head->bankmaster->acc_label;
    //         $split[$label] = ($split[$label] ?? 0) + (float) $item->amount;
    //     }

    //     $splitPayments = json_encode($split);

    //     /** Student Details */
    //     $student = StudentMaster::findOrFail($studentId);

    //     /** Easebuzz Params */
    //     $key = env('EASEBUZZ_KEY');
    //     $salt = env('EASEBUZZ_SALT');
    //     $txnid = $invoice;
    //     $productinfo = 'Salesian College Autonomous - Fee Payment';

    //     $hashString = "$key|$txnid|$payableAmount|$productinfo|{$student->fullname}|{$student->mail_id}|$studentId||||||||||$salt";
    //     $hash = strtolower(hash('sha512', $hashString));

    //     /** Initiate Payment */
    //     $client = new \GuzzleHttp\Client();
    //     $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
    //         'form_params' => [
    //             'key' => $key,
    //             'txnid' => $txnid,
    //             'amount' => $payableAmount,
    //             'productinfo' => $productinfo,
    //             'firstname' => $student->fullname,
    //             'phone' => $student->mobile_no,
    //             'email' => $student->mail_id,
    //             'surl' => route('payment.success'),
    //             'furl' => route('payment.failure'),
    //             'hash' => $hash,
    //             'udf1' => $studentId,
    //             'split_payments' => $splitPayments
    //         ],
    //     ]);

    //     $apiResponse = json_decode($response->getBody(), true);

    //     if ($apiResponse['status'] == 1) {
    //         return redirect(env('EASEBUZZ_PAYMENT_URL') . $apiResponse['data']);
    //     }

    //     return back()->withErrors('Payment initiation failed');
    // }

    public function createOrder(Request $request)
    {
        $request->validate([
            'fee_structure_id' => 'required|array|min:1',
            'gateway' => 'required'
        ]);

        $studentId = $request->studentId;
        $feeStructureIds = $request->fee_structure_id;
        $gateway = $request->gateway;

        $payMaster = PaymentGatewayType::where('title', $gateway)->firstOrFail();
        $paymentGatewayId = $payMaster->id;

        // ---- FETCH LATE FEE (ONCE) ----
        $lateFeePerDay = LateFee::value('late_fee_amount') ?? 0;

        // ---- FETCH EXEMPTIONS ----
        $exemptions = StudentLateFeeExemption::where('student_id', $studentId)
            ->where('is_active', true)
            ->get()
            ->keyBy('fee_structure_id');

        $hasBlanketExemption = $exemptions->contains(function ($exemption) {
            return is_null($exemption->fee_structure_id);
        });

        // ---- STUDENT ----
        $student = StudentMaster::find($studentId);

        // ---- INVOICE ----
        $prefix = $gateway === 'easebuzz' ? 'EZ' : 'BL';
        $invoice = StaticController::generateInvoiceId($prefix . $studentId);

        // ---- REMOVE PREVIOUS INITIATED PAYMENTS ----
        StudentPayment::where('student_id', $studentId)
            ->whereIn('fee_structure_id', $feeStructureIds)
            ->where('status', 'initiated')
            ->delete();

        $finalPayable = 0;

        // ---- INSERT PAYMENT ROWS ----
        foreach ($feeStructureIds as $feeId) {

            $feeStructure = FeesStructure::with('feeHeads')->findOrFail($feeId);

            $baseAmount = $feeStructure->feeHeads->sum('amount');

            // ---- LATE FEE CALCULATION WITH EXEMPTION ----
            $lateDays = 0;
            $lateFee  = 0;
            $isExempted = $hasBlanketExemption || $exemptions->has($feeId);

            if ($feeStructure->due_date && !$isExempted) {
                $dueDate = Carbon::parse($feeStructure->due_date);
                $today   = Carbon::today();

                if ($today->gt($dueDate)) {
                    $lateDays = $dueDate->diffInDays($today);
                    $lateFee  = $lateDays * $lateFeePerDay;
                }
            }

            $totalPayable = $baseAmount + $lateFee;
            $finalPayable += $totalPayable;

            // ---- SAVE PAYMENT ROW ----
            $rec = new StudentPayment();
            $rec->invoice_id = $invoice;
            $rec->student_id = $studentId;
            $rec->roll_no = $student->roll_no;
            $rec->fee_structure_id = $feeId;
            $rec->status = 'intiated';
            $rec->amount = $baseAmount;
            $rec->late_fee_amount  = $lateFee;
            $rec->late_days  = $lateDays;
            $rec->transaction_date = Carbon::now();
            $rec->gateway_type_id = $paymentGatewayId;
            $rec->save();
        }
        // ---- SPLIT PAYMENT (BASE AMOUNT ONLY) ----
        $splitData = FeeStructureHasHead::whereIn('fee_structure_id', $feeStructureIds)
            ->with('head.bankmaster:id,acc_label')
            ->get();

        $split = [];
        foreach ($splitData as $item) {
            $label = $item->head->bankmaster->acc_label;
            $split[$label] = ($split[$label] ?? 0) + (float) $item->amount;
        }

        // OPTIONAL: Add late fee under a separate head
        if ($finalPayable > array_sum($split)) {
            $label = ($student->campus_id == 2) ? 'SAL_ACFEES' : 'SAL_FEES';
            $split[$label] = ($split[$label] ?? 0) + ($finalPayable - array_sum($split)); //add Late Fee Label
        }

        $splitPayments = json_encode($split);


        // ---- EASEBUZZ PARAMS ----
        $key = env('EASEBUZZ_KEY');
        $salt = env('EASEBUZZ_SALT');
        $txnid = $invoice;
        $mobile_no = $student->mobile_no;
        $mail_id = $student->mail_id;
        $first_name = trim($student->first_name) . ' ' . trim($student->last_name);
        $productinfo = 'Salesian College Autonomous - Fee Payment';
        $roll_no = $student->roll_no;

        $hashString = "$key|$txnid|$finalPayable|$productinfo|$first_name|$mail_id|$studentId|$roll_no|||||||||$salt";

        $hash = strtolower(hash('sha512', $hashString));

        // ---- INITIATE PAYMENT ----
        $client = new \GuzzleHttp\Client();
        $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
            'form_params' => [
                'key' => $key,
                'txnid' => $txnid,
                'amount' => $finalPayable,
                'productinfo' => $productinfo,
                'firstname' => $first_name,
                'phone' => $mobile_no,
                'email' => $mail_id,
                'surl' => route('payment.success'),
                'furl' => route('payment.failure'),
                'hash' => $hash,
                'udf1' => $studentId,
                'udf2' => $roll_no,
                'split_payments' => $splitPayments
            ],
        ]);

        $apiResponse = json_decode($response->getBody(), true);

        if ($apiResponse['status'] == 1) {
            return redirect(env('EASEBUZZ_PAYMENT_URL') . $apiResponse['data']);
        }

        return back()->withErrors('Payment initiation failed');
    }

    public function paymentSuccess(Request $request)
    {

        $hash  =  $request->hash;
        $amount = $request->amount;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;
        $userId = $request->udf1;
        //Online Transaction - Update Payment Record
        StudentPayment::where('invoice_id', $txnid)
            ->update(
                [
                    'gateway_ref_code' => $easepayid,
                    'captured_amount' => $amount,
                    'status' => $status,
                    'message' => $msg,
                    'hash' => $hash,
                ]
            );
        //show success page to Student  
        return redirect('erp/student/transaction-success/' . $txnid);
    }

    function showSuccessPage($txnId, $fixedLateFee = null)
    {
        $txnrecs =  StudentPayment::where('invoice_id', $txnId)->with([
            'studentmaster:id,first_name,last_name,roll_no,mobile_no,mail_id',
            'feepaymentinfo:id,quarter_title',
            'feepaymentinfo.feeHeads.head:id,head_name'
        ])->get();
        $data = json_decode($txnrecs, true);

        return view('includes.success-page', [
            'invoiceId' => $data[0]['invoice_id'],
            'gatewayRef' => $data[0]['gateway_ref_code'],
            'transactionDate' => $data[0]['transaction_date'],
            'student' => $data[0]['studentmaster'],
            'transactions' => $data,
            'status' => $data[0]['status'],
            'gatewayType' => $data[0]['gateway_type_id'],
            'fixedLateFee' => $fixedLateFee,
            'downloadPdfUrl' => url('erp/student/transaction-success/' . $txnId . '/download-pdf'),
        ]);
    }

    function downloadInvoice($txnId)
    {
        $txnrecs =  StudentPayment::where('invoice_id', $txnId)->with([
            'studentmaster:id,first_name,last_name,roll_no,mobile_no,mail_id',
            'feepaymentinfo:id,quarter_title',
            'feepaymentinfo.feeHeads.head:id,head_name'
        ])->get();
        $transactions = json_decode($txnrecs, true);

        $data = [
            'invoiceId'        => $transactions[0]['invoice_id'],
            'gatewayRef'       => $transactions[0]['gateway_ref_code'],
            'transactionDate' => $transactions[0]['transaction_date'],
            'student'          => $transactions[0]['studentmaster'],
            'transactions'     => $transactions,
            'status'           => $transactions[0]['status'],
        ];

        $pdf = Pdf::loadView('includes.success-page', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download(
            'invoice-' . $data['invoiceId'] . '.pdf'
        );
    }

    public function paymentFailure(Request $request)
    {

        $hash  =  $request->hash;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;

        StudentPayment::where('invoice_id', $txnid)
            ->update(
                [
                    'gateway_ref_code' => $easepayid,
                    'status' => $status,
                    'message' => $msg,
                    'hash' => $hash,
                ]
            );

        return redirect('erp/student/fee-payment/')->with('error', 'Transaction Failed. Please try again.');
    }


    /**
     * Webhook: Easebuzz server->server notifications
     */
    public function webhook(Request $request)
    {
        // Validate signature if Easebuzz sends one (check docs)
        // Example: $signature = $request->header('X-Easebuzz-Signature'); verify it
        $payload = $request->all();
        $txnid = $payload['txnid'] ?? null;

        if (!$txnid) {
            return response()->json(['status' => 'error', 'message' => 'txnid missing'], 400);
        }

        $payment = StudentPayment::where('invoice_id', $txnid)->get();

        if (!$payment) {
            // maybe log and create a record
            Log::warning('Easebuzz webhook for unknown txn: ' . $txnid, $payload);
            return response()->json(['status' => 'ok']);
        }

        // Update according to webhook payload status
        $status = $payload['status'] ?? 'pending';
        $payment->update([
            'status' => strtoupper($status),
            'raw_response' => json_encode($payload)
        ]);

        // perform reconciliation, ledger updates etc.

        return response()->json(['status' => 'ok']);
    }



    //BillDesk Integeration
    private function startBilldeskPayment($student, $amount, $txnid)
    {
        $payload = [
            "mercid" => "XXXXX",
            "orderid" => $txnid,
            "amount" => $amount,
            "order_date" => date('Y-m-d H:i:s'),
            "currency" => "INR",
            "ru" => url('payment/billdesk-response'),
        ];

        // Build checksum, send to Billdesk (pseudo)
        $checksum = hash('sha256', json_encode($payload));

        return view('payment.billdesk-redirect', [
            'payload' => $payload,
            'checksum' => $checksum
        ]);
    }


    function allPayments(Request $request)
    {
        if ($request->has('from_date') && $request->has('to_date')) {
            $from = Carbon::parse($request->from_date)->startOfDay();
            $to = Carbon::parse($request->to_date)->endOfDay();

            $payments = StudentPayment::with([
                'studentmaster:id,first_name,last_name,roll_no',
                'feepaymentinfo:id,quarter_title',
                'gatewayType:id,title'
            ])->whereBetween('transaction_date', [$from, $to])->orderBy('transaction_date', 'desc')->get();
        } else {
            $payments = StudentPayment::with([
                'studentmaster:id,first_name,last_name,roll_no',
                'feepaymentinfo:id,quarter_title',
                'gatewayType:id,title'
            ])->orderBy('created_at', 'desc')->get();
        }




        return view('admin.accounts.all-payments', [
            'payments' => $payments
        ]);
    }

    function verifyTransaction($txnid)
    {
        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        $data =  $response['msg']['0'];
        return view('admin.accounts.ez-payment-verification', ['data' => $data]);
    }

    // ---- LATE FEE EXEMPTION MANAGEMENT ----

    public function lateFeeExemptionIndex(Request $request)
    {
        $exemptions = StudentLateFeeExemption::with([
            'student:id,first_name,last_name,roll_no',
            'feeStructure:id,quarter_title',
            'approver:id,name'
        ])->orderBy('created_at', 'desc')->paginate(50);

        // Fetch students with their exemption status
        $studentsQuery = StudentMaster::select('id', 'roll_no', 'first_name', 'last_name', 'batch', 'programme', 'current_year')
            ->with(['batchmaster:id,batch_name', 'programgroup:id,program_code']);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('roll_no', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply batch filter
        if ($request->has('batch_filter') && $request->batch_filter) {
            $studentsQuery->where('batch', $request->batch_filter);
        }

        $students = $studentsQuery->paginate(50);

        // Get exemption counts for each student
        $studentIds = $students->pluck('id');
        $exemptionCounts = StudentLateFeeExemption::whereIn('student_id', $studentIds)
            ->where('is_active', true)
            ->selectRaw('student_id, COUNT(*) as count, MAX(CASE WHEN fee_structure_id IS NULL THEN 1 ELSE 0 END) as has_blanket')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return view('admin.accounts.late-fee-exemptions', compact('exemptions', 'students', 'exemptionCounts'));
    }

    public function grantLateFeeExemption(Request $request)
    {
        $request->validate([
            'roll_no' => 'required|exists:student_masters,roll_no',
            'fee_structure_id' => 'required|exists:fees_structures,id',
            'reason' => 'required|string|max:500',
            'fixed_late_fee' => 'nullable|numeric|min:0',
        ]);

        // Get student ID from roll number
        $student = StudentMaster::where('roll_no', $request->roll_no)->firstOrFail();
        $studentId = $student->id;

        // If no fee structures selected, it's a blanket exemption
        $feeStructureId = $request->fee_structure_id;

        if (empty($feeStructureIds)) {
            // Blanket exemption - applies to all fees
            $feeStructureIds = null;
        }

        $createdCount = 0;


        $createdCount = StudentLateFeeExemption::updateOrCreate(
            [
                'student_id' => $studentId,
                'fee_structure_id' => $feeStructureId,
            ],
            [
                'reason' => $request->reason,
                'fixed_late_fee' => $request->input('fixed_late_fee'),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'is_active' => true
            ]
        );

        $message = 'Late fee exemption updated successfully!';
        return redirect()->back()->with('success', $message);
    }

    public function revokeLateFeeExemption($id)
    {
        $exemption = StudentLateFeeExemption::findOrFail($id);
        $exemption->delete();

        return redirect()->back()->with('success', 'Late fee exemption revoked successfully!');
    }

    // ---- API ENDPOINTS FOR EXEMPTION PAGE ----

    public function searchStudents(Request $request)
    {
        $query = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 20;

        $students = StudentMaster::where(function ($q) use ($query) {
            $q->where('roll_no', 'LIKE', "%{$query}%")
                ->orWhere('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%");
        })
            ->select('id', 'roll_no', 'first_name', 'last_name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'roll_no' => $student->roll_no,
                    'fullname' => $student->first_name . ' ' . $student->last_name
                ];
            }),
            'pagination' => [
                'more' => $students->hasMorePages()
            ]
        ]);
    }

    public function getStudentFeeStructures($studentId)
    {
        $student = StudentMaster::findOrFail($studentId);

        $feeStructures = FeesStructure::where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->programme);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->select('id', 'quarter_title', 'std_current_year', 'quarter_no')
            ->orderBy('std_current_year')
            ->orderBy('quarter_no')
            ->get();

        return response()->json($feeStructures);
    }



    public function getStudentUnpaidFees($rollno)
    {
        $roll = trim($rollno);

        // ---- FETCH LATE FEE (ONCE) ----
        $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount'); // 100

        // ---- FETCH STUDENT ----
        $student = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure',
            'stdfeestructure.programspivot',
            'feepayment',
            'feepayment.feestructuremaster.feeHeads',
        ])
            ->where('roll_no', $roll)
            ->firstOrFail();

        // ---- FETCH EXEMPTIONS FOR THIS STUDENT ----
        $exemptions = StudentLateFeeExemption::where('student_id', $student->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('fee_structure_id');

        // Check if student has blanket exemption (fee_structure_id = null)
        $hasBlanketExemption = $exemptions->contains(function ($exemption) {
            return is_null($exemption->fee_structure_id);
        });

        // ---- FETCH APPLICABLE FEE STRUCTURES ----
        $applicableFS = FeesStructure::with('feeHeads')
            ->where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->programme);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year')
            ->get();

        // Only show fee structures for which late fee has been paid
        $paidFeeStructureIds = \App\Models\StudentPayment::where('late_fee_amount', '>', 0)
            ->where('status', 'success')
            ->distinct()
            ->pluck('fee_structure_id');
        $feeStructures = \App\Models\FeesStructure::whereIn('id', $paidFeeStructureIds)
            ->orderBy('quarter_title')
            ->get();

        // ---- PREPARE FEE STATUS ----
        $feeStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $exemptions, $hasBlanketExemption) {

            // Success payment
            $successPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->first();

            // Latest attempt
            $latestPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->sortByDesc('created_at')
                ->first();

            $baseAmount = $fs->feeHeads->sum('amount');

            // ---- LATE FEE LOGIC WITH EXEMPTION CHECK ----
            $lateDays = 0;
            $lateFee = 0;
            $isExempted = false;

            if (!$successPayment && $fs->due_date) {
                $dueDate = Carbon::parse($fs->due_date);
                $today   = Carbon::today();

                if ($today->gt($dueDate)) {
                    $lateDays = $dueDate->diffInDays($today);

                    // ---- CHECK EXEMPTION ----
                    $isExempted = $hasBlanketExemption || $exemptions->has($fs->id);

                    if (!$isExempted) {
                        $lateFee  = $lateDays * $lateFeePerDay;
                    }
                }
            }

            return [
                'fee_structure_id'   => $fs->id,
                'fee_structure_name' => $fs->quarter_title,
                'year'               => $fs->std_current_year,
                'quarter'            => $fs->quarter_no,
                'is_payable'         => $fs->is_payable,
                'base_amount'        => $baseAmount,
                'late_days'          => $lateDays,
                'late_fee'           => $lateFee,
                'is_late_fee_exempted' => $isExempted,
                'total_payable'      => $baseAmount + $lateFee,

                // CORE PAYMENT INFO
                'paid'               => $successPayment ? true : false,
                'paid_amount'        => $successPayment->amount ?? 0,
                'status'             => $successPayment
                    ? 'PAID'
                    : ($isExempted && $lateDays > 0 ? 'DUE (Late Fee Exempted)' : ($lateFee > 0 ? 'LATE' : 'DUE')),

                // UI / Debug
                'last_attempt_status' => $latestPayment->status ?? null,
                'paymentinfo'         => $latestPayment
            ];
        });

        // ---- FILTER OUT: PAID FEES & FEES WITH ACTIVE EXEMPTIONS ----
        $feeStatus = $feeStatus
            ->filter(function ($item) use ($hasBlanketExemption) {
                // Exclude if already paid
                if ($item['paid'] === true) {
                    return false;
                }

                // Exclude if already has blanket exemption
                if ($hasBlanketExemption) {
                    return false;
                }

                // Exclude if this specific fee already has an exemption
                if ($item['is_late_fee_exempted'] === true) {
                    return false;
                }

                return true;
            })
            ->values();

        // ---- RETURN JSON RESPONSE ----
        return response()->json($feeStatus);
    }

    /**
     * Show a report of all late fee payments by students.
     */
    public function lateFeeRevenueReport(Request $request)
    {
        // Fetch all batches and fee structures for filters
        $batches = \App\Models\BatchMaster::orderBy('batch_name')->get();
        $feeStructures = \App\Models\FeesStructure::whereIn('id', \App\Models\StudentPayment::where('late_fee_amount', '>', 0)->distinct()->pluck('fee_structure_id'))->orderBy('quarter_title')->get();

        $query = \App\Models\StudentPayment::with([
            'studentmaster.batchmaster',
            'feepaymentinfo.batch',
        ])
            ->where('late_fee_amount', '>', 0)
            ->where('status', 'success');

        // Apply filters
        if ($request->filled('batch')) {
            $query->whereHas('studentmaster', function ($q) use ($request) {
                $q->where('batch', $request->batch);
            });
        }
        if ($request->filled('fee_structure')) {
            $query->where('fee_structure_id', $request->fee_structure);
        }

        $lateFeePayments = $query->orderByDesc('transaction_date')->get();
        $totalRevenue = $lateFeePayments->sum('late_fee_amount');

        return view('admin.accounts.late-fee-revenue-report', [
            'lateFeePayments' => $lateFeePayments,
            'batches' => $batches,
            'feeStructures' => $feeStructures,
            'totalRevenue' => $totalRevenue,
            'selectedBatch' => $request->batch,
            'selectedFeeStructure' => $request->fee_structure,
        ]);
    }

    function defaultersList(Request $request)
    {
        //fetch user's campus
        $campusId =  StaticController::fetchCampusSettings();
        if ($campusId == null) {
            $query = StudentMaster::with([
                'batchmaster',
                'programgroup.programInfo',
                'stdfeestructure',
                'stdfeestructure.programspivot',
                'feepayment'
            ]);
        } else {
            $query = StudentMaster::with([
                'batchmaster',
                'programgroup.programInfo',
                'stdfeestructure',
                'stdfeestructure.programspivot',
                'feepayment'
            ])->where('campus_id', $campusId);
        }


        // ---- Apply Filters ----
        if ($request->filter_batch) {
            $query->where('batch', $request->filter_batch);
        }

        if ($request->filter_semester) {
            $query->where('current_year', $request->filter_semester);
        }

        if ($request->filter_program) {
            $query->whereHas('programgroup.programInfo', function ($q) use ($request) {
                $q->where('id', $request->filter_program);
            });
        }

        $students = $query->get();

        $defaulters = [];

        $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount') ?? 0;

        foreach ($students as $student) {
            $applicableFS = FeesStructure::where('batch_id', $student->batch)
                ->whereHas('programspivot', function ($q) use ($student) {
                    $q->where('std_program_id', $student->programme);
                })
                ->whereIn('std_current_year', range(1, $student->current_year))
                ->where('is_payable', 1)
                ->get();

            foreach ($applicableFS as $fs) {
                $payment = $student->feepayment
                    ->where('fee_structure_id', $fs->id)
                    ->where('student_id', $student->id)
                    ->where('status', 'success')
                    ->first();

                if (!$payment && $fs->due_date) {
                    $dueDate = Carbon::parse($fs->due_date)->timezone('asia/kolkata');
                    $today = Carbon::today()->timezone('asia/kolkata');

                    if ($today->gt($dueDate)) {
                        $lateDays = $dueDate->diffInDays($today);
                        $lateFee = $lateDays * $lateFeePerDay;

                        $defaulters[] = [
                            'student' => $student,
                            'fee_structure' => $fs,
                            'late_days' => $lateDays,
                            'late_fee' => $lateFee,
                            'due_date' => $fs->due_date,
                        ];
                    }
                }
            }
        }

        return view('admin.accounts.defaulters', [
            'defaulters' => $defaulters
        ]);
    }

    function admissionApplicationFee(Request $request)
    {

        $data = AdmissionApplicationPaymentLog::with('applicationmaster.registrationmaster.campusmaster')->latest()->get();
        return view('admin.accounts.admission-fee-collection', [
            'data' => $data
        ]);
    }

    public function feeHeadWiseReport(Request $request)
    {
        $batches = BatchMaster::orderBy('batch_name')->get();

        $query = DB::table('student_payments as sp')
            ->join('fee_structure_has_heads as fshh', 'sp.fee_structure_id', '=', 'fshh.fee_structure_id')
            ->join('fee_heads as fh', 'fshh.fee_head_id', '=', 'fh.id')
            ->where('sp.status', 'success')
            ->whereNull('fh.deleted_at')
            ->select(
                'fh.id',
                'fh.head_name',
                DB::raw('SUM(fshh.amount) as total_collected'),
                DB::raw('COUNT(DISTINCT sp.id) as payment_count')
            )
            ->groupBy('fh.id', 'fh.head_name');

        if ($request->filled('from_date')) {
            $query->where('sp.transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('sp.transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('batch')) {
            $query->join('student_masters as sm', 'sp.student_id', '=', 'sm.id')
                ->where('sm.batch', $request->batch);
        }

        $report = $query->orderByDesc('total_collected')->get();
        $totalCollected = $report->sum('total_collected');

        return view('admin.accounts.fee-head-wise-report', compact('report', 'totalCollected', 'batches'));
    }

    public function bankAccountWiseReport(Request $request)
    {
        $query = DB::table('student_payments as sp')
            ->join('fee_structure_has_heads as fshh', 'sp.fee_structure_id', '=', 'fshh.fee_structure_id')
            ->join('fee_heads as fh', 'fshh.fee_head_id', '=', 'fh.id')
            ->join('college_bank_accounts as cba', 'fh.bank_acc_id', '=', 'cba.id')
            ->where('sp.status', 'success')
            ->whereNull('fh.deleted_at')
            ->whereNull('cba.deleted_at')
            ->select(
                'cba.id',
                'cba.acc_label',
                'cba.acc_name',
                'cba.acc_no',
                'cba.bank_name',
                'cba.branch',
                DB::raw('SUM(fshh.amount) as total_collected'),
                DB::raw('COUNT(DISTINCT sp.id) as payment_count')
            )
            ->groupBy('cba.id', 'cba.acc_label', 'cba.acc_name', 'cba.acc_no', 'cba.bank_name', 'cba.branch');

        if ($request->filled('from_date')) {
            $query->where('sp.transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('sp.transaction_date', '<=', $request->to_date);
        }

        $report = $query->orderByDesc('total_collected')->get();
        $totalCollected = $report->sum('total_collected');

        return view('admin.accounts.bank-account-wise-report', compact('report', 'totalCollected'));
    }

    public function paymentReportByDate(Request $request)
    {
        $query = StudentPayment::with(['studentmaster', 'feepaymentinfo', 'gatewaytype'])
            ->where('status', 'success');

        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $payments = $query->orderByDesc('transaction_date')->get();
        $totalAmount = $payments->sum('amount');

        return view('admin.accounts.payment-report-by-date', compact('payments', 'totalAmount'));
    }

    public function paymentTypeReport(Request $request)
    {
        $query = StudentPayment::with(['studentmaster', 'feepaymentinfo', 'gatewaytype'])
            ->where('status', 'success');

        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('payment_type')) {
            if ($request->payment_type === 'CASH') {
                $query->whereIn('gateway_type_id', [3, 4]);
            } elseif ($request->payment_type === 'ONLINE') {
                $query->whereIn('gateway_type_id', [1, 2]);
            }
        }

        $payments = $query->orderByDesc('transaction_date')->get();
        $cashTotal    = $payments->whereIn('gateway_type_id', [3, 4])->sum('amount');
        $onlineTotal  = $payments->whereIn('gateway_type_id', [1, 2])->sum('amount');
        $grandTotal   = $payments->sum('amount');

        return view('admin.accounts.payment-type-report', compact('payments', 'cashTotal', 'onlineTotal', 'grandTotal'));
    }
}

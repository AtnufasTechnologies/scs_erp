<?php

namespace App\Http\Controllers;

use App\Models\FailedTransaction;
use App\Models\FailedTransactionLog;
use App\Models\FeesStructure;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\PaymentGatewayType;
use App\Models\StudentMaster;
use App\Models\StudentPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Easebuzz\PayWithEasebuzzLaravel\Lib\EasebuzzLib\Easebuzz;
use Illuminate\Support\Facades\Auth;

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
        $data = $query->paginate(20)->withQueryString(); // <<<<<< THIS IS THE KEY

        // ---- TRANSFORM EACH RECORD USING through() ----
        $students = $data->through(function ($student) {

            $applicableFS = FeesStructure::where('batch_id', $student->batch)
                ->whereHas('programspivot', function ($q) use ($student) {
                    $q->where('std_program_id', $student->programme);
                })
                ->whereIn('std_current_year', range(1, $student->current_year))
                ->get();

            $fsWithStatus = $applicableFS->map(function ($fs) use ($student) {

                $payment = $student->feepayment
                    ->where('fee_structure_id', $fs->id)
                    ->where('student_id', $student->id)
                    ->where('status', 'success')
                    ->first();

                return [
                    'paymentinfo' => $payment,
                    'fee_structure_id' => $fs->id,
                    'quarter' => $fs->quarter_title,
                    'year' => $fs->std_current_year,
                    'total_amount' => $fs->feeHeads->sum('amount'),
                    'paid' => $payment ? true : false,
                    'paid_amount' => $payment->amount ?? 0,
                    'status' => $payment->status ?? 'NOT PAID',
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

        return $this->showSuccessPage($payment->invoice_id);
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


    //     // Fetch student
    //     $student = StudentMaster::with([
    //         'batchmaster',
    //         'programgroup.programInfo',
    //         'stdfeestructure',
    //         'stdfeestructure.programspivot',
    //         'feepayment'
    //     ])->where('roll_no', $roll)
    //         ->first();



    //     // ---- FETCH APPLICABLE FEE STRUCTURES ----
    //     $applicableFS = FeesStructure::where('batch_id', $student->batch)
    //         ->whereHas('programspivot', function ($q) use ($student) {
    //             $q->where('std_program_id', $student->programme);
    //         })
    //         ->whereIn('std_current_year', range(1, $student->current_year))
    //         ->where('is_payable', 1)
    //         ->get();

    //     $feeStatus = $applicableFS->map(function ($fs) use ($student) {

    //         // SUCCESS payment (paid quarter)
    //         $successPayment = $student->feepayment
    //             ->where('fee_structure_id', $fs->id)
    //             ->where('student_id', $student->id)
    //             ->where('status', 'success')
    //             ->first();

    //         // Failed / pending payments (optional info)
    //         $latestPayment = $student->feepayment
    //             ->where('fee_structure_id', $fs->id)
    //             ->where('student_id', $student->id)
    //             ->sortByDesc('created_at')
    //             ->first();

    //         return [
    //             'fee_structure_id'   => $fs->id,
    //             'fee_structure_name' => $fs->quarter_title,
    //             'year'               => $fs->std_current_year,
    //             'total_amount'       => $fs->feeHeads->sum('amount'),

    //             // CORE LOGIC
    //             'paid'               => $successPayment ? true : false,
    //             'paid_amount'        => $successPayment->amount ?? 0,
    //             'status'             => $successPayment ? 'PAID' : 'NOT PAID',

    //             // Optional (for debugging / UI)
    //             'paymentinfo'        => $latestPayment
    //         ];
    //     });


    //     // ---- FINAL RESPONSE ARRAY ----
    //     $studentData = [
    //         'studentinfo' => [
    //             'id'       => $student->id,
    //             'fullname' => $student->fullname,
    //             'rollno'   => $student->roll_no,
    //             'mobile'   => $student->mobile_no,
    //             'email'    => $student->mail_id,
    //         ],
    //         'programinfo' => $student->programgroup->programInfo->name ?? '',
    //         'batch'       => $student->batchmaster->batch_name ?? '',
    //         'current_year' => $student->current_year,
    //         'feesinfo'    => $feeStatus
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

        // ---- FETCH APPLICABLE FEE STRUCTURES (QUARTERS) ----
        $applicableFS = FeesStructure::with('feeHeads')
            ->where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->programme);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->where('is_payable', 1)
            ->orderBy('std_current_year')
            ->get();

        // ---- PREPARE FEE STATUS (QUARTER-WISE) ----
        $feeStatus = $applicableFS->map(function ($fs) use ($student) {

            // Check if SUCCESS payment exists for this quarter
            $successPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->first();

            // Get latest payment attempt (success / failed / pending)
            $latestPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->sortByDesc('created_at')
                ->first();

            return [
                'fee_structure_id'   => $fs->id,
                'fee_structure_name' => $fs->quarter_title,
                'year'               => $fs->std_current_year,
                'quarter'            => $fs->quarter_no,

                'total_amount'       => $fs->feeHeads->sum('amount'),

                // CORE LOGIC
                'paid'               => $successPayment ? true : false,
                'paid_amount'        => $successPayment->amount ?? 0,
                'status'             => $successPayment ? 'PAID' : 'NOT PAID',

                // Optional debug / UI info
                'last_attempt_status' => $latestPayment->status ?? null,
                'paymentinfo'        => $latestPayment
            ];
        });

        // ---- OPTIONAL: SHOW ONLY UNPAID QUARTERS ----
        $feeStatus = $feeStatus
            ->filter(fn($item) => $item['paid'] === false)
            ->values();

        // ---- FINAL RESPONSE ----
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

        // Generate UNIQUE Invoice
        $prefix = $gateway === 'easebuzz' ? 'EZ' : 'BL';
        $invoice = StaticController::generateInvoiceId($prefix . $studentId);

        /** Remove previous initiated payments for same fees */
        StudentPayment::where('student_id', $studentId)
            ->whereIn('fee_structure_id', $feeStructureIds)
            ->where('status', 'initiated')
            ->delete();

        /** Insert new payment rows */
        foreach ($feeStructureIds as $feeId) {

            $amount = FeeStructureHasHead::where('fee_structure_id', $feeId)->sum('amount');

            $rec = new StudentPayment();
            $rec->invoice_id = $invoice;
            $rec->student_id = $studentId;
            $rec->fee_structure_id = $feeId;
            $rec->status = 'intiated';
            $rec->amount = $amount;
            $rec->transaction_date = Carbon::now();
            $rec->gateway_type_id = $paymentGatewayId;
            $rec->save();
        }

        /** Calculate FINAL payable amount */
        $payableAmount = StudentPayment::where('invoice_id', $invoice)
            ->where('student_id', $studentId)
            ->sum('amount');

        /** SPLIT PAYMENT (MULTIPLE FEES SAFE) */
        $splitData = FeeStructureHasHead::whereIn('fee_structure_id', $feeStructureIds)
            ->with('head.bankmaster:id,acc_label')
            ->get();

        $split = [];
        foreach ($splitData as $item) {
            $label = $item->head->bankmaster->acc_label;
            $split[$label] = ($split[$label] ?? 0) + (float) $item->amount;
        }

        $splitPayments = json_encode($split);

        /** Student Details */
        $student = StudentMaster::findOrFail($studentId);

        /** Easebuzz Params */
        $key = env('EASEBUZZ_KEY');
        $salt = env('EASEBUZZ_SALT');
        $txnid = $invoice;
        $productinfo = 'Salesian College Autonomous - Fee Payment';

        $hashString = "$key|$txnid|$payableAmount|$productinfo|{$student->fullname}|{$student->mail_id}|$studentId||||||||||$salt";
        $hash = strtolower(hash('sha512', $hashString));

        /** Initiate Payment */
        $client = new \GuzzleHttp\Client();
        $response = $client->post(env('EASEBUZZ_INITIATE_URL'), [
            'form_params' => [
                'key' => $key,
                'txnid' => $txnid,
                'amount' => $payableAmount,
                'productinfo' => $productinfo,
                'firstname' => $student->fullname,
                'phone' => $student->mobile_no,
                'email' => $student->mail_id,
                'surl' => route('payment.success'),
                'furl' => route('payment.failure'),
                'hash' => $hash,
                'udf1' => $studentId,
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

    function showSuccessPage($txnId)
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
}

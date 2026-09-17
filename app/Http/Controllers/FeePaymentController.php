<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplicationPaymentLog;
use App\Models\FinancialYearMaster;
use App\Models\FailedTransaction;
use App\Models\FailedTransactionLog;
use App\Models\FeesStructure;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\DegreeTrackMaster;
use App\Models\LateFee;
use App\Models\PaymentGatewayType;
use App\Models\StudentMaster;
use App\Models\StudentPayment;
use App\Models\StudentLateFeeExemption;
use App\Models\StudentFullFeeExemption;
use App\Models\StudentQuarterFeeExemption;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Easebuzz\PayWithEasebuzzLaravel\Lib\EasebuzzLib\Easebuzz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\BatchMaster;
use App\Models\FeeHead;
use App\Models\CollegeBankAccount;

class FeePaymentController extends Controller
{
    private function getActiveFinancialYear(): ?FinancialYearMaster
    {
        return FinancialYearMaster::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }

    private function getFinancialYearOptions()
    {
        return FinancialYearMaster::query()
            ->orderByDesc('start_date')
            ->get(['id', 'title', 'start_date', 'end_date', 'is_active']);
    }

    private function resolveFinancialYearFromRequest(Request $request, ?FinancialYearMaster $activeFinancialYear = null): ?FinancialYearMaster
    {
        $activeFinancialYear = $activeFinancialYear ?: $this->getActiveFinancialYear();
        $selectedFinancialYearId = (int) $request->input('financial_year_id', 0);

        if ($selectedFinancialYearId > 0) {
            return FinancialYearMaster::query()->find($selectedFinancialYearId) ?: $activeFinancialYear;
        }

        return $activeFinancialYear;
    }

    private function applyFinancialYearFilter($query, ?FinancialYearMaster $activeFinancialYear, string $column = 'transaction_date')
    {
        if ($activeFinancialYear) {
            $query->whereDate($column, '>=', $activeFinancialYear->start_date)
                ->whereDate($column, '<=', $activeFinancialYear->end_date);
        }

        return $query;
    }

    private function inActiveFinancialYear($payment, ?FinancialYearMaster $activeFinancialYear): bool
    {
        if (!$activeFinancialYear) {
            return true;
        }

        $date = $payment->transaction_date ?? $payment->created_at ?? null;
        if (empty($date)) {
            return false;
        }

        $paymentDate = Carbon::parse($date)->startOfDay();
        $start = Carbon::parse($activeFinancialYear->start_date)->startOfDay();
        $end = Carbon::parse($activeFinancialYear->end_date)->endOfDay();

        return $paymentDate->between($start, $end);
    }

    private function getFixedLateFeeMapForInvoice(int $studentId, $feeStructureIds): array
    {
        $ids = collect($feeStructureIds)->filter()->unique()->values();

        if ($studentId <= 0 || $ids->isEmpty()) {
            return [];
        }

        return StudentLateFeeExemption::where('student_id', $studentId)
            ->whereIn('fee_structure_id', $ids)
            ->where('is_active', true)
            ->whereNotNull('fixed_late_fee')
            ->get()
            ->pluck('fixed_late_fee', 'fee_structure_id')
            ->map(function ($amount) {
                return (float) $amount;
            })
            ->toArray();
    }

    private function getActiveFullFeeExemption(int $studentId): ?StudentFullFeeExemption
    {
        if ($studentId <= 0) {
            return null;
        }

        return StudentFullFeeExemption::where('student_id', $studentId)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    private function getActiveQuarterFeeExemptionMap(int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }

        return StudentQuarterFeeExemption::where('student_id', $studentId)
            ->where('is_active', true)
            ->get()
            ->keyBy('fee_structure_id')
            ->toArray();
    }

    private function applyFeeStructureApplicabilityFilters($query, StudentMaster $student)
    {
        if (Schema::hasColumn('fees_structures', 'academic_pathway_id')) {
            if (!empty($student->academic_pathway_id)) {
                $query->where('academic_pathway_id', (int) $student->academic_pathway_id);
            } else {
                $query->whereNull('academic_pathway_id');
            }
        }

        if (Schema::hasColumn('fees_structures', 'degree_track_id')) {
            if (!empty($student->degree_track_id)) {
                $regularDegreeTrackId = DegreeTrackMaster::query()
                    ->whereRaw('LOWER(name) = ?', ['regular'])
                    ->value('id');

                $query->where(function ($subQuery) use ($student, $regularDegreeTrackId) {
                    $subQuery->where('degree_track_id', (int) $student->degree_track_id)
                        ->orWhereNull('degree_track_id');

                    if (!empty($regularDegreeTrackId) && (int) $regularDegreeTrackId !== (int) $student->degree_track_id) {
                        $subQuery->orWhere('degree_track_id', (int) $regularDegreeTrackId);
                    }
                });
            } else {
                $query->whereNull('degree_track_id');
            }
        }

        return $query;
    }

    function index(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();

        // ---- Base Query ----
        $query = StudentMaster::with([
            'batchmaster',
            'programgroup.programInfo',
            'stdfeestructure.feeHeads',
            'stdfeestructure',
            // 'stdfeestructure.programspivot',
            'stdprogramenrolled',  //newly added direct link to student program
            'degreetrack',
            'feepayment'
        ]);

        // ---- Filters ----

        if ($request->roll_no) {
            $searchValues = preg_split('/\s+/', $request->roll_no, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->orWhere('roll_no', 'LIKE', "%$value%");
                    $q->orWhere('first_name', 'LIKE', "%$value%");
                    $q->orWhere('last_name', 'LIKE', "%$value%");
                }
            });
        }

        if ($request->filter_batch) {
            $query->where('batch', $request->filter_batch);
        }

        if ($request->filter_pgr) {
            $query->where('new_program_id', $request->filter_pgr);
        }

        if ($request->filter_campus) {
            $query->where('campus_id', (int) $request->filter_campus);
        }

        if ($request->filter_year) {
            $query->where('current_year', (int) $request->filter_year);
        }

        if ($request->filter_pathway) {
            $query->where('academic_pathway_id', (int) $request->filter_pathway);
        }

        if ($request->filter_degree_track) {
            $query->where('degree_track_id', (int) $request->filter_degree_track);
        }

        if ($request->payment_state === 'paid') {
            $query->whereHas('feepayment', function ($q) use ($selectedFinancialYear) {
                $q->where('status', 'success');
                $this->applyFinancialYearFilter($q, $selectedFinancialYear, 'transaction_date');
            });
        } elseif ($request->payment_state === 'unpaid') {
            $query->whereDoesntHave('feepayment', function ($q) use ($selectedFinancialYear) {
                $q->where('status', 'success');
                $this->applyFinancialYearFilter($q, $selectedFinancialYear, 'transaction_date');
            });
        }

        if ($sortBy === 'name') {
            $query->orderBy('first_name', $sortDir)->orderBy('last_name', $sortDir);
        } else {
            $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
        }

        // ---- PAGINATION ----
        $data = $query->paginate(36)->appends($request->query());


        // ---- TRANSFORM EACH RECORD USING through() ----
        $students = $data->through(function ($student) use ($selectedFinancialYear) {

            $fullFeeExemption = $this->getActiveFullFeeExemption((int) $student->id);
            $isFullFeeExempted = !is_null($fullFeeExemption);
            $quarterFeeExemptions = $this->getActiveQuarterFeeExemptionMap((int) $student->id);

            $exemptions = StudentLateFeeExemption::where('student_id', $student->id)
                ->where('is_active', true)
                ->get()
                ->keyBy('fee_structure_id');

            $hasBlanketExemption = $exemptions->contains(function ($exemption) {
                return is_null($exemption->fee_structure_id);
            });

            $applicableFS = FeesStructure::with(['feeHeads.head.bankmaster'])
                ->where('batch_id', $student->batch)
                ->whereHas('programspivot', function ($q) use ($student) {
                    $q->where('std_program_id', $student->new_program_id);
                })
                ->whereIn('std_current_year', range(1, $student->current_year));

            $applicableFS = $this->applyFeeStructureApplicabilityFilters($applicableFS, $student)->get();
            $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount'); // 100

            $fsWithStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $exemptions, $hasBlanketExemption, $quarterFeeExemptions, $selectedFinancialYear) {

                $payment = $student->feepayment
                    ->where('fee_structure_id', $fs->id)
                    ->where('student_id', $student->id)
                    ->where('status', 'success')
                    ->filter(fn($item) => $this->inActiveFinancialYear($item, $selectedFinancialYear))
                    ->first();

                $quarterExemption = $quarterFeeExemptions[(int) $fs->id] ?? null;
                $isQuarterFeeExempted = !is_null($quarterExemption);

                $totalAmount = $fs->feeHeads->sum('amount');

                // ---- LATE FEE CALCULATION ----
                $lateDays = 0;
                $lateFee = 0;
                $isExempted = false;
                $fixedLateFee = null;

                if (!$payment) {
                    $dueDate = Carbon::parse($fs->due_date)->timezone('asia/kolkata');

                    $today = Carbon::today()->timezone('asia/kolkata');

                    if ($today->gt($dueDate)) {

                        $lateDays = $dueDate->diffInDays($today);

                        $isExempted = $hasBlanketExemption || $exemptions->has($fs->id);
                        if ($isExempted) {
                            $exemption = $hasBlanketExemption
                                ? $exemptions->first(function ($e) {
                                    return is_null($e->fee_structure_id);
                                })
                                : $exemptions->get($fs->id);

                            if ($exemption && !is_null($exemption->fixed_late_fee)) {
                                $fixedLateFee = (float) $exemption->fixed_late_fee;
                                $lateFee = $fixedLateFee;
                            }
                        } else {
                            $lateFee = $lateDays * $lateFeePerDay;
                        }
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

                $appliedExemption = $hasBlanketExemption
                    ? $exemptions->first(function ($e) {
                        return is_null($e->fee_structure_id);
                    })
                    : $exemptions->get($fs->id);

                $paidBaseAmount = (float) ($payment->amount ?? 0);
                $paidLateFeeAmount = (float) ($payment->late_fee_amount ?? 0);
                $paidFixedLateFeeAmount = !is_null($appliedExemption?->fixed_late_fee)
                    ? (float) $appliedExemption->fixed_late_fee
                    : null;
                $displayPaidLateFeeAmount = !is_null($paidFixedLateFeeAmount)
                    ? $paidFixedLateFeeAmount
                    : $paidLateFeeAmount;
                $displayPaidTotalAmount = $paidBaseAmount + $displayPaidLateFeeAmount;

                return [
                    'paymentinfo' => $payment,
                    'fee_structure_id' => $fs->id,
                    'quarter' => $fs->quarter_title,
                    'is_payable' => $fs->is_payable == 1 ? 'Active' : 'Inactive',
                    'year' => $fs->std_current_year,
                    'total_amount' => $totalAmount,
                    'late_days' => $lateDays,
                    'late_fee' => $lateFee,
                    'is_late_fee_exempted' => $isExempted,
                    'is_quarter_fee_exempted' => $isQuarterFeeExempted,
                    'quarter_fee_exemption_reason' => $quarterExemption['reason'] ?? null,
                    'fixed_late_fee' => $fixedLateFee,
                    'payable_amount' => ($payment || !$isQuarterFeeExempted) ? ($totalAmount + $lateFee) : 0,
                    'paid' => $payment ? true : false,
                    'paid_amount' => $payment->amount ?? 0,
                    'paid_base_amount' => $paidBaseAmount,
                    'paid_late_fee_amount' => $paidLateFeeAmount,
                    'paid_late_days' => (int) ($payment->late_days ?? 0),
                    'paid_total_amount' => $paidBaseAmount + $paidLateFeeAmount,
                    'paid_fixed_late_fee' => $paidFixedLateFeeAmount,
                    'display_paid_late_fee_amount' => $displayPaidLateFeeAmount,
                    'display_paid_total_amount' => $displayPaidTotalAmount,
                    'status' => $payment
                        ? 'success'
                        : ($isQuarterFeeExempted ? 'quarter-fee-exempted' : ($isExempted && $lateDays > 0 ? 'due-exempted' : ($lateFee > 0 ? 'late' : 'due'))),
                    'bank_accounts' => $bankAccounts,
                ];
            });

            if ($isFullFeeExempted) {
                $fsWithStatus = $fsWithStatus->map(function ($fee) use ($fullFeeExemption) {
                    if (($fee['status'] ?? '') === 'success') {
                        return $fee;
                    }

                    $fee['late_days'] = 0;
                    $fee['late_fee'] = 0;
                    $fee['payable_amount'] = 0;
                    $fee['is_late_fee_exempted'] = true;
                    $fee['status'] = 'full-fee-exempted';
                    $fee['full_fee_exempted'] = true;
                    $fee['full_fee_exemption_reason'] = $fullFeeExemption->reason;

                    return $fee;
                });
            }


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
                'stdprogramenrolled' => $student->stdprogramenrolled,
                'academic_pathway_label' => ((int) ($student->academic_pathway_id ?? 0) === 1)
                    ? 'Single Major'
                    : (((int) ($student->academic_pathway_id ?? 0) === 2) ? 'Dual Major' : 'Not Set'),
                'degree_track_label' => $student->degreetrack->name ?? 'Not Set',
                'current_year' => $student->current_year,
                'is_full_fee_exempted' => $isFullFeeExempted,
                'full_fee_exemption_reason' => $isFullFeeExempted ? $fullFeeExemption->reason : null,
                'fee_status' => $fsWithStatus
            ];
        });
        // ---- Return view ----


        return view('admin.accounts.fee-payment-records', [
            'data' => $students,
            'activeFinancialYear' => $activeFinancialYear,
            'selectedFinancialYear' => $selectedFinancialYear,
            'financialYears' => $financialYears,
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
            'late_fee_amount' => 'nullable|numeric',
            'late_days' => 'nullable|integer',
        ]);

        /** Payment Gateway Logic
            'activeFinancialYear' => $activeFinancialYear,
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


        $student = StudentMaster::findOrFail($request->student_id);

        if ($this->getActiveFullFeeExemption((int) $student->id)) {
            return redirect()->back()->with('error', 'Full course fee exemption is active for this student. Payment entry is blocked.');
        }

        $hasQuarterExemption = StudentQuarterFeeExemption::where('student_id', (int) $student->id)
            ->where('fee_structure_id', (int) $request->fee_structure_id)
            ->where('is_active', true)
            ->exists();

        if ($hasQuarterExemption) {
            return redirect()->back()->with('error', 'Quarter fee exemption is active for this student and selected fee. Payment entry is blocked.');
        }

        $rec = new StudentPayment();
        $rec->invoice_id = $invoice;
        $rec->student_id = $request->student_id;
        $rec->roll_no = $student->roll_no;
        $rec->fee_structure_id = $request->fee_structure_id;
        $rec->status = 'success';
        $rec->amount = $request->amount;
        $rec->transaction_date = $request->transaction_date;
        $rec->transaction_id = $request->transaction_ref;
        $rec->gateway_type_id = $request->gateway_type_id;
        $rec->late_fee_amount = $request->late_fee_amount ?? 0;
        $rec->late_days = $request->late_days ?? 0;
        $rec->message = "Manual Entry from Accounts Office";
        $rec->save();
        return redirect()->back()->with('success', 'Payment updated successfully!');
    }





    public function generateInvoice($rollno)
    {
        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'stdprogramenrolled',
        ])->where('roll_no', $rollno)->firstOrFail();

        $payments = StudentPayment::with('feepaymentinfo:id,quarter_title')
            ->where('student_id', $student->id)
            ->whereRaw('LOWER(status) = ?', ['success'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $fixedLateFeeMap = $this->getFixedLateFeeMapForInvoice($student->id, $payments->pluck('fee_structure_id'));

        $paidInvoices = [];
        $totalPaid = 0;

        foreach ($payments as $payment) {
            $amount = FeeStructureHasHead::where('fee_structure_id', $payment->fee_structure_id)->sum('amount');
            $storedLateFee = (float)($payment->late_fee_amount ?? 0);
            $hasFixedLateFee = array_key_exists((int) $payment->fee_structure_id, $fixedLateFeeMap);
            $lateFee = $hasFixedLateFee
                ? (float) $fixedLateFeeMap[(int) $payment->fee_structure_id]
                : $storedLateFee;

            $paidInvoices[] = [
                'quarter'        => $payment->feepaymentinfo->quarter_title ?? 'N/A',
                'payable_amount' => $amount,
                'late_fee'       => $lateFee,
                'has_fixed_late_fee' => $hasFixedLateFee,
                'grand_amount'   => $amount + $lateFee,
                'status'         => 'PAID',
                'paid_on'        => $payment->transaction_date ?? 'N/A',
                'inv_id'         => $payment->invoice_id ?? 'N/A',
            ];

            $totalPaid += $amount + $lateFee;
        }


        return view('pdf.fee-invoice', [
            'student' => $student,
            'paidInvoices' => $paidInvoices,
            'total_paid' => $totalPaid,
            'invoice_no' => "INV-" . now()->format('Ymd') . "-" . $student->id,
        ]);
    }


    function generateFeeReciept(int $feeId)
    {
        $paymentRecord = StudentPayment::findOrFail($feeId);

        $student = StudentMaster::with([
            'campusmaster',
            'batchmaster',
            'programGroup.feeprogpivot.feeStructure',
            'programGroup.programInfo',
            'feepayment' // your payment table
        ])->where('id', $paymentRecord->student_id)->firstOrFail();

        if ($student->feepayment == null) {
            abort(404, "No successful payment found for this fee");
        }

        $payment = $student->feepayment
            ->where('id', $feeId)
            ->where('status', 'success')
            ->first();

        // Build fee-structure-wise late fee overrides for this invoice.
        $invoiceFeeStructureIds = StudentPayment::where('invoice_id', $payment->invoice_id)
            ->pluck('fee_structure_id')
            ->filter()
            ->unique()
            ->values();

        $fixedLateFeeMap = $this->getFixedLateFeeMapForInvoice($student->id, $invoiceFeeStructureIds);

        return $this->showSuccessPage($payment->invoice_id, $fixedLateFeeMap);
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

        $fullFeeExemption = $this->getActiveFullFeeExemption((int) $student->id);
        if ($fullFeeExemption) {
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
                'feesinfo'     => [],
                'is_full_fee_exempted' => true,
                'full_fee_exemption_reason' => $fullFeeExemption->reason,
            ];

            return view('student.gateway-selection', [
                'data' => $studentData
            ]);
        }

        $quarterFeeExemptions = $this->getActiveQuarterFeeExemptionMap((int) $student->id);

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
                $q->where('std_program_id', $student->new_program_id);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year');

        $applicableFS = $this->applyFeeStructureApplicabilityFilters($applicableFS, $student)->get();
        // ---- PREPARE FEE STATUS ----
        $feeStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $exemptions, $hasBlanketExemption, $quarterFeeExemptions) {
            // Success payment
            $successPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->first();

            $quarterExemption = $quarterFeeExemptions[(int) $fs->id] ?? null;
            $isQuarterFeeExempted = !is_null($quarterExemption);

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
                'is_quarter_fee_exempted' => $isQuarterFeeExempted,
                'quarter_fee_exemption_reason' => $quarterExemption['reason'] ?? null,
                'total_payable'      => ($successPayment || !$isQuarterFeeExempted) ? ($baseAmount + $lateFee) : 0,

                // CORE PAYMENT INFO
                'paid'               => $successPayment ? true : false,
                'paid_amount'        => $successPayment->amount ?? 0,
                'status'             => $successPayment
                    ? 'PAID'
                    : ($isQuarterFeeExempted ? 'EXEMPTED' : ($isExempted && $lateDays > 0 ? 'DUE (Late Fee Exempted)' : ($lateFee > 0 ? 'LATE' : 'DUE'))),

                // UI / Debug
                'last_attempt_status' => $latestPayment->status ?? null,
                'paymentinfo'         => $latestPayment
            ];
        });

        // ---- FILTER OUT: PAID FEES & FEES WITH ACTIVE EXEMPTIONS ----
        $feeStatus = $feeStatus
            ->filter(fn($item) => $item['paid'] === false && $item['is_quarter_fee_exempted'] === false)
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
        // Context must be inferred server-side; never trust client input for redirect targets.
        $paymentContext = $request->is('erp/admin/accounts/*') ? 'accounts' : 'student';

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

        if (!$student) {
            return back()->withErrors('Student record not found.');
        }

        if ($this->getActiveFullFeeExemption((int) $student->id)) {
            return back()->withErrors('Full course fee exemption is active for this student. Online payment is blocked.');
        }

        $exemptedQuarterIds = StudentQuarterFeeExemption::where('student_id', (int) $student->id)
            ->where('is_active', true)
            ->whereIn('fee_structure_id', array_map('intval', $feeStructureIds))
            ->pluck('fee_structure_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        if (!empty($exemptedQuarterIds)) {
            return back()->withErrors('Quarter fee exemption is active for one or more selected fee structures. Online payment blocked for exempted fees.');
        }

        $allowedFeeIds = $this->applyFeeStructureApplicabilityFilters(
            FeesStructure::whereIn('id', $feeStructureIds),
            $student
        )->pluck('id')->map(fn($id) => (int) $id)->toArray();

        $invalidFeeIds = array_diff(array_map('intval', $feeStructureIds), $allowedFeeIds);
        if (!empty($invalidFeeIds)) {
            return back()->withErrors('Selected fee structure does not match the student academic pathway and degree track.');
        }

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
            $rec->message = $paymentContext === 'accounts'
                ? 'ONLINE_INITIATED_BY_ACCOUNTS'
                : 'ONLINE_INITIATED_BY_STUDENT';
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

        $udfValues = [
            (string) $studentId,
            (string) $roll_no,
            (string) $paymentContext,
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        $hashString = implode('|', array_merge(
            [$key, $txnid, (string) $finalPayable, $productinfo, $first_name, $mail_id],
            $udfValues,
            [$salt]
        ));

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
                'udf3' => $paymentContext,
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
        $paymentContext = $this->resolvePaymentContext($request->input('txnid'), $request->input('udf3'));

        if (!$this->isValidEasebuzzResponseHash($request)) {
            Log::warning('Rejected payment success callback due to invalid hash', [
                'txnid' => $request->txnid,
                'easepayid' => $request->easepayid,
                'ip' => $request->ip(),
            ]);

            if ($paymentContext === 'accounts') {
                return redirect('erp/admin/accounts/std-fee-payments')->with('error', 'Unable to verify payment response. Please contact support.');
            }

            return redirect('erp/student/fee-payment/')->with('error', 'Unable to verify payment response. Please contact support.');
        }

        $amount = $request->amount;
        $msg = $request->error_Message;
        $easepayid = $request->easepayid;
        $status = $request->status;
        $txnid = $request->txnid;

        //Online Transaction - Update Payment Record
        StudentPayment::where('invoice_id', $txnid)
            ->update(
                [
                    'gateway_ref_code' => $easepayid,
                    'captured_amount' => $amount,
                    'status' => $status,
                    'message' => $msg,
                ]
            );

        if ($paymentContext === 'accounts') {
            return redirect('erp/admin/accounts/transaction-info/' . $txnid . '?source=accounts')
                ->with('success', 'Online payment completed successfully.');
        }

        //show success page to Student
        return redirect('erp/student/transaction-success/' . $txnid);
    }

    function showSuccessPage($txnId, $fixedLateFeeMap = [])
    {
        $txnrecs =  StudentPayment::where('invoice_id', $txnId)->with([
            'studentmaster:id,first_name,last_name,roll_no,mobile_no,mail_id',
            'feepaymentinfo:id,quarter_title',
            'feepaymentinfo.feeHeads.head:id,head_name'
        ])->get();
        $data = json_decode($txnrecs, true);

        if (empty($data)) {
            abort(404, "Transaction not found");
        }

        if (empty($fixedLateFeeMap)) {
            $studentId = (int) ($txnrecs->first()->student_id ?? 0);
            $fixedLateFeeMap = $this->getFixedLateFeeMapForInvoice($studentId, $txnrecs->pluck('fee_structure_id'));
        }

        return view('includes.success-page', [
            'invoiceId' => $data[0]['invoice_id'] ?? 'N/A',
            'gatewayRef' => $data[0]['gateway_ref_code'] ?? 'N/A',
            'transactionDate' => $data[0]['transaction_date'] ?? now(),
            'student' => $data[0]['studentmaster'] ?? [],
            'transactions' => $data,
            'status' => $data[0]['status'] ?? 'pending',
            'gatewayType' => $data[0]['gateway_type_id'] ?? null,
            'fixedLateFeeMap' => $fixedLateFeeMap,
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

        if ($txnrecs->isEmpty()) {
            abort(404, "Transaction not found");
        }

        $transactions = json_decode($txnrecs, true);

        $studentId = (int) ($txnrecs->first()->student_id ?? 0);
        $fixedLateFeeMap = $this->getFixedLateFeeMapForInvoice($studentId, $txnrecs->pluck('fee_structure_id'));

        $data = [
            'invoiceId'        => $transactions[0]['invoice_id'],
            'gatewayRef'       => $transactions[0]['gateway_ref_code'],
            'transactionDate' => $transactions[0]['transaction_date'],
            'student'          => $transactions[0]['studentmaster'],
            'transactions'     => $transactions,
            'status'           => $transactions[0]['status'],
            'fixedLateFeeMap'  => $fixedLateFeeMap,
        ];

        $pdf = Pdf::loadView('includes.success-page', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download(
            'invoice-' . $data['invoiceId'] . '.pdf'
        );
    }

    public function paymentFailure(Request $request)
    {
        $paymentContext = $this->resolvePaymentContext($request->input('txnid'), $request->input('udf3'));

        if (!$this->isValidEasebuzzResponseHash($request)) {
            Log::warning('Rejected payment failure callback due to invalid hash', [
                'txnid' => $request->txnid,
                'easepayid' => $request->easepayid,
                'ip' => $request->ip(),
            ]);

            if ($paymentContext === 'accounts') {
                return redirect('erp/admin/accounts/std-fee-payments')->with('error', 'Unable to verify payment response. Please contact support.');
            }

            return redirect('erp/student/fee-payment/')->with('error', 'Unable to verify payment response. Please contact support.');
        }

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
                ]
            );

        if ($paymentContext === 'accounts') {
            return redirect('erp/admin/accounts/std-fee-payments')->with('error', 'Transaction failed. Please try again.');
        }

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

        $payment = StudentPayment::where('invoice_id', $txnid)->first();

        if (!$payment) {
            // maybe log and create a record
            Log::warning('Easebuzz webhook for unknown txn: ' . $txnid, [
                'txnid' => $txnid,
                'status' => $payload['status'] ?? null,
                'easepayid' => $payload['easepayid'] ?? null,
            ]);
            return response()->json(['status' => 'ok']);
        }

        if (!$this->isValidEasebuzzResponseHash($request)) {
            Log::warning('Rejected webhook due to invalid hash', [
                'txnid' => $txnid,
                'easepayid' => $payload['easepayid'] ?? null,
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'invalid hash'], 400);
        }

        // Update according to webhook payload status
        $status = $payload['status'] ?? 'pending';
        $payment->update([
            'status' => strtoupper($status),
            'raw_response' => json_encode($this->sanitizedEasebuzzPayload($payload))
        ]);

        // perform reconciliation, ledger updates etc.

        return response()->json(['status' => 'ok']);
    }

    private function isValidEasebuzzResponseHash(Request $request): bool
    {
        $receivedHash = strtolower((string) $request->input('hash', ''));
        $status = (string) $request->input('status', '');
        $txnid = (string) $request->input('txnid', '');
        $amount = (string) $request->input('amount', '');
        $productinfo = (string) $request->input('productinfo', '');
        $firstname = (string) $request->input('firstname', '');
        $email = (string) $request->input('email', '');
        $key = (string) env('EASEBUZZ_KEY');
        $salt = (string) env('EASEBUZZ_SALT');

        if ($receivedHash === '' || $status === '' || $txnid === '' || $key === '' || $salt === '') {
            return false;
        }

        $udfs = [];
        for ($i = 1; $i <= 10; $i++) {
            $udfs[] = (string) $request->input("udf{$i}", '');
        }

        $reverseUdfs = implode('|', array_reverse($udfs));
        $baseSequence = $salt
            . '|' . $status
            . '|' . $reverseUdfs
            . '|' . $email
            . '|' . $firstname
            . '|' . $productinfo
            . '|' . $amount
            . '|' . $txnid
            . '|' . $key;

        $expectedHashes = [strtolower(hash('sha512', $baseSequence))];

        $additionalCharges = $request->input('additionalCharges');
        if (!is_null($additionalCharges) && $additionalCharges !== '') {
            $expectedHashes[] = strtolower(hash('sha512', $additionalCharges . '|' . $baseSequence));
        }

        foreach ($expectedHashes as $expectedHash) {
            if (hash_equals($expectedHash, $receivedHash)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizedEasebuzzPayload(array $payload): array
    {
        unset($payload['hash'], $payload['key'], $payload['salt']);

        return $payload;
    }

    private function resolvePaymentContext(?string $invoiceId, ?string $udf3): string
    {
        $contextFromDb = null;

        if (!empty($invoiceId)) {
            $contextMessage = StudentPayment::where('invoice_id', $invoiceId)
                ->value('message');

            if ($contextMessage === 'ONLINE_INITIATED_BY_ACCOUNTS') {
                $contextFromDb = 'accounts';
            } elseif ($contextMessage === 'ONLINE_INITIATED_BY_STUDENT') {
                $contextFromDb = 'student';
            }
        }

        if (!empty($contextFromDb)) {
            return $contextFromDb;
        }

        return $udf3 === 'accounts' ? 'accounts' : 'student';
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
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();
        $query = StudentPayment::with([
            'studentmaster:id,first_name,last_name,roll_no',
            'feepaymentinfo:id,quarter_title',
            'gatewayType:id,title'
        ]);

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'transaction_date');

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('transaction_date', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        }

        $payments = $query->orderBy('transaction_date', 'desc')->get();




        return view('admin.accounts.all-payments', [
            'payments' => $payments,
            'activeFinancialYear' => $activeFinancialYear,
            'selectedFinancialYear' => $selectedFinancialYear,
            'financialYears' => $financialYears,
        ]);
    }

    function verifyTransaction($txnid)
    {
        $response = StaticController::easebuzz_verifyPaymentWithHash($txnid);
        $data =  $response['msg']['0'];
        return view('admin.accounts.ez-payment-verification', ['data' => $data]);
    }

    // ---- FULL COURSE FEE EXEMPTION MANAGEMENT ----

    public function fullFeeExemptionIndex(Request $request)
    {
        $studentsQuery = StudentMaster::select('id', 'roll_no', 'first_name', 'last_name', 'batch', 'current_year', 'academic_pathway_id', 'degree_track_id')
            ->with(['batchmaster:id,batch_name', 'academicpathway:id,name', 'degreetrack:id,name'])
            ->orderBy('roll_no', 'asc');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('roll_no', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('batch_filter')) {
            $studentsQuery->where('batch', (int) $request->batch_filter);
        }

        $students = $studentsQuery->paginate(50)->appends($request->query());

        $activeExemptions = StudentFullFeeExemption::whereIn('student_id', $students->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('student_id');

        $activeQuarterExemptions = StudentQuarterFeeExemption::with('feeStructure:id,quarter_title,std_current_year')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('is_active', true)
            ->get()
            ->groupBy('student_id');

        $exemptions = StudentFullFeeExemption::with([
            'student:id,roll_no,first_name,last_name,academic_pathway_id,degree_track_id',
            'student.academicpathway:id,name',
            'student.degreetrack:id,name',
            'approver:id,name',
            'revoker:id,name'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(50, ['*'], 'history_page')
            ->appends($request->query());

        $quarterExemptions = StudentQuarterFeeExemption::with([
            'student:id,roll_no,first_name,last_name,academic_pathway_id,degree_track_id',
            'student.academicpathway:id,name',
            'student.degreetrack:id,name',
            'feeStructure:id,quarter_title,std_current_year',
            'approver:id,name',
            'revoker:id,name'
        ])
            ->orderBy('created_at', 'desc')
            ->paginate(50, ['*'], 'quarter_history_page')
            ->appends($request->query());

        $batches = BatchMaster::orderBy('batch_name', 'desc')->get(['id', 'batch_name']);

        return view('admin.accounts.full-fee-exemptions', compact('students', 'activeExemptions', 'activeQuarterExemptions', 'exemptions', 'quarterExemptions', 'batches'));
    }

    public function grantFullFeeExemption(Request $request)
    {
        $request->validate([
            'roll_no' => 'required|exists:student_masters,roll_no',
            'reason' => 'required|string|max:500',
        ]);

        $student = StudentMaster::where('roll_no', trim((string) $request->roll_no))->firstOrFail();

        StudentFullFeeExemption::updateOrCreate(
            ['student_id' => $student->id],
            [
                'reason' => trim((string) $request->reason),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'is_active' => true,
                'revoked_by' => null,
                'revoked_at' => null,
            ]
        );

        return redirect()->back()->with('success', 'Full course fee exemption saved successfully.');
    }

    public function revokeFullFeeExemption($id)
    {
        $exemption = StudentFullFeeExemption::findOrFail($id);
        $exemption->is_active = false;
        $exemption->revoked_by = Auth::id();
        $exemption->revoked_at = now();
        $exemption->save();

        return redirect()->back()->with('success', 'Full course fee exemption revoked successfully.');
    }

    public function grantQuarterFeeExemption(Request $request)
    {
        $request->validate([
            'roll_no' => 'required|exists:student_masters,roll_no',
            'fee_structure_ids' => 'required|array|min:1',
            'fee_structure_ids.*' => 'required|integer|exists:fees_structures,id',
            'reason' => 'required|string|max:500',
        ]);

        $student = StudentMaster::where('roll_no', trim((string) $request->roll_no))->firstOrFail();

        if ($this->getActiveFullFeeExemption((int) $student->id)) {
            return redirect()->back()->with('error', 'Full course fee exemption is active for this student. Quarterly exemptions are unnecessary until full exemption is revoked.');
        }

        $createdCount = 0;
        foreach ((array) $request->fee_structure_ids as $feeStructureId) {
            StudentQuarterFeeExemption::updateOrCreate(
                [
                    'student_id' => (int) $student->id,
                    'fee_structure_id' => (int) $feeStructureId,
                ],
                [
                    'reason' => trim((string) $request->reason),
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'is_active' => true,
                    'revoked_by' => null,
                    'revoked_at' => null,
                ]
            );
            $createdCount++;
        }

        return redirect()->back()->with('success', 'Quarterly fee exemption saved for ' . $createdCount . ' fee structure(s).');
    }

    public function revokeQuarterFeeExemption($id)
    {
        $exemption = StudentQuarterFeeExemption::findOrFail($id);
        $exemption->is_active = false;
        $exemption->revoked_by = Auth::id();
        $exemption->revoked_at = now();
        $exemption->save();

        return redirect()->back()->with('success', 'Quarterly fee exemption revoked successfully.');
    }

    public function fullFeeExemptionHistory(Request $request)
    {
        $query = StudentFullFeeExemption::with([
            'student:id,roll_no,first_name,last_name,batch,academic_pathway_id,degree_track_id',
            'student.batchmaster:id,batch_name',
            'student.academicpathway:id,name',
            'student.degreetrack:id,name',
            'approver:id,name',
            'revoker:id,name',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('roll_no', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('batch_filter')) {
            $batchId = (int) $request->batch_filter;
            $query->whereHas('student', function ($q) use ($batchId) {
                $q->where('batch', $batchId);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'revoked') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('approved_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('approved_at', '<=', $request->to_date);
        }

        $histories = $query->paginate(50)->appends($request->query());
        $batches = BatchMaster::orderBy('batch_name', 'desc')->get(['id', 'batch_name']);

        return view('admin.accounts.full-fee-exemption-history', compact('histories', 'batches'));
    }

    public function quarterFeeExemptionHistory(Request $request)
    {
        $query = StudentQuarterFeeExemption::with([
            'student:id,roll_no,first_name,last_name,batch,academic_pathway_id,degree_track_id',
            'student.batchmaster:id,batch_name',
            'student.academicpathway:id,name',
            'student.degreetrack:id,name',
            'feeStructure:id,quarter_title,std_current_year',
            'approver:id,name',
            'revoker:id,name',
        ])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('roll_no', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('batch_filter')) {
            $batchId = (int) $request->batch_filter;
            $query->whereHas('student', function ($q) use ($batchId) {
                $q->where('batch', $batchId);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'revoked') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('approved_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('approved_at', '<=', $request->to_date);
        }

        if ($request->filled('fee_structure_id')) {
            $query->where('fee_structure_id', (int) $request->fee_structure_id);
        }

        $histories = $query->paginate(50)->appends($request->query());
        $batches = BatchMaster::orderBy('batch_name', 'desc')->get(['id', 'batch_name']);
        $feeStructures = FeesStructure::query()
            ->select('id', 'quarter_title', 'std_current_year')
            ->orderBy('std_current_year')
            ->orderBy('quarter_title')
            ->get();

        return view('admin.accounts.quarter-fee-exemption-history', compact('histories', 'batches', 'feeStructures'));
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

    public function getStudentFullFeeExemptionBreakdown($studentId)
    {
        $student = StudentMaster::with('feepayment')->findOrFail($studentId);

        $lateFeePerDay = LateFee::where('status', 1)->value('late_fee_amount') ?? 0;

        $lateExemptions = StudentLateFeeExemption::where('student_id', $student->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('fee_structure_id');

        $hasBlanketLateExemption = $lateExemptions->contains(function ($exemption) {
            return is_null($exemption->fee_structure_id);
        });

        $quarterFeeExemptions = $this->getActiveQuarterFeeExemptionMap((int) $student->id);

        $applicableFS = FeesStructure::with('feeHeads')
            ->where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->new_program_id);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year');

        $applicableFS = $this->applyFeeStructureApplicabilityFilters($applicableFS, $student)->get();

        $rows = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $lateExemptions, $hasBlanketLateExemption, $quarterFeeExemptions) {
            $successPayment = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->first();

            $baseAmount = $successPayment
                ? (float) ($successPayment->amount ?? 0)
                : (float) $fs->feeHeads->sum('amount');
            $lateDays = 0;
            $lateFee = 0;
            $isPaid = !is_null($successPayment);

            if ($isPaid) {
                $lateFee = (float) ($successPayment->late_fee_amount ?? 0);
                $lateDays = (int) ($successPayment->late_days ?? 0);
            }

            if (!$isPaid && $fs->due_date) {
                $dueDate = Carbon::parse($fs->due_date);
                $today = Carbon::today();

                if ($today->gt($dueDate)) {
                    $lateDays = $dueDate->diffInDays($today);
                    $isLateExempted = $hasBlanketLateExemption || $lateExemptions->has($fs->id);

                    if ($isLateExempted) {
                        $exemption = $hasBlanketLateExemption
                            ? $lateExemptions->first(function ($e) {
                                return is_null($e->fee_structure_id);
                            })
                            : $lateExemptions->get($fs->id);

                        if ($exemption && !is_null($exemption->fixed_late_fee)) {
                            $lateFee = (float) $exemption->fixed_late_fee;
                        }
                    } else {
                        $lateFee = (float) ($lateDays * $lateFeePerDay);
                    }
                }
            }

            $isQuarterFeeExempted = isset($quarterFeeExemptions[(int) $fs->id]);
            $lineTotal = $baseAmount + $lateFee;
            $exemptableAmount = (!$isPaid && !$isQuarterFeeExempted) ? $lineTotal : 0;

            return [
                'fee_structure_id' => (int) $fs->id,
                'fee_structure_name' => (string) ($fs->quarter_title ?? 'N/A'),
                'year' => (int) ($fs->std_current_year ?? 0),
                'base_amount' => $baseAmount,
                'late_days' => (int) $lateDays,
                'late_fee' => (float) $lateFee,
                'total_payable' => $lineTotal,
                'is_paid' => $isPaid,
                'is_payable' => (int) ($fs->is_payable ?? 0),
                'is_quarter_fee_exempted' => $isQuarterFeeExempted,
                'quarter_fee_exemption_reason' => $quarterFeeExemptions[(int) $fs->id]['reason'] ?? null,
                'exemptable_amount' => $exemptableAmount,
            ];
        })
            ->values();

        return response()->json([
            'student' => [
                'id' => (int) $student->id,
                'roll_no' => (string) $student->roll_no,
                'name' => (string) trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
            ],
            'fees' => $rows,
            'summary' => [
                'count' => (int) $rows->count(),
                'total_base_amount' => (float) $rows->sum('base_amount'),
                'total_late_fee' => (float) $rows->sum('late_fee'),
                'total_course_amount' => (float) $rows->sum('total_payable'),
                'total_paid_amount' => (float) $rows->where('is_paid', true)->sum('total_payable'),
                'total_to_be_exempted' => (float) $rows->sum('exemptable_amount'),
            ],
        ]);
    }

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
        $student = StudentMaster::with('feepayment')->findOrFail($studentId);

        if ($this->getActiveFullFeeExemption((int) $student->id)) {
            return response()->json([]);
        }

        $feeStructures = FeesStructure::query()->where('batch_id', $student->batch)
            ->whereHas('programspivot', function ($q) use ($student) {
                $q->where('std_program_id', $student->new_program_id);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year');

        $hasQuarterNo = Schema::hasColumn('fees_structures', 'quarter_no');
        if ($hasQuarterNo) {
            $feeStructures->orderBy('quarter_no');
        } else {
            $feeStructures->orderBy('id');
        }

        $selectColumns = ['id', 'quarter_title', 'std_current_year', 'is_payable'];
        if ($hasQuarterNo) {
            $selectColumns[] = 'quarter_no';
        }

        $feeStructures = $this->applyFeeStructureApplicabilityFilters($feeStructures, $student)
            ->get($selectColumns);

        $rows = $feeStructures->map(function ($fs) use ($student) {
            $isPaid = $student->feepayment
                ->where('fee_structure_id', $fs->id)
                ->where('student_id', $student->id)
                ->where('status', 'success')
                ->isNotEmpty();

            return [
                'id' => (int) $fs->id,
                'quarter_title' => (string) ($fs->quarter_title ?? ''),
                'std_current_year' => (int) ($fs->std_current_year ?? 0),
                'quarter_no' => (int) ($fs->quarter_no ?? 0),
                'is_payable' => (int) ($fs->is_payable ?? 0),
                'is_paid' => $isPaid,
            ];
        })->values();

        return response()->json($rows);
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

        if ($this->getActiveFullFeeExemption((int) $student->id)) {
            return response()->json([]);
        }

        $quarterFeeExemptions = $this->getActiveQuarterFeeExemptionMap((int) $student->id);

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
                $q->where('std_program_id', $student->new_program_id);
            })
            ->whereIn('std_current_year', range(1, $student->current_year))
            ->orderBy('std_current_year');

        $applicableFS = $this->applyFeeStructureApplicabilityFilters($applicableFS, $student)->get();

        // Only show fee structures for which late fee has been paid
        $paidFeeStructureIds = \App\Models\StudentPayment::where('late_fee_amount', '>', 0)
            ->where('status', 'success')
            ->distinct()
            ->pluck('fee_structure_id');
        $feeStructures = \App\Models\FeesStructure::whereIn('id', $paidFeeStructureIds)
            ->orderBy('quarter_title')
            ->get();

        // ---- PREPARE FEE STATUS ----
        $feeStatus = $applicableFS->map(function ($fs) use ($student, $lateFeePerDay, $exemptions, $hasBlanketExemption, $quarterFeeExemptions) {

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
            $isQuarterFeeExempted = isset($quarterFeeExemptions[(int) $fs->id]);

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
                'is_quarter_fee_exempted' => $isQuarterFeeExempted,
                'total_payable'      => ($successPayment || !$isQuarterFeeExempted) ? ($baseAmount + $lateFee) : 0,

                // CORE PAYMENT INFO
                'paid'               => $successPayment ? true : false,
                'paid_amount'        => $successPayment->amount ?? 0,
                'status'             => $successPayment
                    ? 'PAID'
                    : ($isQuarterFeeExempted ? 'EXEMPTED' : ($isExempted && $lateDays > 0 ? 'DUE (Late Fee Exempted)' : ($lateFee > 0 ? 'LATE' : 'DUE'))),

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

                if (($item['is_quarter_fee_exempted'] ?? false) === true) {
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
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();

        // Fetch all batches and fee structures for filters
        $batches = \App\Models\BatchMaster::orderBy('batch_name')->get();
        $feeStructures = \App\Models\FeesStructure::whereIn('id', \App\Models\StudentPayment::where('late_fee_amount', '>', 0)->distinct()->pluck('fee_structure_id'))->orderBy('quarter_title')->get();

        $query = \App\Models\StudentPayment::with([
            'studentmaster.batchmaster',
            'feepaymentinfo.batch',
        ])
            ->where('late_fee_amount', '>', 0)
            ->where('status', 'success');

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'transaction_date');

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
            'activeFinancialYear' => $activeFinancialYear,
            'selectedFinancialYear' => $selectedFinancialYear,
            'financialYears' => $financialYears,
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
                    $q->where('std_program_id', $student->new_program_id);
                })
                ->whereIn('std_current_year', range(1, $student->current_year))
                ->where('is_payable', 1);

            $applicableFS = $this->applyFeeStructureApplicabilityFilters($applicableFS, $student)->get();

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
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();
        $query = AdmissionApplicationPaymentLog::with('applicationmaster.registrationmaster.campusmaster');
        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'created_at');
        $data = $query->latest()->get();

        return view('admin.accounts.admission-fee-collection', [
            'data' => $data,
            'activeFinancialYear' => $activeFinancialYear,
            'selectedFinancialYear' => $selectedFinancialYear,
            'financialYears' => $financialYears,
        ]);
    }

    public function feeHeadWiseReport(Request $request)
    {
        $batches = BatchMaster::orderBy('batch_name')->get();
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();

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

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'sp.transaction_date');

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

        return view('admin.accounts.fee-head-wise-report', compact('report', 'totalCollected', 'batches', 'activeFinancialYear', 'selectedFinancialYear', 'financialYears'));
    }

    public function bankAccountWiseReport(Request $request)
    {
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();
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

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'sp.transaction_date');

        if ($request->filled('from_date')) {
            $query->where('sp.transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('sp.transaction_date', '<=', $request->to_date);
        }

        $report = $query->orderByDesc('total_collected')->get();
        $totalCollected = $report->sum('total_collected');

        return view('admin.accounts.bank-account-wise-report', compact('report', 'totalCollected', 'activeFinancialYear', 'selectedFinancialYear', 'financialYears'));
    }

    public function paymentReportByDate(Request $request)
    {
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();
        $query = StudentPayment::with(['studentmaster', 'feepaymentinfo', 'gatewaytype'])
            ->where('status', 'success');

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'transaction_date');

        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $payments = $query->orderByDesc('transaction_date')->get();
        $totalAmount = $payments->sum('amount');

        return view('admin.accounts.payment-report-by-date', compact('payments', 'totalAmount', 'activeFinancialYear', 'selectedFinancialYear', 'financialYears'));
    }

    public function paymentTypeReport(Request $request)
    {
        $activeFinancialYear = $this->getActiveFinancialYear();
        $selectedFinancialYear = $this->resolveFinancialYearFromRequest($request, $activeFinancialYear);
        $financialYears = $this->getFinancialYearOptions();
        $query = StudentPayment::with(['studentmaster', 'feepaymentinfo', 'gatewaytype'])
            ->where('status', 'success');

        $query = $this->applyFinancialYearFilter($query, $selectedFinancialYear, 'transaction_date');

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

        return view('admin.accounts.payment-type-report', compact('payments', 'cashTotal', 'onlineTotal', 'grandTotal', 'activeFinancialYear', 'selectedFinancialYear', 'financialYears'));
    }

    public function financialYearsIndex()
    {
        $financialYears = FinancialYearMaster::query()
            ->orderByDesc('start_date')
            ->get();

        return view('admin.accounts.financial-years', [
            'financialYears' => $financialYears,
            'activeFinancialYear' => $this->getActiveFinancialYear(),
        ]);
    }

    public function financialYearsStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100|unique:financial_year_masters,title',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|in:1',
        ]);

        DB::transaction(function () use ($validated) {
            $isActive = !empty($validated['is_active']);
            if ($isActive) {
                FinancialYearMaster::query()->update(['is_active' => false]);
            }

            FinancialYearMaster::create([
                'title' => trim((string) $validated['title']),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $isActive,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->back()->with('success', 'Financial year created successfully.');
    }

    public function financialYearsActivate($id)
    {
        DB::transaction(function () use ($id) {
            FinancialYearMaster::query()->update(['is_active' => false]);

            FinancialYearMaster::query()
                ->where('id', (int) $id)
                ->update([
                    'is_active' => true,
                    'updated_by' => Auth::id(),
                ]);
        });

        return redirect()->back()->with('success', 'Active financial year updated successfully.');
    }


    function updateTransactionDate(Request $request)
    {

        $request->validate([
            'transaction_date' => 'required|date',
        ]);

        $id = $request->id;
        $payment = StudentPayment::find($id);
        $payment->transaction_date = $request->transaction_date;
        $payment->save();

        return redirect()->back()->with('success', 'Transaction date updated successfully.');
    }
}

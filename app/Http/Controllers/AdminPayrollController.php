<?php

namespace App\Http\Controllers;

use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLoan;
use App\Models\FacultySalaryMaster;
use App\Models\FacultySalarySlip;
use App\Models\FinancialYearMaster;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPayrollController extends Controller
{
  /**
   * Display list of all salary slips
   */
  public function index(Request $request)
  {
    $month = $request->get('month');
    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    $activeFinancialYearId = $activeFinancialYear?->id;
    $mappedAnnualSessionId = null;
    if ($activeFinancialYear) {
      $mappedAnnualSessionId = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->value('id');
    }

    // Always lock listing to the active financial year mapping.
    $annualSessionId = $mappedAnnualSessionId;
    $financialYearMissing = !$activeFinancialYear;
    $financialYearSessionMismatch = $activeFinancialYear && !$mappedAnnualSessionId;

    $periodsQuery = FacultySalarySlip::query()
      ->leftJoin('annual_sessions', 'faculty_salary_slips.annual_session_id', '=', 'annual_sessions.id')
      ->leftJoin('financial_year_masters', 'faculty_salary_slips.financial_year_id', '=', 'financial_year_masters.id')
      ->select(
        'faculty_salary_slips.month',
        'faculty_salary_slips.year',
        'faculty_salary_slips.annual_session_id',
        'faculty_salary_slips.financial_year_id',
        DB::raw('COALESCE(annual_sessions.title, "N/A") as annual_session_title'),
        DB::raw('COALESCE(financial_year_masters.title, "N/A") as financial_year_title'),
        DB::raw('COUNT(faculty_salary_slips.id) as slips_count'),
        DB::raw('SUM(faculty_salary_slips.net_salary) as total_net_salary')
      );

    if ($month) {
      $periodsQuery->where('faculty_salary_slips.month', $month);
    }

    if ($activeFinancialYearId) {
      $periodsQuery->where(function ($query) use ($activeFinancialYearId, $annualSessionId, $financialYearSessionMismatch, $activeFinancialYear) {
        $query->where('faculty_salary_slips.financial_year_id', $activeFinancialYearId)
          ->orWhere(function ($legacyQuery) use ($annualSessionId, $financialYearSessionMismatch, $activeFinancialYear) {
            $legacyQuery->whereNull('faculty_salary_slips.financial_year_id');

            if ($annualSessionId) {
              $legacyQuery->where('faculty_salary_slips.annual_session_id', $annualSessionId);
            } elseif ($financialYearSessionMismatch) {
              $legacyQuery->whereRaw(
                "STR_TO_DATE(CONCAT(faculty_salary_slips.year, '-', faculty_salary_slips.month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?",
                [
                  $activeFinancialYear->start_date->format('Y-m-d'),
                  $activeFinancialYear->end_date->format('Y-m-d')
                ]
              );
            }
          });
      });
    } elseif ($financialYearMissing) {
      // Prevent showing historical data when active FY is missing.
      $periodsQuery->whereRaw('1 = 0');
    }

    $payrollPeriods = $periodsQuery
      ->groupBy(
        'faculty_salary_slips.year',
        'faculty_salary_slips.month',
        'faculty_salary_slips.annual_session_id',
        'faculty_salary_slips.financial_year_id',
        'financial_year_masters.title',
        'annual_sessions.title'
      )
      ->orderBy('faculty_salary_slips.year', 'desc')
      ->orderBy('faculty_salary_slips.month', 'desc')
      ->get();

    return view('admin.accounts.payroll.index', compact(
      'payrollPeriods',
      'month',
      'annualSessionId',
      'activeFinancialYear',
      'financialYearMissing',
      'financialYearSessionMismatch'
    ));
  }

  /**
   * Display individual payroll slips for a selected month and financial year.
   */
  public function periodPayrolls(Request $request)
  {
    $request->validate([
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
      'annual_session_id' => 'nullable|exists:annual_sessions,id',
    ]);

    $month = $request->get('month');
    $year = $request->get('year');

    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    if (!$activeFinancialYear) {
      return redirect()->route('admin.payroll.index')
        ->with('error', 'No active financial year is configured. Please set an active financial year first.');
    }

    $annualSessionId = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->value('id');
    $activeFinancialYearId = $activeFinancialYear->id;
    $salarySlipsQuery = FacultySalarySlip::with(['faculty', 'annualSession', 'financialYear'])
      ->where('month', $month)
      ->where('year', $year)
      ->orderBy('status')
      ->orderBy('faculty_id');

    $periodSession = null;
    $salarySlipsQuery->where(function ($query) use ($activeFinancialYearId, $annualSessionId, $activeFinancialYear) {
      $query->where('financial_year_id', $activeFinancialYearId)
        ->orWhere(function ($legacyQuery) use ($annualSessionId, $activeFinancialYear) {
          $legacyQuery->whereNull('financial_year_id');

          if ($annualSessionId) {
            $legacyQuery->where('annual_session_id', $annualSessionId);
          } else {
            $legacyQuery->whereRaw(
              "STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?",
              [
                $activeFinancialYear->start_date->format('Y-m-d'),
                $activeFinancialYear->end_date->format('Y-m-d')
              ]
            );
          }
        });
    });

    if ($annualSessionId) {
      $periodSession = AnnualSession::find($annualSessionId);
    }

    $salarySlips = $salarySlipsQuery
      ->paginate(30)
      ->appends($request->query());

    return view('admin.accounts.payroll.period-payrolls', compact(
      'salarySlips',
      'month',
      'year',
      'annualSessionId',
      'periodSession',
      'activeFinancialYear'
    ));
  }

  /**
   * Export printable faculty payroll acceptance sheet for signatures.
   */
  public function exportPeriodAcceptanceSheet(Request $request)
  {
    $request->validate([
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
      'annual_session_id' => 'nullable|exists:annual_sessions,id',
    ]);

    $month = $request->get('month');
    $year = $request->get('year');

    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    if (!$activeFinancialYear) {
      return redirect()->route('admin.payroll.index')
        ->with('error', 'No active financial year is configured. Please set an active financial year first.');
    }

    $annualSessionId = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->value('id');
    $activeFinancialYearId = $activeFinancialYear->id;

    $salarySlipsQuery = FacultySalarySlip::with(['faculty', 'annualSession', 'financialYear'])
      ->where('month', $month)
      ->where('year', $year)
      ->orderBy('faculty_id');

    $salarySlipsQuery->where(function ($query) use ($activeFinancialYearId, $annualSessionId, $activeFinancialYear) {
      $query->where('financial_year_id', $activeFinancialYearId)
        ->orWhere(function ($legacyQuery) use ($annualSessionId, $activeFinancialYear) {
          $legacyQuery->whereNull('financial_year_id');

          if ($annualSessionId) {
            $legacyQuery->where('annual_session_id', $annualSessionId);
          } else {
            $legacyQuery->whereRaw(
              "STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') BETWEEN ? AND ?",
              [
                $activeFinancialYear->start_date->format('Y-m-d'),
                $activeFinancialYear->end_date->format('Y-m-d')
              ]
            );
          }
        });
    });

    $salarySlips = $salarySlipsQuery->get();

    if ($salarySlips->isEmpty()) {
      return back()->with('error', 'No payroll slips found for this period to export.');
    }

    $monthLabel = Carbon::createFromFormat('m', $month)->format('F');
    $title = 'Salary Sheet ' . strtoupper($monthLabel) . ' ' . $year;

    $pdf = Pdf::loadView('admin.accounts.payroll.exports.acceptance-sheet', [
      'salarySlips' => $salarySlips,
      'month' => $month,
      'year' => $year,
      'monthLabel' => $monthLabel,
      'activeFinancialYear' => $activeFinancialYear,
      'title' => $title,
    ])->setPaper('a4', 'landscape');

    return $pdf->download('faculty-payroll-acceptance-sheet-' . strtolower($monthLabel) . '-' . $year . '.pdf');
  }

  /**
   * Show form to create salary slips
   */
  public function create()
  {
    $salaryMasters = FacultySalaryMaster::with(['faculty', 'payMatrix'])
      ->active()
      ->whereHas('faculty', function ($query) {
        $query->where('IS_LEFT', 0);
      })
      ->orderBy('faculty_id')
      ->get();

    $loanDeductionMap = FacultyLoan::where('status', 'active')
      ->select('faculty_id', DB::raw('SUM(emi_amount) as total_emi'))
      ->groupBy('faculty_id')
      ->pluck('total_emi', 'faculty_id');

    $loanSummaryMap = FacultyLoan::where('status', 'active')
      ->select('faculty_id', 'remaining_amount', 'paid_installments', 'total_installments', 'emi_amount')
      ->get()
      ->groupBy('faculty_id')
      ->map(function ($loans) {
        $paidEmis = (int) $loans->sum('paid_installments');
        $pendingEmis = (int) $loans->sum(function ($loan) {
          return max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);
        });

        return [
          'loan_count' => (int) $loans->count(),
          'monthly_emi' => (float) $loans->sum('emi_amount'),
          'pending_amount' => (float) $loans->sum('remaining_amount'),
          'paid_emis' => $paidEmis,
          'pending_emis' => $pendingEmis,
        ];
      });

    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    $activeAnnualSession = null;
    if ($activeFinancialYear) {
      $activeAnnualSession = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->first();
    }
    $financialYearSessionMismatch = $activeFinancialYear && !$activeAnnualSession;

    return view('admin.accounts.payroll.create', compact(
      'salaryMasters',
      'loanDeductionMap',
      'loanSummaryMap',
      'activeAnnualSession',
      'activeFinancialYear',
      'financialYearSessionMismatch'
    ));
  }

  /**
   * Store a new salary slip
   */
  public function store(Request $request)
  {
    $request->validate([
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
      'annual_session_id' => 'nullable|exists:annual_sessions,id',
      'monthly_working_days' => 'required|integer|min:1|max:31',
      'faculty_ids' => 'required|array|min:1',
      'faculty_ids.*' => 'required|integer|exists:faculties,id',
      'individual_faculty_id' => 'nullable|integer|exists:faculties,id',
      'publish_individual' => 'nullable|in:0,1',
      'manual_pf_deductions' => 'nullable|array',
      'manual_pf_deductions.*' => 'nullable|numeric|min:0',
      'manual_pt_deductions' => 'nullable|array',
      'manual_pt_deductions.*' => 'nullable|numeric|min:0',
      'late_attendance_deductions' => 'nullable|array',
      'late_attendance_deductions.*' => 'nullable|numeric|min:0',
      'leave_deductions' => 'nullable|array',
      'leave_deductions.*' => 'nullable|numeric|min:0',
      'skip_loan_emi' => 'nullable|array',
      'skip_loan_emi.*' => 'nullable|integer|exists:faculties,id',
      'emi_deduction_counts' => 'nullable|array',
      'emi_deduction_counts.*' => 'nullable|integer|min:0',
      'manual_deductions' => 'nullable|array',
      'manual_deductions.*' => 'nullable|numeric|min:0',
      'present_days' => 'nullable|array',
      'present_days.*' => 'nullable|integer|min:0',
      'absent_days' => 'nullable|array',
      'absent_days.*' => 'nullable|integer|min:0',
      'remarks' => 'nullable|array',
      'remarks.*' => 'nullable|string|max:500',
    ]);

    $month = $request->month;
    $year = $request->year;
    $monthlyWorkingDays = (int) $request->input('monthly_working_days');
    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    if (!$activeFinancialYear) {
      return back()->with('error', 'No active financial year is configured. Please set an active financial year first.');
    }
    $activeFinancialYearId = $activeFinancialYear->id;

    $activeAnnualSessionId = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->value('id');

    $facultyIds = collect($request->faculty_ids)->unique()->values();
    $individualFacultyId = $request->filled('individual_faculty_id') ? (int) $request->individual_faculty_id : null;
    $publishIndividual = $request->input('publish_individual') === '1';
    if (!is_null($individualFacultyId)) {
      $facultyIds = collect([$individualFacultyId]);
    }
    $manualPfDeductions = $request->input('manual_pf_deductions', []);
    $manualPtDeductions = $request->input('manual_pt_deductions', []);
    $lateAttendanceDeductions = $request->input('late_attendance_deductions', []);
    $leaveDeductions = $request->input('leave_deductions', []);
    $skipLoanEmiFacultyIds = collect($request->input('skip_loan_emi', []))
      ->map(fn($id) => (int) $id)
      ->values()
      ->all();
    $emiDeductionCounts = $request->input('emi_deduction_counts', []);
    $manualDeductions = $request->input('manual_deductions', []);
    $presentDaysByFaculty = $request->input('present_days', []);
    $absentDaysByFaculty = $request->input('absent_days', []);
    $remarksByFaculty = $request->input('remarks', []);
    $isIndividualPublish = $publishIndividual && !is_null($individualFacultyId);

    $salaryMasters = FacultySalaryMaster::with('faculty')
      ->active()
      ->whereIn('faculty_id', $facultyIds)
      ->get()
      ->keyBy('faculty_id');

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $individualExistingFinalizedSlipId = null;

    DB::transaction(function () use (
      $facultyIds,
      $salaryMasters,
      $manualPfDeductions,
      $manualPtDeductions,
      $lateAttendanceDeductions,
      $leaveDeductions,
      $skipLoanEmiFacultyIds,
      $emiDeductionCounts,
      $manualDeductions,
      $presentDaysByFaculty,
      $absentDaysByFaculty,
      $remarksByFaculty,
      $monthlyWorkingDays,
      $request,
      $month,
      $year,
      $activeFinancialYearId,
      $activeAnnualSessionId,
      $individualFacultyId,
      $publishIndividual,
      $isIndividualPublish,
      &$created,
      &$updated,
      &$skipped,
      &$individualExistingFinalizedSlipId
    ) {
      foreach ($facultyIds as $facultyId) {
        $salaryMaster = $salaryMasters->get($facultyId);

        if (!$salaryMaster) {
          $skipped++;
          continue;
        }

        $existingSlip = FacultySalarySlip::withTrashed()->where('faculty_id', $facultyId)
          ->where('year', $year)
          ->where('month', $month)
          ->where(function ($query) use ($activeFinancialYearId, $activeAnnualSessionId) {
            $query->where('financial_year_id', $activeFinancialYearId)
              ->orWhere(function ($legacyQuery) use ($activeAnnualSessionId) {
                $legacyQuery->whereNull('financial_year_id');

                if (!is_null($activeAnnualSessionId)) {
                  $legacyQuery->where(function ($sessionQuery) use ($activeAnnualSessionId) {
                    $sessionQuery->where('annual_session_id', $activeAnnualSessionId)
                      ->orWhereNull('annual_session_id');
                  });
                }
              });
          })
          ->orderByRaw('CASE WHEN financial_year_id = ? THEN 0 ELSE 1 END', [$activeFinancialYearId])
          ->first();

        if ($existingSlip && $existingSlip->trashed()) {
          $existingSlip->restore();
        }

        if ($existingSlip && is_null($existingSlip->financial_year_id)) {
          $existingSlip->financial_year_id = $activeFinancialYearId;
          if (is_null($existingSlip->annual_session_id) && !is_null($activeAnnualSessionId)) {
            $existingSlip->annual_session_id = $activeAnnualSessionId;
          }
          $existingSlip->save();
        }

        if ($existingSlip && $existingSlip->status === 'paid') {
          if (!is_null($individualFacultyId) && (int) $individualFacultyId === (int) $facultyId) {
            $individualExistingFinalizedSlipId = $existingSlip->id;
          }
          $skipped++;
          continue;
        }

        $activeLoans = FacultyLoan::where('faculty_id', $facultyId)
          ->active()
          ->get();

        $loanDeduction = (float) $activeLoans->sum('emi_amount');
        $manualPfDeduction = (float) ($manualPfDeductions[$facultyId] ?? 0);
        $manualPtDeduction = (float) ($manualPtDeductions[$facultyId] ?? 0);
        $lateAttendanceDeduction = (float) ($lateAttendanceDeductions[$facultyId] ?? 0);
        $leaveDeduction = (float) ($leaveDeductions[$facultyId] ?? 0);
        $pendingEmiCount = (int) $activeLoans->sum(function ($loan) {
          return max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);
        });
        $requestedEmiCount = isset($emiDeductionCounts[$facultyId]) ? (int) $emiDeductionCounts[$facultyId] : ($pendingEmiCount > 0 ? 1 : 0);
        $skipLoanEmi = in_array((int) $facultyId, $skipLoanEmiFacultyIds, true);
        if ($skipLoanEmi) {
          $requestedEmiCount = 0;
        }
        $requestedEmiCount = max(0, min($requestedEmiCount, $pendingEmiCount));
        $otherManualDeduction = (float) ($manualDeductions[$facultyId] ?? 0);

        $computedLoanDeduction = 0.0;
        if ($requestedEmiCount > 0 && $loanDeduction > 0) {
          $simulatedLoans = $activeLoans->map(function ($loan) {
            return [
              'emi' => (float) $loan->emi_amount,
              'pending' => max(((int) $loan->total_installments - (int) $loan->paid_installments), 0),
            ];
          })->values()->all();

          for ($cycle = 0; $cycle < $requestedEmiCount; $cycle++) {
            foreach ($simulatedLoans as &$simulatedLoan) {
              if ($simulatedLoan['pending'] <= 0) {
                continue;
              }
              $computedLoanDeduction += $simulatedLoan['emi'];
              $simulatedLoan['pending']--;
            }
            unset($simulatedLoan);
          }
        }

        $salarySlip = $existingSlip ?: new FacultySalarySlip();
        $salarySlip->faculty_id = $facultyId;
        $salarySlip->financial_year_id = $activeFinancialYearId;
        $salarySlip->annual_session_id = $activeAnnualSessionId;
        $salarySlip->month = $month;
        $salarySlip->year = $year;
        if (!$existingSlip) {
          $salarySlip->salary_slip_number = 'SAL-' . $year . $month . '-FY' . $activeFinancialYearId . '-' . str_pad($facultyId, 4, '0', STR_PAD_LEFT);
        }

        $salarySlip->basic_salary = $salaryMaster->basic_salary;
        $salarySlip->da = $salaryMaster->da;
        $salarySlip->hra = $salaryMaster->hra;
        $salarySlip->ta = $salaryMaster->ta;
        $salarySlip->medical_allowance = $salaryMaster->medical_allowance;
        $salarySlip->special_allowance = $salaryMaster->special_allowance;
        $salarySlip->other_allowances = $salaryMaster->other_allowances;

        $salarySlip->pf = (float) ($salaryMaster->pf ?? 0) + $manualPfDeduction;
        $salarySlip->esi = (float) ($salaryMaster->esi ?? 0);
        $salarySlip->professional_tax = (float) ($salaryMaster->professional_tax ?? 0) + $manualPtDeduction;
        $salarySlip->tds = (float) ($salaryMaster->tds ?? 0);
        $salarySlip->loan_deduction = $computedLoanDeduction;
        $salarySlip->late_attendance_deduction = $lateAttendanceDeduction;
        $salarySlip->leave_deduction_amount = $leaveDeduction;
        $salarySlip->manual_other_deduction = $otherManualDeduction;
        $salarySlip->emi_deduction_count = $requestedEmiCount;
        $salarySlip->other_deductions = (float) ($salaryMaster->other_deductions ?? 0)
          + $otherManualDeduction;

        $absentDays = max((int) ($absentDaysByFaculty[$facultyId] ?? 0), 0);
        $absentDays = min($absentDays, $monthlyWorkingDays);
        $presentDays = max($monthlyWorkingDays - $absentDays, 0);

        $salarySlip->working_days = $monthlyWorkingDays;
        $salarySlip->present_days = $presentDays;
        $salarySlip->leave_days = $absentDays;
        $salarySlip->remarks = $remarksByFaculty[$facultyId] ?? null;
        $salarySlip->status = ($existingSlip && $existingSlip->status === 'approved') || $isIndividualPublish ? 'approved' : 'draft';
        if ($salarySlip->status === 'approved') {
          $salarySlip->approved_by = $existingSlip?->approved_by ?: Auth::id();
          $salarySlip->approved_at = $existingSlip?->approved_at ?: now();
        }

        $salarySlip->calculateTotals();

        if (!$existingSlip && $requestedEmiCount > 0) {
          for ($cycle = 0; $cycle < $requestedEmiCount; $cycle++) {
            foreach ($activeLoans as $loan) {
              if ($loan->status !== 'active') {
                continue;
              }
              $loan->deductEMI();
            }
          }
        }

        if ($existingSlip) {
          $updated++;
        } else {
          $created++;
        }
      }
    });

    $message = is_null($individualFacultyId)
      ? "Monthly payroll processed. Created: {$created}, Updated existing: {$updated}, Skipped: {$skipped}."
      : (($publishIndividual
        ? "Individual monthly payroll created and published. Created: {$created}, Updated existing: {$updated}, Skipped: {$skipped}."
        : "Individual monthly payroll processed. Created: {$created}, Updated existing: {$updated}, Skipped: {$skipped}."));

    if (!is_null($individualExistingFinalizedSlipId) && $created === 0 && $updated === 0) {
      return redirect()->route('admin.payroll.show', $individualExistingFinalizedSlipId)
        ->with('success', 'Salary slip already finalized for this month. Opened the existing slip for review.')
        ->with('payroll_notice', 'existing_finalized_slip_opened');
    }

    return redirect()->route('admin.payroll.index')
      ->with('success', $message);
  }

  /**
   * Show salary slip details
   */
  public function show($id)
  {
    $salarySlip = FacultySalarySlip::with(['faculty', 'annualSession', 'approver'])->findOrFail($id);
    return view('admin.accounts.payroll.show', compact('salarySlip'));
  }

  /**
   * Show form to edit salary slip
   */
  public function edit($id)
  {
    $salarySlip = FacultySalarySlip::with('faculty')->findOrFail($id);

    if ($salarySlip->status === 'paid') {
      return back()->with('error', 'Cannot edit a paid salary slip.');
    }

    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();
    $sessions = AnnualSession::orderBy('id', 'desc')->get();

    return view('admin.accounts.payroll.edit', compact('salarySlip', 'faculties', 'sessions'));
  }

  /**
   * Update salary slip
   */
  public function update(Request $request, $id)
  {
    $salarySlip = FacultySalarySlip::findOrFail($id);

    if ($salarySlip->status === 'paid') {
      return back()->with('error', 'Cannot update a paid salary slip.');
    }

    $request->validate([
      'manual_pf_deduction' => 'nullable|numeric|min:0',
      'manual_pt_deduction' => 'nullable|numeric|min:0',
      'late_attendance_deduction' => 'nullable|numeric|min:0',
      'leave_deduction' => 'nullable|numeric|min:0',
      'manual_other_deduction' => 'nullable|numeric|min:0',
      'present_days' => 'nullable|integer|min:0',
      'absent_days' => 'nullable|integer|min:0',
      'emi_deduction_count' => 'nullable|integer|min:0',
      'skip_loan_emi' => 'nullable|in:0,1',
      'remarks' => 'nullable|string|max:500',
    ]);

    $salaryMaster = FacultySalaryMaster::where('faculty_id', $salarySlip->faculty_id)
      ->active()
      ->first();

    $baseBasic = (float) ($salaryMaster->basic_salary ?? $salarySlip->basic_salary);
    $baseDa = (float) ($salaryMaster->da ?? $salarySlip->da);
    $baseHra = (float) ($salaryMaster->hra ?? $salarySlip->hra);
    $baseTa = (float) ($salaryMaster->ta ?? $salarySlip->ta);
    $baseMedical = (float) ($salaryMaster->medical_allowance ?? $salarySlip->medical_allowance);
    $baseSpecial = (float) ($salaryMaster->special_allowance ?? $salarySlip->special_allowance);
    $baseOtherAllowances = (float) ($salaryMaster->other_allowances ?? $salarySlip->other_allowances);

    $basePf = (float) ($salaryMaster->pf ?? 0);
    $basePt = (float) ($salaryMaster->professional_tax ?? 0);
    $baseEsi = (float) ($salaryMaster->esi ?? $salarySlip->esi);
    $baseTds = (float) ($salaryMaster->tds ?? $salarySlip->tds);
    $baseOtherDeduction = (float) ($salaryMaster->other_deductions ?? 0);

    $manualPfDeduction = (float) ($request->manual_pf_deduction ?? 0);
    $manualPtDeduction = (float) ($request->manual_pt_deduction ?? 0);
    $lateAttendanceDeduction = (float) ($request->late_attendance_deduction ?? 0);
    $leaveDeduction = (float) ($request->leave_deduction ?? 0);
    $manualOtherDeduction = (float) ($request->manual_other_deduction ?? 0);

    $loanPool = FacultyLoan::where('faculty_id', $salarySlip->faculty_id)
      ->whereIn('status', ['active', 'completed'])
      ->orderBy('id')
      ->get();
    $activeLoans = $loanPool->where('status', 'active')->values();
    $loanDeductionPerCycle = (float) $activeLoans->sum('emi_amount');
    $pendingEmiCount = (int) $loanPool->sum(function ($loan) {
      return max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);
    });
    $existingEmiCount = max((int) ($salarySlip->emi_deduction_count ?? 0), 0);
    $editableEmiCapacity = $pendingEmiCount + $existingEmiCount;
    $requestedEmiCount = (int) ($request->emi_deduction_count ?? 0);
    if ($request->input('skip_loan_emi') === '1') {
      $requestedEmiCount = 0;
    }
    $requestedEmiCount = max(0, min($requestedEmiCount, $editableEmiCapacity));

    $computedLoanDeduction = max((float) ($salarySlip->loan_deduction ?? 0), 0);
    $emiCountDelta = $requestedEmiCount - $existingEmiCount;

    $presentDays = max((int) ($request->present_days ?? 0), 0);
    $absentDays = max((int) ($request->absent_days ?? 0), 0);

    $salarySlip->basic_salary = $baseBasic;
    $salarySlip->da = $baseDa;
    $salarySlip->hra = $baseHra;
    $salarySlip->ta = $baseTa;
    $salarySlip->medical_allowance = $baseMedical;
    $salarySlip->special_allowance = $baseSpecial;
    $salarySlip->other_allowances = $baseOtherAllowances;

    $salarySlip->pf = $basePf + $manualPfDeduction;
    $salarySlip->esi = $baseEsi;
    $salarySlip->professional_tax = $basePt + $manualPtDeduction;
    $salarySlip->tds = $baseTds;
    $salarySlip->loan_deduction = $computedLoanDeduction;
    $salarySlip->late_attendance_deduction = $lateAttendanceDeduction;
    $salarySlip->leave_deduction_amount = $leaveDeduction;
    $salarySlip->manual_other_deduction = $manualOtherDeduction;
    $salarySlip->emi_deduction_count = $requestedEmiCount;
    $salarySlip->other_deductions = $baseOtherDeduction + $manualOtherDeduction;

    $salarySlip->working_days = $presentDays + $absentDays;
    $salarySlip->present_days = $presentDays;
    $salarySlip->leave_days = $absentDays;
    $salarySlip->remarks = $request->remarks;

    DB::transaction(function () use ($loanPool, $emiCountDelta, &$computedLoanDeduction, $salarySlip) {
      if ($emiCountDelta > 0) {
        // Apply extra deduction cycles when EMI count is increased during edit.
        for ($cycle = 0; $cycle < $emiCountDelta; $cycle++) {
          foreach ($loanPool as $loan) {
            if ($loan->status !== 'active') {
              continue;
            }
            if ($loan->deductEMI()) {
              $computedLoanDeduction += (float) $loan->emi_amount;
            }
          }
        }
      } elseif ($emiCountDelta < 0) {
        // Roll back deduction cycles when EMI count is reduced during edit.
        $cyclesToReverse = abs($emiCountDelta);
        for ($cycle = 0; $cycle < $cyclesToReverse; $cycle++) {
          $reversedThisCycle = false;

          foreach ($loanPool as $loan) {
            if ($loan->status !== 'active' || (int) $loan->paid_installments <= 0) {
              continue;
            }
            if ($loan->reverseEMI()) {
              $computedLoanDeduction -= (float) $loan->emi_amount;
              $reversedThisCycle = true;
            }
          }

          if (!$reversedThisCycle) {
            foreach ($loanPool as $loan) {
              if ($loan->status !== 'completed' || (int) $loan->paid_installments <= 0) {
                continue;
              }
              if ($loan->reverseEMI()) {
                $computedLoanDeduction -= (float) $loan->emi_amount;
                $reversedThisCycle = true;
              }
            }
          }

          if (!$reversedThisCycle) {
            break;
          }
        }
      }

      $salarySlip->loan_deduction = max($computedLoanDeduction, 0);
      $salarySlip->calculateTotals();
      $salarySlip->save();
    });

    return redirect()->route('admin.payroll.show', $id)
      ->with('success', 'Salary slip updated successfully.');
  }

  /**
   * Delete salary slip
   */
  public function destroy($id)
  {
    $salarySlip = FacultySalarySlip::findOrFail($id);

    if ($salarySlip->status === 'paid') {
      return back()->with('error', 'Cannot delete a paid salary slip.');
    }

    $salarySlip->delete();

    return redirect()->route('admin.payroll.index')
      ->with('success', 'Salary slip deleted successfully.');
  }

  /**
   * Approve salary slip
   */
  public function approve($id)
  {
    $salarySlip = FacultySalarySlip::findOrFail($id);

    if ($salarySlip->status !== 'draft') {
      return back()->with('error', 'Only draft salary slips can be approved.');
    }

    $salarySlip->status = 'approved';
    $salarySlip->approved_by = Auth::id();
    $salarySlip->approved_at = now();
    $salarySlip->save();

    return back()->with('success', 'Salary slip approved successfully.');
  }

  /**
   * Mark salary slip as paid
   */
  public function markAsPaid(Request $request, $id)
  {
    $request->validate([
      'payment_date' => 'required|date',
      'payment_mode' => 'required|in:bank_transfer,cash,cheque',
      'payment_reference' => 'nullable|string',
    ]);

    $salarySlip = FacultySalarySlip::findOrFail($id);

    if ($salarySlip->status === 'paid') {
      return back()->with('error', 'Salary slip is already marked as paid.');
    }

    $salarySlip->status = 'paid';
    $salarySlip->payment_date = $request->payment_date;
    $salarySlip->payment_mode = $request->payment_mode;
    $salarySlip->payment_reference = $request->payment_reference;

    if (!$salarySlip->approved_at) {
      $salarySlip->approved_by = Auth::id();
      $salarySlip->approved_at = now();
    }

    $salarySlip->save();

    return back()->with('success', 'Salary slip marked as paid successfully.');
  }

  /**
   * Bulk mark selected salary slips as approved and paid.
   */
  public function bulkApproveAndMarkPaid(Request $request)
  {
    $request->validate([
      'slip_ids' => 'required|array|min:1',
      'slip_ids.*' => 'integer|exists:faculty_salary_slips,id',
      'payment_date' => 'required|date',
      'payment_mode' => 'required|in:bank_transfer,cash,cheque',
      'payment_reference' => 'nullable|string|max:255',
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
    ]);

    $selectedIds = collect($request->input('slip_ids', []))->map(fn($id) => (int) $id)->unique()->values();
    if ($selectedIds->isEmpty()) {
      return back()->with('error', 'Please select at least one salary slip.');
    }

    $paymentDate = $request->input('payment_date');
    $paymentMode = $request->input('payment_mode');
    $paymentReference = $request->input('payment_reference');
    $month = $request->input('month');
    $year = $request->input('year');

    $slips = FacultySalarySlip::whereIn('id', $selectedIds)
      ->where('month', $month)
      ->where('year', $year)
      ->get();

    $updated = 0;
    $alreadyPaid = 0;

    DB::transaction(function () use ($slips, $paymentDate, $paymentMode, $paymentReference, &$updated, &$alreadyPaid) {
      foreach ($slips as $slip) {
        if ($slip->status === 'paid') {
          $alreadyPaid++;
          continue;
        }

        if (!$slip->approved_at) {
          $slip->approved_by = Auth::id();
          $slip->approved_at = now();
        }

        $slip->status = 'paid';
        $slip->payment_date = $paymentDate;
        $slip->payment_mode = $paymentMode;
        $slip->payment_reference = $paymentReference;
        $slip->save();
        $updated++;
      }
    });

    if ($updated === 0 && $alreadyPaid > 0) {
      return back()->with('error', 'All selected salary slips are already marked as paid.');
    }

    $message = 'Selected salary slips marked as approved and paid: ' . $updated . '.';
    if ($alreadyPaid > 0) {
      $message .= ' Already paid skipped: ' . $alreadyPaid . '.';
    }

    return back()->with('success', $message);
  }

  /**
   * Bulk generate salary slips for all faculty
   */
  public function bulkGenerate(Request $request)
  {
    $request->validate([
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
    ]);

    $month = $request->month;
    $year = $request->year;
    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    if (!$activeFinancialYear) {
      return back()->with('error', 'No active financial year is configured. Please set an active financial year first.');
    }
    $activeFinancialYearId = $activeFinancialYear->id;
    $activeAnnualSessionId = AnnualSession::where('title', $activeFinancialYear->title)->orderBy('id', 'desc')->value('id');

    // Get all faculties with active salary masters
    $salaryMasters = FacultySalaryMaster::with('faculty')
      ->active()
      ->whereHas('faculty', function ($q) {
        $q->where('IS_LEFT', 0);
      })
      ->get();

    $created = 0;
    $skipped = 0;
    $autoApproved = 0;

    foreach ($salaryMasters as $salaryMaster) {
      // Check if slip already exists
      $exists = FacultySalarySlip::withTrashed()->where('faculty_id', $salaryMaster->faculty_id)
        ->where('year', $year)
        ->where('month', $month)
        ->where('financial_year_id', $activeFinancialYearId)
        ->exists();

      if ($exists) {
        $skipped++;
        continue;
      }

      // Get all active loans and auto-select them for EMI deduction
      $activeLoans = FacultyLoan::where('faculty_id', $salaryMaster->faculty_id)
        ->active()
        ->get();

      $totalLoanDeduction = $activeLoans->sum('emi_amount');

      // Generate salary slip number
      $slipNumber = 'SAL-' . $year . $month . '-FY' . $activeFinancialYearId . '-' . str_pad($salaryMaster->faculty_id, 4, '0', STR_PAD_LEFT);

      // Create salary slip from salary master
      $salarySlip = new FacultySalarySlip();
      $salarySlip->faculty_id = $salaryMaster->faculty_id;
      $salarySlip->financial_year_id = $activeFinancialYearId;
      $salarySlip->annual_session_id = $activeAnnualSessionId;
      $salarySlip->month = $month;
      $salarySlip->year = $year;
      $salarySlip->salary_slip_number = $slipNumber;

      // Copy from salary master
      $salarySlip->basic_salary = $salaryMaster->basic_salary;
      $salarySlip->da = $salaryMaster->da;
      $salarySlip->hra = $salaryMaster->hra;
      $salarySlip->ta = $salaryMaster->ta;
      $salarySlip->medical_allowance = $salaryMaster->medical_allowance;
      $salarySlip->special_allowance = $salaryMaster->special_allowance;
      $salarySlip->other_allowances = $salaryMaster->other_allowances;

      $salarySlip->pf = (float) ($salaryMaster->pf ?? 0);
      $salarySlip->esi = (float) ($salaryMaster->esi ?? 0);
      $salarySlip->professional_tax = (float) ($salaryMaster->professional_tax ?? 0);
      $salarySlip->tds = (float) ($salaryMaster->tds ?? 0);
      $salarySlip->loan_deduction = $totalLoanDeduction;
      $salarySlip->other_deductions = (float) ($salaryMaster->other_deductions ?? 0);

      $salarySlip->working_days = $salaryMaster->working_days;
      $salarySlip->present_days = $salaryMaster->working_days;
      $salarySlip->leave_days = 0;

      // Auto-approve if requested, otherwise set as draft
      if ($request->has('auto_approve') && $request->auto_approve == '1') {
        $salarySlip->status = 'approved';
        $salarySlip->approved_by = auth()->id();
        $salarySlip->approved_at = now();
        $autoApproved++;
      } else {
        $salarySlip->status = 'draft';
      }

      $salarySlip->calculateTotals();
      $salarySlip->save();

      // Deduct EMI from all active loans
      foreach ($activeLoans as $loan) {
        $loan->deductEMI();
      }

      $created++;
    }

    $message = "Bulk generation completed. Created: {$created}, Skipped: {$skipped}";
    if ($autoApproved > 0) {
      $message .= ", Auto-approved: {$autoApproved}";
    }

    return back()->with('success', $message);
  }

  /**
   * Faculty Loans Management
   */
  public function loans(Request $request)
  {
    $facultyId = $request->get('faculty_id');
    $loanType = $request->get('loan_type');
    $loanNumber = trim((string) $request->get('loan_number', ''));
    $loanTypeOptions = $this->loanTypeOptions();
    $paymentModeOptions = $this->paymentModeOptions();

    $query = FacultyLoan::with([
      'faculty',
      'transactions' => function ($transactionQuery) {
        $transactionQuery->orderByDesc('payment_date')->orderByDesc('id');
      }
    ])->orderBy('created_at', 'desc');

    if ($facultyId) {
      $query->where('faculty_id', $facultyId);
    }

    // Keep this page focused on active/ongoing loans only.
    $query->where('status', 'active');

    if ($loanType) {
      $query->where('loan_type', $loanType);
    }

    if ($loanNumber !== '') {
      $query->where('loan_number', 'like', '%' . $loanNumber . '%');
    }

    $loans = $query->paginate(20);
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();
    $salaryMasters = FacultySalaryMaster::active()
      ->whereIn('faculty_id', $faculties->pluck('id'))
      ->get()
      ->keyBy('faculty_id');

    $fullSalaryMap = $salaryMasters->map(function ($salaryMaster) {
      return (float) $salaryMaster->basic_salary
        + (float) $salaryMaster->da
        + (float) $salaryMaster->hra
        + (float) $salaryMaster->ta
        + (float) $salaryMaster->medical_allowance
        + (float) $salaryMaster->special_allowance
        + (float) $salaryMaster->other_allowances;
    });

    $stats = [
      'active_loans' => FacultyLoan::active()->count(),
      'total_disbursed' => FacultyLoan::sum('loan_amount'),
      'total_recovered' => FacultyLoan::sum('total_paid'),
      'pending_recovery' => FacultyLoan::active()->sum('remaining_amount'),
    ];

    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();

    return view('admin.accounts.payroll.loans', compact('loans', 'faculties', 'stats', 'facultyId', 'loanType', 'loanNumber', 'loanTypeOptions', 'paymentModeOptions', 'fullSalaryMap', 'activeFinancialYear'));
  }

  /**
   * Cleared/completed loans listing page.
   */
  public function clearedLoans(Request $request)
  {
    $facultyId = $request->get('faculty_id');
    $loanType = $request->get('loan_type');
    $loanNumber = trim((string) $request->get('loan_number', ''));
    $loanTypeOptions = $this->loanTypeOptions();

    $query = FacultyLoan::with([
      'faculty',
      'transactions' => function ($transactionQuery) {
        $transactionQuery->orderByDesc('payment_date')->orderByDesc('id');
      }
    ])->where('status', 'completed')->orderByDesc('end_date')->orderByDesc('id');

    if ($facultyId) {
      $query->where('faculty_id', $facultyId);
    }

    if ($loanType) {
      $query->where('loan_type', $loanType);
    }

    if ($loanNumber !== '') {
      $query->where('loan_number', 'like', '%' . $loanNumber . '%');
    }

    $loans = $query->paginate(20);
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();

    $stats = [
      'cleared_count' => FacultyLoan::where('status', 'completed')->count(),
      'total_disbursed' => FacultyLoan::where('status', 'completed')->sum('loan_amount'),
      'total_recovered' => FacultyLoan::where('status', 'completed')->sum('total_paid'),
    ];

    return view('admin.accounts.payroll.cleared-loans', compact('loans', 'faculties', 'stats', 'facultyId', 'loanType', 'loanNumber', 'loanTypeOptions'));
  }

  /**
   * Store new loan
   */
  public function storeLoan(Request $request)
  {
    $loanTypeOptions = $this->loanTypeOptions();

    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'loan_type' => ['required', Rule::in(array_keys($loanTypeOptions))],
      'loan_amount' => 'required|numeric|min:1',
      'emi_amount' => 'required|numeric|min:1|lte:loan_amount',
      'total_installments' => 'nullable|integer|min:1',
      'start_date' => 'required|date',
    ]);

    $activeFinancialYear = FinancialYearMaster::where('is_active', true)->orderBy('id', 'desc')->first();
    if (!$activeFinancialYear) {
      return back()->with('error', 'No active financial year is configured. Please set an active financial year first.')->withInput();
    }

    $loanAmount = round((float) $request->loan_amount, 2);
    $emiAmount = round((float) $request->emi_amount, 2);
    $totalInstallments = (int) ceil($loanAmount / max($emiAmount, 1));
    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $fyStart = $activeFinancialYear->start_date->copy()->startOfDay();
    $fyEnd = $activeFinancialYear->end_date->copy()->endOfDay();

    if ($startDate->lt($fyStart) || $startDate->gt($fyEnd)) {
      return back()->with('error', 'Loan start date must be within the active financial year (' . $activeFinancialYear->title . ').')->withInput();
    }

    // Installments are deducted monthly; installment count includes the starting month.
    $projectedEndDate = $startDate->copy()->addMonths(max($totalInstallments - 1, 0));
    if ($projectedEndDate->gt($fyEnd)) {
      return back()->with('error', 'This loan cannot be fully repaid within the active financial year (' . $activeFinancialYear->title . '). Please increase EMI amount or reduce loan amount.')->withInput();
    }

    $loanNumber = 'LOAN-' . date('Ymd') . '-' . str_pad($request->faculty_id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

    $loan = new FacultyLoan();
    $loan->faculty_id = $request->faculty_id;
    $loan->loan_number = $loanNumber;
    $loan->loan_type = (string) $request->loan_type;
    $loan->advance_months = 1;
    $loan->loan_amount = $loanAmount;
    $loan->emi_amount = $emiAmount;
    $loan->total_installments = $totalInstallments;
    $loan->remaining_amount = $loanAmount;
    $loan->start_date = $startDate->toDateString();
    $loan->end_date = $projectedEndDate->toDateString();
    $loan->status = 'active';
    $loan->remarks = $request->remarks;
    $loan->approved_by = Auth::id();
    $loan->approved_at = now();
    $loan->save();

    return back()->with('success', 'Loan created successfully.');
  }

  /**
   * Supported loan types for payroll loans.
   */
  private function loanTypeOptions(): array
  {
    return [
      'advance' => 'Salary Advance',
      'personal' => 'Personal Loan',
      'medical' => 'Medical Loan',
      'vehicle' => 'Vehicle Loan',
      'housing' => 'Housing Loan',
      'education' => 'Education Loan',
      'emergency' => 'Emergency Loan',
    ];
  }

  /**
   * Supported payment modes for manual loan transactions.
   */
  private function paymentModeOptions(): array
  {
    return [
      'cash' => 'Cash',
      'bank_transfer' => 'Bank Transfer',
      'cheque' => 'Cheque',
      'upi' => 'UPI',
      'online' => 'Online',
      'other' => 'Other',
    ];
  }

  /**
   * Update loan status
   */
  public function updateLoanStatus(Request $request, $id)
  {
    $loan = FacultyLoan::findOrFail($id);

    $request->validate([
      'status' => 'required|in:active,completed,suspended',
    ]);

    $loan->status = $request->status;

    if ($request->status === 'completed' && !$loan->end_date) {
      $loan->end_date = now();
    }

    $loan->save();

    return back()->with('success', 'Loan status updated successfully.');
  }

  /**
   * Manually clear a loan mid-term with one-time settlement.
   */
  public function clearLoanManually(Request $request, $id)
  {
    $loan = FacultyLoan::findOrFail($id);
    $paymentModeOptions = $this->paymentModeOptions();

    $request->validate([
      'payment_date' => 'required|date',
      'payment_mode' => ['required', Rule::in(array_keys($paymentModeOptions))],
      'clear_remarks' => 'nullable|string|max:500',
    ]);

    if ($loan->status === 'completed' || (float) $loan->remaining_amount <= 0) {
      return back()->with('error', 'Loan is already completed.');
    }

    $paymentDate = Carbon::parse($request->input('payment_date'))->startOfDay();
    $paymentMode = (string) $request->input('payment_mode');
    $receiptNumber = $this->generateLoanTransactionReceiptNumber();
    $settlementAmount = max((float) $loan->remaining_amount, 0);

    DB::transaction(function () use ($loan, $request, $paymentDate, $paymentMode, $receiptNumber, $settlementAmount) {
      $loan->total_paid = (float) $loan->total_paid + $settlementAmount;
      $loan->remaining_amount = 0;
      $loan->paid_installments = (int) $loan->total_installments;
      $loan->status = 'completed';
      $loan->end_date = now();

      $remark = trim((string) $request->input('clear_remarks', ''));
      $auditNote = ' [Manual closure on ' . now()->format('d-M-Y H:i') . ', payment date: ' . $paymentDate->format('d-M-Y') . ', mode: ' . str_replace('_', ' ', $paymentMode) . ($receiptNumber !== '' ? ', receipt: ' . $receiptNumber : '') . ', settlement: ₹' . number_format($settlementAmount, 2) . ']';
      $loan->remarks = trim(((string) $loan->remarks) . ($remark !== '' ? ' ' . $remark : '') . $auditNote);
      $loan->save();

      DB::table('faculty_loan_transactions')->insert([
        'faculty_loan_id' => $loan->id,
        'faculty_id' => $loan->faculty_id,
        'transaction_type' => 'manual_clear',
        'payment_date' => $paymentDate->toDateString(),
        'payment_mode' => $paymentMode,
        'receipt_number' => $receiptNumber !== '' ? $receiptNumber : null,
        'emi_count' => null,
        'amount' => $settlementAmount,
        'remarks' => $remark,
        'processed_by' => Auth::id(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    });

    return back()->with('success', 'Loan manually cleared successfully. Receipt No: ' . $receiptNumber . '.');
  }

  /**
   * Manually repay one or more EMI cycles for an active loan.
   */
  public function repayLoanEmi(Request $request, $id)
  {
    $loan = FacultyLoan::findOrFail($id);
    $paymentModeOptions = $this->paymentModeOptions();

    $pendingEmis = max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);

    $request->validate([
      'emi_count' => 'required|integer|min:1|max:' . max($pendingEmis, 1),
      'payment_date' => 'required|date',
      'payment_mode' => ['required', Rule::in(array_keys($paymentModeOptions))],
      'repay_remarks' => 'nullable|string|max:500',
    ]);

    if ($loan->status !== 'active' || (float) $loan->remaining_amount <= 0 || $pendingEmis <= 0) {
      return back()->with('error', 'EMI repayment is allowed only for active loans with pending installments.');
    }

    $emiCount = (int) $request->input('emi_count', 1);
    $emiCount = max(1, min($emiCount, $pendingEmis));
    $paymentDate = Carbon::parse($request->input('payment_date'))->startOfDay();
    $paymentMode = (string) $request->input('payment_mode');
    $receiptNumber = $this->generateLoanTransactionReceiptNumber();
    $initialTotalPaid = (float) $loan->total_paid;

    $applied = 0;
    for ($i = 0; $i < $emiCount; $i++) {
      if (!$loan->deductEMI()) {
        break;
      }
      $applied++;
      $loan->refresh();
      if ($loan->status !== 'active') {
        break;
      }
    }

    if ($applied <= 0) {
      return back()->with('error', 'Unable to apply EMI repayment for this loan.');
    }

    $appliedAmount = max(0, round((float) $loan->total_paid - $initialTotalPaid, 2));
    $remarks = trim((string) $request->input('repay_remarks', ''));
    $auditNote = ' [Manual EMI repayment on ' . now()->format('d-M-Y H:i') . ': ' . $applied . ' EMI(s), payment date: ' . $paymentDate->format('d-M-Y') . ', mode: ' . str_replace('_', ' ', $paymentMode) . ($receiptNumber !== '' ? ', receipt: ' . $receiptNumber : '') . ', amount: ₹' . number_format($appliedAmount, 2) . ']';
    $loan->remarks = trim(((string) $loan->remarks) . ($remarks !== '' ? ' ' . $remarks : '') . $auditNote);
    $loan->save();

    DB::table('faculty_loan_transactions')->insert([
      'faculty_loan_id' => $loan->id,
      'faculty_id' => $loan->faculty_id,
      'transaction_type' => 'emi_repayment',
      'payment_date' => $paymentDate->toDateString(),
      'payment_mode' => $paymentMode,
      'receipt_number' => $receiptNumber !== '' ? $receiptNumber : null,
      'emi_count' => $applied,
      'amount' => $appliedAmount,
      'remarks' => $remarks,
      'processed_by' => Auth::id(),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    return back()->with('success', 'EMI repayment posted successfully (' . $applied . ' EMI). Receipt No: ' . $receiptNumber . '.');
  }

  /**
   * Generate unique receipt number for manual loan transactions.
   */
  private function generateLoanTransactionReceiptNumber(): string
  {
    do {
      $candidate = 'LRC-' . now()->format('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    } while (DB::table('faculty_loan_transactions')->where('receipt_number', $candidate)->exists());

    return $candidate;
  }

  /**
   * Delete loan record (soft delete) when no recovery has started.
   */
  public function deleteLoan($id)
  {
    $loan = FacultyLoan::findOrFail($id);

    if ((int) $loan->paid_installments > 0 || (float) $loan->total_paid > 0) {
      return back()->with('error', 'This loan already has recovered EMI entries and cannot be deleted. Please suspend or complete it instead.');
    }

    if ((float) $loan->remaining_amount < (float) $loan->loan_amount) {
      return back()->with('error', 'This loan already has repayment adjustments and cannot be deleted.');
    }

    $loan->delete();

    return back()->with('success', 'Loan deleted successfully.');
  }

  /**
   * Get faculty loan and last salary info (AJAX)
   */
  public function getFacultyInfo($facultyId)
  {
    $faculty = Faculty::findOrFail($facultyId);

    // Get all active loans
    $activeLoans = FacultyLoan::where('faculty_id', $facultyId)
      ->where('status', 'active')
      ->get()
      ->map(function ($loan) {
        return [
          'id' => $loan->id,
          'loan_number' => $loan->loan_number,
          'loan_type' => $loan->loan_type,
          'loan_amount' => $loan->loan_amount,
          'emi_amount' => $loan->emi_amount,
          'remaining_amount' => $loan->remaining_amount,
          'paid_installments' => $loan->paid_installments,
          'total_installments' => $loan->total_installments,
          'progress_percentage' => $loan->progress_percentage,
        ];
      });

    // Get last salary slip
    $lastSalary = FacultySalarySlip::where('faculty_id', $facultyId)
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc')
      ->first();

    return response()->json([
      'success' => true,
      'loans' => $activeLoans,
      'lastSalary' => $lastSalary ? [
        'basic_salary' => $lastSalary->basic_salary,
        'da' => $lastSalary->da,
        'hra' => $lastSalary->hra,
        'ta' => $lastSalary->ta,
        'medical_allowance' => $lastSalary->medical_allowance,
        'special_allowance' => $lastSalary->special_allowance,
        'other_allowances' => $lastSalary->other_allowances,
        'pf' => $lastSalary->pf,
        'esi' => $lastSalary->esi,
        'professional_tax' => $lastSalary->professional_tax,
        'tds' => $lastSalary->tds,
        'other_deductions' => $lastSalary->other_deductions,
      ] : null,
    ]);
  }

  /**
   * Salary Masters Management
   */

  /**
   * List all faculty salary masters
   */
  public function salaryMasters(Request $request)
  {
    $query = FacultySalaryMaster::with(['faculty', 'payMatrix'])
      ->active()
      ->whereHas('faculty', function ($q) {
        $q->where('IS_LEFT', 0);
      })
      ->whereNotNull('pay_matrix_id');

    $salaryMasters = $query->orderBy('id', 'desc')->paginate(30);

    $totalActiveEmployees = Faculty::where('IS_LEFT', 0)->count();
    $payMatrixAdded = Faculty::where('IS_LEFT', 0)
      ->whereHas('salaryMaster', function ($q) {
        $q->whereNotNull('pay_matrix_id');
      })
      ->count();
    $payMatrixNotAdded = max($totalActiveEmployees - $payMatrixAdded, 0);

    $analytics = [
      'total_active_employees' => $totalActiveEmployees,
      'pay_matrix_added' => $payMatrixAdded,
      'pay_matrix_not_added' => $payMatrixNotAdded,
    ];

    return view('admin.accounts.payroll.salary-masters', compact('salaryMasters', 'analytics'));
  }

  /**
   * Dedicated page for all employees list with search filter.
   */
  public function employeesList(Request $request)
  {
    $search = trim((string) $request->get('search', ''));

    $query = Faculty::with(['salaryMaster.payMatrix'])
      ->where('IS_LEFT', 0);

    if ($search !== '') {
      $query->where(function ($q) use ($search) {
        $q->where('USER_CODE', 'like', "%{$search}%")
          ->orWhere('FIRST_NAME', 'like', "%{$search}%")
          ->orWhere('LAST_NAME', 'like', "%{$search}%")
          ->orWhere('MOBILE_NO', 'like', "%{$search}%")
          ->orWhere('designation', 'like', "%{$search}%")
          ->orWhere('employee_type', 'like', "%{$search}%")
          ->orWhereHas('salaryMaster.payMatrix', function ($matrixQuery) use ($search) {
            $matrixQuery->where('matrix_code', 'like', "%{$search}%")
              ->orWhere('designation', 'like', "%{$search}%")
              ->orWhere('grade_level', 'like', "%{$search}%");
          });
      });
    }

    $employees = $query
      ->orderBy('FIRST_NAME')
      ->orderBy('LAST_NAME')
      ->paginate(30)
      ->appends($request->query());

    return view('admin.accounts.payroll.employees-list', compact('employees', 'search'));
  }
}

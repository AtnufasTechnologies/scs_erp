<?php

namespace App\Http\Controllers;

use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLoan;
use App\Models\FacultySalaryMaster;
use App\Models\FacultySalarySlip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPayrollController extends Controller
{
  /**
   * Display list of all salary slips
   */
  public function index(Request $request)
  {
    $year = $request->get('year', Carbon::now()->year);
    $month = $request->get('month');
    $status = $request->get('status');
    $facultyId = $request->get('faculty_id');

    $query = FacultySalarySlip::with(['faculty', 'annualSession'])
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc');

    if ($year) {
      $query->where('year', $year);
    }

    if ($month) {
      $query->where('month', $month);
    }

    if ($status) {
      $query->where('status', $status);
    }

    if ($facultyId) {
      $query->where('faculty_id', $facultyId);
    }

    $salarySlips = $query->paginate(20);

    // Get faculties for filter
    $faculties = Faculty::orderBy('FIRST_NAME')->get();

    // Get available years
    $availableYears = FacultySalarySlip::distinct()
      ->pluck('year')
      ->sort()
      ->reverse();

    // Statistics
    $stats = [
      'total_slips' => FacultySalarySlip::where('year', $year)->count(),
      'approved' => FacultySalarySlip::where('year', $year)->where('status', 'approved')->count(),
      'paid' => FacultySalarySlip::where('year', $year)->where('status', 'paid')->count(),
      'draft' => FacultySalarySlip::where('year', $year)->where('status', 'draft')->count(),
      'total_amount' => FacultySalarySlip::where('year', $year)->paid()->sum('net_salary'),
    ];

    return view('admin.accounts.payroll.index', compact(
      'salarySlips',
      'faculties',
      'availableYears',
      'stats',
      'year',
      'month',
      'status',
      'facultyId'
    ));
  }

  /**
   * Show form to create salary slips
   */
  public function create()
  {
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();
    $sessions = AnnualSession::orderBy('id', 'desc')->get();

    return view('admin.accounts.payroll.create', compact('faculties', 'sessions'));
  }

  /**
   * Store a new salary slip
   */
  public function store(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'month' => 'required|digits:2',
      'year' => 'required|digits:4',
      'basic_salary' => 'required|numeric|min:0',
    ]);

    // Check if slip already exists
    $exists = FacultySalarySlip::where('faculty_id', $request->faculty_id)
      ->where('year', $request->year)
      ->where('month', $request->month)
      ->exists();

    if ($exists) {
      return back()->with('error', 'Salary slip already exists for this faculty for the selected month/year.');
    }

    // Generate salary slip number
    $slipNumber = 'SAL-' . $request->year . $request->month . '-' . str_pad($request->faculty_id, 4, '0', STR_PAD_LEFT);

    // Calculate total loan EMI deduction from selected loans
    $loanDeduction = 0;
    $selectedLoanIds = $request->input('selected_loans', []);
    $selectedLoans = [];

    if (!empty($selectedLoanIds)) {
      $selectedLoans = FacultyLoan::whereIn('id', $selectedLoanIds)
        ->where('faculty_id', $request->faculty_id)
        ->where('status', 'active')
        ->get();

      foreach ($selectedLoans as $loan) {
        $loanDeduction += $loan->emi_amount;
      }
    }

    // Create salary slip
    $salarySlip = new FacultySalarySlip();
    $salarySlip->faculty_id = $request->faculty_id;
    $salarySlip->annual_session_id = $request->annual_session_id;
    $salarySlip->month = $request->month;
    $salarySlip->year = $request->year;
    $salarySlip->salary_slip_number = $slipNumber;

    // Earnings
    $salarySlip->basic_salary = $request->basic_salary;
    $salarySlip->da = $request->da ?? 0;
    $salarySlip->hra = $request->hra ?? 0;
    $salarySlip->ta = $request->ta ?? 0;
    $salarySlip->medical_allowance = $request->medical_allowance ?? 0;
    $salarySlip->special_allowance = $request->special_allowance ?? 0;
    $salarySlip->other_allowances = $request->other_allowances ?? 0;

    // Deductions
    $salarySlip->pf = $request->pf ?? 0;
    $salarySlip->esi = $request->esi ?? 0;
    $salarySlip->professional_tax = $request->professional_tax ?? 0;
    $salarySlip->tds = $request->tds ?? 0;
    $salarySlip->loan_deduction = $loanDeduction + ($request->additional_loan_deduction ?? 0);
    $salarySlip->other_deductions = $request->other_deductions ?? 0;

    // Attendance
    $salarySlip->working_days = $request->working_days ?? 26;
    $salarySlip->present_days = $request->present_days ?? 26;
    $salarySlip->leave_days = $request->leave_days ?? 0;

    $salarySlip->remarks = $request->remarks;
    $salarySlip->status = 'draft';

    // Calculate totals
    $salarySlip->calculateTotals();
    $salarySlip->save();

    // Deduct EMI from all selected loans
    foreach ($selectedLoans as $loan) {
      $loan->deductEMI();
    }

    return redirect()->route('admin.payroll.index')
      ->with('success', 'Salary slip created successfully with EMI deduction from ' . count($selectedLoans) . ' loan(s).');
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
      'basic_salary' => 'required|numeric|min:0',
    ]);

    // Update fields
    $salarySlip->basic_salary = $request->basic_salary;
    $salarySlip->da = $request->da ?? 0;
    $salarySlip->hra = $request->hra ?? 0;
    $salarySlip->ta = $request->ta ?? 0;
    $salarySlip->medical_allowance = $request->medical_allowance ?? 0;
    $salarySlip->special_allowance = $request->special_allowance ?? 0;
    $salarySlip->other_allowances = $request->other_allowances ?? 0;

    $salarySlip->pf = $request->pf ?? 0;
    $salarySlip->esi = $request->esi ?? 0;
    $salarySlip->professional_tax = $request->professional_tax ?? 0;
    $salarySlip->tds = $request->tds ?? 0;
    $salarySlip->loan_deduction = $request->loan_deduction ?? 0;
    $salarySlip->other_deductions = $request->other_deductions ?? 0;

    $salarySlip->working_days = $request->working_days ?? 26;
    $salarySlip->present_days = $request->present_days ?? 26;
    $salarySlip->leave_days = $request->leave_days ?? 0;

    $salarySlip->remarks = $request->remarks;

    // Calculate totals
    $salarySlip->calculateTotals();
    $salarySlip->save();

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
      $exists = FacultySalarySlip::where('faculty_id', $salaryMaster->faculty_id)
        ->where('year', $year)
        ->where('month', $month)
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
      $slipNumber = 'SAL-' . $year . $month . '-' . str_pad($salaryMaster->faculty_id, 4, '0', STR_PAD_LEFT);

      // Create salary slip from salary master
      $salarySlip = new FacultySalarySlip();
      $salarySlip->faculty_id = $salaryMaster->faculty_id;
      $salarySlip->annual_session_id = $request->annual_session_id;
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

      $salarySlip->pf = $salaryMaster->pf;
      $salarySlip->esi = $salaryMaster->esi;
      $salarySlip->professional_tax = $salaryMaster->professional_tax;
      $salarySlip->tds = $salaryMaster->tds;
      $salarySlip->loan_deduction = $totalLoanDeduction;
      $salarySlip->other_deductions = $salaryMaster->other_deductions;

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
    $status = $request->get('status');

    $query = FacultyLoan::with('faculty')->orderBy('created_at', 'desc');

    if ($facultyId) {
      $query->where('faculty_id', $facultyId);
    }

    if ($status) {
      $query->where('status', $status);
    }

    $loans = $query->paginate(20);
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();

    $stats = [
      'active_loans' => FacultyLoan::active()->count(),
      'total_disbursed' => FacultyLoan::sum('loan_amount'),
      'total_recovered' => FacultyLoan::sum('total_paid'),
      'pending_recovery' => FacultyLoan::active()->sum('remaining_amount'),
    ];

    return view('admin.accounts.payroll.loans', compact('loans', 'faculties', 'stats', 'facultyId', 'status'));
  }

  /**
   * Store new loan
   */
  public function storeLoan(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'loan_type' => 'required|string',
      'loan_amount' => 'required|numeric|min:0',
      'emi_amount' => 'required|numeric|min:0',
      'total_installments' => 'required|integer|min:1',
      'start_date' => 'required|date',
    ]);

    $loanNumber = 'LOAN-' . date('Ymd') . '-' . str_pad($request->faculty_id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

    $loan = new FacultyLoan();
    $loan->faculty_id = $request->faculty_id;
    $loan->loan_number = $loanNumber;
    $loan->loan_type = $request->loan_type;
    $loan->loan_amount = $request->loan_amount;
    $loan->emi_amount = $request->emi_amount;
    $loan->total_installments = $request->total_installments;
    $loan->remaining_amount = $request->loan_amount;
    $loan->start_date = $request->start_date;
    $loan->status = 'active';
    $loan->remarks = $request->remarks;
    $loan->approved_by = Auth::id();
    $loan->approved_at = now();
    $loan->save();

    return back()->with('success', 'Loan created successfully.');
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
    $query = FacultySalaryMaster::with('faculty');

    // Filter by faculty
    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    // Filter by status
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $salaryMasters = $query->orderBy('id', 'desc')->paginate(30);
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();

    // Statistics
    $stats = [
      'total' => FacultySalaryMaster::active()->count(),
      'total_monthly_cost' => FacultySalaryMaster::active()->get()->sum('net_salary'),
    ];

    return view('admin.accounts.payroll.salary-masters', compact('salaryMasters', 'faculties', 'stats'));
  }

  /**
   * Show form to create salary master
   */
  public function createSalaryMaster()
  {
    $faculties = Faculty::where('IS_LEFT', 0)
      ->whereDoesntHave('salaryMaster')
      ->orderBy('FIRST_NAME')
      ->get();

    return view('admin.accounts.payroll.create-salary-master', compact('faculties'));
  }

  /**
   * Store salary master
   */
  public function storeSalaryMaster(Request $request)
  {
    $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'basic_salary' => 'required|numeric|min:0',
    ]);

    // Check if faculty already has active salary master
    $existing = FacultySalaryMaster::where('faculty_id', $request->faculty_id)
      ->where('status', 'active')
      ->exists();

    if ($existing) {
      return back()->with('error', 'This faculty already has an active salary master. Please deactivate it first.');
    }

    $salaryMaster = FacultySalaryMaster::create([
      'faculty_id' => $request->faculty_id,
      'basic_salary' => $request->basic_salary,
      'da' => $request->da ?? 0,
      'hra' => $request->hra ?? 0,
      'ta' => $request->ta ?? 0,
      'medical_allowance' => $request->medical_allowance ?? 0,
      'special_allowance' => $request->special_allowance ?? 0,
      'other_allowances' => $request->other_allowances ?? 0,
      'pf' => $request->pf ?? 0,
      'esi' => $request->esi ?? 0,
      'professional_tax' => $request->professional_tax ?? 0,
      'tds' => $request->tds ?? 0,
      'other_deductions' => $request->other_deductions ?? 0,
      'working_days' => $request->working_days ?? 26,
      'effective_from' => $request->effective_from,
      'remarks' => $request->remarks,
      'status' => 'active',
    ]);

    return redirect()->route('admin.payroll.salary-masters')
      ->with('success', 'Salary master created successfully.');
  }

  /**
   * Show form to edit salary master
   */
  public function editSalaryMaster($id)
  {
    $salaryMaster = FacultySalaryMaster::with('faculty')->findOrFail($id);
    $faculties = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();

    return view('admin.accounts.payroll.edit-salary-master', compact('salaryMaster', 'faculties'));
  }

  /**
   * Update salary master
   */
  public function updateSalaryMaster(Request $request, $id)
  {
    $salaryMaster = FacultySalaryMaster::findOrFail($id);

    $request->validate([
      'basic_salary' => 'required|numeric|min:0',
    ]);

    $salaryMaster->update([
      'basic_salary' => $request->basic_salary,
      'da' => $request->da ?? 0,
      'hra' => $request->hra ?? 0,
      'ta' => $request->ta ?? 0,
      'medical_allowance' => $request->medical_allowance ?? 0,
      'special_allowance' => $request->special_allowance ?? 0,
      'other_allowances' => $request->other_allowances ?? 0,
      'pf' => $request->pf ?? 0,
      'esi' => $request->esi ?? 0,
      'professional_tax' => $request->professional_tax ?? 0,
      'tds' => $request->tds ?? 0,
      'other_deductions' => $request->other_deductions ?? 0,
      'working_days' => $request->working_days ?? 26,
      'effective_from' => $request->effective_from,
      'remarks' => $request->remarks,
    ]);

    return redirect()->route('admin.payroll.salary-masters')
      ->with('success', 'Salary master updated successfully.');
  }

  /**
   * Delete salary master
   */
  public function destroySalaryMaster($id)
  {
    $salaryMaster = FacultySalaryMaster::findOrFail($id);
    $salaryMaster->delete();

    return back()->with('success', 'Salary master deleted successfully.');
  }

  /**
   * Toggle salary master status
   */
  public function toggleSalaryMasterStatus($id)
  {
    $salaryMaster = FacultySalaryMaster::findOrFail($id);
    $salaryMaster->status = $salaryMaster->status === 'active' ? 'inactive' : 'active';
    $salaryMaster->save();

    return back()->with('success', 'Salary master status updated successfully.');
  }
}

<?php

namespace App\Http\Controllers;

use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLoan;
use App\Models\FacultySalaryMaster;
use App\Models\FacultySalarySlip;
use App\Models\HrPayMatrix;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HrPayrollController extends Controller
{
  /**
   * Display payroll dashboard
   */
  public function index(Request $request)
  {
    $year = $request->get('year', Carbon::now()->year);
    $month = $request->get('month', Carbon::now()->format('m'));

    // Get salary slips for selected month/year
    $salarySlips = FacultySalarySlip::with(['faculty', 'annualSession'])
      ->where('year', $year)
      ->where('month', $month)
      ->latest()
      ->paginate(20);

    // Statistics
    $stats = [
      'total_faculty' => Faculty::where('IS_LEFT', 0)->count(),
      'slips_generated' => FacultySalarySlip::where('year', $year)->where('month', $month)->count(),
      'approved' => FacultySalarySlip::where('year', $year)->where('month', $month)->where('status', 'approved')->count(),
      'paid' => FacultySalarySlip::where('year', $year)->where('month', $month)->where('status', 'paid')->count(),
      'total_amount' => FacultySalarySlip::where('year', $year)->where('month', $month)->sum('net_salary'),
    ];

    return view('hr.payroll.index', compact('salarySlips', 'stats', 'year', 'month'));
  }

  /**
   * Show form to generate payroll for faculty
   */
  public function generateForm()
  {
    // Get faculties without active salary master
    $facultiesWithoutSalary = Faculty::where('IS_LEFT', 0)
      ->whereDoesntHave('salaryMaster', function ($query) {
        $query->where('status', 'active');
      })
      ->orderBy('FIRST_NAME')
      ->get();

    // Get active pay matrices
    $payMatrices = HrPayMatrix::active()
      ->orderBy('designation')
      ->orderBy('grade_level')
      ->get();

    // Get faculties with active salary master
    $facultiesWithSalary = Faculty::where('IS_LEFT', 0)
      ->whereHas('salaryMaster', function ($query) {
        $query->where('status', 'active');
      })
      ->with(['salaryMaster.payMatrix'])
      ->orderBy('FIRST_NAME')
      ->get();

    return view('hr.payroll.generate', compact('facultiesWithoutSalary', 'payMatrices', 'facultiesWithSalary'));
  }

  /**
   * Assign pay matrix to faculty and create salary master
   */
  public function assignPayMatrix(Request $request)
  {
    $validated = $request->validate([
      'faculty_id' => 'required|exists:faculties,id',
      'pay_matrix_id' => 'required|exists:hr_pay_matrix,id',
      'effective_from' => 'required|date',
    ]);

    try {
      $payMatrix = HrPayMatrix::findOrFail($validated['pay_matrix_id']);
      $components = $payMatrix->getSalaryComponents();

      DB::beginTransaction();

      // Deactivate existing active salary masters
      FacultySalaryMaster::where('faculty_id', $validated['faculty_id'])
        ->where('status', 'active')
        ->update([
          'status' => 'inactive',
          'effective_to' => date('Y-m-d', strtotime($validated['effective_from'] . ' -1 day')),
        ]);

      // Create new salary master
      $salaryMaster = FacultySalaryMaster::create([
        'faculty_id' => $validated['faculty_id'],
        'pay_matrix_id' => $payMatrix->id,
        'basic_salary' => $components['earnings']['basic_salary'],
        'da' => $components['earnings']['da'],
        'hra' => $components['earnings']['hra'],
        'ta' => $components['earnings']['ta'],
        'medical_allowance' => $components['earnings']['medical_allowance'],
        'special_allowance' => $components['earnings']['special_allowance'],
        // Keep research allowance tracked in pay matrix and merged into salary master.
        'other_allowances' => ($components['earnings']['other_allowances'] ?? 0) + ($components['earnings']['research_allowance'] ?? 0),
        'pf' => $components['deductions']['pf'],
        'esi' => $components['deductions']['esi'],
        'professional_tax' => $components['deductions']['professional_tax'],
        'tds' => $components['deductions']['tds'],
        'other_deductions' => $components['deductions']['other_deductions'],
        'working_days' => $payMatrix->default_working_days,
        'status' => 'active',
        'effective_from' => $validated['effective_from'],
        'remarks' => "Assigned Pay Matrix: {$payMatrix->matrix_code} - {$payMatrix->full_designation}",
      ]);

      DB::commit();

      return redirect()
        ->route('hr.payroll.generate')
        ->with('success', 'Pay matrix assigned successfully.');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Pay matrix assignment failed: ' . $e->getMessage());
      return back()
        ->withInput()
        ->with('error', 'Failed to assign pay matrix. Please try again.');
    }
  }

  /**
   * Bulk generate salary slips for a month
   */
  public function bulkGenerate(Request $request)
  {
    $validated = $request->validate([
      'month' => 'required|digits:2|min:1|max:12',
      'year' => 'required|digits:4',
      'working_days' => 'required|integer|min:1|max:31',
      'faculty_ids' => 'nullable|array',
      'faculty_ids.*' => 'exists:faculties,id',
    ]);

    try {
      DB::beginTransaction();

      // Get faculties to generate salary for
      if (!empty($validated['faculty_ids'])) {
        $faculties = Faculty::whereIn('id', $validated['faculty_ids'])
          ->where('IS_LEFT', 0)
          ->with(['salaryMaster' => function ($query) {
            $query->where('status', 'active');
          }])
          ->get();
      } else {
        $faculties = Faculty::where('IS_LEFT', 0)
          ->whereHas('salaryMaster', function ($query) {
            $query->where('status', 'active');
          })
          ->with(['salaryMaster' => function ($query) {
            $query->where('status', 'active');
          }])
          ->get();
      }

      $generatedCount = 0;
      $skippedCount = 0;
      $errors = [];

      foreach ($faculties as $faculty) {
        // Check if salary slip already exists
        $existingSlip = FacultySalarySlip::where('faculty_id', $faculty->id)
          ->where('month', $validated['month'])
          ->where('year', $validated['year'])
          ->first();

        if ($existingSlip) {
          $skippedCount++;
          continue;
        }

        $salaryMaster = $faculty->salaryMaster;
        if (!$salaryMaster) {
          $errors[] = "{$faculty->FIRST_NAME} {$faculty->LAST_NAME} - No active salary master";
          continue;
        }

        // Calculate pro-rata if working days differ
        $ratio = $validated['working_days'] / $salaryMaster->working_days;

        // Get active loan EMI for this faculty
        $activeLoan = FacultyLoan::where('faculty_id', $faculty->id)
          ->where('status', 'active')
          ->first();

        $loanDeduction = $activeLoan ? $activeLoan->emi_amount : 0;

        // Calculate total earnings
        $basicSalary = $salaryMaster->basic_salary * $ratio;
        $da = $salaryMaster->da * $ratio;
        $hra = $salaryMaster->hra * $ratio;
        $ta = $salaryMaster->ta * $ratio;
        $medicalAllowance = $salaryMaster->medical_allowance * $ratio;
        $specialAllowance = $salaryMaster->special_allowance * $ratio;
        $otherAllowances = $salaryMaster->other_allowances * $ratio;

        $grossSalary = $basicSalary + $da + $hra + $ta + $medicalAllowance + $specialAllowance + $otherAllowances;

        // Calculate deductions
        $pf = $salaryMaster->pf * $ratio;
        $esi = $salaryMaster->esi * $ratio;
        $professionalTax = $salaryMaster->professional_tax * $ratio;
        $tds = $salaryMaster->tds * $ratio;
        $otherDeductions = $salaryMaster->other_deductions * $ratio;

        $totalDeductions = $pf + $esi + $professionalTax + $tds + $loanDeduction + $otherDeductions;
        $netSalary = $grossSalary - $totalDeductions;

        // Generate salary slip number
        $slipNumber = $this->generateSalarySlipNumber($validated['year'], $validated['month']);

        // Get current session
        $session = AnnualSession::where('is_active', 1)->first();

        // Create salary slip
        FacultySalarySlip::create([
          'faculty_id' => $faculty->id,
          'annual_session_id' => $session ? $session->id : null,
          'month' => $validated['month'],
          'year' => $validated['year'],
          'salary_slip_number' => $slipNumber,
          'basic_salary' => round($basicSalary, 2),
          'da' => round($da, 2),
          'hra' => round($hra, 2),
          'ta' => round($ta, 2),
          'medical_allowance' => round($medicalAllowance, 2),
          'special_allowance' => round($specialAllowance, 2),
          'other_allowances' => round($otherAllowances, 2),
          'pf' => round($pf, 2),
          'esi' => round($esi, 2),
          'professional_tax' => round($professionalTax, 2),
          'tds' => round($tds, 2),
          'loan_deduction' => round($loanDeduction, 2),
          'other_deductions' => round($otherDeductions, 2),
          'gross_salary' => round($grossSalary, 2),
          'total_deductions' => round($totalDeductions, 2),
          'net_salary' => round($netSalary, 2),
          'working_days' => $validated['working_days'],
          'present_days' => $validated['working_days'], // Default to full attendance
          'leave_days' => 0,
          'status' => 'draft',
          'remarks' => 'Generated from Pay Matrix via HR Module',
        ]);

        // Update loan progress if there's a deduction
        if ($activeLoan && $loanDeduction > 0) {
          $activeLoan->increment('installments_paid');
          $activeLoan->increment('amount_paid', $loanDeduction);

          // Mark as completed if fully paid
          if ($activeLoan->installments_paid >= $activeLoan->total_installments) {
            $activeLoan->update(['status' => 'completed']);
          }
        }

        $generatedCount++;
      }

      DB::commit();

      $message = "Generated {$generatedCount} salary slip(s).";
      if ($skippedCount > 0) {
        $message .= " Skipped {$skippedCount} (already exists).";
      }
      if (count($errors) > 0) {
        $message .= " " . count($errors) . " error(s): " . implode(', ', $errors);
      }

      return redirect()
        ->route('hr.payroll.index', ['year' => $validated['year'], 'month' => $validated['month']])
        ->with('success', $message);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Bulk salary generation failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to generate salary slips. Please try again.');
    }
  }

  /**
   * Show individual salary slip
   */
  public function show($id)
  {
    $salarySlip = FacultySalarySlip::with(['faculty', 'annualSession'])->findOrFail($id);

    return view('hr.payroll.show', compact('salarySlip'));
  }

  /**
   * Approve salary slip
   */
  public function approve($id)
  {
    try {
      $salarySlip = FacultySalarySlip::findOrFail($id);

      if ($salarySlip->status !== 'draft') {
        return back()->with('error', 'Only draft salary slips can be approved.');
      }

      $salarySlip->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
      ]);

      return back()->with('success', 'Salary slip approved successfully.');
    } catch (\Exception $e) {
      Log::error('Salary slip approval failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to approve salary slip. Please try again.');
    }
  }

  /**
   * Mark salary slip as paid
   */
  public function markPaid(Request $request, $id)
  {
    $validated = $request->validate([
      'payment_date' => 'required|date',
      'payment_mode' => 'required|in:bank_transfer,cash,cheque',
      'payment_reference' => 'nullable|string|max:255',
    ]);

    try {
      $salarySlip = FacultySalarySlip::findOrFail($id);

      if ($salarySlip->status === 'paid') {
        return back()->with('error', 'Salary slip is already marked as paid.');
      }

      $salarySlip->update([
        'status' => 'paid',
        'payment_date' => $validated['payment_date'],
        'payment_mode' => $validated['payment_mode'],
        'payment_reference' => $validated['payment_reference'],
        'approved_by' => Auth::id(),
        'approved_at' => $salarySlip->approved_at ?? now(),
      ]);

      return back()->with('success', 'Salary slip marked as paid successfully.');
    } catch (\Exception $e) {
      Log::error('Mark as paid failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to mark salary slip as paid. Please try again.');
    }
  }

  /**
   * Delete salary slip
   */
  public function destroy($id)
  {
    try {
      $salarySlip = FacultySalarySlip::findOrFail($id);

      if ($salarySlip->status === 'paid') {
        return back()->with('error', 'Cannot delete a paid salary slip.');
      }

      $salarySlip->delete();

      return redirect()
        ->route('hr.payroll.index')
        ->with('success', 'Salary slip deleted successfully.');
    } catch (\Exception $e) {
      Log::error('Salary slip deletion failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to delete salary slip. Please try again.');
    }
  }

  /**
   * Generate unique salary slip number
   */
  private function generateSalarySlipNumber($year, $month)
  {
    $prefix = "SS{$year}{$month}";
    $lastSlip = FacultySalarySlip::where('salary_slip_number', 'like', "{$prefix}%")
      ->orderBy('salary_slip_number', 'desc')
      ->first();

    if ($lastSlip) {
      $lastNumber = intval(substr($lastSlip->salary_slip_number, -4));
      $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
      $newNumber = '0001';
    }

    return "{$prefix}{$newNumber}";
  }

  /**
   * Payroll statistics
   */
  public function statistics(Request $request)
  {
    $year = $request->get('year', Carbon::now()->year);

    $monthlyStats = [];
    for ($month = 1; $month <= 12; $month++) {
      $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
      $slips = FacultySalarySlip::where('year', $year)->where('month', $monthPadded);

      $monthlyStats[] = [
        'month' => $month,
        'month_name' => Carbon::create()->month($month)->format('F'),
        'total_slips' => $slips->count(),
        'approved' => $slips->where('status', 'approved')->count(),
        'paid' => $slips->where('status', 'paid')->count(),
        'total_amount' => $slips->sum('net_salary'),
      ];
    }

    // Pay matrix usage
    $payMatrixUsage = HrPayMatrix::withCount('facultySalaries')
      ->having('faculty_salaries_count', '>', 0)
      ->get();

    return view('hr.payroll.statistics', compact('monthlyStats', 'payMatrixUsage', 'year'));
  }
}

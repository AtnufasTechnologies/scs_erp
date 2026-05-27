<?php

namespace App\Http\Controllers;

use App\Models\HrPayMatrix;
use App\Models\FacultySalaryMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HrPayMatrixController extends Controller
{
  /**
   * Display a listing of pay matrix entries
   */
  public function index(Request $request)
  {
    $query = HrPayMatrix::query()->with(['creator', 'updater']);

    // Search filter
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('matrix_code', 'like', "%{$search}%")
          ->orWhere('matrix_name', 'like', "%{$search}%")
          ->orWhere('designation', 'like', "%{$search}%")
          ->orWhere('grade_level', 'like', "%{$search}%");
      });
    }

    // Status filter
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    // Employment type filter
    if ($request->filled('employment_type')) {
      $query->where('employment_type', $request->employment_type);
    }

    // Designation filter
    if ($request->filled('designation')) {
      $query->where('designation', $request->designation);
    }

    $payMatrices = $query->latest()->paginate(20);

    // Get unique designations for filter
    $designations = HrPayMatrix::distinct()->pluck('designation');

    return view('hr.pay-matrix.index', compact('payMatrices', 'designations'));
  }

  /**
   * Show the form for creating a new pay matrix
   */
  public function create()
  {
    $designations = \App\Models\HrDesignation::active()->ordered()->get();
    $gradeLevels = \App\Models\HrGradeLevel::active()->ordered()->get();

    return view('hr.pay-matrix.create', compact('designations', 'gradeLevels'));
  }

  /**
   * Store a newly created pay matrix
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'matrix_name' => 'required|string|max:255',
      'designation_id' => 'required|exists:hr_designations,id',
      'grade_level_id' => 'required|exists:hr_grade_levels,id',
      'designation' => 'nullable|string|max:255',
      'grade_level' => 'nullable|string|max:255',
      'pay_band' => 'nullable|integer',
      'grade_pay' => 'nullable|integer',
      'employment_type' => 'required|in:permanent,contractual,adhoc,guest,visiting',
      'basic_salary' => 'required|numeric|min:0',
      'da_percentage' => 'nullable|numeric|min:0|max:100',
      'da_fixed' => 'nullable|numeric|min:0',
      'hra_percentage' => 'nullable|numeric|min:0|max:100',
      'hra_fixed' => 'nullable|numeric|min:0',
      'ta' => 'nullable|numeric|min:0',
      'medical_allowance' => 'nullable|numeric|min:0',
      'special_allowance' => 'nullable|numeric|min:0',
      'other_allowances' => 'nullable|numeric|min:0',
      'pf_percentage' => 'nullable|numeric|min:0|max:100',
      'pf_fixed' => 'nullable|numeric|min:0',
      'esi_percentage' => 'nullable|numeric|min:0|max:100',
      'esi_fixed' => 'nullable|numeric|min:0',
      'professional_tax' => 'nullable|numeric|min:0',
      'tds_percentage' => 'nullable|numeric|min:0|max:100',
      'other_deductions' => 'nullable|numeric|min:0',
      'annual_increment_percentage' => 'nullable|numeric|min:0|max:100',
      'increment_month' => 'nullable|integer|min:1|max:12',
      'default_working_days' => 'required|integer|min:1|max:31',
      'status' => 'required|in:active,inactive,archived',
      'effective_from' => 'nullable|date',
      'effective_to' => 'nullable|date|after:effective_from',
      'description' => 'nullable|string',
      'remarks' => 'nullable|string',
    ]);

    try {
      $payMatrix = HrPayMatrix::create($validated);

      return redirect()
        ->route('hr.pay-matrix.show', $payMatrix->id)
        ->with('success', 'Pay matrix created successfully.');
    } catch (\Exception $e) {
      Log::error('Pay matrix creation failed: ' . $e->getMessage());
      return back()
        ->withInput()
        ->with('error', 'Failed to create pay matrix. Please try again.');
    }
  }

  /**
   * Display the specified pay matrix
   */
  public function show($id)
  {
    $payMatrix = HrPayMatrix::with(['creator', 'updater', 'facultySalaries.faculty'])->findOrFail($id);

    // Get salary components breakdown
    $components = $payMatrix->getSalaryComponents();

    // Get faculty members using this pay matrix
    $facultyCount = $payMatrix->facultySalaries()->count();

    return view('hr.pay-matrix.show', compact('payMatrix', 'components', 'facultyCount'));
  }

  /**
   * Show the form for editing the pay matrix
   */
  public function edit($id)
  {
    $payMatrix = HrPayMatrix::findOrFail($id);

    return view('hr.pay-matrix.edit', compact('payMatrix'));
  }

  /**
   * Update the specified pay matrix
   */
  public function update(Request $request, $id)
  {
    $payMatrix = HrPayMatrix::findOrFail($id);

    $validated = $request->validate([
      'matrix_name' => 'required|string|max:255',
      'designation' => 'required|string|max:255',
      'grade_level' => 'required|string|max:255',
      'pay_band' => 'nullable|integer',
      'grade_pay' => 'nullable|integer',
      'employment_type' => 'required|in:permanent,contractual,adhoc,guest,visiting',
      'basic_salary' => 'required|numeric|min:0',
      'da_percentage' => 'nullable|numeric|min:0|max:100',
      'da_fixed' => 'nullable|numeric|min:0',
      'hra_percentage' => 'nullable|numeric|min:0|max:100',
      'hra_fixed' => 'nullable|numeric|min:0',
      'ta' => 'nullable|numeric|min:0',
      'medical_allowance' => 'nullable|numeric|min:0',
      'special_allowance' => 'nullable|numeric|min:0',
      'other_allowances' => 'nullable|numeric|min:0',
      'pf_percentage' => 'nullable|numeric|min:0|max:100',
      'pf_fixed' => 'nullable|numeric|min:0',
      'esi_percentage' => 'nullable|numeric|min:0|max:100',
      'esi_fixed' => 'nullable|numeric|min:0',
      'professional_tax' => 'nullable|numeric|min:0',
      'tds_percentage' => 'nullable|numeric|min:0|max:100',
      'other_deductions' => 'nullable|numeric|min:0',
      'annual_increment_percentage' => 'nullable|numeric|min:0|max:100',
      'increment_month' => 'nullable|integer|min:1|max:12',
      'default_working_days' => 'required|integer|min:1|max:31',
      'status' => 'required|in:active,inactive,archived',
      'effective_from' => 'nullable|date',
      'effective_to' => 'nullable|date|after:effective_from',
      'description' => 'nullable|string',
      'remarks' => 'nullable|string',
    ]);

    try {
      $payMatrix->update($validated);

      return redirect()
        ->route('hr.pay-matrix.show', $payMatrix->id)
        ->with('success', 'Pay matrix updated successfully.');
    } catch (\Exception $e) {
      Log::error('Pay matrix update failed: ' . $e->getMessage());
      return back()
        ->withInput()
        ->with('error', 'Failed to update pay matrix. Please try again.');
    }
  }

  /**
   * Remove the specified pay matrix
   */
  public function destroy($id)
  {
    try {
      $payMatrix = HrPayMatrix::findOrFail($id);

      // Check if any faculty is using this pay matrix
      $facultyCount = $payMatrix->facultySalaries()->count();
      if ($facultyCount > 0) {
        return back()->with('error', "Cannot delete pay matrix. {$facultyCount} faculty members are currently using this matrix.");
      }

      $payMatrix->delete();

      return redirect()
        ->route('hr.pay-matrix.index')
        ->with('success', 'Pay matrix deleted successfully.');
    } catch (\Exception $e) {
      Log::error('Pay matrix deletion failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to delete pay matrix. Please try again.');
    }
  }

  /**
   * Archive the pay matrix
   */
  public function archive($id)
  {
    try {
      $payMatrix = HrPayMatrix::findOrFail($id);
      $payMatrix->update(['status' => 'archived']);

      return back()->with('success', 'Pay matrix archived successfully.');
    } catch (\Exception $e) {
      Log::error('Pay matrix archival failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to archive pay matrix. Please try again.');
    }
  }

  /**
   * Duplicate an existing pay matrix
   */
  public function duplicate($id)
  {
    try {
      $original = HrPayMatrix::findOrFail($id);

      $newMatrix = $original->replicate();
      $newMatrix->matrix_code = null; // Will be auto-generated
      $newMatrix->matrix_name = $original->matrix_name . ' (Copy)';
      $newMatrix->status = 'inactive';
      $newMatrix->effective_from = null;
      $newMatrix->effective_to = null;
      $newMatrix->save();

      return redirect()
        ->route('hr.pay-matrix.edit', $newMatrix->id)
        ->with('success', 'Pay matrix duplicated successfully. Please update the details.');
    } catch (\Exception $e) {
      Log::error('Pay matrix duplication failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to duplicate pay matrix. Please try again.');
    }
  }

  /**
   * Apply pay matrix to faculty
   */
  public function applyToFaculty(Request $request, $id)
  {
    $validated = $request->validate([
      'faculty_ids' => 'required|array',
      'faculty_ids.*' => 'exists:faculties,id',
      'effective_from' => 'required|date',
    ]);

    try {
      $payMatrix = HrPayMatrix::findOrFail($id);
      $components = $payMatrix->getSalaryComponents();

      DB::beginTransaction();

      foreach ($validated['faculty_ids'] as $facultyId) {
        // Deactivate existing active salary masters
        FacultySalaryMaster::where('faculty_id', $facultyId)
          ->where('status', 'active')
          ->update([
            'status' => 'inactive',
            'effective_to' => date('Y-m-d', strtotime($validated['effective_from'] . ' -1 day')),
          ]);

        // Create new salary master based on pay matrix
        FacultySalaryMaster::create([
          'faculty_id' => $facultyId,
          'pay_matrix_id' => $payMatrix->id,
          'basic_salary' => $components['earnings']['basic_salary'],
          'da' => $components['earnings']['da'],
          'hra' => $components['earnings']['hra'],
          'ta' => $components['earnings']['ta'],
          'medical_allowance' => $components['earnings']['medical_allowance'],
          'special_allowance' => $components['earnings']['special_allowance'],
          'other_allowances' => $components['earnings']['other_allowances'],
          'pf' => $components['deductions']['pf'],
          'esi' => $components['deductions']['esi'],
          'professional_tax' => $components['deductions']['professional_tax'],
          'tds' => $components['deductions']['tds'],
          'other_deductions' => $components['deductions']['other_deductions'],
          'working_days' => $payMatrix->default_working_days,
          'status' => 'active',
          'effective_from' => $validated['effective_from'],
          'remarks' => "Applied from Pay Matrix: {$payMatrix->matrix_code}",
        ]);
      }

      DB::commit();

      $count = count($validated['faculty_ids']);
      return redirect()
        ->route('hr.pay-matrix.show', $id)
        ->with('success', "Pay matrix applied to {$count} faculty member(s) successfully.");
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Pay matrix application failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to apply pay matrix. Please try again.');
    }
  }

  /**
   * Get pay matrix calculation preview
   */
  public function preview($id)
  {
    $payMatrix = HrPayMatrix::findOrFail($id);
    $components = $payMatrix->getSalaryComponents();

    return response()->json([
      'success' => true,
      'components' => $components,
      'matrix' => $payMatrix,
    ]);
  }
}

<?php

namespace App\Http\Controllers;

use App\Models\DeductionMaster;
use App\Models\Faculty;
use App\Models\FacultyDeductionAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDeductionMasterController extends Controller
{


  /**
   * Show deduction masters and faculty assignments.
   */
  public function index(Request $request)
  {


    $masters = DeductionMaster::latest()->get();

    $faculties = Faculty::where('IS_LEFT', 0)
      ->orderBy('FIRST_NAME')
      ->get();

    $assignmentQuery = FacultyDeductionAssignment::with(['faculty', 'deductionMaster'])
      ->orderByDesc('id');

    if ($request->filled('faculty_id')) {
      $assignmentQuery->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('deduction_master_id')) {
      $assignmentQuery->where('deduction_master_id', $request->deduction_master_id);
    }

    if ($request->filled('status')) {
      $assignmentQuery->where('status', $request->status);
    }

    $assignments = $assignmentQuery->paginate(20, ['*'], 'assignments_page');

    return view('admin.accounts.payroll.deduction-masters', compact(
      'masters',
      'faculties',
      'assignments'
    ));
  }

  /**
   * Create a deduction master.
   */
  public function storeMaster(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'tds' => 'nullable|numeric|min:0',
      'epf' => 'nullable|numeric|min:0',
      'pt' => 'nullable|numeric|min:0',
      'esic' => 'nullable|numeric|min:0',
      'lwf' => 'nullable|numeric|min:0',
      'status' => 'required',
    ]);

    DeductionMaster::create([
      'title' => $request->title,
      'TDS' => $request->tds,
      'EPF' => $request->epf,
      'PT' => $request->pt,
      'ESIC' => $request->esic,
      'LWF' => $request->lwf,
      'status' => $request->status
    ]);

    return back()->with('success', 'Deduction master created successfully.');
  }

  /**
   * Update a deduction master.
   */
  public function updateMaster(Request $request, $id)
  {


    return back()->with('success', 'Deduction master updated successfully.');
  }

  /**
   * Activate or deactivate a deduction master.
   */
  public function toggleMasterStatus($id)
  {
    $master = DeductionMaster::findOrFail($id);
    $master->status = $master->status == 1 ? 0 : 1;
    $master->save();

    return back()->with('success', 'Deduction master status updated successfully.');
  }

  /**
   * Assign deduction master to multiple faculties.
   */
  public function assignToFaculties(Request $request)
  {
    $request->validate([
      'deduction_master_id' => 'required|exists:accounts_deduction_masters,id',
      'faculty_ids' => 'required|array|min:1',
      'faculty_ids.*' => 'required|exists:faculties,id',
      'remarks' => 'nullable|string|max:1000',
    ]);

    $userId = Auth::id();
    $assignedCount = 0;

    foreach ($request->faculty_ids as $facultyId) {
      $assignment = FacultyDeductionAssignment::firstOrNew([
        'faculty_id' => $facultyId,
        'deduction_master_id' => $request->deduction_master_id,
      ]);

      if (!$assignment->exists) {
        $assignment->created_by = $userId;
      }

      $assignment->status = 'active';
      $assignment->remarks = $request->remarks;
      $assignment->updated_by = $userId;
      $assignment->save();
      $assignedCount++;
    }

    return back()->with('success', 'Deduction assigned successfully to ' . $assignedCount . ' faculty(s).');
  }

  /**
   * Activate or deactivate a faculty deduction assignment.
   */
  public function toggleAssignmentStatus($id)
  {
    $assignment = FacultyDeductionAssignment::findOrFail($id);
    $assignment->status = $assignment->status === 'active' ? 'inactive' : 'active';
    $assignment->updated_by = Auth::id();
    $assignment->save();

    return back()->with('success', 'Assignment status updated successfully.');
  }
}

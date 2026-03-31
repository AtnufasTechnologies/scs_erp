<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\StudentExitRecord;
use App\Models\StudentMaster;
use Illuminate\Http\Request;

class ExitCertificationController extends Controller
{
  public function index(Request $request)
  {
    $query = StudentExitRecord::with(['student']);

    if ($request->has('status') && $request->status != '') {
      $query->where('exit_status', $request->status);
    }

    if ($request->has('year') && $request->year != '') {
      $query->whereYear('exit_date', $request->year);
    }

    $records = $query->orderBy('exit_date', 'desc')->paginate(50);

    return view('coe.exit-certification.index', compact('records'));
  }

  public function create()
  {
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.exit-certification.create', compact('students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id|unique:student_exit_records,exam_student_id',
      'exit_date' => 'required|date',
      'exit_status' => 'required|in:graduated,transferred,withdrawn,expelled',
      'cgpa' => 'nullable|numeric',
      'final_result' => 'nullable|in:pass,fail',
    ]);

    StudentExitRecord::create($request->all());

    return redirect()->route('coe.exit-certification.index')
      ->with('success', 'Exit record created successfully');
  }

  public function show($id)
  {
    $record = StudentExitRecord::with(['student'])->findOrFail($id);
    return view('coe.exit-certification.show', compact('record'));
  }

  public function edit($id)
  {
    $record = StudentExitRecord::findOrFail($id);
    $students = StudentMaster::where('is_deleted', 0)->get();

    return view('coe.exit-certification.edit', compact('record', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'exit_date' => 'required|date',
      'exit_status' => 'required|in:graduated,transferred,withdrawn,expelled',
      'cgpa' => 'nullable|numeric',
      'final_result' => 'nullable|in:pass,fail',
    ]);

    $record = StudentExitRecord::findOrFail($id);
    $record->update($request->all());

    return redirect()->route('coe.exit-certification.index')
      ->with('success', 'Exit record updated successfully');
  }

  public function destroy($id)
  {
    $record = StudentExitRecord::findOrFail($id);
    $record->delete();

    return redirect()->route('coe.exit-certification.index')
      ->with('success', 'Exit record deleted successfully');
  }

  public function certificate($id)
  {
    $record = StudentExitRecord::with(['student'])->findOrFail($id);

    return view('coe.exit-certification.certificate', compact('record'));
  }

  public function export(Request $request)
  {
    $query = StudentExitRecord::with(['student', 'exitType']);

    if ($request->has('exit_type') && $request->exit_type != '') {
      $query->where('exit_type', $request->exit_type);
    }

    $records = $query->get();
    return response()->json($records);
  }

  public function approve(Request $request)
  {
    $request->validate([
      'record_id' => 'required|exists:student_exit_records,id',
    ]);

    $record = StudentExitRecord::findOrFail($request->record_id);
    $record->update([
      'status' => 'approved',
      'approved_at' => now(),
      'approved_by' => auth()->id(),
    ]);

    return redirect()->back()
      ->with('success', 'Exit certification approved successfully');
  }
}

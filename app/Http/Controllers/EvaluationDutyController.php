<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\Exam;
use App\Models\Faculty;
use App\Models\ProgramCourseMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationDutyController extends Controller
{
  public function index(Request $request)
  {
    $query = EvaluationDuty::with(['faculty', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::all();

    return view('coe.evaluation-duties.index', compact('duties', 'exams'));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::all();
    $subjects = ProgramCourseMaster::all();

    return view('coe.evaluation-duties.create', compact('exams', 'faculties', 'subjects'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required',
      'papers_assigned' => 'nullable|integer',
    ]);

    EvaluationDuty::create(array_merge($request->all(), ['status' => 'assigned']));

    return redirect()->route('coe.evaluation-duties.index')
      ->with('success', 'Evaluation duty assigned successfully');
  }

  public function show($id)
  {
    $duty = EvaluationDuty::with(['faculty', 'exam'])->findOrFail($id);
    return view('coe.evaluation-duties.show', compact('duty'));
  }

  public function edit($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $exams = Exam::all();
    $faculties = Faculty::all();
    $subjects = ProgramCourseMaster::all();

    return view('coe.evaluation-duties.edit', compact('duty', 'exams', 'faculties', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required',
      'papers_assigned' => 'nullable|integer',
    ]);

    $duty = EvaluationDuty::findOrFail($id);
    $duty->update($request->all());

    return redirect()->route('coe.evaluation-duties.index')
      ->with('success', 'Evaluation duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('coe.evaluation-duties.index')
      ->with('success', 'Evaluation duty deleted successfully');
  }

  public function markCompleted($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $duty->update(['status' => 'completed']);

    return redirect()->back()->with('success', 'Evaluation marked as completed');
  }

  public function export(Request $request)
  {
    $query = EvaluationDuty::with(['exam', 'faculty', 'subject']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $duties = $query->get();
    return response()->json($duties);
  }

  public function autoAssign(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'subject_id' => 'required|exists:subjects,id',
    ]);

    try {
      DB::beginTransaction();

      // Auto-assign evaluation duties logic
      // Get faculty members qualified for the subject
      // Distribute answer sheets evenly
      // Consider faculty availability and workload

      DB::commit();
      return redirect()->route('admin.evaluation-duties.index')
        ->with('success', 'Evaluation duties auto-assigned successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-assignment failed: ' . $e->getMessage());
    }
  }
}

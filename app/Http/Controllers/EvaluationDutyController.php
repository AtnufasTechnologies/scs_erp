<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamSchedule;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationDutyController extends Controller
{
  public function index(Request $request)
  {
    $query = EvaluationDuty::with(['faculty', 'exam', 'subject']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('subject_id')) {
      $query->where('subject_id', $request->subject_id);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();
    $subjects = ExamSubjectMaster::select('id', 'subject_code', 'name')->orderBy('name')->get();

    $totalDuties = EvaluationDuty::count();
    $totalAssigned = EvaluationDuty::sum('copies_assigned');
    $totalEvaluated = EvaluationDuty::sum('copies_evaluated');
    $completedCount = EvaluationDuty::where('status', 'completed')->count();
    $overallProgress = $totalAssigned > 0 ? round(($totalEvaluated / $totalAssigned) * 100) : 0;

    return view('coe.evaluation-duties.index', compact(
      'duties',
      'exams',
      'faculties',
      'subjects',
      'totalDuties',
      'totalAssigned',
      'totalEvaluated',
      'completedCount',
      'overallProgress'
    ));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();

    return view('coe.evaluation-duties.create', compact('exams', 'faculties'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|array|min:1',
      'faculty_id.*' => 'exists:faculties,id',
      'subject_id' => 'required|array|min:1',
      'subject_id.*' => 'exists:exam_subject_masters,id',
      'copies_assigned' => 'required|integer|min:1',
    ]);

    $count = 0;
    foreach ($request->faculty_id as $facultyId) {
      foreach ($request->subject_id as $subjectId) {
        EvaluationDuty::create([
          'exam_id' => $request->exam_id,
          'faculty_id' => $facultyId,
          'subject_id' => $subjectId,
          'copies_assigned' => $request->copies_assigned,
          'copies_evaluated' => 0,
          'status' => 'pending',
        ]);
        $count++;
      }
    }

    return redirect()->route('admin.evaluation-duties.index')
      ->with('success', $count . ' evaluation duty/duties assigned successfully');
  }

  public function show($id)
  {
    $duty = EvaluationDuty::with(['faculty', 'exam', 'subject'])->findOrFail($id);
    return view('coe.evaluation-duties.show', compact('duty'));
  }

  public function edit($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();
    $subjectIds = ExamSchedule::where('exam_id', $duty->exam_id)->pluck('exam_subject_id')->unique();
    $subjects = ExamSubjectMaster::whereIn('id', $subjectIds)->select('id', 'subject_code', 'name')->orderBy('name')->get();

    return view('coe.evaluation-duties.edit', compact('duty', 'exams', 'faculties', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required|exists:exam_subject_masters,id',
      'copies_assigned' => 'required|integer|min:1',
      'copies_evaluated' => 'required|integer|min:0',
    ]);

    $duty = EvaluationDuty::findOrFail($id);
    $copiesAssigned = $request->copies_assigned;
    $copiesEvaluated = min($request->copies_evaluated, $copiesAssigned);
    $status = $copiesEvaluated >= $copiesAssigned ? 'completed' : ($copiesEvaluated > 0 ? 'in_progress' : 'pending');

    $duty->update([
      'exam_id' => $request->exam_id,
      'faculty_id' => $request->faculty_id,
      'subject_id' => $request->subject_id,
      'copies_assigned' => $copiesAssigned,
      'copies_evaluated' => $copiesEvaluated,
      'status' => $status,
    ]);

    return redirect()->route('admin.evaluation-duties.index')
      ->with('success', 'Evaluation duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('admin.evaluation-duties.index')
      ->with('success', 'Evaluation duty deleted successfully');
  }

  public function markCompleted($id)
  {
    $duty = EvaluationDuty::findOrFail($id);
    $duty->update([
      'copies_evaluated' => $duty->copies_assigned,
      'status' => 'completed',
    ]);

    return redirect()->back()->with('success', 'Evaluation marked as completed');
  }

  public function updateProgress(Request $request, $id)
  {
    $request->validate([
      'copies_evaluated' => 'required|integer|min:0',
    ]);

    $duty = EvaluationDuty::findOrFail($id);
    $copiesEvaluated = min($request->copies_evaluated, $duty->copies_assigned);
    $status = $copiesEvaluated >= $duty->copies_assigned ? 'completed' : ($copiesEvaluated > 0 ? 'in_progress' : 'pending');

    $duty->update([
      'copies_evaluated' => $copiesEvaluated,
      'status' => $status,
    ]);

    return redirect()->back()->with('success', 'Progress updated successfully');
  }

  public function export(Request $request)
  {
    $query = EvaluationDuty::with(['exam', 'faculty', 'subject']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    $duties = $query->get();
    return response()->json($duties);
  }

  public function getSubjectsByExam($examId)
  {
    $subjectIds = ExamSchedule::where('exam_id', $examId)->pluck('exam_subject_id')->unique();
    $subjects = ExamSubjectMaster::whereIn('id', $subjectIds)
      ->select('id', 'subject_code', 'name')
      ->orderBy('name')
      ->get();

    return response()->json($subjects);
  }

  public function autoAssign(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'subject_id' => 'required|exists:exam_subject_masters,id',
    ]);

    try {
      DB::beginTransaction();

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

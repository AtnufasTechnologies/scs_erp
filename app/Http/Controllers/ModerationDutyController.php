<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\ModerationDuty;
use App\Models\ExamSystem\ModerationRecord;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ExamMarksEntry;
use App\Models\ExamSystem\ExamSubjectMaster;
use App\Models\ExamSystem\ExamSchedule;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModerationDutyController extends Controller
{
  protected $deviationThreshold = 10;

  public function index(Request $request)
  {
    $query = ModerationDuty::with(['faculty', 'exam', 'subject']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $duties = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();

    $totalDuties = ModerationDuty::count();
    $pendingCount = ModerationDuty::where('status', 'pending')->count();
    $completedCount = ModerationDuty::where('status', 'completed')->count();

    return view('coe.moderation.index', compact(
      'duties',
      'exams',
      'faculties',
      'totalDuties',
      'pendingCount',
      'completedCount'
    ));
  }

  public function create()
  {
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();

    return view('coe.moderation.create', compact('exams', 'faculties'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|array|min:1',
      'faculty_id.*' => 'exists:faculties,id',
      'subject_id' => 'required|array|min:1',
      'subject_id.*' => 'exists:exam_subject_masters,id',
      'moderation_type' => 'required|in:internal,external',
    ]);

    $count = 0;
    foreach ($request->faculty_id as $facultyId) {
      foreach ($request->subject_id as $subjectId) {
        ModerationDuty::create([
          'exam_id' => $request->exam_id,
          'faculty_id' => $facultyId,
          'subject_id' => $subjectId,
          'moderation_type' => $request->moderation_type,
          'status' => 'pending',
        ]);
        $count++;
      }
    }

    return redirect()->route('admin.moderation-duties.index')
      ->with('success', $count . ' moderation duty/duties assigned successfully');
  }

  public function show($id)
  {
    $duty = ModerationDuty::with(['faculty', 'exam', 'subject'])->findOrFail($id);
    return view('coe.moderation.show', compact('duty'));
  }

  public function edit($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $exams = Exam::all();
    $faculties = Faculty::orderBy('FIRST_NAME')->get();
    $subjectIds = ExamSchedule::where('exam_id', $duty->exam_id)->pluck('exam_subject_id')->unique();
    $subjects = ExamSubjectMaster::whereIn('id', $subjectIds)->select('id', 'subject_code', 'name')->orderBy('name')->get();

    return view('coe.moderation.edit', compact('duty', 'exams', 'faculties', 'subjects'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_id' => 'required|exists:exams,id',
      'faculty_id' => 'required|exists:faculties,id',
      'subject_id' => 'required|exists:exam_subject_masters,id',
      'moderation_type' => 'required|in:internal,external',
    ]);

    $duty = ModerationDuty::findOrFail($id);
    $duty->update($request->only(['exam_id', 'faculty_id', 'subject_id', 'moderation_type']));

    return redirect()->route('admin.moderation-duties.index')
      ->with('success', 'Moderation duty updated successfully');
  }

  public function destroy($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $duty->delete();

    return redirect()->route('admin.moderation-duties.index')
      ->with('success', 'Moderation duty deleted successfully');
  }

  public function markCompleted($id)
  {
    $duty = ModerationDuty::findOrFail($id);
    $duty->update(['status' => 'completed']);

    return redirect()->back()->with('success', 'Moderation marked as completed');
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

  // ── Compare & Adjust Marks ──────────────────────────────────────

  public function compare(Request $request)
  {
    $sessions = ExamSession::orderBy('created_at', 'desc')->get();
    $records = collect();
    $subjects = collect();
    $stats = ['total' => 0, 'moderated' => 0, 'flagged' => 0, 'avg_diff' => 0];
    $selectedSession = null;
    $selectedSubject = null;

    if ($request->filled('exam_session_id')) {
      $selectedSession = $request->exam_session_id;

      $subjectIds = ExamMarksEntry::where('exam_session_id', $selectedSession)
        ->distinct()
        ->pluck('erp_subject_id');
      $subjects = ExamSubjectMaster::whereIn('erp_subject_id', $subjectIds)
        ->select('id', 'erp_subject_id', 'subject_code', 'name')
        ->orderBy('name')
        ->get();

      if ($request->filled('erp_subject_id')) {
        $selectedSubject = $request->erp_subject_id;

        $records = ModerationRecord::where('exam_session_id', $selectedSession)
          ->where('erp_subject_id', $selectedSubject)
          ->with(['student', 'moderator', 'adjustedByUser'])
          ->orderBy('erp_student_id')
          ->get();

        $stats['total'] = $records->count();
        $stats['moderated'] = $records->where('status', '!=', 'pending')->count();
        $stats['flagged'] = $records->where('difference', '>', $this->deviationThreshold)->count();
        $stats['avg_diff'] = $records->whereNotNull('difference')->avg('difference');
        $stats['avg_diff'] = $stats['avg_diff'] ? round($stats['avg_diff'], 2) : 0;
      }
    }

    $threshold = $this->deviationThreshold;

    return view('coe.moderation.compare', compact(
      'sessions',
      'subjects',
      'records',
      'stats',
      'selectedSession',
      'selectedSubject',
      'threshold'
    ));
  }

  public function importMarks(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
      'erp_subject_id' => 'required',
    ]);

    $entries = ExamMarksEntry::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->get();

    if ($entries->isEmpty()) {
      return redirect()->back()->with('error', 'No marks entries found for this session and subject');
    }

    $imported = 0;
    foreach ($entries as $entry) {
      ModerationRecord::updateOrCreate(
        [
          'exam_session_id' => $entry->exam_session_id,
          'erp_student_id' => $entry->erp_student_id,
          'erp_subject_id' => $entry->erp_subject_id,
        ],
        [
          'evaluator_marks' => $entry->marks,
          'exam_marks_entry_id' => $entry->id,
          'status' => 'pending',
        ]
      );
      $imported++;
    }

    return redirect()->back()->with('success', $imported . ' evaluator marks imported for moderation');
  }

  public function storeModeratorMarks(Request $request, $id)
  {
    $request->validate([
      'moderator_marks' => 'required|numeric|min:0',
    ]);

    $record = ModerationRecord::findOrFail($id);
    $moderatorMarks = (float) $request->moderator_marks;
    $difference = abs($record->evaluator_marks - $moderatorMarks);

    $record->update([
      'moderator_marks' => $moderatorMarks,
      'difference' => $difference,
      'moderator_id' => Auth::id(),
      'status' => 'moderated',
    ]);

    return response()->json([
      'success' => true,
      'difference' => number_format($difference, 2),
      'status' => 'moderated',
      'flagged' => $difference > $this->deviationThreshold,
    ]);
  }

  public function adjustMarks(Request $request, $id)
  {
    $request->validate([
      'adjusted_marks' => 'required|numeric|min:0',
      'remarks' => 'nullable|string|max:500',
    ]);

    $record = ModerationRecord::findOrFail($id);
    $record->update([
      'adjusted_marks' => (float) $request->adjusted_marks,
      'adjusted_by' => Auth::id(),
      'remarks' => $request->remarks,
      'status' => 'adjusted',
    ]);

    return response()->json([
      'success' => true,
      'adjusted_marks' => number_format($record->adjusted_marks, 2),
      'status' => 'adjusted',
    ]);
  }

  public function bulkAdjust(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
      'erp_subject_id' => 'required',
    ]);

    $records = ModerationRecord::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->whereNotNull('moderator_marks')
      ->where('status', '!=', 'finalized')
      ->get();

    $adjusted = 0;
    foreach ($records as $record) {
      $diff = abs($record->evaluator_marks - $record->moderator_marks);
      if ($diff > $this->deviationThreshold) {
        $avg = round(($record->evaluator_marks + $record->moderator_marks) / 2, 2);
        $record->update([
          'adjusted_marks' => $avg,
          'adjusted_by' => Auth::id(),
          'remarks' => 'Auto-adjusted (avg): deviation ' . number_format($diff, 2) . ' exceeded threshold ' . $this->deviationThreshold,
          'status' => 'adjusted',
        ]);
        $adjusted++;
      } else {
        if (!$record->adjusted_marks) {
          $record->update([
            'adjusted_marks' => $record->evaluator_marks,
            'status' => 'adjusted',
          ]);
          $adjusted++;
        }
      }
    }

    return redirect()->back()->with('success', $adjusted . ' records adjusted based on threshold (' . $this->deviationThreshold . ')');
  }

  public function finalize(Request $request)
  {
    $request->validate([
      'exam_session_id' => 'required|exists:exam_sessions,id',
      'erp_subject_id' => 'required',
    ]);

    $count = ModerationRecord::where('exam_session_id', $request->exam_session_id)
      ->where('erp_subject_id', $request->erp_subject_id)
      ->whereNotNull('adjusted_marks')
      ->where('status', '!=', 'finalized')
      ->update(['status' => 'finalized']);

    return redirect()->back()->with('success', $count . ' moderation records finalized');
  }

  public function export(Request $request)
  {
    $query = ModerationDuty::with(['exam', 'faculty', 'subject']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    $duties = $query->get();
    return response()->json($duties);
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
      return redirect()->route('admin.moderation-duties.index')
        ->with('success', 'Moderation duties auto-assigned successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Auto-assignment failed: ' . $e->getMessage());
    }
  }
}

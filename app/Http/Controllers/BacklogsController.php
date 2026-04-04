<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamSession;
use App\Models\ExamSystem\ResultSubject;
use App\Models\ExamSystem\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BacklogsController extends Controller
{
  public function index(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam', 'examSession']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }
    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }
    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('enrollment_no', 'LIKE', "%{$search}%");
      });
    }

    $backlogs = $query->orderBy('created_at', 'desc')->paginate(50);
    $exams = Exam::orderBy('name')->get();
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();

    $totalBacklogs = Backlog::count();
    $pendingCount = Backlog::where('status', 'pending')->count();
    $registeredCount = Backlog::where('status', 'registered')->count();
    $clearedCount = Backlog::where('status', 'cleared')->count();

    return view('coe.backlogs.index', compact(
      'backlogs',
      'exams',
      'sessions',
      'totalBacklogs',
      'pendingCount',
      'registeredCount',
      'clearedCount'
    ));
  }

  public function failedSubjects(Request $request)
  {
    $query = ResultSubject::with(['result.student', 'result.examSession', 'subjectMaster'])
      ->where(function ($q) {
        $q->whereIn('grade', ['F', 'Ab'])
          ->orWhere('result_status', 'Absent');
      });

    if ($request->filled('exam_session_id')) {
      $query->whereHas('result', function ($q) use ($request) {
        $q->where('exam_session_id', $request->exam_session_id);
      });
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('result.student', function ($q) use ($search) {
        $q->where('enrollment_no', 'LIKE', "%{$search}%");
      });
    }

    $failedSubjects = $query->orderBy('created_at', 'desc')->paginate(50);
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();
    $exams = Exam::orderBy('name')->get();

    return view('coe.backlogs.failed_subjects', compact('failedSubjects', 'sessions', 'exams'));
  }

  public function registerBacklog(Request $request)
  {
    $request->validate([
      'result_subject_ids' => 'required|array|min:1',
      'result_subject_ids.*' => 'exists:result_subjects,id',
      'exam_id' => 'required|exists:exams,id',
      'exam_session_id' => 'nullable|exists:exam_sessions,id',
    ]);

    try {
      DB::beginTransaction();

      $registered = 0;

      foreach ($request->result_subject_ids as $rsId) {
        $rs = ResultSubject::with(['result.student'])->findOrFail($rsId);
        $examStudent = $rs->result->student;

        if (!$examStudent) continue;

        // Count previous attempts
        $previousAttempts = Backlog::where('exam_student_id', $examStudent->id)
          ->where('exam_subject_id', $rs->erp_subject_id)
          ->count();

        // Skip if already registered for same exam
        $exists = Backlog::where('exam_student_id', $examStudent->id)
          ->where('exam_subject_id', $rs->erp_subject_id)
          ->where('exam_id', $request->exam_id)
          ->exists();

        if ($exists) continue;

        Backlog::create([
          'exam_student_id' => $examStudent->id,
          'exam_subject_id' => $rs->erp_subject_id,
          'exam_id' => $request->exam_id,
          'exam_session_id' => $request->exam_session_id,
          'status' => 'registered',
          'attempt_number' => $previousAttempts + 1,
          'previous_marks' => $rs->total_marks,
          'previous_grade' => $rs->grade,
          'registered_at' => now(),
        ]);

        $registered++;
      }

      DB::commit();

      return redirect()->route('coe.backlogs.index')
        ->with('success', "Successfully registered {$registered} backlog(s).");
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Registration failed: ' . $e->getMessage());
    }
  }

  public function show($id)
  {
    $backlog = Backlog::with(['student', 'subject', 'exam', 'examSession'])->findOrFail($id);

    // Attempt history
    $attemptHistory = Backlog::with(['exam', 'examSession'])
      ->where('exam_student_id', $backlog->exam_student_id)
      ->where('exam_subject_id', $backlog->exam_subject_id)
      ->orderBy('attempt_number')
      ->get();

    return view('coe.backlogs.show', compact('backlog', 'attemptHistory'));
  }

  public function markCleared(Request $request, $id)
  {
    $backlog = Backlog::findOrFail($id);

    $backlog->update([
      'status' => 'cleared',
      'cleared_at' => now(),
      'remarks' => $request->input('remarks'),
    ]);

    return redirect()->route('coe.backlogs.show', $id)
      ->with('success', 'Backlog marked as cleared.');
  }

  public function destroy($id)
  {
    $backlog = Backlog::findOrFail($id);

    if ($backlog->status === 'cleared') {
      return redirect()->back()->with('error', 'Cannot delete cleared backlogs.');
    }

    $backlog->delete();

    return redirect()->route('coe.backlogs.index')
      ->with('success', 'Backlog record deleted successfully.');
  }

  public function report(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam', 'examSession']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }
    if ($request->filled('exam_session_id')) {
      $query->where('exam_session_id', $request->exam_session_id);
    }

    $backlogs = $query->get();
    $exams = Exam::orderBy('name')->get();
    $sessions = ExamSession::orderBy('academic_year', 'desc')->orderBy('semester')->get();

    $totalBacklogs = $backlogs->count();
    $pendingBacklogs = $backlogs->where('status', 'pending')->count();
    $registeredBacklogs = $backlogs->where('status', 'registered')->count();
    $clearedBacklogs = $backlogs->where('status', 'cleared')->count();

    $studentBacklogs = $backlogs->groupBy('exam_student_id')->map(function ($group) {
      $student = $group->first()->student;
      return [
        'student' => $student,
        'total' => $group->count(),
        'pending' => $group->where('status', 'pending')->count(),
        'registered' => $group->where('status', 'registered')->count(),
        'cleared' => $group->where('status', 'cleared')->count(),
        'max_attempt' => $group->max('attempt_number'),
        'subjects' => $group,
      ];
    })->sortByDesc('total');

    $subjectBacklogs = $backlogs->groupBy('exam_subject_id')->map(function ($group) {
      $subject = $group->first()->subject;
      return [
        'subject' => $subject,
        'total' => $group->count(),
        'pending' => $group->where('status', 'pending')->count(),
        'cleared' => $group->where('status', 'cleared')->count(),
      ];
    })->sortByDesc('total');

    return view('coe.backlogs.report', compact(
      'backlogs',
      'exams',
      'sessions',
      'totalBacklogs',
      'pendingBacklogs',
      'registeredBacklogs',
      'clearedBacklogs',
      'studentBacklogs',
      'subjectBacklogs'
    ));
  }

  public function export(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam', 'examSession']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $backlogs = $query->get();
    return response()->json($backlogs);
  }
}

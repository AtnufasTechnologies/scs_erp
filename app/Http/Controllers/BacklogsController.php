<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Backlog;
use App\Models\ExamSystem\Exam;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BacklogsController extends Controller
{
  /**
   * Display a listing of all backlogs
   */
  public function index(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam']);

    // Filter by exam
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    // Filter by status
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    // Search by student name or reg no
    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('student', function ($q) use ($search) {
        $q->where('full_name', 'LIKE', "%{$search}%")
          ->orWhere('register_no', 'LIKE', "%{$search}%");
      });
    }

    $backlogs = $query->orderBy('created_at', 'desc')->paginate(20);
    $exams = Exam::orderBy('year', 'desc')->get();

    return view('coe.backlogs.index', compact('backlogs', 'exams'));
  }

  /**
   * Display backlog report with statistics
   */
  public function report(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam']);

    // Filter by exam
    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    $backlogs = $query->get();
    $exams = Exam::orderBy('year', 'desc')->get();

    // Statistics
    $totalBacklogs = $backlogs->count();
    $pendingBacklogs = $backlogs->where('status', 'pending')->count();
    $clearedBacklogs = $backlogs->where('status', 'cleared')->count();

    // Group by student
    $studentBacklogs = $backlogs->groupBy('exam_student_id')->map(function ($group) {
      $student = $group->first()->student;
      return [
        'student' => $student,
        'total' => $group->count(),
        'pending' => $group->where('status', 'pending')->count(),
        'cleared' => $group->where('status', 'cleared')->count(),
        'subjects' => $group->pluck('subject')
      ];
    });

    // Group by subject
    $subjectBacklogs = $backlogs->groupBy('exam_subject_id')->map(function ($group) {
      $subject = $group->first()->subject;
      return [
        'subject' => $subject,
        'total' => $group->count(),
        'pending' => $group->where('status', 'pending')->count(),
        'cleared' => $group->where('status', 'cleared')->count()
      ];
    })->sortByDesc('total');

    return view('coe.backlogs.report', compact(
      'backlogs',
      'exams',
      'totalBacklogs',
      'pendingBacklogs',
      'clearedBacklogs',
      'studentBacklogs',
      'subjectBacklogs'
    ));
  }

  /**
   * Show the form for creating a new backlog
   */
  public function create()
  {
    $exams = Exam::orderBy('year', 'desc')->get();
    $students = StudentMaster::orderBy('full_name')->get();

    return view('coe.backlogs.create', compact('exams', 'students'));
  }

  /**
   * Store a newly created backlog
   */
  public function store(Request $request)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'exam_subject_id' => 'required',
      'exam_id' => 'required|exists:exams,id',
      'status' => 'required|in:pending,cleared'
    ]);

    Backlog::create($request->all());

    return redirect()->route('coe.backlogs.index')
      ->with('success', 'Backlog record created successfully');
  }

  /**
   * Display the specified backlog
   */
  public function show($id)
  {
    $backlog = Backlog::with(['student', 'subject', 'exam'])->findOrFail($id);

    return view('coe.backlogs.show', compact('backlog'));
  }

  /**
   * Show the form for editing the specified backlog
   */
  public function edit($id)
  {
    $backlog = Backlog::findOrFail($id);
    $exams = Exam::orderBy('year', 'desc')->get();
    $students = StudentMaster::orderBy('full_name')->get();

    return view('coe.backlogs.edit', compact('backlog', 'exams', 'students'));
  }

  /**
   * Update the specified backlog
   */
  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'exam_subject_id' => 'required',
      'exam_id' => 'required|exists:exams,id',
      'status' => 'required|in:pending,cleared'
    ]);

    $backlog = Backlog::findOrFail($id);
    $backlog->update($request->all());

    return redirect()->route('coe.backlogs.index')
      ->with('success', 'Backlog record updated successfully');
  }

  /**
   * Remove the specified backlog
   */
  public function destroy($id)
  {
    $backlog = Backlog::findOrFail($id);
    $backlog->delete();

    return redirect()->route('coe.backlogs.index')
      ->with('success', 'Backlog record deleted successfully');
  }

  /**
   * Export backlogs data
   */
  public function export(Request $request)
  {
    $query = Backlog::with(['student', 'subject', 'exam']);

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

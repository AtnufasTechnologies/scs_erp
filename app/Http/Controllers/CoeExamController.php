<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Program;
use App\Models\ExamSystem\ProgramRegulation;
use App\Models\ExamSystem\ExamAttendance;
use Illuminate\Support\Facades\DB;

class CoeExamController extends Controller
{
  /**
   * Display a listing of exams
   */
  public function index(Request $request)
  {
    $query = Exam::with(['program', 'regulation']);

    // Apply filters
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('exam_type')) {
      $query->where('exam_type', $request->exam_type);
    }

    if ($request->filled('program_id')) {
      $query->where('program_id', $request->program_id);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('exam_type', 'like', "%{$search}%");
      });
    }

    $exams = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

    // Calculate statistics
    $totalExams = Exam::count();
    $upcomingExams = Exam::where('status', 'upcoming')->count();
    $ongoingExams = Exam::where('status', 'ongoing')->count();
    $completedExams = Exam::where('status', 'completed')->count();

    // Load programs for filter dropdown
    $programs = Program::orderBy('name')->get();

    return view('coe.exams.index', compact(
      'exams',
      'totalExams',
      'upcomingExams',
      'ongoingExams',
      'completedExams',
      'programs'
    ));
  }

  /**
   * Show the form for creating a new exam
   */
  public function create()
  {
    $programs = Program::orderBy('name')->get();
    $regulations = ProgramRegulation::orderBy('regulation_name')->get();

    return view('coe.exams.create', compact('programs', 'regulations'));
  }

  /**
   * Store a newly created exam
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'exam_type' => 'required|string|in:Regular,Backlog,Improvement,Special',
      'semester' => 'required|in:Odd,Even',
      'program_id' => 'required|exists:programs,id',
      'regulation_id' => 'required|exists:program_regulations,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'exam_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
      'status' => 'required|in:upcoming,ongoing,completed,cancelled'
    ]);

    try {
      $exam = Exam::create($validated);

      return redirect()
        ->route('coe.exams.show', $exam->id)
        ->with('success', 'Exam created successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to create exam: ' . $e->getMessage());
    }
  }

  /**
   * Display the specified exam
   */
  public function show($id)
  {
    $exam = Exam::with(['program', 'regulation', 'registrations'])->findOrFail($id);

    // Calculate attendance statistics
    $attendanceStats = [
      'present' => ExamAttendance::where('exam_id', $id)
        ->where('status', 'present')
        ->count(),
      'absent' => ExamAttendance::where('exam_id', $id)
        ->where('status', 'absent')
        ->count(),
      'total' => ExamAttendance::where('exam_id', $id)->count(),
    ];

    // Calculate percentage
    if ($attendanceStats['total'] > 0) {
      $attendanceStats['percentage'] = round(
        ($attendanceStats['present'] / $attendanceStats['total']) * 100,
        2
      );
    } else {
      $attendanceStats['percentage'] = 0;
    }

    // Duty statistics (placeholder - adjust based on your duty tables)
    $dutyStats = [
      'invigilation' => 0,
      'evaluation' => 0,
      'moderation' => 0,
    ];

    return view('coe.exams.show', compact('exam', 'attendanceStats', 'dutyStats'));
  }

  /**
   * Show the form for editing the specified exam
   */
  public function edit($id)
  {
    $exam = Exam::findOrFail($id);
    $programs = Program::orderBy('name')->get();
    $regulations = ProgramRegulation::orderBy('regulation_name')->get();

    return view('coe.exams.edit', compact('exam', 'programs', 'regulations'));
  }

  /**
   * Update the specified exam
   */
  public function update(Request $request, $id)
  {
    $exam = Exam::findOrFail($id);

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'exam_type' => 'required|string|in:Regular,Backlog,Improvement,Special',
      'semester' => 'required|in:Odd,Even',
      'program_id' => 'required|exists:programs,id',
      'regulation_id' => 'required|exists:program_regulations,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'exam_date' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
      'status' => 'required|in:upcoming,ongoing,completed,cancelled'
    ]);

    try {
      $exam->update($validated);

      return redirect()
        ->route('coe.exams.show', $exam->id)
        ->with('success', 'Exam updated successfully!');
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'Failed to update exam: ' . $e->getMessage());
    }
  }

  /**
   * Remove the specified exam
   */
  public function destroy($id)
  {
    try {
      $exam = Exam::findOrFail($id);

      // Check if there are any attendance records
      $attendanceCount = ExamAttendance::where('exam_id', $id)->count();

      if ($attendanceCount > 0) {
        return redirect()
          ->back()
          ->with('error', 'Cannot delete exam with existing attendance records. Please delete attendance records first.');
      }

      $examName = $exam->name;
      $exam->delete();

      return redirect()
        ->route('coe.exams.index')
        ->with('success', "Exam '{$examName}' deleted successfully!");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete exam: ' . $e->getMessage());
    }
  }
}

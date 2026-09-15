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
  private function resolveAssessmentModule(?string $module): ?string
  {
    if (!$module) {
      return null;
    }

    $normalized = strtoupper(str_replace('-', '', trim($module)));
    if ($normalized === 'FA2') {
      return 'FA2';
    }

    if ($normalized === 'SA') {
      return 'SA';
    }

    return null;
  }

  /**
   * Display a listing of exams
   */
  public function index(Request $request)
  {
    $module = $this->resolveAssessmentModule($request->input('module'));
    $query = Exam::with(['program', 'regulation']);

    if ($module) {
      $query->where('assessment_type', $module);
    }

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
    $statsQuery = Exam::query();
    if ($module) {
      $statsQuery->where('assessment_type', $module);
    }

    $totalExams = (clone $statsQuery)->count();
    $upcomingExams = (clone $statsQuery)->where('status', 'upcoming')->count();
    $ongoingExams = (clone $statsQuery)->where('status', 'ongoing')->count();
    $completedExams = (clone $statsQuery)->where('status', 'completed')->count();

    // Load programs for filter dropdown
    $programs = Program::orderBy('name')->get();

    return view('coe.exams.index', compact(
      'exams',
      'totalExams',
      'upcomingExams',
      'ongoingExams',
      'completedExams',
      'programs',
      'module'
    ));
  }

  /**
   * Show the form for creating a new exam
   */
  public function create()
  {
    $module = $this->resolveAssessmentModule(request()->input('module')) ?? 'SA';
    $programs = Program::orderBy('name')->get();
    $regulations = ProgramRegulation::orderBy('regulation_name')->get();

    return view('coe.exams.create', compact('programs', 'regulations', 'module'));
  }

  /**
   * Store a newly created exam
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'assessment_type' => 'required|in:SA,FA2',
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
        ->route('coe.exams.show', ['id' => $exam->id, 'module' => $exam->assessment_type])
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
    $module = $this->resolveAssessmentModule(request()->input('module'));
    $exam = Exam::with(['program', 'regulation', 'registrations'])->findOrFail($id);

    if (!$module) {
      $module = $exam->assessment_type ?? 'SA';
    }

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

    return view('coe.exams.show', compact('exam', 'attendanceStats', 'dutyStats', 'module'));
  }

  /**
   * Show the form for editing the specified exam
   */
  public function edit($id)
  {
    $module = $this->resolveAssessmentModule(request()->input('module'));
    $exam = Exam::findOrFail($id);
    if (!$module) {
      $module = $exam->assessment_type ?? 'SA';
    }
    $programs = Program::orderBy('name')->get();
    $regulations = ProgramRegulation::orderBy('regulation_name')->get();

    return view('coe.exams.edit', compact('exam', 'programs', 'regulations', 'module'));
  }

  /**
   * Update the specified exam
   */
  public function update(Request $request, $id)
  {
    $exam = Exam::findOrFail($id);

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'assessment_type' => 'required|in:SA,FA2',
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
        ->route('coe.exams.show', ['id' => $exam->id, 'module' => $validated['assessment_type']])
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
        ->route('coe.exams.index', ['module' => $exam->assessment_type ?? 'SA'])
        ->with('success', "Exam '{$examName}' deleted successfully!");
    } catch (\Exception $e) {
      return redirect()
        ->back()
        ->with('error', 'Failed to delete exam: ' . $e->getMessage());
    }
  }
}

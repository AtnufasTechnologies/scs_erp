<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSystem\Student;
use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\EvaluationDuty;
use App\Models\ExamSystem\ModerationDuty;
use App\Models\ExamSystem\Backlog;
use App\Models\FacultyRemuneration;
use App\Models\ExamSystem\InvigilationDuty;
use Illuminate\Support\Facades\DB;

class CoeDashboardController extends Controller
{
  public function index()
  {
    $totalStudents = Student::count();
    $todayExams = Exam::whereDate('exam_date', today())->count();
    $attendancePercent = ExamAttendance::where('status', 'present')->count()
      / max(1, ExamAttendance::count()) * 100;
    $pendingEvaluations = EvaluationDuty::where('status', 'pending')->count();
    $pendingModerations = ModerationDuty::where('status', 'pending')->count();
    $backlogs = Backlog::where('status', 'pending')->count();
    $pendingPayments = FacultyRemuneration::where('status', 'pending')->sum('total_amount');
    $todayInvigilation = InvigilationDuty::whereDate('date', today())->get();
    $runningExams = Exam::where('status', 'ongoing')->get();
    $exams = Exam::all();
    $sessions = DB::table('exam_sessions')->get();

    $attendanceSummary = collect(); // TODO: Replace with real summary data
    $invigilationDuties = collect(); // TODO: Replace with real duties data
    $evaluationStatus = collect(); // TODO: Replace with real evaluation data
    $attendancePresent = 0;
    $attendanceAbsent = 0;
    $evaluationLabels = [];
    $evaluationData = [];

    return view('coe.dashboard', compact(
      'totalStudents',
      'todayExams',
      'attendancePercent',
      'pendingEvaluations',
      'pendingModerations',
      'backlogs',
      'pendingPayments',
      'todayInvigilation',
      'runningExams',
      'exams',
      'sessions',
      'attendanceSummary',
      'invigilationDuties',
      'evaluationStatus',
      'attendancePresent',
      'attendanceAbsent',
      'evaluationLabels',
      'evaluationData'
    ));
  }
  /**
   * AJAX filter handler for COE Dashboard
   */
  public function filter(Request $request)
  {
    // Example: filter by exam date or status
    $examDate = $request->input('exam_date');
    $status = $request->input('status');

    $query = \App\Models\ExamSystem\Exam::query();
    if ($examDate) {
      $query->whereDate('exam_date', $examDate);
    }
    if ($status) {
      $query->where('status', $status);
    }
    $filteredExams = $query->get();

    // Render a partial Blade view for the filtered table (create if missing)
    $html = view('coe.partials.exam_table', compact('filteredExams'))->render();

    return response()->json([
      'html' => $html,
      'count' => $filteredExams->count(),
    ]);
  }
}

<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\Registration;
use App\Models\ExamSystem\Mark;
use App\Models\ExamSystem\Result;
use App\Models\ExamSystem\Backlog;
use App\Models\StudentMaster;
use Barryvdh\DomPDF\Facade\Pdf as Pdf;
use Illuminate\Http\Request;


class ExamReportsController extends Controller
{
  public function index()
  {
    return view('coe.reports.index');
  }

  public function registrationReport(Request $request)
  {
    $query = Registration::with(['student', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $registrations = $query->get();
    $exams = Exam::all();

    return view('coe.reports.registration', compact('registrations', 'exams'));
  }

  public function attendanceReport(Request $request)
  {
    // Attendance report logic
    return view('coe.reports.attendance');
  }

  public function marksReport(Request $request)
  {
    $query = Mark::with(['student', 'exam', 'subject']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $marks = $query->get();
    $exams = Exam::all();

    return view('coe.reports.marks', compact('marks', 'exams'));
  }

  public function resultsReport(Request $request)
  {
    $query = Result::with(['student', 'exam']);

    if ($request->has('exam_id') && $request->exam_id != '') {
      $query->where('exam_id', $request->exam_id);
    }

    $results = $query->get();
    $exams = Exam::all();

    return view('coe.reports.results', compact('results', 'exams'));
  }

  public function backlogReport(Request $request)
  {
    $query = Backlog::with(['student', 'subject']);

    if ($request->has('semester') && $request->semester != '') {
      $query->where('semester', $request->semester);
    }

    $backlogs = $query->where('status', 'pending')->get();

    return view('coe.reports.backlog', compact('backlogs'));
  }

  public function remunerationReport(Request $request)
  {
    // Remuneration report logic
    return view('coe.reports.remuneration');
  }

  public function dutyReport(Request $request)
  {
    // Duty allocation report logic
    return view('coe.reports.duty');
  }

  public function studentProgressReport($studentId)
  {
    $student = StudentMaster::with([
      'registrations',
      'marks',
      'results',
      'backlogs',
      'credits'
    ])->findOrFail($studentId);

    return view('coe.reports.student-progress', compact('student'));
  }

  public function exportPdf(Request $request)
  {
    $reportType = $request->input('report_type');

    // Generate PDF based on report type
    $pdf = Pdf::loadView('coe.reports.pdf.' . $reportType, []);

    return $pdf->download($reportType . '-report.pdf');
  }

  public function exportExcel(Request $request)
  {
    $reportType = $request->input('report_type');

    // Export to Excel logic
    return redirect()->back()->with('info', 'Excel export coming soon');
  }

  public function dashboard()
  {
    $stats = [
      'total_exams' => Exam::count(),
      'total_registrations' => Registration::count(),
      'pending_registrations' => Registration::where('status', 'pending')->count(),
      'total_students' => StudentMaster::where('is_deleted', 0)->where('is_left', 0)->count(),
      'pending_backlogs' => Backlog::where('status', 'pending')->count(),
    ];

    return view('coe.reports.dashboard', compact('stats'));
  }
}

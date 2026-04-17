<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Exam;
use App\Models\ExamSystem\ExamAttendance;
use App\Models\ExamSystem\Result;
use App\Models\FacultyRemuneration;
use App\Models\ExamSystem\FacultyProfile;
use App\Models\AcademicDepartment;
use App\Models\ExamSystem\Registration;
use App\Models\ExamSystem\Backlog;
use App\Models\StudentMaster;
use App\Exports\GenericExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamReportsController extends Controller
{
  public function index()
  {
    $stats = [
      'total_exams' => Exam::count(),
      'total_registrations' => Registration::count(),
      'total_students' => StudentMaster::where('is_deleted', 0)->where('is_left', 0)->count(),
      'pending_backlogs' => Backlog::where('status', 'pending')->count(),
    ];

    return view('coe.reports.index', compact('stats'));
  }

  // ─── Attendance Report ───────────────────────────────────────

  public function attendanceReport(Request $request)
  {
    $query = ExamAttendance::with(['student', 'exam', 'subject']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('department')) {
      $query->whereHas('student', function ($q) use ($request) {
        $q->where('department', $request->department);
      });
    }

    if ($request->filled('date')) {
      $query->whereDate('marked_at', $request->date);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $attendances = $query->orderBy('created_at', 'desc')->paginate(50);

    // Summary
    $totalQuery = ExamAttendance::query();
    if ($request->filled('exam_id')) $totalQuery->where('exam_id', $request->exam_id);
    if ($request->filled('department')) {
      $totalQuery->whereHas('student', fn($q) => $q->where('department', $request->department));
    }
    if ($request->filled('date')) $totalQuery->whereDate('marked_at', $request->date);

    $totalCount = $totalQuery->count();
    $presentCount = (clone $totalQuery)->present()->count();
    $absentCount = (clone $totalQuery)->absent()->count();
    $malpracticeCount = (clone $totalQuery)->malpractice()->count();

    $exams = Exam::orderBy('name')->get();
    $departments = AcademicDepartment::where('isActive', 1)->orderBy('name')->get();

    return view('coe.reports.attendance', compact(
      'attendances',
      'exams',
      'departments',
      'totalCount',
      'presentCount',
      'absentCount',
      'malpracticeCount'
    ));
  }

  // ─── Results Report ──────────────────────────────────────────

  public function resultsReport(Request $request)
  {
    $query = Result::with(['student', 'exam', 'examSession']);

    if ($request->filled('exam_id')) {
      $query->where('exam_id', $request->exam_id);
    }

    if ($request->filled('department')) {
      $query->whereHas('student', function ($q) use ($request) {
        $q->whereHas('studentMaster', function ($sq) use ($request) {
          $sq->where('department', $request->department);
        });
      });
    }

    if ($request->filled('date')) {
      $query->whereDate('published_at', $request->date);
    }

    if ($request->filled('result_status')) {
      if ($request->result_status === 'published') {
        $query->published();
      } elseif ($request->result_status === 'unpublished') {
        $query->unpublished();
      }
    }

    $results = $query->orderBy('created_at', 'desc')->paginate(50);

    // Summary
    $summaryQuery = Result::query();
    if ($request->filled('exam_id')) $summaryQuery->where('exam_id', $request->exam_id);

    $totalResults = $summaryQuery->count();
    $publishedCount = (clone $summaryQuery)->published()->count();
    $passCount = (clone $summaryQuery)->where('result_status', 'pass')->count();
    $failCount = (clone $summaryQuery)->where('result_status', 'fail')->count();
    $avgSgpa = (clone $summaryQuery)->where('sgpa', '>', 0)->avg('sgpa');

    $exams = Exam::orderBy('name')->get();
    $departments = AcademicDepartment::where('isActive', 1)->orderBy('name')->get();

    return view('coe.reports.results', compact(
      'results',
      'exams',
      'departments',
      'totalResults',
      'publishedCount',
      'passCount',
      'failCount',
      'avgSgpa'
    ));
  }

  // ─── Faculty Payment Report ──────────────────────────────────

  public function remunerationReport(Request $request)
  {
    $query = FacultyRemuneration::with('faculty');

    if ($request->filled('faculty_id')) {
      $query->where('faculty_id', $request->faculty_id);
    }

    if ($request->filled('department')) {
      $query->whereHas('faculty', function ($q) use ($request) {
        $q->where('department', $request->department);
      });
    }

    if ($request->filled('duty_type')) {
      $query->where('duty_type', $request->duty_type);
    }

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('date_from')) {
      $query->whereDate('generated_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
      $query->whereDate('generated_at', '<=', $request->date_to);
    }

    $remunerations = $query->orderBy('created_at', 'desc')->paginate(50);

    // Faculty-wise summary
    $summaryQuery = FacultyRemuneration::query();
    if ($request->filled('department')) {
      $summaryQuery->whereHas('faculty', fn($q) => $q->where('department', $request->department));
    }
    if ($request->filled('date_from')) $summaryQuery->whereDate('generated_at', '>=', $request->date_from);
    if ($request->filled('date_to')) $summaryQuery->whereDate('generated_at', '<=', $request->date_to);

    $totalAmount = $summaryQuery->sum('total_amount');
    $pendingAmount = (clone $summaryQuery)->pending()->sum('total_amount');
    $approvedAmount = (clone $summaryQuery)->approved()->sum('total_amount');
    $paidAmount = (clone $summaryQuery)->paid()->sum('total_amount');
    $facultyCount = (clone $summaryQuery)->distinct('faculty_id')->count('faculty_id');

    $faculties = FacultyProfile::orderBy('name')->get();
    $departments = AcademicDepartment::where('isActive', 1)->orderBy('name')->get();

    return view('coe.reports.remuneration', compact(
      'remunerations',
      'faculties',
      'departments',
      'totalAmount',
      'pendingAmount',
      'approvedAmount',
      'paidAmount',
      'facultyCount'
    ));
  }

  // ─── Exports ─────────────────────────────────────────────────

  public function exportPdf(Request $request)
  {
    $reportType = $request->input('report_type');
    $data = $this->getExportData($request, $reportType);

    $pdf = Pdf::loadView('coe.reports.pdf.' . $reportType, $data)
      ->setPaper('a4', 'landscape');

    return $pdf->download($reportType . '-report.pdf');
  }

  public function exportExcel(Request $request)
  {
    $reportType = $request->input('report_type');
    $data = $this->getExportData($request, $reportType);

    $export = new GenericExport($data, 'coe.reports.excel.' . $reportType);

    return Excel::download($export, $reportType . '-report.xlsx');
  }

  private function getExportData(Request $request, $type)
  {
    switch ($type) {
      case 'attendance':
        $query = ExamAttendance::with(['student', 'exam', 'subject']);
        if ($request->filled('exam_id')) $query->where('exam_id', $request->exam_id);
        if ($request->filled('department')) {
          $query->whereHas('student', fn($q) => $q->where('department', $request->department));
        }
        if ($request->filled('date')) $query->whereDate('marked_at', $request->date);
        return ['records' => $query->get(), 'title' => 'Attendance Report'];

      case 'results':
        $query = Result::with(['student', 'exam', 'examSession']);
        if ($request->filled('exam_id')) $query->where('exam_id', $request->exam_id);
        if ($request->filled('department')) {
          $query->whereHas('student', fn($q) => $q->whereHas('studentMaster', fn($sq) => $sq->where('department', $request->department)));
        }
        if ($request->filled('date')) $query->whereDate('published_at', $request->date);
        return ['records' => $query->get(), 'title' => 'Results Report'];

      case 'remuneration':
        $query = FacultyRemuneration::with('faculty');
        if ($request->filled('department')) {
          $query->whereHas('faculty', fn($q) => $q->where('department', $request->department));
        }
        if ($request->filled('date_from')) $query->whereDate('generated_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('generated_at', '<=', $request->date_to);
        return ['records' => $query->get(), 'title' => 'Faculty Payment Report'];

      default:
        return ['records' => collect(), 'title' => 'Report'];
    }
  }

  // ─── Other reports kept for sidebar compatibility ────────────

  public function dashboard()
  {
    $stats = [
      'total_exams' => Exam::count(),
      'total_registrations' => Registration::count(),
      'pending_registrations' => Registration::where('status', 'pending')->count(),
      'total_students' => StudentMaster::where('is_deleted', 0)->where('is_left', 0)->count(),
      'pending_backlogs' => Backlog::where('status', 'pending')->count(),
    ];

    return view('coe.reports.index', compact('stats'));
  }

  public function registrationReport(Request $request)
  {
    return redirect()->route('admin.exam-reports.index');
  }

  public function marksReport(Request $request)
  {
    return redirect()->route('admin.exam-reports.index');
  }

  public function backlogReport(Request $request)
  {
    return redirect()->route('admin.exam-reports.index');
  }

  public function dutyReport(Request $request)
  {
    return redirect()->route('admin.exam-reports.index');
  }

  public function studentProgressReport(Request $request)
  {
    return redirect()->route('admin.exam-reports.index');
  }
}

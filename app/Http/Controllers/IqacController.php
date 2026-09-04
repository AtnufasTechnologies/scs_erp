<?php

namespace App\Http\Controllers;

use App\Models\DepartmentActivity;
use App\Models\DsaStudentCouncilDocument;
use App\Models\EcEvent;
use App\Models\EcEventIqacReport;
use App\Models\InternationalOfficeEvent;
use App\Models\InternationalOfficeEventIqacReport;
use App\Models\Subject;
use App\Repositories\Dean\EventAnalyticsRepository;
use App\Services\Dean\DeanReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IqacController extends Controller
{
  public function dashboard()
  {
    $hasApprovalStatus = Schema::hasColumn('international_office_event_iqac_reports', 'approval_status');
    $hasDeptIqacStatus = Schema::hasColumn('department_activities', 'iqac_approval_status');

    $eventCount = InternationalOfficeEvent::count();
    $reportCount = InternationalOfficeEventIqacReport::count();
    $pendingCount = $hasApprovalStatus
      ? InternationalOfficeEventIqacReport::where('approval_status', 'pending')->count()
      : $reportCount;
    $approvedCount = $hasApprovalStatus
      ? InternationalOfficeEventIqacReport::where('approval_status', 'approved')->count()
      : 0;
    $rejectedCount = $hasApprovalStatus
      ? InternationalOfficeEventIqacReport::where('approval_status', 'rejected')->count()
      : 0;

    $recentPendingReportsQuery = InternationalOfficeEventIqacReport::with([
      'event:id,activity_type_master_id,visiting_institution_name,trip_start_date,trip_end_date',
      'event.activityType:id,title',
    ])
      ->orderByDesc('submitted_on')
      ->orderByDesc('id')
      ->limit(12);

    if ($hasApprovalStatus) {
      $recentPendingReportsQuery->where('approval_status', 'pending');
    }

    $recentPendingReports = $recentPendingReportsQuery->get();

    $departmentActivityCount = DepartmentActivity::count();
    $departmentPendingCount = $hasDeptIqacStatus
      ? DepartmentActivity::where('iqac_approval_status', 'pending')->count()
      : $departmentActivityCount;

    $recentDepartmentPendingActivitiesQuery = DepartmentActivity::with('subject:id,title,code')
      ->orderByDesc('activity_date')
      ->orderByDesc('id')
      ->limit(8);

    if ($hasDeptIqacStatus) {
      $recentDepartmentPendingActivitiesQuery->where('iqac_approval_status', 'pending');
    }

    $recentDepartmentPendingActivities = $recentDepartmentPendingActivitiesQuery->get();

    return view('iqac.dashboard', compact(
      'eventCount',
      'reportCount',
      'pendingCount',
      'approvedCount',
      'rejectedCount',
      'recentPendingReports',
      'departmentActivityCount',
      'departmentPendingCount',
      'recentDepartmentPendingActivities'
    ));
  }

  public function internationalOfficeReports(Request $request)
  {
    $hasApprovalStatus = Schema::hasColumn('international_office_event_iqac_reports', 'approval_status');

    $query = InternationalOfficeEventIqacReport::with([
      'event:id,activity_type_master_id,visiting_institution_name,trip_start_date,trip_end_date,department_scope,nature_of_activity,approval_type',
      'event.activityType:id,title',
    ]);

    if ($hasApprovalStatus && $request->filled('status')) {
      $query->where('approval_status', $request->status);
    }

    if ($request->filled('submitted_on')) {
      $query->whereDate('submitted_on', $request->submitted_on);
    }

    $reports = $query
      ->orderByDesc('submitted_on')
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    $events = InternationalOfficeEvent::with([
      'activityType:id,title',
      'iqacReports' => function ($q) {
        $q->orderByDesc('submitted_on')->orderByDesc('id');
      },
    ])
      ->orderByDesc('trip_start_date')
      ->orderByDesc('id')
      ->paginate(12, ['*'], 'events_page')
      ->withQueryString();

    if (!$hasApprovalStatus) {
      $reports->getCollection()->transform(function ($report) {
        $report->approval_status = 'pending';
        return $report;
      });

      $events->getCollection()->transform(function ($event) {
        $event->iqacReports->transform(function ($report) {
          $report->approval_status = 'pending';
          return $report;
        });

        return $event;
      });
    }

    return view('iqac.international-office-reports', compact('reports', 'events'));
  }

  public function updateInternationalOfficeReportStatus(Request $request, $reportId)
  {
    if (!Schema::hasColumn('international_office_event_iqac_reports', 'approval_status')) {
      return redirect()->back()->with('error', 'IQAC approval columns are not available yet. Please run migrations first.');
    }

    $report = InternationalOfficeEventIqacReport::findOrFail((int) $reportId);

    $validated = $request->validate([
      'approval_status' => 'required|in:approved,rejected,pending',
      'review_remarks' => 'nullable|string|max:2000|required_if:approval_status,rejected',
    ]);

    $report->update([
      'approval_status' => $validated['approval_status'],
      'review_remarks' => $validated['review_remarks'] ?? null,
      'reviewed_by_user_id' => auth()->id(),
      'reviewed_at' => now(),
    ]);

    return redirect()->back()->with('success', 'IQAC review status updated successfully.');
  }

  public function departmentalActivities(Request $request)
  {
    $hasDeptIqacStatus = Schema::hasColumn('department_activities', 'iqac_approval_status');

    $subjectOptions = Subject::orderBy('title')->get(['id', 'title', 'code']);

    $query = DepartmentActivity::with('subject:id,title,code');

    if ($request->filled('subject_id')) {
      $query->where('subject_id', (int) $request->subject_id);
    }

    if ($request->filled('activity_type')) {
      $query->where('activity_type', $request->activity_type);
    }

    if ($request->filled('activity_date')) {
      $query->whereDate('activity_date', $request->activity_date);
    }

    if ($hasDeptIqacStatus && $request->filled('iqac_status')) {
      $query->where('iqac_approval_status', $request->iqac_status);
    }

    $activities = $query
      ->orderByDesc('activity_date')
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    if (!$hasDeptIqacStatus) {
      $activities->getCollection()->transform(function ($activity) {
        $activity->iqac_approval_status = 'pending';
        return $activity;
      });
    }

    return view('iqac.departmental-activities', compact('activities', 'subjectOptions'));
  }

  public function updateDepartmentalActivityStatus(Request $request, $activityId)
  {
    if (!Schema::hasColumn('department_activities', 'iqac_approval_status')) {
      return redirect()->back()->with('error', 'IQAC activity review columns are not available yet. Please run migrations first.');
    }

    $activity = DepartmentActivity::findOrFail((int) $activityId);

    $validated = $request->validate([
      'iqac_approval_status' => 'required|in:approved,rejected,pending',
      'iqac_review_remarks' => 'nullable|string|max:2000|required_if:iqac_approval_status,rejected',
    ]);

    $activity->update([
      'iqac_approval_status' => $validated['iqac_approval_status'],
      'iqac_review_remarks' => $validated['iqac_review_remarks'] ?? null,
      'iqac_reviewed_by_user_id' => auth()->id(),
      'iqac_reviewed_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Departmental activity review updated successfully.');
  }

  public function deanStudentAffairs(Request $request, DeanReportsService $deanReportsService, EventAnalyticsRepository $eventRepo)
  {
    $studentAffairs = $deanReportsService->studentAffairsReport();

    $departmentActivitiesQuery = DepartmentActivity::with('subject:id,title,code')
      ->orderByDesc('activity_date')
      ->orderByDesc('id');

    if ($request->filled('activity_date')) {
      $departmentActivitiesQuery->whereDate('activity_date', $request->activity_date);
    }

    if ($request->filled('department_id')) {
      $departmentActivitiesQuery->where('subject_id', (int) $request->department_id);
    }

    $departmentActivities = $departmentActivitiesQuery
      ->paginate(20, ['*'], 'activities_page')
      ->withQueryString();

    $eventPrograms = $eventRepo->eventProgramRows();

    if ($request->filled('program_date')) {
      $eventPrograms = $eventPrograms
        ->filter(fn($program) => optional($program->program_date)->format('Y-m-d') === $request->program_date)
        ->values();
    }

    $attendanceShortage = $deanReportsService->attendanceShortageReport();
    if (method_exists($attendanceShortage, 'take')) {
      $attendanceShortage = $attendanceShortage->take(200);
    }

    $regularizationRegister = $deanReportsService->attendanceRegularizationRegister();
    if ($request->filled('regularization_date')) {
      $regularizationRegister = $regularizationRegister
        ->filter(fn($item) => optional($item->attendance_date)->format('Y-m-d') === $request->regularization_date)
        ->values();
    }

    $councilDocumentsQuery = DsaStudentCouncilDocument::with([
      'council:id,name,academic_year',
      'meeting:id,title,meeting_date',
    ])
      ->orderByDesc('published_at')
      ->orderByDesc('id');

    if ($request->filled('document_type')) {
      $councilDocumentsQuery->where('document_type', $request->document_type);
    }

    $councilDocuments = $councilDocumentsQuery
      ->paginate(20, ['*'], 'documents_page')
      ->withQueryString();

    $subjectOptions = Subject::orderBy('title')->get(['id', 'title', 'code']);

    return view('iqac.dean-student-affairs', compact(
      'studentAffairs',
      'departmentActivities',
      'eventPrograms',
      'attendanceShortage',
      'regularizationRegister',
      'councilDocuments',
      'subjectOptions'
    ));
  }

  public function eventControllerReports(Request $request)
  {
    $hasApprovalStatus = Schema::hasColumn('ec_event_iqac_reports', 'approval_status');

    $query = EcEventIqacReport::with('event:id,title,start_date,end_date,venue,status');

    if ($hasApprovalStatus && $request->filled('status')) {
      $query->where('approval_status', $request->status);
    }

    if ($request->filled('submitted_on')) {
      $query->whereDate('submitted_on', $request->submitted_on);
    }

    $reports = $query
      ->orderByDesc('submitted_on')
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    $events = EcEvent::with([
      'iqacReports' => function ($q) {
        $q->orderByDesc('submitted_on')->orderByDesc('id');
      },
    ])
      ->orderByDesc('start_date')
      ->orderByDesc('id')
      ->paginate(12, ['*'], 'events_page')
      ->withQueryString();

    if (!$hasApprovalStatus) {
      $reports->getCollection()->transform(function ($report) {
        $report->approval_status = 'pending';
        return $report;
      });

      $events->getCollection()->transform(function ($event) {
        $event->iqacReports->transform(function ($report) {
          $report->approval_status = 'pending';
          return $report;
        });

        return $event;
      });
    }

    return view('iqac.event-controller-reports', compact('reports', 'events'));
  }

  public function updateEventControllerReportStatus(Request $request, $reportId)
  {
    if (!Schema::hasTable('ec_event_iqac_reports') || !Schema::hasColumn('ec_event_iqac_reports', 'approval_status')) {
      return redirect()->back()->with('error', 'Event IQAC approval columns are not available yet. Please run migrations first.');
    }

    $report = EcEventIqacReport::findOrFail((int) $reportId);

    $validated = $request->validate([
      'approval_status' => 'required|in:approved,rejected,pending',
      'review_remarks' => 'nullable|string|max:2000|required_if:approval_status,rejected',
    ]);

    $report->update([
      'approval_status' => $validated['approval_status'],
      'review_remarks' => $validated['review_remarks'] ?? null,
      'reviewed_by_user_id' => auth()->id(),
      'reviewed_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Event Controller IQAC review status updated successfully.');
  }
}

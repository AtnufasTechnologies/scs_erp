<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\MentorshipAssignment;
use App\Models\MentorshipGroup;
use App\Models\MentorshipGroupStudent;
use App\Models\MentorshipSession;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipStudentNote;
use App\Repositories\Dean\MentoringAnalyticsRepository;
use App\Services\Dean\CampusContextService;

class MentoringDashboardController extends Controller
{
  public function __construct(
    protected MentoringAnalyticsRepository $mentoringRepo,
    protected CampusContextService $campusContext,
  ) {}

  public function index()
  {
    $campusId = $this->campusContext->campusId();

    $summary = $this->mentoringRepo->summary();

    $groupsQuery = MentorshipGroup::with('faculty')->latest();
    if ($campusId) {
      $groupsQuery->whereHas('faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }
    $groups = $groupsQuery->paginate(20, ['*'], 'groups_page');

    $sessionsQuery = MentorshipSession::with(['group.faculty'])
      ->withCount('attendances')
      ->withCount([
        'attendances as present_count' => function ($query) {
          $query->where('status', 'present');
        },
        'attendances as absent_count' => function ($query) {
          $query->where('status', 'absent');
        },
        'attendances as excused_count' => function ($query) {
          $query->where('status', 'excused');
        },
      ])
      ->latest('session_date');

    if ($campusId) {
      $sessionsQuery->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $sessions = $sessionsQuery->paginate(20, ['*'], 'sessions_page');

    $assignmentsQuery = MentorshipAssignment::with(['group.faculty'])
      ->withCount('submissions')
      ->withCount([
        'submissions as graded_submissions_count' => function ($query) {
          $query->whereNotNull('marks_obtained');
        },
      ])
      ->latest('due_date');

    if ($campusId) {
      $assignmentsQuery->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $assignments = $assignmentsQuery->paginate(20, ['*'], 'assignments_page');

    $enrollmentsQuery = MentorshipGroupStudent::with(['group.faculty', 'student'])
      ->latest();

    if ($campusId) {
      $enrollmentsQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      })->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $enrollments = $enrollmentsQuery->paginate(25, ['*'], 'enrollments_page');

    $attendanceRecordsQuery = MentorshipSessionAttendance::with(['session.group.faculty', 'student'])
      ->latest();

    if ($campusId) {
      $attendanceRecordsQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      })->whereHas('session.group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $attendanceRecords = $attendanceRecordsQuery->paginate(25, ['*'], 'attendance_page');

    $highRiskNotesQuery = MentorshipStudentNote::with('student')
      ->where(function ($query) {
        $query->where('category', 'like', '%risk%')
          ->orWhere('category', 'like', '%urgent%')
          ->orWhere('category', 'like', '%counselling%');
      });

    if ($campusId) {
      $highRiskNotesQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }

    $highRiskNotes = $highRiskNotesQuery->latest()->limit(200)->get();

    return view('student-affairs.mentoring.index', compact('summary', 'groups', 'sessions', 'assignments', 'enrollments', 'attendanceRecords', 'highRiskNotes'));
  }
}

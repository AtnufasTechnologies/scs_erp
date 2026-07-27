<?php

namespace App\Http\Controllers;

use App\Models\DsaClub;
use App\Models\DsaClubMembership;
use App\Models\MentorshipAssignment;
use App\Models\MentorshipGroup;
use App\Models\MentorshipGroupStudent;
use App\Models\MentorshipSession;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipStudentNote;
use App\Models\StudentMaster;
use App\Models\UserCampusSetting;
use App\Repositories\Dean\MentoringAnalyticsRepository;
use App\Repositories\Dean\Student360Repository;
use App\Services\Dean\CampusContextService;
use Illuminate\Http\Request;

class PrincipalMonitoringController extends Controller
{
  public function __construct(
    protected CampusContextService $campusContext,
    protected MentoringAnalyticsRepository $mentoringRepo,
    protected Student360Repository $student360Repo,
  ) {}

  public function mentoring()
  {
    $campusId = $this->campusContext->campusId();

    $summary = $this->mentoringRepo->summary();

    $groupsQuery = MentorshipGroup::with('faculty')->latest();
    if ($campusId) {
      $groupsQuery->whereHas('faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }
    $groups = $groupsQuery->paginate(15, ['*'], 'groups_page');

    $sessionsQuery = MentorshipSession::with(['group.faculty'])
      ->withCount('attendances')
      ->withCount([
        'attendances as present_count' => function ($query) {
          $query->where('status', 'present');
        },
        'attendances as absent_count' => function ($query) {
          $query->where('status', 'absent');
        },
      ])
      ->latest('session_date');

    if ($campusId) {
      $sessionsQuery->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }
    $sessions = $sessionsQuery->paginate(15, ['*'], 'sessions_page');

    $assignmentsQuery = MentorshipAssignment::with(['group.faculty'])
      ->withCount('submissions')
      ->latest('due_date');

    if ($campusId) {
      $assignmentsQuery->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }
    $assignments = $assignmentsQuery->paginate(15, ['*'], 'assignments_page');

    $enrollmentsQuery = MentorshipGroupStudent::with(['group.faculty', 'student'])->latest();
    if ($campusId) {
      $enrollmentsQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }
    $enrollments = $enrollmentsQuery->paginate(20, ['*'], 'enrollments_page');

    $attendanceRecordsQuery = MentorshipSessionAttendance::with(['session.group.faculty', 'student'])->latest();
    if ($campusId) {
      $attendanceRecordsQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }
    $attendanceRecords = $attendanceRecordsQuery->paginate(20, ['*'], 'attendance_page');

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
    $highRiskNotes = $highRiskNotesQuery->latest()->limit(120)->get();

    return view('principal.monitoring.mentoring', compact(
      'summary',
      'groups',
      'sessions',
      'assignments',
      'enrollments',
      'attendanceRecords',
      'highRiskNotes'
    ));
  }

  public function student360(Request $request)
  {
    $studentId = (int) $request->query('student_id', 0);

    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')
      ->orderBy('first_name')
      ->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    if ($studentId <= 0) {
      return view('principal.monitoring.student-360', [
        'students' => $students,
        'profile' => null,
      ]);
    }

    $selectedStudent = StudentMaster::where('id', $studentId);
    $this->campusContext->applyStudentCampus($selectedStudent);
    if (!$selectedStudent->exists()) {
      abort(403, 'Selected student is outside your allowed campus scope.');
    }

    $profile = $this->student360Repo->profile($studentId);

    return view('principal.monitoring.student-360', [
      'students' => $students,
      'profile' => $profile,
    ]);
  }

  public function clubs()
  {
    $clubsQuery = DsaClub::with('coordinator')
      ->withCount('memberships')
      ->latest();

    $campusId = $this->campusContext->campusId();
    if ($campusId) {
      $campusUserIds = UserCampusSetting::where('campus_id', $campusId)->select('user_id');

      $clubsQuery->where(function ($query) use ($campusId, $campusUserIds) {
        $query->whereIn('created_by', $campusUserIds)
          ->orWhereHas('coordinator', function ($coordinatorQuery) use ($campusId) {
            $coordinatorQuery->where('CAMPUS_ID', $campusId);
          })
          ->orWhereHas('memberships.student', function ($memberQuery) use ($campusId) {
            $memberQuery->where('campus_id', $campusId);
          });
      });
    }

    $clubs = $clubsQuery->paginate(20);

    return view('principal.monitoring.clubs.index', compact('clubs'));
  }

  public function clubMembers(DsaClub $club)
  {
    $this->authorizeClubCampus($club);

    $membershipsQuery = DsaClubMembership::with('student:id,first_name,last_name,roll_no')
      ->where('club_id', $club->id)
      ->latest();

    $this->campusContext->applyStudentRelationCampus($membershipsQuery, 'student');
    $memberships = $membershipsQuery->paginate(30);

    return view('principal.monitoring.clubs.show', compact('club', 'memberships'));
  }

  private function authorizeClubCampus(DsaClub $club): void
  {
    $campusId = $this->campusContext->campusId();
    if (!$campusId) {
      return;
    }

    $campusUserIds = UserCampusSetting::where('campus_id', $campusId)->select('user_id');

    $isAuthorized = DsaClub::query()
      ->where('id', $club->id)
      ->where(function ($query) use ($campusId, $campusUserIds) {
        $query->whereIn('created_by', $campusUserIds)
          ->orWhereHas('coordinator', function ($coordinatorQuery) use ($campusId) {
            $coordinatorQuery->where('CAMPUS_ID', $campusId);
          })
          ->orWhereHas('memberships.student', function ($memberQuery) use ($campusId) {
            $memberQuery->where('campus_id', $campusId);
          });
      })
      ->exists();

    if (!$isAuthorized) {
      abort(403, 'Club/cell is outside your allowed campus scope.');
    }
  }
}

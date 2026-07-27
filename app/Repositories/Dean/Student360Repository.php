<?php

namespace App\Repositories\Dean;

use App\Models\DsaAttendanceRegularizationItem;
use App\Models\DsaClubMembership;
use App\Models\DsaCounsellingCase;
use App\Models\DsaDisciplineCase;
use App\Models\DsaStudentCouncilMember;
use App\Models\MentorshipAssignmentSubmission;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipStudentNote;
use App\Models\StudentAttendance;
use App\Models\StudentMaster;
use App\Services\Dean\CampusContextService;

class Student360Repository
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function profile(int $studentId): array
  {
    $studentQuery = StudentMaster::with(['batchmaster', 'subjectmaster'])->where('id', $studentId);
    $this->campusContext->applyStudentCampus($studentQuery);
    $student = $studentQuery->firstOrFail();

    $attendanceTotal = StudentAttendance::where('student_id', $studentId)->count();
    $attendancePresent = StudentAttendance::where('student_id', $studentId)->where('status', 'present')->count();
    $attendancePct = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 2) : 0;

    $mentorshipAttendanceTotal = MentorshipSessionAttendance::where('student_id', $studentId)->count();
    $mentorshipAttendancePresent = MentorshipSessionAttendance::where('student_id', $studentId)->where('status', 'present')->count();
    $mentorshipAttendancePct = $mentorshipAttendanceTotal > 0 ? round(($mentorshipAttendancePresent / $mentorshipAttendanceTotal) * 100, 2) : 0;

    $assignmentSubmissionCount = MentorshipAssignmentSubmission::where('student_id', $studentId)->count();
    $assignmentGradedCount = MentorshipAssignmentSubmission::where('student_id', $studentId)->whereNotNull('marks_obtained')->count();

    return [
      'student' => $student,
      'attendance_pct' => $attendancePct,
      'attendance_total' => $attendanceTotal,
      'attendance_present' => $attendancePresent,
      'attendance_absent' => max($attendanceTotal - $attendancePresent, 0),
      'mentorship_attendance_total' => $mentorshipAttendanceTotal,
      'mentorship_attendance_present' => $mentorshipAttendancePresent,
      'mentorship_attendance_pct' => $mentorshipAttendancePct,
      'assignment_submission_count' => $assignmentSubmissionCount,
      'assignment_graded_count' => $assignmentGradedCount,
      'mentoring_notes' => MentorshipStudentNote::where('student_id', $studentId)->latest()->limit(30)->get(),
      'club_memberships' => DsaClubMembership::with('club')->where('student_id', $studentId)->latest()->get(),
      'council_roles' => DsaStudentCouncilMember::with('council')->where('student_id', $studentId)->latest()->get(),
      'discipline_cases' => DsaDisciplineCase::with(['actions' => function ($query) {
        $query->latest();
      }, 'hearings' => function ($query) {
        $query->latest('hearing_date');
      }])->where('student_id', $studentId)->latest()->get(),
      'counselling_cases' => DsaCounsellingCase::with(['followups' => function ($query) {
        $query->latest('followup_date');
      }])->where('student_id', $studentId)->latest()->get(),
      'attendance_regularizations' => DsaAttendanceRegularizationItem::with('regularization')
        ->where('student_id', $studentId)
        ->latest()
        ->limit(100)
        ->get(),
    ];
  }
}

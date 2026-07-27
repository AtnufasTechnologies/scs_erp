<?php

namespace App\Repositories\Dean;

use App\Models\MentorshipGroup;
use App\Models\MentorshipSession;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipStudentNote;
use App\Services\Dean\CampusContextService;

class MentoringAnalyticsRepository
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function summary(): array
  {
    $campusId = $this->campusContext->campusId();

    $groupsQuery = MentorshipGroup::query();
    if ($campusId) {
      $groupsQuery->whereHas('faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $totalGroups = $groupsQuery->count();

    $sessionsQuery = MentorshipSession::query();
    if ($campusId) {
      $sessionsQuery->whereHas('group.faculty', function ($query) use ($campusId) {
        $query->where('CAMPUS_ID', $campusId);
      });
    }

    $totalSessions = $sessionsQuery->count();

    $notesQuery = MentorshipStudentNote::query();
    if ($campusId) {
      $notesQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }

    $totalNotes = $notesQuery->count();

    $highRiskQuery = MentorshipStudentNote::where(function ($query) {
      $query->where('category', 'like', '%risk%')
        ->orWhere('category', 'like', '%urgent%')
        ->orWhere('category', 'like', '%counselling%');
    });

    if ($campusId) {
      $highRiskQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }

    $highRisk = $highRiskQuery->distinct('student_id')->count('student_id');

    $attendancePct = 0;
    $attendanceTotalQuery = MentorshipSessionAttendance::query();
    if ($campusId) {
      $attendanceTotalQuery->whereHas('student', function ($query) use ($campusId) {
        $query->where('campus_id', $campusId);
      });
    }

    $attendanceTotal = $attendanceTotalQuery->count();
    if ($attendanceTotal > 0) {
      $presentQuery = MentorshipSessionAttendance::where('status', 'present');
      if ($campusId) {
        $presentQuery->whereHas('student', function ($query) use ($campusId) {
          $query->where('campus_id', $campusId);
        });
      }

      $present = $presentQuery->count();
      $attendancePct = round(($present / $attendanceTotal) * 100, 2);
    }

    return [
      'total_groups' => $totalGroups,
      'total_sessions' => $totalSessions,
      'total_notes' => $totalNotes,
      'high_risk_students' => $highRisk,
      'session_attendance_pct' => $attendancePct,
    ];
  }
}

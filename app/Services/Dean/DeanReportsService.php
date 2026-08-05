<?php

namespace App\Services\Dean;

use App\Models\DsaAttendanceRegularizationItem;
use App\Models\DsaClubMembership;
use App\Models\DsaCounsellingCase;
use App\Models\DsaDisciplineCase;
use App\Repositories\Dean\AttendanceAnalyticsRepository;
use App\Repositories\Dean\EventAnalyticsRepository;
use App\Services\Dean\CampusContextService;

class DeanReportsService
{
  public function __construct(
    protected AttendanceAnalyticsRepository $attendanceRepo,
    protected EventAnalyticsRepository $eventRepo,
    protected CampusContextService $campusContext,
  ) {}

  public function studentAffairsReport(): array
  {
    $attendanceRows = $this->attendanceRepo->studentPercentages();

    $disciplineQuery = DsaDisciplineCase::query();
    $this->campusContext->applyStudentRelationCampus($disciplineQuery, 'student');

    $counsellingQuery = DsaCounsellingCase::query();
    $this->campusContext->applyStudentRelationCampus($counsellingQuery, 'student');

    $clubMembershipQuery = DsaClubMembership::query();
    $this->campusContext->applyStudentRelationCampus($clubMembershipQuery, 'student');

    return [
      'attendance_thresholds' => $this->attendanceRepo->thresholdBuckets($attendanceRows),
      'discipline_cases' => $disciplineQuery->count(),
      'counselling_cases' => $counsellingQuery->count(),
      'club_memberships' => $clubMembershipQuery->count(),
      'event_summary' => $this->eventRepo->summary(),
    ];
  }

  public function attendanceShortageReport()
  {
    return $this->attendanceRepo->studentPercentages()
      ->where('attendance_pct', '<', 75)
      ->values();
  }

  public function attendanceRegularizationRegister()
  {
    $query = DsaAttendanceRegularizationItem::with(['student:id,first_name,last_name,roll_no', 'regularization'])
      ->latest()
      ->limit(2000);

    $this->campusContext->applyStudentRelationCampus($query, 'student');

    return $query->get();
  }
}

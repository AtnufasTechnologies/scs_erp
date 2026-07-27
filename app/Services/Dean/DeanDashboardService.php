<?php

namespace App\Services\Dean;

use App\Models\DsaCounsellingCase;
use App\Models\DsaDisciplineCase;
use App\Models\DsaStudentCouncil;
use App\Models\StudentMaster;
use App\Repositories\Dean\AttendanceAnalyticsRepository;
use App\Repositories\Dean\EventAnalyticsRepository;
use App\Repositories\Dean\MentoringAnalyticsRepository;
use App\Services\Dean\CampusContextService;

class DeanDashboardService
{
  public function __construct(
    protected AttendanceAnalyticsRepository $attendanceRepo,
    protected EventAnalyticsRepository $eventRepo,
    protected MentoringAnalyticsRepository $mentoringRepo,
    protected CampusContextService $campusContext,
  ) {}

  public function buildDashboardData(): array
  {
    $studentQuery = StudentMaster::query();
    $this->campusContext->applyStudentCampus($studentQuery);

    $activeStudentQuery = StudentMaster::where('status', 'active');
    $this->campusContext->applyStudentCampus($activeStudentQuery);

    $inactiveStudentQuery = StudentMaster::where('status', '!=', 'active');
    $this->campusContext->applyStudentCampus($inactiveStudentQuery);

    $studentCounts = [
      'total' => $studentQuery->count(),
      'active' => $activeStudentQuery->count(),
      'inactive' => $inactiveStudentQuery->count(),
    ];

    $attendanceRows = $this->attendanceRepo->studentPercentages();
    $attendanceBuckets = $this->attendanceRepo->thresholdBuckets($attendanceRows);

    $mentoringSummary = $this->mentoringRepo->summary();
    $eventSummary = $this->eventRepo->summary();

    $departmentAnalytics = $attendanceRows
      ->groupBy('department_id')
      ->map(function ($rows, $deptId) {
        $count = $rows->count();
        $avg = $count > 0 ? round($rows->avg('attendance_pct'), 2) : 0;
        return [
          'department_id' => (int) $deptId,
          'student_count' => $count,
          'avg_attendance_pct' => $avg,
          'critical_below_40' => $rows->where('attendance_pct', '<', 40)->count(),
        ];
      })
      ->values();

    $counsellingQuery = DsaCounsellingCase::where('status', 'open');
    $this->campusContext->applyStudentRelationCampus($counsellingQuery, 'student');

    $disciplineQuery = DsaDisciplineCase::where('status', 'open');
    $this->campusContext->applyStudentRelationCampus($disciplineQuery, 'student');

    $activeCouncilsQuery = DsaStudentCouncil::where('status', 'active');
    $campusId = $this->campusContext->campusId();
    if ($campusId) {
      $activeCouncilsQuery->where('campus_id', $campusId);
    }

    return [
      'student_counts' => $studentCounts,
      'attendance_alerts' => $attendanceBuckets,
      'below_40_students' => $attendanceRows->where('attendance_pct', '<', 40)->take(100)->values(),
      'mentoring_summary' => $mentoringSummary,
      'event_summary' => $eventSummary,
      'counselling_open_cases' => $counsellingQuery->count(),
      'discipline_open_cases' => $disciplineQuery->count(),
      'active_councils' => $activeCouncilsQuery->count(),
      'department_analytics' => $departmentAnalytics,
    ];
  }
}

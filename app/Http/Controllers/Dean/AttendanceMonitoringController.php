<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Repositories\Dean\AttendanceAnalyticsRepository;

class AttendanceMonitoringController extends Controller
{
  public function __construct(protected AttendanceAnalyticsRepository $attendanceRepo) {}

  public function index()
  {
    $rows = $this->attendanceRepo->studentPercentages();
    $thresholds = $this->attendanceRepo->thresholdBuckets($rows);

    return view('student-affairs.attendance.monitoring', [
      'rows' => $rows,
      'thresholds' => $thresholds,
      'criticalRows' => $rows->where('attendance_pct', '<', 40)->values(),
    ]);
  }
}

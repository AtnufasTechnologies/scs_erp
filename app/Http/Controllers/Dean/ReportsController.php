<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Services\Dean\DeanReportsService;

class ReportsController extends Controller
{
  public function __construct(protected DeanReportsService $reportsService) {}

  public function index()
  {
    $studentAffairs = $this->reportsService->studentAffairsReport();
    $attendanceShortage = $this->reportsService->attendanceShortageReport();
    $regularizationRegister = $this->reportsService->attendanceRegularizationRegister();

    return view('student-affairs.reports.index', compact('studentAffairs', 'attendanceShortage', 'regularizationRegister'));
  }
}

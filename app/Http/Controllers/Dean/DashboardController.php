<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Services\Dean\DeanDashboardService;

class DashboardController extends Controller
{
  public function __construct(protected DeanDashboardService $dashboardService) {}

  public function index()
  {
    return view('student-affairs.dashboard.index', $this->dashboardService->buildDashboardData());
  }
}

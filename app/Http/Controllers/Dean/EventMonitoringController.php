<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Repositories\Dean\EventAnalyticsRepository;

class EventMonitoringController extends Controller
{
  public function __construct(protected EventAnalyticsRepository $eventRepo) {}

  public function index()
  {
    $summary = $this->eventRepo->summary();
    $programs = $this->eventRepo->eventProgramRows();
    $departmentActivities = $this->eventRepo->departmentActivityRows();

    return view('student-affairs.events.index', compact('summary', 'programs', 'departmentActivities'));
  }
}

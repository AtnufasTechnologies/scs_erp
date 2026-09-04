<?php

namespace App\Http\Controllers;

use App\Models\BiometricAttendanceLog;
use Illuminate\Http\Request;

class HrBiometricAttendanceController extends Controller
{
  public function index(Request $request)
  {
    $employeeNo = trim((string) $request->get('employee_no', ''));
    $eventType = trim((string) $request->get('event_type', ''));
    $dateFrom = trim((string) $request->get('date_from', ''));
    $dateTo = trim((string) $request->get('date_to', ''));

    $query = BiometricAttendanceLog::query();

    if ($employeeNo !== '') {
      $query->where('employee_no', 'like', '%' . $employeeNo . '%');
    }

    if ($eventType !== '') {
      $query->where('event_type', $eventType);
    }

    if ($dateFrom !== '') {
      $query->whereDate('punch_time', '>=', $dateFrom);
    }

    if ($dateTo !== '') {
      $query->whereDate('punch_time', '<=', $dateTo);
    }

    $logs = $query
      ->orderByDesc('punch_time')
      ->orderByDesc('id')
      ->paginate(50)
      ->withQueryString();

    $eventTypes = BiometricAttendanceLog::query()
      ->select('event_type')
      ->whereNotNull('event_type')
      ->where('event_type', '!=', '')
      ->distinct()
      ->orderBy('event_type')
      ->pluck('event_type');

    return view('hr.biometric-attendance.index', compact('logs', 'eventTypes', 'employeeNo', 'eventType', 'dateFrom', 'dateTo'));
  }
}

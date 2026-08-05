<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\DsaAttendanceRegularization;
use App\Services\Dean\AttendanceRegularizationService;
use App\Services\Dean\CampusContextService;
use Illuminate\Http\Request;

class AttendanceRegularizationController extends Controller
{
  public function __construct(
    protected AttendanceRegularizationService $regularizationService,
    protected CampusContextService $campusContext,
  ) {}

  public function index(Request $request)
  {
    $eventSource = (string) $request->query('event_source', 'ec_event');
    $events = $this->regularizationService->fetchApprovedEvents($eventSource);

    $historyQuery = DsaAttendanceRegularization::withCount([
      'items as items_count' => function ($query) {
        $this->campusContext->applyStudentRelationCampus($query, 'student');
      }
    ])->latest();

    $this->campusContext->applyStudentRelationCampus($historyQuery, 'items.student');
    $history = $historyQuery->paginate(20);

    return view('student-affairs.attendance.regularization', compact('eventSource', 'events', 'history'));
  }

  public function preview(Request $request)
  {
    $validated = $request->validate([
      'event_source' => 'required|in:ec_event,department_activity',
      'event_id' => 'required|integer',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $rows = $this->regularizationService->previewAbsences(
      $validated['event_source'],
      (int) $validated['event_id'],
      $validated['start_date'],
      $validated['end_date']
    );

    return response()->json([
      'status' => true,
      'rows' => $rows,
    ]);
  }

  public function approve(Request $request)
  {
    $validated = $request->validate([
      'event_source' => 'required|in:ec_event,department_activity',
      'event_id' => 'required|integer',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'attendance_ids' => 'required|array|min:1',
      'attendance_ids.*' => 'integer',
      'remarks' => 'nullable|string|max:500',
    ]);

    $record = $this->regularizationService->approveRegularization(
      $validated['event_source'],
      (int) $validated['event_id'],
      $validated['start_date'],
      $validated['end_date'],
      $validated['attendance_ids'],
      $validated['remarks'] ?? null,
    );

    return response()->json([
      'status' => true,
      'message' => 'Attendance regularization approved with full audit trail.',
      'request_no' => $record->request_no,
    ]);
  }

  public function history(DsaAttendanceRegularization $regularization)
  {
    $campusId = $this->campusContext->campusId();
    if ($campusId) {
      $hasCampusItem = $regularization->items()
        ->whereHas('student', function ($query) use ($campusId) {
          $query->where('campus_id', $campusId);
        })
        ->exists();

      if (!$hasCampusItem) {
        abort(403, 'Record is outside your assigned campus.');
      }
    }

    $regularization->load('items.student');

    if ($campusId) {
      $regularization->setRelation(
        'items',
        $regularization->items->filter(function ($item) use ($campusId) {
          return (int) ($item->student->campus_id ?? 0) === (int) $campusId;
        })->values()
      );
    }

    return view('student-affairs.attendance.regularization-history', compact('regularization'));
  }
}

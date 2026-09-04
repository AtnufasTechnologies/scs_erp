<?php

namespace App\Http\Controllers;

use App\Models\EcEvent;
use App\Models\EcEventIqacReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EventIqacReportController extends Controller
{
  public function index($eventId)
  {
    $event = EcEvent::findOrFail($eventId);

    $reports = EcEventIqacReport::where('ec_event_id', $event->id)
      ->orderByDesc('submitted_on')
      ->orderByDesc('id')
      ->get();

    return view('event-coordinator.events.iqac-report', [
      'event' => $event,
      'reports' => $reports,
    ]);
  }

  public function store(Request $request, $eventId)
  {
    $event = EcEvent::findOrFail($eventId);

    $validated = $request->validate([
      'report_title' => 'nullable|string|max:255',
      'submitted_on' => 'required|date',
      'report_file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240',
      'submission_note' => 'nullable|string|max:2000',
    ]);

    $reportFilePath = $request->file('report_file')->store('ec-events/iqac-reports', 'public');

    $createPayload = [
      'ec_event_id' => $event->id,
      'report_title' => $validated['report_title'] ?? null,
      'submitted_on' => $validated['submitted_on'],
      'report_file_path' => $reportFilePath,
      'submission_note' => $validated['submission_note'] ?? null,
      'submitted_by_user_id' => Auth::id(),
    ];

    if (Schema::hasColumn('ec_event_iqac_reports', 'approval_status')) {
      $createPayload['approval_status'] = 'pending';
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'review_remarks')) {
      $createPayload['review_remarks'] = null;
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'reviewed_by_user_id')) {
      $createPayload['reviewed_by_user_id'] = null;
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'reviewed_at')) {
      $createPayload['reviewed_at'] = null;
    }

    EcEventIqacReport::create($createPayload);

    return redirect()->route('event-coordinator.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report submitted successfully.');
  }

  public function update(Request $request, $eventId, $reportId)
  {
    $event = EcEvent::findOrFail($eventId);

    $report = EcEventIqacReport::where('ec_event_id', $event->id)
      ->where('id', $reportId)
      ->firstOrFail();

    $validated = $request->validate([
      'report_title' => 'nullable|string|max:255',
      'submitted_on' => 'required|date',
      'report_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240',
      'submission_note' => 'nullable|string|max:2000',
    ]);

    $reportFilePath = $report->report_file_path;
    if ($request->hasFile('report_file')) {
      if ($reportFilePath) {
        Storage::disk('public')->delete($reportFilePath);
      }
      $reportFilePath = $request->file('report_file')->store('ec-events/iqac-reports', 'public');
    }

    $updatePayload = [
      'report_title' => $validated['report_title'] ?? null,
      'submitted_on' => $validated['submitted_on'],
      'report_file_path' => $reportFilePath,
      'submission_note' => $validated['submission_note'] ?? null,
    ];

    if (Schema::hasColumn('ec_event_iqac_reports', 'approval_status')) {
      $updatePayload['approval_status'] = 'pending';
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'review_remarks')) {
      $updatePayload['review_remarks'] = null;
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'reviewed_by_user_id')) {
      $updatePayload['reviewed_by_user_id'] = null;
    }
    if (Schema::hasColumn('ec_event_iqac_reports', 'reviewed_at')) {
      $updatePayload['reviewed_at'] = null;
    }

    $report->update($updatePayload);

    return redirect()->route('event-coordinator.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report updated successfully.');
  }

  public function destroy($eventId, $reportId)
  {
    $event = EcEvent::findOrFail($eventId);

    $report = EcEventIqacReport::where('ec_event_id', $event->id)
      ->where('id', $reportId)
      ->firstOrFail();

    if ($report->report_file_path) {
      Storage::disk('public')->delete($report->report_file_path);
    }

    $report->delete();

    return redirect()->route('event-coordinator.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report deleted successfully.');
  }
}

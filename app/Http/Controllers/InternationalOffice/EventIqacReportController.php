<?php

namespace App\Http\Controllers\InternationalOffice;

use App\Http\Controllers\Controller;
use App\Models\InternationalOfficeEvent;
use App\Models\InternationalOfficeEventIqacReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventIqacReportController extends Controller
{
  public function index($eventId)
  {
    $event = InternationalOfficeEvent::with('activityType:id,title')->findOrFail($eventId);

    $reports = InternationalOfficeEventIqacReport::where('international_office_event_id', $event->id)
      ->orderByDesc('submitted_on')
      ->orderByDesc('id')
      ->get();

    return view('international-office.events.iqac-report', [
      'event' => $event,
      'reports' => $reports,
    ]);
  }

  public function store(Request $request, $eventId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $validated = $request->validate([
      'report_title' => 'nullable|string|max:255',
      'submitted_on' => 'required|date',
      'report_file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png|max:10240',
      'submission_note' => 'nullable|string|max:2000',
    ]);

    $reportFilePath = $request->file('report_file')->store('international-office/events/iqac-reports', 'public');

    InternationalOfficeEventIqacReport::create([
      'international_office_event_id' => $event->id,
      'report_title' => $validated['report_title'] ?? null,
      'submitted_on' => $validated['submitted_on'],
      'report_file_path' => $reportFilePath,
      'submission_note' => $validated['submission_note'] ?? null,
      'submitted_by_user_id' => Auth::id(),
    ]);

    return redirect()->route('international-office.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report submitted successfully.');
  }

  public function update(Request $request, $eventId, $reportId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $report = InternationalOfficeEventIqacReport::where('international_office_event_id', $event->id)
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
      $reportFilePath = $request->file('report_file')->store('international-office/events/iqac-reports', 'public');
    }

    $report->update([
      'report_title' => $validated['report_title'] ?? null,
      'submitted_on' => $validated['submitted_on'],
      'report_file_path' => $reportFilePath,
      'submission_note' => $validated['submission_note'] ?? null,
    ]);

    return redirect()->route('international-office.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report updated successfully.');
  }

  public function destroy($eventId, $reportId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $report = InternationalOfficeEventIqacReport::where('international_office_event_id', $event->id)
      ->where('id', $reportId)
      ->firstOrFail();

    if ($report->report_file_path) {
      Storage::disk('public')->delete($report->report_file_path);
    }

    $report->delete();

    return redirect()->route('international-office.events.iqac-reports.index', $event->id)
      ->with('success', 'IQAC report deleted successfully.');
  }
}

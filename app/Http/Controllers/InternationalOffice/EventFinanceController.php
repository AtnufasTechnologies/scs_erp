<?php

namespace App\Http\Controllers\InternationalOffice;

use App\Http\Controllers\Controller;
use App\Models\InternationalOfficeEvent;
use App\Models\InternationalOfficeEventFinanceNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventFinanceController extends Controller
{
  public function index($eventId)
  {
    $event = InternationalOfficeEvent::with('activityType:id,title')->findOrFail($eventId);

    $notes = InternationalOfficeEventFinanceNote::where('international_office_event_id', $event->id)
      ->orderByDesc('note_date')
      ->orderByDesc('id')
      ->get();

    $totalDebit = (float) $notes->where('entry_type', 'debit')->sum('amount');
    $totalCredit = (float) $notes->where('entry_type', 'credit')->sum('amount');
    $netExpense = $totalDebit - $totalCredit;

    return view('international-office.events.finance', [
      'event' => $event,
      'notes' => $notes,
      'totalDebit' => $totalDebit,
      'totalCredit' => $totalCredit,
      'netExpense' => $netExpense,
    ]);
  }

  public function store(Request $request, $eventId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $validated = $request->validate([
      'entry_type' => 'required|in:debit,credit',
      'amount' => 'required|numeric|min:0.01',
      'note_date' => 'required|date',
      'reference_no' => 'nullable|string|max:100',
      'note_text' => 'nullable|string|max:2000',
    ]);

    InternationalOfficeEventFinanceNote::create([
      'international_office_event_id' => $event->id,
      'entry_type' => $validated['entry_type'],
      'amount' => $validated['amount'],
      'note_date' => $validated['note_date'],
      'reference_no' => $validated['reference_no'] ?? null,
      'note_text' => $validated['note_text'] ?? null,
      'created_by_user_id' => Auth::id(),
    ]);

    return redirect()->route('international-office.events.finances.index', $event->id)
      ->with('success', ucfirst($validated['entry_type']) . ' note added successfully.');
  }

  public function update(Request $request, $eventId, $noteId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $note = InternationalOfficeEventFinanceNote::where('international_office_event_id', $event->id)
      ->where('id', $noteId)
      ->firstOrFail();

    $validated = $request->validate([
      'entry_type' => 'required|in:debit,credit',
      'amount' => 'required|numeric|min:0.01',
      'note_date' => 'required|date',
      'reference_no' => 'nullable|string|max:100',
      'note_text' => 'nullable|string|max:2000',
    ]);

    $note->update([
      'entry_type' => $validated['entry_type'],
      'amount' => $validated['amount'],
      'note_date' => $validated['note_date'],
      'reference_no' => $validated['reference_no'] ?? null,
      'note_text' => $validated['note_text'] ?? null,
    ]);

    return redirect()->route('international-office.events.finances.index', $event->id)
      ->with('success', 'Finance note updated successfully.');
  }

  public function destroy($eventId, $noteId)
  {
    $event = InternationalOfficeEvent::findOrFail($eventId);

    $note = InternationalOfficeEventFinanceNote::where('international_office_event_id', $event->id)
      ->where('id', $noteId)
      ->firstOrFail();

    $note->delete();

    return redirect()->route('international-office.events.finances.index', $event->id)
      ->with('success', 'Finance note deleted successfully.');
  }
}

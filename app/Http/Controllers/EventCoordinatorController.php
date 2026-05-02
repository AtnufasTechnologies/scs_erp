<?php

namespace App\Http\Controllers;

use App\Models\EcEvent;
use App\Models\EcFacultyDuty;
use App\Models\EcFundTransaction;
use App\Models\EcProgram;
use App\Models\EcSponsor;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventCoordinatorController extends Controller
{
  // ================================================================
  // DASHBOARD
  // ================================================================

  public function dashboard()
  {
    $totalEvents      = EcEvent::count();
    $activeEvents     = EcEvent::where('status', 'active')->count();
    $completedEvents  = EcEvent::where('status', 'completed')->count();
    $upcomingPrograms = EcProgram::where('program_date', '>=', today())
      ->where('status', 'upcoming')
      ->with('event')
      ->orderBy('program_date')
      ->take(5)
      ->get();

    $recentEvents = EcEvent::with(['programs', 'sponsors'])
      ->latest()
      ->take(6)
      ->get();

    $totalBudgetAllocated = EcEvent::sum('total_budget');
    $totalExpenses        = EcFundTransaction::where('type', 'expense')->sum('amount');
    $totalIncome          = EcFundTransaction::where('type', 'income')->sum('amount');
    $totalSponsorship     = EcSponsor::where('status', 'received')->sum('received_amount');

    return view('event-coordinator.dashboard', compact(
      'totalEvents',
      'activeEvents',
      'completedEvents',
      'upcomingPrograms',
      'recentEvents',
      'totalBudgetAllocated',
      'totalExpenses',
      'totalIncome',
      'totalSponsorship'
    ));
  }

  // ================================================================
  // EVENTS – CRUD
  // ================================================================

  public function eventsIndex()
  {
    $events = EcEvent::withCount(['programs', 'sponsors', 'facultyDuties'])
      ->latest()
      ->paginate(15);

    return view('event-coordinator.events.index', compact('events'));
  }

  public function eventsCreate()
  {
    return view('event-coordinator.events.create');
  }

  public function eventsStore(Request $request)
  {
    $validated = $request->validate([
      'title'        => 'required|string|max:255',
      'description'  => 'nullable|string',
      'start_date'   => 'required|date',
      'end_date'     => 'required|date|after_or_equal:start_date',
      'venue'        => 'nullable|string|max:255',
      'total_budget' => 'required|numeric|min:0',
      'banner_image' => 'nullable|image|max:2048',
      'status'       => 'required|in:draft,active,completed,cancelled',
    ]);

    if ($request->hasFile('banner_image')) {
      $validated['banner_image'] = $request->file('banner_image')
        ->store('ec_events/banners', 'public');
    }

    $validated['created_by'] = Auth::id();
    EcEvent::create($validated);

    return redirect()->route('event-coordinator.events.index')
      ->with('success', 'Event created successfully.');
  }

  public function eventsShow(EcEvent $event)
  {
    $event->load([
      'programs',
      'facultyDuties.faculty',
      'facultyDuties.program',
      'fundTransactions',
      'sponsors',
    ]);

    $faculties       = Faculty::where('IS_LEFT', 0)->orderBy('FIRST_NAME')->get();
    $programs        = $event->programs;
    $totalExpense    = $event->fundTransactions->where('type', 'expense')->sum('amount');
    $totalIncome     = $event->fundTransactions->where('type', 'income')->sum('amount');
    $balance         = $totalIncome - $totalExpense;
    $totalSponsorship = $event->sponsors->sum('received_amount');

    return view('event-coordinator.events.show', compact(
      'event',
      'faculties',
      'programs',
      'totalExpense',
      'totalIncome',
      'balance',
      'totalSponsorship'
    ));
  }

  public function eventsEdit(EcEvent $event)
  {
    return view('event-coordinator.events.edit', compact('event'));
  }

  public function eventsUpdate(Request $request, EcEvent $event)
  {
    $validated = $request->validate([
      'title'        => 'required|string|max:255',
      'description'  => 'nullable|string',
      'start_date'   => 'required|date',
      'end_date'     => 'required|date|after_or_equal:start_date',
      'venue'        => 'nullable|string|max:255',
      'total_budget' => 'required|numeric|min:0',
      'banner_image' => 'nullable|image|max:2048',
      'status'       => 'required|in:draft,active,completed,cancelled',
    ]);

    if ($request->hasFile('banner_image')) {
      if ($event->banner_image) {
        Storage::disk('public')->delete($event->banner_image);
      }
      $validated['banner_image'] = $request->file('banner_image')
        ->store('ec_events/banners', 'public');
    }

    $event->update($validated);

    return redirect()->route('event-coordinator.events.show', $event)
      ->with('success', 'Event updated successfully.');
  }

  public function eventsDestroy(EcEvent $event)
  {
    $event->delete();

    return redirect()->route('event-coordinator.events.index')
      ->with('success', 'Event deleted.');
  }

  // ================================================================
  // PROGRAMS
  // ================================================================

  public function programsEdit(EcProgram $program)
  {
    $program->load('event');
    return view('event-coordinator.programs.edit', compact('program'));
  }

  public function programsStore(Request $request, EcEvent $event)
  {
    $validated = $request->validate([
      'name'                    => 'required|string|max:255',
      'program_type'            => 'required|in:intra-college,inter-college',
      'program_scope'           => 'required|in:national,international',
      'description'             => 'nullable|string',
      'program_date'            => 'required|date',
      'start_time'              => 'nullable|date_format:H:i',
      'end_time'                => 'nullable|date_format:H:i',
      'venue'                   => 'nullable|string|max:255',
      'registration_fee'        => 'required|numeric|min:0',
      'registration_start_date' => 'nullable|date',
      'registration_end_date'   => 'nullable|date|after_or_equal:registration_start_date',
      'max_participants'        => 'nullable|integer|min:0',
      'status'                  => 'required|in:upcoming,ongoing,completed,cancelled',
    ]);

    $validated['event_id']         = $event->id;
    $validated['max_participants']  = $validated['max_participants'] ?? 0;
    EcProgram::create($validated);

    return back()->with('success', 'Program added successfully.');
  }

  public function programsUpdate(Request $request, EcProgram $program)
  {
    $validated = $request->validate([
      'name'                    => 'required|string|max:255',
      'program_type'            => 'required|in:intra-college,inter-college',
      'program_scope'           => 'required|in:national,international',
      'description'             => 'nullable|string',
      'program_date'            => 'required|date',
      'start_time'              => 'nullable|date_format:H:i',
      'end_time'                => 'nullable|date_format:H:i',
      'venue'                   => 'nullable|string|max:255',
      'registration_fee'        => 'required|numeric|min:0',
      'registration_start_date' => 'nullable|date',
      'registration_end_date'   => 'nullable|date|after_or_equal:registration_start_date',
      'max_participants'        => 'nullable|integer|min:0',
      'status'                  => 'required|in:upcoming,ongoing,completed,cancelled',
    ]);

    $program->update($validated);

    return back()->with('success', 'Program updated successfully.');
  }

  public function programsDestroy(EcProgram $program)
  {
    $eventId = $program->event_id;
    $program->delete();

    return redirect()->route('event-coordinator.events.show', $eventId)
      ->with('success', 'Program removed.');
  }

  // ================================================================
  // FACULTY DUTIES
  // ================================================================

  public function dutiesStore(Request $request, EcEvent $event)
  {
    $validated = $request->validate([
      'faculty_id'     => 'required|integer|exists:faculties,id',
      'program_id'     => 'nullable|integer|exists:ec_programs,id',
      'duty_title'     => 'required|string|max:255',
      'responsibility' => 'nullable|string',
      'remarks'        => 'nullable|string',
    ]);

    $validated['event_id']    = $event->id;
    $validated['assigned_by'] = Auth::id();
    EcFacultyDuty::create($validated);

    return back()->with('success', 'Duty assigned successfully.');
  }

  public function dutiesUpdate(Request $request, EcFacultyDuty $duty)
  {
    $validated = $request->validate([
      'duty_title'     => 'required|string|max:255',
      'responsibility' => 'nullable|string',
      'status'         => 'required|in:assigned,acknowledged,completed',
      'remarks'        => 'nullable|string',
    ]);

    $duty->update($validated);

    return back()->with('success', 'Duty updated.');
  }

  public function dutiesDestroy(EcFacultyDuty $duty)
  {
    $eventId = $duty->event_id;
    $duty->delete();

    return redirect()->route('event-coordinator.events.show', $eventId)
      ->with('success', 'Duty removed.');
  }

  // ================================================================
  // FUND TRANSACTIONS
  // ================================================================

  public function fundStore(Request $request, EcEvent $event)
  {
    $validated = $request->validate([
      'type'             => 'required|in:income,expense',
      'category'         => 'required|string|max:100',
      'description'      => 'required|string|max:255',
      'amount'           => 'required|numeric|min:0.01',
      'transaction_date' => 'required|date',
      'receipt_no'       => 'nullable|string|max:50',
      'payment_mode'     => 'nullable|string|max:50',
      'program_id'       => 'nullable|integer|exists:ec_programs,id',
      'attachment'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('attachment')) {
      $validated['attachment'] = $request->file('attachment')
        ->store('ec_events/receipts', 'public');
    }

    $validated['event_id']    = $event->id;
    $validated['recorded_by'] = Auth::id();
    EcFundTransaction::create($validated);

    return back()->with('success', 'Transaction recorded successfully.');
  }

  public function fundDestroy(EcFundTransaction $transaction)
  {
    $eventId = $transaction->event_id;
    if ($transaction->attachment) {
      Storage::disk('public')->delete($transaction->attachment);
    }
    $transaction->delete();

    return redirect()->route('event-coordinator.events.show', $eventId)
      ->with('success', 'Transaction deleted.');
  }

  // ================================================================
  // SPONSORS
  // ================================================================

  public function sponsorsStore(Request $request, EcEvent $event)
  {
    $validated = $request->validate([
      'name'             => 'required|string|max:255',
      'contact_person'   => 'nullable|string|max:255',
      'phone'            => 'nullable|string|max:20',
      'email'            => 'nullable|email|max:255',
      'address'          => 'nullable|string|max:500',
      'pledged_amount'   => 'required|numeric|min:0',
      'received_amount'  => 'required|numeric|min:0',
      'tier'             => 'required|in:platinum,gold,silver,bronze,in_kind',
      'benefits_offered' => 'nullable|string',
      'status'           => 'required|in:pending,confirmed,received,cancelled',
      'notes'            => 'nullable|string',
      'logo'             => 'nullable|image|max:1024',
    ]);

    if ($request->hasFile('logo')) {
      $validated['logo'] = $request->file('logo')
        ->store('ec_events/sponsors', 'public');
    }

    $validated['event_id'] = $event->id;
    EcSponsor::create($validated);

    return back()->with('success', 'Sponsor added successfully.');
  }

  public function sponsorsUpdate(Request $request, EcSponsor $sponsor)
  {
    $validated = $request->validate([
      'name'             => 'required|string|max:255',
      'contact_person'   => 'nullable|string|max:255',
      'phone'            => 'nullable|string|max:20',
      'email'            => 'nullable|email|max:255',
      'pledged_amount'   => 'required|numeric|min:0',
      'received_amount'  => 'required|numeric|min:0',
      'tier'             => 'required|in:platinum,gold,silver,bronze,in_kind',
      'status'           => 'required|in:pending,confirmed,received,cancelled',
      'notes'            => 'nullable|string',
    ]);

    $sponsor->update($validated);

    return back()->with('success', 'Sponsor updated.');
  }

  public function sponsorsDestroy(EcSponsor $sponsor)
  {
    $eventId = $sponsor->event_id;

    if ($sponsor->logo) {
      Storage::disk('public')->delete($sponsor->logo);
    }
    $sponsor->delete();

    return redirect()->route('event-coordinator.events.show', $eventId)
      ->with('success', 'Sponsor removed.');
  }

  // ================================================================
  // REPORT
  // ================================================================

  public function report(EcEvent $event)
  {
    $event->load([
      'programs',
      'facultyDuties.faculty',
      'facultyDuties.program',
      'fundTransactions.recordedBy',
      'sponsors',
      'creator',
    ]);

    $expenseByCategory = $event->fundTransactions
      ->where('type', 'expense')
      ->groupBy('category')
      ->map(fn($items) => $items->sum('amount'));

    $incomeByCategory = $event->fundTransactions
      ->where('type', 'income')
      ->groupBy('category')
      ->map(fn($items) => $items->sum('amount'));

    $totalExpense    = $event->fundTransactions->where('type', 'expense')->sum('amount');
    $totalIncome     = $event->fundTransactions->where('type', 'income')->sum('amount');
    $balance         = $totalIncome - $totalExpense;
    $totalSponsorship = $event->sponsors->sum('received_amount');

    $dutiesByStatus = $event->facultyDuties->groupBy('status');

    return view('event-coordinator.report', compact(
      'event',
      'expenseByCategory',
      'incomeByCategory',
      'totalExpense',
      'totalIncome',
      'balance',
      'totalSponsorship',
      'dutiesByStatus'
    ));
  }
}

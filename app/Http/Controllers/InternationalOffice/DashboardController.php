<?php

namespace App\Http\Controllers\InternationalOffice;

use App\Http\Controllers\Controller;
use App\Models\InternationalOfficeActivityTypeMaster;
use App\Models\InternationalOfficeEvent;
use App\Models\InternationalOfficeEventFinanceNote;
use App\Models\InternationalOfficeInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
  public function index()
  {
    $activityTypeCount = InternationalOfficeActivityTypeMaster::count();
    $activeActivityTypeCount = InternationalOfficeActivityTypeMaster::where('is_active', 1)->count();

    $institutionCount = InternationalOfficeInstitution::count();
    $institutionMouSignedCount = InternationalOfficeInstitution::where('has_mou', 1)->count();
    $institutionWithoutMouCount = max($institutionCount - $institutionMouSignedCount, 0);

    $eventCount = InternationalOfficeEvent::count();
    $eventMouCount = InternationalOfficeEvent::where('has_mou', 1)->count();

    $totalDebit = (float) InternationalOfficeEventFinanceNote::where('entry_type', 'debit')->sum('amount');
    $totalCredit = (float) InternationalOfficeEventFinanceNote::where('entry_type', 'credit')->sum('amount');
    $netExpense = $totalDebit - $totalCredit;

    $recentEvents = InternationalOfficeEvent::with([
      'activityType:id,title',
      'financeNotes:id,international_office_event_id,entry_type,amount',
    ])
      ->orderByDesc('trip_start_date')
      ->orderByDesc('id')
      ->take(10)
      ->get();

    $recentEvents->each(function ($event) {
      $event->total_debit = (float) $event->financeNotes->where('entry_type', 'debit')->sum('amount');
      $event->total_credit = (float) $event->financeNotes->where('entry_type', 'credit')->sum('amount');
      $event->net_expense = $event->total_debit - $event->total_credit;
    });

    return view('international-office.dashboard', [
      'activityTypeCount' => $activityTypeCount,
      'activeActivityTypeCount' => $activeActivityTypeCount,
      'institutionCount' => $institutionCount,
      'institutionMouSignedCount' => $institutionMouSignedCount,
      'institutionWithoutMouCount' => $institutionWithoutMouCount,
      'eventCount' => $eventCount,
      'eventMouCount' => $eventMouCount,
      'totalDebit' => $totalDebit,
      'totalCredit' => $totalCredit,
      'netExpense' => $netExpense,
      'recentEvents' => $recentEvents,
    ]);
  }

  function activityMaster()
  {
    $data = InternationalOfficeActivityTypeMaster::orderBy('sort_order')->orderBy('title')->get();
    return view('international-office.activitytypemaster', ['data' => $data]);
  }

  public function storeActivityType(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:150',
      'slug' => 'nullable|string|max:160|alpha_dash|unique:international_office_activity_type_masters,slug',
      'description' => 'nullable|string|max:1000',
      'sort_order' => 'nullable|integer|min:0',
    ]);

    $slugBase = $validated['slug'] ?? Str::slug($validated['title']);
    $slug = $this->resolveUniqueActivityTypeSlug($slugBase);

    InternationalOfficeActivityTypeMaster::create([
      'title' => $validated['title'],
      'slug' => $slug,
      'description' => $validated['description'] ?? null,
      'sort_order' => $validated['sort_order'] ?? 100,
      'is_active' => 1,
      'is_system' => 0,
    ]);

    return redirect()->back()->with('success', 'Activity type created successfully.');
  }

  public function updateActivityType(Request $request, $id)
  {
    $activityType = InternationalOfficeActivityTypeMaster::findOrFail($id);

    $validated = $request->validate([
      'title' => 'required|string|max:150',
      'description' => 'nullable|string|max:1000',
      'sort_order' => 'nullable|integer|min:0',
      'is_active' => 'nullable|in:0,1',
      'slug' => [
        'nullable',
        'string',
        'max:160',
        'alpha_dash',
        Rule::unique('international_office_activity_type_masters', 'slug')->ignore((int) $activityType->id),
      ],
    ]);

    $activityType->title = $validated['title'];
    $activityType->description = $validated['description'] ?? null;
    $activityType->sort_order = $validated['sort_order'] ?? $activityType->sort_order;
    $activityType->is_active = (int) ($validated['is_active'] ?? ($activityType->is_active ? 1 : 0)) === 1;

    if (!$activityType->is_system) {
      $requestedSlug = trim((string) ($validated['slug'] ?? ''));
      if ($requestedSlug !== '') {
        $activityType->slug = $this->resolveUniqueActivityTypeSlug($requestedSlug, (int) $activityType->id);
      }
    }

    $activityType->save();

    return redirect()->back()->with('success', 'Activity type updated successfully.');
  }

  public function toggleActivityType($id)
  {
    $activityType = InternationalOfficeActivityTypeMaster::findOrFail($id);

    if ($activityType->is_active && InternationalOfficeActivityTypeMaster::where('is_active', 1)->count() <= 1) {
      return redirect()->back()->with('error', 'At least one active activity type is required.');
    }

    $activityType->is_active = $activityType->is_active ? 0 : 1;
    $activityType->save();

    return redirect()->back()->with('success', 'Activity type status updated successfully.');
  }

  public function destroyActivityType($id)
  {
    $activityType = InternationalOfficeActivityTypeMaster::findOrFail($id);

    if ($activityType->is_system) {
      return redirect()->back()->with('error', 'System activity types cannot be deleted.');
    }

    $activityType->delete();

    return redirect()->back()->with('success', 'Activity type deleted successfully.');
  }

  private function resolveUniqueActivityTypeSlug(string $base, int $ignoreId = 0): string
  {
    $slug = trim($base) !== '' ? Str::slug($base) : 'activity-type';
    $candidate = $slug;
    $i = 1;

    while (
      InternationalOfficeActivityTypeMaster::where('slug', $candidate)
      ->when($ignoreId > 0, fn($q) => $q->where('id', '!=', $ignoreId))
      ->exists()
    ) {
      $candidate = $slug . '-' . $i;
      $i++;
    }

    return $candidate;
  }
}

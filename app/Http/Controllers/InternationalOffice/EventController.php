<?php

namespace App\Http\Controllers\InternationalOffice;

use App\Http\Controllers\Controller;
use App\Models\InternationalOfficeActivityTypeMaster;
use App\Models\InternationalOfficeEvent;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
  private function resolveSiliguriCampusId(): int
  {
    $campusId = (int) DB::table('campuses')
      ->where(function ($query) {
        $query->where('slug', 'siliguri-campus')
          ->orWhere('slug', 'siliguri')
          ->orWhere('name', 'like', '%Siliguri%');
      })
      ->orderBy('id')
      ->value('id');

    return $campusId > 0 ? $campusId : 0;
  }

  private function siliguriSubjectsQuery()
  {
    $campusId = $this->resolveSiliguriCampusId();

    $query = Subject::query()->orderBy('title');
    if ($campusId > 0) {
      $query->where('campus_id', $campusId);
    }

    return $query;
  }

  public function index()
  {
    $activityTypes = InternationalOfficeActivityTypeMaster::where('is_active', 1)
      ->orderBy('sort_order')
      ->orderBy('title')
      ->get(['id', 'title']);

    $subjects = $this->siliguriSubjectsQuery()->get(['id', 'title', 'code']);

    $events = InternationalOfficeEvent::with([
      'activityType:id,title',
      'financeNotes:id,international_office_event_id,entry_type,amount',
    ])
      ->orderByDesc('trip_start_date')
      ->orderByDesc('id')
      ->get();

    $events->each(function ($event) {
      $totalDebit = (float) $event->financeNotes->where('entry_type', 'debit')->sum('amount');
      $totalCredit = (float) $event->financeNotes->where('entry_type', 'credit')->sum('amount');

      $event->total_debit = $totalDebit;
      $event->total_credit = $totalCredit;
      $event->net_expense = $totalDebit - $totalCredit;
    });

    return view('international-office.events.index', [
      'activityTypes' => $activityTypes,
      'subjects' => $subjects,
      'events' => $events,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $this->validatePayload($request);

    $mouDocumentPath = null;
    if ($request->hasFile('mou_document')) {
      $mouDocumentPath = $request->file('mou_document')->store('international-office/events/mou', 'public');
    }

    $geotaggedPaths = [];
    if ($request->hasFile('geotag_photos')) {
      foreach ($request->file('geotag_photos') as $photo) {
        $geotaggedPaths[] = $photo->store('international-office/events/geotagged', 'public');
      }
    }

    $visitPhotoPaths = [];
    if ($request->hasFile('visit_photos')) {
      foreach ($request->file('visit_photos') as $photo) {
        $visitPhotoPaths[] = $photo->store('international-office/events/visit-photos', 'public');
      }
    }

    InternationalOfficeEvent::create([
      'activity_type_master_id' => (int) $validated['activity_type_master_id'],
      'nature_of_activity' => $validated['nature_of_activity'],
      'department_scope' => $validated['department_scope'],
      'department_subject_ids' => array_map('intval', $validated['department_subject_ids']),
      'approval_type' => $validated['approval_type'],
      'visiting_institution_name' => $validated['visiting_institution_name'],
      'visiting_institution_contact' => $validated['visiting_institution_contact'] ?? null,
      'visiting_institution_email' => $validated['visiting_institution_email'] ?? null,
      'visiting_institution_address' => $validated['visiting_institution_address'] ?? null,
      'has_mou' => $request->boolean('has_mou'),
      'mou_document_path' => $mouDocumentPath,
      'trip_start_date' => $validated['trip_start_date'],
      'trip_end_date' => $validated['trip_end_date'],
      'geotagged_photo_paths' => !empty($geotaggedPaths) ? $geotaggedPaths : null,
      'visit_photo_paths' => !empty($visitPhotoPaths) ? $visitPhotoPaths : null,
      'members_json' => $this->normalizeMembers($validated['members'] ?? []),
      'remarks' => $validated['remarks'] ?? null,
      'created_by_user_id' => Auth::id(),
    ]);

    return redirect()->route('international-office.events.index')->with('success', 'Event created successfully.');
  }

  public function edit($id)
  {
    $event = InternationalOfficeEvent::findOrFail($id);

    $activityTypes = InternationalOfficeActivityTypeMaster::where('is_active', 1)
      ->orWhere('id', $event->activity_type_master_id)
      ->orderBy('sort_order')
      ->orderBy('title')
      ->get(['id', 'title']);

    $subjects = $this->siliguriSubjectsQuery()->get(['id', 'title', 'code']);

    return view('international-office.events.edit', [
      'event' => $event,
      'activityTypes' => $activityTypes,
      'subjects' => $subjects,
    ]);
  }

  public function update(Request $request, $id)
  {
    $event = InternationalOfficeEvent::findOrFail($id);
    $validated = $this->validatePayload($request, true);

    $mouDocumentPath = $event->mou_document_path;
    if ($request->boolean('has_mou') && $request->hasFile('mou_document')) {
      if ($mouDocumentPath) {
        Storage::disk('public')->delete($mouDocumentPath);
      }
      $mouDocumentPath = $request->file('mou_document')->store('international-office/events/mou', 'public');
    }

    if (!$request->boolean('has_mou') && $mouDocumentPath) {
      Storage::disk('public')->delete($mouDocumentPath);
      $mouDocumentPath = null;
    }

    $geotaggedPaths = is_array($event->geotagged_photo_paths) ? $event->geotagged_photo_paths : [];
    if ($request->hasFile('geotag_photos')) {
      foreach ($request->file('geotag_photos') as $photo) {
        $geotaggedPaths[] = $photo->store('international-office/events/geotagged', 'public');
      }
    }

    $visitPhotoPaths = is_array($event->visit_photo_paths) ? $event->visit_photo_paths : [];
    if ($request->hasFile('visit_photos')) {
      foreach ($request->file('visit_photos') as $photo) {
        $visitPhotoPaths[] = $photo->store('international-office/events/visit-photos', 'public');
      }
    }

    $event->update([
      'activity_type_master_id' => (int) $validated['activity_type_master_id'],
      'nature_of_activity' => $validated['nature_of_activity'],
      'department_scope' => $validated['department_scope'],
      'department_subject_ids' => array_map('intval', $validated['department_subject_ids']),
      'approval_type' => $validated['approval_type'],
      'visiting_institution_name' => $validated['visiting_institution_name'],
      'visiting_institution_contact' => $validated['visiting_institution_contact'] ?? null,
      'visiting_institution_email' => $validated['visiting_institution_email'] ?? null,
      'visiting_institution_address' => $validated['visiting_institution_address'] ?? null,
      'has_mou' => $request->boolean('has_mou'),
      'mou_document_path' => $mouDocumentPath,
      'trip_start_date' => $validated['trip_start_date'],
      'trip_end_date' => $validated['trip_end_date'],
      'geotagged_photo_paths' => !empty($geotaggedPaths) ? $geotaggedPaths : null,
      'visit_photo_paths' => !empty($visitPhotoPaths) ? $visitPhotoPaths : null,
      'members_json' => $this->normalizeMembers($validated['members'] ?? []),
      'remarks' => $validated['remarks'] ?? null,
    ]);

    return redirect()->route('international-office.events.index')->with('success', 'Event updated successfully.');
  }

  public function destroy($id)
  {
    $event = InternationalOfficeEvent::findOrFail($id);

    if ($event->mou_document_path) {
      Storage::disk('public')->delete($event->mou_document_path);
    }

    foreach ((array) $event->geotagged_photo_paths as $path) {
      if ($path) {
        Storage::disk('public')->delete($path);
      }
    }

    foreach ((array) $event->visit_photo_paths as $path) {
      if ($path) {
        Storage::disk('public')->delete($path);
      }
    }

    $event->delete();

    return redirect()->route('international-office.events.index')->with('success', 'Event deleted successfully.');
  }

  private function validatePayload(Request $request, bool $isUpdate = false): array
  {
    $validated = $request->validate([
      'activity_type_master_id' => 'required|integer|exists:international_office_activity_type_masters,id',
      'nature_of_activity' => 'required|in:student,faculty,both',
      'department_scope' => 'required|in:one,multiple',
      'department_subject_ids' => 'required|array|min:1',
      'department_subject_ids.*' => 'integer|exists:subjects,id',
      'approval_type' => 'required|in:personal,institutional',
      'visiting_institution_name' => 'required|string|max:255',
      'visiting_institution_contact' => 'nullable|string|max:100',
      'visiting_institution_email' => 'nullable|email|max:255',
      'visiting_institution_address' => 'nullable|string|max:1000',
      'has_mou' => 'nullable|boolean',
      'mou_document' => $isUpdate
        ? 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
        : 'required_if:has_mou,1|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
      'trip_start_date' => 'required|date',
      'trip_end_date' => 'required|date|after_or_equal:trip_start_date',
      'geotag_photos' => 'nullable|array',
      'geotag_photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
      'visit_photos' => 'nullable|array',
      'visit_photos.*' => 'image|mimes:jpg,jpeg,png|max:5120',
      'remarks' => 'nullable|string|max:1000',
      'members' => 'required|array|min:1',
      'members.*.name' => 'required|string|max:150',
      'members.*.designation' => 'nullable|string|max:150',
      'members.*.department' => 'nullable|string|max:150',
      'members.*.contact' => 'nullable|string|max:50',
      'members.*.email' => 'nullable|email|max:255',
    ]);

    $selectedSubjectIds = collect($validated['department_subject_ids'] ?? [])
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $allowedSubjectIds = $this->siliguriSubjectsQuery()
      ->whereIn('id', $selectedSubjectIds->all())
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->values();

    if ($selectedSubjectIds->count() !== $allowedSubjectIds->count()) {
      throw ValidationException::withMessages([
        'department_subject_ids' => 'Only Siliguri departments are allowed for International Office events.',
      ]);
    }

    return $validated;
  }

  private function normalizeMembers(array $members): array
  {
    return collect($members)
      ->map(function ($member) {
        return [
          'name' => trim((string) ($member['name'] ?? '')),
          'designation' => trim((string) ($member['designation'] ?? '')),
          'department' => trim((string) ($member['department'] ?? '')),
          'contact' => trim((string) ($member['contact'] ?? '')),
          'email' => trim((string) ($member['email'] ?? '')),
        ];
      })
      ->filter(fn($member) => $member['name'] !== '')
      ->values()
      ->all();
  }
}

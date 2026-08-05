<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\DsaStudentCouncilDocument;
use App\Models\DsaStudentCouncilMember;
use App\Models\DsaStudentCouncil;
use App\Models\DsaStudentCouncilMeeting;
use App\Models\StudentMaster;
use App\Services\Dean\CampusContextService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class StudentCouncilController extends Controller
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function index()
  {
    $campusId = $this->campusContext->campusId();
    $councilsQuery = DsaStudentCouncil::withCount(['members', 'meetings'])->latest();

    if ($campusId) {
      $councilsQuery->where('campus_id', $campusId);
    }

    $councils = $councilsQuery->paginate(20);

    $batches = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);

    return view('student-affairs.student-council.index', compact('councils', 'batches'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'academic_year' => 'required|string|max:30|exists:batch_masters,batch_name',
      'constituted_on' => 'nullable|date',
      'remarks' => 'nullable|string',
    ]);

    DsaStudentCouncil::create($validated + [
      'created_by' => auth()->id(),
      'campus_id' => $this->campusContext->campusId(),
    ]);

    return back()->with('success', 'Student council created.');
  }

  public function meetings(DsaStudentCouncil $council)
  {
    $campusId = $this->campusContext->campusId();
    if ($campusId && (int) $council->campus_id !== $campusId) {
      abort(403, 'Council does not belong to your assigned campus.');
    }

    $meetings = DsaStudentCouncilMeeting::with('documents')
      ->where('council_id', $council->id)
      ->latest('meeting_date')
      ->paginate(30);

    return view('student-affairs.student-council.meetings', compact('council', 'meetings'));
  }

  public function members(DsaStudentCouncil $council)
  {
    $campusId = $this->campusContext->campusId();
    if ($campusId && (int) $council->campus_id !== $campusId) {
      abort(403, 'Council does not belong to your assigned campus.');
    }

    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')->orderBy('first_name')->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    $membersQuery = $council->members()->with('student:id,first_name,last_name,roll_no')->latest();
    $this->campusContext->applyStudentRelationCampus($membersQuery, 'student');
    $members = $membersQuery->get();

    return view('student-affairs.student-council.members', compact('council', 'members', 'students'));
  }

  public function storeMember(Request $request, DsaStudentCouncil $council)
  {
    $campusId = $this->campusContext->campusId();
    if ($campusId && (int) $council->campus_id !== $campusId) {
      abort(403, 'Council does not belong to your assigned campus.');
    }

    $validated = $request->validate([
      'student_id' => 'required|integer|exists:student_masters,id',
      'role_title' => 'required|string|max:120',
      'role_slug' => 'nullable|string|max:60',
      'is_executive' => 'nullable|boolean',
      'appointed_on' => 'nullable|date',
      'ended_on' => 'nullable|date|after_or_equal:appointed_on',
      'status' => 'required|in:active,inactive,resigned,removed',
    ]);

    $studentQuery = StudentMaster::where('id', (int) $validated['student_id']);
    $this->campusContext->applyStudentCampus($studentQuery);
    if (!$studentQuery->exists()) {
      abort(403, 'Selected student is outside your assigned campus.');
    }

    $payload = [
      'council_id' => $council->id,
      'student_id' => (int) $validated['student_id'],
      'role_slug' => $validated['role_slug'] ?: strtolower(trim((string) str($validated['role_title'])->slug('-'))),
      'role_title' => $validated['role_title'],
      'is_executive' => (bool) ($validated['is_executive'] ?? false),
      'appointed_on' => $validated['appointed_on'] ?? null,
      'ended_on' => $validated['ended_on'] ?? null,
      'status' => $validated['status'],
    ];

    try {
      DsaStudentCouncilMember::create($payload);
    } catch (QueryException $exception) {
      return back()->withInput()->withErrors([
        'student_id' => 'This student is already assigned with the same role in this council.',
      ]);
    }

    return back()->with('success', 'Council member added successfully.');
  }

  public function storeMeeting(Request $request, DsaStudentCouncil $council)
  {
    $campusId = $this->campusContext->campusId();
    if ($campusId && (int) $council->campus_id !== $campusId) {
      abort(403, 'Council does not belong to your assigned campus.');
    }

    $validated = $request->validate([
      'meeting_no' => 'nullable|string|max:40',
      'title' => 'required|string|max:255',
      'meeting_date' => 'required|date',
      'start_time' => 'nullable|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'venue' => 'nullable|string|max:255',
      'agenda' => 'nullable|string',
      'minutes' => 'nullable|string',
      'resolutions' => 'nullable|string',
      'status' => 'required|in:scheduled,completed,cancelled,rescheduled',
      'minutes_pdf' => 'nullable|file|mimes:pdf|max:10240',
    ]);

    $meeting = DsaStudentCouncilMeeting::create([
      'council_id' => $council->id,
      'meeting_no' => $validated['meeting_no'] ?? null,
      'title' => $validated['title'],
      'meeting_date' => $validated['meeting_date'],
      'start_time' => $validated['start_time'] ?? null,
      'end_time' => $validated['end_time'] ?? null,
      'venue' => $validated['venue'] ?? null,
      'agenda' => $validated['agenda'] ?? null,
      'minutes' => $validated['minutes'] ?? null,
      'resolutions' => $validated['resolutions'] ?? null,
      'convened_by' => auth()->id(),
      'status' => $validated['status'],
    ]);

    if ($request->hasFile('minutes_pdf')) {
      $filePath = $request->file('minutes_pdf')->store('student-affairs/council-documents', 'public');

      DsaStudentCouncilDocument::create([
        'council_id' => $council->id,
        'meeting_id' => $meeting->id,
        'document_type' => 'minutes',
        'title' => 'Minutes - ' . $meeting->title,
        'file_path' => $filePath,
        'published_at' => now(),
        'uploaded_by' => auth()->id(),
      ]);
    }

    return back()->with('success', 'Meeting saved successfully.');
  }
}

<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\DsaDisciplineAction;
use App\Models\DsaDisciplineCase;
use App\Models\StudentMaster;
use App\Services\Dean\CampusContextService;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function index()
  {
    $casesQuery = DsaDisciplineCase::with([
      'student:id,first_name,last_name,roll_no',
      'actions' => function ($query) {
        $query->latest();
      }
    ])->latest();
    $this->campusContext->applyStudentRelationCampus($casesQuery, 'student');
    $cases = $casesQuery->paginate(25);

    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')->orderBy('first_name')->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    return view('student-affairs.discipline.index', compact('cases', 'students'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'student_id' => 'required|exists:student_masters,id',
      'summary' => 'required|string|max:500',
      'details' => 'nullable|string',
      'severity' => 'required|in:low,medium,high,critical',
      'incident_date' => 'nullable|date',
    ]);

    $validated['case_no'] = 'DISC-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    $validated['status'] = 'open';
    $validated['created_by'] = auth()->id();

    $allowedStudent = StudentMaster::where('id', (int) $validated['student_id']);
    $this->campusContext->applyStudentCampus($allowedStudent);
    if (!$allowedStudent->exists()) {
      abort(403, 'Selected student is outside your assigned campus.');
    }

    DsaDisciplineCase::create($validated);

    return back()->with('success', 'Discipline case created.');
  }

  public function storeAction(Request $request, DsaDisciplineCase $case)
  {
    $allowedCase = DsaDisciplineCase::where('id', $case->id);
    $this->campusContext->applyStudentRelationCampus($allowedCase, 'student');
    if (!$allowedCase->exists()) {
      abort(403, 'Case is outside your assigned campus.');
    }

    $validated = $request->validate([
      'action_type' => 'required|string|max:60',
      'action_from' => 'nullable|date',
      'action_to' => 'nullable|date|after_or_equal:action_from',
      'remarks' => 'nullable|string|max:2000',
      'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
      'status_after_action' => 'nullable|in:open,in_progress,resolved,closed,dropped',
    ]);

    $documentPath = null;
    if ($request->hasFile('document')) {
      $documentPath = $request->file('document')->store('student-affairs/discipline-actions', 'public');
    }

    DsaDisciplineAction::create([
      'discipline_case_id' => $case->id,
      'action_type' => $validated['action_type'],
      'action_from' => $validated['action_from'] ?? null,
      'action_to' => $validated['action_to'] ?? null,
      'remarks' => $validated['remarks'] ?? null,
      'document_path' => $documentPath,
      'issued_by' => auth()->id(),
    ]);

    if (!empty($validated['status_after_action'])) {
      $case->update(['status' => $validated['status_after_action']]);
    }

    return back()->with('success', 'Action taken updated successfully.');
  }

  public function updateStatus(Request $request, DsaDisciplineCase $case)
  {
    $allowedCase = DsaDisciplineCase::where('id', $case->id);
    $this->campusContext->applyStudentRelationCampus($allowedCase, 'student');
    if (!$allowedCase->exists()) {
      abort(403, 'Case is outside your assigned campus.');
    }

    $validated = $request->validate([
      'status' => 'required|in:open,in_progress,resolved,closed,dropped',
    ]);

    $case->update(['status' => $validated['status']]);

    return back()->with('success', 'Case status updated.');
  }
}

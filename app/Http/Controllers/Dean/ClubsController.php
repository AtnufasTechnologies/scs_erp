<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\DsaClub;
use App\Models\DsaClubMembership;
use App\Models\Faculty;
use App\Models\StudentMaster;
use App\Models\UserCampusSetting;
use App\Services\Dean\CampusContextService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubsController extends Controller
{
  public function __construct(protected CampusContextService $campusContext) {}

  public function index()
  {
    $clubsQuery = DsaClub::with(['coordinator'])
      ->withCount([
        'memberships as memberships_count' => function ($query) {
          $this->campusContext->applyStudentRelationCampus($query, 'student');
        }
      ])
      ->latest();

    $campusId = $this->campusContext->campusId();
    if ($campusId) {
      $campusUserIds = UserCampusSetting::where('campus_id', $campusId)->select('user_id');

      $clubsQuery->where(function ($query) use ($campusId, $campusUserIds) {
        $query->whereIn('created_by', $campusUserIds)
          ->orWhereHas('coordinator', function ($coordinatorQuery) use ($campusId) {
            $coordinatorQuery->where('CAMPUS_ID', $campusId);
          })
          ->orWhereHas('memberships.student', function ($memberQuery) use ($campusId) {
            $memberQuery->where('campus_id', $campusId);
          });
      });
    }

    $clubs = $clubsQuery->paginate(20);

    $facultyQuery = Faculty::select('id', 'USER_CODE', 'FIRST_NAME', 'LAST_NAME')
      ->where('IS_LEFT', 0)->orderBy('FIRST_NAME')->limit(1000);
    if ($campusId) {
      $facultyQuery->where('CAMPUS_ID', $campusId);
    }
    $faculty = $facultyQuery->get();

    return view('student-affairs.clubs.index', compact('clubs', 'faculty'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'club_type' => 'required|string|max:40',
      'faculty_coordinator_id' => 'nullable|integer',
      'description' => 'nullable|string',
      'established_on' => 'nullable|date',
    ]);

    $campusId = $this->campusContext->campusId();
    if ($campusId && !empty($validated['faculty_coordinator_id'])) {
      $coordinatorExists = Faculty::where('id', (int) $validated['faculty_coordinator_id'])
        ->where('CAMPUS_ID', $campusId)
        ->exists();

      if (!$coordinatorExists) {
        abort(403, 'Selected coordinator is outside your assigned campus.');
      }
    }

    DsaClub::create($validated + [
      'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
      'created_by' => auth()->id(),
    ]);

    return back()->with('success', 'Club/Cell/Association created.');
  }

  public function show(DsaClub $club)
  {
    $this->authorizeClubCampus($club);

    $membershipsQuery = $club->memberships()->with('student:id,first_name,last_name,roll_no')->latest();
    $this->campusContext->applyStudentRelationCampus($membershipsQuery, 'student');
    $memberships = $membershipsQuery->paginate(50);

    $studentsQuery = StudentMaster::select('id', 'first_name', 'last_name', 'roll_no')->orderBy('first_name')->limit(1000);
    $this->campusContext->applyStudentCampus($studentsQuery);
    $students = $studentsQuery->get();

    return view('student-affairs.clubs.show', compact('club', 'memberships', 'students'));
  }

  public function storeMember(Request $request, DsaClub $club)
  {
    $this->authorizeClubCampus($club);

    $validated = $request->validate([
      'student_id' => 'required|integer|exists:student_masters,id',
      'role_title' => 'required|string|max:80',
      'joined_on' => 'nullable|date',
      'left_on' => 'nullable|date|after_or_equal:joined_on',
      'status' => 'required|in:active,inactive,left,suspended',
    ]);

    $studentQuery = StudentMaster::where('id', (int) $validated['student_id']);
    $this->campusContext->applyStudentCampus($studentQuery);
    if (!$studentQuery->exists()) {
      abort(403, 'Selected student is outside your assigned campus.');
    }

    try {
      DsaClubMembership::create([
        'club_id' => $club->id,
        'student_id' => (int) $validated['student_id'],
        'role_title' => $validated['role_title'],
        'joined_on' => $validated['joined_on'] ?? null,
        'left_on' => $validated['left_on'] ?? null,
        'status' => $validated['status'],
      ]);
    } catch (QueryException $exception) {
      return back()->withInput()->withErrors([
        'student_id' => 'This student is already added to this club/cell.',
      ]);
    }

    return back()->with('success', 'Member added successfully.');
  }

  public function updateMember(Request $request, DsaClub $club, DsaClubMembership $membership)
  {
    $this->authorizeClubCampus($club);

    if ((int) $membership->club_id !== (int) $club->id) {
      abort(404);
    }

    $validated = $request->validate([
      'role_title' => 'required|string|max:80',
      'joined_on' => 'nullable|date',
      'left_on' => 'nullable|date|after_or_equal:joined_on',
      'status' => 'required|in:active,inactive,left,suspended',
    ]);

    $studentQuery = StudentMaster::where('id', (int) $membership->student_id);
    $this->campusContext->applyStudentCampus($studentQuery);
    if (!$studentQuery->exists()) {
      abort(403, 'Member is outside your assigned campus.');
    }

    $membership->update($validated);

    return back()->with('success', 'Member updated successfully.');
  }

  public function destroyMember(DsaClub $club, DsaClubMembership $membership)
  {
    $this->authorizeClubCampus($club);

    if ((int) $membership->club_id !== (int) $club->id) {
      abort(404);
    }

    $studentQuery = StudentMaster::where('id', (int) $membership->student_id);
    $this->campusContext->applyStudentCampus($studentQuery);
    if (!$studentQuery->exists()) {
      abort(403, 'Member is outside your assigned campus.');
    }

    $membership->delete();

    return back()->with('success', 'Member removed successfully.');
  }

  private function authorizeClubCampus(DsaClub $club): void
  {
    $campusId = $this->campusContext->campusId();
    if (!$campusId) {
      return;
    }

    $campusUserIds = UserCampusSetting::where('campus_id', $campusId)->select('user_id');

    $isAuthorized = DsaClub::query()
      ->where('id', $club->id)
      ->where(function ($query) use ($campusId, $campusUserIds) {
        $query->whereIn('created_by', $campusUserIds)
          ->orWhereHas('coordinator', function ($coordinatorQuery) use ($campusId) {
            $coordinatorQuery->where('CAMPUS_ID', $campusId);
          })
          ->orWhereHas('memberships.student', function ($memberQuery) use ($campusId) {
            $memberQuery->where('campus_id', $campusId);
          });
      })
      ->exists();

    if (!$isAuthorized) {
      abort(403, 'Club/cell does not belong to your assigned campus.');
    }
  }
}

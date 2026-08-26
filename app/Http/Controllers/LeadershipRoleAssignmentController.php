<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\LeadershipRoleAssignment;
use App\Models\RoleMaster;
use App\Models\Subject;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadershipRoleAssignmentController extends Controller
{
  public function index(Request $request)
  {
    $this->deactivateExpiredAssignments();

    $status = trim((string) $request->input('status', 'active'));
    $selectedRoleId = (int) $request->input('role_master_id', 0);

    $roleOptions = RoleMaster::query()
      ->where('is_active', 1)
      ->orderBy('role_name')
      ->get(['id', 'slug', 'role_name']);

    $facultyUsers = SubjectFacultyMaster::query()
      ->whereNotNull('access_id')
      ->with([
        'useraccess:id,name,email',
        'faculty:id,USER_CODE,FIRST_NAME,MIDDLE_NAME,LAST_NAME',
      ])
      ->get()
      ->filter(fn($item) => $item->useraccess !== null)
      ->unique('access_id')
      ->sortBy(fn($item) => strtolower(trim((string) ($item->useraccess->name ?? ''))))
      ->values();

    $query = LeadershipRoleAssignment::query()
      ->with([
        'user:id,name,email',
        'faculty:id,USER_CODE,FIRST_NAME,MIDDLE_NAME,LAST_NAME',
        'roleMaster:id,slug,role_name',
        'assignedByUser:id,name',
        'relievedByUser:id,name',
      ]);

    if ($selectedRoleId > 0) {
      $query->where('role_master_id', $selectedRoleId);
    }

    $today = now()->toDateString();

    if ($status === 'active') {
      $query
        ->where('is_active', 1)
        ->whereDate('effective_from', '<=', $today)
        ->where(function ($q) use ($today) {
          $q->whereNull('effective_to')
            ->orWhereDate('effective_to', '>=', $today);
        });
    } elseif ($status === 'scheduled') {
      $query
        ->where('is_active', 1)
        ->whereDate('effective_from', '>', $today);
    } elseif ($status === 'history') {
      $query->where(function ($q) use ($today) {
        $q->where('is_active', 0)
          ->orWhere(function ($subQ) use ($today) {
            $subQ->whereNotNull('effective_to')
              ->whereDate('effective_to', '<', $today);
          });
      });
    }

    $assignments = $query->latest('id')->paginate(40)->appends($request->query());

    return view('admin.user-manager.leadership-role-assignments', [
      'assignments' => $assignments,
      'roleOptions' => $roleOptions,
      'facultyUsers' => $facultyUsers,
      'status' => $status,
      'selectedRoleId' => $selectedRoleId,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'user_id' => 'required|integer|exists:users,id',
      'faculty_id' => 'nullable|integer|exists:faculties,id',
      'role_master_ids' => 'required|array|min:1',
      'role_master_ids.*' => 'required|integer|exists:role_masters,id',
      'assignment_scope' => 'nullable|string|max:120',
      'effective_from' => 'required|date',
      'effective_to' => 'nullable|date|after_or_equal:effective_from',
      'remarks' => 'nullable|string|max:1000',
      'replace_existing' => 'nullable|in:0,1',
    ]);

    $user = User::findOrFail((int) $validated['user_id']);
    $effectiveFrom = Carbon::parse((string) $validated['effective_from'])->toDateString();
    $effectiveTo = !empty($validated['effective_to'])
      ? Carbon::parse((string) $validated['effective_to'])->toDateString()
      : null;

    $scope = trim((string) ($validated['assignment_scope'] ?? ''));
    $scope = $scope === '' ? null : $scope;
    $replaceExisting = (string) ($validated['replace_existing'] ?? '0') === '1';

    DB::transaction(function () use ($validated, $user, $effectiveFrom, $effectiveTo, $scope, $replaceExisting) {
      $roleMasters = RoleMaster::query()
        ->whereIn('id', $validated['role_master_ids'])
        ->get(['id', 'slug', 'role_name']);

      foreach ($roleMasters as $roleMaster) {
        $roleSlug = strtolower(trim((string) $roleMaster->slug));
        if ($roleSlug === '') {
          continue;
        }

        if ($replaceExisting) {
          $this->closeActiveAssignmentsForRole(
            $roleMaster->id,
            $scope,
            $effectiveFrom,
            (int) $user->id
          );
        }

        LeadershipRoleAssignment::create([
          'user_id' => (int) $user->id,
          'faculty_id' => !empty($validated['faculty_id']) ? (int) $validated['faculty_id'] : null,
          'role_master_id' => (int) $roleMaster->id,
          'role_name' => $roleSlug,
          'assignment_scope' => $scope,
          'effective_from' => $effectiveFrom,
          'effective_to' => $effectiveTo,
          'is_active' => 1,
          'remarks' => $validated['remarks'] ?? null,
          'assigned_by' => Auth::id(),
        ]);

        $this->syncActiveRoleToUser((int) $user->id, $roleMaster);

        if ($roleSlug === 'hod') {
          $this->syncHodDepartmentMapping(
            (int) $user->id,
            !empty($validated['faculty_id']) ? (int) $validated['faculty_id'] : null
          );
        }
      }
    });

    return redirect()->back()->with('success', 'Leadership roles assigned successfully.');
  }

  public function relieve(Request $request, int $id)
  {
    $assignment = LeadershipRoleAssignment::findOrFail($id);

    $validated = $request->validate([
      'effective_to' => 'required|date|after_or_equal:' . $assignment->effective_from->format('Y-m-d'),
      'relieved_reason' => 'nullable|string|max:1000',
    ]);

    $effectiveTo = Carbon::parse((string) $validated['effective_to'])->toDateString();

    $assignment->effective_to = $effectiveTo;
    $assignment->is_active = 0;
    $assignment->relieved_by = Auth::id();
    $assignment->relieved_reason = $validated['relieved_reason'] ?? null;
    $assignment->save();

    $roleMaster = $assignment->roleMaster;
    if ($roleMaster) {
      $this->syncActiveRoleToUser((int) $assignment->user_id, $roleMaster);
    }

    return redirect()->back()->with('success', 'Role relieved and history updated successfully.');
  }

  private function closeActiveAssignmentsForRole(int $roleMasterId, ?string $scope, string $newFromDate, int $nextUserId): void
  {
    $query = LeadershipRoleAssignment::query()
      ->where('role_master_id', $roleMasterId)
      ->where('is_active', 1)
      ->whereDate('effective_from', '<=', $newFromDate)
      ->where(function ($q) use ($newFromDate) {
        $q->whereNull('effective_to')
          ->orWhereDate('effective_to', '>=', $newFromDate);
      });

    if ($scope === null) {
      $query->whereNull('assignment_scope');
    } else {
      $query->where('assignment_scope', $scope);
    }

    $activeAssignments = $query->get();

    foreach ($activeAssignments as $activeAssignment) {
      if ((int) $activeAssignment->user_id === $nextUserId) {
        continue;
      }

      $existingFrom = Carbon::parse((string) $activeAssignment->effective_from->format('Y-m-d'));
      $newFrom = Carbon::parse($newFromDate);

      $endDate = $existingFrom->lt($newFrom)
        ? $newFrom->copy()->subDay()->toDateString()
        : $newFrom->toDateString();

      $activeAssignment->effective_to = $endDate;
      $activeAssignment->is_active = 0;
      $activeAssignment->relieved_by = Auth::id();
      $activeAssignment->relieved_reason = 'Auto-relieved due to role reassignment.';
      $activeAssignment->save();

      $roleMaster = $activeAssignment->roleMaster;
      if ($roleMaster) {
        $this->syncActiveRoleToUser((int) $activeAssignment->user_id, $roleMaster);
      }
    }
  }

  private function deactivateExpiredAssignments(): void
  {
    $today = now()->toDateString();

    $expiredAssignments = LeadershipRoleAssignment::query()
      ->where('is_active', 1)
      ->whereNotNull('effective_to')
      ->whereDate('effective_to', '<', $today)
      ->with('roleMaster:id,slug,role_name')
      ->get();

    foreach ($expiredAssignments as $assignment) {
      $assignment->is_active = 0;
      $assignment->save();

      $roleMaster = $assignment->roleMaster;
      if ($roleMaster) {
        $this->syncActiveRoleToUser((int) $assignment->user_id, $roleMaster);
      }
    }
  }

  private function syncActiveRoleToUser(int $userId, RoleMaster $roleMaster): void
  {
    $roleSlug = strtolower(trim((string) $roleMaster->slug));
    if ($roleSlug === '') {
      return;
    }

    $today = now()->toDateString();

    $hasActiveAssignment = LeadershipRoleAssignment::query()
      ->where('user_id', $userId)
      ->where('role_master_id', (int) $roleMaster->id)
      ->where('is_active', 1)
      ->whereDate('effective_from', '<=', $today)
      ->where(function ($q) use ($today) {
        $q->whereNull('effective_to')
          ->orWhereDate('effective_to', '>=', $today);
      })
      ->exists();

    $hasRoleId = Schema::hasColumn('user_has_roles', 'role_id');
    $hasSource = Schema::hasColumn('user_has_roles', 'source');

    $criteria = [
      'user_id' => $userId,
      'role_name' => $roleSlug,
    ];

    if ($hasSource) {
      $criteria['source'] = 'leadership-assignment';
    }

    if ($hasRoleId) {
      $criteria['role_id'] = (int) $roleMaster->id;
    }

    if ($hasActiveAssignment) {
      UserHasRole::firstOrCreate($criteria);
      return;
    }

    $deleteQuery = UserHasRole::query()
      ->where('user_id', $userId)
      ->where('role_name', $roleSlug);

    if ($hasSource) {
      $deleteQuery->where('source', 'leadership-assignment');
    }

    if ($hasRoleId) {
      $deleteQuery->where(function ($q) use ($roleMaster) {
        $q->whereNull('role_id')
          ->orWhere('role_id', (int) $roleMaster->id);
      });
    }

    $deleteQuery->delete();
  }

  private function syncHodDepartmentMapping(int $userId, ?int $facultyId): void
  {
    $subjectId = 0;

    if ($facultyId !== null && $facultyId > 0) {
      $subjectId = (int) SubjectFacultyMaster::query()
        ->where('faculty_id', $facultyId)
        ->whereNotNull('subject_id')
        ->orderByDesc('id')
        ->value('subject_id');
    }

    if ($subjectId <= 0) {
      $subjectId = (int) SubjectFacultyMaster::query()
        ->where('access_id', $userId)
        ->whereNotNull('subject_id')
        ->orderByDesc('id')
        ->value('subject_id');
    }

    if ($subjectId <= 0) {
      return;
    }

    SubjectHasDeptAdmin::updateOrCreate(
      ['subject_id' => $subjectId],
      ['user_id' => $userId]
    );

    $campusId = (int) Subject::where('id', $subjectId)->value('campus_id');
    if ($campusId > 0) {
      UserCampusSetting::updateOrCreate(
        ['user_id' => $userId],
        ['campus_id' => $campusId]
      );
    }
  }
}

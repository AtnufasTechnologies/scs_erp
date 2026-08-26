<?php

use Carbon\Carbon;

?>

@include('includes.header')
@include('admin.sidebar')

<h3>Leadership Role Assignment Manager</h3>

<div class="row mb-3">
  <div class="col-lg-4">
    <button class="cst-button" style="--clr: #1d9bf0;" data-bs-toggle="modal" data-bs-target="#assignRoleModal">
      <span class="button-decor"></span>
      <div class="button-content">
        <div class="button__icon">
          <i class="fa fa-user-tag"></i>
        </div>
        <span class="button__text">Assign / Shift Roles</span>
      </div>
    </button>
  </div>
</div>

<form method="GET" class="row g-2 mb-3 align-items-end">
  <div class="col-md-3">
    <label class="form-label">Role</label>
    <select name="role_master_id" class="form-control">
      <option value="0">All Roles</option>
      @foreach ($roleOptions as $roleOption)
      <option value="{{ $roleOption->id }}" {{ (int) $selectedRoleId === (int) $roleOption->id ? 'selected' : '' }}>
        {{ $roleOption->role_name }}
      </option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-control">
      <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
      <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
      <option value="history" {{ $status === 'history' ? 'selected' : '' }}>History</option>
      <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100" type="submit">Apply</button>
  </div>
  <div class="col-md-2">
    <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">Reset</a>
  </div>
</form>

<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign / Shift Leadership Roles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" action="{{ route('admin.leadership-role-assignments.store') }}">
        @csrf
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Faculty User Account *</label>
              <select name="user_id" class="dselect-example" required>
                <option value="">Select faculty login</option>
                @foreach($facultyUsers as $facultyUser)
                @php
                $faculty = $facultyUser->faculty;
                $user = $facultyUser->useraccess;
                $facultyLabel = $faculty
                ? trim(((string) ($faculty->USER_CODE ?? '')) . ' - ' . ((string) ($faculty->FIRST_NAME ?? '')) . ' ' . ((string) ($faculty->MIDDLE_NAME ?? '')) . ' ' . ((string) ($faculty->LAST_NAME ?? '')))
                : '';
                @endphp
                <option value="{{ $user->id }}">
                  {{ $user->name }} ({{ $user->email }}) {{ $facultyLabel !== '' ? ' - ' . $facultyLabel : '' }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Faculty Master (optional link)</label>
              <select name="faculty_id" class="dselect-example">
                <option value="">Auto / Not linked</option>
                @foreach($facultyUsers as $facultyUser)
                @if($facultyUser->faculty)
                @php
                $faculty = $facultyUser->faculty;
                @endphp
                <option value="{{ $faculty->id }}">
                  {{ trim(((string) ($faculty->USER_CODE ?? '')) . ' - ' . ((string) ($faculty->FIRST_NAME ?? '')) . ' ' . ((string) ($faculty->MIDDLE_NAME ?? '')) . ' ' . ((string) ($faculty->LAST_NAME ?? ''))) }}
                </option>
                @endif
                @endforeach
              </select>
            </div>
          </div>

          <label class="form-label mt-3">Roles *</label>
          <select name="role_master_ids[]" class="dselect-example" multiple required>
            @foreach($roleOptions as $roleOption)
            <option value="{{ $roleOption->id }}">{{ $roleOption->role_name }} ({{ $roleOption->slug }})</option>
            @endforeach
          </select>

          <div class="row g-2 mt-2">
            <div class="col-md-4">
              <label class="form-label">Effective From *</label>
              <input type="date" name="effective_from" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Effective To</label>
              <input type="date" name="effective_to" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Scope (optional)</label>
              <input type="text" name="assignment_scope" class="form-control" placeholder="e.g. CSE Department">
            </div>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="replace_existing" name="replace_existing" value="1">
            <label class="form-check-label" for="replace_existing">
              Auto-relieve currently active holder for same role + scope
            </label>
          </div>

          <label class="form-label mt-3">Remarks</label>
          <textarea name="remarks" class="form-control" rows="2" placeholder="Transfer order reference, office note, etc."></textarea>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Assignment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body table-responsive">
    <table class="table table-bordered table-hover" id="exportTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Faculty Login</th>
          <th>Role</th>
          <th>Scope</th>
          <th>From</th>
          <th>To</th>
          <th>Status</th>
          <th>Assigned By</th>
          <th>Remarks</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($assignments as $index => $assignment)
        @php
        $today = Carbon::today();
        $starts = $assignment->effective_from ? Carbon::parse($assignment->effective_from) : null;
        $ends = $assignment->effective_to ? Carbon::parse($assignment->effective_to) : null;

        $liveNow = $assignment->is_active
        && $starts !== null
        && $starts->lte($today)
        && ($ends === null || $ends->gte($today));

        $future = $assignment->is_active && $starts !== null && $starts->gt($today);
        @endphp
        <tr>
          <td>{{ ($assignments->firstItem() ?? 0) + $index }}</td>
          <td>
            <div>{{ $assignment->user->name ?? 'N/A' }}</div>
            <small class="text-muted">{{ $assignment->user->email ?? '' }}</small>
          </td>
          <td>{{ $assignment->roleMaster->role_name ?? strtoupper($assignment->role_name) }}</td>
          <td>{{ $assignment->assignment_scope ?: 'General' }}</td>
          <td>{{ optional($assignment->effective_from)->format('Y-m-d') }}</td>
          <td>{{ optional($assignment->effective_to)->format('Y-m-d') ?: 'Open' }}</td>
          <td>
            @if($liveNow)
            <span class="badge bg-success">Active</span>
            @elseif($future)
            <span class="badge bg-info">Scheduled</span>
            @else
            <span class="badge bg-secondary">Closed</span>
            @endif
          </td>
          <td>{{ $assignment->assignedByUser->name ?? 'N/A' }}</td>
          <td>{{ $assignment->remarks ?: '-' }}</td>
          <td>
            @if($liveNow || $future)
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#relieveModal{{ $assignment->id }}">Relieve</button>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
        </tr>

        <div class="modal fade" id="relieveModal{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Relieve {{ $assignment->user->name ?? '' }} ({{ $assignment->roleMaster->role_name ?? $assignment->role_name }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form method="POST" action="{{ route('admin.leadership-role-assignments.relieve', $assignment->id) }}">
                @csrf
                <div class="modal-body">
                  <label class="form-label">Effective To *</label>
                  <input type="date" name="effective_to" class="form-control" value="{{ now()->format('Y-m-d') }}" required>

                  <label class="form-label mt-2">Reason</label>
                  <textarea name="relieved_reason" class="form-control" rows="2" placeholder="Transferred, tenure completed, etc."></textarea>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-danger">Confirm Relieve</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @empty
        <tr>
          <td colspan="10" class="text-center">No assignments found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt-2">
      {{ $assignments->links() }}
    </div>
  </div>
</div>

@include('includes.footer')
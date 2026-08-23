<?php

use App\Models\Campus;
use App\Models\RoleMaster;

$userTypes = RoleMaster::all();
$campusMaster = Campus::all();
?>

@include('includes.header')
@include('admin.sidebar')
<h3>User Access Management</h3>

<div class="row">
  <div class="col-lg-4">
    <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
      <span class="button-decor"></span>
      <div class="button-content">
        <div class="button__icon">
          <i class="fa fa-plus-circle"></i>
        </div>
        <span class="button__text">Add New</span>
      </div>
    </button>
  </div>
</div>

<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New User Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('add.newuser') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <label>Full Name *</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="Type Here..." required>

          <label>Login Email *</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="Type Here..." required>

          <label>Login Password (required only for brand-new email)</label>
          <input type="text" name="password" class="form-control mb-3" placeholder="Leave empty to assign roles to existing account">
          <div class="form-text mb-2">If email already exists, this form will only assign/append selected roles to that same login.</div>

          <label>Roles *</label>
          <select name="roles[]" class="dselect-example mb-3" multiple required>
            @foreach ($userTypes as $ut)
            <option value="{{ $ut->slug }}">{{ $ut->role_name }}</option>
            @endforeach
          </select>

          <label>Connect Faculty (Optional)</label>
          <select name="faculty_id" class="dselect-example mb-3">
            <option value="">Select Faculty</option>
            @foreach (($faculties ?? collect()) as $faculty)
            <option value="{{ $faculty->id }}">
              {{ trim(($faculty->USER_CODE ? $faculty->USER_CODE . ' - ' : '') . $faculty->FIRST_NAME . ' ' . $faculty->MIDDLE_NAME . ' ' . $faculty->LAST_NAME) }}
            </option>
            @endforeach
          </select>

          <label>Authorized For Campus</label>
          <select name="campus" class="form-control mb-3">
            <option value="">Select Campus</option>
            @foreach ($campusMaster as $cm)
            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if ($data->count())
<div class="row">
  <table class="table table-bordered" id="myTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Roles</th>
        <th>Connected Faculty</th>
        <th>Campus</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data as $itm)
      @php
      $facultyAccess = $itm->facultyAccesses->first();
      $connectedFaculty = $facultyAccess ? $facultyAccess->faculty : null;
      $connectedFacultyId = $connectedFaculty ? (int) $connectedFaculty->id : 0;
      @endphp
      <tr>
        <td>{{ ($data->firstItem() ?? 0) + $loop->index }}</td>
        <td>{{ $itm->name }}</td>
        <td>{{ $itm->email }}</td>
        <td><span class="badge badge-warning">{{ $itm->decrypted_password }}</span></td>
        <td>
          @php
          $roleNames = $itm->userroles->pluck('role_name')->filter()->values();
          @endphp
          {{ $roleNames->isNotEmpty() ? $roleNames->implode(', ') : 'N/A' }}
        </td>
        <td>
          @if ($connectedFaculty)
          {{ trim(($connectedFaculty->USER_CODE ? $connectedFaculty->USER_CODE . ' - ' : '') . $connectedFaculty->FIRST_NAME . ' ' . $connectedFaculty->MIDDLE_NAME . ' ' . $connectedFaculty->LAST_NAME) }}
          @else
          <span class="text-muted">Not Connected</span>
          @endif
        </td>
        <td>
          @if ($roleNames->isNotEmpty())
          @if($roleNames->contains('super-admin') || $roleNames->contains('principal'))
          All Access
          @else
          @if ($itm->campuspermission && $itm->campuspermission->campus)
          {{ $itm->campuspermission->campus->name }}
          @else
          No Campus
          @endif
          @endif
          @endif
        </td>
        <td>
          <button
            class="btn btn-primary btn-sm edit-user-btn"
            data-bs-toggle="modal"
            data-bs-target="#editUserModal"
            data-user-id="{{ $itm->id }}"
            data-name="{{ $itm->name }}"
            data-email="{{ $itm->email }}"
            data-roles='@json($roleNames->all())'
            data-campus-id="{{ $itm->campuspermission->campus_id ?? '' }}"
            data-faculty-id="{{ $connectedFacultyId > 0 ? $connectedFacultyId : '' }}">
            Edit
          </button>
          <a href="{{ route('admin.user-access.delete', $itm->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user access?');">Delete</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>



<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User Access</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm" action="" method="post">
        @csrf
        <div class="modal-body">
          <label>Full Name *</label>
          <input type="text" id="edit_name" name="name" class="form-control mb-3" required>

          <label>Login Email *</label>
          <input type="email" id="edit_email" name="email" class="form-control mb-3" required>

          <label>New Password (optional)</label>
          <input type="text" name="password" class="form-control mb-3" placeholder="Leave blank to keep current password">

          <label>Roles *</label>
          <select id="edit_roles" name="roles[]" class="dselect-example mb-3" multiple required>
            @foreach ($userTypes as $ut)
            <option value="{{ $ut->slug }}">{{ $ut->role_name }}</option>
            @endforeach
          </select>

          <label>Connect Faculty (Optional)</label>
          <select id="edit_faculty_id" name="faculty_id" class="dselect-example mb-3">
            <option value="">Select Faculty</option>
            @foreach (($faculties ?? collect()) as $facultyItem)
            <option value="{{ $facultyItem->id }}">
              {{ trim(($facultyItem->USER_CODE ? $facultyItem->USER_CODE . ' - ' : '') . $facultyItem->FIRST_NAME . ' ' . $facultyItem->MIDDLE_NAME . ' ' . $facultyItem->LAST_NAME) }}
            </option>
            @endforeach
          </select>

          <label>Authorized For Campus</label>
          <select id="edit_campus" name="campus" class="form-control mb-3">
            <option value="">Select Campus</option>
            @foreach ($campusMaster as $cm)
            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    const nameInput = document.getElementById('edit_name');
    const emailInput = document.getElementById('edit_email');
    const rolesSelect = document.getElementById('edit_roles');
    const facultySelect = document.getElementById('edit_faculty_id');
    const campusSelect = document.getElementById('edit_campus');
    const routeTemplate = '{{ route("admin.user-access.update", "__ID__") }}';

    document.querySelectorAll('.edit-user-btn').forEach(function(button) {
      button.addEventListener('click', function() {
        const userId = button.getAttribute('data-user-id') || '';
        form.action = routeTemplate.replace('__ID__', userId);
        nameInput.value = button.getAttribute('data-name') || '';
        emailInput.value = button.getAttribute('data-email') || '';
        const roleValues = JSON.parse(button.getAttribute('data-roles') || '[]');
        Array.from(rolesSelect.options).forEach(function(option) {
          option.selected = roleValues.includes(option.value);
        });
        facultySelect.value = button.getAttribute('data-faculty-id') || '';
        campusSelect.value = button.getAttribute('data-campus-id') || '';
      });
    });
  });
</script>
@else
<p class="display-4 text-center">No users found.</p>
@endif

@include('includes.footer')
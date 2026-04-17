@include('includes.header')

@include('admin.sidebar')
<h3>Role Master</h3>
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addRoleModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New Role</span>
  </div>
</button>

{{-- Add Role Modal --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addRoleLabel">New Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.add.role') }}" method="post">
        @csrf
        <div class="modal-body">
          <input type="text" class="form-control mb-3" name="role_name" placeholder="Role Name" required>
          <input type="text" class="form-control mb-3" name="description" placeholder="Description (optional)">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Role Modal --}}
@foreach ($data as $dt)
<div class="modal fade" id="editRoleModal{{ $dt->id }}" tabindex="-1" aria-labelledby="editRoleLabel{{ $dt->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editRoleLabel{{ $dt->id }}">Edit Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.update.role', $dt->id) }}" method="post">
        @csrf
        <div class="modal-body">
          <input type="text" class="form-control mb-3" name="role_name" value="{{ $dt->role_name }}" placeholder="Role Name" required>
          <input type="text" class="form-control mb-3" name="description" value="{{ $dt->description }}" placeholder="Description (optional)">
          <select name="is_active" class="form-control mb-3">
            <option value="1" {{ $dt->is_active ? 'selected' : '' }}>Active</option>
            <option value="0" {{ !$dt->is_active ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<table class="table table-bordered" id="exportTable">
  <thead>
    <tr>
      <th>#</th>
      <th>Slug</th>
      <th>Role Name</th>
      <th>Description</th>
      <th>Status</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($data as $key => $dt)
    <tr>
      <td>{{ $key + 1 }}</td>
      <td>{{ $dt->slug }}</td>
      <td>{{ $dt->role_name }}</td>
      <td>{{ $dt->description ?? '-' }}</td>
      <td>
        @if($dt->is_active)
        <span class="badge bg-success">Active</span>
        @else
        <span class="badge bg-danger">Inactive</span>
        @endif
      </td>
      <td>{{ date('d-M-Y', strtotime($dt->created_at)) }}</td>
      <td>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $dt->id }}">
          <i class="fa fa-edit"></i>
        </button>
        <a href="{{ route('admin.delete.role', $dt->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">
          <i class="fa fa-trash"></i>
        </a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@include('includes.footer')
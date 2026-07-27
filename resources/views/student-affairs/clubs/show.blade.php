@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>{{ $club->name }} Members</h3>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Add Member</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.clubs.members.store', $club->id) }}" class="row g-2">
            @csrf
            <div class="col-md-4">
              <select name="student_id" class="dselect-example" required>
                <option value="">Select Student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}" {{ (int) old('student_id') === (int) $student->id ? 'selected' : '' }}>
                  {{ $student->roll_no ?: 'No Roll No' }} - {{ $student->first_name }} {{ $student->last_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2"><input name="role_title" class="form-control" placeholder="Role" value="{{ old('role_title', 'Member') }}" required></div>
            <div class="col-md-2"><input type="date" name="joined_on" class="form-control" value="{{ old('joined_on') }}"></div>
            <div class="col-md-2"><input type="date" name="left_on" class="form-control" value="{{ old('left_on') }}"></div>
            <div class="col-md-1">
              <select name="status" class="form-select" required>
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="left" {{ old('status') === 'left' ? 'selected' : '' }}>Left</option>
                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
              </select>
            </div>
            <div class="col-md-1"><button class="btn btn-primary w-100">Add</button></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Memberships</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Student</th>
                <th>Roll</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Left</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($memberships as $m)
              <tr>
                <td>{{ $m->student->first_name ?? '' }} {{ $m->student->last_name ?? '' }}</td>
                <td>{{ $m->student->roll_no ?? '-' }}</td>
                <td>
                  <form method="POST" action="{{ route('dean.clubs.members.update', [$club->id, $m->id]) }}" class="d-flex gap-2 align-items-center flex-wrap">
                    @csrf
                    @method('PUT')
                    <input type="text" name="role_title" class="form-control form-control-sm" value="{{ $m->role_title }}" style="min-width: 120px; max-width: 160px;" required>
                </td>
                <td><input type="date" name="joined_on" class="form-control form-control-sm" value="{{ optional($m->joined_on)->format('Y-m-d') }}"></td>
                <td><input type="date" name="left_on" class="form-control form-control-sm" value="{{ optional($m->left_on)->format('Y-m-d') }}"></td>
                <td>
                  <select name="status" class="form-select form-select-sm" style="min-width: 120px;" required>
                    <option value="active" {{ $m->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $m->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="left" {{ $m->status === 'left' ? 'selected' : '' }}>Left</option>
                    <option value="suspended" {{ $m->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                  </select>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-success" type="submit">Update</button>
                  </form>
                  <form method="POST" action="{{ route('dean.clubs.members.destroy', [$club->id, $m->id]) }}" class="d-inline" onsubmit="return confirm('Remove this member from the club/cell?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No members found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $memberships->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
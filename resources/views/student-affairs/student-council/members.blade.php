@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Council Members: {{ $council->title }}</h3>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Add Member</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.student-council.members.store', $council->id) }}" class="row g-2">
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
            <div class="col-md-2"><input name="role_title" class="form-control" placeholder="Role Title" value="{{ old('role_title') }}" required></div>
            <div class="col-md-2"><input name="role_slug" class="form-control" placeholder="role-slug" value="{{ old('role_slug') }}"></div>
            <div class="col-md-1">
              <select name="is_executive" class="form-select">
                <option value="0" {{ old('is_executive') == '0' ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('is_executive') == '1' ? 'selected' : '' }}>Yes</option>
              </select>
            </div>
            <div class="col-md-2"><input type="date" name="appointed_on" class="form-control" value="{{ old('appointed_on') }}"></div>
            <div class="col-md-1">
              <button class="btn btn-primary w-100">Add</button>
            </div>
            <div class="col-md-2">
              <select name="status" class="form-select" required>
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="resigned" {{ old('status') === 'resigned' ? 'selected' : '' }}>Resigned</option>
                <option value="removed" {{ old('status') === 'removed' ? 'selected' : '' }}>Removed</option>
              </select>
            </div>
            <div class="col-md-2"><input type="date" name="ended_on" class="form-control" value="{{ old('ended_on') }}" placeholder="End Date"></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Members</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered" id="exportTable">
            <thead>
              <tr>
                <th>#</th>
                <th>RollNo</th>
                <th>Student</th>
                <th>Roll</th>
                <th>Role</th>
                <th>Executive</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($members as $member)
              <tr>
                <td>{{$loop->iteration}}</td>
                <td> {{ $member->student->roll_no ?? 'No Roll No' }}</td>
                <td>{{ $member->student->first_name ?? '' }} {{ $member->student->last_name ?? '' }}</td>
                <td>{{ $member->student->roll_no ?? '-' }}</td>
                <td>{{ $member->role_title }}</td>
                <td>{{ $member->is_executive ? 'Yes' : 'No' }}</td>
                <td>{{ $member->status }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No members added.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
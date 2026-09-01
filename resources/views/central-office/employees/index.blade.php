@include('includes.header')
@include('central-office.sidebar')

<div class="container-fluid">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Central Office</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('central-office.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('central-office.employees.index') }}" class="row g-2">
          <div class="col-md-3">
            <label class="form-label">Department (Subjects)</label>
            <select class="form-select" name="department_id">
              <option value="0">All Departments</option>
              @foreach($departments as $department)
              <option value="{{ $department->id }}" {{ (int) $departmentId === (int) $department->id ? 'selected' : '' }}>
                {{ $department->title }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
              <option value="left" {{ $status === 'left' ? 'selected' : '' }}>Left</option>
              <option value="deleted" {{ $status === 'deleted' ? 'selected' : '' }}>Deleted</option>
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Name / Employee code / Email / Mobile">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Apply</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Employees</h6>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary">Total in Siliguri Campus: {{ (int) ($totalEmployeesCollege ?? 0) }}</span>
          <a href="{{ route('central-office.employees.export', ['department_id' => $departmentId, 'status' => $status, 'search' => $search]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-csv me-1"></i>Export CSV
          </a>
          <span class="badge bg-info">{{ $employees->total() }} records</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Emp Code</th>
                <th>Name</th>
                <th>Joining Date</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Department (Subjects)</th>
                <th>Deanery</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($employees as $index => $employee)
              @php
              $statusLabel = 'Active';
              $statusClass = 'success';
              if ($employee->deleted_at) {
              $statusLabel = 'Deleted';
              $statusClass = 'secondary';
              } elseif ((int) ($employee->IS_LEFT ?? 0) === 1) {
              $statusLabel = 'Left';
              $statusClass = 'warning';
              }
              $joiningDate = 'N/A';
              if (!empty($employee->DOJ)) {
              $joiningTimestamp = strtotime((string) $employee->DOJ);
              if ($joiningTimestamp !== false) {
              $joiningDate = date('d M Y', $joiningTimestamp);
              }
              }
              $departmentText = collect($employee->subject_departments ?? [])->filter()->implode(', ');
              $deaneryText = collect($employee->deanery_names ?? [])->filter()->implode(', ');
              @endphp
              <tr>
                <td>{{ $employees->firstItem() + $index }}</td>
                <td>{{ $employee->USER_CODE ?: 'N/A' }}</td>
                <td>{{ trim(($employee->FIRST_NAME ?? '') . ' ' . ($employee->MIDDLE_NAME ?? '') . ' ' . ($employee->LAST_NAME ?? '')) ?: 'N/A' }}</td>
                <td>{{ $joiningDate }}</td>
                <td>{{ $employee->MAIL_ID ?: 'N/A' }}</td>
                <td>{{ $employee->MOBILE_NO ?: 'N/A' }}</td>
                <td>{{ $departmentText !== '' ? $departmentText : 'N/A' }}</td>
                <td>{{ $deaneryText !== '' ? $deaneryText : 'N/A' }}</td>
                <td>{{ $employee->designation ?: 'N/A' }}</td>
                <td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                <td>
                  @if(!$employee->deleted_at)
                  <form action="{{ route('central-office.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Delete this faculty record? This cannot be undone from Central Office panel.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                  @else
                  <span class="text-muted small">Already deleted</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="11" class="text-center py-4">No employees found for the selected filters.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white">
        {{ $employees->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
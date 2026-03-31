@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')

  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Exam Registrations Management</h1>
      <p class="fs-6 mb-0 text-dark">Manage student exam registrations, approvals, and tracking</p>
    </div>
  </div>

  <div class="container-fluid">
    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filters</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.exam-registrations.index') }}" method="GET">
          <div class="row g-3">
            <div class="col-md-3">
              <label for="exam_session_id" class="form-label">Exam Session</label>
              <select name="exam_session_id" id="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($examSessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} ({{ $session->program_type }})
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="campus_id" class="form-label">Campus</label>
              <select name="campus_id" id="campus_id" class="form-select">
                <option value="">All Campuses</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                  {{ $campus->name }}
                </option>
                @endforeach
              </select>
            </div>



            <div class="col-md-3">
              <label for="is_backlog" class="form-label">Type</label>
              <select name="is_backlog" id="is_backlog" class="form-select">
                <option value="">All Types</option>
                <option value="0" {{ request('is_backlog') === '0' ? 'selected' : '' }}>Regular</option>
                <option value="1" {{ request('is_backlog') === '1' ? 'selected' : '' }}>Backlog</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="search" class="form-label">Search Student</label>
              <input type="text" name="search" id="search" class="form-control"
                placeholder="Search by name, reg no, roll no..."
                value="{{ request('search') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">
                <i class="fa fa-search"></i> Search
              </button>
              <a href="{{ route('admin.exam-registrations.index') }}" class="btn btn-secondary">
                <i class="fa fa-refresh"></i> Reset
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <a href="{{ route('admin.exam-registrations.create') }}" class="btn btn-success">
          <i class="fa fa-plus"></i> New Registration
        </a>
      </div>
      <div>
        <button type="button" class="btn btn-primary" id="bulkApproveBtn" disabled>
          <i class="fa fa-check"></i> Approve Selected
        </button>
        <button type="button" class="btn btn-danger" id="bulkRejectBtn" disabled>
          <i class="fa fa-times"></i> Reject Selected
        </button>
      </div>
    </div>

    <!-- Registrations Table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="table-dark">
              <tr>
                <th width="3%">
                  <input type="checkbox" id="selectAll" class="form-check-input">
                </th>
                <th width="5%">#</th>
                <th>Student Details</th>
                <th>Exam Session</th>
                <th>Program Type</th>
                <th>Type</th>
                <th>Status</th>
                <th>Registered At</th>
                <th width="12%">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($registrations as $registration)
              <tr>
                <td>
                  <input type="checkbox" class="form-check-input registration-checkbox"
                    value="{{ $registration->id }}"
                    data-status="{{ $registration->status }}">
                </td>
                <td>{{ $loop->iteration + ($registrations->currentPage() - 1) * $registrations->perPage() }}</td>
                <td>
                  <div>
                    <strong class="text-capitalize">
                      {{ $registration->student->first_name ?? '' }}
                      {{ $registration->student->last_name ?? '' }}
                    </strong>
                  </div>
                  <small class="text-muted">
                    Reg: {{ $registration->student->register_no ?? 'N/A' }} |
                    Roll: {{ $registration->student->roll_no ?? 'N/A' }}
                  </small><br>
                  <small class="text-muted">
                    <i class="fa fa-building"></i> {{ $registration->student->campusmaster->name ?? 'N/A' }}
                  </small>
                </td>
                <td>
                  <strong>{{ $registration->examSession->name ?? 'N/A' }}</strong><br>
                  <small class="text-muted">{{ $registration->examSession->academic_year ?? '' }} | Sem {{ $registration->examSession->semester ?? '' }}</small>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ $registration->program_type ?? 'N/A' }}</span>
                </td>
                <td>
                  @if($registration->is_backlog)
                  <span class="badge bg-warning">Backlog</span>
                  @else
                  <span class="badge bg-info">Regular</span>
                  @endif
                </td>
                <td>
                  @if($registration->status == 'pending')
                  <span class="badge bg-warning">Pending</span>
                  @elseif($registration->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($registration->status == 'rejected')
                  <span class="badge bg-danger">Rejected</span>
                  @else
                  <span class="badge bg-secondary">Cancelled</span>
                  @endif
                </td>
                <td>{{ $registration->registered_at ? $registration->registered_at->format('d M Y') : 'N/A' }}</td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="{{ route('admin.exam-registrations.show', $registration->id) }}"
                      class="btn btn-sm btn-info" title="View">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.exam-registrations.edit', $registration->id) }}"
                      class="btn btn-sm btn-primary" title="Edit">
                      <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.exam-registrations.destroy', $registration->id) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this registration?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                  <p class="text-muted">No exam registrations found</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $registrations->appends(request()->query())->links() }}
        </div>
      </div>
    </div>
  </div>


  <!-- Bulk Action Forms -->
  <form id="bulkApproveForm" action="{{ route('admin.exam-registrations.bulk-approve') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="registration_ids" id="approveIds">
  </form>

  <form id="bulkRejectForm" action="{{ route('admin.exam-registrations.bulk-reject') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="registration_ids" id="rejectIds">
  </form>
</div>
@include('includes.footer')

<script>
  // Select all checkboxes
  document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.registration-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.checked = this.checked;
    });
    updateBulkButtons();
  });

  // Update bulk buttons state
  document.querySelectorAll('.registration-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
  });

  function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
    const approveBtn = document.getElementById('bulkApproveBtn');
    const rejectBtn = document.getElementById('bulkRejectBtn');

    if (checkedBoxes.length > 0) {
      approveBtn.disabled = false;
      rejectBtn.disabled = false;
    } else {
      approveBtn.disabled = true;
      rejectBtn.disabled = true;
    }
  }

  // Bulk approve
  document.getElementById('bulkApproveBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);

    if (confirm(`Are you sure you want to approve ${ids.length} registration(s)?`)) {
      document.getElementById('approveIds').value = JSON.stringify(ids);
      document.getElementById('bulkApproveForm').submit();
    }
  });

  // Bulk reject
  document.getElementById('bulkRejectBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);

    if (confirm(`Are you sure you want to reject ${ids.length} registration(s)?`)) {
      document.getElementById('rejectIds').value = JSON.stringify(ids);
      document.getElementById('bulkRejectForm').submit();
    }
  });
</script>
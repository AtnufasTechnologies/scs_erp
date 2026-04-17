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

            <div class="col-md-2">
              <label for="attendance_clearance" class="form-label">Attendance</label>
              <select name="attendance_clearance" id="attendance_clearance" class="form-select">
                <option value="">All</option>
                <option value="cleared" {{ request('attendance_clearance') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                <option value="not_cleared" {{ request('attendance_clearance') == 'not_cleared' ? 'selected' : '' }}>Not Cleared</option>
                <option value="pending" {{ request('attendance_clearance') == 'pending' ? 'selected' : '' }}>Pending</option>
              </select>
            </div>

            <div class="col-md-2">
              <label for="library_clearance" class="form-label">Library</label>
              <select name="library_clearance" id="library_clearance" class="form-select">
                <option value="">All</option>
                <option value="cleared" {{ request('library_clearance') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                <option value="not_cleared" {{ request('library_clearance') == 'not_cleared' ? 'selected' : '' }}>Not Cleared</option>
                <option value="pending" {{ request('library_clearance') == 'pending' ? 'selected' : '' }}>Pending</option>
              </select>
            </div>

            <div class="col-md-2">
              <label for="fees_clearance" class="form-label">Fees</label>
              <select name="fees_clearance" id="fees_clearance" class="form-select">
                <option value="">All</option>
                <option value="cleared" {{ request('fees_clearance') == 'cleared' ? 'selected' : '' }}>Cleared</option>
                <option value="not_cleared" {{ request('fees_clearance') == 'not_cleared' ? 'selected' : '' }}>Not Cleared</option>
                <option value="pending" {{ request('fees_clearance') == 'pending' ? 'selected' : '' }}>Pending</option>
              </select>
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
        <button type="button" class="btn btn-warning" id="checkClearancesBtn" disabled>
          <i class="fa fa-refresh"></i> Check Clearances
        </button>
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
                <th>Subjects</th>
                <th>Attendance</th>
                <th>Library</th>
                <th>Fees</th>
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
                  @if($registration->registrationSubjects && $registration->registrationSubjects->count())
                  @php
                  $regularSubjects = $registration->registrationSubjects->where('is_backlog', false);
                  $backlogSubjects = $registration->registrationSubjects->where('is_backlog', true);
                  @endphp
                  @foreach($regularSubjects as $rs)
                  <span class="badge bg-primary mb-1" title="{{ $rs->examSubject->master->name ?? '' }}">
                    {{ $rs->examSubject->master->subject_code ?? 'N/A' }}
                  </span>
                  @endforeach
                  @foreach($backlogSubjects as $rs)
                  <span class="badge bg-danger mb-1" title="{{ $rs->examSubject->master->name ?? '' }} (Backlog)">
                    {{ $rs->examSubject->master->subject_code ?? 'N/A' }} <i class="fa fa-repeat"></i>
                  </span>
                  @endforeach
                  <br><small class="text-muted">{{ $regularSubjects->count() }} regular{{ $backlogSubjects->count() > 0 ? ', ' . $backlogSubjects->count() . ' backlog' : '' }}</small>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($registration->attendance_clearance == 'cleared')
                  <span class="badge bg-success" title="Attendance: {{ $registration->attendance_percentage ?? 0 }}%">
                    <i class="fa fa-check-circle"></i> Cleared
                  </span>
                  @if($registration->attendance_percentage)
                  <br><small class="text-muted">{{ $registration->attendance_percentage }}%</small>
                  @endif
                  @elseif($registration->attendance_clearance == 'not_cleared')
                  <span class="badge bg-danger" title="Attendance: {{ $registration->attendance_percentage ?? 0 }}%">
                    <i class="fa fa-times-circle"></i> Not Cleared
                  </span>
                  @if($registration->attendance_percentage)
                  <br><small class="text-danger">{{ $registration->attendance_percentage }}%</small>
                  @endif
                  @else
                  <span class="badge bg-secondary"><i class="fa fa-clock-o"></i> Pending</span>
                  @endif
                </td>
                <td>
                  @if($registration->library_clearance == 'cleared')
                  <span class="badge bg-success"><i class="fa fa-check-circle"></i> Cleared</span>
                  @elseif($registration->library_clearance == 'not_cleared')
                  <span class="badge bg-danger"><i class="fa fa-times-circle"></i> Not Cleared</span>
                  @else
                  <span class="badge bg-secondary"><i class="fa fa-clock-o"></i> Pending</span>
                  @endif
                  <br>
                  <a href="javascript:void(0)" class="text-primary small update-clearance-link"
                    data-id="{{ $registration->id }}" data-field="library_clearance"
                    data-current="{{ $registration->library_clearance }}">
                    <i class="fa fa-edit"></i> Update
                  </a>
                </td>
                <td>
                  @if($registration->fees_clearance == 'cleared')
                  <span class="badge bg-success"><i class="fa fa-check-circle"></i> Cleared</span>
                  @elseif($registration->fees_clearance == 'not_cleared')
                  <span class="badge bg-danger"><i class="fa fa-times-circle"></i> Not Cleared</span>
                  @else
                  <span class="badge bg-secondary"><i class="fa fa-clock-o"></i> Pending</span>
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
                <td colspan="13" class="text-center py-5">
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

  <form id="checkClearancesForm" action="{{ route('admin.exam-registrations.check-clearances') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="registration_ids" id="clearanceIds">
  </form>

  <!-- Clearance Update Modal -->
  <div class="modal fade" id="clearanceModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Clearance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="clearanceRegId">
          <input type="hidden" id="clearanceField">
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select id="clearanceValue" class="form-select">
              <option value="pending">Pending</option>
              <option value="cleared">Cleared</option>
              <option value="not_cleared">Not Cleared</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Remarks (optional)</label>
            <textarea id="clearanceRemarks" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveClearanceBtn">Save</button>
        </div>
      </div>
    </div>
  </div>
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
    const clearanceBtn = document.getElementById('checkClearancesBtn');

    if (checkedBoxes.length > 0) {
      approveBtn.disabled = false;
      rejectBtn.disabled = false;
      clearanceBtn.disabled = false;
    } else {
      approveBtn.disabled = true;
      rejectBtn.disabled = true;
      clearanceBtn.disabled = true;
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

  // Check clearances
  document.getElementById('checkClearancesBtn').addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.registration-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);

    if (confirm(`Check attendance & fees clearances for ${ids.length} registration(s)?`)) {
      document.getElementById('clearanceIds').value = JSON.stringify(ids);
      document.getElementById('checkClearancesForm').submit();
    }
  });

  // Update clearance link click
  document.querySelectorAll('.update-clearance-link').forEach(link => {
    link.addEventListener('click', function() {
      document.getElementById('clearanceRegId').value = this.dataset.id;
      document.getElementById('clearanceField').value = this.dataset.field;
      document.getElementById('clearanceValue').value = this.dataset.current;
      document.getElementById('clearanceRemarks').value = '';
      new bootstrap.Modal(document.getElementById('clearanceModal')).show();
    });
  });

  // Save clearance update
  document.getElementById('saveClearanceBtn').addEventListener('click', function() {
    const regId = document.getElementById('clearanceRegId').value;
    const field = document.getElementById('clearanceField').value;
    const value = document.getElementById('clearanceValue').value;
    const remarks = document.getElementById('clearanceRemarks').value;

    fetch(`{{ url('/admin/exam-registrations') }}/${regId}/update-clearance`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          field,
          value,
          remarks
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || 'Failed to update clearance');
        }
      })
      .catch(error => {
        alert('Error updating clearance');
      });
  });
</script>
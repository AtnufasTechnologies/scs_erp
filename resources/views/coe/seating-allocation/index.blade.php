@include('includes.header')
<style>
  .seating-allocation-page .card-header {
    padding: 0.5rem 0.75rem;
  }

  .seating-allocation-page .card-body {
    padding: 0.75rem;
  }

  .seating-allocation-page .btn {
    padding: 0.25rem 0.6rem;
    font-size: 0.85rem;
  }

  .seating-allocation-page .form-label {
    margin-bottom: 0.2rem;
    font-size: 0.85rem;
  }

  .seating-allocation-page .form-select,
  .seating-allocation-page .form-control {
    padding: 0.3rem 0.5rem;
    font-size: 0.875rem;
    min-height: calc(1.5em + 0.6rem + 2px);
  }

  .seating-allocation-page .table td,
  .seating-allocation-page .table th {
    padding: 0.4rem 0.5rem;
  }

  .seating-allocation-page .alert {
    margin-bottom: 0.65rem;
  }

  .seating-allocation-page .modal-content .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }
</style>

<div class="wrapper seating-allocation-page">
  @include('coe.sidebar')

  <div class="p-3  ">
    <div class="py-2 card">
      <h3 class="mb-1">Seating Allocation Management</h3>
      @if(!empty($selectedExamLabel))
      <p class="mb-1">
        <span class="badge badge-warning text-light px-2 py-1 mb-2">
          Selected Exam: {{ $selectedExamLabel }}
        </span>
      </p>
      @endif
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('admin.seating-allocation.exam-sessions') }}" class="btn btn-dark">
            <i class="fa fa-clock"></i> Exam Sessions
          </a>
          <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateSeatingModal">
            <i class="fa fa-magic"></i> Generate Seating
          </button>
          <form action="{{ route('admin.seating-allocation.finalize-drafts') }}" method="POST" class="d-inline">
            @csrf
            @if(request()->filled('exam_master_id'))
            <input type="hidden" name="exam_master_id" value="{{ (int) request('exam_master_id') }}">
            @endif
            @if(request()->filled('exam_id'))
            <input type="hidden" name="exam_id" value="{{ (int) request('exam_id') }}">
            @endif
            @if(request()->filled('operational_session_id'))
            <input type="hidden" name="operational_session_id" value="{{ (int) request('operational_session_id') }}">
            @endif
            <button type="submit" class="btn btn-warning" onclick="return confirm('Finalize all draft seating allocations for current exam context?')">
              <i class="fa fa-check"></i> Finalize Drafts
            </button>
          </form>
          <a href="{{ route('admin.seating-allocation.create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> Manual Allocation
          </a>
        </div>
        <a href="{{ route('admin.seating-allocation.export', ['exam_id' => request('exam_id'), 'room_id' => request('room_id'), 'exam_master_id' => request('exam_master_id')]) }}"
          class="btn btn-info">
          <i class="fa fa-download"></i> Export
        </a>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
      <i class="fa fa-check-circle"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
      <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Control Panel -->

    <div class="row">
      <div class="col-lg-6">
        <div class="card shadow-sm mb-3">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-sliders"></i> Filters & Quick Actions</h5>
          </div>
          <div class="card-body py-3">
            <form action="{{ route('admin.seating-allocation.index') }}" method="GET">
              @if(request()->filled('exam_master_id'))
              <input type="hidden" name="exam_master_id" value="{{ request('exam_master_id') }}">
              @endif
              <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                  <label for="exam_id" class="form-label">Exam</label>
                  <select name="exam_id" id="exam_id" class="form-select">
                    <option value="">All Exams</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                      {{ $exam->exam->name ?? 'Exam' }} | {{ $exam->exam_date }} {{ $exam->start_time }}
                    </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-lg-4 col-md-6">
                  <label for="status" class="form-label">Status</label>
                  <select name="status" id="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalized</option>
                  </select>
                </div>

                <div class="col-lg-4 col-md-6 d-flex gap-2">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-search"></i>
                  </button>
                  <a href="{{ route('admin.seating-allocation.index', request()->filled('exam_master_id') ? ['exam_master_id' => request('exam_master_id')] : []) }}" class="btn btn-warning">
                    <i class="fa fa-sync"></i>
                  </a>
                </div>
              </div>
            </form>

            <hr class="my-2">


          </div>
        </div>
      </div>
      <div class="col-lg-6">

        <div class="card shadow-sm mb-3">
          <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fa fa-clock"></i> Exam Sessions (Operational Units)</h5>
          </div>
          <div class="card-body py-3">
            <form action="{{ route('admin.seating-allocation.index') }}" method="GET" class="row g-2 align-items-end">
              @if(request()->filled('exam_master_id'))
              <input type="hidden" name="exam_master_id" value="{{ request('exam_master_id') }}">
              @endif
              <div class="col-lg-6 col-md-12">
                <label for="operational_session_id" class="form-label">Session Slot (Date + Start Time)</label>
                <select name="operational_session_id" id="operational_session_id" class="form-select">
                  <option value="">Select Session Slot</option>
                  @foreach($operationalSessions as $slot)
                  <option value="{{ $slot->id }}" {{ (int) request('operational_session_id') === (int) $slot->id ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::parse($slot->exam_date)->format('d M Y') }} | {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}
                    @if(isset($slot->course_count))
                    | {{ $slot->course_count }} courses
                    @endif
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-3 col-md-6">
                <button type="submit" class="btn btn-outline-success w-100">
                  <i class="fa fa-eye"></i>
                </button>
              </div>
              <div class="col-lg-3 col-md-6">
                <a href="{{ route('admin.seating-allocation.index', request()->filled('exam_master_id') ? ['exam_master_id' => request('exam_master_id')] : []) }}" class="btn btn-warning w-100">
                  <i class="fa fa-sync"></i>
                </a>
              </div>
            </form>

            @if($selectedOperationalSessionId > 0)
            <div class="table-responsive mt-2">
              <table class="table table-sm table-bordered align-middle mb-0">
                <thead>
                  <tr>
                    <th>Room</th>
                    <th>Generated Physical Seats</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($generatedSeatSummary as $summary)
                  <tr>
                    <td>{{ $summary->room->name ?? ('Room #' . $summary->room_id) }}</td>
                    <td><span class="badge bg-success">{{ (int) $summary->generated_count }}</span></td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="2" class="text-center text-muted">No physical seats generated yet for this session.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>



    <!-- Room-wise Allocation Display -->
    @if(request('exam_id') && $allocations->count() > 0)
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-door-open"></i> Room-wise Seating Allocation</h5>
      </div>
      <div class="card-body py-2">
        @php
        $allocationsByRoom = $allocations->groupBy('room_id');
        @endphp

        @foreach($allocationsByRoom as $roomId => $roomAllocations)
        <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
          <h6 class="bg-light p-2 rounded mb-2">
            <i class="fa fa-building"></i>
            {{ $roomAllocations->first()->room->name ?? 'Room N/A' }}
            <span class="badge bg-info float-end">{{ $roomAllocations->count() }} Students</span>
          </h6>

          <div class="row g-2">
            @foreach($roomAllocations as $allocation)
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="card border">
                <div class="card-body p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>Seat: {{ $allocation->seat_no }}</strong><br>
                      <small>{{ $allocation->examStudent->student->first_name ?? '' }} {{ $allocation->examStudent->student->last_name ?? '' }}</small><br>
                      <small class="text-muted">Roll: {{ $allocation->examStudent->student->roll_no ?? 'N/A' }}</small>
                    </div>
                    <a href="{{ route('admin.seating-allocation.show', $allocation->id) }}"
                      class="btn btn-sm btn-info">
                      <i class="fa fa-eye"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Allocations Table -->
    <div class="card shadow-sm">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Seating Allocations</h5>
        <span class="badge bg-secondary">{{ $allocations->total() }} Records</span>
      </div>
      <div class="card-body py-3">
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th width="5%">#</th>
                <th>Exam</th>
                <th>Room</th>
                <th>Seat No</th>
                <th>Student Details</th>
                <th>Campus</th>
                <th>Status</th>
                <th width="12%">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allocations as $allocation)
              <tr>
                <td>{{ $loop->iteration + ($allocations->currentPage() - 1) * $allocations->perPage() }}</td>
                <td>
                  <strong>{{ $allocation->examSchedule->exam->name ?? 'N/A' }}</strong><br>
                  <small class="badge bg-secondary">{{ $allocation->examSchedule->exam->exam_type ?? '' }}</small><br>
                  <small class="text-muted">{{ $allocation->examSchedule->exam_date ?? '' }} {{ $allocation->examSchedule->start_time ?? '' }}</small>
                </td>
                <td>
                  <strong>{{ $allocation->room->name ?? 'N/A' }}</strong><br>
                  <small class="text-muted">Building: {{ $allocation->room->building ?? 'N/A' }}</small>
                </td>
                <td>
                  <span class="badge bg-primary fs-6">{{ $allocation->seat_no }}</span>
                </td>
                <td>
                  <div>
                    <strong class="text-capitalize">
                      {{ $allocation->examStudent->student->first_name ?? '' }}
                      {{ $allocation->examStudent->student->last_name ?? '' }}
                    </strong>
                  </div>
                  <small class="text-muted">
                    Reg: {{ $allocation->examStudent->student->register_no ?? 'N/A' }} |
                    Roll: {{ $allocation->examStudent->student->roll_no ?? 'N/A' }}
                  </small>
                </td>
                <td>
                  <i class="fa fa-building"></i> {{ $allocation->examStudent->student->campusmaster->name ?? 'N/A' }}
                </td>
                <td>
                  @php
                  $allocStatus = $allocation->status ?? 'finalized';
                  @endphp
                  <span class="badge {{ $allocStatus === 'draft' ? 'bg-warning text-dark' : 'bg-success' }}">{{ ucfirst($allocStatus) }}</span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="{{ route('admin.seating-allocation.show', $allocation->id) }}"
                      class="btn btn-sm btn-info" title="View">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.seating-allocation.edit', $allocation->id) }}"
                      class="btn btn-sm btn-warning" title="Edit">
                      <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.seating-allocation.destroy', $allocation->id) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this allocation?')">
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
                <td colspan="8" class="text-center py-4">
                  <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                  <p class="text-muted">No seating allocations found. Click "Generate Seating" to auto-allocate seats.</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        @if($allocations->hasPages())
        <div class="mt-2">
          {{ $allocations->links() }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Generate Seating Modal -->
<div class="modal fade" id="generateSeatingModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.seating-allocation.auto-allocate') }}" method="POST">
        @csrf
        @php
        $resolvedExamMasterId = (int) ($selectedExamMasterId ?? 0);
        $canAutoGenerate = $resolvedExamMasterId > 0 && !empty($availableRoomsForExam) && $availableRoomsForExam->count() > 0;
        @endphp
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fa fa-magic"></i> Auto Generate Seating</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body py-3">
          <div class="mb-2">
            <small class="text-muted d-block">Session slot selection is optional. If no slot is selected, generation will run for all slots under the selected exam context.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Available Rooms (Auto Selected)</label>
            <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto;">
              @if(!empty($availableRoomsForExam) && $availableRoomsForExam->count() > 0)
              @foreach($availableRoomsForExam as $room)
              <div class="small mb-1">
                <i class="fa fa-check-circle text-success"></i>
                {{ $room->name }}
                <span class="text-muted">(Rows: {{ $room->rows ?? 'N/A' }}, Columns: {{ $room->columns ?? 'N/A' }}, Capacity: {{ $room->capacity ?? 'N/A' }})</span>
              </div>
              @endforeach
              @else
              <div class="text-muted small">No available rooms found for current exam context, or exam is not selected.</div>
              @endif
            </div>
            <small class="text-muted">Rooms are auto-picked where no seating allocation exists for the selected examination.</small>
          </div>

          <div class="mb-3">
            <label class="form-label mb-1">Seating Rules</label>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="enforce_same_course_separation" id="enforce_same_course_separation" value="1" checked>
              <label class="form-check-label" for="enforce_same_course_separation">
                Avoid adjacent seating for students from the same course
              </label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="strict_room_lock" id="strict_room_lock" value="1" checked>
              <label class="form-check-label" for="strict_room_lock">
                Strict room lock: keep students in their fixed room for the same exam
              </label>
            </div>
            <label for="allowed_course_groups" class="form-label">Allowed Course Groups (Optional)</label>
            <textarea name="allowed_course_groups" id="allowed_course_groups" class="form-control" rows="3" placeholder="Examples: 101-102, 205-210, 101-101 or CS101-CS101"></textarea>
            <small class="text-muted">Enter course pairs separated by comma/new line. Use exam subject IDs or subject codes from this session. For same-course adjacency exceptions, use the same value on both sides (for example: 101-101).</small>
          </div>

          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Note:</strong> This generates room seats and auto-assigns registered students exam-wise using room capacity and seating rules. Students keep the same room across papers of the same exam whenever possible.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" {{ $canAutoGenerate ? '' : 'disabled' }}>
            <i class="fa fa-magic"></i> Generate Seating
          </button>
        </div>
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        @if($resolvedExamMasterId > 0)
        <input type="hidden" name="exam_master_id" value="{{ $resolvedExamMasterId }}">
        @endif
        @if(request()->filled('exam_id'))
        <input type="hidden" name="exam_id" value="{{ (int) request('exam_id') }}">
        @endif
      </form>
    </div>
  </div>
</div>

@if((int) request('open_generate') === 1)
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('generateSeatingModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
      return;
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  });
</script>
@endif

@include('includes.footer')
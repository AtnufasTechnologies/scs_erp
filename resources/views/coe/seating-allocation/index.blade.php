@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')

  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Seating Allocation Management</h1>
      <p class="fs-6 mb-0 text-dark">Manage exam seating arrangements and room-wise allocations</p>
    </div>
  </div>

  <div class="container-fluid">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Filters</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.seating-allocation.index') }}" method="GET">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="exam_id" class="form-label">Exam</label>
              <select name="exam_id" id="exam_id" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                  {{ $exam->name }} ({{ $exam->exam_type }})
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4">
              <label for="room_id" class="form-label">Room</label>
              <select name="room_id" id="room_id" class="form-select">
                <option value="">All Rooms</option>
                @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                  {{ $room->name }} (Capacity: {{ $room->capacity ?? 'N/A' }})
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">
                <i class="fa fa-search"></i> Search
              </button>
              <a href="{{ route('admin.seating-allocation.index') }}" class="btn btn-secondary">
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
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#generateSeatingModal">
          <i class="fa fa-magic"></i> Generate Seating
        </button>
        <a href="{{ route('admin.seating-allocation.create') }}" class="btn btn-primary">
          <i class="fa fa-plus"></i> Manual Allocation
        </a>
      </div>
      <div>
        <a href="{{ route('admin.seating-allocation.export') }}?exam_id={{ request('exam_id') }}&room_id={{ request('room_id') }}"
          class="btn btn-info">
          <i class="fa fa-download"></i> Export
        </a>
      </div>
    </div>

    <!-- Room-wise Allocation Display -->
    @if(request('exam_id') && $allocations->count() > 0)
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-door-open"></i> Room-wise Seating Allocation</h5>
      </div>
      <div class="card-body">
        @php
        $allocationsByRoom = $allocations->groupBy('room_id');
        @endphp

        @foreach($allocationsByRoom as $roomId => $roomAllocations)
        <div class="mb-4">
          <h6 class="bg-light p-3 rounded">
            <i class="fa fa-building"></i>
            {{ $roomAllocations->first()->room->name ?? 'Room N/A' }}
            <span class="badge bg-info float-end">{{ $roomAllocations->count() }} Students</span>
          </h6>

          <div class="row">
            @foreach($roomAllocations as $allocation)
            <div class="col-md-3 mb-2">
              <div class="card border">
                <div class="card-body p-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>Seat: {{ $allocation->seat_no }}</strong><br>
                      <small>{{ $allocation->student->first_name ?? '' }} {{ $allocation->student->last_name ?? '' }}</small><br>
                      <small class="text-muted">Roll: {{ $allocation->student->roll_no ?? 'N/A' }}</small>
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
        <hr>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Allocations Table -->
    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0">All Seating Allocations</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="table-dark">
              <tr>
                <th width="5%">#</th>
                <th>Exam</th>
                <th>Room</th>
                <th>Seat No</th>
                <th>Student Details</th>
                <th>Campus</th>
                <th width="12%">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allocations as $allocation)
              <tr>
                <td>{{ $loop->iteration + ($allocations->currentPage() - 1) * $allocations->perPage() }}</td>
                <td>
                  <strong>{{ $allocation->exam->name ?? 'N/A' }}</strong><br>
                  <small class="badge bg-secondary">{{ $allocation->exam->exam_type ?? '' }}</small>
                </td>
                <td>
                  <strong>{{ $allocation->room->name ?? 'N/A' }}</strong><br>
                  <small class="text-muted">Block: {{ $allocation->room->block ?? 'N/A' }}</small>
                </td>
                <td>
                  <span class="badge bg-primary fs-6">{{ $allocation->seat_no }}</span>
                </td>
                <td>
                  <div>
                    <strong class="text-capitalize">
                      {{ $allocation->student->first_name ?? '' }}
                      {{ $allocation->student->last_name ?? '' }}
                    </strong>
                  </div>
                  <small class="text-muted">
                    Reg: {{ $allocation->student->register_no ?? 'N/A' }} |
                    Roll: {{ $allocation->student->roll_no ?? 'N/A' }}
                  </small>
                </td>
                <td>
                  <i class="fa fa-building"></i> {{ $allocation->student->campusmaster->name ?? 'N/A' }}
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
                <td colspan="7" class="text-center py-4">
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
        <div class="mt-3">
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
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fa fa-magic"></i> Auto Generate Seating</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="modal_exam_id" class="form-label">Select Exam <span class="text-danger">*</span></label>
            <select name="exam_id" id="modal_exam_id" class="form-select" required>
              <option value="">-- Select Exam --</option>
              @foreach($exams as $exam)
              <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->exam_type }})</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Select Rooms <span class="text-danger">*</span></label>
            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
              @foreach($rooms as $room)
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="room_ids[]"
                  value="{{ $room->id }}" id="room_{{ $room->id }}">
                <label class="form-check-label" for="room_{{ $room->id }}">
                  {{ $room->name }}
                  <small class="text-muted">(Capacity: {{ $room->capacity ?? 'N/A' }})</small>
                </label>
              </div>
              @endforeach
            </div>
            <small class="text-muted">Select multiple rooms for allocation</small>
          </div>

          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Note:</strong> This will automatically allocate registered students to the selected rooms based on availability.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-magic"></i> Generate Seating
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid py-4">

    <!-- Page Header -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4"
      style="background: linear-gradient(135deg, #1a6336 0%, #27ae60 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white">Course Seat Manager</span>
        </a>
        <button class="btn btn-light ms-auto" data-bs-toggle="modal" data-bs-target="#addSeatModal">
          <i class="fa fa-plus-circle me-1"></i> Assign Seats
        </button>
      </div>
    </nav>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($courses->isEmpty())
    <div class="alert alert-info text-center">
      <i class="fa fa-info-circle me-2"></i>
      No courses are assigned to your department yet. Please set up courses in the Course Master first.
    </div>
    @elseif($allocations->isEmpty())
    <div class="alert alert-warning text-center">
      <i class="fa fa-chair me-2"></i>
      No seat allocations configured yet. Click <strong>Assign Seats</strong> to begin.
    </div>
    @else

    {{-- Group allocations by Batch → Semester --}}
    @php
    $grouped = $allocations->groupBy(fn($a) => $a->batch->batch_name ?? 'Unknown Batch');
    @endphp

    @foreach($grouped as $batchName => $batchAllocations)
    <div class="card mb-4 shadow-sm">
      <div class="card-header text-white fw-bold d-flex justify-content-between align-items-center"
        style="background: linear-gradient(90deg,#1a6336,#27ae60);">
        <span><i class="fa fa-users me-2"></i>Batch: {{ $batchName }}</span>
        <span class="badge bg-light text-dark">{{ $batchAllocations->count() }} course(s)</span>
      </div>
      <div class="card-body p-0">
        @php
        $semGroups = $batchAllocations->groupBy(fn($a) => $a->semester->title ?? 'Unknown Semester');
        @endphp

        @foreach($semGroups as $semName => $semAllocations)
        @php
        $firstAlloc = $semAllocations->first();
        $allOpen = $semAllocations->every(fn($a) => $a->is_open);
        @endphp
        <div class="border-bottom">
          <!-- Semester header with bulk toggle -->
          <div class="p-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-secondary">
              <i class="fa fa-calendar me-1"></i>{{ $semName }}
            </h6>
            <div class="d-flex gap-2 align-items-center">
              <small class="text-muted me-2">Bulk Toggle:</small>
              <!-- Open All -->
              <form action="{{ route('department.seats.bulk-toggle') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $firstAlloc->batch_id }}">
                <input type="hidden" name="semester_id" value="{{ $firstAlloc->semester_id }}">
                <input type="hidden" name="is_open" value="1">
                <button type="submit" class="btn btn-sm btn-success {{ $allOpen ? 'disabled' : '' }}"
                  title="Open registration for all courses in this semester">
                  <i class="fa fa-lock-open me-1"></i>Open All
                </button>
              </form>
              <!-- Close All -->
              <form action="{{ route('department.seats.bulk-toggle') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $firstAlloc->batch_id }}">
                <input type="hidden" name="semester_id" value="{{ $firstAlloc->semester_id }}">
                <input type="hidden" name="is_open" value="0">
                <button type="submit" class="btn btn-sm btn-outline-danger {{ !$allOpen ? 'disabled' : '' }}"
                  title="Close registration for all courses in this semester">
                  <i class="fa fa-lock me-1"></i>Close All
                </button>
              </form>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Type</th>
                  <th class="text-center">Total Seats</th>
                  <th class="text-center">Registration</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($semAllocations as $alloc)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="badge bg-secondary">
                      {{ $alloc->courseMaster->course_code ?? '—' }}
                    </span>
                  </td>
                  <td>{{ $alloc->courseMaster->course_title ?? '—' }}</td>
                  <td>
                    <span class="badge bg-info text-dark">
                      {{ $alloc->courseMaster->coursetypemaster->title ?? '—' }}
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="fw-bold fs-6">{{ $alloc->total_seats }}</span>
                  </td>
                  <td class="text-center">
                    <form action="{{ route('department.seats.toggle', $alloc->id) }}" method="POST"
                      style="display:inline;">
                      @csrf
                      @if($alloc->is_open)
                      <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-lock-open me-1"></i>Open
                      </button>
                      @else
                      <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-lock me-1"></i>Closed
                      </button>
                      @endif
                    </form>
                  </td>
                  <td class="text-center">
                    <!-- Edit -->
                    <button class="btn btn-sm btn-warning me-1" title="Edit Seats"
                      data-bs-toggle="modal"
                      data-bs-target="#editSeatModal{{ $alloc->id }}">
                      <i class="fa fa-edit"></i>
                    </button>

                    <!-- Delete -->
                    <form action="{{ route('department.seats.destroy', $alloc->id) }}" method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Remove this seat allocation?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>

                <!-- Edit Seats Modal -->
                <div class="modal fade" id="editSeatModal{{ $alloc->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">
                          <i class="fa fa-edit me-2 text-warning"></i>Update Seats
                          — {{ $alloc->courseMaster->course_code ?? '—' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="{{ route('department.seats.update', $alloc->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Course</label>
                            <input type="text" class="form-control"
                              value="{{ $alloc->courseMaster->course_title ?? '—' }}" disabled>
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Batch / Semester</label>
                            <input type="text" class="form-control"
                              value="{{ $alloc->batch->batch_name ?? '—' }} / {{ $alloc->semester->title ?? '—' }}"
                              disabled>
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Total Seats *</label>
                            <input type="number" name="total_seats" class="form-control"
                              value="{{ $alloc->total_seats }}" min="1" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                {{-- /Edit Modal --}}

                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @endforeach

    @endif

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     Add Seat Allocation Modal
══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addSeatModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-chair me-2 text-success"></i>Assign Course Seats
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('department.seats.store') }}" method="POST">
        @csrf
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
            <select name="batch_id" id="addBatchSelect" class="form-select" required>
              <option value="">— Select Batch —</option>
              @foreach($batches as $b)
              <option value="{{ $b->id }}">{{ $b->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
            <select name="semester_id" id="addSemesterSelect" class="form-select" required>
              <option value="">— Select Semester —</option>
              @foreach($semesters as $s)
              <option value="{{ $s->id }}">{{ $s->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
            <select name="course_master_id" class="form-select" required>
              <option value="">— Select Course —</option>
              @foreach($courses as $course)
              <option value="{{ $course->id }}">
                ({{ $course->course_code ?? '—' }}) {{ $course->course_title }}
                @if($course->semestermaster)
                — Sem {{ $course->semestermaster->title }}
                @endif
              </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Total Seats <span class="text-danger">*</span></label>
            <input type="number" name="total_seats" class="form-control" min="1"
              placeholder="e.g. 60" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-save me-1"></i>Save Allocation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
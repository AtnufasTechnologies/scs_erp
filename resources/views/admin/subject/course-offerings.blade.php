@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid py-4">

    <!-- Page Header -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4"
      style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white">Course Type Offerings &amp; Intake Capacity</span>
        </a>
        <button class="btn btn-light ms-auto" data-bs-toggle="modal" data-bs-target="#addOfferingModal">
          <i class="fa fa-plus-circle me-1"></i> New Offering
        </button>
      </div>
    </nav>

    <!-- Filters -->
    <form method="GET" action="{{ route('department.offerings.index') }}" class="row g-2 mb-4">
      <div class="col-auto">
        <select name="batch_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Batches</option>
          @foreach($batches as $b)
          <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
            {{ $b->batch_name }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Semesters</option>
          @foreach($semesters as $s)
          <option value="{{ $s->id }}" {{ request('semester_id') == $s->id ? 'selected' : '' }}>
            {{ $s->title }}
          </option>
          @endforeach
        </select>
      </div>
    </form>

    @if($offerings->isEmpty())
    <div class="text-center py-5 text-muted">
      <i class="fa fa-inbox fa-3x mb-3"></i>
      <p>No offerings configured yet. Click <strong>New Offering</strong> to start.</p>
    </div>
    @else

    <!-- Group by Batch → Semester -->
    @php
    $grouped = $offerings->groupBy(fn($o) => $o->batch->batch_name ?? 'Unknown Batch');
    @endphp

    @foreach($grouped as $batchName => $batchOfferings)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-primary text-white fw-bold">
        <i class="fa fa-users me-2"></i>Batch: {{ $batchName }}
      </div>
      <div class="card-body p-0">
        @php
        $semGroups = $batchOfferings->groupBy(fn($o) => $o->semester->title ?? 'Unknown Semester');
        @endphp
        @foreach($semGroups as $semName => $semOfferings)
        <div class="border-bottom">
          <div class="p-3 bg-light">
            <h6 class="mb-0 text-secondary"><i class="fa fa-calendar me-1"></i>{{ $semName }}</h6>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Course Type</th>
                  <th class="text-center">Intake</th>
                  <th class="text-center">Confirmed</th>
                  <th class="text-center">Waitlisted</th>
                  <th class="text-center">Available</th>
                  <th class="text-center">Registration</th>
                  <th>Window</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($semOfferings as $offering)
                @php
                $confirmed = $offering->confirmedRegistrations->count();
                $waitlisted = $offering->waitlistedRegistrations->count();
                $available = max(0, $offering->intake_capacity - $confirmed);
                @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="badge bg-secondary fs-6">{{ $offering->courseType->title ?? '—' }}</span>
                  </td>
                  <td class="text-center fw-bold">{{ $offering->intake_capacity }}</td>
                  <td class="text-center">
                    <span class="badge bg-success">{{ $confirmed }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-warning text-dark">{{ $waitlisted }}</span>
                  </td>
                  <td class="text-center">
                    @if($available > 0)
                    <span class="badge bg-info text-dark">{{ $available }}</span>
                    @else
                    <span class="badge bg-danger">Full</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <form action="{{ route('department.offerings.toggle', $offering->id) }}" method="POST"
                      style="display:inline;">
                      @csrf
                      @if($offering->is_registration_open)
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
                  <td style="font-size:12px;">
                    @if($offering->registration_opens_at)
                    <span class="text-success">
                      <i class="fa fa-play-circle me-1"></i>{{ $offering->registration_opens_at->format('d M Y h:i A') }}
                    </span><br>
                    @endif
                    @if($offering->registration_closes_at)
                    <span class="text-danger">
                      <i class="fa fa-stop-circle me-1"></i>{{ $offering->registration_closes_at->format('d M Y h:i A') }}
                    </span>
                    @endif
                    @if(!$offering->registration_opens_at && !$offering->registration_closes_at)
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <a href="{{ route('department.offerings.registrations', $offering->id) }}"
                      class="btn btn-sm btn-info me-1" title="View Registrations">
                      <i class="fa fa-list"></i>
                    </a>

                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-warning me-1" title="Edit"
                      data-bs-toggle="modal"
                      data-bs-target="#editOfferingModal{{ $offering->id }}">
                      <i class="fa fa-edit"></i>
                    </button>

                    <!-- Delete -->
                    <form action="{{ route('department.offerings.destroy', $offering->id) }}" method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Delete this offering? This cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editOfferingModal{{ $offering->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Offering —
                          {{ $offering->courseType->title ?? '—' }} /
                          {{ $offering->batch->batch_name ?? '—' }} /
                          {{ $offering->semester->title ?? '—' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="{{ route('department.offerings.update', $offering->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Intake Capacity *</label>
                            <input type="number" name="intake_capacity" class="form-control"
                              value="{{ $offering->intake_capacity }}" min="1" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Registration Opens At</label>
                            <input type="datetime-local" name="registration_opens_at" class="form-control"
                              value="{{ $offering->registration_opens_at?->format('Y-m-d\TH:i') }}">
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Registration Closes At</label>
                            <input type="datetime-local" name="registration_closes_at" class="form-control"
                              value="{{ $offering->registration_closes_at?->format('Y-m-d\TH:i') }}">
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
                <!-- /Edit Modal -->

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
     Add Offering Modal
═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addOfferingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus-circle me-2 text-primary"></i>New Course Offering</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('department.offerings.store') }}" method="POST">
        @csrf
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-semibold">Batch *</label>
            <select name="batch_id" class="form-select" required>
              <option value="">— Select Batch —</option>
              @foreach($batches as $b)
              <option value="{{ $b->id }}">{{ $b->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Semester *</label>
            <select name="semester_id" class="form-select" required>
              <option value="">— Select Semester —</option>
              @foreach($semesters as $s)
              <option value="{{ $s->id }}">{{ $s->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Course Type *</label>
            <select name="course_type_id" class="form-select select-example" required>
              <option value="">— Select Course Type —</option>
              @foreach($courseTypes as $ct)
              <option value="{{ $ct->id }}">{{ $ct->title }} - {{ $ct->description  }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Intake Capacity *</label>
            <input type="number" name="intake_capacity" class="form-control" min="1"
              placeholder="e.g. 60" required>
            <small class="text-muted">Maximum number of students that can be confirmed.</small>
          </div>

          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label fw-semibold">Registration Opens At</label>
                <input type="datetime-local" name="registration_opens_at" class="form-control">
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label fw-semibold">Registration Closes At</label>
                <input type="datetime-local" name="registration_closes_at" class="form-control">
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-save me-1"></i>Create Offering
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index') }}">Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exam Details</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2">
                    <i class="fas fa-clipboard-list me-2"></i>{{ $exam->name }}
                  </h3>
                  <p class="text-white-50 mb-0">
                    <span class="badge bg-light text-dark me-2">{{ $exam->exam_type }}</span>
                    @if($exam->status === 'upcoming')
                    <span class="badge bg-warning">Upcoming</span>
                    @elseif($exam->status === 'ongoing')
                    <span class="badge bg-success">Ongoing</span>
                    @elseif($exam->status === 'completed')
                    <span class="badge bg-info">Completed</span>
                    @elseif($exam->status === 'cancelled')
                    <span class="badge bg-danger">Cancelled</span>
                    @endif
                  </p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.exams.edit', $exam->id) }}" class="btn btn-light me-2">
                    <i class="fa fa-edit me-1"></i>Edit
                  </a>
                  <a href="{{ route('coe.exams.index') }}" class="btn btn-outline-light">
                    <i class="fa fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="row">
        <!-- Exam Details -->
        <div class="col-lg-8">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Exam Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Exam Name</label>
                  <p class="fw-bold mb-0">{{ $exam->name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Exam Type</label>
                  <p class="mb-0"><span class="badge bg-info">{{ $exam->exam_type }}</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Semester</label>
                  <p class="mb-0"><span class="badge bg-secondary">{{ $exam->semester ?? 'N/A' }}</span></p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Program</label>
                  <p class="fw-bold mb-0">{{ $exam->program->name }} ({{ $exam->program->code }})</p>
                  <small class="text-muted">{{ $exam->program->type }}</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Regulation</label>
                  <p class="fw-bold mb-0">{{ $exam->regulation->regulation_name ?? 'N/A' }}</p>
                  @if($exam->regulation)
                  <small class="text-muted">{{ $exam->regulation->start_year }}-{{ $exam->regulation->end_year }}</small>
                  @endif
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="text-muted small">Start Date</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}
                  </p>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="text-muted small">End Date</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
                  </p>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="text-muted small">Duration</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-hourglass-half text-info me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->start_date)->diffInDays($exam->end_date) + 1 }} days
                  </p>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Created At</label>
                  <p class="mb-0">{{ $exam->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="text-muted small">Last Updated</label>
                  <p class="mb-0">{{ $exam->updated_at->format('d M Y, h:i A') }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Related Data Tabs -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i>Related Information</h6>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs" id="examTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab"
                    data-bs-target="#attendance" type="button" role="tab">
                    <i class="fa fa-user-check me-1"></i>Attendance
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="registrations-tab" data-bs-toggle="tab"
                    data-bs-target="#registrations" type="button" role="tab">
                    <i class="fa fa-users me-1"></i>Registrations
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="duties-tab" data-bs-toggle="tab"
                    data-bs-target="#duties" type="button" role="tab">
                    <i class="fa fa-chalkboard-teacher me-1"></i>Duties
                  </button>
                </li>
              </ul>
              <div class="tab-content mt-3" id="examTabsContent">
                <!-- Attendance Tab -->
                <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3 bg-success-subtle">
                          <i class="fa fa-check text-success"></i>
                        </div>
                        <div>
                          <p class="text-muted mb-0 small">Present</p>
                          <h5 class="mb-0 fw-bold">{{ $attendanceStats['present'] ?? 0 }}</h5>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3 bg-danger-subtle">
                          <i class="fa fa-times text-danger"></i>
                        </div>
                        <div>
                          <p class="text-muted mb-0 small">Absent</p>
                          <h5 class="mb-0 fw-bold">{{ $attendanceStats['absent'] ?? 0 }}</h5>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 mb-3">
                      <div class="d-flex align-items-center">
                        <div class="icon-wrapper me-3 bg-info-subtle">
                          <i class="fa fa-percentage text-info"></i>
                        </div>
                        <div>
                          <p class="text-muted mb-0 small">Percentage</p>
                          <h5 class="mb-0 fw-bold">{{ $attendanceStats['percentage'] ?? 0 }}%</h5>
                        </div>
                      </div>
                    </div>
                  </div>
                  <a href="{{ route('coe.attendance.view', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fa fa-external-link-alt me-1"></i>View Attendance Records
                  </a>
                </div>

                <!-- Registrations Tab -->
                <div class="tab-pane fade" id="registrations" role="tabpanel">
                  <p class="text-muted">
                    <strong>Total Registrations:</strong> {{ $exam->registrations->count() }}
                  </p>
                  @if($exam->registrations->count() > 0)
                  <div class="table-responsive">
                    <table class="table table-sm table-hover">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th>Student</th>
                          <th>Registration Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($exam->registrations->take(10) as $registration)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $registration->student->first_name ?? 'N/A' }}</td>
                          <td>{{ $registration->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @if($exam->registrations->count() > 10)
                  <small class="text-muted">Showing first 10 of {{ $exam->registrations->count() }} registrations</small>
                  @endif
                  @else
                  <p class="text-muted">No registrations yet for this exam.</p>
                  @endif
                </div>

                <!-- Duties Tab -->
                <div class="tab-pane fade" id="duties" role="tabpanel">
                  <div class="row">
                    <div class="col-md-4">
                      <p class="mb-1"><strong>Invigilation Duties:</strong></p>
                      <h5 class="text-primary">{{ $dutyStats['invigilation'] ?? 0 }}</h5>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-1"><strong>Evaluation Duties:</strong></p>
                      <h5 class="text-success">{{ $dutyStats['evaluation'] ?? 0 }}</h5>
                    </div>
                    <div class="col-md-4">
                      <p class="mb-1"><strong>Moderation Duties:</strong></p>
                      <h5 class="text-info">{{ $dutyStats['moderation'] ?? 0 }}</h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
              <div class="d-grid gap-2">
                <a href="{{ route('coe.attendance.index') }}" class="btn btn-outline-primary">
                  <i class="fa fa-user-check me-2"></i>Mark Attendance
                </a>
                <a href="{{ route('coe.exams.edit', $exam->id) }}" class="btn btn-outline-secondary">
                  <i class="fa fa-edit me-2"></i>Edit Exam
                </a>
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                  <i class="fa fa-trash me-2"></i>Delete Exam
                </button>
              </div>
            </div>
          </div>

          <!-- Status Card -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Exam Statistics</h6>
            </div>
            <div class="card-body">
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Status:</span>
                  <span class="fw-bold text-capitalize">{{ $exam->status }}</span>
                </div>
              </div>
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Duration:</span>
                  <span class="fw-bold">{{ \Carbon\Carbon::parse($exam->start_date)->diffInDays($exam->end_date) + 1 }} days</span>
                </div>
              </div>
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Registrations:</span>
                  <span class="fw-bold">{{ $exam->registrations->count() }}</span>
                </div>
              </div>
              <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Attendance Marked:</span>
                  <span class="fw-bold">{{ $attendanceStats['total'] ?? 0 }}</span>
                </div>
              </div>
              <div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Days Remaining:</span>
                  <span class="fw-bold text-primary">
                    @php
                    $daysRemaining = \Carbon\Carbon::parse($exam->end_date)->diffInDays(today(), false);
                    @endphp
                    @if($daysRemaining < 0)
                      {{ abs($daysRemaining) }} days
                      @elseif($daysRemaining==0)
                      Today
                      @else
                      Completed
                      @endif
                      </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
  }

  .nav-tabs .nav-link.active {
    color: #667eea;
    border-bottom: 2px solid #667eea;
    background: transparent;
  }

  .nav-tabs .nav-link:hover {
    border-bottom: 2px solid #667eea;
  }
</style>

<script>
  function confirmDelete() {
    if (confirm('Are you sure you want to delete this exam: "{{ $exam->name }}"? This action cannot be undone.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `{{ route('coe.exams.destroy', $exam->id) }}`;

      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = '{{ csrf_token() }}';

      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';

      form.appendChild(csrfToken);
      form.appendChild(methodInput);
      document.body.appendChild(form);
      form.submit();
    }
  }
</script>

@include('includes.footer')
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
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index', ['module' => ($module ?? ($exam->assessment_type ?? 'SA'))]) }}">Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exam Details</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-3 exam-details-page">
      @php
      $activeModule = $module ?? ($exam->assessment_type ?? 'SA');
      @endphp
      <!-- Page Header -->
      <div class="row mb-3">
        <div class="col-12">
          <div class="card gradient-coe shadow-sm border-0">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2">
                    <i class="fas fa-clipboard-list me-2"></i>{{ $exam->name }}
                  </h3>
                  <p class="text-white-50 mb-0">
                    <span class="badge bg-dark me-2">{{ $activeModule === 'FA2' ? 'FA-2' : 'SA' }}</span>
                    <span class="badge bg-light text-dark me-2">{{ $exam->exam_type }}</span>
                    @if((bool) ($exam->is_published ?? false))
                    <span class="badge bg-success me-2">Published</span>
                    @else
                    <span class="badge bg-secondary me-2">Unpublished</span>
                    @endif
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
                  <a href="{{ route('coe.exams.edit', ['id' => $exam->id, 'module' => $activeModule]) }}" class="btn btn-light me-2">
                    <i class="fa fa-edit me-1"></i>Edit
                  </a>
                  <a href="{{ route('coe.exams.index', ['module' => $activeModule]) }}" class="btn btn-light">
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

      <div class="row g-3">
        <!-- Exam Details -->
        <div class="col-lg-8">
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom py-2">
              <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Exam Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Exam Name</label>
                  <p class="fw-bold mb-0">{{ $exam->name }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Exam Type</label>
                  <p class="mb-0"><span class="badge bg-info">{{ $exam->exam_type }}</span></p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Semester</label>
                  <p class="mb-0"><span class="badge bg-secondary">{{ $exam->semester ?? 'N/A' }}</span></p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Program</label>
                  <p class="fw-bold mb-0">{{ $exam->program->name }} ({{ $exam->program->code }})</p>
                  <small class="text-muted">{{ $exam->program->type }}</small>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Regulation</label>
                  <p class="fw-bold mb-0">{{ $exam->regulation->regulation_name ?? 'N/A' }}</p>
                  @if($exam->regulation)
                  <small class="text-muted">{{ $exam->regulation->start_year }}-{{ $exam->regulation->end_year }}</small>
                  @endif
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Registration Mode</label>
                  <p class="mb-0">
                    @if(($exam->registration_mode ?? 'registration_required') === 'auto_registered')
                    <span class="badge bg-warning text-dark">Auto Registered</span>
                    @else
                    <span class="badge bg-primary">Student Registration Required</span>
                    @endif
                  </p>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-4 mb-2">
                  <label class="text-muted small">Start Date</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}
                  </p>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="text-muted small">End Date</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-calendar text-primary me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}
                  </p>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="text-muted small">Duration</label>
                  <p class="fw-bold mb-0">
                    <i class="fa fa-hourglass-half text-info me-2"></i>
                    {{ \Carbon\Carbon::parse($exam->start_date)->diffInDays($exam->end_date) + 1 }} days
                  </p>
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Created At</label>
                  <p class="mb-0">{{ $exam->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="col-md-6 mb-2">
                  <label class="text-muted small">Last Updated</label>
                  <p class="mb-0">{{ $exam->updated_at->format('d M Y, h:i A') }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Related Data Tabs -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2">
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
              <div class="tab-content mt-2" id="examTabsContent">
                <!-- Attendance Tab -->
                <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                  <div class="row">
                    <div class="col-md-4 mb-2">
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
                    <div class="col-md-4 mb-2">
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
                    <div class="col-md-4 mb-2">
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
                    <strong>Total Registrations:</strong> {{ (int) ($registrationCount ?? 0) }}
                  </p>
                  @if((int) ($registrationCount ?? 0) > 0)
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
                        @foreach(($registrationPreview ?? collect()) as $registration)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $registration->student->first_name ?? 'N/A' }}</td>
                          <td>{{ $registration->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @if((int) ($registrationCount ?? 0) > 10)
                  <small class="text-muted">Showing first 10 of {{ (int) ($registrationCount ?? 0) }} registrations</small>
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
          <div class="card shadow-sm mb-3">
            <div class="card-header bg-transparent border-bottom py-2">
              <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="card-body">

              <div class="d-grid gap-2">
                <a href="{{ route('coe.exams.calendar', ['module' => $activeModule, 'exam_id' => $exam->id]) }}" class="btn btn-outline-info">
                  <i class="fa fa-calendar-alt me-2"></i>Schedule Timetable
                </a>
                @if(($exam->registration_mode ?? 'registration_required') !== 'auto_registered')
                <a href="{{ route('admin.exam-registrations.index', ['exam_id' => $exam->id]) }}" class="btn btn-outline-dark">
                  <i class="fa fa-user-plus me-2"></i>Open Registrations
                </a>
                @endif
                <a href="{{ route('coe.attendance.index') }}" class="btn btn-outline-primary">
                  <i class="fa fa-user-check me-2"></i>Mark Attendance
                </a>
                <a href="{{ route('coe.exams.edit', $exam->id) }}" class="btn btn-outline-secondary">
                  <i class="fa fa-edit me-2"></i>Edit Exam
                </a>
                <form action="{{ route('coe.exams.toggle-publish', $exam->id) }}" method="POST" class="d-grid">
                  @csrf
                  @if((bool) ($exam->is_published ?? false))
                  <button type="submit" class="btn btn-outline-warning" onclick="return confirmPublishToggle(event, this, 'Unpublish this exam? It will be hidden from students.')">
                    <i class="fa fa-eye-slash me-2"></i>Unpublish for Students
                  </button>
                  @else
                  <button type="submit" class="btn btn-outline-success" onclick="return confirmPublishToggle(event, this, 'Publish this exam? It will be visible to students.')">
                    <i class="fa fa-upload me-2"></i>Publish for Students
                  </button>
                  @endif
                </form>
                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                  <i class="fa fa-trash me-2"></i>Delete Exam
                </button>
              </div>
            </div>
          </div>

          <!-- Status Card -->
          <div class="card shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2">
              <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Exam Statistics</h6>
            </div>
            <div class="card-body">
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Status:</span>
                  <span class="fw-bold text-capitalize">{{ $exam->status }}</span>
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Student Visibility:</span>
                  @if((bool) ($exam->is_published ?? false))
                  <span class="fw-bold text-success">Published</span>
                  @else
                  <span class="fw-bold text-secondary">Hidden</span>
                  @endif
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Registration Mode:</span>
                  @if(($exam->registration_mode ?? 'registration_required') === 'auto_registered')
                  <span class="fw-bold text-warning">Auto Registered</span>
                  @else
                  <span class="fw-bold text-primary">Registration Required</span>
                  @endif
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Duration:</span>
                  <span class="fw-bold">{{ \Carbon\Carbon::parse($exam->start_date)->diffInDays($exam->end_date) + 1 }} days</span>
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Registrations:</span>
                  <span class="fw-bold">{{ (int) ($registrationCount ?? 0) }}</span>
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Eligible Students:</span>
                  <span class="fw-bold text-success">{{ (int) ($eligibleStudentCount ?? 0) }}</span>
                </div>
              </div>
              <div class="mb-2 pb-2 border-bottom">
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
    background: linear-gradient(135deg, #3654d7 0%, #7c14e3 100%);
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

  .exam-details-page .card-body {
    padding: 0.95rem 1rem;
  }

  .exam-details-page .card-header {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .exam-details-page .btn {
    padding-top: 0.42rem;
    padding-bottom: 0.42rem;
  }

  .exam-details-page hr {
    margin: 0.85rem 0;
  }

  @media (max-width: 991.98px) {
    .exam-details-page {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
    }

    .exam-details-page .card-body {
      padding: 0.85rem;
    }
  }
</style>

<script>
  function confirmPublishToggle(event, button, message) {
    event.preventDefault();

    const form = button.closest('form');
    if (!form) {
      return false;
    }

    if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
      Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
      return false;
    }

    if (confirm(message)) {
      form.submit();
    }
    return false;
  }

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
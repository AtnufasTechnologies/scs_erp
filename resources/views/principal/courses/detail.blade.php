@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Course Detail</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.courses.index') }}">Courses & CSO</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Course Info Header --}}
    <div class=" mt-3 border-0 shadow-sm">
      <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #24d4ce 100%); border-radius: 0.5rem;">
        <div class="row text-white">
          <div class="col-md-8">
            <h3 class="mb-1 text-light">{{ $cm ? $cm->course_code : '-' }} - {{ $cm ? $cm->course_title : '-' }}</h3>
            <div class="d-flex flex-wrap gap-3 mt-2">
              <span><i class="fas fa-tag me-1"></i> {{ $cm && $cm->coursetypemaster ? $cm->coursetypemaster->title : '-' }}</span>
              <span><i class="fas fa-layer-group me-1"></i> {{ $syllabus->semestermaster ? $syllabus->semestermaster->title : '-' }}</span>
              <span><i class="fas fa-users me-1"></i> {{ $syllabus->batchmaster ? $syllabus->batchmaster->batch_name : '-' }}</span>
              <span><i class="fas fa-book me-1"></i> {{ $syllabus->subject ? $syllabus->subject->title : '-' }}</span>
              @if($cm)
              <span><i class="fas fa-calendar me-1"></i> Academic Year: {{ $cm->academic_year }}</span>
              @endif
            </div>
            @if($faculty)
            <div class="mt-2">
              <i class="fas fa-chalkboard-teacher me-1"></i> Faculty: <strong>{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</strong>
            </div>
            @endif
          </div>
          <div class="col-md-4 text-end">
            @if($cm)
            <div class="mt-2">
              <span class="badge bg-light text-dark fs-6">Credits: {{ $cm->credits ?? '-' }}</span>
              <span class="badge bg-light text-dark fs-6 ms-1">Hrs/Week: {{ $cm->hrs_per_week ?? '-' }}</span>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3 g-3">
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-primary">{{ $totalSubunits }}</div>
            <div class="text-muted small">Total Subunits</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-success">{{ $completedSubunits }}</div>
            <div class="text-muted small">Completed</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold {{ $completionPercent >= 75 ? 'text-success' : ($completionPercent >= 50 ? 'text-warning' : 'text-danger') }}">{{ $completionPercent }}%</div>
            <div class="text-muted small">Completion</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-info">{{ count($attendanceByDate) }}</div>
            <div class="text-muted small">Classes Taken</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-warning">{{ $totalFeedback }}</div>
            <div class="text-muted small">Feedbacks</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-primary">{{ $avgRating ? number_format($avgRating, 1) : '-' }}</div>
            <div class="text-muted small">Avg Rating</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3 g-3">
      {{-- CSO Subunits Table with scroll --}}
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="fas fa-list-check me-2 text-primary"></i>CSO Subunits Progress</h6>
            <span class="badge bg-primary">{{ $completedSubunits }}/{{ $totalSubunits }}</span>
          </div>
          <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>#</th>
                    <th>Subunit Title</th>
                    <th>Status</th>
                    <th>Completed On</th>
                    <th>Feedback</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody>
                  @if($subunits->count())
                  @php $sl = 1; @endphp
                  @foreach($subunits as $su)
                  <tr>
                    <td>{{ $sl++ }}</td>
                    <td>{{ $su->csoSubunit ? $su->csoSubunit->title : 'N/A' }}</td>
                    <td>
                      @if($su->is_completed)
                      <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span>
                      @else
                      <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                      @endif
                    </td>
                    <td>
                      @if($su->is_completed && $su->updated_at)
                      {{ \Carbon\Carbon::parse($su->updated_at)->format('d M Y') }}
                      @else
                      <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if(isset($subunitFeedback[$su->id]) && $subunitFeedback[$su->id]['count'] > 0)
                      <span class="badge bg-info">{{ number_format($subunitFeedback[$su->id]['avg_rating'], 1) }}</span>
                      <small class="text-muted">({{ $subunitFeedback[$su->id]['count'] }})</small>
                      @else
                      <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if(isset($subunitFeedbackDetails[$su->id]) && $subunitFeedbackDetails[$su->id]->count() > 0)
                      <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $su->id }}">
                        <i class="fas fa-comments"></i>
                      </button>
                      @else
                      <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                  @else
                  <tr>
                    <td colspan="6" class="text-center py-3 text-muted">No subunits found</td>
                  </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- Completion Progress Visual --}}
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>Completion Overview</h6>
          </div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <div style="position: relative; width: 180px; height: 180px;">
              <canvas id="completionChart"></canvas>
              <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                <div class="fs-2 fw-bold {{ $completionPercent >= 75 ? 'text-success' : ($completionPercent >= 50 ? 'text-warning' : 'text-danger') }}">{{ $completionPercent }}%</div>
                <div class="small text-muted">Complete</div>
              </div>
            </div>
            <div class="d-flex gap-4 mt-3">
              <div class="text-center">
                <span class="badge bg-success">{{ $completedSubunits }}</span>
                <div class="small text-muted">Done</div>
              </div>
              <div class="text-center">
                <span class="badge bg-warning">{{ $totalSubunits - $completedSubunits }}</span>
                <div class="small text-muted">Pending</div>
              </div>
            </div>
          </div>
        </div>

        {{-- Class Log by Date --}}
        <div class="card border-0 shadow-sm mt-3">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2 text-info"></i>Attendance (by Date)</h6>
            <span class="badge bg-info">{{ $totalClassesTaken }} dates</span>
          </div>
          <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
            @if($attendanceByDate->count())
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Date</th>
                  <th class="text-end">Students Present</th>
                </tr>
              </thead>
              <tbody>
                @foreach($attendanceByDate as $ad)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($ad->attendance_date)->format('d M Y, l') }}</td>
                  <td class="text-end"><span class="badge bg-success">{{ $ad->students_present }}</span></td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <p class="text-muted text-center py-3">No class records found</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Student-wise Attendance - Card Layout --}}
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="fas fa-user-check me-2 text-primary"></i>Student-wise Attendance</h6>
        <span class="badge bg-primary">{{ $studentAttendanceSummary->count() }} students</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @if($studentAttendanceSummary->count())
          @foreach($studentAttendanceSummary as $sa)
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border h-100" style="border-left: 4px solid {{ $sa->percentage >= 75 ? '#198754' : ($sa->percentage >= 50 ? '#ffc107' : '#dc3545') }} !important;">
              <div class="card-body p-3">
                <div class="d-flex align-items-center mb-2">
                  <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; background: {{ $sa->percentage >= 75 ? '#19875420' : ($sa->percentage >= 50 ? '#ffc10720' : '#dc354520') }};">
                    <i class="fas fa-user-graduate" style="color: {{ $sa->percentage >= 75 ? '#198754' : ($sa->percentage >= 50 ? '#ffc107' : '#dc3545') }};"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 text-capitalize" style="font-size: 0.85rem;">{{ $sa->student ? $sa->student->first_name . ' ' . $sa->student->last_name : 'N/A' }}</h6>
                    <small class="text-muted">{{ $sa->student ? $sa->student->roll_no : '-' }}</small>
                  </div>
                </div>
                <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar {{ $sa->percentage >= 75 ? 'bg-success' : ($sa->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                    role="progressbar" style="width: {{ $sa->percentage }}%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success" style="font-size: 0.7rem;">P: {{ $sa->present }}</span>
                    <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">A: {{ $sa->absent }}</span>
                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">T: {{ $sa->total }}</span>
                  </div>
                  <span class="fw-bold {{ $sa->percentage >= 75 ? 'text-success' : ($sa->percentage >= 50 ? 'text-warning' : 'text-danger') }}">{{ $sa->percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
          @endforeach
          @else
          <div class="col-12">
            <div class="text-center py-4 text-muted">No attendance records found</div>
          </div>
          @endif
        </div>
      </div>
    </div>

    {{-- All Attendance Records Table --}}
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="fas fa-calendar-check me-2 text-info"></i>All Attendance Records</h6>
        <span class="badge bg-info">{{ $allAttendance->count() }} records</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
          <table class="table table-sm table-hover table-striped mb-0" id="exportTable">
            <thead class="table-light sticky-top">
              <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @if($allAttendance->count())
              @php $sl = 1; @endphp
              @foreach($allAttendance as $att)
              <tr>
                <td>{{ $sl++ }}</td>
                <td class="text-capitalize">{{ $att->student ? $att->student->first_name . ' ' . $att->student->last_name : 'N/A' }}</td>
                <td><span class="text-uppercase">{{ $att->student ? $att->student->roll_no : '-' }}</span></td>
                <td>{{ \Carbon\Carbon::parse($att->attendance_date)->format('d M Y') }}</td>
                <td>
                  @if($att->status === 'present')
                  <span class="badge bg-success">Present</span>
                  @elseif($att->status === 'absent')
                  <span class="badge bg-danger">Absent</span>
                  @else
                  <span class="badge bg-secondary">{{ ucfirst($att->status) }}</span>
                  @endif
                </td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">No attendance records found</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-3 mb-4">
      <a href="{{ route('principal.courses.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Courses
      </a>
    </div>

    {{-- Subunit Feedback Modals --}}
    @foreach($subunits as $su)
    @if(isset($subunitFeedbackDetails[$su->id]) && $subunitFeedbackDetails[$su->id]->count() > 0)
    <div class="modal fade" id="feedbackModal{{ $su->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h6 class="modal-title text-white">
              <i class="fas fa-comments me-2"></i>Student Feedback - {{ $su->csoSubunit ? $su->csoSubunit->title : 'N/A' }}
            </h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-light rounded">
              <div>
                <span class="text-muted small">Total Feedback:</span>
                <span class="badge bg-info ms-1">{{ $subunitFeedbackDetails[$su->id]->count() }}</span>
              </div>
              <div>
                <span class="text-muted small">Avg Rating:</span>
                <span class="badge bg-primary ms-1">{{ $subunitFeedback[$su->id]['avg_rating'] ? number_format($subunitFeedback[$su->id]['avg_rating'], 1) : '-' }} / 5</span>
              </div>
            </div>
            @foreach($subunitFeedbackDetails[$su->id] as $fb)
            <div class="card border mb-2">
              <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                      <i class="fas fa-user text-primary" style="font-size: 0.75rem;"></i>
                    </div>
                    <div>
                      <strong class="text-capitalize" style="font-size: 0.85rem;">{{ $fb->student ? $fb->student->first_name . ' ' . $fb->student->last_name : 'N/A' }}</strong>
                      <div class="text-muted" style="font-size: 0.7rem;">{{ $fb->student ? $fb->student->roll_no : '-' }}</div>
                    </div>
                  </div>
                  <div>
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fas fa-star {{ $i <= $fb->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.75rem;"></i>
                      @endfor
                      <span class="badge bg-primary ms-1">{{ $fb->rating }}/5</span>
                  </div>
                </div>
                @if($fb->feedback)
                <p class="mb-0 mt-2 text-muted small" style="font-size: 0.8rem;"><i class="fas fa-quote-left me-1 text-muted"></i>{{ $fb->feedback }}</p>
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endif
    @endforeach
  </main>
</div>


@include('includes.footer')
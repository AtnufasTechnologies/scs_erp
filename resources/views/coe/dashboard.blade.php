@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">COE Home</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!--start welcome section-->
    <div class="row">
      <div class="col-12">
        <div class="card gradient-coe shadow-lg border-0 mb-4">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h4 class="text-white fw-bold mb-2">Welcome to COE Dashboard, {{ Auth::user()->name }}!</h4>
                <p class="text-white-50 mb-0">Manage exams, attendance, evaluations, and faculty duties efficiently.</p>
              </div>
              <div class="col-md-4 text-md-end">
                <div class="display-5 text-white fw-bold">{{ date('d M Y') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end welcome section-->

    <!--start stats cards-->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-user-graduate text-primary" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Students</p>
                <h4 class="mb-0 fw-bold" id="totalStudents">{{ $totalStudents }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-clipboard-list text-success" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Today's Exams</p>
                <h4 class="mb-0 fw-bold" id="todayExams">{{ $todayExams }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-user-check text-warning" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Attendance %</p>
                <h4 class="mb-0 fw-bold" id="attendancePercent">{{ number_format($attendancePercent, 1) }}%</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-tasks text-danger" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending Evaluations</p>
                <h4 class="mb-0 fw-bold" id="pendingEvaluations">{{ $pendingEvaluations }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end stats cards-->

    <!--start additional stats-->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-check-double text-info" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending Moderations</p>
                <h4 class="mb-0 fw-bold">{{ $pendingModerations }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-redo text-secondary" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Backlogs</p>
                <h4 class="mb-0 fw-bold">{{ $backlogs }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-money-bill-wave text-success" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending Payments</p>
                <h4 class="mb-0 fw-bold">₹{{ number_format($pendingPayments, 2) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-chalkboard-teacher text-primary" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Today's Invigilation</p>
                <h4 class="mb-0 fw-bold">{{ $todayInvigilation->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end additional stats-->

    <!--start filter section-->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Dashboard Data</h6>
          </div>
          <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
              <div class="col-md-3">
                <label for="examFilter" class="form-label">Exam</label>
                <select class="form-select" id="examFilter" name="exam_id">
                  <option value="">All Exams</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label for="dateFilter" class="form-label">Date</label>
                <input type="date" class="form-control" id="dateFilter" name="date">
              </div>
              <div class="col-md-3">
                <label for="sessionFilter" class="form-label">Session</label>
                <select class="form-select" id="sessionFilter" name="session_id">
                  <option value="">All Sessions</option>
                  @foreach($sessions as $session)
                  <option value="{{ $session->id }}">{{ $session->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Apply Filter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!--end filter section-->

    <!--start charts-->
    <div class="row mb-4">
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Attendance Overview</h6>
          </div>
          <div class="card-body">
            <canvas id="attendanceChart" height="200"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-info"></i>Evaluation Progress</h6>
          </div>
          <div class="card-body">
            <canvas id="evaluationChart" height="200"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!--end charts-->

    <!--start data tables-->
    <div class="row">
      <!-- Running Exams -->
      <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2 text-primary"></i>Running Exams</h6>
              </div>
              <div class="col-auto">
                <span class="badge bg-primary rounded-pill">{{ $runningExams->count() }} Active</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Exam Name</th>
                    <th>Date</th>
                    <th>Session</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="runningExamsTable">
                  @forelse($runningExams as $exam)
                  <tr>
                    <td><strong>{{ $exam->name }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}</td>
                    <td>{{ $exam->session_name ?? '-' }}</td>
                    <td><span class="badge bg-success">{{ ucfirst($exam->status) }}</span></td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">No running exams at the moment</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Summary -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-user-check me-2 text-success"></i>Attendance Summary</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Exam</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Percent</th>
                  </tr>
                </thead>
                <tbody id="attendanceSummaryTable">
                  @forelse($attendanceSummary as $row)
                  <tr>
                    <td><strong>{{ $row->exam_name }}</strong></td>
                    <td><span class="badge bg-success">{{ $row->present }}</span></td>
                    <td><span class="badge bg-danger">{{ $row->absent }}</span></td>
                    <td>{{ $row->percent }}%</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">No attendance data available</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Invigilation Duties -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Invigilation Duties</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Faculty</th>
                    <th>Date</th>
                    <th>Session</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="invigilationDutiesTable">
                  @forelse($invigilationDuties as $duty)
                  <tr>
                    <td><strong>{{ $duty->faculty_name }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($duty->date)->format('d M Y') }}</td>
                    <td>{{ $duty->session_name ?? '-' }}</td>
                    <td><span class="badge bg-{{ $duty->status == 'completed' ? 'success' : 'warning' }}">{{ ucfirst($duty->status) }}</span></td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">No invigilation duties scheduled</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Evaluation Status -->
      <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-info"></i>Evaluation Status</h6>
              </div>
              <div class="col-auto">
                <span class="badge bg-danger rounded-pill">{{ $pendingEvaluations }} Pending</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Faculty</th>
                    <th>Exam</th>
                    <th>Status</th>
                    <th>Copies Assigned</th>
                    <th>Copies Evaluated</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody id="evaluationStatusTable">
                  @forelse($evaluationStatus as $row)
                  <tr>
                    <td><strong>{{ $row->faculty_name }}</strong></td>
                    <td>{{ $row->exam_name }}</td>
                    <td><span class="badge bg-{{ $row->status == 'completed' ? 'success' : 'warning' }}">{{ ucfirst($row->status) }}</span></td>
                    <td>{{ $row->copies_assigned }}</td>
                    <td>{{ $row->copies_evaluated }}</td>
                    <td>
                      @php
                      $progress = $row->copies_assigned > 0 ? ($row->copies_evaluated / $row->copies_assigned) * 100 : 0;
                      @endphp
                      <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ $progress }}%;"></div>
                      </div>
                      <small class="text-muted">{{ number_format($progress, 0) }}%</small>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">No evaluation data available</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end data tables-->

  </main>
  <!--end page content-->
</div>

<!-- Hidden inputs for JS references -->
<input type="hidden" id="jsAttendancePresent" value="{{ $attendancePresent ?? 0 }}">
<input type="hidden" id="jsAttendanceAbsent" value="{{ $attendanceAbsent ?? 0 }}">
<input type="hidden" id="jsEvaluationLabels" value="{{ json_encode($evaluationLabels ?? []) }}">
<input type="hidden" id="jsEvaluationData" value="{{ json_encode($evaluationData ?? []) }}">

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stats-card {
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
  }

  .card {
    transition: all 0.3s ease;
  }

  .card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  }

  @media (max-width: 768px) {
    .card-body {
      padding: 1.25rem 0.75rem;
    }
  }
</style>

<script>
  // Chart.js - Attendance Overview
  const attendanceChart = new Chart(document.getElementById('attendanceChart'), {
    type: 'doughnut',
    data: {
      labels: ['Present', 'Absent'],
      datasets: [{
        data: [parseFloat(document.getElementById('jsAttendancePresent').value), parseFloat(document.getElementById('jsAttendanceAbsent').value)],
        backgroundColor: ['#198754', '#dc3545'],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        }
      }
    }
  });

  // Chart.js - Evaluation Progress
  const evaluationChart = new Chart(document.getElementById('evaluationChart'), {
    type: 'bar',
    data: {
      labels: JSON.parse(document.getElementById('jsEvaluationLabels').value),
      datasets: [{
        label: 'Evaluated Copies',
        data: JSON.parse(document.getElementById('jsEvaluationData').value),
        backgroundColor: '#0d6efd',
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });

  // AJAX filtering
  $('#filterForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: "{{ route('coe.dashboard.filter') }}",
      method: 'GET',
      data: $(this).serialize(),
      beforeSend: function() {
        $('#filterForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...');
      },
      success: function(data) {
        if (data.html) {
          $('#runningExamsTable').html(data.html);
        }
        // Reload page or update other sections as needed
      },
      error: function(xhr) {
        console.error('Filter error:', xhr);
        alert('Failed to apply filter. Please try again.');
      },
      complete: function() {
        $('#filterForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-filter me-2"></i>Apply Filter');
      }
    });
  });
</script>

@include('includes.footer')
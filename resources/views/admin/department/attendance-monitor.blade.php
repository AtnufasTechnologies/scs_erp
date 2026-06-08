@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid">

    <!-- Header -->
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1a1a1a;">
              <i class="fas fa-clipboard-check me-2" style="color: #5b4cdb;"></i>Faculty Attendance Monitor
            </h3>
            <p class="text-muted mb-0">{{ $subject->title }} - Department Wide Attendance Records</p>
          </div>
          <div>
            <a href="{{ route('department.dashboard') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm" style="border-radius: 12px; border-left: 4px solid #6366f1;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Total Records</h6>
                <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
              </div>
              <div class="icon-circle bg-primary-light">
                <i class="fas fa-file-alt text-primary"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card shadow-sm" style="border-radius: 12px; border-left: 4px solid #10b981;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Present</h6>
                <h3 class="mb-0 fw-bold text-success">{{ number_format($stats['present']) }}</h3>
                <small class="text-success">{{ $stats['present_percentage'] }}%</small>
              </div>
              <div class="icon-circle bg-success-light">
                <i class="fas fa-check text-success"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card shadow-sm" style="border-radius: 12px; border-left: 4px solid #ef4444;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Absent</h6>
                <h3 class="mb-0 fw-bold text-danger">{{ number_format($stats['absent']) }}</h3>
              </div>
              <div class="icon-circle bg-danger-light">
                <i class="fas fa-times text-danger"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-3">
        <div class="card shadow-sm" style="border-radius: 12px; border-left: 4px solid #f59e0b;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-muted mb-1">Late</h6>
                <h3 class="mb-0 fw-bold text-warning">{{ number_format($stats['late']) }}</h3>
              </div>
              <div class="icon-circle bg-warning-light">
                <i class="fas fa-clock text-warning"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Weekly Analysis Chart -->
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="card shadow-sm" style="border-radius: 16px;">
          <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
            <h5 class="mb-0 fw-bold">
              <i class="fas fa-chart-bar me-2" style="color: #5b4cdb;"></i>Weekly Analysis
            </h5>
          </div>
          <div class="card-body">
            <canvas id="weeklyChart" style="max-height: 300px;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4" style="border-radius: 16px;">
      <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
        <h5 class="mb-0 fw-bold">
          <i class="fas fa-filter me-2" style="color: #5b4cdb;"></i>Filters
        </h5>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('department.attendance.monitor') }}">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate) }}">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">End Date</label>
              <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate) }}">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Faculty</label>
              <select name="faculty_id" class="form-select">
                <option value="">All Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                  {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Batch</label>
              <select name="batch" class="form-select">
                <option value="">All Batches</option>
                @foreach($batches as $batch)
                <option value="{{ $batch->id }}" {{ request('batch') == $batch->id ? 'selected' : '' }}>
                  {{ $batch->batch_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search me-1"></i>Apply Filters
            </button>
            <a href="{{ route('department.attendance.monitor') }}" class="btn btn-secondary">
              <i class="fas fa-redo me-1"></i>Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Attendance Records Table -->
    <div class="card shadow-sm" style="border-radius: 16px;">
      <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
        <h5 class="mb-0 fw-bold">
          <i class="fas fa-list me-2" style="color: #5b4cdb;"></i>Attendance Records
          <span class="badge bg-primary ms-2">{{ $attendanceRecords->total() }}</span>
        </h5>
      </div>
      <div class="card-body p-0">
        @if($attendanceRecords->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background: #f9fafb;">
              <tr>
                <th style="padding: 14px 16px; color: #6b7280; font-weight: 600;">#</th>
                <th style="color: #6b7280; font-weight: 600;">Date</th>
                <th style="color: #6b7280; font-weight: 600;">Student</th>
                <th style="color: #6b7280; font-weight: 600;">Roll No</th>
                <th style="color: #6b7280; font-weight: 600;">Course</th>
                <th style="color: #6b7280; font-weight: 600;">Status</th>
                <th style="color: #6b7280; font-weight: 600;">Time</th>
                <th style="color: #6b7280; font-weight: 600;">Remarks</th>
              </tr>
            </thead>
            <tbody>
              @foreach($attendanceRecords as $index => $record)
              <tr>
                <td style="padding: 14px 16px;">{{ $attendanceRecords->firstItem() + $index }}</td>
                <td>{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                <td>
                  <div class="fw-semibold">{{ $record->student->first_name }} {{ $record->student->last_name }}</div>
                  <small class="text-muted">{{ $record->student->register_no }}</small>
                </td>
                <td><code>{{ $record->student->roll_no }}</code></td>
                <td>
                  <small class="text-muted">
                    {{ $record->courseinfo->course_name ?? ($record->routine->subjectsyllabus->coursemasterrelation->course_name ?? 'N/A') }}
                  </small>
                </td>
                <td>
                  @if($record->status == 'present')
                  <span class="badge bg-success">Present</span>
                  @elseif($record->status == 'absent')
                  <span class="badge bg-danger">Absent</span>
                  @elseif($record->status == 'late')
                  <span class="badge bg-warning text-dark">Late</span>
                  @elseif($record->status == 'excused')
                  <span class="badge bg-info">Excused</span>
                  @endif
                </td>
                <td>
                  <small class="text-muted">
                    {{ $record->lecture_start_time ? \Carbon\Carbon::parse($record->lecture_start_time)->format('h:i A') : '-' }}
                  </small>
                </td>
                <td>
                  <small class="text-muted">{{ $record->remarks ? Str::limit($record->remarks, 30) : '-' }}</small>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-white border-0">
          {{ $attendanceRecords->links() }}
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">No attendance records found for the selected filters.</p>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>

@include('includes.footer')

<style>
  .icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
  }

  .bg-primary-light {
    background-color: rgba(99, 102, 241, 0.1);
  }

  .bg-success-light {
    background-color: rgba(16, 185, 129, 0.1);
  }

  .bg-danger-light {
    background-color: rgba(239, 68, 68, 0.1);
  }

  .bg-warning-light {
    background-color: rgba(245, 158, 11, 0.1);
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Weekly Analysis Chart
  const weeklyData = @json($weeklyAnalysis);

  const ctx = document.getElementById('weeklyChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: weeklyData.map(w => w.week),
      datasets: [{
          label: 'Present',
          data: weeklyData.map(w => w.present),
          backgroundColor: 'rgba(16, 185, 129, 0.8)',
          borderColor: 'rgba(16, 185, 129, 1)',
          borderWidth: 1
        },
        {
          label: 'Absent',
          data: weeklyData.map(w => w.absent),
          backgroundColor: 'rgba(239, 68, 68, 0.8)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        title: {
          display: true,
          text: 'Weekly Attendance Breakdown (Present vs Absent)'
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 10
          }
        }
      }
    }
  });
</script>
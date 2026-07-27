@include('includes.header')

<div class="wrapper">
  @include('student-affairs.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <h3 class="mb-3">Dean of Student Affairs Dashboard</h3>
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Total Students</small>
              <h4>{{ $student_counts['total'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Below 40% Attendance</small>
              <h4 class="text-danger">{{ $attendance_alerts['below_40'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Open Discipline Cases</small>
              <h4>{{ $discipline_open_cases ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Open Counselling Cases</small>
              <h4>{{ $counselling_open_cases ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-header">Attendance Alert Thresholds</div>
            <div class="card-body">
              <canvas id="attendanceThresholdChart" height="110"
                data-below-75="{{ (int) ($attendance_alerts['below_75'] ?? 0) }}"
                data-below-60="{{ (int) ($attendance_alerts['below_60'] ?? 0) }}"
                data-below-50="{{ (int) ($attendance_alerts['below_50'] ?? 0) }}"
                data-below-40="{{ (int) ($attendance_alerts['below_40'] ?? 0) }}"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm">
            <div class="card-header">Event Participation Snapshot</div>
            <div class="card-body">
              <p class="mb-1">EC Events: <strong>{{ $event_summary['ec_events'] ?? 0 }}</strong></p>
              <p class="mb-1">EC Program Participants: <strong>{{ $event_summary['ec_participants'] ?? 0 }}</strong></p>
              <p class="mb-1">Department Activities: <strong>{{ $event_summary['department_activities'] ?? 0 }}</strong></p>
              <p class="mb-0">Department Participants: <strong>{{ $event_summary['department_participants'] ?? 0 }}</strong></p>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Critical Attendance Students (&lt; 40%)</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Student</th>
                <th>Total</th>
                <th>Present</th>
                <th>Attendance %</th>
              </tr>
            </thead>
            <tbody>
              @forelse($below_40_students as $row)
              <tr>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['total_classes'] }}</td>
                <td>{{ $row['present_classes'] }}</td>
                <td><span class="badge bg-danger">{{ $row['attendance_pct'] }}%</span></td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No critical students</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const attendanceCtx = document.getElementById('attendanceThresholdChart');
  if (attendanceCtx) {
    const attendanceData = [
      Number.parseInt(attendanceCtx.dataset.below75 || '0', 10),
      Number.parseInt(attendanceCtx.dataset.below60 || '0', 10),
      Number.parseInt(attendanceCtx.dataset.below50 || '0', 10),
      Number.parseInt(attendanceCtx.dataset.below40 || '0', 10)
    ];

    new Chart(attendanceCtx, {
      type: 'bar',
      data: {
        labels: ['Below 75%', 'Below 60%', 'Below 50%', 'Below 40%'],
        datasets: [{
          label: 'Students',
          data: attendanceData,
          backgroundColor: ['#f59e0b', '#fb923c', '#ef4444', '#b91c1c']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }
</script>
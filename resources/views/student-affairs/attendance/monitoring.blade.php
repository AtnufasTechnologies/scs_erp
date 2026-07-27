@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Attendance Monitoring (Read-only)</h3>
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Below 75%</small>
              <h4>{{ $thresholds['below_75'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Below 60%</small>
              <h4>{{ $thresholds['below_60'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Below 50%</small>
              <h4>{{ $thresholds['below_50'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card">
            <div class="card-body"><small>Below 40%</small>
              <h4 class="text-danger">{{ $thresholds['below_40'] }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Critical Students (&lt; 40%)</div>
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
              @forelse($criticalRows as $row)
              <tr>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['total_classes'] }}</td>
                <td>{{ $row['present_classes'] }}</td>
                <td><span class="badge bg-danger">{{ $row['attendance_pct'] }}%</span></td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No critical attendance records.</td>
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
@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Dean Reports</h3>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body"><small>Discipline Cases</small>
              <h4>{{ $studentAffairs['discipline_cases'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body"><small>Counselling Cases</small>
              <h4>{{ $studentAffairs['counselling_cases'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body"><small>Club Memberships</small>
              <h4>{{ $studentAffairs['club_memberships'] }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Attendance Shortage Report (&lt; 75%)</div>
        <div class="card-body table-responsive" style="max-height: 340px;">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Student</th>
                <th>Total</th>
                <th>Present</th>
                <th>%</th>
              </tr>
            </thead>
            <tbody>
              @foreach($attendanceShortage as $row)
              <tr>
                <td>{{ $row['student_name'] }}</td>
                <td>{{ $row['total_classes'] }}</td>
                <td>{{ $row['present_classes'] }}</td>
                <td>{{ $row['attendance_pct'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Attendance Regularization Register</div>
        <div class="card-body table-responsive" style="max-height: 340px;">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Student</th>
                <th>Date</th>
                <th>Original</th>
                <th>Effective</th>
                <th>Request</th>
              </tr>
            </thead>
            <tbody>
              @foreach($regularizationRegister as $item)
              <tr>
                <td>{{ ($item->student->first_name ?? '') . ' ' . ($item->student->last_name ?? '') }}</td>
                <td>{{ optional($item->attendance_date)->format('d-M-Y') }}</td>
                <td>{{ $item->original_status }}</td>
                <td>{{ $item->effective_status }}</td>
                <td>{{ $item->regularization->request_no ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
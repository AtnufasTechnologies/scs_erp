@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{ asset('admin/images/logo.png') }}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Examination Results</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
    </div>
  </div>
</header>

<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class="fas fa-file-alt me-2 text-primary"></i>
        @if($result->examSession)
        {{ $result->examSession->name }} — Semester {{ $result->examSession->semester }}
        @else
        {{ $result->exam->name ?? 'Result' }}
        @endif
      </h4>
      <p class="text-muted mb-0">Enrollment No: <strong>{{ strtoupper($enrollmentNo) }}</strong></p>
    </div>
    <a href="{{ url('erp/student/results/search') }}" class="btn btn-outline-secondary"
      onclick="event.preventDefault(); document.getElementById('jsBackForm').submit();">
      <i class="fas fa-arrow-left me-2"></i>Back to Results
    </a>
    <form id="jsBackForm" method="POST" action="{{ url('erp/student/results/search') }}" class="d-none">
      @csrf
      <input type="hidden" name="enrollment_no" value="{{ $enrollmentNo }}">
    </form>
  </div>

  <!-- Result Summary -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <div class="fs-1 fw-bold {{ $result->sgpa >= 7 ? 'text-success' : ($result->sgpa >= 5 ? 'text-warning' : 'text-danger') }}">
          {{ number_format($result->sgpa, 2) }}
        </div>
        <div class="text-muted">SGPA</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        <div class="fs-1 fw-bold text-primary">{{ number_format($result->percentage, 2) }}%</div>
        <div class="text-muted">Percentage</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 text-center p-4">
        @if($result->result_status == 'pass')
        <div class="fs-1 fw-bold text-success">PASS</div>
        @elseif($result->result_status == 'fail')
        <div class="fs-1 fw-bold text-danger">FAIL</div>
        @elseif($result->result_status == 'withheld')
        <div class="fs-1 fw-bold text-dark">WITHHELD</div>
        @else
        <div class="fs-1 fw-bold text-warning">{{ strtoupper($result->result_status) }}</div>
        @endif
        <div class="text-muted">Result</div>
      </div>
    </div>
  </div>

  <!-- Subject-wise Marks Table -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
      <h5 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-info"></i>Subject-wise Marks</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">#</th>
              <th>Subject Code</th>
              <th>Subject Name</th>
              <th class="text-center">FA<br><small class="text-muted">(Internal)</small></th>
              <th class="text-center">SA<br><small class="text-muted">(External)</small></th>
              <th class="text-center">Total</th>
              <th class="text-center">Credit</th>
              <th class="text-center">Grade Point<br><small class="text-muted">(Out of 10)</small></th>
              <th class="text-center">Grade</th>
            </tr>
          </thead>
          <tbody>
            @php $totalCredits = 0; $totalWeightedGP = 0; @endphp
            @forelse($result->resultSubjects as $index => $rs)
            <tr class="{{ $rs->result_status == 'Withheld' ? 'table-dark' : ($rs->result_status == 'Absent' ? 'table-warning' : ($rs->grade == 'F' ? 'table-danger' : '')) }}">
              <td class="ps-4">{{ $index + 1 }}</td>
              <td><span class="fw-semibold">{{ $rs->subject_code }}</span></td>
              <td>{{ $rs->subject_name }}</td>
              <td class="text-center">
                @if($rs->result_status == 'Withheld')
                <span class="text-muted">&mdash;</span>
                @else
                {{ $rs->fa_marks !== null ? number_format($rs->fa_marks, 2) : '&mdash;' }}
                @endif
              </td>
              <td class="text-center">
                @if($rs->result_status == 'Withheld')
                <span class="text-muted">&mdash;</span>
                @else
                {{ $rs->sa_marks !== null ? number_format($rs->sa_marks, 2) : '&mdash;' }}
                @endif
              </td>
              <td class="text-center fw-bold">
                @if($rs->result_status == 'Withheld')
                <span class="text-muted">&mdash;</span>
                @elseif($rs->result_status == 'Absent')
                <span class="text-danger">0</span>
                @else
                {{ number_format($rs->total_marks, 2) }}
                @endif
              </td>
              <td class="text-center">{{ $rs->credits }}</td>
              <td class="text-center">
                <span class="fw-bold {{ $rs->grade_point >= 7 ? 'text-success' : ($rs->grade_point >= 5 ? 'text-warning' : 'text-danger') }}">
                  {{ number_format($rs->grade_point, 2) }}
                </span>
              </td>
              <td class="text-center">
                @if($rs->grade == 'Ab')
                <span class="badge bg-warning text-dark">Ab</span>
                @elseif($rs->grade == 'W')
                <span class="badge bg-dark">W</span>
                @elseif($rs->grade == 'F')
                <span class="badge bg-danger">F</span>
                @else
                <span class="badge bg-success">{{ $rs->grade }}</span>
                @endif
              </td>
            </tr>
            @php
            if ($rs->result_status !== 'Withheld' && $rs->credits > 0) {
            $totalCredits += $rs->credits;
            $totalWeightedGP += ($rs->grade_point * $rs->credits);
            }
            @endphp
            @empty
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">No subject details available.</td>
            </tr>
            @endforelse
          </tbody>
          @if($result->resultSubjects->count() > 0)
          <tfoot class="bg-light fw-bold">
            <tr>
              <td class="ps-4" colspan="3">Total</td>
              <td class="text-center">&mdash;</td>
              <td class="text-center">&mdash;</td>
              <td class="text-center">&mdash;</td>
              <td class="text-center">{{ $totalCredits }}</td>
              <td class="text-center text-primary">
                {{ $totalCredits > 0 ? number_format($totalWeightedGP / $totalCredits, 2) : '0.00' }}
              </td>
              <td class="text-center">
                SGPA: <span class="text-primary">{{ number_format($result->sgpa, 2) }}</span>
              </td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>

  <!-- SGPA Explanation -->
  <div class="card shadow-sm border-0 mt-4">
    <div class="card-body">
      <p class="mb-0 text-muted small">
        <i class="fas fa-info-circle me-1"></i>
        <strong>SGPA</strong> = &sum;(Grade Point &times; Credits) / &sum;Credits
        = {{ number_format($totalWeightedGP, 2) }} / {{ $totalCredits }}
        = <strong>{{ $totalCredits > 0 ? number_format($totalWeightedGP / $totalCredits, 2) : '0.00' }}</strong>
        &nbsp;&middot;&nbsp; Published on {{ $result->published_at ? $result->published_at->format('d M Y') : 'N/A' }}
      </p>
    </div>
  </div>
</div>

@include('includes.footer')
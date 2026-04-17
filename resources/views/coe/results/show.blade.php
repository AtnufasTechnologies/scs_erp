@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Results</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index') }}">Results</a></li>
            <li class="breadcrumb-item active" aria-current="page">Result Details</li>
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
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-file-alt me-2"></i>Result Details</h3>
                  <p class="text-muted mb-0">Subject-wise marks breakdown with grades and SGPA</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exam-results.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Results
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Student Info Card -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Information</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="fw-semibold text-muted" style="width: 40%">Enrollment No</td>
                  <td>{{ $result->student->enrollment_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Student ID (ERP)</td>
                  <td>{{ $result->student->erp_student_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Program</td>
                  <td>{{ $result->student->program_id ?? 'N/A' }}</td>
                </tr>
                @if($result->examSession)
                <tr>
                  <td class="fw-semibold text-muted">Session</td>
                  <td>{{ $result->examSession->name }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Semester</td>
                  <td><span class="badge bg-primary">Semester {{ $result->examSession->semester }}</span></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Academic Year</td>
                  <td>{{ $result->examSession->academic_year }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-success"></i>Result Summary</h5>
            </div>
            <div class="card-body">
              <div class="row text-center">
                <div class="col-3">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold {{ $result->sgpa >= 7 ? 'text-success' : ($result->sgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                      {{ number_format($result->sgpa, 2) }}
                    </div>
                    <div class="text-muted small">SGPA</div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold {{ $result->cgpa ? ($result->cgpa >= 7 ? 'text-success' : ($result->cgpa >= 5 ? 'text-warning' : 'text-danger')) : 'text-muted' }}">
                      {{ $result->cgpa ? number_format($result->cgpa, 2) : '—' }}
                    </div>
                    <div class="text-muted small">CGPA</div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold text-info">{{ $result->earned_credits ?? '—' }}</div>
                    <div class="text-muted small">Earned Credits</div>
                  </div>
                </div>
                <div class="col-3">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold">
                      @if($result->result_status == 'pass')
                      <span class="text-success">PASS</span>
                      @elseif($result->result_status == 'fail')
                      <span class="text-danger">FAIL</span>
                      @elseif($result->result_status == 'withheld')
                      <span class="text-dark">WITHHELD</span>
                      @else
                      <span class="text-warning">{{ strtoupper($result->result_status) }}</span>
                      @endif
                    </div>
                    <div class="text-muted small">Status</div>
                  </div>
                </div>
              </div>
              <div class="row text-center mt-3">
                <div class="col-12">
                  <span class="text-muted small">{{ number_format($result->percentage, 2) }}% Overall Percentage</span>
                </div>
              </div>
              <div class="mt-3 text-center">
                @if($result->is_published)
                <span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-globe me-1"></i>Published on {{ $result->published_at->format('d M Y, h:i A') }}</span>
                @else
                <span class="badge bg-secondary fs-6 px-3 py-2"><i class="fas fa-lock me-1"></i>Not Published</span>
                @endif
                @if($isLocked)
                <span class="badge bg-dark fs-6 px-3 py-2 ms-2"><i class="fas fa-lock me-1"></i>Session Locked</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subject-wise Results Table -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-info"></i>Subject-wise Marks Breakdown</h5>
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
                  <th class="text-center">Status</th>
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
                    <span class="text-muted">—</span>
                    @else
                    {{ $rs->fa_marks !== null ? number_format($rs->fa_marks, 2) : '—' }}
                    @endif
                  </td>
                  <td class="text-center">
                    @if($rs->result_status == 'Withheld')
                    <span class="text-muted">—</span>
                    @else
                    {{ $rs->sa_marks !== null ? number_format($rs->sa_marks, 2) : '—' }}
                    @endif
                  </td>
                  <td class="text-center fw-bold">
                    @if($rs->result_status == 'Withheld')
                    <span class="text-muted">—</span>
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
                  <td class="text-center">
                    @if($rs->result_status == 'Withheld')
                    <span class="badge bg-dark">Withheld</span>
                    @elseif($rs->result_status == 'Absent')
                    <span class="badge bg-warning text-dark">Absent</span>
                    @else
                    <span class="badge bg-light text-dark">Normal</span>
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
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    No subject results found.
                  </td>
                </tr>
                @endforelse
              </tbody>
              @if($result->resultSubjects->count() > 0)
              <tfoot class="bg-light fw-bold">
                <tr>
                  <td class="ps-4" colspan="3" class="text-end">Totals</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center">—</td>
                  <td class="text-center">{{ $totalCredits }}</td>
                  <td class="text-center text-primary">
                    {{ $totalCredits > 0 ? number_format($totalWeightedGP / $totalCredits, 2) : '0.00' }}
                  </td>
                  <td class="text-center" colspan="2">
                    SGPA: <span class="text-primary">{{ number_format($result->sgpa, 2) }}</span>
                  </td>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

      <!-- SGPA & CGPA Calculation -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6 class="fw-bold mb-2"><i class="fas fa-calculator me-2 text-secondary"></i>SGPA Calculation (This Semester)</h6>
              <p class="mb-0 text-muted small">
                SGPA = &sum;(Grade Point &times; Credits) / &sum;Credits
                = {{ number_format($totalWeightedGP, 2) }} / {{ $totalCredits }}
                = <strong class="text-primary">{{ $totalCredits > 0 ? number_format($totalWeightedGP / $totalCredits, 2) : '0.00' }}</strong>
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6 class="fw-bold mb-2"><i class="fas fa-chart-line me-2 text-info"></i>CGPA (Cumulative)</h6>
              @if($result->cgpa)
              <p class="mb-0">
                <span class="fs-4 fw-bold {{ $result->cgpa >= 7 ? 'text-success' : ($result->cgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                  {{ number_format($result->cgpa, 2) }}
                </span>
                <span class="text-muted small ms-2">across all semesters</span>
              </p>
              <p class="mb-0 mt-1 text-muted small">
                Earned Credits (this semester): <strong class="text-info">{{ $result->earned_credits ?? 0 }}</strong>
              </p>
              @else
              <p class="mb-0 text-muted small">CGPA will be calculated when results are published.</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exit Certification</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exit-certification.index') }}">Exit Certification</a></li>
            <li class="breadcrumb-item active" aria-current="page">New Certification</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-plus-circle me-2"></i>New Exit Certification</h3>
                  <p class="text-muted mb-0">Check student credit eligibility and generate exit certification</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exit-certification.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <!-- Step 1: Select Student -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2 text-primary"></i>Step 1: Select Student & Check Credits</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exit-certification.create') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Student</label>
              <select name="exam_student_id" class="form-select form-select-lg" required>
                <option value="">Select Student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}" {{ ($selectedStudent && $selectedStudent->id == $student->id) ? 'selected' : '' }}>
                  {{ $student->enrollment_no }} (ID: {{ $student->erp_student_id }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Check Eligibility
              </button>
            </div>
          </form>
        </div>
      </div>

      @if($eligibility)
      <!-- Student Summary -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-success"></i>Student Credit Summary</h5>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="text-muted small">Enrollment No</div>
                <div class="fs-5 fw-bold">{{ $eligibility['student']->enrollment_no }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="text-muted small">Total Credits</div>
                <div class="fs-3 fw-bold text-primary">{{ $eligibility['total_credits'] }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="text-muted small">Semesters Completed</div>
                <div class="fs-3 fw-bold text-info">{{ $eligibility['semesters_completed'] }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="text-muted small">CGPA</div>
                <div class="fs-3 fw-bold {{ $eligibility['cgpa'] >= 7 ? 'text-success' : ($eligibility['cgpa'] >= 5 ? 'text-warning' : 'text-danger') }}">
                  {{ number_format($eligibility['cgpa'], 2) }}
                </div>
              </div>
            </div>
          </div>

          @if(!empty($eligibility['credit_summary']))
          <h6 class="fw-bold mb-3">Semester-wise Breakdown</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="bg-light">
                <tr>
                  <th>Semester</th>
                  <th class="text-center">Credits Earned</th>
                  <th class="text-center">SGPA</th>
                  <th class="text-center">Result</th>
                </tr>
              </thead>
              <tbody>
                @foreach($eligibility['credit_summary'] as $sem)
                <tr>
                  <td>Semester {{ $sem['semester'] }}</td>
                  <td class="text-center fw-bold">{{ $sem['credits_earned'] }}</td>
                  <td class="text-center">{{ isset($sem['sgpa']) ? number_format($sem['sgpa'], 2) : '—' }}</td>
                  <td class="text-center">
                    @if(($sem['result_status'] ?? '') == 'pass')
                    <span class="badge bg-success">Pass</span>
                    @elseif(($sem['result_status'] ?? '') == 'fail')
                    <span class="badge bg-danger">Fail</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($sem['result_status'] ?? '—') }}</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>

      <!-- Eligibility Matrix -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2 text-warning"></i>Step 2: Eligibility & Issue</h5>
        </div>
        <div class="card-body">
          <div class="row g-4">
            @php
            $levelColors = ['certificate' => 'info', 'diploma' => 'primary', 'degree' => 'success', 'honors' => 'warning'];
            $levelIcons = ['certificate' => 'scroll', 'diploma' => 'award', 'degree' => 'graduation-cap', 'honors' => 'star'];
            @endphp

            @foreach($eligibility['eligibility'] as $level => $info)
            <div class="col-md-3">
              <div class="card h-100 border {{ $info['eligible'] ? 'border-success' : 'border-secondary' }}">
                <div class="card-body text-center">
                  <div class="mb-3">
                    <i class="fas fa-{{ $levelIcons[$level] }} fa-3x text-{{ $levelColors[$level] }}"></i>
                  </div>
                  <h5 class="fw-bold">{{ $info['label'] }}</h5>

                  <div class="my-3">
                    <!-- Credits check -->
                    <div class="d-flex justify-content-between mb-1">
                      <span class="small">Credits</span>
                      <span>
                        @if($info['credits_ok'])
                        <i class="fas fa-check-circle text-success"></i>
                        @else
                        <i class="fas fa-times-circle text-danger"></i>
                        @endif
                        {{ $info['credits_earned'] }} / {{ $info['credits_required'] }}
                      </span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                      @php $pct = $info['credits_required'] > 0 ? min(100, ($info['credits_earned'] / $info['credits_required']) * 100) : 0; @endphp
                      <div class="progress-bar bg-{{ $info['credits_ok'] ? 'success' : 'danger' }}" style="width: {{ $pct }}%"></div>
                    </div>

                    <!-- Semesters check -->
                    <div class="d-flex justify-content-between mb-1">
                      <span class="small">Semesters</span>
                      <span>
                        @if($info['semesters_ok'])
                        <i class="fas fa-check-circle text-success"></i>
                        @else
                        <i class="fas fa-times-circle text-danger"></i>
                        @endif
                        {{ $info['semesters_completed'] }} / {{ $info['semesters_required'] }}
                      </span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                      @php $semPct = $info['semesters_required'] > 0 ? min(100, ($info['semesters_completed'] / $info['semesters_required']) * 100) : 0; @endphp
                      <div class="progress-bar bg-{{ $info['semesters_ok'] ? 'success' : 'danger' }}" style="width: {{ $semPct }}%"></div>
                    </div>
                  </div>

                  @if($info['already_issued'])
                  <span class="badge bg-dark"><i class="fas fa-ban me-1"></i>Already Issued</span>
                  @elseif($info['eligible'])
                  <form method="POST" action="{{ route('admin.exit-certification.store') }}">
                    @csrf
                    <input type="hidden" name="exam_student_id" value="{{ $selectedStudent->id }}">
                    <input type="hidden" name="exit_level" value="{{ $level }}">
                    <div class="mb-2">
                      <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                    </div>
                    <button type="submit" class="btn btn-{{ $levelColors[$level] }} w-100" onclick="return confirm('Issue {{ $info['label'] }} certification?')">
                      <i class="fas fa-certificate me-1"></i>Generate
                    </button>
                  </form>
                  @else
                  <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i>Not Eligible</span>
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')
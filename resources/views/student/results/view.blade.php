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
      <h4 class="fw-bold mb-1"><i class="fas fa-user-graduate me-2 text-primary"></i>Results for {{ strtoupper($enrollmentNo) }}</h4>
      <p class="text-muted mb-0">Enrollment No: <strong>{{ $examStudent->enrollment_no }}</strong></p>
    </div>
    <a href="{{ url('erp/student/results') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back
    </a>
  </div>

  @if($results->count() > 0)
  <div class="row g-4">
    @foreach($results as $result)
    <div class="col-md-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
              @if($result->examSession)
              {{ $result->examSession->name }}
              @else
              {{ $result->exam->name ?? 'Exam' }}
              @endif
            </h5>
            @if($result->examSession)
            <span class="badge bg-primary">Semester {{ $result->examSession->semester }}</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <div class="row text-center mb-3">
            <div class="col-4">
              <div class="fs-3 fw-bold {{ $result->sgpa >= 7 ? 'text-success' : ($result->sgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                {{ number_format($result->sgpa, 2) }}
              </div>
              <div class="text-muted small">SGPA</div>
            </div>
            <div class="col-4">
              <div class="fs-3 fw-bold text-primary">{{ number_format($result->percentage, 2) }}%</div>
              <div class="text-muted small">Percentage</div>
            </div>
            <div class="col-4">
              @if($result->result_status == 'pass')
              <div class="fs-3 fw-bold text-success">PASS</div>
              @elseif($result->result_status == 'fail')
              <div class="fs-3 fw-bold text-danger">FAIL</div>
              @elseif($result->result_status == 'withheld')
              <div class="fs-3 fw-bold text-dark">WITHHELD</div>
              @else
              <div class="fs-3 fw-bold text-warning">{{ strtoupper($result->result_status) }}</div>
              @endif
              <div class="text-muted small">Status</div>
            </div>
          </div>
          <div class="text-muted small mb-3">
            <i class="fas fa-book me-1"></i>{{ $result->resultSubjects->count() }} subjects
            @if($result->examSession)
            &middot; {{ $result->examSession->academic_year }}
            @endif
          </div>
        </div>
        <div class="card-footer bg-white text-end">
          <a href="{{ url('erp/student/results/' . $result->id . '?enrollment_no=' . urlencode($enrollmentNo)) }}"
            class="btn btn-primary btn-sm">
            <i class="fas fa-eye me-1"></i>View Details
          </a>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="text-center py-5 text-muted">
    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
    <p>No published results available.</p>
  </div>
  @endif
</div>

@include('includes.footer')
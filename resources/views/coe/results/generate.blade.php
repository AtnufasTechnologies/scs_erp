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
            <li class="breadcrumb-item active" aria-current="page">Generate</li>
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
                <div class="col-md-8">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-cogs me-2"></i>Generate Results</h3>
                  <p class="text-muted mb-0">Auto-generate semester results from marks entries with FA/SA breakdown, grades, and SGPA</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.exam-results.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Results
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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Generation Form -->
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-sliders-h me-2 text-primary"></i>Generation Parameters</h5>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="{{ route('admin.exam-results.do-generate') }}" id="generateForm">
                @csrf

                <div class="mb-4">
                  <label class="form-label fw-semibold">Exam Session <span class="text-danger">*</span></label>
                  <select name="exam_session_id" class="form-select form-select-lg" required>
                    <option value="">Select Exam Session</option>
                    @foreach($sessions as $session)
                    <option value="{{ $session->id }}">
                      {{ $session->name }} — Semester {{ $session->semester }} ({{ $session->academic_year }})
                    </option>
                    @endforeach
                  </select>
                  <div class="form-text">Select the exam session to generate results for. All students with marks in this session will be processed.</div>
                  @error('exam_session_id')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label class="form-label fw-semibold">Exam (Optional)</label>
                  <select name="exam_id" class="form-select">
                    <option value="">Auto-detect from session</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->exam_type ?? '' }})</option>
                    @endforeach
                  </select>
                  <div class="form-text">Optionally link results to a specific exam. Used for attendance/malpractice lookups.</div>
                </div>

                <div class="card bg-light border-0 mb-4">
                  <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-info"></i>Generation Rules</h6>
                    <ul class="mb-0 small">
                      <li><strong>FA (Internal Assessment):</strong> Derived from component weightages if available</li>
                      <li><strong>SA (External Assessment):</strong> From exam marks entries</li>
                      <li><strong>Total:</strong> FA + SA marks</li>
                      <li><strong>Grade & Grade Point:</strong> Calculated from program grade mappings (out of 10)</li>
                      <li><strong>SGPA:</strong> &sum;(Grade Point &times; Credits) / &sum;Credits</li>
                      <li><strong>Absent:</strong> Marks set to 0, grade = Ab</li>
                      <li><strong>Malpractice:</strong> Result withheld, grade = W</li>
                      <li>Already published results will be <strong>skipped</strong></li>
                    </ul>
                  </div>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-success btn-lg" id="jsGenerateBtn">
                    <i class="fas fa-cogs me-2"></i>Generate Results
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('generateForm');
    var btn = document.getElementById('jsGenerateBtn');

    form.addEventListener('submit', function() {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating... Please wait';
    });
  });
</script>
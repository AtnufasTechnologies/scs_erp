@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index') }}">Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Exam</li>
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
              <h3 class="text-white fw-bold mb-0">
                <i class="fas fa-plus-circle me-2"></i>Create New Exam
              </h3>
            </div>
          </div>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fa fa-exclamation-triangle me-2"></i>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Create Form -->
      <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Exam Information</h6>
            </div>
            <div class="card-body p-4">
              <form action="{{ route('coe.exams.store') }}" method="POST" id="examForm">
                @csrf

                <!-- Basic Information -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Basic Information</h6>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="examName" class="form-label fw-bold">
                      Exam Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="examName" name="name"
                      placeholder="e.g., End Semester Examination - May 2026"
                      value="{{ old('name') }}" required>
                    <small class="text-muted">Enter the full name of the examination</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="examType" class="form-label fw-bold">
                      Exam Type <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="examType" name="exam_type" required>
                      <option value="" selected disabled>Select exam type...</option>
                      <option value="Regular" {{ old('exam_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                      <option value="Backlog" {{ old('exam_type') == 'Backlog' ? 'selected' : '' }}>Backlog</option>
                      <option value="Improvement" {{ old('exam_type') == 'Improvement' ? 'selected' : '' }}>Improvement</option>
                      <option value="Special" {{ old('exam_type') == 'Special' ? 'selected' : '' }}>Special</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label fw-bold">
                      Semester <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="semester" name="semester" required>
                      <option value="" selected disabled>Select semester...</option>
                      <option value="Odd" {{ old('semester') == 'Odd' ? 'selected' : '' }}>Odd Semester</option>
                      <option value="Even" {{ old('semester') == 'Even' ? 'selected' : '' }}>Even Semester</option>
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="programId" class="form-label fw-bold">
                      Program <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="programId" name="program_id" required>
                      <option value="" selected disabled>Select program...</option>
                      @foreach($programs as $program)
                      <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->code }})
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="regulationId" class="form-label fw-bold">
                      Regulation <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="regulationId" name="regulation_id" required>
                      <option value="" selected disabled>Select regulation...</option>
                      @foreach($regulations as $regulation)
                      <option value="{{ $regulation->id }}" {{ old('regulation_id') == $regulation->id ? 'selected' : '' }}>
                        {{ $regulation->regulation_name }} ({{ $regulation->start_year }}-{{ $regulation->end_year }})
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <hr>

                <!-- Schedule Information -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Schedule</h6>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="startDate" class="form-label fw-bold">
                      Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="startDate" name="start_date"
                      value="{{ old('start_date') }}" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="endDate" class="form-label fw-bold">
                      End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="endDate" name="end_date"
                      value="{{ old('end_date') }}" required>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label for="examDate" class="form-label fw-bold">
                      Primary Exam Date
                    </label>
                    <input type="date" class="form-control" id="examDate" name="exam_date"
                      value="{{ old('exam_date') }}">
                    <small class="text-muted">Main exam date (optional)</small>
                  </div>
                </div>

                <hr>

                <!-- Status -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Status</h6>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="status" class="form-label fw-bold">
                      Exam Status <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="status" name="status" required>
                      <option value="upcoming" {{ old('status', 'upcoming') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                      <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                      <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                      <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                  </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                  <a href="{{ route('coe.exams.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i>Cancel
                  </a>
                  <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                    <span id="submitBtnText"><i class="fa fa-save me-2"></i>Create Exam</span>
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .form-label {
    margin-bottom: 0.5rem;
    color: #495057;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .card {
    transition: all 0.3s ease;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('examForm');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const examDate = document.getElementById('examDate');

    // Date validation
    startDate.addEventListener('change', function() {
      endDate.min = this.value;
      if (endDate.value && endDate.value < this.value) {
        endDate.value = this.value;
      }
    });

    endDate.addEventListener('change', function() {
      startDate.max = this.value;
      if (startDate.value && startDate.value > this.value) {
        alert('End date cannot be before start date');
        this.value = '';
      }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      const submitBtnText = document.getElementById('submitBtnText');
      const loader = document.getElementById('loader');

      // Disable button and show loader
      submitBtnText.classList.add('d-none');
      loader.classList.remove('d-none');
      submitBtn.disabled = true;
    });

    // Auto-fill exam date with start date if not set
    startDate.addEventListener('change', function() {
      if (!examDate.value) {
        examDate.value = this.value;
      }
    });
  });
</script>

@include('includes.footer')
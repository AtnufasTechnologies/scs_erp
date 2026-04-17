@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Regulation Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.regulations.index') }}">Regulations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Regulation</li>
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
                <i class="fas fa-plus-circle me-2"></i>Create New Regulation
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
              <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Regulation Information</h6>
            </div>
            <div class="card-body p-4">
              <form action="{{ route('coe.regulations.store') }}" method="POST" id="regulationForm">
                @csrf

                <!-- Basic Information -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Basic Information</h6>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="regulationName" class="form-label fw-bold">
                      Regulation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="regulationName" name="regulation_name"
                      placeholder="e.g., CBCS Regulation 2022"
                      value="{{ old('regulation_name') }}" required>
                    <small class="text-muted">Enter a unique regulation name</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="regulationType" class="form-label fw-bold">
                      Regulation Type <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="regulationType" name="regulation_type" required>
                      <option value="" selected disabled>Select regulation type...</option>
                      <option value="Annual" {{ old('regulation_type') == 'Annual' ? 'selected' : '' }}>Annual</option>
                      <option value="Semester" {{ old('regulation_type') == 'Semester' ? 'selected' : '' }}>Semester</option>
                      <option value="Choice Based" {{ old('regulation_type') == 'Choice Based' ? 'selected' : '' }}>Choice Based</option>
                    </select>
                  </div>

                  <div class="col-md-12 mb-3">
                    <label for="programId" class="form-label fw-bold">
                      Program <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="programId" name="program_id" required>
                      <option value="" selected disabled>Select program...</option>
                      @foreach($programs as $program)
                      <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->code }}) - {{ $program->type }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <hr>

                <!-- Academic Period -->
                <div class="row mb-4">
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">Academic Period</h6>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="startYear" class="form-label fw-bold">
                      Start Year <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" id="startYear" name="start_year"
                      min="2000" max="2100" placeholder="e.g., 2022"
                      value="{{ old('start_year', date('Y')) }}" required>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="endYear" class="form-label fw-bold">
                      End Year <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" id="endYear" name="end_year"
                      min="2000" max="2100" placeholder="e.g., 2026"
                      value="{{ old('end_year', date('Y') + 4) }}" required>
                    <small class="text-muted">Usually 4-6 years from start year</small>
                  </div>
                </div>

                <!-- Form Actions -->
                <hr>
                <div class="d-flex justify-content-between align-items-center mt-4">
                  <a href="{{ route('coe.regulations.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-times me-1"></i>Cancel
                  </a>
                  <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fa fa-save me-1"></i>Save Regulation
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
</style>

<script>
  // Year validation
  document.getElementById('startYear')?.addEventListener('change', function() {
    const startYear = parseInt(this.value);
    const endYearInput = document.getElementById('endYear');

    if (endYearInput.value && parseInt(endYearInput.value) < startYear) {
      endYearInput.value = startYear + 4;
    }

    endYearInput.min = startYear;
  });

  // Form submission loader
  document.getElementById('regulationForm')?.addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
  });
</script>

@include('includes.footer')
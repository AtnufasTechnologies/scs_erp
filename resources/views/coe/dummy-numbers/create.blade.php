@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dummy Numbers</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.dummy-numbers.index') }}">Dummy Numbers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign New</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-sort-numeric-down me-2"></i>Assign Dummy Number</h3>
                  <p class="text-white-50 mb-0">Assign exam dummy number to a student for anonymity</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.dummy-numbers.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <!-- Form -->
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <form action="{{ route('coe.dummy-numbers.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                  <label for="exam_id" class="form-label fw-bold">
                    <i class="fas fa-clipboard-list me-2"></i>Select Exam <span class="text-danger">*</span>
                  </label>
                  <select name="exam_id" id="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
                    <option value="">-- Select Exam --</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                      {{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                  </select>
                  @error('exam_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="exam_student_id" class="form-label fw-bold">
                    <i class="fas fa-user me-2"></i>Select Student <span class="text-danger">*</span>
                  </label>
                  <select name="exam_student_id" id="exam_student_id" class="form-select @error('exam_student_id') is-invalid @enderror" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('exam_student_id') == $student->id ? 'selected' : '' }}>
                      {{ $student->enrollment_no ?? 'N/A' }} - {{ $student->student->first_name ?? '' }} {{ $student->student->last_name ?? '' }}
                    </option>
                    @endforeach
                  </select>
                  @error('exam_student_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <small class="text-muted">Only active exam students are listed</small>
                </div>

                <div class="alert alert-info border-0">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>Note:</strong> A unique dummy number will be <strong>auto-generated</strong> when you submit. Dummy numbers ensure student anonymity during exam evaluation.
                </div>

                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('coe.dummy-numbers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                  </a>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Assign Dummy Number
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
  // Initialize Select2 for better dropdowns (if available)
  $(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
      $('#exam_student_id').select2({
        placeholder: '-- Search Student --',
        allowClear: true,
        width: '100%'
      });
    }
  });
</script>

@include('includes.footer')
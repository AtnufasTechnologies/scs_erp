@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Moderation</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.moderation-duties.index') }}">Moderation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign Duty</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-user-plus me-2"></i>Assign Moderation Duty</h3>
                  <p class="text-muted mb-0">Assign faculty as moderators for exam subjects</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-info">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Assignment Form -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <form action="{{ route('admin.moderation-duties.store') }}" method="POST">
            @csrf

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Exam <span class="text-danger">*</span></label>
                <select name="exam_id" id="examSelect" class="form-select @error('exam_id') is-invalid @enderror" required>
                  <option value="">Select Exam</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                    {{ $exam->name }}
                  </option>
                  @endforeach
                </select>
                @error('exam_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                <select name="subject_id[]" id="subjectSelect" class="select-multiple form-select @error('subject_id') is-invalid @enderror" multiple required>
                  <option value="" disabled>Select Exam first</option>
                </select>
                @error('subject_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Faculty (Moderator) <span class="text-danger">*</span></label>
                <select name="faculty_id[]" multiple class="select-multiple form-select @error('faculty_id') is-invalid @enderror" required>
                  @foreach($faculties as $faculty)
                  <option value="{{ $faculty->id }}" {{ in_array($faculty->id, old('faculty_id', [])) ? 'selected' : '' }}>
                    {{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                  </option>
                  @endforeach
                </select>
                @error('faculty_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Moderation Type <span class="text-danger">*</span></label>
                <select name="moderation_type" class="form-select @error('moderation_type') is-invalid @enderror" required>
                  <option value="">Select Type</option>
                  <option value="internal" {{ old('moderation_type') == 'internal' ? 'selected' : '' }}>Internal</option>
                  <option value="external" {{ old('moderation_type') == 'external' ? 'selected' : '' }}>External</option>
                </select>
                @error('moderation_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-2"></i>Assign Duty
              </button>
              <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-outline-danger ms-2">
                <i class="fas fa-times me-2"></i>Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<input type="hidden" id="jsSubjectsByExamUrl" value="{{ route('admin.moderation-duties.subjects-by-exam', '__EXAM_ID__') }}">

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var examSelect = document.getElementById('examSelect');
    var subjectSelect = document.getElementById('subjectSelect');
    var baseUrl = document.getElementById('jsSubjectsByExamUrl').value;

    examSelect.addEventListener('change', function() {
      var examId = this.value;
      subjectSelect.innerHTML = '<option value="" disabled>Loading...</option>';

      if (!examId) {
        subjectSelect.innerHTML = '<option value="" disabled>Select Exam first</option>';
        return;
      }

      fetch(baseUrl.replace('__EXAM_ID__', examId))
        .then(function(response) {
          return response.json();
        })
        .then(function(subjects) {
          subjectSelect.innerHTML = '';
          if (subjects.length === 0) {
            subjectSelect.innerHTML = '<option value="" disabled>No subjects found for this exam</option>';
            return;
          }
          subjects.forEach(function(subject) {
            var option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.subject_code + ' - ' + subject.name;
            subjectSelect.appendChild(option);
          });
        })
        .catch(function() {
          subjectSelect.innerHTML = '<option value="" disabled>Error loading subjects</option>';
        });
    });
  });
</script>
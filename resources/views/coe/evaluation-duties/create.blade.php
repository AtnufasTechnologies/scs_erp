@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Evaluation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.evaluation-duties.index') }}">Evaluation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Assign Copies</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-user-plus me-2"></i>Assign Evaluation Copies</h3>
                  <p class="text-dark-50 mb-0">Assign answer script copies to faculty for evaluation</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.evaluation-duties.index') }}" class="btn btn-info">
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
          <form action="{{ route('admin.evaluation-duties.store') }}" method="POST">
            @csrf

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Exam <span class="text-danger">*</span></label>
                <select name="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
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
                <select name="subject_id[]" class="select-multiple form-select @error('subject_id') is-invalid @enderror" id="subjectSelect" multiple required>
                  <option value="" disabled>Select Exam first</option>
                </select>
                @error('subject_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
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
                <label class="form-label fw-semibold">Copies Assigned <span class="text-danger">*</span></label>
                <input type="number" name="copies_assigned" class="form-control @error('copies_assigned') is-invalid @enderror"
                  value="{{ old('copies_assigned') }}" min="1" placeholder="Number of copies per faculty" required>
                @error('copies_assigned')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-2"></i>Assign Copies
              </button>
              <a href="{{ route('admin.evaluation-duties.index') }}" class="btn btn-outline-danger ms-2">
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

<input type="hidden" id="jsSubjectsByExamUrl" value="{{ route('admin.evaluation-duties.subjects-by-exam', '__EXAM_ID__') }}">

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var examSelect = document.querySelector('select[name="exam_id"]');
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
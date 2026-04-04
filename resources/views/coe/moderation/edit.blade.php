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
            <li class="breadcrumb-item active" aria-current="page">Edit Duty</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-edit me-2"></i>Edit Moderation Duty</h3>
                  <p class="text-white-50 mb-0">Update the moderation duty assignment</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-light">
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

      <!-- Edit Form -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <form action="{{ route('admin.moderation-duties.update', $duty->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Exam <span class="text-danger">*</span></label>
                <select name="exam_id" id="examSelect" class="form-select @error('exam_id') is-invalid @enderror" required>
                  <option value="">Select Exam</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}" {{ old('exam_id', $duty->exam_id) == $exam->id ? 'selected' : '' }}>
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
                <select name="subject_id" id="subjectSelect" class="form-select @error('subject_id') is-invalid @enderror" required>
                  <option value="">Select Subject</option>
                  @foreach($subjects as $subject)
                  <option value="{{ $subject->id }}" {{ old('subject_id', $duty->subject_id) == $subject->id ? 'selected' : '' }}>
                    {{ $subject->subject_code }} - {{ $subject->name }}
                  </option>
                  @endforeach
                </select>
                @error('subject_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Faculty (Moderator) <span class="text-danger">*</span></label>
                <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
                  <option value="">Select Faculty</option>
                  @foreach($faculties as $faculty)
                  <option value="{{ $faculty->id }}" {{ old('faculty_id', $duty->faculty_id) == $faculty->id ? 'selected' : '' }}>
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
                  <option value="internal" {{ old('moderation_type', $duty->moderation_type) == 'internal' ? 'selected' : '' }}>Internal</option>
                  <option value="external" {{ old('moderation_type', $duty->moderation_type) == 'external' ? 'selected' : '' }}>External</option>
                </select>
                @error('moderation_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Update Duty
              </button>
              <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-outline-secondary btn-lg ms-2">
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
<input type="hidden" id="jsCurrentSubjectId" value="{{ $duty->subject_id }}">

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var examSelect = document.getElementById('examSelect');
    var subjectSelect = document.getElementById('subjectSelect');
    var baseUrl = document.getElementById('jsSubjectsByExamUrl').value;
    var currentSubjectId = document.getElementById('jsCurrentSubjectId').value;

    examSelect.addEventListener('change', function() {
      var examId = this.value;
      subjectSelect.innerHTML = '<option value="">Loading...</option>';

      if (!examId) {
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        return;
      }

      fetch(baseUrl.replace('__EXAM_ID__', examId))
        .then(function(response) {
          return response.json();
        })
        .then(function(subjects) {
          subjectSelect.innerHTML = '<option value="">Select Subject</option>';
          subjects.forEach(function(subject) {
            var option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.subject_code + ' - ' + subject.name;
            if (String(subject.id) === String(currentSubjectId)) {
              option.selected = true;
            }
            subjectSelect.appendChild(option);
          });
        })
        .catch(function() {
          subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
        });
    });
  });
</script>
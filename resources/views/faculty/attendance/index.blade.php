@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Attendance</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <h2 class="fw-bold">Student Attendance</h2>
          <p class="text-muted">Select a subject to take or view attendance</p>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if($syllabusAssignments->isEmpty())
      <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>No subjects assigned to you yet.
      </div>
      @else
      <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <!-- Subject Selection Dropdown -->
              <div class="mb-4">
                <label for="subjectSelect" class="form-label fw-bold">
                  <i class="fa fa-book me-2"></i>Select Subject
                </label>
                <select class="form-select form-select-lg" id="subjectSelect">
                  <option value="" selected disabled>Choose a subject...</option>
                  @foreach($syllabusAssignments as $item)
                  <option value="{{ $item->id }}"
                    data-subject-title="{{ $item->syllabus->subject->title ?? 'N/A' }}"
                    data-course-title="{{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}"
                    data-course-code="{{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }}"
                    data-semester="{{ $item->syllabus->semestermaster->title ?? 'N/A' }}"
                    data-batch="{{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Subject Details Card (Hidden by default) -->
              <div id="subjectDetails" class="d-none">
                <div class="alert alert-light border">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <small class="text-muted d-block">Subject</small>
                      <strong id="detailSubjectTitle">-</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                      <small class="text-muted d-block">Course Code</small>
                      <strong id="detailCourseCode">-</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                      <small class="text-muted d-block">Course Title</small>
                      <strong id="detailCourseTitle">-</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                      <small class="text-muted d-block">Semester</small>
                      <strong id="detailSemester">-</strong>
                    </div>
                    <div class="col-md-3 mb-3">
                      <small class="text-muted d-block">Batch</small>
                      <strong id="detailBatch">-</strong>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 mt-4">
                  <button type="button" class="btn btn-primary btn-lg flex-fill" id="btnTakeAttendance">
                    <i class="fa fa-plus-circle me-2"></i>Take Attendance
                  </button>
                  <button type="button" class="btn btn-outline-primary btn-lg flex-fill" id="btnViewRecords">
                    <i class="fa fa-eye me-2"></i>View Records
                  </button>
                </div>
              </div>

              <!-- Help Text (Visible by default) -->
              <div id="helpText" class="text-center text-muted py-5">
                <i class="bi bi-arrow-up-circle display-4 text-muted mb-3 d-block"></i>
                <p>Please select a subject from the dropdown above to continue</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('subjectSelect');
    const subjectDetails = document.getElementById('subjectDetails');
    const helpText = document.getElementById('helpText');
    const btnTakeAttendance = document.getElementById('btnTakeAttendance');
    const btnViewRecords = document.getElementById('btnViewRecords');

    // Handle subject selection
    subjectSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];

      if (this.value) {
        // Get data from selected option
        const subjectTitle = selectedOption.dataset.subjectTitle;
        const courseTitle = selectedOption.dataset.courseTitle;
        const courseCode = selectedOption.dataset.courseCode;
        const semester = selectedOption.dataset.semester;
        const batch = selectedOption.dataset.batch;

        // Update details
        document.getElementById('detailSubjectTitle').textContent = subjectTitle;
        document.getElementById('detailCourseCode').textContent = courseCode;
        document.getElementById('detailCourseTitle').textContent = courseTitle;
        document.getElementById('detailSemester').textContent = semester;
        document.getElementById('detailBatch').textContent = batch;

        // Show details, hide help text
        subjectDetails.classList.remove('d-none');
        helpText.classList.add('d-none');

        // Update button URLs
        const syllabusId = this.value;
        btnTakeAttendance.onclick = function() {
          window.location.href = `{{ route('faculty.attendance.take', '') }}/${syllabusId}`;
        };
        btnViewRecords.onclick = function() {
          window.location.href = `{{ route('faculty.attendance.view', '') }}/${syllabusId}`;
        };
      } else {
        // Hide details, show help text
        subjectDetails.classList.add('d-none');
        helpText.classList.remove('d-none');
      }
    });
  });
</script>

<style>
  .form-select-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
  }

  #subjectSelect {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
  }

  #subjectSelect option {
    padding: 10px;
    font-size: 0.95rem;
  }

  #subjectSelect:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
  }

  .alert-light {
    background-color: #f8f9fa;
  }

  .btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
  }

  .card {
    border: none;
  }
</style>

@include('includes.footer')
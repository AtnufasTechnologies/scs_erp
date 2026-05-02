<?php

use App\Models\HourMaster;

$hourMaster = HourMaster::all();
?>
@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Dashboard</div>
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
          <h2 class="fw-bold">Remedial Class Attendance</h2>
          <p class="text-muted">Select a subject to take or view attendance for remedial classes</p>
        </div>
        <a href="{{ route('faculty.attendance.view.remedial-class') }}"><button class="btn btn-primary">View Attendance List</button></a>
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
                    data-semester-id="{{ $item->syllabus->semester_id ?? '' }}"
                    data-batch-id="{{ $item->syllabus->batchmaster->id ?? '' }}"
                    data-syllabus-id="{{ $item->syllabus->id ?? '' }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                  </option>
                  @endforeach
                </select>
              </div>


              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-4">
                    <label for="hourSelect" class="form-label fw-bold">Hour</label>
                    <select id="hourSelect" class="form-select">
                      <option value="" selected disabled>Choose hour...</option>
                      @foreach(App\Models\HourMaster::orderBy('id')->get() as $hour)
                      <option value="{{ $hour->id }}">{{ $hour->title ?? $hour->hour_name ?? 'Hour '.$hour->id }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <label for="attendanceDate" class="form-label fw-bold">Date</label>
                  <input type="date" id="attendanceDate" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>
              </div>

              <div class="mb-4 text-center">
                <button type="button" class="btn btn-success btn-lg" id="btnLoadStudents" disabled>
                  <i class="fa fa-users me-2"></i>Load Students
                </button>
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
    const hourSelect = document.getElementById('hourSelect');
    const attendanceDate = document.getElementById('attendanceDate');
    const btnLoadStudents = document.getElementById('btnLoadStudents');

    function checkEnableButton() {
      if (subjectSelect.value && hourSelect.value && attendanceDate.value) {
        btnLoadStudents.disabled = false;
      } else {
        btnLoadStudents.disabled = true;
      }
    }

    subjectSelect.addEventListener('change', checkEnableButton);
    hourSelect.addEventListener('change', checkEnableButton);
    attendanceDate.addEventListener('change', checkEnableButton);

    btnLoadStudents.addEventListener('click', function() {
      const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
      const recId = subjectSelect.value;
      const hourId = hourSelect.value;
      const date = attendanceDate.value;
      const semesterId = selectedOption.dataset.semesterId;
      const batchId = selectedOption.dataset.batchId;
      const syllabusId = selectedOption.dataset.syllabusId;
      // Redirect or fetch students as needed
      // Example: redirect to attendance creation page with params
      const url = `{{ url('erp/faculty/attendance/create/remedial-class') }}?rec_id=${recId}&syllabus_id=${syllabusId}&hour_id=${hourId}&attendance_date=${date}&semester_id=${semesterId}&batch_id=${batchId}`;
      window.location.href = url;
    });
  });
  document.addEventListener('DOMContentLoaded', function() {
    // Prevent Sunday selection in attendance date
    const dateInput = document.getElementById('attendanceDate');

    dateInput.addEventListener('input', function() {
      const selectedDate = new Date(this.value);
      // getDay() returns 0 for Sunday
      if (selectedDate.getDay() === 0) {
        alert('⚠️ Sunday is a holiday. Please select a weekday for attendance.');
        this.value = ''; // Clear the invalid date
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
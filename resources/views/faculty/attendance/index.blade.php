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
        <a href="{{ route('faculty.attendance.view') }}"><button class="btn btn-primary">View Attendance List</button></a>
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
        <div class="col-lg-6 col-md-10 mx-auto">
          <div class="card shadow-sm">
            <div class="card-header fw-bold"> <i class="fal fa-qrcode"></i> QR BASED (AUTO SYSTEM )</div>
            <div class="card-body p-4 js-attendance-config-card">
              <!-- Subject Selection Dropdown -->
              <div class="mb-4">
                <label for="subjectSelectQr" class="form-label fw-bold">
                  <i class="fa fa-book me-2"></i>Select Subject
                </label>
                <select class="form-select js-subject-select" id="subjectSelectQr">
                  <option value="" selected disabled>Choose a subject...</option>
                  @foreach($syllabusAssignments as $item)
                  <option value="{{ $item->id }}"
                    data-semester-id="{{ $item->syllabus->semester_id ?? '' }}"
                    data-batch-id="{{ $item->syllabus->batch_id ?? '' }}"
                    data-batch-name="{{ $item->syllabus->batchmaster->batch_name ?? '' }}"
                    data-syllabus-id="{{ $item->syllabus->id ?? '' }}"
                    data-shift="{{ strtolower($item->shift ?? 'common') }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                  </option>
                  @endforeach
                </select>
              </div>


              <div class="row">
                <div class="col-lg-3">
                  <div class="mb-4">
                    <label for="hourSelectQr" class="form-label fw-bold">Hour</label>
                    <select id="hourSelectQr" class="form-select js-hour-select">
                      <option value="" selected disabled>Select subject first...</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-3">
                  <label for="attendanceTypeQr" class="form-label fw-bold">Class Type</label>
                  <select id="attendanceTypeQr" class="form-select js-attendance-type" name="attendance_type">
                    <option value="regular" selected>Regular</option>
                    <option value="remedial">Remedial</option>
                  </select>
                </div>
                <div class="col-lg-6">
                  <label for="attendanceDateQr" class="form-label fw-bold">Date</label>
                  <input type="date" id="attendanceDateQr" class="form-control js-attendance-date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>
              </div>

              <div class="mb-4 text-center">
                <button type="button" class="btn btn-success btn-lg mt-3 js-load-students" id="btnLoadStudentsQr" disabled>
                  Generate QR <i class="fal fa-qrcode"></i>
                </button>
              </div>

            </div>
          </div>
        </div>

        <div class="col-lg-6 col-md-10 mx-auto">
          <div class="card shadow-sm">
            <div class="card-header fw-bold"><i class="far fa-clipboard-list-check"></i> MANUAL RECORDER</div>
            <div class="card-body p-4 js-attendance-config-card">
              <!-- Subject Selection Dropdown -->

              <div class="mb-4">
                <label for="subjectSelectManual" class="form-label fw-bold">
                  <i class="fa fa-book me-2"></i>Select Subject
                </label>
                <select class="form-select js-subject-select" id="subjectSelectManual">
                  <option value="" selected disabled>Choose a subject...</option>
                  @foreach($syllabusAssignments as $item)
                  <option value="{{ $item->id }}"
                    data-semester-id="{{ $item->syllabus->semester_id ?? '' }}"
                    data-batch-id="{{ $item->syllabus->batch_id ?? '' }}"
                    data-batch-name="{{ $item->syllabus->batchmaster->batch_name ?? '' }}"
                    data-syllabus-id="{{ $item->syllabus->id ?? '' }}"
                    data-shift="{{ strtolower($item->shift ?? 'common') }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                  </option>
                  @endforeach
                </select>
              </div>


              <div class="row">
                <div class="col-lg-3">
                  <div class="mb-4">
                    <label for="hourSelectManual" class="form-label fw-bold">Hour</label>
                    <select id="hourSelectManual" class="form-select js-hour-select">
                      <option value="" selected disabled>Select subject first...</option>
                    </select>
                  </div>
                </div>
                <div class="col-lg-3">
                  <label for="attendanceTypeManual" class="form-label fw-bold">Class Type</label>
                  <select id="attendanceTypeManual" class="form-select js-attendance-type" name="attendance_type">
                    <option value="regular" selected>Regular</option>
                    <option value="remedial">Remedial</option>
                  </select>
                </div>
                <div class="col-lg-6">
                  <label for="attendanceDateManual" class="form-label fw-bold">Date</label>
                  <input type="date" id="attendanceDateManual" class="form-control js-attendance-date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>
              </div>

              <div class="mb-4 text-center">
                <button type="button" class="btn btn-success btn-lg mt-3 js-load-students" id="btnLoadStudentsManual" disabled>
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
    const hoursEndpoint = `{{ route('faculty.attendance.hours') }}`;

    function wireAttendanceCard(card) {
      const subjectSelect = card.querySelector('.js-subject-select');
      const hourSelect = card.querySelector('.js-hour-select');
      const attendanceDate = card.querySelector('.js-attendance-date');
      const attendanceTypeSelect = card.querySelector('.js-attendance-type');
      const btnLoadStudents = card.querySelector('.js-load-students');

      if (!subjectSelect || !hourSelect || !attendanceDate || !attendanceTypeSelect || !btnLoadStudents) {
        return;
      }

      function checkEnableButton() {
        btnLoadStudents.disabled = !(subjectSelect.value && hourSelect.value && attendanceDate.value);
      }

      async function loadHoursForSelectedSubject() {
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        const shift = selectedOption?.dataset?.shift || '';
        const recId = subjectSelect.value || '';

        hourSelect.innerHTML = '<option value="" selected disabled>Loading hours...</option>';
        hourSelect.disabled = true;
        checkEnableButton();

        if (!shift) {
          hourSelect.innerHTML = '<option value="" selected disabled>No shift mapped for subject</option>';
          return;
        }

        try {
          const response = await fetch(`${hoursEndpoint}?rec_id=${encodeURIComponent(recId)}&shift=${encodeURIComponent(shift)}`, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to fetch hours.');
          }

          const hours = Array.isArray(result.data) ? result.data : [];
          if (hours.length === 0) {
            hourSelect.innerHTML = '<option value="" selected disabled>No teaching hours for selected shift</option>';
            return;
          }

          hourSelect.innerHTML = '<option value="" selected disabled>Choose hour...</option>';
          hours.forEach((hour) => {
            const option = document.createElement('option');
            option.value = hour.id;
            option.textContent = hour.label;
            hourSelect.appendChild(option);
          });

          hourSelect.disabled = false;
        } catch (error) {
          console.error('Failed to load hours by shift', error);
          hourSelect.innerHTML = '<option value="" selected disabled>Failed to load hours</option>';
        }
      }

      subjectSelect.addEventListener('change', function() {
        loadHoursForSelectedSubject();
        checkEnableButton();
      });

      hourSelect.addEventListener('change', checkEnableButton);
      attendanceDate.addEventListener('change', checkEnableButton);

      attendanceDate.addEventListener('input', function() {
        const selectedDate = new Date(this.value);
        if (selectedDate.getDay() === 0) {
          alert('⚠️ Sunday is a holiday. Please select a weekday for attendance.');
          this.value = '';
          checkEnableButton();
        }
      });

      btnLoadStudents.addEventListener('click', function() {
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        const recId = subjectSelect.value;
        const hourId = hourSelect.value;
        const date = attendanceDate.value;
        const semesterId = selectedOption.dataset.semesterId;
        const batchId = selectedOption.dataset.batchId;
        const syllabusId = selectedOption.dataset.syllabusId;
        const attendanceType = attendanceTypeSelect.value || 'regular';
        const url = `{{ url('erp/faculty/attendance/create') }}?rec_id=${recId}&syllabus_id=${syllabusId}&hour_id=${hourId}&attendance_date=${date}&semester_id=${semesterId}&batch_id=${batchId}&attendance_type=${encodeURIComponent(attendanceType)}`;
        window.location.href = url;
      });

      checkEnableButton();
    }

    document.querySelectorAll('.js-attendance-config-card').forEach((card) => {
      wireAttendanceCard(card);
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
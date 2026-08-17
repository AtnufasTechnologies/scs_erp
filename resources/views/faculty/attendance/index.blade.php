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
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('faculty.attendance.view') }}"><button class="btn btn-primary">View Attendance List</button></a>
          <a href="{{ route('faculty.attendance.qr.records') }}"><button class="btn btn-dark"><i class="fa fa-qrcode me-1"></i>QR Records</button></a>
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

      <style>
        .attendance-card {
          border: 1px solid #dbe6f2;
          border-radius: 14px;
          box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }

        .attendance-card .card-header {
          background: linear-gradient(120deg, #f7fbff 0%, #edf5ff 100%);
          border-bottom: 1px solid #dbe6f2;
          letter-spacing: 0.02em;
        }

        .subject-meta {
          border: 1px dashed #c5d6e9;
          border-radius: 10px;
          background: #f8fbff;
          padding: 10px 12px;
        }

        .subject-meta .k {
          color: #64748b;
          font-size: 0.78rem;
          margin-right: 8px;
        }

        .subject-meta .v {
          color: #0f172a;
          font-weight: 600;
        }
      </style>

      @if($syllabusAssignments->isEmpty())
      <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>No subjects assigned to you yet.
      </div>
      @else
      <div class="row">
        <div class="col-lg-6 col-md-10 mx-auto">
          <div class="card attendance-card">
            <div class="card-header fw-bold"> <i class="fal fa-qrcode"></i> QR BASED (AUTO SYSTEM )</div>
            <div class="card-body p-4 js-attendance-config-card">
              <!-- Subject Selection Dropdown -->
              <div class="mb-4">
                <label for="subjectSelectQr" class="form-label fw-bold">
                  <i class="fa fa-book me-2"></i>Select Course
                </label>
                <select class="form-select js-subject-select" id="subjectSelectQr">
                  <option value="" selected disabled>Choose a Course...</option>
                  @foreach($syllabusAssignments as $item)
                  @php
                  $deliveryType = trim((string) ($item->teachingAssignment->delivery_type ?? $item->teachingAllocation->delivery_type ?? 'Regular'));
                  @endphp
                  <option value="{{ $item->id }}"
                    data-semester-id="{{ $item->syllabus->semester_id ?? '' }}"
                    data-batch-id="{{ $item->syllabus->batch_id ?? '' }}"
                    data-batch-name="{{ $item->syllabus->batchmaster->batch_name ?? '' }}"
                    data-syllabus-id="{{ $item->syllabus->id ?? '' }}"
                    data-course-id="{{ $item->syllabus->courseLink->courseMaster->id ?? '' }}"
                    data-shift="{{ strtolower($item->shift ?? 'common') }}"
                    data-delivery-type="{{ $deliveryType }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                    | Delivery: {{ $deliveryType }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="subject-meta mb-3 js-subject-meta">
                <span class="k">Delivery Type</span>
                <span class="v">Select a subject to view</span>
              </div>
              <div class="subject-meta mb-3 js-student-count-meta">
                <span class="k">Students</span>
                <span class="v">Select a subject to load count</span>
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
                <div class="col-lg-4">
                  <label for="attendanceDateQr" class="form-label fw-bold">Date</label>
                  <input type="date" id="attendanceDateQr" class="form-control js-attendance-date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-lg-2">
                  <label for="qrExpiryMinutes" class="form-label fw-bold"> Expiry </label>
                  <div class="input-group">
                    <input type="number" id="qrExpiryMinutes" class="form-control js-expiry-minutes" min="1" max="60" value="5">

                  </div>
                </div>
              </div>
              <div class="mb-4 text-center">
                <button type="button" class="btn btn-danger btn-lg mt-3" disabled>Coming Soon...</button>
              </div>
              <!-- <div class="mb-4 text-center">
                <button type="button" class="btn btn-success btn-lg mt-3 js-load-students" id="btnLoadStudentsQr" disabled>
                  Generate QR <i class="fal fa-qrcode"></i>
                </button>
              </div> -->

            </div>
          </div>
        </div>

        <div class="col-lg-6 col-md-10 mx-auto">
          <div class="card attendance-card">
            <div class="card-header fw-bold"><i class="far fa-clipboard-list-check"></i> MANUAL RECORDER</div>
            <div class="card-body p-4 js-attendance-config-card">
              <!-- Subject Selection Dropdown -->

              <div class="mb-4">
                <label for="subjectSelectManual" class="form-label fw-bold">
                  <i class="fa fa-book me-2"></i>Select Course
                </label>
                <select class="form-select js-subject-select" id="subjectSelectManual">
                  <option value="" selected disabled>Choose a Course...</option>
                  @foreach($syllabusAssignments as $item)
                  @php
                  $deliveryType = trim((string) ($item->teachingAssignment->delivery_type ?? $item->teachingAllocation->delivery_type ?? 'Regular'));
                  @endphp
                  <option value="{{ $item->id }}"
                    data-semester-id="{{ $item->syllabus->semester_id ?? '' }}"
                    data-batch-id="{{ $item->syllabus->batch_id ?? '' }}"
                    data-batch-name="{{ $item->syllabus->batchmaster->batch_name ?? '' }}"
                    data-syllabus-id="{{ $item->syllabus->id ?? '' }}"
                    data-course-id="{{ $item->syllabus->courseLink->courseMaster->id ?? '' }}"
                    data-shift="{{ strtolower($item->shift ?? 'common') }}"
                    data-delivery-type="{{ $deliveryType }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                    | Delivery: {{ $deliveryType }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="subject-meta mb-3 js-subject-meta">
                <span class="k">Delivery Type</span>
                <span class="v">Select a subject to view</span>
              </div>
              <div class="subject-meta mb-3 js-student-count-meta">
                <span class="k">Students</span>
                <span class="v">Select a subject to load count</span>
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

    <div class="modal fade" id="studentAttendanceQrModal" tabindex="-1" aria-labelledby="studentAttendanceQrModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="studentAttendanceQrModalLabel"><i class="fa fa-qrcode me-2"></i>Student Attendance QR</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-4 align-items-center">
              <div class="col-md-6 text-center">
                <div id="studentAttendanceQrCanvas" class="d-inline-block p-2 border rounded bg-white"></div>
              </div>
              <div class="col-md-6">
                <p class="mb-2" id="studentAttendanceQrCourse"></p>
                <p class="mb-2" id="studentAttendanceQrBatch"></p>
                <p class="mb-2"><strong>Type:</strong> <span id="studentAttendanceQrType"></span></p>
                <p class="mb-2"><strong>Expires:</strong> <span id="studentAttendanceQrExpiry"></span></p>
                <p class="mb-2"><strong>Countdown:</strong> <span id="studentAttendanceQrCountdown" class="badge bg-dark">--:--</span></p>
                <p class="mb-2" id="studentAttendanceQrFinalizeStatus"></p>
                <div class="alert alert-warning mb-0">
                  Students must scan this QR and log in with their student account. Attendance will be auto-recorded as PRESENT.
                </div>
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label fw-semibold">Scan URL</label>
              <input type="text" class="form-control" id="studentAttendanceQrUrl" readonly>
            </div>
            <div class="mt-3 d-flex justify-content-end">
              <button type="button" class="btn btn-outline-danger" id="btnDeleteActiveQr">
                <i class="fa fa-trash me-1"></i>Delete QR
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hoursEndpoint = `{{ route('faculty.attendance.hours') }}`;
    const studentCountEndpoint = `{{ route('faculty.attendance.student-count') }}`;
    const generateQrEndpoint = `{{ route('faculty.attendance.qr.generate') }}`;
    const finalizeQrEndpoint = `{{ route('faculty.attendance.qr.finalize') }}`;
    const deleteQrEndpoint = `{{ route('faculty.attendance.qr.delete') }}`;
    const csrfToken = `{{ csrf_token() }}`;
    const qrModalElement = document.getElementById('studentAttendanceQrModal');
    const qrModal = qrModalElement ? new bootstrap.Modal(qrModalElement) : null;
    let countdownInterval = null;
    let finalizeInFlight = false;
    let activeQrRecordId = 0;

    function formatDuration(totalSeconds) {
      const safeSeconds = Math.max(0, totalSeconds);
      const mins = Math.floor(safeSeconds / 60).toString().padStart(2, '0');
      const secs = (safeSeconds % 60).toString().padStart(2, '0');
      return `${mins}:${secs}`;
    }

    function showFinalizeStatus(message, isError = false) {
      const target = document.getElementById('studentAttendanceQrFinalizeStatus');
      if (!target) {
        return;
      }

      target.textContent = message || '';
      target.className = isError ? 'mb-2 text-danger fw-semibold' : 'mb-2 text-success fw-semibold';
    }

    async function deleteActiveQr() {
      if (!activeQrRecordId) {
        return;
      }

      const shouldDelete = confirm('Delete this QR record? You can then regenerate for the same slot.');
      if (!shouldDelete) {
        return;
      }

      try {
        const response = await fetch(deleteQrEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            record_id: Number(activeQrRecordId)
          })
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to delete QR record.');
        }

        if (countdownInterval) {
          clearInterval(countdownInterval);
          countdownInterval = null;
        }
        activeQrRecordId = 0;
        qrModal?.hide();
        alert(result.message || 'QR deleted successfully.');
      } catch (error) {
        alert(error.message || 'Unable to delete QR record.');
      }
    }

    async function finalizeExpiredQr(recordId) {
      if (!recordId || finalizeInFlight) {
        return;
      }

      finalizeInFlight = true;
      showFinalizeStatus('QR expired. Finalizing attendance...');

      try {
        const response = await fetch(finalizeQrEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            record_id: Number(recordId)
          })
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to finalize attendance for expired QR.');
        }

        const data = result.data || {};
        const summary = `Finalized. Total: ${data.total_students ?? 0}, Present: ${data.present_students ?? 0}, Absent Marked: ${data.absent_marked ?? 0}`;
        showFinalizeStatus(summary, false);
      } catch (error) {
        showFinalizeStatus(error.message || 'Failed to finalize expired QR.', true);
      } finally {
        finalizeInFlight = false;
      }
    }

    function startCountdown(payload) {
      const countdownNode = document.getElementById('studentAttendanceQrCountdown');
      if (!countdownNode) {
        return;
      }

      if (countdownInterval) {
        clearInterval(countdownInterval);
      }

      const expiryIso = payload.expires_at_iso || '';
      const expiryTs = Date.parse(expiryIso);
      if (!Number.isFinite(expiryTs)) {
        countdownNode.textContent = '--:--';
        return;
      }

      const tick = () => {
        const nowTs = Date.now();
        const diffSeconds = Math.floor((expiryTs - nowTs) / 1000);
        countdownNode.textContent = formatDuration(diffSeconds);

        if (diffSeconds <= 0) {
          clearInterval(countdownInterval);
          countdownInterval = null;
          countdownNode.textContent = '00:00';
          finalizeExpiredQr(activeQrRecordId);
        }
      };

      tick();
      countdownInterval = setInterval(tick, 1000);
    }

    function renderAttendanceQr(payload) {
      const qrContainer = document.getElementById('studentAttendanceQrCanvas');
      const qrUrlInput = document.getElementById('studentAttendanceQrUrl');
      const qrCourse = document.getElementById('studentAttendanceQrCourse');
      const qrBatch = document.getElementById('studentAttendanceQrBatch');
      const qrType = document.getElementById('studentAttendanceQrType');
      const qrExpiry = document.getElementById('studentAttendanceQrExpiry');

      if (!qrContainer || !qrUrlInput || !qrCourse || !qrBatch || !qrType || !qrExpiry) {
        return;
      }

      qrContainer.innerHTML = '';
      new QRCode(qrContainer, {
        text: payload.scan_url,
        width: 260,
        height: 260,
      });

      qrUrlInput.value = payload.scan_url || '';
      qrCourse.innerHTML = `<strong>Course:</strong> ${payload.course_label || 'N/A'}`;
      qrBatch.innerHTML = `<strong>Batch:</strong> ${payload.batch_label || 'N/A'}`;
      qrType.textContent = payload.attendance_type === 'remedial' ? 'Remedial' : 'Regular';
      qrExpiry.textContent = payload.expires_at || 'N/A';
      showFinalizeStatus('');
      activeQrRecordId = Number(payload.record_id || 0);
      startCountdown(payload);
      qrModal?.show();
    }

    function wireAttendanceCard(card) {
      const subjectSelect = card.querySelector('.js-subject-select');
      const subjectMeta = card.querySelector('.js-subject-meta .v');
      const studentCountMeta = card.querySelector('.js-student-count-meta .v');
      const hourSelect = card.querySelector('.js-hour-select');
      const attendanceDate = card.querySelector('.js-attendance-date');
      const attendanceTypeSelect = card.querySelector('.js-attendance-type');
      const expiryMinutesInput = card.querySelector('.js-expiry-minutes');
      const btnLoadStudents = card.querySelector('.js-load-students');

      if (!subjectSelect || !hourSelect || !attendanceDate || !attendanceTypeSelect || !btnLoadStudents) {
        return;
      }

      function checkEnableButton() {
        btnLoadStudents.disabled = !(subjectSelect.value && hourSelect.value && attendanceDate.value);
      }

      function updateDeliveryMeta() {
        if (!subjectMeta) {
          return;
        }
        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        subjectMeta.textContent = selectedOption?.dataset?.deliveryType || 'Select a subject to view';
      }

      async function loadResolvedStudentCount() {
        if (!studentCountMeta) {
          return;
        }

        const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
        const recId = subjectSelect.value || '';
        const syllabusId = selectedOption?.dataset?.syllabusId || '';
        const batchId = selectedOption?.dataset?.batchId || '';
        const semesterId = selectedOption?.dataset?.semesterId || '';

        if (!recId || !syllabusId) {
          studentCountMeta.textContent = 'Select a subject to load count';
          return;
        }

        studentCountMeta.textContent = 'Resolving student count...';

        try {
          const query = new URLSearchParams({
            rec_id: recId,
            syllabus_id: syllabusId,
            batch_id: batchId,
            semester_id: semesterId,
          });

          const response = await fetch(`${studentCountEndpoint}?${query.toString()}`, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to resolve student count.');
          }

          const count = Number(result?.data?.count || 0);
          studentCountMeta.textContent = `${count} Student${count === 1 ? '' : 's'}`;
        } catch (error) {
          studentCountMeta.textContent = 'Count unavailable';
        }
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
        loadResolvedStudentCount();
        updateDeliveryMeta();
        checkEnableButton();
      });

      hourSelect.addEventListener('change', checkEnableButton);
      attendanceDate.addEventListener('change', checkEnableButton);
      updateDeliveryMeta();
      if (studentCountMeta) {
        studentCountMeta.textContent = 'Select a subject to load count';
      }

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
        const expiryMinutes = Number(expiryMinutesInput?.value || 5);

        if (btnLoadStudents.id === 'btnLoadStudentsQr') {
          btnLoadStudents.disabled = true;

          fetch(generateQrEndpoint, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({
                routine_id: recId,
                syllabus_id: syllabusId,
                course_id: Number(selectedOption.dataset.courseId || 0),
                hour_id: Number(hourId),
                semester_id: Number(semesterId),
                batch_id: Number(batchId),
                attendance_date: date,
                attendance_type: attendanceType,
                expiry_minutes: Number.isFinite(expiryMinutes) && expiryMinutes > 0 ? expiryMinutes : 5,
              })
            })
            .then(async (response) => {
              const result = await response.json();
              if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to generate QR code.');
              }
              renderAttendanceQr(result.data || {});
            })
            .catch((error) => {
              alert(error.message || 'Unable to generate QR code.');
            })
            .finally(() => {
              checkEnableButton();
            });

          return;
        }

        const url = `{{ url('erp/faculty/attendance/create') }}?rec_id=${recId}&syllabus_id=${syllabusId}&hour_id=${hourId}&attendance_date=${date}&semester_id=${semesterId}&batch_id=${batchId}&attendance_type=${encodeURIComponent(attendanceType)}`;
        window.location.href = url;
      });

      checkEnableButton();
    }

    document.querySelectorAll('.js-attendance-config-card').forEach((card) => {
      wireAttendanceCard(card);
    });

    document.getElementById('btnDeleteActiveQr')?.addEventListener('click', deleteActiveQr);
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
<?php

use App\Models\HourMaster;

$hourmaster = HourMaster::all();
?>
@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Remedial Classes</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">

            <li class="breadcrumb-item"><a href="{{ route('faculty.remedial.classes') }}"></a></li>
            <li class="breadcrumb-item active" aria-current="page">Take Attendance</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <h2 class="fw-bold">Take Attendance</h2>
          <p class="text-muted mb-1">
            <strong> Code:</strong> {{ $syllabusAssignment->courseLink->courseMaster->course_code ?? 'N/A' }}
            | <strong>{{ $syllabusAssignment->semestermaster->title ?? 'N/A' }} - ({{$syllabusAssignment->batchmaster->batch_name ?? 'N/A' }})</strong>
            | <strong>Shift:</strong> {{ $routineShift ?? 'Common' }}
          </p>
          <p class="text-muted">
            <strong>Course:</strong> {{ $syllabusAssignment->courseLink->courseMaster->course_title ?? 'N/A' }}


          </p>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <form action="{{ route('faculty.remedial.classes.store') }}" method="POST" id="attendanceForm">
            @csrf

            <input type="hidden" name="routine_id" value="{{ $recordId }}">
            <input type="hidden" name="course_id" value="{{ $course_id }}">
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            <input type="hidden" name="batch" value="{{$syllabusAssignment->batchmaster->batch_name ?? 'N/A' }}">
            <div class="row mb-4">
              <div class="col-md-4">
                <label for="attendance_date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date"
                  value="{{ $attendanceDate }}" required max="{{ date('Y-m-d') }}">
              </div>

              <div class="col-md-4">
                <label for="hour_id" class="form-label">Selected Hour</label>
                <select name="hour_id" id="hour_id" class="form-select">
                  @foreach ($hourmaster as $hour)
                  <option value="{{ $hour->id }}" {{ $hour->id == $hourId ? 'selected' : '' }}>{{ $hour->title }}</option>
                  @endforeach
                </select>
              </div>



            </div>

            <div class="alert alert-info d-flex align-items-center">
              <i class="fa fa-info-circle fs-4 me-3"></i>
              <div>
                <strong>Quick Marking:</strong> Only Selected students are marked <strong class="text-success">PRESENT</strong> and
                Considered Eligible for <strong class="text-danger">Remedial Classes</strong>.
              </div>
            </div>

            @if($students->isEmpty())
            <div class="alert alert-warning">
              <i class="fa fa-exclamation-triangle me-2"></i>No students enrolled in this subject.
            </div>
            @else
            <!-- Search Box -->
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-text bg-white">
                    <i class="fa fa-search"></i>
                  </span>
                  <input type="text" class="form-control" id="studentSearch"
                    placeholder="Search by name or registration number..."
                    autocomplete="off">
                  <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                    <i class="fa fa-times"></i> Clear
                  </button>
                </div>
                <small class="text-muted">
                  <span id="searchResultCount"></span>
                </small>

              </div>

              <div class="col-md-6 text-end d-flex align-items-center justify-content-end">
                <span class="mb-3 btn-sm  btn-success mx-2">Present: <strong id="presentCount">{{ $students->count() }}</strong></span>
                <span class="mb-3 btn-sm btn-danger mx-2">Absent: <strong id="absentCount">0</strong></span>
                <span class="mb-3 btn-sm btn-primary mx-2">Total: <strong id="totalCount">{{ $students->count() }}</strong></span>
                <button type="submit" class="btn btn-dark" id="submitBtn">
                  <span id="submitBtnText"><i class="fa fa-save "></i>Save Attendance</span>
                  <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>

              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light sticky-top">
                  <tr>
                    <th width="5%">#</th>
                    <th width="15%">Reg No</th>
                    <th width="40%">Student Name</th>
                    <th width="10%" class="text-center">Present</th>

                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $index => $student)
                  @php
                  $existingStatus = $existingAttendance[$student->id]->status ?? 'present';
                  $existingRemarks = $existingAttendance[$student->id]->remarks ?? '';
                  @endphp
                  <tr class="student-row" data-student-id="{{ $student->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td><span class="badge bg-secondary text-uppercase">{{ $student->roll_no ?? 'N/A' }}</span></td>
                    <td class="student-name">{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td class="text-center">
                      <div class="form-check d-inline-block">
                        <input class="form-check-input status-checkbox" type="checkbox"
                          value="present"
                          id="present_{{ $student->id }}"
                          data-student="{{ $student->id }}"
                          {{ $existingStatus === 'present' ? 'checked' : '' }}>
                        <label class="form-check-label" for="present_{{ $student->id }}"></label>
                      </div>
                    </td>

                    <!-- Hidden input to store the final status -->
                    <input type="hidden" name="attendance[{{ $student->id }}]"
                      id="status_{{ $student->id }}"
                      value="{{ $existingStatus }}">
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>



            <div class="d-flex justify-content-between mt-4">
              <a href="{{ route('faculty.attendance.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
              </a>

            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Prevent Sunday selection in attendance date
    const dateInput = document.getElementById('attendance_date');

    dateInput.addEventListener('input', function() {
      const selectedDate = new Date(this.value);
      // getDay() returns 0 for Sunday
      if (selectedDate.getDay() === 0) {
        alert('⚠️ Sunday is a holiday. Please select a weekday for attendance.');
        this.value = ''; // Clear the invalid date
      }
    });

    // Handle checkbox changes - only one status per student
    document.querySelectorAll('.status-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const studentId = this.dataset.student;
        const row = this.closest('tr');

        if (this.checked) {
          // Checked means present
          document.getElementById('status_' + studentId).value = 'present';
          row.classList.remove('table-danger', 'table-warning');
          row.classList.add('table-success');
        } else {
          // Unchecked means absent
          document.getElementById('status_' + studentId).value = 'absent';
          row.classList.remove('table-success', 'table-warning');
          row.classList.add('table-danger');
        }

        updateSummary();
      });
    });

    // Initialize row highlighting and summary
    function initializeRows() {
      document.querySelectorAll('.student-row').forEach(row => {
        const studentId = row.dataset.studentId;
        const statusInput = document.getElementById('status_' + studentId);
        const checkbox = document.getElementById('present_' + studentId);

        row.classList.remove('table-success', 'table-danger', 'table-warning');
        // By default, all are absent (unchecked)
        if (statusInput) statusInput.value = 'absent';
        if (checkbox) checkbox.checked = false;
        row.classList.add('table-danger');
      });
      updateSummary();
    }

    // Update attendance summary
    function updateSummary() {
      const total = document.querySelectorAll('.student-row').length;
      let present = 0;
      let absent = 0;

      document.querySelectorAll('.status-checkbox').forEach(cb => {
        if (cb.checked) present++;
        else absent++;
      });

      document.getElementById('totalCount').textContent = total;
      document.getElementById('presentCount').textContent = present;
      document.getElementById('absentCount').textContent = absent;
    }

    // Clear all selections
    window.clearAll = function() {
      document.querySelectorAll('.status-checkbox').forEach(cb => cb.checked = false);
      document.querySelectorAll('[id^="status_"]').forEach(input => input.value = 'absent');
      document.querySelectorAll('.student-row').forEach(row => {
        row.classList.remove('table-success', 'table-warning');
        row.classList.add('table-danger');
      });
      updateSummary();
    };

    // Search functionality
    const searchInput = document.getElementById('studentSearch');
    const searchResultCount = document.getElementById('searchResultCount');
    const visibleCount = document.getElementById('visibleCount');

    function performSearch() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.student-row');
      let visibleStudents = 0;

      rows.forEach(row => {
        const regNo = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const studentName = row.querySelector('.student-name').textContent.toLowerCase();

        if (regNo.includes(searchTerm) || studentName.includes(searchTerm)) {
          row.style.display = '';
          visibleStudents++;
        } else {
          row.style.display = 'none';
        }
      });

      // Update visible count
      visibleCount.textContent = visibleStudents;

      // Update search result text
      if (searchTerm) {
        if (visibleStudents === 0) {
          searchResultCount.textContent = '❌ No students found';
          searchResultCount.classList.add('text-danger');
          searchResultCount.classList.remove('text-success');
        } else {
          searchResultCount.textContent = `✓ Found ${visibleStudents} student${visibleStudents !== 1 ? 's' : ''}`;
          searchResultCount.classList.add('text-success');
          searchResultCount.classList.remove('text-danger');
        }
      } else {
        searchResultCount.textContent = '';
      }
    }

    // Clear search
    window.clearSearch = function() {
      searchInput.value = '';
      performSearch();
      searchInput.focus();
    };

    // Listen for search input
    searchInput?.addEventListener('input', performSearch);

    // Listen for Enter key on search
    searchInput?.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
      }
    });

    // Initialize on page load
    initializeRows();

    // Form validation
    document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
      const date = document.getElementById('attendance_date').value;
      const time = document.getElementById('lecture_start_time').value;

      if (!date || !time) {
        e.preventDefault();
        alert('Please select both date and lecture start time.');
        return false;
      }

      // Check if selected date is Sunday
      const selectedDate = new Date(date);
      if (selectedDate.getDay() === 0) {
        e.preventDefault();
        alert('⚠️ Cannot submit attendance for Sunday. Sunday is a holiday. Please select a weekday.');
        document.getElementById('attendance_date').value = '';
        return false;
      }

      // Show loader and disable submit button
      var submitBtn = document.getElementById('submitBtn');
      var submitBtnText = document.getElementById('submitBtnText');
      var loader = document.getElementById('loader');
      if (submitBtn && submitBtnText && loader) {
        submitBtn.disabled = true;
        submitBtnText.classList.add('d-none');
        loader.classList.remove('d-none');
      }
    });
  });
</script>

<style>
  .table-responsive {
    max-height: 60vh;
    overflow-y: auto;
  }

  .sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
  }

  .student-row {
    transition: background-color 0.3s ease;
  }

  .student-row.table-success {
    background-color: #d1e7dd !important;
  }

  .student-row.table-danger {
    background-color: #f8d7da !important;
  }

  .student-row.table-warning {
    background-color: #fff3cd !important;
  }

  .student-row.table-info {
    background-color: #cff4fc !important;
  }

  .form-check-input {
    cursor: pointer;
    width: 1.25rem;
    height: 1.25rem;
  }

  .form-check-input:checked[data-status="absent"] {
    background-color: #dc3545;
    border-color: #dc3545;
  }

  .form-check-input:checked[data-status="late"] {
    background-color: #ffc107;
    border-color: #ffc107;
  }

  .form-check-input:checked[data-status="excused"] {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
  }

  .student-name {
    font-weight: 500;
  }

  .remarks-input {
    border: 1px solid #dee2e6;
  }

  .remarks-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
  }

  /* Search Box Styling */
  #studentSearch {
    border-left: none;
    font-size: 1rem;
    padding: 0.75rem;
  }

  #studentSearch:focus {
    box-shadow: none;
    border-color: #dee2e6;
  }

  .input-group-text {
    border-right: none;
  }

  #studentSearch:focus+.input-group-text {
    border-color: #86b7fe;
  }

  #searchResultCount {
    display: block;
    margin-top: 0.25rem;
    font-weight: 500;
  }

  .table tbody tr {
    transition: all 0.2s ease;
  }

  .table tbody tr[style*="display: none"] {
    opacity: 0;
  }
</style>

@include('includes.footer')
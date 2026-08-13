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
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}">Attendance</a></li>
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

    <div class="container-fluid py-4 attendance-shell">
      <div class="attendance-hero mb-4">
        <div>
          <p class="hero-kicker mb-1">Attendance Operations</p>
          <h2 class="fw-bold mb-0">Manual Attendance Recorder</h2>
        </div>
      </div>

      <div class="">
        <div class="card-body">
          <form action="{{ route('faculty.attendance.store') }}" method="POST" id="attendanceForm">
            @csrf

            <input type="hidden" name="routine_id" value="{{ $recordId }}">
            <input type="hidden" name="course_id" value="{{ $course_id }}">
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            <input type="hidden" name="batch" value="{{$syllabusAssignment->batchmaster->batch_name ?? 'N/A' }}">
            <div class="row g-3 mb-4 control-grid">
              <div class="col-xl-8">
                <div class="card control-card h-100">
                  <div class="card-body">
                    <h6 class="section-title mb-3">Session Controls</h6>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="attendance_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control modern-input" id="attendance_date" name="attendance_date"
                          value="{{ $attendanceDate }}" required max="{{ date('Y-m-d') }}">
                      </div>

                      <div class="col-md-6">
                        <label for="hour_id" class="form-label">Selected Teaching Hour</label>
                        <select name="hour_id" id="hour_id" class="form-select modern-input">
                          @forelse (($availableHours ?? collect()) as $hour)
                          <option value="{{ $hour->id }}" {{ (int) $hour->id === (int) $hourId ? 'selected' : '' }}>{{ $hour->label }}</option>
                          @empty
                          <option value="" selected disabled>No teaching hours for this shift</option>
                          @endforelse
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-xl-4">
                <div class="card control-card session-meta h-100">
                  <div class="card-body">
                    <h6 class="section-title mb-3">Class Snapshot</h6>
                    <p class="meta-line mb-2"><span>Code</span><strong>{{ $syllabusAssignment->courseLink->courseMaster->course_code ?? 'N/A' }}</strong></p>
                    <p class="meta-line mb-2"><span>Term</span><strong>{{ $syllabusAssignment->semestermaster->title ?? 'N/A' }}</strong></p>
                    <p class="meta-line mb-2"><span>Batch</span><strong>{{ $syllabusAssignment->batchmaster->batch_name ?? 'N/A' }}</strong></p>
                    <p class="meta-line mb-0"><span>Shift</span><strong>{{ $routineShift ?? 'Common' }}</strong></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="attendance-guide mb-4">
              <div class="guide-icon">
                <i class="fa fa-info-circle"></i>
              </div>
              <div>
                <p class="mb-1"><strong>Quick Marking:</strong> All students are marked <strong class="text-success">PRESENT</strong> by default. Check only those who are <strong class="text-danger">ABSENT</strong>.</p>
                <p class="mb-0 guide-course"><strong>Course:</strong> {{ $syllabusAssignment->courseLink->courseMaster->course_title ?? 'N/A' }}</p>
              </div>
            </div>

            @if($students->isEmpty())
            <div class="alert alert-warning">
              <i class="fa fa-exclamation-triangle me-2"></i>No students enrolled in this subject.
            </div>
            @else
            <div class="card control-card mb-3 attendance-toolbar">
              <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                  <div class="col-lg-7">
                    <label for="studentSearch" class="form-label mb-2">Find Student</label>
                    <div class="input-group search-group">
                      <span class="input-group-text bg-white">
                        <i class="fa fa-search"></i>
                      </span>
                      <input type="text" class="form-control modern-input" id="studentSearch"
                        placeholder="Search by name or registration number..."
                        autocomplete="off">
                      <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                    <small class="text-muted">
                      <span id="searchResultCount"></span>
                    </small>
                  </div>

                  <div class="col-lg-5">
                    <div class="stats-ribbon">
                      <div class="stat-pill">
                        <span class="label">Visible</span>
                        <strong id="visibleCount">{{ $students->count() }}</strong>
                      </div>
                      <div class="stat-pill">
                        <span class="label">Total</span>
                        <strong id="totalCount">{{ $students->count() }}</strong>
                      </div>
                      <div class="stat-pill success">
                        <span class="label">Present</span>
                        <strong id="presentCount">{{ $students->count() }}</strong>
                      </div>
                      <div class="stat-pill danger">
                        <span class="label">Absent</span>
                        <strong id="absentCount">0</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="student-list-wrap mb-3">
              <div class="student-list" id="studentList">
                @foreach($students as $index => $student)
                @php
                $existingStatus = $existingAttendance[$student->id]->status ?? 'present';
                $existingRemarks = $existingAttendance[$student->id]->remarks ?? '';
                @endphp
                <div class="student-row student-card" data-student-id="{{ $student->id }}" data-roll="{{ strtolower((string) ($student->roll_no ?? '')) }}" data-name="{{ strtolower(trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))) }}">
                  <div class="student-card-head">
                    <div class="student-seq">{{ $index + 1 }}</div>
                    <div class="student-meta">
                      <p class="student-name mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                      <span class="badge bg-secondary text-uppercase">{{ $student->roll_no ?? 'N/A' }}</span>
                    </div>
                    <div class="absent-toggle">
                      <label class="absent-label" for="absent_{{ $student->id }}">Absent</label>
                      <div class="form-check form-switch m-0">
                        <input class="form-check-input status-checkbox" type="checkbox"
                          value="absent"
                          id="absent_{{ $student->id }}"
                          data-student="{{ $student->id }}"
                          {{ $existingStatus === 'absent' ? 'checked' : '' }}>
                      </div>
                    </div>
                  </div>

                  <input type="hidden" name="attendance[{{ $student->id }}]"
                    id="status_{{ $student->id }}"
                    value="{{ $existingStatus }}">
                </div>
                @endforeach
              </div>
            </div>

            <div class="action-footer">
              <button type="submit" class="btn btn-lg btn-success save-attendance-btn" id="submitBtn">
                <span id="submitBtnText"><i class="fa fa-save me-1"></i>Save Attendance</span>
                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
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
        const row = this.closest('.student-row');

        if (this.checked) {
          // Update hidden input with absent status
          document.getElementById('status_' + studentId).value = 'absent';

          // Highlight row as absent (red)
          row.classList.remove('table-success');
          row.classList.add('table-danger');
        } else {
          // If unchecked, mark as present
          document.getElementById('status_' + studentId).value = 'present';
          row.classList.remove('table-danger');
          row.classList.add('table-success');
        }

        updateSummary();
      });
    });

    // Initialize row highlighting and summary
    function initializeRows() {
      document.querySelectorAll('.student-row').forEach(row => {
        const studentId = row.dataset.studentId;
        const status = document.getElementById('status_' + studentId).value;

        row.classList.remove('table-success', 'table-danger');
        if (status === 'present') row.classList.add('table-success');
        else if (status === 'absent') row.classList.add('table-danger');
      });
      updateSummary();
    }

    // Update attendance summary
    function updateSummary() {
      const total = document.querySelectorAll('.student-row').length;
      let absent = 0;

      document.querySelectorAll('[id^="status_"]').forEach(input => {
        const status = input.value;
        if (status === 'absent') absent++;
      });

      const present = total - absent;

      document.getElementById('totalCount').textContent = total;
      document.getElementById('presentCount').textContent = present;
      document.getElementById('absentCount').textContent = absent;
    }

    // Clear all selections
    window.clearAll = function() {
      document.querySelectorAll('.status-checkbox').forEach(cb => cb.checked = false);
      document.querySelectorAll('[id^="status_"]').forEach(input => input.value = 'present');
      document.querySelectorAll('.student-row').forEach(row => {
        row.classList.remove('table-danger');
        row.classList.add('table-success');
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
        const regNo = row.dataset.roll || '';
        const studentName = row.dataset.name || '';

        if (regNo.includes(searchTerm) || studentName.includes(searchTerm)) {
          row.style.display = 'block';
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

      if (!date) {
        e.preventDefault();
        alert('Please select attendance date.');
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
  :root {
    --attendance-surface: #eef3f8;
    --attendance-panel: #ffffff;
    --attendance-border: #d4dde9;
    --attendance-text: #10223b;
    --attendance-muted: #5a6b82;
    --attendance-accent: #0d6efd;
    --attendance-success-bg: #e9f8ef;
    --attendance-danger-bg: #fdeef0;
  }

  .attendance-shell {
    background: radial-gradient(circle at 15% 0%, #ffffff 0%, #f4f8fd 48%, var(--attendance-surface) 100%);
    border-radius: 18px;
  }

  .attendance-hero {
    padding: 0.75rem 0.25rem;
    border-bottom: 1px solid #d9e3ef;
  }

  .hero-kicker {
    text-transform: uppercase;
    letter-spacing: 1.2px;
    font-weight: 700;
    color: #264b74;
    font-size: 0.74rem;
  }

  .attendance-card {
    border: 1px solid var(--attendance-border);
    border-radius: 16px;
    background: var(--attendance-panel);
  }

  .control-card {
    border: 1px solid var(--attendance-border);
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(16, 34, 59, 0.05);
  }

  .section-title {
    color: #1c3c62;
    font-weight: 700;
    letter-spacing: 0.2px;
  }

  .modern-input {
    min-height: 44px;
    border-color: #c9d6e4;
  }

  .modern-input:focus {
    border-color: #8fb6e2;
    box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.12);
  }

  .session-meta {
    background: linear-gradient(140deg, #f4f8ff 0%, #fbfdff 100%);
  }

  .meta-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    font-size: 0.92rem;
    color: var(--attendance-muted);
  }

  .meta-line strong {
    color: #173559;
    font-size: 0.93rem;
  }

  .attendance-guide {
    border: 1px solid #d6e5fb;
    border-radius: 12px;
    background: linear-gradient(135deg, #edf4ff 0%, #f8fbff 100%);
    padding: 0.85rem 0.95rem;
    display: flex;
    gap: 0.8rem;
    color: #133050;
  }

  .guide-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #dcecff;
    color: #185ea8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    margin-top: 2px;
  }

  .guide-course {
    color: #405b78;
    font-size: 0.93rem;
  }

  .search-group .input-group-text {
    border-color: #c9d6e4;
    border-right: 0;
  }

  .search-group .btn {
    border-color: #c9d6e4;
  }

  .stats-ribbon {
    border: 1px solid var(--attendance-border);
    border-radius: 10px;
    padding: 0.5rem;
    background: #fbfdff;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.45rem;
  }

  .stat-pill {
    border: 1px solid #d8e0ea;
    border-radius: 8px;
    padding: 0.42rem 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
  }

  .stat-pill .label {
    font-size: 0.78rem;
    color: var(--attendance-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }

  .stat-pill strong {
    color: #173559;
    font-size: 0.95rem;
  }

  .stat-pill.success {
    border-color: #b8e5c8;
    background: #f4fcf7;
  }

  .stat-pill.danger {
    border-color: #f1c5cc;
    background: #fff6f7;
  }

  .student-list-wrap {
    max-height: 58vh;
    overflow: auto;
    padding-right: 0.15rem;
  }

  .student-list {
    display: grid;
    gap: 0.7rem;
  }

  .student-card {
    border: 1px solid var(--attendance-border);
    border-radius: 12px;
    padding: 0.75rem 0.85rem;
    background: #fff;
    box-shadow: 0 4px 16px rgba(16, 34, 59, 0.04);
  }

  .student-card-head {
    display: grid;
    grid-template-columns: 34px minmax(0, 1fr) auto;
    align-items: center;
    gap: 0.75rem;
  }

  .student-seq {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #eaf1fb;
    color: #2b4a70;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .student-row {
    transition: all 0.25s ease;
  }

  .student-row.table-success {
    background-color: var(--attendance-success-bg) !important;
    border-color: #bfe6ce;
  }

  .student-row.table-danger {
    background-color: var(--attendance-danger-bg) !important;
    border-color: #efc1c8;
  }

  .form-check-input {
    cursor: pointer;
    width: 1.3rem;
    height: 1.3rem;
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
    color: var(--attendance-text);
  }

  .absent-toggle {
    display: flex;
    align-items: center;
    gap: 0.55rem;
  }

  .absent-label {
    font-weight: 600;
    color: #8a1f2d;
    font-size: 0.85rem;
  }

  #searchResultCount {
    display: block;
    margin-top: 0.25rem;
    font-weight: 500;
  }

  .save-attendance-btn {
    min-width: 240px;
    font-weight: 600;
    letter-spacing: 0.2px;
    box-shadow: 0 8px 18px rgba(25, 135, 84, 0.2);
  }

  .action-footer {
    position: sticky;
    bottom: 0;
    z-index: 20;
    padding: 0.8rem;
    margin: 0 -0.25rem -0.25rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, rgba(243, 247, 252, 0.95) 40%, rgba(243, 247, 252, 1) 100%);
    border-top: 1px solid #d3deeb;
    display: flex;
    justify-content: flex-end;
  }

  @media (max-width: 991.98px) {
    .attendance-shell {
      border-radius: 12px;
      padding-left: 0.25rem;
      padding-right: 0.25rem;
    }

    .attendance-card .card-body {
      padding: 1rem;
    }

    .student-list-wrap {
      max-height: 60vh;
    }
  }

  @media (max-width: 767.98px) {
    .attendance-shell {
      width: 100vw;
      max-width: 100vw;
      margin-left: calc(50% - 50vw);
      margin-right: calc(50% - 50vw);
    }

    .container-fluid.attendance-shell {
      padding-top: 1rem !important;
      padding-bottom: 5.5rem !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
      border-radius: 0;
    }

    .attendance-hero,
    #attendanceForm {
      padding-left: 0.85rem;
      padding-right: 0.85rem;
    }

    .attendance-card {
      border: 0;
      border-radius: 0;
      box-shadow: none !important;
      background: transparent;
    }

    .attendance-card .card-body {
      padding: 0 !important;
    }

    .control-grid {
      margin-bottom: 0.75rem !important;
    }

    .control-card .card-body {
      padding: 0.8rem;
    }

    .attendance-guide {
      padding: 0.75rem;
      margin-bottom: 0.75rem !important;
    }

    .stats-ribbon {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .student-list-wrap {
      max-height: none;
      overflow: visible;
      padding-right: 0;
    }

    .student-card {
      border-radius: 10px;
      padding: 0.68rem 0.72rem;
    }

    .student-card-head {
      grid-template-columns: 30px minmax(0, 1fr) auto;
      gap: 0.55rem;
    }

    .student-seq {
      width: 30px;
      height: 30px;
      font-size: 0.78rem;
    }

    .student-name {
      font-size: 0.95rem;
      margin-bottom: 0.25rem !important;
    }

    .absent-label {
      font-size: 0.8rem;
    }

    .action-footer {
      padding: 0.7rem;
      margin: 0;
      border-radius: 10px 10px 0 0;
      justify-content: stretch;
    }

    .save-attendance-btn {
      width: 100%;
      min-width: 0;
      border-radius: 10px;
      min-height: 44px;
    }
  }
</style>

@include('includes.footer')
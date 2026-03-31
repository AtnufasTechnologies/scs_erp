@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Attendance</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mark Attendance</li>
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
      <!-- Exam Details Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <h3 class="text-white fw-bold mb-3">
                <i class="fas fa-user-check me-2"></i>Mark Exam Attendance
              </h3>
              <div class="row text-white">
                <div class="col-md-6 mb-2">
                  <p class="mb-1"><strong>Exam:</strong> {{ $examDetails->exam_name ?? 'N/A' }}</p>
                  <p class="mb-1"><strong>Subject:</strong> {{ $subjectDetails->name ?? 'N/A' }} ({{ $subjectDetails->code ?? 'N/A' }})</p>
                </div>
                <div class="col-md-6 mb-2">
                  <p class="mb-1"><strong>Session:</strong> {{ $sessionDetails->name ?? 'N/A' }}</p>
                  <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($examDate)->format('d M Y') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <form action="{{ route('coe.attendance.store') }}" method="POST" id="attendanceForm">
            @csrf

            <input type="hidden" name="exam_id" value="{{ $examId }}">
            <input type="hidden" name="session_id" value="{{ $sessionId }}">
            <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            <input type="hidden" name="exam_date" value="{{ $examDate }}">

            <div class="alert alert-info d-flex align-items-center mb-4">
              <i class="fa fa-info-circle fs-4 me-3"></i>
              <div>
                <strong>Quick Marking:</strong> All students are marked <strong class="text-success">PRESENT</strong> by default.
                Only check the box for students who are <strong class="text-danger">ABSENT</strong>.
              </div>
            </div>

            @if($students->isEmpty())
            <div class="alert alert-warning">
              <i class="fa fa-exclamation-triangle me-2"></i>No students enrolled for this exam.
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
              <div class="col-md-6 text-end d-flex align-items-center justify-content-end gap-3">
                <span class="badge bg-secondary fs-6 px-3 py-2">
                  Total Students: <strong id="visibleCount">{{ $students->count() }}</strong>
                </span>
                <button type="button" class="btn btn-outline-danger" onclick="clearAll()">
                  <i class="fa fa-eraser me-1"></i>Clear All
                </button>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light sticky-top">
                  <tr>
                    <th width="5%">#</th>
                    <th width="15%">Reg No</th>
                    <th width="35%">Student Name</th>
                    <th width="15%">Course</th>
                    <th width="10%" class="text-center">Absent</th>
                    <th width="20%">Remarks</th>
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
                    <td>{{ $student->programgroup->code ?? 'N/A' }}</td>
                    <td class="text-center">
                      <div class="form-check d-inline-block">
                        <input class="form-check-input status-checkbox" type="checkbox"
                          value="absent"
                          id="absent_{{ $student->id }}"
                          data-student="{{ $student->id }}"
                          {{ $existingStatus === 'absent' ? 'checked' : '' }}>
                        <label class="form-check-label" for="absent_{{ $student->id }}"></label>
                      </div>
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm"
                        name="remarks[{{ $student->id }}]"
                        placeholder="Optional notes..."
                        value="{{ $existingRemarks }}">
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

            <div class="row mt-3">
              <div class="col-md-12">
                <div class="alert alert-light border">
                  <strong>Summary:</strong>
                  <span class="ms-2">Total: <strong id="totalCount">{{ $students->count() }}</strong></span>
                  <span class="ms-3 text-success">Present: <strong id="presentCount">{{ $students->count() }}</strong></span>
                  <span class="ms-3 text-danger">Absent: <strong id="absentCount">0</strong></span>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <a href="{{ route('coe.attendance.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
              </a>
              <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <span id="submitBtnText"><i class="bi bi-save me-1"></i>Save Attendance</span>
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

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .sticky-top {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
  }

  .table-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
  }

  .table-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
  }

  .student-row {
    transition: all 0.2s ease;
  }

  .student-row:hover {
    background-color: rgba(0, 0, 0, 0.02) !important;
  }

  #studentSearch {
    border-radius: 8px 0 0 8px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
  }

  #studentSearch:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
  }

  #searchResultCount {
    display: inline-block;
    margin-top: 5px;
    font-weight: 500;
  }

  .form-check-input {
    cursor: pointer;
    width: 1.5em;
    height: 1.5em;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle checkbox changes
    document.querySelectorAll('.status-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        const studentId = this.dataset.student;
        const row = this.closest('tr');

        if (this.checked) {
          document.getElementById('status_' + studentId).value = 'absent';
          row.classList.remove('table-success');
          row.classList.add('table-danger');
        } else {
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
        const regNo = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const studentName = row.querySelector('.student-name').textContent.toLowerCase();

        if (regNo.includes(searchTerm) || studentName.includes(searchTerm)) {
          row.style.display = '';
          visibleStudents++;
        } else {
          row.style.display = 'none';
        }
      });

      visibleCount.textContent = visibleStudents;

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

    searchInput?.addEventListener('input', performSearch);

    searchInput?.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
      }
    });

    // Initialize on page load
    initializeRows();

    // Form validation
    document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      const submitBtnText = document.getElementById('submitBtnText');
      const loader = document.getElementById('loader');

      submitBtnText.classList.add('d-none');
      loader.classList.remove('d-none');
      submitBtn.disabled = true;
    });
  });
</script>

@include('includes.footer')
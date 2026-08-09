@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">ITCELL Student MDC Mapper</h4>
      <p class="text-muted mb-0">Update student MDC selection and auto-enroll the selected MDC course for the active semester.</p>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="alert alert-info">
    <strong>How it works:</strong> Selecting a new MDC course removes previously enrolled MDC course(s) for the same student and semester, then enrolls the selected one.
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.student-mdc-selection.index') }}" class="row g-3 align-items-end">
        <div class="col-md-2">
          <label class="form-label">Batch</label>
          <select name="batch_id" class="form-select">
            <option value="">All Batches</option>
            @foreach(($batches ?? collect()) as $batch)
            <option value="{{ $batch->id }}" {{ (int)$selectedBatchId === (int)$batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Semester</label>
          <select name="semester_id" class="form-select">
            <option value="">All Semesters</option>
            @foreach(($semesters ?? collect()) as $semester)
            <option value="{{ $semester->id }}" {{ (int)$selectedSemesterId === (int)$semester->id ? 'selected' : '' }}>
              {{ $semester->title }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Campus</label>
          <select name="campus_id" class="form-select">
            <option value="">All Campuses</option>
            @foreach(($campuses ?? collect()) as $campus)
            <option value="{{ $campus->id }}" {{ (int)$selectedCampusId === (int)$campus->id ? 'selected' : '' }}>
              {{ $campus->name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Program</label>
          <select name="program_id" class="form-select">
            <option value="">All Programs</option>
            @foreach(($enrolledPrograms ?? collect()) as $program)
            <option value="{{ $program->id }}" {{ (int)$selectedProgramId === (int)$program->id ? 'selected' : '' }}>
              {{ $program->code }} - {{ $program->name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Search Student</label>
          <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Roll no, register no, application code, or name">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Generate List</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
      <h5 class="mb-0">MDC Offered In Selected Batch + Semester</h5>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-secondary">{{ count($offeredMdcCourses ?? []) }} Course(s)</span>
        <a
          href="{{ route('itcell.student-mdc-selection.export', ['batch_id' => $selectedBatchId, 'semester_id' => $selectedSemesterId, 'campus_id' => $selectedCampusId, 'program_id' => $selectedProgramId]) }}"
          class="btn btn-sm btn-outline-success {{ (empty($selectedBatchId) || empty($selectedSemesterId) || empty($offeredMdcCourses ?? [])) ? 'disabled' : '' }}"
          {{ (empty($selectedBatchId) || empty($selectedSemesterId) || empty($offeredMdcCourses ?? [])) ? 'aria-disabled=true tabindex=-1' : '' }}>
          <i class="fas fa-file-csv"></i> Export CSV
        </a>
      </div>
    </div>
    <div class="card-body">
      @if(empty($selectedBatchId) || empty($selectedSemesterId))
      <div class="alert alert-warning mb-0">Select both batch and semester to view MDC offered list from curriculam_engine.</div>
      @elseif(empty($offeredMdcCourses ?? []))
      <div class="alert alert-warning mb-0">No MDC course found in curriculam_engine for the selected batch and semester.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 60px;">#</th>
              <th>Course</th>
              <th>Offered Department</th>
              <th>Offered Programs</th>
            </tr>
          </thead>
          <tbody>
            @foreach(($offeredMdcCourses ?? []) as $idx => $course)
            <tr>
              <td>{{ $idx + 1 }}</td>
              <td>
                <strong>{{ $course['course_code'] ?: 'N/A' }}</strong>
                <div class="small text-muted">{{ $course['course_title'] ?: 'Untitled course' }}</div>
              </td>
              <td>{{ $course['offered_department'] ?: 'N/A' }}</td>
              <td>
                @if(!empty($course['offered_programs']))
                {{ implode(', ', $course['offered_programs']) }}
                @else
                <span class="text-muted">Not mapped</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-transparent">
      <h5 class="mb-0">Eligible Students</h5>
    </div>
    <div class="card-body">
      @if($students->isEmpty())
      <div class="alert alert-warning mb-0">No students found for the selected filters.</div>
      @else

      <form method="POST" action="{{ route('itcell.student-mdc-selection.store') }}" id="bulkMdcEnrollForm">
        @csrf
        <input type="hidden" name="batch_id" value="{{ (int) $selectedBatchId }}">
        <input type="hidden" name="semester_id" value="{{ (int) $selectedSemesterId }}">

        <div class="row g-2 align-items-end mb-3">
          <div class="col-lg-8">
            <label class="form-label">Select MDC To Enroll For Checked Students</label>
            <select name="mdc_course_id" class="form-select dselect-example" id="bulkMdcCourseSelect" required>
              <option value="">Select MDC Course</option>
              @foreach(($offeredMdcCourses ?? []) as $course)
              <option value="{{ (int) $course['course_id'] }}">
                {{ ($course['course_code'] ?: 'N/A') . ' - ' . ($course['course_title'] ?: 'Untitled course') }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-4 d-grid">
            <button type="submit" class="btn btn-success" id="bulkEnrollBtn" disabled>
              Enroll Selected Students
            </button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Search In Generated Student List</label>
          <input type="text" class="form-control" id="studentTableSearch" value="{{ $search }}" placeholder="Search by roll no, register no, name, program, or campus">
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle" id="eligibleStudentsTable">
            <thead class="table-light">
              <tr>
                <th style="width: 56px;">
                  <input type="checkbox" id="selectAllStudents">
                </th>
                <th>#</th>
                <th>Roll No</th>
                <th>Student</th>
                <th>Batch</th>
                <th>Program</th>
                <th>Campus</th>
                <th>Semester</th>
                <th>Current MDC</th>
              </tr>
            </thead>
            <tbody>
              @foreach($students as $index => $student)
              @php
              $studentId = (int) $student->id;
              $semesterId = (int) ($student->activeSemesterConfig->semester_id ?? 0);
              $currentMdc = $currentMdcByStudent[$studentId] ?? null;
              $isSelectable = ($selectedSemesterId > 0 && $semesterId === (int) $selectedSemesterId);
              @endphp
              <tr>
                <td>
                  <input
                    type="checkbox"
                    class="student-checkbox"
                    name="student_ids[]"
                    value="{{ $studentId }}"
                    {{ $isSelectable ? '' : 'disabled' }}>
                </td>
                <td>{{ $students->firstItem() + $index }}</td>
                <td><strong>{{ $student->roll_no ?: 'N/A' }}</strong></td>
                <td>
                  {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                  @if(!empty($student->register_no))
                  <div class="small text-muted">Reg: {{ $student->register_no }}</div>
                  @endif
                </td>
                <td>{{ $student->batchmaster->batch_name ?? '-' }}</td>
                <td>
                  @if($student->stdprogramenrolled)
                  {{ $student->stdprogramenrolled->code }} - {{ $student->stdprogramenrolled->name }}
                  @else
                  <span class="text-muted">Not set</span>
                  @endif
                </td>
                <td>{{ $student->campusmaster->name ?? '-' }}</td>
                <td>
                  @if($semesterId > 0)
                  <span class="badge bg-primary">Sem {{ $semesterId }}</span>
                  @else
                  <span class="text-muted">Not set</span>
                  @endif
                </td>
                <td>
                  @if(!empty($currentMdc['label']))
                  <span class="small">{{ $currentMdc['label'] }}</span>
                  @else
                  <span class="text-muted">No MDC enrolled</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </form>

      <div class="mt-3">
        {{ $students->links('vendor.pagination.bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  (function() {
    const selectAll = document.getElementById('selectAllStudents');
    const studentCheckboxes = Array.from(document.querySelectorAll('.student-checkbox:not(:disabled)'));
    const bulkMdcCourseSelect = document.getElementById('bulkMdcCourseSelect');
    const bulkEnrollBtn = document.getElementById('bulkEnrollBtn');
    const bulkMdcEnrollForm = document.getElementById('bulkMdcEnrollForm');
    const studentTableSearch = document.getElementById('studentTableSearch');

    function syncBulkButtonState() {
      if (!bulkEnrollBtn) {
        return;
      }
      const hasCheckedStudent = studentCheckboxes.some(function(checkbox) {
        return checkbox.checked;
      });
      const hasMdc = bulkMdcCourseSelect && String(bulkMdcCourseSelect.value || '').trim() !== '';
      bulkEnrollBtn.disabled = !(hasCheckedStudent && hasMdc);
    }

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        studentCheckboxes.forEach(function(checkbox) {
          checkbox.checked = selectAll.checked;
        });
        syncBulkButtonState();
      });
    }

    studentCheckboxes.forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        if (!checkbox.checked && selectAll) {
          selectAll.checked = false;
        }
        syncBulkButtonState();
      });
    });

    if (bulkMdcCourseSelect) {
      bulkMdcCourseSelect.addEventListener('change', syncBulkButtonState);
    }

    if (bulkMdcEnrollForm) {
      bulkMdcEnrollForm.addEventListener('submit', function(event) {
        const hasCheckedStudent = studentCheckboxes.some(function(checkbox) {
          return checkbox.checked;
        });
        const hasMdc = bulkMdcCourseSelect && String(bulkMdcCourseSelect.value || '').trim() !== '';

        if (!hasCheckedStudent || !hasMdc) {
          event.preventDefault();
        }
      });
    }

    if (studentTableSearch) {
      let searchTimer = null;

      const runFullDataSearch = function() {
        const term = String(studentTableSearch.value || '').trim();
        const url = new URL(window.location.href);

        if (term === '') {
          url.searchParams.delete('search');
        } else {
          url.searchParams.set('search', term);
        }

        // Reset pagination when applying a new full-data search.
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
      };

      studentTableSearch.addEventListener('input', function() {
        if (searchTimer) {
          clearTimeout(searchTimer);
        }

        searchTimer = setTimeout(function() {
          runFullDataSearch();
        }, 450);
      });

      studentTableSearch.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') {
          return;
        }

        event.preventDefault();
        if (searchTimer) {
          clearTimeout(searchTimer);
        }
        runFullDataSearch();
      });
    }

    syncBulkButtonState();
  })();
</script>

@include('includes.footer')
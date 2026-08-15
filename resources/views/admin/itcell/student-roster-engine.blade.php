@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">Student Roster Engine Tester</h4>
      <p class="text-muted mb-0">Batch-wise curriculum rows from Curriculum Engine, then resolve roster using selected row context.</p>
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

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.student-roster-engine.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Batch <span class="text-danger">*</span></label>
          <select name="batch_id" class="form-select" required>
            <option value="">Select batch</option>
            @foreach(($batches ?? collect()) as $batch)
            <option value="{{ $batch->id }}" {{ (int) $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Semester (optional)</label>
          <select name="semester_id" class="form-select">
            <option value="">All semesters</option>
            @foreach(($semesters ?? collect()) as $semester)
            <option value="{{ $semester->id }}" {{ (int) $selectedSemesterId === (int) $semester->id ? 'selected' : '' }}>
              {{ $semester->title }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Teaching Group</label>
          <input type="number" min="0" name="teaching_group_id" class="form-control" value="{{ (int) ($teachingGroupId ?? 0) > 0 ? (int) $teachingGroupId : '' }}" placeholder="optional">
        </div>

        <div class="col-md-2">
          <label class="form-label">Teaching Assignment</label>
          <input type="number" min="0" name="teaching_assignment_id" class="form-control" value="{{ (int) ($teachingAssignmentId ?? 0) > 0 ? (int) $teachingAssignmentId : '' }}" placeholder="optional">
        </div>

        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">Load Curriculum Rows</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Curriculum Rows</h5>
      <span class="badge bg-secondary">{{ ($curriculumRows ?? collect())->count() }} Row(s)</span>
    </div>
    <div class="card-body">
      @if((int) $selectedBatchId <= 0)
        <div class="alert alert-warning mb-0">Select a batch to load curriculum rows from Curriculum Engine.</div>
    @elseif(($curriculumRows ?? collect())->isEmpty())
    <div class="alert alert-warning mb-0">No curriculum rows found for selected filters.</div>
    @else
    <form method="GET" action="{{ route('itcell.student-roster-engine.index') }}" id="resolveRosterForm">
      <input type="hidden" name="batch_id" value="{{ (int) $selectedBatchId }}">
      <input type="hidden" name="semester_id" value="{{ (int) $selectedSemesterId }}">
      <input type="hidden" name="teaching_group_id" value="{{ (int) ($teachingGroupId ?? 0) }}">
      <input type="hidden" name="teaching_assignment_id" value="{{ (int) ($teachingAssignmentId ?? 0) }}">

      @php
      $programCodeOptions = collect($curriculumRows ?? [])
      ->pluck('program_code')
      ->map(fn($code) => strtoupper(trim((string) $code)))
      ->filter(fn($code) => $code !== '' && $code !== '-')
      ->unique()
      ->sort()
      ->values();
      $persistedCurriculumSearch = (string) request('curriculum_search', '');
      $persistedProgramCodeFilter = strtoupper(trim((string) request('program_code_filter', '')));
      @endphp

      <div class="row g-3 mb-3">
        <div class="col-md-8">
          <label class="form-label">Search Curriculum Rows</label>
          <input
            type="text"
            name="curriculum_search"
            id="curriculumRowsSearch"
            class="form-control"
            value="{{ $persistedCurriculumSearch }}"
            placeholder="Search by course code/title, batch                                                                                                                                                                                                                                                                                                                                                                                                                                     name, semester, delivery, selection, program name, pathway, or department">
        </div>
        <div class="col-md-4">
          <label class="form-label">Program Code (Quick Filter)</label>
          <select name="program_code_filter" id="curriculumProgramCodeFilter" class="dselect-example">
            <option value="">All Program Codes</option>
            @foreach($programCodeOptions as $programCode)
            <option value="{{ $programCode }}" {{ $persistedProgramCodeFilter === $programCode ? 'selected' : '' }}>{{ $programCode }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-3" id="curriculumRowsTable">
          <thead class="table-light">
            <tr>
              <th style="width: 70px;">Use</th>
              <th>Course</th>
              <th>Batch Name</th>
              <th>Batch</th>
              <th>Semester</th>
              <th>Delivery</th>
              <th>Selection</th>
              <th>Program Code</th>
              <th>Program Name</th>
              <th>Program Type</th>
              <th>Pathway / Track</th>
            </tr>
          </thead>
          <tbody id="curriculumRowsTableBody">
            @foreach(($curriculumRows ?? collect()) as $row)
            @php
            $rowId = (int) ($row->curriculum_row_id ?? 0);
            @endphp
            <tr class="curriculum-row-item" data-program-code="{{ strtoupper(trim((string) ($row->program_code ?? ''))) }}">
              <td class="text-center">
                <input type="radio" name="curriculum_row_id" value="{{ $rowId }}" {{ (int) $selectedCurriculumRowId === $rowId ? 'checked' : '' }} required>
              </td>
              <td>
                <strong>{{ $row->course_code ?? 'N/A' }}</strong>
                <div class="small text-muted">{{ $row->course_title ?? 'Untitled course' }}</div>
              </td>
              <td>{{ $row->batch_name ?? '-' }}</td>
              <td>{{ $row->batch_name ?? '-' }}</td>
              <td>{{ $row->semester ?? '-' }}</td>
              <td><span class="badge bg-info text-dark">{{ strtoupper((string) ($row->delivery_category ?? 'COMMON')) }}</span></td>
              <td><span class="badge bg-warning text-dark">{{ strtoupper((string) ($row->course_type ?? 'AUTO')) }}</span></td>
              <td>{{ $row->program_code ?? '-' }}</td>
              <td>{{ $row->program_name ?? '-' }}</td>

              <td>{{ strtoupper((string) ($row->program_type ?? '-')) }}</td>
              <td>
                <div>{{ $row->pathway_name ?? 'All Pathways' }}</div>
                <div class="small text-muted">{{ $row->degree_track_name ?? 'All Tracks' }}</div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="alert alert-warning d-none" id="curriculumRowsNoMatch">
        No curriculum rows match your search.
      </div>

      <button type="submit" class="btn btn-success" id="resolveRosterButton">
        <span class="resolve-label-default">Resolve Student Roster</span>
        <span class="resolve-label-loading d-none">Please wait, fetching students...</span>
      </button>
    </form>
    @endif
  </div>
</div>

@if(!empty($selectedCurriculumRow))
<div class="card shadow-sm mb-4">
  <div class="card-header bg-transparent">
    <h5 class="mb-0">Resolved Context</h5>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><strong>Course:</strong> {{ $selectedCurriculumRow->course_code ?? 'N/A' }} - {{ $selectedCurriculumRow->course_title ?? 'Untitled course' }}</div>
      <div class="col-md-3"><strong>Batch:</strong> {{ $selectedCurriculumRow->batch_name ?? '-' }}</div>
      <div class="col-md-2"><strong>Semester:</strong> {{ $rosterContext['semester_id'] ?? '-' }}</div>
      <div class="col-md-2"><strong>Delivery:</strong> {{ strtoupper((string) ($rosterContext['delivery_type'] ?? '')) }}</div>
      <div class="col-md-2"><strong>Selection:</strong> {{ strtoupper((string) ($rosterContext['selection_type'] ?? '')) }}</div>
      <div class="col-md-12"><strong>Program Source:</strong> {{ $selectedCurriculumRow->program_name ?? '-' }} ({{ $selectedCurriculumRow->program_code ?? '-' }})</div>
    </div>
  </div>
</div>
@endif

@if(($resolvedProgramStats ?? collect())->isNotEmpty())
<div class="card shadow-sm mb-4">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Program-wise Student Analytics</h5>
    <span class="badge bg-dark">{{ ($resolvedProgramStats ?? collect())->count() }} Program(s)</span>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Program Code</th>
            <th>Program Name</th>
            <th style="width: 180px;">Student Count</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($resolvedProgramStats ?? collect()) as $index => $stat)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td><strong>{{ $stat['program_code'] ?? '-' }}</strong></td>
            <td>{{ $stat['program_name'] ?? '-' }}</td>
            <td>
              <span class="badge bg-primary">{{ (int) ($stat['student_count'] ?? 0) }} Student(s)</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

@if(($dashboardProgramStats ?? collect())->isNotEmpty())
<div class="card shadow-sm mb-4 border-danger-subtle">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <h5 class="mb-0 text-danger">Dropped Student Proof (Dashboard vs Roster Engine)</h5>
    <span class="badge bg-danger">{{ (int) (($droppedRosterStudents ?? collect())->count()) }} Dropped</span>
  </div>
  <div class="card-body">
    <div class="table-responsive mb-3">
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Program Code</th>
            <th>Program Name</th>
            <th>Dept Dashboard Count</th>
            <th>Roster Engine Count</th>
            <th>Dropped</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($dashboardProgramStats ?? collect()) as $index => $stat)
          <tr class="{{ (int) ($stat['dropped_count'] ?? 0) > 0 ? 'table-danger' : '' }}">
            <td>{{ $index + 1 }}</td>
            <td><strong>{{ $stat['program_code'] ?? '-' }}</strong></td>
            <td>{{ $stat['program_name'] ?? '-' }}</td>
            <td>{{ (int) ($stat['dashboard_count'] ?? 0) }}</td>
            <td>{{ (int) ($stat['roster_count'] ?? 0) }}</td>
            <td>
              <span class="badge {{ (int) ($stat['dropped_count'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' }}">
                {{ (int) ($stat['dropped_count'] ?? 0) }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(($droppedRosterStudents ?? collect())->isEmpty())
    <div class="alert alert-success mb-0">No dropped students found for the selected context.</div>
    @else
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Student</th>
            <th>Roll No</th>
            <th>Register No</th>
            <th>Program</th>
            <th>Has Course Enrollment</th>
            <th>Proof Note</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($droppedRosterStudents ?? collect()) as $index => $student)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student['student_name'] ?? '-' }}
              <div class="small text-muted">ID: {{ (int) ($student['student_id'] ?? 0) }}</div>
            </td>
            <td>{{ $student['roll_no'] ?? '-' }}</td>
            <td>{{ $student['register_no'] ?? '-' }}</td>
            <td>{{ $student['program_code'] ?? '-' }} - {{ $student['program_name'] ?? '-' }}</td>
            <td>
              <span class="badge {{ !empty($student['has_course_enrollment']) ? 'bg-warning text-dark' : 'bg-secondary' }}">
                {{ !empty($student['has_course_enrollment']) ? 'Yes' : 'No' }}
              </span>
            </td>
            <td>{{ $student['proof_note'] ?? '-' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endif

<div class="card shadow-sm">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Resolved Students</h5>
    <span class="badge bg-primary">{{ ($resolvedRoster ?? collect())->count() }} Student(s)</span>
  </div>
  <div class="card-body">
    @if(($resolvedRoster ?? collect())->isEmpty())
    <div class="alert alert-warning mb-0">No students resolved yet. Select a curriculum row and click Resolve Student Roster.</div>
    @else
    <div class="table-responsive">
      <table class="table table-bordered table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Student</th>
            <th>Roll No</th>

            <th>Program</th>
            <th>Batch</th>
            <th>Semester</th>
            <th>Pathway</th>
            <th>Track</th>
            <th>Delivery</th>
            <th>Selection</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($resolvedRoster ?? collect()) as $index => $student)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student['student_name'] ?? '-' }}
              <div class="small text-muted">ID: {{ (int) ($student['student_id'] ?? 0) }}</div>
            </td>
            <td>{{ $student['roll_no'] ?? '-' }}</td>

            <td>
              @php
              $programCode = trim((string) ($student['program_code'] ?? ''));
              $programName = trim((string) ($student['program_name'] ?? ''));
              @endphp
              @if($programCode !== '' || $programName !== '')
              <div><strong>{{ $programCode !== '' ? $programCode : 'N/A' }}</strong></div>
              <div class="small text-muted">{{ $programName !== '' ? $programName : 'Unknown Program' }}</div>
              @else
              {{ (int) ($student['program_id'] ?? 0) }}
              @endif
            </td>
            <td>{{ (string) ($student['batch_name'] ?? '-') }}</td>
            <td>{{ (int) ($student['semester_id'] ?? 0) }}</td>
            <td>{{ $student['academic_pathway'] ?? '-' }}</td>
            <td>{{ $student['degree_track'] ?? '-' }}</td>
            <td>{{ strtoupper((string) ($student['delivery_type'] ?? '')) }}</td>
            <td>{{ strtoupper((string) ($student['selection_type'] ?? '')) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
</div>

<script>
  (function() {
    'use strict';

    const searchInput = document.getElementById('curriculumRowsSearch');
    const programCodeFilter = document.getElementById('curriculumProgramCodeFilter');
    const tableBody = document.getElementById('curriculumRowsTableBody');
    const noMatchAlert = document.getElementById('curriculumRowsNoMatch');
    const resolveForm = document.getElementById('resolveRosterForm');
    const resolveButton = document.getElementById('resolveRosterButton');

    if (!searchInput || !tableBody) {
      return;
    }

    const rows = Array.from(tableBody.querySelectorAll('.curriculum-row-item'));

    const applyFilter = function() {
      const query = String(searchInput.value || '').trim().toLowerCase();
      const selectedProgramCode = String(programCodeFilter ? (programCodeFilter.value || '') : '').trim().toUpperCase();
      let visibleCount = 0;

      rows.forEach(function(row) {
        const text = String(row.textContent || '').toLowerCase();
        const rowProgramCode = String(row.getAttribute('data-program-code') || '').trim().toUpperCase();
        const textMatch = query === '' || text.indexOf(query) !== -1;
        const programCodeMatch = selectedProgramCode === '' || rowProgramCode === selectedProgramCode;
        const isMatch = textMatch && programCodeMatch;
        row.classList.toggle('d-none', !isMatch);
        if (isMatch) {
          visibleCount++;
        }
      });

      if (noMatchAlert) {
        noMatchAlert.classList.toggle('d-none', visibleCount > 0);
      }
    };

    searchInput.addEventListener('input', applyFilter);
    if (programCodeFilter) {
      programCodeFilter.addEventListener('change', applyFilter);
    }

    // Reapply persisted filters after page reload (e.g., after Resolve Student Roster).
    applyFilter();

    if (resolveForm && resolveButton) {
      resolveForm.addEventListener('submit', function() {
        resolveButton.disabled = true;
        const defaultLabel = resolveButton.querySelector('.resolve-label-default');
        const loadingLabel = resolveButton.querySelector('.resolve-label-loading');
        if (defaultLabel) {
          defaultLabel.classList.add('d-none');
        }
        if (loadingLabel) {
          loadingLabel.classList.remove('d-none');
        }
      });
    }
  }());
</script>

@include('includes.footer')
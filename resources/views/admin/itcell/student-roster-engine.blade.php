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
    <form method="GET" action="{{ route('itcell.resolve.student.list') }}" id="resolveRosterForm">
      <input type="hidden" name="batch_id" value="{{ (int) $selectedBatchId }}">
      <input type="hidden" name="semester_id" value="{{ (int) $selectedSemesterId }}">
      <input type="hidden" name="teaching_group_id" value="{{ (int) ($teachingGroupId ?? 0) }}">
      <input type="hidden" name="teaching_assignment_id" value="{{ (int) ($teachingAssignmentId ?? 0) }}">
      <input type="hidden" name="curriculum_id" id="selectedCurriculumId" value="{{ (int) ($selectedCurriculumRowId ?? 0) }}">
      <input type="hidden" name="program_id" id="selectedProgramId" value="{{ (int) ($selectedCurriculumRow->program_id ?? 0) }}">

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
          <div class="input-group">
            <input
              type="text"
              name="curriculum_search"
              id="curriculumRowsSearch"
              class="form-control"
              value="{{ $persistedCurriculumSearch }}"
              placeholder="Search by course code/title, batch                                                                                                                                                                                                                                                                                                                                                                                                                                     name, semester, delivery, selection, program name, pathway, or department">
            <button
              type="button"
              id="clearCurriculumRowsSearch"
              class="btn btn-outline-secondary"
              aria-label="Clear search"
              title="Clear search">
              &times;
            </button>
          </div>
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
              <th style="width: 120px;">Curriculum ID</th>
              <th>Course</th>
              <th>Course Type</th>
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
                <input type="radio" name="curriculum_row_id" value="{{ $rowId }}" data-program-id="{{ (int) ($row->program_id ?? 0) }}" {{ (int) $selectedCurriculumRowId === $rowId ? 'checked' : '' }} required>
              </td>

              <td><span class="badge bg-light text-dark border">{{ $rowId }}</span></td>

              <td>
                <strong>{{ $row->course_code ?? 'N/A' }}</strong>
                <div class="small text-muted">{{ $row->course_title ?? 'Untitled course' }}</div>
              </td>
              <td>{{ trim((string) ($row->course_type_name ?? '')) !== '' ? $row->course_type_name : strtoupper((string) ($row->course_type ?? '-')) }}</td>

              <td>{{ $row->batch_name ?? '-' }}</td>
              <td>{{ $row->semester ?? '-' }}</td>
              <td><span class="badge badge-warning">{{ strtoupper((string) ($row->delivery_category ?? 'COMMON')) }}</span></td>
              <td>{{ strtoupper((string) ($row->course_type ?? 'AUTO')) }}</td>
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
      <div class="col-md-2"><strong>Students:</strong> {{ ($resolvedRoster ?? collect())->count() }}</div>
      <div class="col-md-12"><strong>Rule Applied:</strong> {{ $rosterContext['rule_code'] ?? 'NO_RULE' }} - {{ $rosterContext['rule_name'] ?? 'No rule matched' }} @if((int) ($rosterContext['rule_mapping_id'] ?? 0) > 0)<span class="text-muted">(Mapping ID: {{ (int) $rosterContext['rule_mapping_id'] }})</span>@endif</div>
      <div class="col-md-3"><strong>Course Type:</strong> {{ trim((string) ($selectedCurriculumRow->course_type_name ?? '')) !== '' ? $selectedCurriculumRow->course_type_name : strtoupper((string) ($selectedCurriculumRow->course_type ?? '-')) }}</div>
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

@if(($rosterExclusionReasons ?? collect())->isNotEmpty())
<div class="card shadow-sm mb-4 border-warning-subtle">
  <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
    <h5 class="mb-0 text-warning-emphasis">Why Students Were Excluded (Latest Resolve)</h5>
    <span class="badge bg-warning text-dark">{{ ($rosterExclusionReasons ?? collect())->sum('total') }} Exclusion(s)</span>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Reason Code</th>
            <th style="width: 160px;">Count</th>
          </tr>
        </thead>
        <tbody>
          @foreach(($rosterExclusionReasons ?? collect()) as $index => $reason)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td><strong>{{ $reason['reason_code'] ?? 'UNKNOWN' }}</strong></td>
            <td><span class="badge bg-secondary">{{ (int) ($reason['total'] ?? 0) }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="small text-muted mt-2">
      Reason counts are pulled from student_roster_rule_results for the latest Resolve Student Roster run.
    </div>

    @if(($rosterExcludedStudents ?? collect())->isNotEmpty())
    <hr class="my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
      <h6 class="mb-0">Excluded Students (Latest Resolve)</h6>
      <div style="min-width: 260px;">
        <label for="excludedReasonFilter" class="form-label mb-1">Filter by Reason Code</label>
        <select id="excludedReasonFilter" class="form-select form-select-sm">
          <option value="">All Reasons</option>
          @foreach(($rosterExclusionReasons ?? collect()) as $reason)
          <option value="{{ strtoupper(trim((string) ($reason['reason_code'] ?? 'UNKNOWN'))) }}">
            {{ strtoupper(trim((string) ($reason['reason_code'] ?? 'UNKNOWN'))) }}
          </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 70px;">#</th>
            <th>Student</th>
            <th>Roll No</th>
            <th>Register No</th>
            <th>Program</th>
            <th>Batch</th>
            <th>Pathway / Track</th>
            <th>Reason Code</th>
            <th>Reason</th>
          </tr>
        </thead>
        <tbody id="excludedStudentsTableBody">
          @foreach(($rosterExcludedStudents ?? collect()) as $index => $student)
          <tr class="excluded-student-row" data-reason-code="{{ strtoupper(trim((string) ($student['reason_code'] ?? 'UNKNOWN'))) }}">
            <td>{{ $index + 1 }}</td>
            <td>
              {{ $student['student_name'] ?? '-' }}
              <div class="small text-muted">ID: {{ (int) ($student['student_id'] ?? 0) }}</div>
            </td>
            <td>{{ $student['roll_no'] ?? '-' }}</td>
            <td>{{ $student['register_no'] ?? '-' }}</td>
            <td>
              <div><strong>{{ $student['program_code'] ?? 'N/A' }}</strong></div>
              <div class="small text-muted">{{ $student['program_name'] ?? 'Unknown Program' }}</div>
            </td>
            <td>{{ $student['batch_name'] ?? '-' }}</td>
            <td>
              <div>{{ $student['academic_pathway'] ?? '-' }}</div>
              <div class="small text-muted">{{ $student['degree_track'] ?? '-' }}</div>
            </td>
            <td><span class="badge bg-secondary">{{ $student['reason_code'] ?? 'UNKNOWN' }}</span></td>
            <td>
              {{ $student['reason'] ?? '-' }}
              @if(strtoupper(trim((string) ($student['reason_code'] ?? ''))) === 'NO_ACADEMIC_PATHWAY')
              <div class="mt-2">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-success js-fix-pathway-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#fixPathwayModal"
                  data-student-id="{{ (int) ($student['student_id'] ?? 0) }}"
                  data-student-name="{{ $student['student_name'] ?? '' }}">
                  Fix Pathway
                </button>
              </div>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="alert alert-warning d-none mt-2 mb-0" id="excludedStudentsNoMatch">
      No excluded students found for the selected reason code.
    </div>
    @endif
  </div>
</div>
@endif

<div class="modal fade" id="fixPathwayModal" tabindex="-1" aria-labelledby="fixPathwayModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('itcell.student-roster-engine.fix-pathway') }}" id="fixPathwayForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="fixPathwayModalLabel">Fix Academic Pathway</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert d-none" id="fixPathwayFeedback" role="alert"></div>

          <p class="mb-2">
            Update pathway for:
            <strong id="fixPathwayStudentName">-</strong>
          </p>

          <input type="hidden" name="student_id" id="fixPathwayStudentId" value="0">
          <input type="hidden" name="batch_id" value="{{ (int) ($selectedBatchId ?? 0) }}">
          <input type="hidden" name="semester_id" value="{{ (int) (($rosterContext['semester_id'] ?? 0) > 0 ? ($rosterContext['semester_id'] ?? 0) : ($selectedSemesterId ?? 0)) }}">
          <input type="hidden" name="curriculum_row_id" value="{{ (int) ($selectedCurriculumRowId ?? 0) }}">
          <input type="hidden" name="teaching_group_id" value="{{ (int) ($teachingGroupId ?? 0) }}">
          <input type="hidden" name="teaching_assignment_id" value="{{ (int) ($teachingAssignmentId ?? 0) }}">

          <label for="fixPathwayAcademicPathway" class="form-label">Academic Pathway</label>
          <select name="academic_pathway_id" id="fixPathwayAcademicPathway" class="form-select" required>
            <option value="">Select pathway</option>
            @foreach(($pathways ?? collect()) as $pathway)
            <option value="{{ (int) ($pathway->id ?? 0) }}">{{ $pathway->name ?? 'Unnamed Pathway' }}</option>
            @endforeach
          </select>

          <label for="fixPathwayDegreeTrack" class="form-label mt-3">Degree Track </label>
          <select name="degree_track_id" id="fixPathwayDegreeTrack" class="form-select">
            <option value="">Keep existing degree track</option>
            @foreach(($degreeTracks ?? collect()) as $track)
            <option value="{{ (int) ($track->id ?? 0) }}">{{ $track->name ?? 'Unnamed Track' }}</option>
            @endforeach
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="fixPathwaySubmitButton">Save Pathway &amp; Track</button>
        </div>
      </form>
    </div>
  </div>
</div>

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
    const clearSearchButton = document.getElementById('clearCurriculumRowsSearch');
    const programCodeFilter = document.getElementById('curriculumProgramCodeFilter');
    const tableBody = document.getElementById('curriculumRowsTableBody');
    const noMatchAlert = document.getElementById('curriculumRowsNoMatch');
    const resolveForm = document.getElementById('resolveRosterForm');
    const selectedCurriculumIdField = document.getElementById('selectedCurriculumId');
    const selectedProgramIdField = document.getElementById('selectedProgramId');
    const resolveButton = document.getElementById('resolveRosterButton');
    const excludedReasonFilter = document.getElementById('excludedReasonFilter');
    const excludedStudentsTableBody = document.getElementById('excludedStudentsTableBody');
    const excludedStudentsNoMatch = document.getElementById('excludedStudentsNoMatch');
    const fixPathwayButtons = document.querySelectorAll('.js-fix-pathway-btn');
    const fixPathwayStudentIdInput = document.getElementById('fixPathwayStudentId');
    const fixPathwayStudentName = document.getElementById('fixPathwayStudentName');
    const fixPathwayAcademicPathwaySelect = document.getElementById('fixPathwayAcademicPathway');
    const fixPathwayDegreeTrackSelect = document.getElementById('fixPathwayDegreeTrack');
    const fixPathwayForm = document.getElementById('fixPathwayForm');
    const fixPathwayModal = document.getElementById('fixPathwayModal');
    const fixPathwaySubmitButton = document.getElementById('fixPathwaySubmitButton');
    const fixPathwayFeedback = document.getElementById('fixPathwayFeedback');

    if (!searchInput || !tableBody) {
      return;
    }

    const rows = Array.from(tableBody.querySelectorAll('.curriculum-row-item'));
    const curriculumRadioButtons = Array.from(document.querySelectorAll('input[name="curriculum_row_id"]'));

    const syncSelectedCurriculumId = function() {
      if (!selectedCurriculumIdField) {
        return;
      }

      const selectedRadio = curriculumRadioButtons.find(function(radio) {
        return radio.checked;
      });

      selectedCurriculumIdField.value = selectedRadio ? String(selectedRadio.value || '') : '';

      if (selectedProgramIdField) {
        selectedProgramIdField.value = selectedRadio ? String(selectedRadio.getAttribute('data-program-id') || '') : '';
      }
    };

    const applyFilter = function() {
      const query = String(searchInput.value || '').trim().toLowerCase();
      const selectedProgramCode = String(programCodeFilter ? (programCodeFilter.value || '') : '').trim().toUpperCase();
      let visibleCount = 0;

      if (clearSearchButton) {
        clearSearchButton.classList.toggle('d-none', query === '');
      }

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

    if (clearSearchButton) {
      clearSearchButton.addEventListener('click', function() {
        searchInput.value = '';
        applyFilter();
        searchInput.focus();
      });
    }

    if (programCodeFilter) {
      programCodeFilter.addEventListener('change', applyFilter);
    }

    // Reapply persisted filters after page reload (e.g., after Resolve Student Roster).
    applyFilter();

    curriculumRadioButtons.forEach(function(radio) {
      radio.addEventListener('change', syncSelectedCurriculumId);
    });
    syncSelectedCurriculumId();

    const applyExcludedStudentFilter = function() {
      if (!excludedReasonFilter || !excludedStudentsTableBody) {
        return;
      }

      const selectedReasonCode = String(excludedReasonFilter.value || '').trim().toUpperCase();
      const excludedRows = Array.from(excludedStudentsTableBody.querySelectorAll('.excluded-student-row'));
      let visibleCount = 0;

      excludedRows.forEach(function(row) {
        const rowReasonCode = String(row.getAttribute('data-reason-code') || '').trim().toUpperCase();
        const isMatch = selectedReasonCode === '' || rowReasonCode === selectedReasonCode;
        row.classList.toggle('d-none', !isMatch);
        if (isMatch) {
          visibleCount++;
        }
      });

      if (excludedStudentsNoMatch) {
        excludedStudentsNoMatch.classList.toggle('d-none', visibleCount > 0);
      }
    };

    if (excludedReasonFilter && excludedStudentsTableBody) {
      excludedReasonFilter.addEventListener('change', applyExcludedStudentFilter);
      applyExcludedStudentFilter();
    }

    const isDualMajorPathwayName = function(pathwayName) {
      const normalized = String(pathwayName || '').trim().toLowerCase();
      return normalized.indexOf('dual') !== -1 && normalized.indexOf('major') !== -1;
    };

    const findRegularTrackOption = function() {
      if (!fixPathwayDegreeTrackSelect) {
        return null;
      }

      const options = Array.from(fixPathwayDegreeTrackSelect.options || []);
      return options.find(function(option) {
        return String(option.textContent || '').trim().toLowerCase().indexOf('regular') !== -1 && String(option.value || '').trim() !== '';
      }) || null;
    };

    const applyPathwayDegreeTrackDependency = function() {
      if (!fixPathwayAcademicPathwaySelect || !fixPathwayDegreeTrackSelect) {
        return;
      }

      const selectedOption = fixPathwayAcademicPathwaySelect.options[fixPathwayAcademicPathwaySelect.selectedIndex];
      const selectedPathwayName = selectedOption ? String(selectedOption.textContent || '') : '';
      const isDualMajor = isDualMajorPathwayName(selectedPathwayName);
      const degreeTrackOptions = Array.from(fixPathwayDegreeTrackSelect.options || []);

      degreeTrackOptions.forEach(function(option) {
        option.disabled = false;
      });

      if (!isDualMajor) {
        if (fixPathwayFeedback && fixPathwayFeedback.getAttribute('data-source') === 'pathway-rule') {
          fixPathwayFeedback.className = 'alert d-none';
          fixPathwayFeedback.textContent = '';
          fixPathwayFeedback.removeAttribute('data-source');
        }
        return;
      }

      const regularOption = findRegularTrackOption();
      if (!regularOption) {
        if (fixPathwayFeedback) {
          fixPathwayFeedback.className = 'alert alert-danger';
          fixPathwayFeedback.textContent = 'Dual Major pathway requires a Regular degree track, but no Regular option is available.';
          fixPathwayFeedback.setAttribute('data-source', 'pathway-rule');
        }
        if (fixPathwaySubmitButton) {
          fixPathwaySubmitButton.disabled = true;
        }
        return;
      }

      degreeTrackOptions.forEach(function(option) {
        option.disabled = String(option.value || '') !== String(regularOption.value || '');
      });

      fixPathwayDegreeTrackSelect.value = String(regularOption.value || '');

      if (fixPathwayFeedback) {
        fixPathwayFeedback.className = 'alert alert-info';
        fixPathwayFeedback.textContent = 'Dual Major selected: Degree Track is automatically set to Regular.';
        fixPathwayFeedback.setAttribute('data-source', 'pathway-rule');
      }
    };

    if (fixPathwayAcademicPathwaySelect) {
      fixPathwayAcademicPathwaySelect.addEventListener('change', function() {
        if (fixPathwaySubmitButton) {
          fixPathwaySubmitButton.disabled = false;
        }
        applyPathwayDegreeTrackDependency();
      });
    }

    if (fixPathwayButtons.length > 0 && fixPathwayStudentIdInput && fixPathwayStudentName) {
      fixPathwayButtons.forEach(function(button) {
        button.addEventListener('click', function() {
          const studentId = String(button.getAttribute('data-student-id') || '0');
          const studentName = String(button.getAttribute('data-student-name') || '-');
          fixPathwayStudentIdInput.value = studentId;
          fixPathwayStudentName.textContent = studentName;

          if (fixPathwayFeedback) {
            fixPathwayFeedback.className = 'alert d-none';
            fixPathwayFeedback.textContent = '';
            fixPathwayFeedback.removeAttribute('data-source');
          }

          if (fixPathwayAcademicPathwaySelect) {
            fixPathwayAcademicPathwaySelect.value = '';
          }

          if (fixPathwayDegreeTrackSelect) {
            fixPathwayDegreeTrackSelect.value = '';
            Array.from(fixPathwayDegreeTrackSelect.options || []).forEach(function(option) {
              option.disabled = false;
            });
          }

          if (fixPathwaySubmitButton) {
            fixPathwaySubmitButton.disabled = false;
          }

          applyPathwayDegreeTrackDependency();
        });
      });
    }

    if (fixPathwayForm) {
      fixPathwayForm.addEventListener('submit', function(event) {
        event.preventDefault();

        if (fixPathwaySubmitButton) {
          fixPathwaySubmitButton.disabled = true;
          fixPathwaySubmitButton.textContent = 'Saving...';
        }

        if (fixPathwayFeedback) {
          fixPathwayFeedback.className = 'alert d-none';
          fixPathwayFeedback.textContent = '';
        }

        const formData = new FormData(fixPathwayForm);

        fetch(fixPathwayForm.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: formData
          })
          .then(function(response) {
            return response.json().then(function(payload) {
              return {
                ok: response.ok,
                status: response.status,
                payload: payload
              };
            });
          })
          .then(function(result) {
            if (!result.ok || !result.payload || result.payload.ok === false) {
              let message = 'Unable to update pathway details. Please try again.';
              if (result.payload && result.payload.message) {
                message = String(result.payload.message);
              } else if (result.payload && result.payload.errors) {
                const firstField = Object.keys(result.payload.errors)[0];
                const firstError = firstField ? result.payload.errors[firstField] : null;
                if (Array.isArray(firstError) && firstError.length > 0) {
                  message = String(firstError[0]);
                }
              }
              throw new Error(message);
            }

            if (window.bootstrap && fixPathwayModal) {
              const modalInstance = window.bootstrap.Modal.getInstance(fixPathwayModal);
              if (modalInstance) {
                modalInstance.hide();
              }
            }

            window.location.reload();
          })
          .catch(function(error) {
            const message = error && error.message ? error.message : 'Request failed. Please try again.';
            if (fixPathwayFeedback) {
              fixPathwayFeedback.className = 'alert alert-danger';
              fixPathwayFeedback.textContent = message;
            }
          })
          .finally(function() {
            if (fixPathwaySubmitButton) {
              fixPathwaySubmitButton.disabled = false;
              fixPathwaySubmitButton.textContent = 'Save Pathway & Track';
            }
          });
      });
    }

    if (resolveForm && resolveButton) {
      resolveForm.addEventListener('submit', function() {
        syncSelectedCurriculumId();
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
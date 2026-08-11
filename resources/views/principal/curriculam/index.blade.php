@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Curriculam</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Program-wise Curriculam</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Program-wise Curriculam Overview</h5>
        <div class="d-flex gap-2 align-items-center">
          <span class="badge bg-primary">Programs: {{ $programRows->count() }}</span>
          <a href="{{ route('principal.curriculam.defaulters') }}" class="btn btn-sm btn-outline-danger">View Defaulters</a>
        </div>
      </div>


      <label for="curriculamSearchInput" class="form-label">Search</label>
      <input
        type="text"
        id="curriculamSearchInput"
        class="form-control"
        placeholder="Search by program, subject, course code/title, delivery type, or faculty">
      <small class="text-muted">Live filter on current loaded records.</small>


      <form method="GET" action="{{ route('principal.curriculam.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Campus</label>
          <select name="campus_id" class="form-select" {{ $isVicePrincipal ? 'disabled' : '' }}>
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (int) $selectedCampusId === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          @if($isVicePrincipal)
          <input type="hidden" name="campus_id" value="{{ (int) $selectedCampusId }}">
          <small class="text-muted">Campus is fixed for vice-principal.</small>
          @endif
        </div>

        <div class="col-md-3">
          <label class="form-label">Batch</label>
          <select name="batch_id" class="form-select">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ (int) $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Subject / Department</label>
          <select name="subject_id" class="form-select">
            <option value="">All Subjects</option>
            @foreach(($subjects ?? collect()) as $subject)
            <option value="{{ $subject->id }}" {{ (int) ($selectedSubjectId ?? 0) === (int) $subject->id ? 'selected' : '' }}>
              {{ $subject->code ? $subject->code . ' - ' : '' }}{{ $subject->title }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-success w-100"><i class="fa fa-search me-1"></i>Apply</button>
          <a href="{{ route('principal.curriculam.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>

    <div class="row mt-3 g-3">
      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Total Departments (Subjects)</div>
            <div class="h4 mb-0">{{ (int) ($curriculumSummary->total_departments ?? 0) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Offered Combinations</div>
            <div class="h4 mb-0">{{ (int) ($curriculumSummary->total_combinations ?? 0) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Combinations with Curriculum</div>
            <div class="h4 mb-0 text-success">{{ (int) ($curriculumSummary->combinations_with_curriculum ?? 0) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Combinations pending Curriculum</div>
            <div class="h4 mb-0 text-danger">{{ (int) ($curriculumSummary->combinations_without_curriculum ?? 0) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="alert alert-secondary mt-3 mb-0">
      Curriculum source table: <strong>{{ $curriculumSummary->curriculum_source_table ?? 'program_wise_semester_courses' }}</strong>
      @if(!empty($curriculumSummary->curriculum_records_found))
      <span class="badge bg-success ms-2">Records Found</span>
      @else
      <span class="badge bg-danger ms-2">No Records Found</span>
      @endif
    </div>

    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white">
        <h6 class="mb-0">Batch-wise Offered Combinations (from subject_has_student_progams)</h6>
      </div>
      <div class="card-body">
        @if(($batchWiseCombinationCounts ?? collect())->isEmpty())
        <div class="text-muted small mb-0">No combination rows found for selected filters.</div>
        @else
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 140px;">Batch</th>
                <th style="width: 220px;">Combinations Offered</th>
                <th style="width: 220px;">Departments (Subjects)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($batchWiseCombinationCounts as $batchStat)
              <tr>
                <td>{{ $batchStat->batch_name }}</td>
                <td>{{ (int) $batchStat->combination_count }}</td>
                <td>{{ (int) $batchStat->department_count }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>


    @if($programRows->isNotEmpty())
    @php
    $totalCurriculamRows = (int) $programRows->sum('curriculum_count');
    $completeCurriculamRows = 0;
    foreach ($programRows as $programRow) {
    foreach ($programRow->combinations as $combinationRow) {
    foreach ($combinationRow->curriculum_courses as $courseRow) {
    if (strtolower(trim((string) ($courseRow['assigned_faculty'] ?? ''))) !== 'not assigned yet') {
    $completeCurriculamRows++;
    }
    }
    }
    }
    $pendingCurriculamRows = max(0, $totalCurriculamRows - $completeCurriculamRows);
    $completionPercentage = $totalCurriculamRows > 0 ? round(($completeCurriculamRows / $totalCurriculamRows) * 100, 2) : 0;
    @endphp

    <div class="row mt-3 g-3" id="curriculamAnalyticsCards">
      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Total Curriculam Rows</div>
            <div class="h4 mb-0" id="analyticsTotalRows">{{ $totalCurriculamRows }}</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Complete</div>
            <div class="h4 mb-0 text-success" id="analyticsCompleteRows">{{ $completeCurriculamRows }}</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Pending</div>
            <div class="h4 mb-0 text-warning" id="analyticsPendingRows">{{ $pendingCurriculamRows }}</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="stat-card">
          <div class="card-body py-3">
            <div class="small text-muted">Completion Percentage</div>
            <div class="h4 mb-0 text-primary" id="analyticsCompletionPercentage">{{ $completionPercentage }}%</div>
          </div>
        </div>
      </div>
    </div>


    @endif

    @if($programRows->isEmpty())
    <div class="alert alert-info mt-3 mb-0">No program-wise curriculam records found for selected filters.</div>
    @else
    <div class="alert alert-warning mt-3 mb-0" id="curriculamNoSearchResult" style="display: none;">
      No matching curriculam records found.
    </div>

    <div class="row mt-3 g-3" id="curriculamProgramRows">
      @foreach($programRows as $program)
      @php
      $programSearchText = strtolower(trim(
      ($program->program_code ?? '') . ' ' .
      ($program->program_name ?? '') . ' ' .
      ($program->campus_name ?? '') . ' ' .
      ($program->program_type_name ?? '')
      ));
      @endphp
      <div class="col-12 curriculam-program-card" data-program-search="{{ $programSearchText }}">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <h5 class="mb-1">{{ $program->program_code }} - {{ $program->program_name }}</h5>
                <div class="text-muted small">Campus: {{ $program->campus_name }} | Program Type: {{ $program->program_type_name }}</div>
              </div>
              <div class="d-flex gap-2">
                <span class="badge bg-dark">Combinations: {{ $program->combination_count }}</span>
                <span class="badge bg-info text-dark">Curriculam Rows: {{ $program->curriculum_count }}</span>
              </div>
            </div>
          </div>
          <div class="card-body">
            @foreach($program->combinations as $combo)
            @php
            $comboSearchText = strtolower(trim(
            ($combo->batch_name ?? '') . ' ' .
            ($combo->subject_code ?? '') . ' ' .
            ($combo->subject_name ?? '') . ' ' .
            ($combo->program_type ?? '') . ' ' .
            ($combo->combination_id ?? '')
            ));
            $isNoCurriculamDefaulter = $combo->curriculum_courses->isEmpty();
            @endphp
            <div class="rounded p-2 mb-3 curriculam-combo-block {{ $isNoCurriculamDefaulter ? 'border border-danger border-2' : 'border' }}" data-combo-search="{{ $comboSearchText }}">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div>
                  <div class="fw-semibold">Batch: {{ $combo->batch_name }} | Subject: {{ $combo->subject_code !== '' ? $combo->subject_code . ' - ' : '' }}{{ $combo->subject_name }}</div>
                  <div class="small text-muted">Program Type: {{ strtoupper((string) ($combo->program_type ?? '-')) }} | Combination ID: {{ $combo->combination_id }}</div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <span class="badge bg-secondary">Courses: {{ $combo->curriculum_count }}</span>
                  @if($isNoCurriculamDefaulter)
                  <span class="badge bg-danger">Defaulter: No Curriculam</span>
                  @endif
                </div>
              </div>

              @if($combo->curriculum_courses->isEmpty())
              <div class="text-muted small">No curriculam courses mapped for this combination.</div>
              @else
              <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 80px;">Sem</th>
                      <th style="width: 160px;">Course Code</th>
                      <th>Course Title</th>
                      <th style="width: 170px;">Course Type</th>
                      <th style="width: 190px;">Delivery Type</th>
                      <th style="width: 340px;">Assigned Faculty</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($combo->curriculum_courses as $course)
                    @php
                    $courseSearchText = strtolower(trim(
                    ($course['course_code'] ?? '') . ' ' .
                    ($course['course_title'] ?? '') . ' ' .
                    ($course['course_type'] ?? '') . ' ' .
                    ($course['delivery_type'] ?? '') . ' ' .
                    ($course['assigned_faculty'] ?? '') . ' ' .
                    ($course['semester'] ?? '')
                    ));
                    @endphp
                    <tr data-search="{{ $courseSearchText }}" data-assigned="{{ strtolower(trim((string) ($course['assigned_faculty'] ?? ''))) !== 'not assigned yet' ? '1' : '0' }}">
                      <td>{{ $course['semester'] > 0 ? $course['semester'] : '-' }}</td>
                      <td>{{ $course['course_code'] }} <span class="badge badge-warning">{{ $course['course_code'] }}</span></td>
                      <td>{{ $course['course_title'] }}</td>
                      <td>{{ $course['course_type'] }}</td>
                      <td><span class="badge bg-light text-dark border">{{ $course['delivery_type'] }}</span></td>
                      <td>{{ $course['assigned_faculty'] }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endif
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </main>
</div>

<script>
  (function() {
    const input = document.getElementById('curriculamSearchInput');
    const programCards = Array.from(document.querySelectorAll('.curriculam-program-card'));
    const noResultBox = document.getElementById('curriculamNoSearchResult');
    const totalRowsEl = document.getElementById('analyticsTotalRows');
    const completeRowsEl = document.getElementById('analyticsCompleteRows');
    const pendingRowsEl = document.getElementById('analyticsPendingRows');
    const completionPercentageEl = document.getElementById('analyticsCompletionPercentage');

    function isVisible(element) {
      return !!(element && element.offsetParent !== null);
    }

    function updateAnalyticsFromVisibleRows() {
      const allRows = Array.from(document.querySelectorAll('tr[data-search]'));
      const visibleRows = allRows.filter((row) => isVisible(row));

      const totalRows = visibleRows.length;
      const completeRows = visibleRows.filter((row) => (row.getAttribute('data-assigned') || '0') === '1').length;
      const pendingRows = Math.max(0, totalRows - completeRows);
      const completionPercentage = totalRows > 0 ? ((completeRows / totalRows) * 100).toFixed(2) : '0.00';

      if (totalRowsEl) {
        totalRowsEl.textContent = String(totalRows);
      }
      if (completeRowsEl) {
        completeRowsEl.textContent = String(completeRows);
      }
      if (pendingRowsEl) {
        pendingRowsEl.textContent = String(pendingRows);
      }
      if (completionPercentageEl) {
        completionPercentageEl.textContent = completionPercentage + '%';
      }
    }

    if (!input || programCards.length === 0) {
      updateAnalyticsFromVisibleRows();
      return;
    }

    function applySearch() {
      const query = (input.value || '').toLowerCase().trim();
      let visibleProgramCount = 0;

      programCards.forEach((programCard) => {
        const programText = (programCard.getAttribute('data-program-search') || '').toLowerCase();
        const comboBlocks = Array.from(programCard.querySelectorAll('.curriculam-combo-block'));
        let hasVisibleCombo = false;

        comboBlocks.forEach((comboBlock) => {
          const comboText = (comboBlock.getAttribute('data-combo-search') || '').toLowerCase();
          const rows = Array.from(comboBlock.querySelectorAll('tbody tr[data-search]'));

          if (rows.length === 0) {
            const comboMatched = query === '' || comboText.includes(query) || programText.includes(query);
            comboBlock.style.display = comboMatched ? '' : 'none';
            hasVisibleCombo = hasVisibleCombo || comboMatched;
            return;
          }

          let comboHasVisibleRows = false;

          rows.forEach((row) => {
            const rowText = (row.getAttribute('data-search') || '').toLowerCase();
            const matched = query === '' || rowText.includes(query) || comboText.includes(query) || programText.includes(query);
            row.style.display = matched ? '' : 'none';
            comboHasVisibleRows = comboHasVisibleRows || matched;
          });

          comboBlock.style.display = comboHasVisibleRows ? '' : 'none';
          hasVisibleCombo = hasVisibleCombo || comboHasVisibleRows;
        });

        const programVisible = hasVisibleCombo || query === '' || programText.includes(query);
        programCard.style.display = programVisible ? '' : 'none';

        if (programVisible) {
          visibleProgramCount++;
        }
      });

      if (noResultBox) {
        noResultBox.style.display = visibleProgramCount === 0 ? '' : 'none';
      }

      updateAnalyticsFromVisibleRows();
    }

    input.addEventListener('input', applySearch);
    updateAnalyticsFromVisibleRows();
  })();
</script>

@include('includes.footer')
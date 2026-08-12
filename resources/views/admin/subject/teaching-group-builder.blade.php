@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-1">Teaching Group Builder</h4>
        <div class="text-muted small text-capitalize">{{ $subject->code ?? '-' }} - {{ $subject->title ?? '-' }}</div>
      </div>
      <a href="{{ route('department.timetable', [$subject->id, $subject->slug ?? 'timetable']) }}" class="btn btn-outline-primary btn-sm">
        <i class="fa fa-arrow-left me-1"></i>Back To Timetable
      </a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Deanery <span class="text-danger">*</span></label>
            <select id="deanerySelect" class="form-select">
              <option value="">--Select Deanery--</option>
              @foreach($deaneries as $deanery)
              <option value="{{ $deanery->id }}" {{ (int) $selectedDeaneryId === (int) $deanery->id ? 'selected' : '' }}>
                {{ $deanery->title }}
                @if(!empty($deanery->program))
                - {{ $deanery->program->name }}
                @endif
              </option>
              @endforeach
            </select>
            <small class="text-muted">Course list will be generated from all departments connected to selected deanery.</small>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold">Batch</label>
            <select id="batchSelect" class="form-select">
              <option value="">All</option>
              @foreach($batchOptions as $batch)
              <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold">Semester</label>
            <select id="semesterSelect" class="form-select">
              <option value="">All</option>
              @foreach($semesterOptions as $semester)
              <option value="{{ $semester->id }}">{{ $semester->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold">Program Type</label>
            <select id="programTypeSelect" class="form-select">
              <option value="">All</option>
              <option value="UG">UG</option>
              <option value="PG">PG</option>
            </select>
          </div>

          <div class="col-md-2">
            <button type="button" class="btn btn-primary w-100" id="loadDeaneryCoursesBtn">
              <i class="fa fa-list me-1"></i>Generate List
            </button>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-semibold">Search In List</label>
            <input type="text" id="courseSearchInput" class="form-control" placeholder="Search course code, title, delivery, or department">
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Deanery Course List</div>
        <div class="small text-muted" id="courseListMeta">Select a deanery and click Generate List.</div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 48px;">
                  <input type="checkbox" id="selectAllCourses">
                </th>
                <th>Course</th>
                <th style="width: 110px;">Batch</th>
                <th style="width: 110px;">Semester</th>
                <th style="width: 120px;">Program</th>
                <th style="width: 130px;">Delivery</th>
                <th style="width: 220px;">Offering Department</th>
              </tr>
            </thead>
            <tbody id="deaneryCourseTableBody">
              <tr id="courseListPlaceholderRow">
                <td colspan="7" class="text-center text-muted py-4">No data loaded yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="small text-muted" id="selectedCountText">Selected: 0</div>
        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
          Continue To Group Builder (next step)
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    const deanerySelect = document.getElementById('deanerySelect');
    const batchSelect = document.getElementById('batchSelect');
    const semesterSelect = document.getElementById('semesterSelect');
    const programTypeSelect = document.getElementById('programTypeSelect');
    const loadBtn = document.getElementById('loadDeaneryCoursesBtn');
    const tableBody = document.getElementById('deaneryCourseTableBody');
    const metaText = document.getElementById('courseListMeta');
    const searchInput = document.getElementById('courseSearchInput');
    const selectAll = document.getElementById('selectAllCourses');
    const selectedCountText = document.getElementById('selectedCountText');
    const endpoint = "{{ route('department.timetable.group-courses', [$subject->id]) }}";

    let allRows = [];

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = value ?? '';
      return div.innerHTML;
    }

    function updateSelectedCount() {
      const checked = tableBody.querySelectorAll('.group-course-checkbox:checked').length;
      selectedCountText.textContent = `Selected: ${checked}`;
    }

    function renderRows() {
      const keyword = (searchInput.value || '').toLowerCase().trim();
      const filtered = keyword === '' ? allRows : allRows.filter((row) => {
        const line = [
          row.course_label,
          row.delivery_type,
          row.offering_dept_name,
          row.program_type,
          String(row.batch || ''),
          String(row.semester || ''),
        ].join(' ').toLowerCase();
        return line.includes(keyword);
      });

      if (!filtered.length) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No matching courses found.</td></tr>';
        updateSelectedCount();
        return;
      }

      tableBody.innerHTML = filtered.map((row) => {
        return `
          <tr data-search="${escapeHtml((row.course_label + ' ' + row.delivery_type + ' ' + row.offering_dept_name + ' ' + row.program_type).toLowerCase())}">
            <td class="text-center">
              <input
                type="checkbox"
                class="group-course-checkbox"
                value="${row.curriculum_row_id}"
                data-course-id="${row.course_id}"
                data-offering-dept-id="${row.offering_dept_id}"
              >
            </td>
            <td>${escapeHtml(row.course_label || '-')}</td>
            <td>${escapeHtml(String(row.batch || '-'))}</td>
            <td>${escapeHtml(String(row.semester || '-'))}</td>
            <td>${escapeHtml(row.program_type || '-')}</td>
            <td>${escapeHtml(row.delivery_type || '-')}</td>
            <td>${escapeHtml(row.offering_dept_name || '-')}</td>
          </tr>
        `;
      }).join('');

      updateSelectedCount();
    }

    async function loadCourseList() {
      const deaneryId = Number(deanerySelect.value || 0);
      if (!deaneryId) {
        alert('Please select a deanery first.');
        return;
      }

      loadBtn.disabled = true;
      loadBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Loading...';

      const params = new URLSearchParams();
      params.set('deanery_id', String(deaneryId));
      if (batchSelect.value) params.set('batch_id', batchSelect.value);
      if (semesterSelect.value) params.set('semester_id', semesterSelect.value);
      if (programTypeSelect.value) params.set('program_type', programTypeSelect.value);
      if (searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());

      try {
        const response = await fetch(`${endpoint}?${params.toString()}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          }
        });

        const payload = await response.json();
        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'Failed to load course list.');
        }

        allRows = Array.isArray(payload.rows) ? payload.rows : [];
        const count = Number(payload.meta?.count || 0);
        metaText.textContent = `Loaded ${count} deanery course row(s).`;
        selectAll.checked = false;
        renderRows();
      } catch (error) {
        allRows = [];
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${escapeHtml(error.message || 'Failed to load courses.')}</td></tr>`;
        metaText.textContent = 'Unable to generate list.';
        updateSelectedCount();
      } finally {
        loadBtn.disabled = false;
        loadBtn.innerHTML = '<i class="fa fa-list me-1"></i>Generate List';
      }
    }

    loadBtn.addEventListener('click', loadCourseList);
    searchInput.addEventListener('input', renderRows);

    tableBody.addEventListener('change', function(event) {
      const checkbox = event.target.closest('.group-course-checkbox');
      if (!checkbox) return;
      updateSelectedCount();
    });

    selectAll.addEventListener('change', function() {
      const checkboxes = tableBody.querySelectorAll('.group-course-checkbox');
      checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
      updateSelectedCount();
    });

    if (Number(deanerySelect.value || 0) > 0) {
      loadCourseList();
    }
  })();
</script>

@include('includes.footer')
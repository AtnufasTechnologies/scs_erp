@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <script id="facultyOptionsJson" type="application/json">
    @json($facultyOptions ?? [])
  </script>
  <script id="facultyOptionsByDeptJson" type="application/json">
    @json($facultyOptionsByDept ?? [])
  </script>
  <script id="createdGroupRowsJson" type="application/json">
    @json($createdGroupRows ?? [])
  </script>
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
            <label class="form-label fw-semibold">Deanery <span class="text-danger">*</span></label> <small class="text-muted">Inter-deanery grouping is blocked. </small>
            <select id="deanerySelect" class="form-select" disabled>
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

          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
            <select id="deptSelect" class="form-select">
              <option value="">--Select Department--</option>
              @if(!empty($departments) && count($departments) > 0)
              @foreach($departments as $department)
              <option value="{{ (int) $department->id }}" data-dept-title="{{ $department->title ?? '-' }}">
                {{ $department->title ?? '-' }}
              </option>
              @endforeach
              @endif
            </select>
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
          <div class="col-md-1">
            <label class="form-label fw-semibold">Semester</label>
            <select id="semesterSelect" class="form-select">
              <option value="">All</option>
              @foreach($semesterOptions as $semester)
              <option value="{{ $semester->id }}">{{ $semester->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-1">
            <label class="form-label fw-semibold">Program Type</label>
            <select id="programTypeSelect" class="form-select">
              <option value="">All</option>
              <option value="UG">UG</option>
              <option value="PG">PG</option>
            </select>
          </div>

        </div>
      </div>
    </div>

    <div class="row g-3 align-items-start">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Deanery Course List</div>
            <div class="small text-muted" id="courseListMeta">Choose batch/semester/program type and click a department.</div>
          </div>
          <div class="card-body border-bottom">
            <label class="form-label fw-semibold mb-1">Search In List</label>
            <div class="input-group">
              <input type="text" id="courseSearchInput" class="form-control" placeholder="Search course code, title, delivery, or department">
              <button type="button" class="btn btn-outline-secondary d-none" id="clearCourseSearchBtn" title="Clear search" aria-label="Clear search">&times;</button>
            </div>
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
            <button type="button" class="btn btn-primary btn-sm" id="addSelectedToBucketBtn" disabled>
              <i class="fa fa-plus me-1"></i>Add Selected To Group Bucket
            </button>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Group Bucket</div>
            <div class="small text-muted" id="bucketMetaText">No courses added yet.</div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Course</th>
                    <th style="width: 110px;">Batch</th>
                    <th style="width: 110px;">Semester</th>
                    <th style="width: 100px;">Students</th>
                    <th style="width: 130px;">Delivery</th>
                    <th style="width: 220px;">Offering Department</th>
                    <th style="width: 90px;">Action</th>
                  </tr>
                </thead>
                <tbody id="groupBucketTableBody">
                  <tr id="groupBucketPlaceholderRow">
                    <td colspan="7" class="text-center text-muted py-4">No course in group bucket.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div class="small text-muted" id="bucketCountText">Bucket: 0 | Total Students Attending: 0</div>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-outline-danger btn-sm" id="clearBucketBtn" disabled>
                <i class="fa fa-trash me-1"></i>Clear Bucket
              </button>
              <button type="button" class="btn btn-success btn-sm" id="continueToGroupBuilderBtn" disabled>
                Create Teaching Group
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Created Teaching Groups</div>
        <button type="button" class="btn btn-primary btn-sm" id="saveCreatedGroupFacultyBtn">
          Save Faculty
        </button>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 150px;">Group ID</th>
                <th style="width: 130px;">Group</th>
                <th>Courses Attached</th>
                <th style="width: 120px;">Students</th>
                <th style="width: 240px;">Assigned Faculty</th>
                <th style="width: 180px;">Room No</th>
                <th style="width: 280px;">Faculty</th>
                <th style="width: 110px;">Action</th>
              </tr>
            </thead>
            <tbody id="createdGroupTableBody">
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No teaching groups created yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white small text-muted" id="createdGroupMetaText">Create a group from bucket, then assign faculty here.</div>
    </div>
  </div>
</div>

<div class="modal fade" id="allotTimeslotModal" tabindex="-1" aria-labelledby="allotTimeslotModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="allotTimeslotModalLabel">Allot Timeslot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted" id="allotTimeslotGroupLabel">Selected Group: -</div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Day</label>
          <select id="allotTimeslotDay" class="form-select">
            <option value="">--Select Day--</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
          </select>
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold">Hour</label>
          <input type="number" id="allotTimeslotHour" class="form-control" min="1" max="20" step="1" placeholder="e.g. 1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="submitAllotTimeslotBtn">Allot</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    const csrfToken = "{{ csrf_token() }}";
    const deanerySelect = document.getElementById('deanerySelect');
    const batchSelect = document.getElementById('batchSelect');
    const semesterSelect = document.getElementById('semesterSelect');
    const programTypeSelect = document.getElementById('programTypeSelect');
    const deptSelect = document.getElementById('deptSelect');
    const tableBody = document.getElementById('deaneryCourseTableBody');
    const metaText = document.getElementById('courseListMeta');
    const searchInput = document.getElementById('courseSearchInput');
    const clearCourseSearchBtn = document.getElementById('clearCourseSearchBtn');
    const selectAll = document.getElementById('selectAllCourses');
    const selectedCountText = document.getElementById('selectedCountText');
    const addSelectedToBucketBtn = document.getElementById('addSelectedToBucketBtn');
    const bucketTableBody = document.getElementById('groupBucketTableBody');
    const bucketMetaText = document.getElementById('bucketMetaText');
    const bucketCountText = document.getElementById('bucketCountText');
    const clearBucketBtn = document.getElementById('clearBucketBtn');
    const continueToGroupBuilderBtn = document.getElementById('continueToGroupBuilderBtn');
    const endpoint = "{{ route('department.timetable.group-courses', [$subject->id]) }}";
    const saveGroupEndpoint = "{{ route('department.timetable.group-builder.save', [$subject->id]) }}";
    const assignFacultyEndpoint = "{{ route('department.timetable.group-builder.assign-faculty', [$subject->id]) }}";
    const deleteGroupEndpoint = "{{ route('department.timetable.group-builder.delete-group', [$subject->id]) }}";
    const allotTimeslotEndpointTemplate = "{{ route('department.timetable.store', [$subject->id, 'BATCH_ID', 'SEMESTER_ID']) }}";
    const createdGroupTableBody = document.getElementById('createdGroupTableBody');
    const createdGroupMetaText = document.getElementById('createdGroupMetaText');
    const saveCreatedGroupFacultyBtn = document.getElementById('saveCreatedGroupFacultyBtn');
    const allotTimeslotGroupLabel = document.getElementById('allotTimeslotGroupLabel');
    const allotTimeslotDay = document.getElementById('allotTimeslotDay');
    const allotTimeslotHour = document.getElementById('allotTimeslotHour');
    const submitAllotTimeslotBtn = document.getElementById('submitAllotTimeslotBtn');
    const facultyOptions = (() => {
      const raw = document.getElementById('facultyOptionsJson')?.textContent || '[]';
      try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    })();
    const facultyOptionsByDept = (() => {
      const raw = document.getElementById('facultyOptionsByDeptJson')?.textContent || '{}';
      try {
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
      } catch (error) {
        return {};
      }
    })();
    let createdGroupRows = (() => {
      const raw = document.getElementById('createdGroupRowsJson')?.textContent || '[]';
      try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    })();

    let allRows = [];
    let selectedDeptId = 0;
    let selectedDeptName = '';
    let bucketRows = [];
    let selectedAllotGroupId = 0;

    function normalizeRow(raw) {
      return {
        curriculum_row_id: Number(raw.curriculum_row_id || 0),
        course_id: Number(raw.course_id || 0),
        course_code: String(raw.course_code || '-'),
        course_title: String(raw.course_title || '-'),
        course_label: String(raw.course_label || `${raw.course_code || '-'} - ${raw.course_title || '-'}`),
        batch: Number(raw.batch || 0),
        batch_name: String(raw.batch_name || ''),
        semester: Number(raw.semester || 0),
        program_type: String(raw.program_type || '-'),
        student_count: Number(raw.student_count || 0),
        delivery_type: String(raw.delivery_type || '-'),
        course_type: String(raw.course_type || ''),
        offering_dept_id: Number(raw.offering_dept_id || 0),
        offering_dept_name: String(raw.offering_dept_name || '-'),
        student_program_id: Number(raw.student_program_id || 0),
        student_program_code: String(raw.student_program_code || '').toUpperCase(),
      };
    }

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = value ?? '';
      return div.innerHTML;
    }

    function updateSelectedCount() {
      const checked = tableBody.querySelectorAll('.group-course-checkbox:checked').length;
      selectedCountText.textContent = `Selected: ${checked}`;
      addSelectedToBucketBtn.disabled = checked <= 0;
    }

    function updateBucketControls() {
      const count = bucketRows.length;
      const programStudentMap = new Map();

      bucketRows.forEach((row) => {
        const programId = Number(row.student_program_id || 0);
        const programCode = String(row.student_program_code || '').trim().toUpperCase();
        const key = programId > 0 ? `id:${programId}` : `code:${programCode || 'UNKNOWN'}`;
        const currentCount = Number(row.student_count || 0);

        if (!programStudentMap.has(key) || currentCount > Number(programStudentMap.get(key).count || 0)) {
          programStudentMap.set(key, {
            label: programCode || 'UNKNOWN',
            count: currentCount,
          });
        }
      });

      const programParts = Array.from(programStudentMap.values())
        .filter((entry) => entry.count > 0)
        .map((entry) => `${entry.label} ${entry.count}`);
      const totalStudentsAttending = Array.from(programStudentMap.values())
        .reduce((sum, entry) => sum + Number(entry.count || 0), 0);

      bucketCountText.textContent = `Bucket: ${count} | Students: ${programParts.length ? programParts.join(', ') : '0'} | Total: ${totalStudentsAttending}`;
      bucketMetaText.textContent = count > 0 ? `${count} course(s) ready for group building.` : 'No courses added yet.';
      clearBucketBtn.disabled = count <= 0;
      continueToGroupBuilderBtn.disabled = count <= 0;
    }

    function syncSearchClearButton() {
      if (!clearCourseSearchBtn) return;
      const hasText = (searchInput?.value || '').trim() !== '';
      clearCourseSearchBtn.classList.toggle('d-none', !hasText);
    }

    function clearSearchInput(shouldRender = false) {
      if (!searchInput) return;
      searchInput.value = '';
      syncSearchClearButton();
      if (shouldRender) {
        renderRows();
      }
    }

    function resolveFacultyOptionsForRow(row) {
      const deptIds = Array.isArray(row?.dept_ids) ? row.dept_ids.map((value) => Number(value || 0)).filter((value) => value > 0) : [];
      const scopedDeptIds = deptIds.length ? deptIds : (selectedDeptId > 0 ? [selectedDeptId] : []);
      const optionMap = new Map();

      scopedDeptIds.forEach((deptId) => {
        const list = facultyOptionsByDept[String(deptId)] || [];
        if (!Array.isArray(list)) return;
        list.forEach((faculty) => {
          const id = Number(faculty.id || 0);
          if (id > 0 && !optionMap.has(id)) {
            optionMap.set(id, {
              id,
              label: String(faculty.label || '-'),
            });
          }
        });
      });

      return optionMap.size > 0 ? Array.from(optionMap.values()) : facultyOptions;
    }

    function renderCreatedGroupFacultyOptions(selectedFacultyId, row) {
      const selectedId = Number(selectedFacultyId || 0);
      const options = ['<option value="">--Select Faculty--</option>'];
      const scopedOptions = resolveFacultyOptionsForRow(row);

      scopedOptions.forEach((faculty) => {
        const id = Number(faculty.id || 0);
        const selected = id === selectedId ? 'selected' : '';
        options.push(`<option value="${id}" ${selected}>${escapeHtml(String(faculty.label || '-'))}</option>`);
      });

      if (selectedId > 0 && !scopedOptions.some((faculty) => Number(faculty.id || 0) === selectedId)) {
        const selectedLabel = String(row?.faculty_label || '').trim() || `Faculty #${selectedId}`;
        options.push(`<option value="${selectedId}" selected>${escapeHtml(selectedLabel)}</option>`);
      }

      return options.join('');
    }

    function allocationAlphabet(groupNumber) {
      const n = Number(groupNumber || 0);
      if (n <= 0) return '-';

      let num = n;
      let result = '';
      while (num > 0) {
        num -= 1;
        result = String.fromCharCode(65 + (num % 26)) + result;
        num = Math.floor(num / 26);
      }

      return result;
    }

    function findCreatedGroupById(groupId) {
      const id = Number(groupId || 0);
      if (id <= 0) return null;
      return createdGroupRows.find((row) => Number(row.allocation_group_id || 0) === id) || null;
    }

    function openAllotTimeslotModal(groupId) {
      const id = Number(groupId || 0);
      if (id <= 0) {
        alert('Invalid group selected.');
        return;
      }

      selectedAllotGroupId = id;
      const row = findCreatedGroupById(id);
      allotTimeslotGroupLabel.textContent = `Selected Group: ${String(row?.group_identifier || ('Group ' + id))}`;
      allotTimeslotDay.value = '';
      allotTimeslotHour.value = '';

      const modal = new bootstrap.Modal(document.getElementById('allotTimeslotModal'));
      modal.show();
    }

    function summarizeAffectedSlots(meta) {
      const slots = Array.isArray(meta?.affected_slots) ? meta.affected_slots : [];
      if (!slots.length) {
        return 'No affected slot details returned.';
      }

      const lines = slots.slice(0, 15).map((slot) => {
        const operation = String(slot.operation || 'updated').toUpperCase();
        const day = String(slot.day || '-');
        const hour = Number(slot.hour || 0);
        const batch = Number(slot.batch_id || 0);
        const semester = Number(slot.semester_id || 0);
        const program = String(slot.program_type || 'UG').toUpperCase();
        return `${operation}: ${day} H${hour} | B${batch} S${semester} ${program}`;
      });

      if (slots.length > lines.length) {
        lines.push(`...and ${slots.length - lines.length} more.`);
      }

      return lines.join('\n');
    }

    function renderCreatedGroups() {
      if (!createdGroupRows.length) {
        createdGroupTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No teaching groups created yet.</td></tr>';
        createdGroupMetaText.textContent = 'Create a group from bucket, then assign faculty here.';
        saveCreatedGroupFacultyBtn.disabled = false;
        return;
      }

      createdGroupTableBody.innerHTML = createdGroupRows.map((row, index) => {
        const displayGroupLabel = `Group ${allocationAlphabet(index + 1)}`;
        const attachedCourseRows = Array.isArray(row.course_rows) ? row.course_rows : [];
        const attachedCourses = Array.isArray(row.course_labels) ? row.course_labels : [];
        const courseHtml = attachedCourseRows.length ?
          attachedCourseRows.map((courseRow) => {
            const label = escapeHtml(String(courseRow.course_label || '-'));
            const batchName = escapeHtml(String(courseRow.batch_name || '-'));
            const semesterTitle = escapeHtml(String(courseRow.semester_title || '-'));
            return `<div class="border rounded px-2 py-1 mb-1 bg-light"><div class="fw-semibold">${label}</div><div class="small text-muted">Batch: ${batchName} | Semester: ${semesterTitle}</div></div>`;
          }).join('') :
          (attachedCourses.length ?
            attachedCourses.map((label) => `<span class="badge bg-light text-dark border me-1 mb-1">${escapeHtml(String(label || '-'))}</span>`).join('') :
            '<span class="text-muted">-</span>');

        return `
          <tr>
            <td>${escapeHtml(row.group_identifier || '-')}</td>
            <td>${escapeHtml(displayGroupLabel)}</td>
            <td>${courseHtml}</td>
            <td>${escapeHtml(String(row.group_students_count || 0))}</td>
            <td>${escapeHtml(String(row.faculty_label || '-'))}</td>
            <td>
              <input
                type="text"
                class="form-control form-control-sm created-group-room-input"
                data-allocation-group-id="${Number(row.allocation_group_id || 0)}"
                value="${escapeHtml(String(row.room_no || ''))}"
                placeholder="e.g. LH-126"
                maxlength="80"
              >
            </td>
            <td>
              <select class="form-select form-select-sm created-group-faculty-select" data-allocation-group-id="${Number(row.allocation_group_id || 0)}">
                ${renderCreatedGroupFacultyOptions(Number(row.faculty_id || 0), row)}
              </select>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-outline-primary btn-sm me-1 open-allot-timeslot-btn"
                data-allocation-group-id="${Number(row.allocation_group_id || 0)}"
                title="Allot Timeslot"
              >
                <i class="fa fa-clock me-1" aria-hidden="true"></i>Allot Timeslot
              </button>
              <button
                type="button"
                class="btn btn-outline-danger btn-sm delete-created-group-btn"
                data-allocation-group-id="${Number(row.allocation_group_id || 0)}"
                title="Delete Group"
              >
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');

      const assignedCount = createdGroupRows.filter((row) => Number(row.faculty_id || 0) > 0).length;
      createdGroupMetaText.textContent = `Created groups: ${createdGroupRows.length} | Faculty assigned: ${assignedCount}`;
      saveCreatedGroupFacultyBtn.disabled = false;
    }

    function renderBucketRows() {
      if (!bucketRows.length) {
        bucketTableBody.innerHTML = '<tr id="groupBucketPlaceholderRow"><td colspan="7" class="text-center text-muted py-4">No course in group bucket.</td></tr>';
        updateBucketControls();
        return;
      }

      bucketTableBody.innerHTML = bucketRows.map((row) => {
        return `
          <tr>
            <td>${escapeHtml(row.course_label || '-')}</td>
            <td>${escapeHtml(row.batch_name || String(row.batch || '-'))}</td>
            <td>${escapeHtml(String(row.semester || '-'))}</td>
            <td>${escapeHtml(String(row.student_count || 0))}${row.student_program_code ? ` <span class="text-muted">(${escapeHtml(row.student_program_code)})</span>` : ''}</td>
            <td>${escapeHtml(row.delivery_type || '-')}</td>
            <td>${escapeHtml(row.offering_dept_name || '-')}</td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-outline-danger btn-sm remove-bucket-row-btn"
                data-curriculum-row-id="${row.curriculum_row_id}"
                title="Remove"
              >
                <i class="fa fa-times"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');

      updateBucketControls();
    }

    function renderRows() {
      const keyword = (searchInput.value || '').toLowerCase().trim();
      const filtered = keyword === '' ? allRows : allRows.filter((row) => {
        const line = [
          row.course_label,
          row.delivery_type,
          row.offering_dept_name,
          row.program_type,
          row.batch_name,
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
            <td>${escapeHtml(row.batch_name || String(row.batch || '-'))}</td>
            <td>${escapeHtml(String(row.semester || '-'))}</td>
            <td>${escapeHtml(row.program_type || '-')}</td>
            <td>${escapeHtml(row.delivery_type || '-')}</td>
            <td>${escapeHtml(row.offering_dept_name || '-')}</td>
          </tr>
        `;
      }).join('');

      updateSelectedCount();
    }

    function setDeptSelection(deptId, deptName) {
      selectedDeptId = Number(deptId || 0);
      selectedDeptName = String(deptName || '').trim();

      if (deptSelect && Number(deptSelect.value || 0) !== selectedDeptId) {
        deptSelect.value = selectedDeptId > 0 ? String(selectedDeptId) : '';
      }
    }

    async function loadCourseList() {
      const deaneryId = Number(deanerySelect.value || 0);
      if (!deaneryId) {
        alert('Please select a deanery first.');
        return;
      }

      if (selectedDeptId <= 0) {
        alert('Please click a department first.');
        return;
      }

      metaText.textContent = `Loading courses for ${selectedDeptName || 'selected department'}...`;

      const params = new URLSearchParams();
      params.set('deanery_id', String(deaneryId));
      params.set('dept_id', String(selectedDeptId));
      if (batchSelect.value) params.set('batch_id', batchSelect.value);
      if (semesterSelect.value) params.set('semester_id', semesterSelect.value);
      if (programTypeSelect.value) params.set('program_type', programTypeSelect.value);
      if (searchInput.value.trim() !== '') params.set('search', searchInput.value.trim());

      try {
        const response = await fetch(`${endpoint}?${params.toString()}`, {
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          }
        });

        const responseText = await response.text();
        let payload = null;
        try {
          payload = JSON.parse(responseText);
        } catch (parseError) {
          const isHtml = responseText.trim().startsWith('<!doctype') || responseText.trim().startsWith('<html');
          if (isHtml) {
            throw new Error('Server returned an HTML error page instead of JSON. Please refresh the page and retry.');
          }
          throw new Error('Invalid response received from server.');
        }

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'Failed to load course list.');
        }

        allRows = Array.isArray(payload.rows) ? payload.rows : [];
        const count = Number(payload.meta?.count || 0);
        metaText.textContent = `Loaded ${count} row(s) for ${selectedDeptName || 'selected department'}.`;
        selectAll.checked = false;
        renderRows();
      } catch (error) {
        allRows = [];
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${escapeHtml(error.message || 'Failed to load courses.')}</td></tr>`;
        metaText.textContent = 'Unable to generate list.';
        updateSelectedCount();
      } finally {
        // keep meta text controlled by success/error blocks
      }
    }

    deptSelect.addEventListener('change', function() {
      const selectedOption = deptSelect.options[deptSelect.selectedIndex];
      const deptId = Number(deptSelect.value || 0);
      const deptTitle = selectedOption ? String(selectedOption.dataset.deptTitle || selectedOption.text || '') : '';
      setDeptSelection(deptId, deptTitle);
      clearSearchInput();
      if (deptId > 0) {
        loadCourseList();
      }
    });

    searchInput.addEventListener('input', function() {
      syncSearchClearButton();
      renderRows();
    });

    if (clearCourseSearchBtn) {
      clearCourseSearchBtn.addEventListener('click', function() {
        clearSearchInput(true);
      });
    }

    [batchSelect, semesterSelect, programTypeSelect].forEach((element) => {
      element.addEventListener('change', function() {
        if (selectedDeptId > 0) {
          loadCourseList();
        }
      });
    });

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

    addSelectedToBucketBtn.addEventListener('click', function() {
      const selectedIds = Array.from(tableBody.querySelectorAll('.group-course-checkbox:checked'))
        .map((checkbox) => Number(checkbox.value || 0))
        .filter((value) => value > 0);

      if (!selectedIds.length) {
        return;
      }

      const rowMap = new Map(allRows.map((row) => [Number(row.curriculum_row_id || 0), normalizeRow(row)]));
      const bucketMap = new Map(bucketRows.map((row) => [Number(row.curriculum_row_id || 0), row]));

      selectedIds.forEach((id) => {
        const row = rowMap.get(id);
        if (row && !bucketMap.has(id)) {
          bucketMap.set(id, row);
        }
      });

      bucketRows = Array.from(bucketMap.values()).sort((a, b) => {
        const aCode = String(a.course_code || '');
        const bCode = String(b.course_code || '');
        return aCode.localeCompare(bCode);
      });

      tableBody.querySelectorAll('.group-course-checkbox:checked').forEach((checkbox) => {
        checkbox.checked = false;
      });
      selectAll.checked = false;
      updateSelectedCount();
      renderBucketRows();
    });

    bucketTableBody.addEventListener('click', function(event) {
      const removeBtn = event.target.closest('.remove-bucket-row-btn');
      if (!removeBtn) return;
      const curriculumRowId = Number(removeBtn.dataset.curriculumRowId || 0);
      if (curriculumRowId <= 0) return;

      bucketRows = bucketRows.filter((row) => Number(row.curriculum_row_id || 0) !== curriculumRowId);
      renderBucketRows();
    });

    clearBucketBtn.addEventListener('click', function() {
      bucketRows = [];
      renderBucketRows();
    });

    continueToGroupBuilderBtn.addEventListener('click', async function() {
      if (!bucketRows.length) {
        return;
      }

      const payload = {
        group_rows: bucketRows.map((row) => ({
          curriculum_row_id: Number(row.curriculum_row_id || 0),
          course_id: Number(row.course_id || 0),
          batch: Number(row.batch || 0),
          semester: Number(row.semester || 0),
          student_program_id: Number(row.student_program_id || 0) > 0 ? Number(row.student_program_id) : null,
          delivery_type: String(row.delivery_type || ''),
          program_type: String(row.program_type || ''),
          offering_dept_id: Number(row.offering_dept_id || 0) > 0 ? Number(row.offering_dept_id) : null,
        })),
      };

      continueToGroupBuilderBtn.disabled = true;
      const originalText = continueToGroupBuilderBtn.textContent;
      continueToGroupBuilderBtn.textContent = 'Creating Group...';

      try {
        const response = await fetch(saveGroupEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to create teaching group.');
        }

        const updatedRows = Number(result.meta?.updated_student_course_rows || 0);
        const createdItems = Number(result.meta?.created_group_items || 0);
        const groupNo = Number(result.meta?.allocation_group || 0);
        alert(`Teaching group created. ${groupNo > 0 ? ('Group ' + groupNo) : ''} | Group rows: ${createdItems} | Student-course rows updated: ${updatedRows}. Assign faculty below.`);
        window.location.reload();
      } catch (error) {
        alert(error.message || 'Unable to create teaching group.');
      } finally {
        continueToGroupBuilderBtn.disabled = false;
        continueToGroupBuilderBtn.textContent = originalText;
      }

    });

    createdGroupTableBody.addEventListener('change', function(event) {
      const select = event.target.closest('.created-group-faculty-select');
      if (select) {
        const groupId = Number(select.dataset.allocationGroupId || 0);
        const facultyId = Number(select.value || 0);
        if (groupId <= 0) return;

        createdGroupRows = createdGroupRows.map((row) => {
          if (Number(row.allocation_group_id || 0) !== groupId) {
            return row;
          }

          const selectedOption = select.options[select.selectedIndex];
          const facultyLabel = facultyId > 0 ?
            String(selectedOption?.text || '').trim() :
            '-';

          return {
            ...row,
            faculty_id: facultyId,
            faculty_label: facultyLabel,
          };
        });

        renderCreatedGroups();
        return;
      }

      const roomInput = event.target.closest('.created-group-room-input');
      if (!roomInput) return;

      const groupId = Number(roomInput.dataset.allocationGroupId || 0);
      if (groupId <= 0) return;

      createdGroupRows = createdGroupRows.map((row) => {
        if (Number(row.allocation_group_id || 0) !== groupId) {
          return row;
        }

        return {
          ...row,
          room_no: String(roomInput.value || '').trim(),
        };
      });
    });

    createdGroupTableBody.addEventListener('click', async function(event) {
      const allotBtn = event.target.closest('.open-allot-timeslot-btn');
      if (allotBtn) {
        const groupId = Number(allotBtn.dataset.allocationGroupId || 0);
        openAllotTimeslotModal(groupId);
        return;
      }

      const deleteBtn = event.target.closest('.delete-created-group-btn');
      if (!deleteBtn) return;

      const groupId = Number(deleteBtn.dataset.allocationGroupId || 0);
      if (groupId <= 0) return;

      if (!confirm(`Delete Group ${groupId}? This will remove all courses under this group.`)) {
        return;
      }

      deleteBtn.disabled = true;

      try {
        const response = await fetch(deleteGroupEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            allocation_group_id: groupId,
          }),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to delete teaching group.');
        }

        createdGroupRows = createdGroupRows.filter((row) => Number(row.allocation_group_id || 0) !== groupId);
        renderCreatedGroups();
      } catch (error) {
        alert(error.message || 'Unable to delete teaching group.');
      } finally {
        deleteBtn.disabled = false;
      }
    });

    submitAllotTimeslotBtn.addEventListener('click', async function() {
      const groupId = Number(selectedAllotGroupId || 0);
      const day = String(allotTimeslotDay.value || '').trim();
      const hourNumber = Number(allotTimeslotHour.value || 0);

      if (groupId <= 0) {
        alert('Please select a valid group.');
        return;
      }

      if (!day) {
        alert('Please select day.');
        return;
      }

      if (hourNumber <= 0) {
        alert('Please enter a valid hour number.');
        return;
      }

      const groupRow = findCreatedGroupById(groupId);
      if (!groupRow) {
        alert('Created group row not found. Please refresh and try again.');
        return;
      }

      const anchorBatchId = Number(groupRow.anchor_batch_id || 0);
      const anchorSemesterId = Number(groupRow.anchor_semester_id || 0);
      const anchorProgramType = String(groupRow.anchor_program_type || 'UG').toUpperCase() === 'PG' ? 'PG' : 'UG';
      const firstCourseId = Number((Array.isArray(groupRow.course_rows) && groupRow.course_rows.length > 0 ? groupRow.course_rows[0]?.course_id : 0) || 0);

      if (anchorBatchId <= 0 || anchorSemesterId <= 0 || firstCourseId <= 0) {
        alert('Unable to derive batch/semester/course for this group. Please refresh and try again.');
        return;
      }

      const saveUrl = allotTimeslotEndpointTemplate
        .replace('BATCH_ID', String(anchorBatchId))
        .replace('SEMESTER_ID', String(anchorSemesterId));

      const payload = {
        batch_id: anchorBatchId,
        semester_id: anchorSemesterId,
        program_type: anchorProgramType,
        shift: 'common',
        timetable: [{
          hour_number: hourNumber,
          day_of_week: day,
          subject_id: firstCourseId,
          teaching_assignment_id: null,
          teaching_group_id: groupId,
          teacher_id: Number(groupRow.faculty_id || 0) > 0 ? Number(groupRow.faculty_id) : null,
          slot_active: 1,
        }],
      };

      submitAllotTimeslotBtn.disabled = true;
      const originalText = submitAllotTimeslotBtn.textContent;
      submitAllotTimeslotBtn.textContent = 'Allotting...';

      try {
        const response = await fetch(saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to allot timeslot.');
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('allotTimeslotModal'));
        if (modal) modal.hide();

        const counts = result.meta || {};
        const created = Number(counts.created || 0);
        const updated = Number(counts.updated || 0);
        const restored = Number(counts.restored || 0);
        const archived = Number(counts.archived || 0);
        const affectedCount = Number(counts.affected_slots_count || 0);
        alert([
          result.message || 'Timeslot allotted successfully.',
          `Created: ${created}, Updated: ${updated}, Restored: ${restored}, Archived: ${archived}`,
          `Affected slots: ${affectedCount}`,
          '',
          summarizeAffectedSlots(counts),
        ].join('\n'));
      } catch (error) {
        alert(error.message || 'Unable to allot timeslot.');
      } finally {
        submitAllotTimeslotBtn.disabled = false;
        submitAllotTimeslotBtn.textContent = originalText;
      }
    });

    saveCreatedGroupFacultyBtn.addEventListener('click', async function() {
      if (!createdGroupRows.length) {
        alert('No created teaching groups found to save faculty.');
        return;
      }

      const missingFaculty = createdGroupRows.some((row) => Number(row.faculty_id || 0) <= 0);
      if (missingFaculty) {
        alert('Please select faculty for all created group rows before saving.');
        return;
      }

      const payload = {
        groups: createdGroupRows.map((row) => ({
          allocation_group_id: Number(row.allocation_group_id || 0),
          faculty_id: Number(row.faculty_id || 0),
          room_no: String(row.room_no || '').trim(),
        })),
      };

      saveCreatedGroupFacultyBtn.disabled = true;
      const originalText = saveCreatedGroupFacultyBtn.textContent;
      saveCreatedGroupFacultyBtn.textContent = 'Saving...';

      try {
        const response = await fetch(assignFacultyEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Unable to save faculty mapping.');
        }

        const updated = Number(result.meta?.updated || 0);
        const assignmentSynced = Number(result.meta?.assignment_synced || 0);
        alert(`Faculty mapping saved successfully. Updated group rows: ${updated} | Timetable assignment sync: ${assignmentSynced}`);
        window.location.reload();
      } catch (error) {
        alert(error.message || 'Unable to save faculty mapping.');
      } finally {
        saveCreatedGroupFacultyBtn.disabled = false;
        saveCreatedGroupFacultyBtn.textContent = originalText;
      }
    });

    if (deptSelect && deptSelect.options.length > 1) {
      const firstRealOption = deptSelect.options[1];
      deptSelect.value = firstRealOption.value;
      setDeptSelection(firstRealOption.value, firstRealOption.dataset.deptTitle || firstRealOption.text || '');
    }

    syncSearchClearButton();
    renderBucketRows();
    renderCreatedGroups();
    loadCourseList();
  })();
</script>

@include('includes.footer')
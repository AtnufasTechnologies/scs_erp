@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.index', $module ? ['module' => $module] : []) }}">Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exam Calendar</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between gap-3 align-items-end">
          <div>
            <h5 class="mb-1">Exam Calendar</h5>
            <p class="text-muted mb-0">Google-style scheduler for exam timetable. Click a slot to add, click an event to edit.</p>
          </div>
          <div class="d-flex flex-wrap gap-2 align-items-end">
            <div>
              <label class="form-label mb-1">Filter Exam</label>
              <select id="calendarExamFilter" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" data-start-date="{{ $exam->start_date }}" data-end-date="{{ $exam->end_date }}" {{ (int) ($initialExamId ?? 0) === (int) $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="form-label mb-1">Schedule Date</label>
              <input type="date" id="selectedScheduleDate" class="form-control">
            </div>
            <button type="button" class="btn btn-outline-primary" id="btnOpenScheduleDate">
              <i class="fa fa-calendar-day me-1"></i>Open Selected Date
            </button>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
          <div class="row">
            <div class="col-lg-6">
              <h6 class="mb-0 fw-bold"><i class="fa fa-file-excel me-2 text-success"></i>Excel Import</h6>
            </div>
            <div class="col-lg-6">
              <a href="{{ route('coe.exams.calendar.template') }}" class="btn btn-link w-100">
                <i class="fa fa-download me-1"></i>Download Sample Template
              </a>
            </div>
          </div>




        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('coe.exams.calendar.import') }}" enctype="multipart/form-data" class="row g-3 align-items-end">
            @csrf
            <div class="col-lg-4">
              <label class="form-label">Exam <span class="text-danger">*</span></label>
              <select name="exam_id" class="dselect-example" required>
                <option value="" selected disabled>Select exam</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ (int) ($initialExamId ?? 0) === (int) $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Excel File <span class="text-danger">*</span></label>
              <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <div class="col-lg-4">
              <button type="submit" class="btn btn-outline-success w-100">Import Timetable</button>
            </div>


          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div id="examCalendar"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addCalendarEntryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Exam Slot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addCalendarEntryForm" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Exam <span class="text-danger">*</span></label>
            <select id="addEntryExamId" class="form-select" required>
              <option value="" selected disabled>Select exam</option>
              @foreach($exams as $exam)
              <option value="{{ $exam->id }}">{{ $exam->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Courses <span class="text-danger">*</span></label>
            <select id="addEntryCourseIds" class="select-multiple" multiple>
              <option value="">Select</option>
              @foreach($courses as $course)
              <option value="{{ $course->id }}" data-course-label="{{ $course->course_code }} - {{ $course->course_title }}">
                {{ $course->course_code }} - {{ $course->course_title }} ({{ (int) ($course->student_count ?? 0) }} students)
              </option>
              @endforeach
            </select>

          </div>
          <input type="hidden" id="addEntryTitle" required>
          <div class="col-md-3">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" id="addEntryDate" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="time" id="addEntryStart" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Duration (Hours)</label>
            <input type="number" id="addEntryDurationHours" class="form-control" min="0.5" max="8" step="0.5" value="2">
          </div>
          <div class="col-md-3">
            <label class="form-label">Calculated End Time</label>
            <input type="hidden" id="addEntryEnd" required>
            <div class="form-control bg-light" id="addEntryEndPreview">--:--</div>
            <small class="text-muted">Auto-calculated </small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnSaveAddEntry">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editCalendarEntryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Exam Slot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editCalendarEntryForm" class="row g-3">
          <input type="hidden" id="editEntryId">
          <div class="col-md-6">
            <label class="form-label">Exam <span class="text-danger">*</span></label>
            <select id="editEntryExamId" class="form-select" required>
              <option value="" selected disabled>Select exam</option>
              @foreach($exams as $exam)
              <option value="{{ $exam->id }}">{{ $exam->name }}</option>
              @endforeach
            </select>
          </div>
          <input type="hidden" id="editEntryTitle" required>
          <div class="col-md-3">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" id="editEntryDate" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="time" id="editEntryStart" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Duration (Hours)</label>
            <input type="number" id="editEntryDurationHours" class="form-control" min="0.5" max="8" step="0.5" value="2">
          </div>
          <div class="col-md-3">
            <label class="form-label">Calculated End Time</label>
            <input type="hidden" id="editEntryEnd" required>
            <div class="form-control bg-light" id="editEntryEndPreview">--:--</div>
            <small class="text-muted">Auto-calculated </small>
          </div>
        </form>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger" id="btnDeleteEntry">Delete</button>
        <div class="ms-auto">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btnSaveEditEntry">Save</button>
        </div>
      </div>
    </div>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
  #examCalendar .fc-day-header-label {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    letter-spacing: 0.2px;
    opacity: 1 !important;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.35);
  }

  #examCalendar .fc .fc-col-header-cell {
    background: #1f2f46 !important;
    border-color: #2d3f58 !important;
  }
</style>

<script id="examCourseIdsByExamJson" type="application/json">
  @json($examCourseIdsByExam ?? [])
</script>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addModal = new bootstrap.Modal(document.getElementById('addCalendarEntryModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editCalendarEntryModal'));

    const examFilter = document.getElementById('calendarExamFilter');
    const selectedScheduleDate = document.getElementById('selectedScheduleDate');
    const btnOpenScheduleDate = document.getElementById('btnOpenScheduleDate');

    const addEntryExamId = document.getElementById('addEntryExamId');
    const addEntryCourseIds = document.getElementById('addEntryCourseIds');
    const addEntryTitle = document.getElementById('addEntryTitle');
    const addEntryDate = document.getElementById('addEntryDate');
    const addEntryStart = document.getElementById('addEntryStart');
    const addEntryDurationHours = document.getElementById('addEntryDurationHours');
    const addEntryEnd = document.getElementById('addEntryEnd');
    const addEntryEndPreview = document.getElementById('addEntryEndPreview');
    const btnSaveAddEntry = document.getElementById('btnSaveAddEntry');

    const editEntryId = document.getElementById('editEntryId');
    const editEntryExamId = document.getElementById('editEntryExamId');
    const editEntryTitle = document.getElementById('editEntryTitle');
    const editEntryDate = document.getElementById('editEntryDate');
    const editEntryStart = document.getElementById('editEntryStart');
    const editEntryDurationHours = document.getElementById('editEntryDurationHours');
    const editEntryEnd = document.getElementById('editEntryEnd');
    const editEntryEndPreview = document.getElementById('editEntryEndPreview');
    const btnSaveEditEntry = document.getElementById('btnSaveEditEntry');
    const btnDeleteEntry = document.getElementById('btnDeleteEntry');
    const examCourseIdsByExamEl = document.getElementById('examCourseIdsByExamJson');
    const examCourseIdsByExam = examCourseIdsByExamEl ? JSON.parse(examCourseIdsByExamEl.textContent || '{}') : {};
    const baseCourseOptions = Array.from(addEntryCourseIds.options || [])
      .filter(option => !!option.value)
      .map(option => ({
        value: String(option.value),
        text: option.textContent,
        courseLabel: option.dataset?.courseLabel || option.textContent,
      }));

    const csrfToken = '{{ csrf_token() }}';
    const eventsUrl = "{{ route('coe.exams.calendar.events') }}";
    const initialExamId = Number("{{ (int) ($initialExamId ?? 0) }}") || 0;
    const isExamLocked = initialExamId > 0;

    let currentAddEntryNotes = null;
    let currentEditEntryNotes = null;
    let currentEditEntryCourseId = null;
    const durationStorageKey = 'coe_exam_calendar_duration_hours';

    function persistExamDurationHours() {
      try {
        const value = Number(addEntryDurationHours?.value || editEntryDurationHours?.value || 2);
        if (Number.isFinite(value) && value > 0) {
          window.localStorage.setItem(durationStorageKey, String(value));
        }
      } catch (e) {
        // Ignore storage failures (private mode / quota / blocked storage).
      }
    }

    function resolvePreferredDurationHours() {
      try {
        const stored = Number(window.localStorage.getItem(durationStorageKey));
        if (Number.isFinite(stored) && stored > 0) {
          return stored;
        }
      } catch (e) {
        // Ignore storage read failures.
      }

      return 2;
    }

    function getSelectedCourseValues() {
      if (!addEntryCourseIds) {
        return [];
      }

      if (addEntryCourseIds.tomselect && typeof addEntryCourseIds.tomselect.getValue === 'function') {
        const raw = addEntryCourseIds.tomselect.getValue();
        const values = Array.isArray(raw) ? raw : String(raw || '').split(',');
        return values.map(value => String(value).trim()).filter(value => value !== '');
      }

      if (window.jQuery && typeof window.jQuery === 'function') {
        const jqValues = window.jQuery(addEntryCourseIds).val();
        if (Array.isArray(jqValues)) {
          return jqValues.map(value => String(value).trim()).filter(value => value !== '');
        }
      }

      return Array.from(addEntryCourseIds.selectedOptions || [])
        .map(option => String(option.value).trim())
        .filter(value => value !== '');
    }

    function getScopedCourseIdsForExam(examIdValue) {
      const key = String(examIdValue || '').trim();
      if (key === '') {
        return [];
      }

      const direct = examCourseIdsByExam[key];
      if (Array.isArray(direct)) {
        return direct.map(id => String(id));
      }

      const numericKey = String(Number(key) || '');
      const numeric = examCourseIdsByExam[numericKey];
      if (Array.isArray(numeric)) {
        return numeric.map(id => String(id));
      }

      return [];
    }

    function applyCourseFilterByExam() {
      const scopedCourseIds = getScopedCourseIdsForExam(addEntryExamId.value);
      const hasScopedFilter = scopedCourseIds.length > 0;
      const allowedSet = new Set(scopedCourseIds);
      const selectedBefore = getSelectedCourseValues();

      const filtered = hasScopedFilter ?
        baseCourseOptions.filter(option => allowedSet.has(String(option.value))) :
        baseCourseOptions.slice();

      if (addEntryCourseIds.tomselect && typeof addEntryCourseIds.tomselect.clearOptions === 'function') {
        const ts = addEntryCourseIds.tomselect;
        ts.clear(true);
        ts.clearOptions();
        ts.addOptions(filtered.map(option => ({
          value: option.value,
          text: option.text,
          courseLabel: option.courseLabel,
        })));
        ts.refreshOptions(false);

        const stillValid = selectedBefore.filter(value => filtered.some(option => option.value === value));
        if (stillValid.length > 0) {
          ts.setValue(stillValid, true);
        }
        return;
      }

      addEntryCourseIds.innerHTML = '';
      filtered.forEach(option => {
        const el = document.createElement('option');
        el.value = option.value;
        el.textContent = option.text;
        el.setAttribute('data-course-label', option.courseLabel);
        if (selectedBefore.includes(option.value)) {
          el.selected = true;
        }
        addEntryCourseIds.appendChild(el);
      });
    }

    function formatTimeFromMinutes(totalMinutes) {
      const safeMinutes = Math.max(0, Math.min(1439, totalMinutes));
      const hours = Math.floor(safeMinutes / 60).toString().padStart(2, '0');
      const minutes = (safeMinutes % 60).toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    }

    function parseTimeToMinutes(timeString) {
      if (!timeString || !timeString.includes(':')) {
        return null;
      }
      const [h, m] = timeString.split(':').map(Number);
      if (Number.isNaN(h) || Number.isNaN(m)) {
        return null;
      }
      return (h * 60) + m;
    }

    function applyEndTimeFromDuration(startInput, durationInput, endInput, previewInput) {
      const startMinutes = parseTimeToMinutes(startInput.value);
      const durationHours = Number(durationInput.value);
      if (startMinutes === null || !Number.isFinite(durationHours) || durationHours <= 0) {
        endInput.value = '';
        previewInput.textContent = '--:--';
        return;
      }

      const durationMinutes = Math.round(durationHours * 60);
      endInput.value = formatTimeFromMinutes(startMinutes + durationMinutes);
      previewInput.textContent = endInput.value;
    }

    function syncTitleFromExam(examSelect, titleInput) {
      const selectedExam = examSelect.options[examSelect.selectedIndex];
      const examLabel = selectedExam && examSelect.value ? selectedExam.textContent.trim() : '';
      titleInput.value = examLabel ? `${examLabel} Slot` : 'Exam Slot';
    }

    function formatDateLocal(dateObj) {
      const y = dateObj.getFullYear();
      const m = String(dateObj.getMonth() + 1).padStart(2, '0');
      const d = String(dateObj.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }

    function formatTimeLocal(dateObj) {
      const h = String(dateObj.getHours()).padStart(2, '0');
      const m = String(dateObj.getMinutes()).padStart(2, '0');
      return `${h}:${m}`;
    }

    function swalConfirm(title, text, icon = 'question') {
      if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
        return Swal.fire({
          title,
          text,
          icon,
          showCancelButton: true,
          confirmButtonText: 'Yes',
          cancelButtonText: 'Cancel',
          reverseButtons: true
        });
      }

      return Promise.resolve({
        isConfirmed: confirm(text)
      });
    }

    function swalToast(icon, title) {
      if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon,
          title,
          showConfirmButton: false,
          timer: 1800
        });
      }
    }

    function setScheduleDateConstraintsFromExam() {
      const selected = examFilter.options[examFilter.selectedIndex];
      const startDate = selected?.dataset?.startDate || '';
      const endDate = selected?.dataset?.endDate || '';

      selectedScheduleDate.min = startDate || '';
      selectedScheduleDate.max = endDate || '';

      if (startDate) {
        selectedScheduleDate.value = startDate;
      } else if (!selectedScheduleDate.value) {
        selectedScheduleDate.value = formatDateLocal(new Date());
      }
    }

    function applyLockedExamContext() {
      if (!isExamLocked) {
        return;
      }

      examFilter.value = String(initialExamId);
      examFilter.setAttribute('disabled', 'disabled');

      addEntryExamId.value = String(initialExamId);
      addEntryExamId.setAttribute('disabled', 'disabled');

      editEntryExamId.setAttribute('disabled', 'disabled');
    }

    function openSelectedDateForScheduling() {
      if (!selectedScheduleDate.value) {
        swalToast('warning', 'Select a date to open');
        return;
      }

      calendar.changeView('timeGridDay', selectedScheduleDate.value);
      swalToast('success', 'Opened selected date for scheduling');
    }

    function openCreateModal(prefill = {}) {
      addEntryExamId.value = isExamLocked ? String(initialExamId) : (prefill.exam_id || examFilter.value || '');
      applyCourseFilterByExam();

      if (addEntryCourseIds.tomselect && typeof addEntryCourseIds.tomselect.clear === 'function') {
        addEntryCourseIds.tomselect.clear(true);
      } else {
        Array.from(addEntryCourseIds.options || []).forEach(option => {
          option.selected = false;
        });
      }
      addEntryDate.value = prefill.date || '';
      addEntryStart.value = prefill.start || '';
      addEntryDurationHours.value = resolvePreferredDurationHours();
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
      syncTitleFromExam(addEntryExamId, addEntryTitle);
      currentAddEntryNotes = null;
      addModal.show();
    }

    function openEditModal(eventObj) {
      editEntryId.value = eventObj.id;
      editEntryExamId.value = eventObj.extendedProps.exam_id || '';
      currentEditEntryCourseId = eventObj.extendedProps.course_id || null;
      currentEditEntryNotes = eventObj.extendedProps.notes || null;

      editEntryTitle.value = eventObj.title || '';
      editEntryDate.value = eventObj.startStr.slice(0, 10);
      editEntryStart.value = eventObj.start ? eventObj.start.toTimeString().slice(0, 5) : '';

      const currentEnd = eventObj.end ? eventObj.end.toTimeString().slice(0, 5) : '';
      editEntryEnd.value = currentEnd;
      editEntryEndPreview.textContent = currentEnd || '--:--';

      const startMinutes = parseTimeToMinutes(editEntryStart.value);
      const endMinutes = parseTimeToMinutes(editEntryEnd.value);
      if (startMinutes !== null && endMinutes !== null && endMinutes > startMinutes) {
        editEntryDurationHours.value = ((endMinutes - startMinutes) / 60).toFixed(2).replace(/\.00$/, '');
      } else {
        editEntryDurationHours.value = resolvePreferredDurationHours();
      }

      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
      if (!editEntryTitle.value) {
        syncTitleFromExam(editEntryExamId, editEntryTitle);
      }

      editModal.show();
    }

    function getAddPayload(courseOption = null) {
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
      if (courseOption) {
        addEntryTitle.value = (courseOption.dataset?.courseLabel || courseOption.textContent || '').trim();
      } else if (!addEntryTitle.value) {
        syncTitleFromExam(addEntryExamId, addEntryTitle);
      }

      return {
        exam_id: addEntryExamId.value,
        course_id: courseOption ? courseOption.value : null,
        title: addEntryTitle.value,
        exam_date: addEntryDate.value,
        start_time: addEntryStart.value,
        end_time: addEntryEnd.value,
        notes: currentAddEntryNotes,
      };
    }

    function getEditPayload() {
      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
      if (!editEntryTitle.value) {
        syncTitleFromExam(editEntryExamId, editEntryTitle);
      }

      return {
        exam_id: editEntryExamId.value,
        course_id: currentEditEntryCourseId,
        title: editEntryTitle.value,
        exam_date: editEntryDate.value,
        start_time: editEntryStart.value,
        end_time: editEntryEnd.value,
        notes: currentEditEntryNotes,
      };
    }

    async function saveAddEntry() {
      const selectedCourses = Array.from(addEntryCourseIds.selectedOptions || []).filter(option => option.value);
      const payloads = selectedCourses.length > 0 ?
        selectedCourses.map(option => getAddPayload(option)) : [getAddPayload(null)];

      for (const payload of payloads) {
        const response = await fetch(`{{ route('coe.exams.calendar.store') }}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify(payload)
        });

        if (!response.ok) {
          throw new Error('Failed to save one or more calendar entries');
        }
      }

      addModal.hide();
      calendar.refetchEvents();
      swalToast('success', payloads.length > 1 ? 'Exam slots created' : 'Exam slot created');
    }

    async function saveEditEntry() {
      const id = editEntryId.value;
      if (!id) {
        throw new Error('Invalid slot for update');
      }

      const payload = getEditPayload();
      const response = await fetch(`{{ url('erp/coe/exams/calendar/events') }}/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new Error('Failed to save calendar entry');
      }

      editModal.hide();
      calendar.refetchEvents();
      swalToast('success', 'Exam slot updated');
    }

    async function updateEventByDrag(eventObj) {
      const payload = {
        exam_id: eventObj.extendedProps.exam_id,
        course_id: eventObj.extendedProps.course_id || null,
        title: eventObj.title,
        exam_date: formatDateLocal(eventObj.start),
        start_time: formatTimeLocal(eventObj.start),
        end_time: formatTimeLocal(eventObj.end || eventObj.start),
        notes: eventObj.extendedProps.notes || null,
      };

      const response = await fetch(`{{ url('erp/coe/exams/calendar/events') }}/${eventObj.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new Error('Failed to update slot schedule');
      }
    }

    async function deleteEntry() {
      const id = editEntryId.value;
      if (!id) {
        return;
      }

      const result = await swalConfirm('Delete Slot', 'Delete this timetable slot?', 'warning');
      if (!result.isConfirmed) {
        return;
      }

      const response = await fetch(`{{ url('erp/coe/exams/calendar/events') }}/${id}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });

      if (!response.ok) {
        throw new Error('Failed to delete calendar entry');
      }

      editModal.hide();
      calendar.refetchEvents();
      swalToast('success', 'Exam slot deleted');
    }

    btnSaveAddEntry.addEventListener('click', async function() {
      try {
        await saveAddEntry();
      } catch (e) {
        swalToast('error', e.message || 'Failed to save');
      }
    });

    btnSaveEditEntry.addEventListener('click', async function() {
      try {
        await saveEditEntry();
      } catch (e) {
        swalToast('error', e.message || 'Failed to save');
      }
    });

    btnDeleteEntry.addEventListener('click', async function() {
      try {
        await deleteEntry();
      } catch (e) {
        swalToast('error', e.message || 'Failed to delete');
      }
    });

    btnOpenScheduleDate.addEventListener('click', function() {
      openSelectedDateForScheduling();
    });

    addEntryExamId.addEventListener('change', function() {
      applyCourseFilterByExam();
      addEntryDurationHours.value = resolvePreferredDurationHours();
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
      syncTitleFromExam(addEntryExamId, addEntryTitle);
    });

    addEntryStart.addEventListener('change', function() {
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
    });

    addEntryDurationHours.addEventListener('input', function() {
      persistExamDurationHours();
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
    });

    addEntryDurationHours.addEventListener('change', function() {
      persistExamDurationHours();
      applyEndTimeFromDuration(addEntryStart, addEntryDurationHours, addEntryEnd, addEntryEndPreview);
    });

    editEntryExamId.addEventListener('change', function() {
      editEntryDurationHours.value = resolvePreferredDurationHours();
      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
      syncTitleFromExam(editEntryExamId, editEntryTitle);
    });

    editEntryStart.addEventListener('change', function() {
      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
    });

    editEntryDurationHours.addEventListener('input', function() {
      persistExamDurationHours();
      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
    });

    editEntryDurationHours.addEventListener('change', function() {
      persistExamDurationHours();
      applyEndTimeFromDuration(editEntryStart, editEntryDurationHours, editEntryEnd, editEntryEndPreview);
    });

    applyLockedExamContext();
    setScheduleDateConstraintsFromExam();
    applyCourseFilterByExam();

    const initialCalendarDate = selectedScheduleDate.value || formatDateLocal(new Date());

    const calendarEl = document.getElementById('examCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'timeGridWeek',
      initialDate: initialCalendarDate,
      dayHeaderContent: function(arg) {
        return {
          html: '<span class="fc-day-header-label">' + arg.text + '</span>'
        };
      },
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      slotMinTime: '07:00:00',
      slotMaxTime: '22:00:00',
      editable: true,
      eventStartEditable: true,
      eventDurationEditable: true,
      selectable: true,
      events: function(fetchInfo, successCallback, failureCallback) {
        const params = new URLSearchParams({
          start: fetchInfo.startStr,
          end: fetchInfo.endStr,
        });

        if (examFilter.value) {
          params.set('exam_id', examFilter.value);
        }

        fetch(`${eventsUrl}?${params.toString()}`)
          .then(res => res.json())
          .then(data => successCallback(data))
          .catch(() => failureCallback());
      },
      select: function(info) {
        if (info.view.type === 'dayGridMonth' || info.allDay) {
          selectedScheduleDate.value = info.startStr.slice(0, 10);
          calendar.changeView('timeGridDay', selectedScheduleDate.value);
          calendar.unselect();
          return;
        }

        openCreateModal({
          date: info.startStr.slice(0, 10),
          start: info.startStr.slice(11, 16),
          exam_id: examFilter.value || ''
        });
      },
      dateClick: function(info) {
        selectedScheduleDate.value = info.dateStr.slice(0, 10);
        if (info.view.type === 'dayGridMonth') {
          calendar.changeView('timeGridDay', selectedScheduleDate.value);
        }
      },
      eventClick: function(info) {
        openEditModal(info.event);
      },
      eventDrop: async function(info) {
        try {
          await updateEventByDrag(info.event);
          swalToast('success', 'Exam slot moved');
        } catch (e) {
          info.revert();
          swalToast('error', e.message || 'Failed to move slot');
        }
      },
      eventResize: async function(info) {
        try {
          await updateEventByDrag(info.event);
          swalToast('success', 'Exam slot duration updated');
        } catch (e) {
          info.revert();
          swalToast('error', e.message || 'Failed to resize slot');
        }
      }
    });

    examFilter.addEventListener('change', function() {
      setScheduleDateConstraintsFromExam();
      calendar.refetchEvents();
    });

    calendar.render();

    if (isExamLocked && selectedScheduleDate.value) {
      calendar.changeView('timeGridDay', selectedScheduleDate.value);
    }
  });
</script>

@include('includes.footer')
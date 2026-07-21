<?php

use App\Http\Controllers\StaticController;
use App\Models\AcademicDepartment;
use App\Models\ShiftMaster;
use App\Models\Subject;
use App\Models\Weekday;

$days = Weekday::all();

?>
@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <style>
    .custom-navbar {
      background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
      box-shadow: 0 4px 16px #5740b433;
    }

    .custom-card {
      border-radius: 1rem;
      box-shadow: 0 2px 12px #5740b433;
      border: none;
      background: #fff;
    }

    .custom-table th,
    .custom-table td {
      border: 1px solid #eee;
      padding: 0.75rem;
      text-align: center;
      vertical-align: middle;
      transition: background 0.2s;
    }

    .custom-table th {
      background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
      color: #5740b4;
      font-weight: 600;
      letter-spacing: 0.05em;
    }

    .custom-table tr:hover td {
      background: #f7f7fa;
    }

    .add-slot-btn {
      transition: background 0.2s, color 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px #5740b433;
    }

    .add-slot-btn:hover {
      background: #5740b4;
      color: #fff;
      box-shadow: 0 4px 16px #5740b433;
    }

    .slot-info .badge {
      font-size: 0.95em;
      padding: 0.5em 1em;
      border-radius: 0.5em;
      margin-top: 0.25em;
      background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
      color: #fff;
      box-shadow: 0 2px 8px #5740b433;
    }

    .modal-content {
      border-radius: 1rem;
      box-shadow: 0 4px 24px #5740b433;
    }

    .modal-header {
      background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
    }

    .modal-title {
      color: #5740b4;
      font-weight: 700;
      letter-spacing: 0.03em;
    }

    .btn-primary {
      background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
      border: none;
      box-shadow: 0 2px 8px #5740b433;
      font-weight: 600;
      letter-spacing: 0.03em;
    }

    .btn-primary:hover {
      background: #8931f6;
      box-shadow: 0 4px 16px #5740b433;
    }

    .btn-success {
      background: linear-gradient(90deg, #3bb54a 0%, #6be585 100%);
      border: none;
      font-weight: 600;
      letter-spacing: 0.03em;
      color: #fff;
      box-shadow: 0 2px 8px #3bb54a33;
    }

    .btn-success:hover {
      background: #3bb54a;
      box-shadow: 0 4px 16px #3bb54a33;
    }

    .form-select,
    .form-label {
      font-size: 1.05em;
      font-weight: 500;
      letter-spacing: 0.02em;
    }

    .form-select {
      border-radius: 0.5em;
      box-shadow: 0 2px 8px #5740b433;
    }

    .card-title {
      font-size: 1.25em;
      font-weight: 700;
      letter-spacing: 0.04em;
      color: #5740b4;
    }

    .navbar-brand img {
      filter: drop-shadow(0 2px 8px #5740b433);
    }

    .slot-entry {
      border-radius: 0.5rem;
      border: 1px solid #d9d2ff;
      background: #faf8ff;
    }

    .slot-chip {
      display: inline-block;
      padding: 0.2rem 0.5rem;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 600;
      margin-right: 0.25rem;
      margin-top: 0.2rem;
    }

    .slot-chip-delivery {
      background: #efe7ff;
      color: #4c2f9d;
    }

    .slot-chip-group {
      background: #e7fff4;
      color: #1d7f52;
    }

    @media (max-width: 991.98px) {
      .custom-navbar .container-fluid {
        align-items: flex-start;
      }

      .custom-navbar .navbar-brand {
        align-items: flex-start !important;
        max-width: 100%;
        margin-right: 0;
      }

      .custom-navbar .navbar-brand img {
        max-height: 40px !important;
      }

      .custom-navbar .navbar-brand span {
        white-space: normal;
        line-height: 1.25;
        font-size: 0.95rem;
        word-break: break-word;
      }

      .card-title {
        font-size: 1.1rem;
      }

      .timetable-filter-row>[class*="col-"] {
        width: 100%;
      }

      .custom-table th,
      .custom-table td {
        padding: 0.55rem;
        font-size: 0.86rem;
      }

      .slot-entry {
        font-size: 11px;
      }

      .slot-entry .btn {
        padding: 0.2rem 0.35rem;
        font-size: 0.72rem;
      }

      .modal-dialog {
        margin: 0.75rem;
      }
    }

    @media (max-width: 575.98px) {
      .custom-navbar .navbar-brand img {
        max-height: 34px !important;
      }

      .custom-navbar .navbar-brand span {
        font-size: 0.84rem;
      }

      .custom-table th,
      .custom-table td {
        padding: 0.42rem;
        font-size: 0.78rem;
      }

      .period-cell {
        min-width: 130px;
      }

      .slot-chip {
        font-size: 10px;
        padding: 0.15rem 0.4rem;
      }
    }
  </style>
  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / TimeTable Management</span>
        </a>
        <div class="d-flex">
          @if(StaticController::fetchUserRole() == 'dept-admin-erp')
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #5740b433;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
          @else
          <a href="{{ url('erp/admin/master/view-subject?id='.$data->id.'/slug='.$data->slug) }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #5740b433;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
          @endif
        </div>
    </nav>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card custom-card mb-4">
            <div class="card-body">
              <h5 class="card-title text-dark mb-3">Select Batch & Semester</h5>
              <form id="timetableSelectForm">
                <div class="row g-3 align-items-end timetable-filter-row">
                  <div class="col-md-3">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" class="form-select" id="batchSelect" style="border-radius:0.5em;">
                      @foreach ($batches as $batch)
                      <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select" id="semesterSelect" style="border-radius:0.5em;">
                      @foreach ($semesterMasters as $sem)
                      <option value="{{ $sem->id }}">{{ $sem->title }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Program Type</label>
                    <select name="program_type" class="form-select" id="programTypeSelect" style="border-radius:0.5em;">
                      <option value="UG" selected>UG</option>
                      <option value="PG">PG</option>
                    </select>
                  </div>
                  @if($subjectUsesShifts)
                  <div class="col-md-2">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-select" id="shiftSelect" style="border-radius:0.5em;">
                      @foreach ($shiftOptions as $shiftOption)
                      <option value="{{ $shiftOption->slug }}" data-shift-id="{{ $shiftOption->id }}" {{ $shiftOption->slug === 'common' ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-primary w-100" id="generateTimetableBtn" onclick="loadTimetable(); return false;" style="border-radius:0.5em;">
                      <i class="fa fa-table"></i> Generate Timetable
                    </button>
                  </div>
                  @else
                  <div class="col-md-4">
                    <button type="button" class="btn btn-primary w-100" id="generateTimetableBtn" onclick="loadTimetable(); return false;" style="border-radius:0.5em;">
                      <i class="fa fa-table"></i> Generate Timetable
                    </button>
                  </div>
                  @endif
                </div>
              </form>
            </div>
          </div>
          <!-- <div class="card custom-card mb-4">
            <div class="card-body">
              <h5 class="card-title text-dark mb-3">Quick Slot Entry</h5>
              <div class="row g-3 align-items-end">
                <div class="col-md-2">
                  <label class="form-label">Day</label>

                  <select class="form-select" id="quickDaySelect" style="border-radius:0.5em;">
                    @foreach ($days as $day)
                    <option value="{{$day->title}}">{{$day->title}}</option>
                    @endforeach

                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Shift</label>
                  <select class="form-select" id="quickShiftSelect" style="border-radius:0.5em;">
                    <option value="">--Select</option>
                    @foreach ($shiftOptions as $shiftOption)
                    <option value="{{ $shiftOption->slug }}" data-shift-id="{{ $shiftOption->id }}" {{ $shiftOption->slug === 'common' ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Hour</label>
                  <select class="form-select" id="quickHourSelect" style="border-radius:0.5em;">
                    <option value="">Select Shift First</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Course / Delivery / Faculty / Allocation Group</label>
                  <select class="form-select" id="quickCourseSelect" style="border-radius:0.5em;">
                    <option value="">Select Batch/Semester First</option>
                  </select>
                </div>


                <div class="col-12 d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-success" id="quickAddSlotBtn" style="border-radius:0.5em;">
                    <i class="fa fa-plus"></i> Add Slot To Grid
                  </button>
                  <small class="text-muted align-self-center" id="quickFormHint">Select day, shift, hour, course, and assignment.</small>
                </div>
                <div class="col-12">
                  <div class="mb-0" id="quickAssignmentInfo" style="display:none;"></div>
                </div>
              </div>
            </div>
          </div> -->
          <div id="timetableGridArea"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal for slot assignment -->
  <div class="modal fade" id="slotModal" tabindex="-1" aria-labelledby="slotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="slotModalLabel">Assign Course & Teacher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Teaching Assignment</label>
            <select class="dselect-example" id="modalTeachingAssignment" style="border-radius:0.5em;">
              <option value="">Select Assignment</option>
            </select>
            <small class="text-muted" id="assignmentHint">Showing active rows from Teaching Assignments.</small>
          </div>
          <div class="mb-3" id="courseAssignmentInfo" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:0.5em;">Close</button>
          <button type="button" class="btn btn-primary" id="saveSlotBtn" style="border-radius:0.5em;">Save Slot</button>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" id="subjectIdInput" value="{{ $data->id }}">
  <input type="hidden" id="selectedHour" value="">
  <input type="hidden" id="selectedDay" value="">
  <script id="teachingAssignmentListJson" type="application/json">
    {
      !!json_encode($teachingAssignmentList ?? []) !!
    }
  </script>
  <script id="curriculumDeliveryJson" type="application/json">
    {
      !!json_encode($curriculumDeliveryRows ?? []) !!
    }
  </script>

  <script>
    let timetableData = [];
    let editingEntryKey = null;

    function safeParseJsonScript(id, fallback = []) {
      try {
        const raw = document.getElementById(id)?.textContent || '';
        if (!raw.trim()) return fallback;
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : fallback;
      } catch (error) {
        console.error(`Failed to parse JSON from #${id}:`, error);
        return fallback;
      }
    }

    let teachingAssignmentList = safeParseJsonScript('teachingAssignmentListJson', []);
    let curriculumDeliveryRows = safeParseJsonScript('curriculumDeliveryJson', []);
    let teachingAssignments = [];
    let assignmentsByCourse = {};
    let assignmentsById = {};
    let gridHourRows = [];
    const totalHours = 6;
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    function buildAssignmentIndexes() {
      assignmentsByCourse = teachingAssignments.reduce((acc, assignment) => {
        const key = String(assignment.course_id || '');
        if (!acc[key]) acc[key] = [];
        acc[key].push(assignment);
        return acc;
      }, {});

      assignmentsById = teachingAssignments.reduce((acc, assignment) => {
        acc[String(assignment.id)] = assignment;
        return acc;
      }, {});
    }

    function syncTeachingAssignments() {
      const currentSubjectId = Number(document.getElementById('subjectIdInput')?.value || 0);
      teachingAssignments = teachingAssignmentList
        .filter(item => {
          const itemSubjectId = Number(item.subject_id || 0);
          const active = Number(item.is_active) === 1;
          const sameSubject = currentSubjectId ? itemSubjectId === currentSubjectId : true;
          return active && sameSubject;
        })
        .map(item => ({
          id: item.id,
          subject_id: item.subject_id,
          course_id: item.course_id,
          faculty_id: item.faculty_id,
          delivery_type: item.delivery_type,
          allocation_group: item.allocation_group,
          allocation_group_label: item.allocation_group_label,
          course_label: item.course_text,
          faculty_label: item.faculty_text,
          room: item.room_raw || item.room || '',
          remarks: item.remarks_raw || item.remarks || '',
        }));
      buildAssignmentIndexes();
    }

    syncTeachingAssignments();

    function getActiveShift() {
      const shiftSelect = document.getElementById('shiftSelect');
      return shiftSelect ? shiftSelect.value : 'common';
    }

    function getActiveShiftLabel() {
      const shiftSelect = document.getElementById('shiftSelect');
      if (!shiftSelect) return 'Common';
      const option = shiftSelect.options[shiftSelect.selectedIndex];
      return option ? option.text : 'Common';
    }

    function getActiveProgramType() {
      const programTypeSelect = document.getElementById('programTypeSelect');
      const value = String(programTypeSelect?.value || 'UG').trim().toUpperCase();
      return value === 'PG' ? 'PG' : 'UG';
    }

    function escapeHtml(text) {
      return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function showConflictMessage(message, title = 'Conflict Detected') {
      const text = message || 'Conflict found. Please choose another slot.';
      if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
        Swal.fire({
          icon: 'warning',
          title,
          text,
          confirmButtonText: 'OK'
        });
        return;
      }
      alert(text);
    }

    function makeEntryKey(entry) {
      return entry.routine_id ? `r_${entry.routine_id}` : (entry.local_key || '');
    }

    function ensureLocalKey(entry) {
      if (!entry.routine_id && !entry.local_key) {
        entry.local_key = `tmp_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
      }
      return entry;
    }

    function getSlotEntries(hour, day) {
      return timetableData.filter(t => Number(t.hour_number) === Number(hour) && t.day_of_week === day);
    }

    function formatEntryMeta(entry) {
      const chunks = [];
      if (entry.delivery_type) chunks.push(entry.delivery_type);
      if (entry.allocation_group_label) chunks.push(entry.allocation_group_label);
      else if (entry.allocation_group) chunks.push(`Group ${entry.allocation_group}`);
      return chunks;
    }

    function getEntryByKey(entryKey) {
      return timetableData.find(entry => makeEntryKey(entry) === entryKey);
    }

    function getCurrentSubjectId() {
      return Number(document.getElementById('subjectIdInput')?.value || 0);
    }

    function dayNameToWeekdayId(dayName) {
      const map = {
        Monday: 1,
        Tuesday: 2,
        Wednesday: 3,
        Thursday: 4,
        Friday: 5,
        Saturday: 6,
        Sunday: 7,
      };
      return Number(map[String(dayName || '').trim()] || 0);
    }

    function getShiftIdBySlug(shiftSlug) {
      const normalized = String(shiftSlug || '').toLowerCase();

      const quickShiftSelect = document.getElementById('quickShiftSelect');
      if (quickShiftSelect) {
        const quickOption = Array.from(quickShiftSelect.options).find(opt => String(opt.dataset.slug || '').toLowerCase() === normalized);
        if (quickOption) {
          const quickShiftId = Number(quickOption.value || 0);
          if (quickShiftId > 0) return quickShiftId;
        }
      }

      const shiftSelect = document.getElementById('shiftSelect');
      if (shiftSelect) {
        const mainOption = Array.from(shiftSelect.options).find(opt => String(opt.value || '').toLowerCase() === normalized);
        const mainShiftId = Number(mainOption?.dataset?.shiftId || 0);
        if (mainShiftId > 0) return mainShiftId;
      }

      return 0;
    }

    function buildDraftEntries(excludeEntryKey = '') {
      return timetableData
        .filter(entry => !excludeEntryKey || makeEntryKey(entry) !== excludeEntryKey)
        .map(entry => ({
          routine_id: Number(entry.routine_id || 0),
          weekday_id: dayNameToWeekdayId(entry.day_of_week),
          hour_id: Number(entry.hour_number || 0),
          faculty_id: Number(entry.teacher_id || 0),
          teaching_assignment_id: Number(entry.teaching_assignment_id || 0),
          lecturehall_id: Number(entry.lecturehall_id || 0),
          shift_id: getShiftIdBySlug(String(entry.shift || getActiveShift() || 'common')),
        }));
    }

    async function fetchBookedFacultiesForSlot(day, hourNumber, ignoreRoutineId = 0) {
      const subjectId = getCurrentSubjectId();
      const shift = getActiveShift();

      if (!subjectId || !day || !hourNumber) {
        return [];
      }

      const conflictUrl = '{{ route("department.timetable.conflicts", ["hourNumber" => "HOUR_NO", "day" => "DAY_NAME"]) }}'
        .replace('HOUR_NO', encodeURIComponent(String(hourNumber)))
        .replace('DAY_NAME', encodeURIComponent(String(day)));

      try {
        const response = await fetch(`${conflictUrl}?subject_id=${encodeURIComponent(String(subjectId))}&shift=${encodeURIComponent(shift)}&ignore_routine_id=${encodeURIComponent(String(ignoreRoutineId || 0))}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        });

        const data = await response.json();
        if (!data?.success || !Array.isArray(data?.booked_faculties)) {
          return [];
        }

        return data.booked_faculties
          .map(id => Number(id || 0))
          .filter(id => id > 0);
      } catch (error) {
        console.error('Failed to fetch booked faculties:', error);
        return [];
      }
    }

    async function validateTimetableConflictBeforeAdd({
      day,
      hourNumber,
      teachingAssignmentId,
      ignoreRoutineId = 0,
      excludeEntryKey = '',
    }) {
      const batchId = Number(document.getElementById('batchSelect')?.value || 0);
      const semesterId = Number(document.getElementById('semesterSelect')?.value || 0);
      const subjectId = getCurrentSubjectId();
      const weekdayId = dayNameToWeekdayId(day);
      const shiftSlug = getActiveShift();
      const programType = getActiveProgramType();

      if (!subjectId || !batchId || !semesterId || !weekdayId || !hourNumber || !teachingAssignmentId) {
        return {
          success: false,
          message: 'Missing required fields for conflict validation.',
        };
      }

      const conflictCheckUrl = '{{ route("department.timetable.conflict-check") }}';
      const response = await fetch(conflictCheckUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
          subject_id: subjectId,
          batch_id: batchId,
          semester_id: semesterId,
          program_type: programType,
          weekday_id: weekdayId,
          hour_id: Number(hourNumber),
          shift: shiftSlug,
          teaching_assignment_id: Number(teachingAssignmentId),
          ignore_routine_id: Number(ignoreRoutineId || 0),
          draft_entries: buildDraftEntries(excludeEntryKey),
        })
      });

      const data = await response.json();
      return {
        success: Boolean(data?.success),
        message: data?.message || 'Conflict check failed.',
      };
    }

    function normalizeDeliveryType(rawValue) {
      const value = String(rawValue || '').trim().toUpperCase().replace(/[_-]+/g, ' ');
      if (!value) return '';
      if (value === 'CORE A' || value === 'MAJOR COMBO1') return 'CORE A';
      if (value === 'CORE B' || value === 'MAJOR COMBO2') return 'CORE B';
      if (value === 'COMMON' || value === 'PROGRAMME COMMON' || value === 'PROGRAM COMMON') return 'COMMON';
      if (value === 'MDC' || value === 'OPEN CHOICE') return 'MDC';
      return value;
    }

    function renderAssignmentInfo(assignmentId, targetId = 'courseAssignmentInfo') {
      const infoBox = document.getElementById(targetId);
      if (!infoBox) return;

      if (!assignmentId) {
        infoBox.style.display = 'none';
        infoBox.innerHTML = '';
        return;
      }

      const assignment = assignmentsById[String(assignmentId)];
      if (!assignment) {
        infoBox.style.display = 'none';
        infoBox.innerHTML = '';
        return;
      }

      const delivery = normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || '-';
      const roomText = assignment.room || '-';
      const groupText = assignment.allocation_group_label || (assignment.allocation_group ? `Group ${assignment.allocation_group}` : '-');

      infoBox.style.display = '';
      infoBox.innerHTML = `
        <div class="border rounded p-2" style="background:#f8f9ff;">
          <div class="fw-bold mb-2">Selected Teaching Assignment</div>
          <div class="row g-2" style="font-size:0.95rem;">
            <div class="col-12"><span class="fw-semibold">Course:</span> ${escapeHtml(assignment.course_label || '-')}</div>
            <div class="col-12"><span class="fw-semibold">Faculty:</span> ${escapeHtml(assignment.faculty_label || '-')}</div>
            <div class="col-md-6"><span class="fw-semibold">Delivery:</span> ${escapeHtml(delivery)}</div>
            <div class="col-md-6"><span class="fw-semibold">Room:</span> ${escapeHtml(roomText)}</div>
            <div class="col-12"><span class="fw-semibold">Allocation Group:</span> ${escapeHtml(groupText)}</div>
          </div>
        </div>
      `;
    }

    function renderCourseAssignmentInfo(assignmentId) {
      renderAssignmentInfo(assignmentId, 'courseAssignmentInfo');
    }

    function renderQuickAssignmentInfo(assignmentId) {
      renderAssignmentInfo(assignmentId, 'quickAssignmentInfo');
    }

    function initTeachingAssignmentLiveSearch() {
      const select = document.getElementById('modalTeachingAssignment');
      if (!select || typeof window.dselect !== 'function') return;

      // Avoid duplicate wrappers when options are repainted.
      const nextEl = select.nextElementSibling;
      if (nextEl && nextEl.classList.contains('dselect-wrapper')) {
        nextEl.remove();
      }

      window.dselect(select, {
        search: true,
        clearable: false,

      });
    }

    function formatHourLabel(hour) {
      const name = hour.name || `Hour ${hour.hour_no || hour.id}`;
      const timing = hour.start_time && hour.end_time ? ` (${hour.start_time} - ${hour.end_time})` : '';
      return `${name}${timing}`;
    }

    function syncMainShiftSelection(shiftSlug, shouldReload = false) {
      const shiftSelect = document.getElementById('shiftSelect');
      if (!shiftSelect) return;
      const hasOption = Array.from(shiftSelect.options).some(option => option.value === shiftSlug);
      if (!hasOption) return;
      const changed = shiftSelect.value !== shiftSlug;
      shiftSelect.value = shiftSlug;
      if (changed && shouldReload) loadTimetable();
    }

    function loadGridHoursForActiveShift() {
      const shiftSlug = getActiveShift();
      const shiftId = getShiftIdBySlug(shiftSlug);
      if (!shiftId) {
        gridHourRows = [];
        return Promise.resolve();
      }

      const hoursUrl = '{{ route("department.timetable.hours") }}' + `?shift_id=${encodeURIComponent(shiftId)}`;
      return fetch(hoursUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          const rows = response.success ? (response.data || []) : [];
          gridHourRows = rows
            .map(hour => ({
              hour_number: Number(hour.hour_no || hour.id || 0),
              name: hour.name || '',
              start_time: hour.start_time || '',
              end_time: hour.end_time || '',
            }))
            .filter(hour => hour.hour_number > 0)
            .sort((a, b) => a.hour_number - b.hour_number);
        })
        .catch(() => {
          gridHourRows = [];
        });
    }

    function getGridHourRows() {
      if (gridHourRows.length) return gridHourRows;
      return Array.from({
        length: totalHours
      }, (_, index) => ({
        hour_number: index + 1,
        name: `Hour ${index + 1}`,
        start_time: '',
        end_time: ''
      }));
    }

    function populateQuickHourOptions() {
      const quickShiftSelect = document.getElementById('quickShiftSelect');
      const selectedShiftId = String(quickShiftSelect?.value || '').trim();
      const hourSelect = document.getElementById('quickHourSelect');
      if (!hourSelect) return;

      if (!selectedShiftId) {
        hourSelect.innerHTML = '<option value="">Select Shift First</option>';
        return;
      }

      hourSelect.innerHTML = '<option value="">Loading hours...</option>';

      const hoursUrl = '{{ route("department.timetable.hours") }}' + `?shift_id=${encodeURIComponent(selectedShiftId)}`;
      fetch(hoursUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          const rows = response.success ? (response.data || []) : [];
          hourSelect.innerHTML = '<option value="">Select Hour</option>';

          rows.forEach(hour => {
            const option = document.createElement('option');
            option.value = String(hour.hour_no || hour.id);
            option.textContent = formatHourLabel(hour);
            hourSelect.appendChild(option);
          });

          if (!rows.length) {
            hourSelect.innerHTML = '<option value="">No teaching hours for selected shift</option>';
          }
        })
        .catch(error => {
          console.error('Error loading hours by shift:', error);
          hourSelect.innerHTML = '<option value="">Failed to load hours</option>';
        });
    }

    function populateQuickCourseOptions() {
      const courseSelect = document.getElementById('quickCourseSelect');
      const hint = document.getElementById('quickFormHint');
      if (!courseSelect) return;

      const batchId = Number(document.getElementById('batchSelect')?.value || 0);
      const semesterId = Number(document.getElementById('semesterSelect')?.value || 0);
      const programType = getActiveProgramType();

      if (!batchId || !semesterId) {
        courseSelect.innerHTML = '<option value="">Select Batch/Semester First</option>';
        renderQuickAssignmentInfo('');
        return;
      }

      const subjectId = Number(document.getElementById('subjectIdInput')?.value || 0);
      const shift = getActiveShift();
      if (!subjectId) {
        courseSelect.innerHTML = '<option value="">Subject not found</option>';
        renderQuickAssignmentInfo('');
        return;
      }

      const dataUrl = '{{ route("department.timetable.data", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'
        .replace('BATCH_ID', String(batchId))
        .replace('SEMESTER_ID', String(semesterId));

      courseSelect.innerHTML = '<option value="">Loading courses...</option>';

      fetch(`${dataUrl}?shift=${encodeURIComponent(shift)}&program_type=${encodeURIComponent(programType)}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          const remoteRows = response.success ? (response.quick_courses || []) : [];
          const rows = remoteRows
            .map(row => ({
              id: Number(row.assignment_id || row.id || 0),
              subject_id: Number(row.subject_id || 0),
              course_id: Number(row.course_id || 0),
              faculty_id: Number(row.faculty_id || 0),
              delivery_type: row.delivery_type || '',
              allocation_group: Number(row.allocation_group || 0),
              allocation_group_label: row.allocation_group_label || '',
              course_label: row.course_label || [row.course_code, row.course_name].filter(Boolean).join(' - ') || 'Course',
              faculty_label: row.faculty_label || [row.faculty_code, row.faculty_name].filter(Boolean).join(' - ') || 'Faculty',
              room: row.room || '',
              remarks: row.remarks || '',
              batch: Number(row.batch || batchId),
              semester: Number(row.semester || semesterId),
              offering_dept: Number(row.offering_dept || 0),
              course_type: row.course_type || '',
              delivery_category: row.delivery_category || '',
              specialization_mode: row.specialization_mode || '',
              specialization_master_id: Number(row.specialization_master_id || 0),
              specialization_master_ids: Array.isArray(row.specialization_master_ids) ? row.specialization_master_ids : [],
              specialization_name: row.specialization_name || '',
            }))
            .filter(row => row.id > 0)
            .sort((a, b) => {
              const left = `${a.course_label || ''}|${a.faculty_label || ''}|${a.delivery_type || ''}`;
              const right = `${b.course_label || ''}|${b.faculty_label || ''}|${b.delivery_type || ''}`;
              return left.localeCompare(right);
            });

          rows.forEach(assignment => {
            assignmentsById[String(assignment.id)] = assignment;
          });

          courseSelect.innerHTML = '<option value="">Select Teaching Assignment</option>';
          rows.forEach(assignment => {
            const option = document.createElement('option');
            option.value = String(assignment.id);
            const delivery = normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || '-';
            const groupText = assignment.allocation_group_label || (assignment.allocation_group ? `Group ${assignment.allocation_group}` : '-');
            option.textContent = `${assignment.course_label || 'Course'} | ${delivery} | ${assignment.faculty_label || 'Faculty'} | ${groupText}`;
            courseSelect.appendChild(option);
          });

          if (rows.length) {
            courseSelect.value = String(rows[0].id);
            renderQuickAssignmentInfo(courseSelect.value);
          } else {
            renderQuickAssignmentInfo('');
          }

          if (hint) {
            hint.textContent = rows.length ?
              `Loaded from timetable-data (batch ${batchId}, semester ${semesterId}): ${rows.length} assignment(s).` :
              `No assignments found from timetable-data for batch ${batchId}, semester ${semesterId}, program ${programType}.`;
          }
        })
        .catch(error => {
          console.error('Error loading quick courses from timetable-data:', error);
          courseSelect.innerHTML = '<option value="">Failed to load courses</option>';
          renderQuickAssignmentInfo('');
          if (hint) {
            hint.textContent = 'Failed to fetch courses from timetable-data endpoint.';
          }
        });
    }

    function populateQuickAssignmentOptions(selectedAssignmentId = '') {
      const courseSelect = document.getElementById('quickCourseSelect');
      if (!courseSelect) return;

      if (selectedAssignmentId && assignmentsById[String(selectedAssignmentId)]) {
        courseSelect.value = String(selectedAssignmentId);
      }

      renderQuickAssignmentInfo(courseSelect.value);
    }

    async function addQuickSlotToGrid() {
      const day = document.getElementById('quickDaySelect')?.value;
      const shift = document.getElementById('quickShiftSelect')?.value || 'common';
      const hourNumber = Number(document.getElementById('quickHourSelect')?.value || 0);
      const teachingAssignmentId = document.getElementById('quickCourseSelect')?.value;

      if (!day) return alert('Please select day');
      if (!hourNumber) return alert('Please select hour');
      if (!teachingAssignmentId) return alert('Please select teaching assignment');

      syncMainShiftSelection(shift, false);

      const assignment = assignmentsById[String(teachingAssignmentId)];
      if (!assignment) return alert('Selected teaching assignment is invalid.');

      try {
        const conflictResult = await validateTimetableConflictBeforeAdd({
          day,
          hourNumber,
          teachingAssignmentId: Number(teachingAssignmentId),
        });

        if (!conflictResult.success) {
          showConflictMessage(conflictResult.message || 'Conflict found. Please choose another slot.');
          return;
        }
      } catch (error) {
        console.error('Conflict check failed:', error);
        showConflictMessage('Could not validate conflicts. Please try again.', 'Validation Error');
        return;
      }

      const duplicate = timetableData.find(entry =>
        Number(entry.hour_number) === hourNumber &&
        entry.day_of_week === day &&
        Number(entry.teaching_assignment_id || 0) === Number(teachingAssignmentId)
      );
      if (duplicate) return alert('This assignment is already present in the selected slot.');

      timetableData.push(ensureLocalKey({
        hour_number: hourNumber,
        day_of_week: day,
        subject_id: Number(assignment.course_id),
        teaching_assignment_id: Number(teachingAssignmentId),
        teacher_id: Number(assignment.faculty_id),
        subject_name: assignment.course_label || 'Course',
        teacher_name: assignment.faculty_label || 'Teacher',
        delivery_type: normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || null,
        allocation_group: assignment.allocation_group || null,
        allocation_group_label: assignment.allocation_group_label || null,
        room: assignment.room || null,
      }));

      renderTimetable();
    }

    async function populateTeachingAssignmentOptions(selectedAssignmentId = '', day = '', hourNumber = 0, ignoreRoutineId = 0) {
      const assignmentSelect = document.getElementById('modalTeachingAssignment');
      const assignmentHint = document.getElementById('assignmentHint');
      if (!assignmentSelect) return;

      const batchId = Number(document.getElementById('batchSelect')?.value || 0);
      const semesterId = Number(document.getElementById('semesterSelect')?.value || 0);
      const shift = getActiveShift();
      const programType = getActiveProgramType();
      const blockedFacultyIds = new Set(await fetchBookedFacultiesForSlot(day, hourNumber, ignoreRoutineId));

      const filterAvailableAssignments = (rows) => {
        const selectedId = String(selectedAssignmentId || '');
        return rows.filter(assignment => {
          const assignmentId = String(assignment.id || '');
          if (selectedId && assignmentId === selectedId) {
            return true;
          }

          const facultyId = Number(assignment.faculty_id || 0);
          if (facultyId <= 0) {
            return true;
          }

          return !blockedFacultyIds.has(facultyId);
        });
      };

      const paintFallback = () => {
        const sortedAssignments = [...teachingAssignments].sort((a, b) => {
          const left = `${a.course_label || ''}|${a.faculty_label || ''}|${a.delivery_type || ''}`;
          const right = `${b.course_label || ''}|${b.faculty_label || ''}|${b.delivery_type || ''}`;
          return left.localeCompare(right);
        });
        const availableAssignments = filterAvailableAssignments(sortedAssignments);

        assignmentSelect.innerHTML = '<option value="">Select Assignment</option>';
        availableAssignments.forEach(assignment => {
          const option = document.createElement('option');
          option.value = String(assignment.id);
          option.textContent = `${assignment.course_label || 'Course'} | ${assignment.faculty_label || 'Faculty'} | ${normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || '-'} | Room: ${assignment.room || '-'}`;
          assignmentSelect.appendChild(option);
        });

        if (selectedAssignmentId && assignmentsById[String(selectedAssignmentId)]) {
          assignmentSelect.value = String(selectedAssignmentId);
        }

        if (assignmentHint) {
          assignmentHint.textContent = availableAssignments.length ?
            `Showing available teaching assignments (${availableAssignments.length}/${sortedAssignments.length} row(s)).` :
            'No available faculty for this slot.';
        }

        initTeachingAssignmentLiveSearch();

        renderCourseAssignmentInfo(assignmentSelect.value);
      };

      if (!batchId || !semesterId) {
        paintFallback();
        return;
      }

      const dataUrl = '{{ route("department.timetable.data", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'
        .replace('BATCH_ID', String(batchId))
        .replace('SEMESTER_ID', String(semesterId));

      assignmentSelect.innerHTML = '<option value="">Loading assignments...</option>';

      fetch(`${dataUrl}?shift=${encodeURIComponent(shift)}&program_type=${encodeURIComponent(programType)}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          const remoteRows = response.success ? (response.quick_courses || []) : [];
          const rows = remoteRows
            .map(row => ({
              id: Number(row.assignment_id || row.id || 0),
              subject_id: Number(row.subject_id || 0),
              course_id: Number(row.course_id || 0),
              faculty_id: Number(row.faculty_id || 0),
              delivery_type: row.delivery_type || '',
              allocation_group: Number(row.allocation_group || 0),
              allocation_group_label: row.allocation_group_label || '',
              course_label: row.course_label || [row.course_code, row.course_name].filter(Boolean).join(' - ') || 'Course',
              faculty_label: row.faculty_label || [row.faculty_code, row.faculty_name].filter(Boolean).join(' - ') || 'Faculty',
              room: row.room || '',
              remarks: row.remarks || '',
            }))
            .filter(row => row.id > 0)
            .sort((a, b) => {
              const left = `${a.course_label || ''}|${a.faculty_label || ''}|${a.delivery_type || ''}`;
              const right = `${b.course_label || ''}|${b.faculty_label || ''}|${b.delivery_type || ''}`;
              return left.localeCompare(right);
            });
          const availableRows = filterAvailableAssignments(rows);

          if (!rows.length) {
            paintFallback();
            return;
          }

          rows.forEach(assignment => {
            assignmentsById[String(assignment.id)] = assignment;
          });

          assignmentSelect.innerHTML = '<option value="">Select Assignment</option>';
          availableRows.forEach(assignment => {
            const option = document.createElement('option');
            option.value = String(assignment.id);
            option.textContent = `${assignment.course_label || 'Course'} | ${assignment.faculty_label || 'Faculty'} | ${normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || '-'} | Room: ${assignment.room || '-'}`;
            assignmentSelect.appendChild(option);
          });

          if (selectedAssignmentId && assignmentsById[String(selectedAssignmentId)]) {
            assignmentSelect.value = String(selectedAssignmentId);
          }

          if (assignmentHint) {
            assignmentHint.textContent = availableRows.length ?
              `Showing available teaching assignments (${availableRows.length}/${rows.length} row(s)).` :
              'No available faculty for this slot.';
          }

          initTeachingAssignmentLiveSearch();

          renderCourseAssignmentInfo(assignmentSelect.value);
        })
        .catch(() => {
          paintFallback();
        });
    }

    function loadTimetable() {
      const batchId = document.getElementById('batchSelect')?.value;
      const semesterId = document.getElementById('semesterSelect')?.value;
      const shift = getActiveShift();
      const programType = getActiveProgramType();

      if (!batchId || !semesterId) {
        alert('Please select Batch and Semester');
        return;
      }

      const loadUrl = '{{ route("department.timetable.data", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'.replace('BATCH_ID', batchId).replace('SEMESTER_ID', semesterId);
      fetch(`${loadUrl}?shift=${encodeURIComponent(shift)}&program_type=${encodeURIComponent(programType)}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          timetableData = response.success ? (response.data || []) : [];
          return loadGridHoursForActiveShift();
        })
        .then(() => {
          renderTimetable();
        })
        .catch(error => {
          console.error('Error loading timetable:', error);
          timetableData = [];
          loadGridHoursForActiveShift().finally(() => renderTimetable());
        });
    }

    function renderTimetable() {
      const timetableGridArea = document.getElementById('timetableGridArea');
      const batchId = document.getElementById('batchSelect').value;
      const semesterId = document.getElementById('semesterSelect').value;
      if (!batchId || !semesterId) return;

      const hourRows = getGridHourRows();
      let tableBody = '';
      hourRows.forEach(hourRow => {
        const hour = Number(hourRow.hour_number || 0);
        const hourLabel = formatHourLabel({
          hour_no: hour,
          name: hourRow.name,
          start_time: hourRow.start_time,
          end_time: hourRow.end_time,
        });

        tableBody += `
          <tr>
            <td class="text-center fw-bold" style="background:#f3e9ff;color:#5740b4;font-weight:600;"><strong>${escapeHtml(hourLabel)}</strong></td>
        `;

        days.forEach(day => {
          const entries = getSlotEntries(hour, day);
          if (!entries.length) {
            tableBody += `
              <td class="period-cell" style="cursor:pointer;padding:15px;" onclick="openSubjectModal(${hour}, '${day}')">
                <small class="text-muted"><i class="fas fa-plus-circle"></i> Add</small>
              </td>
            `;
            return;
          }

          const rows = entries.map(entry => {
            const entryKey = makeEntryKey(entry);
            const entryMeta = formatEntryMeta(entry);
            const routineId = entry.routine_id ? Number(entry.routine_id) : 'null';
            const roomLabel = (entry.room && String(entry.room).trim() !== '') ? String(entry.room).trim() : '-';
            return `
              <div class="slot-entry p-2 mb-2 text-start" style="font-size:12px;">
                <div class="fw-bold">${escapeHtml(entry.subject_name || 'Course')}</div>
                <div><i class="fas fa-user"></i> ${escapeHtml(entry.teacher_name || 'Teacher')}</div>
                <div><i class="fas fa-door-open"></i> Room: ${escapeHtml(roomLabel)}</div>
                <div>
                  ${entryMeta.map((meta, index) => `<span class="slot-chip ${index === 0 ? 'slot-chip-delivery' : 'slot-chip-group'}">${escapeHtml(meta)}</span>`).join('')}
                </div>
                <div class="mt-1 d-flex gap-1">
                  <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); openSubjectModal(${hour}, '${day}', '${entryKey}'); return false;"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-outline-danger btn-sm" onclick="event.stopPropagation(); removeSlot(event, ${routineId}, ${hour}, '${day}', '${entryKey}'); return false;"><i class="fas fa-times"></i></button>
                </div>
              </div>
            `;
          }).join('');

          tableBody += `
            <td class="period-cell filled" style="vertical-align:top;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="fw-bold text-muted">${entries.length} item(s)</small>
                <button class="btn btn-success btn-sm" onclick="openSubjectModal(${hour}, '${day}'); return false;"><i class="fas fa-plus"></i></button>
              </div>
              ${rows}
            </td>
          `;
        });

        tableBody += '</tr>';
      });

      timetableGridArea.innerHTML = `
        <div class='card custom-card'>
          <div class='card-body'>
            <h5 class='card-title text-dark mb-3'>Timetable Grid <span class="badge bg-info ms-2">Shift: ${getActiveShiftLabel()}</span> <span class="badge bg-secondary ms-2">Program: ${escapeHtml(getActiveProgramType())}</span></h5>
            <div class="mb-3">
              <button type="button" class="btn btn-warning btn-sm me-2" onclick="copyFromDay()"><i class="fa fa-copy"></i> Copy Day</button>
              <button type="button" class="btn btn-danger btn-sm me-2" onclick="clearTimetable()"><i class="fa fa-trash"></i> Clear All</button>
              <button type="button" class="btn btn-primary btn-sm" onclick="saveTimetable()"><i class="fa fa-save"></i> Save Timetable</button>
            </div>
            <div class='table-responsive'>
              <table class='table custom-table align-middle' id='timetableGrid'>
                <thead>
                  <tr>
                    <th>Hour</th><th>Monday</th><th>Tuesday</th><th>Wednesday</th><th>Thursday</th><th>Friday</th><th>Saturday</th>
                  </tr>
                </thead>
                <tbody>${tableBody}</tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    }

    function openSubjectModal(hourNumber, day, entryKey = null) {
      document.getElementById('selectedHour').value = hourNumber;
      document.getElementById('selectedDay').value = day;
      editingEntryKey = entryKey;

      const assignmentSelect = document.getElementById('modalTeachingAssignment');
      const saveButton = document.getElementById('saveSlotBtn');

      if (entryKey) {
        const entry = getEntryByKey(entryKey);
        if (entry) {
          populateTeachingAssignmentOptions(entry.teaching_assignment_id, day, hourNumber, Number(entry.routine_id || 0));
          if (saveButton) saveButton.textContent = 'Update Slot';
        }
      } else {
        populateTeachingAssignmentOptions('', day, hourNumber, 0);
        if (assignmentSelect) assignmentSelect.value = '';
        renderCourseAssignmentInfo('');
        if (saveButton) saveButton.textContent = 'Save Slot';
      }

      const modal = new bootstrap.Modal(document.getElementById('slotModal'));
      modal.show();
    }

    async function saveSlot() {
      const hourNumber = Number(document.getElementById('selectedHour').value);
      const day = document.getElementById('selectedDay').value;
      const teachingAssignmentId = document.getElementById('modalTeachingAssignment').value;
      if (!teachingAssignmentId) return alert('Please select a teaching assignment');

      const assignment = assignmentsById[String(teachingAssignmentId)];
      if (!assignment) return alert('Selected teaching assignment is invalid.');

      const currentEntry = editingEntryKey ? getEntryByKey(editingEntryKey) : null;
      const ignoreRoutineId = Number(currentEntry?.routine_id || 0);

      try {
        const conflictResult = await validateTimetableConflictBeforeAdd({
          day,
          hourNumber,
          teachingAssignmentId: Number(teachingAssignmentId),
          ignoreRoutineId,
          excludeEntryKey: editingEntryKey || '',
        });

        if (!conflictResult.success) {
          showConflictMessage(conflictResult.message || 'Conflict found. Please choose another slot.');
          return;
        }
      } catch (error) {
        console.error('Conflict check failed:', error);
        showConflictMessage('Could not validate conflicts. Please try again.', 'Validation Error');
        return;
      }

      const payload = ensureLocalKey({
        hour_number: hourNumber,
        day_of_week: day,
        subject_id: Number(assignment.course_id),
        teaching_assignment_id: Number(teachingAssignmentId),
        teacher_id: Number(assignment.faculty_id),
        subject_name: assignment.course_label || 'Course',
        teacher_name: assignment.faculty_label || 'Teacher',
        delivery_type: normalizeDeliveryType(assignment.delivery_type) || assignment.delivery_type || null,
        allocation_group: assignment.allocation_group || null,
        allocation_group_label: assignment.allocation_group_label || null,
        room: assignment.room || null,
      });

      if (editingEntryKey) {
        timetableData = timetableData.map(entry => {
          if (makeEntryKey(entry) !== editingEntryKey) return entry;
          return {
            ...entry,
            ...payload,
            routine_id: entry.routine_id || null,
            local_key: entry.local_key || payload.local_key,
          };
        });
      } else {
        const duplicate = timetableData.find(entry =>
          Number(entry.hour_number) === hourNumber &&
          entry.day_of_week === day &&
          Number(entry.teaching_assignment_id || 0) === Number(teachingAssignmentId)
        );
        if (duplicate) return alert('This teacher assignment is already added in the selected slot.');
        timetableData.push(payload);
      }

      editingEntryKey = null;
      renderTimetable();
      bootstrap.Modal.getInstance(document.getElementById('slotModal')).hide();
    }

    function removeSlot(event, routineId, hourNumber, day, entryKey = null) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      if (!confirm('Remove this slot entry?')) return false;

      if (routineId && routineId !== 'null') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const deleteUrl = '{{ route("department.timetable.delete", "ROUTINE_ID") }}'.replace('ROUTINE_ID', routineId);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              timetableData = timetableData.filter(t => Number(t.routine_id) !== Number(routineId));
              renderTimetable();
            } else {
              alert('Error deleting slot entry: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error deleting slot entry:', error);
            alert('Error deleting slot entry. Please try again.');
          });
      } else {
        timetableData = timetableData.filter(t => entryKey ? makeEntryKey(t) !== entryKey : !(Number(t.hour_number) === Number(hourNumber) && t.day_of_week === day));
        renderTimetable();
      }

      return false;
    }

    function saveTimetable() {
      const batchId = document.getElementById('batchSelect').value;
      const semesterId = document.getElementById('semesterSelect').value;
      const shift = getActiveShift();
      const programType = getActiveProgramType();

      if (!batchId || !semesterId) return alert('Please select Batch and Semester');
      if (!timetableData.length) return alert('Please add at least one timetable entry');

      const saveUrl = '{{ route("department.timetable.store", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'.replace('BATCH_ID', batchId).replace('SEMESTER_ID', semesterId);
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) return alert('CSRF token not found. Please refresh and try again.');

      const saveButton = document.querySelector('button[onclick="saveTimetable()"]');
      if (saveButton) {
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
      }

      fetch(saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            batch_id: batchId,
            semester_id: semesterId,
            program_type: programType,
            shift,
            timetable: timetableData
          })
        })
        .then(response => response.json())
        .then(response => {
          if (response.success) {
            alert('Timetable saved successfully!');
            loadTimetable();
          } else {
            alert('Error: ' + (response.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error saving timetable:', error);
          alert('Error saving timetable.');
        })
        .finally(() => {
          if (saveButton) {
            saveButton.disabled = false;
            saveButton.innerHTML = '<i class="fa fa-save"></i> Save Timetable';
          }
        });
    }

    function clearTimetable() {
      if (!confirm('Are you sure you want to clear the entire timetable?')) return;

      const subjectId = document.getElementById('subjectIdInput').value;
      const batchId = document.getElementById('batchSelect').value;
      const semesterId = document.getElementById('semesterSelect').value;
      const shift = getActiveShift();
      const programType = getActiveProgramType();
      if (!batchId || !semesterId) return alert('Please select Batch and Semester first');

      const clearUrl = '{{ route("department.timetable.clear", ["subjectId" => "SUBJECT_ID", "batchId" => "BATCH_ID", "semesterId" => "SEMESTER_ID"]) }}'
        .replace('SUBJECT_ID', subjectId)
        .replace('BATCH_ID', batchId)
        .replace('SEMESTER_ID', semesterId) + `?shift=${encodeURIComponent(shift)}&program_type=${encodeURIComponent(programType)}`;

      fetch(clearUrl, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            timetableData = [];
            renderTimetable();
            alert(data.message || 'Timetable cleared successfully');
          } else {
            alert(data.message || 'Failed to clear timetable');
          }
        })
        .catch(error => {
          console.error('Error clearing timetable:', error);
          alert('An error occurred while clearing the timetable');
        });
    }

    function copyFromDay() {
      const sourceDay = prompt('Copy from which day? (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday)');
      if (!sourceDay || !days.includes(sourceDay)) return alert('Invalid day name');

      const targetDay = prompt('Copy to which day? (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday)');
      if (!targetDay || !days.includes(targetDay)) return alert('Invalid day name');

      const sourcePeriods = timetableData.filter(t => t.day_of_week === sourceDay);
      timetableData = timetableData.filter(t => t.day_of_week !== targetDay);
      sourcePeriods.forEach(period => {
        timetableData.push(ensureLocalKey({
          ...period,
          routine_id: null,
          local_key: null,
          day_of_week: targetDay,
        }));
      });
      renderTimetable();
    }

    document.addEventListener('DOMContentLoaded', function() {
      const generateBtn = document.getElementById('generateTimetableBtn');
      if (generateBtn) {
        generateBtn.addEventListener('click', function(e) {
          e.preventDefault();
          loadTimetable();
          syncTeachingAssignments();
          populateQuickCourseOptions();
        });
      }

      const saveSlotBtn = document.getElementById('saveSlotBtn');
      if (saveSlotBtn) saveSlotBtn.addEventListener('click', saveSlot);

      const quickShiftSelect = document.getElementById('quickShiftSelect');
      if (quickShiftSelect) {
        quickShiftSelect.addEventListener('change', function() {
          populateQuickHourOptions();
          const selectedOption = quickShiftSelect.options[quickShiftSelect.selectedIndex];
          const shiftSlug = selectedOption?.dataset?.slug || 'common';
          syncMainShiftSelection(shiftSlug, true);
        });
      }

      const quickCourseSelect = document.getElementById('quickCourseSelect');
      if (quickCourseSelect) {
        quickCourseSelect.addEventListener('change', function() {
          renderQuickAssignmentInfo(this.value);
        });
      }

      const quickAddSlotBtn = document.getElementById('quickAddSlotBtn');
      if (quickAddSlotBtn) quickAddSlotBtn.addEventListener('click', addQuickSlotToGrid);

      const assignmentSelect = document.getElementById('modalTeachingAssignment');
      if (assignmentSelect) {
        assignmentSelect.addEventListener('change', function() {
          renderCourseAssignmentInfo(this.value);
        });
      }

      initTeachingAssignmentLiveSearch();

      const slotModal = document.getElementById('slotModal');
      if (slotModal) {
        slotModal.addEventListener('hidden.bs.modal', function() {
          editingEntryKey = null;
          const saveButton = document.getElementById('saveSlotBtn');
          if (saveButton) saveButton.textContent = 'Save Slot';
        });
      }

      populateTeachingAssignmentOptions();
      const initQuickShift = document.getElementById('quickShiftSelect');
      if (initQuickShift && initQuickShift.value) {
        const selectedOption = initQuickShift.options[initQuickShift.selectedIndex];
        const initShiftSlug = selectedOption?.dataset?.slug || 'common';
        syncMainShiftSelection(initShiftSlug, false);
      }
      populateQuickHourOptions();
      populateQuickCourseOptions();

      const batchSelect = document.getElementById('batchSelect');
      const semesterSelect = document.getElementById('semesterSelect');
      const programTypeSelect = document.getElementById('programTypeSelect');
      if (batchSelect) {
        batchSelect.addEventListener('change', function() {
          populateTeachingAssignmentOptions(
            document.getElementById('modalTeachingAssignment')?.value || '',
            document.getElementById('selectedDay')?.value || '',
            Number(document.getElementById('selectedHour')?.value || 0),
            Number(getEntryByKey(editingEntryKey || '')?.routine_id || 0)
          );
          populateQuickCourseOptions();
          renderQuickAssignmentInfo(document.getElementById('quickCourseSelect')?.value || '');
        });
      }
      if (semesterSelect) {
        semesterSelect.addEventListener('change', function() {
          populateTeachingAssignmentOptions(
            document.getElementById('modalTeachingAssignment')?.value || '',
            document.getElementById('selectedDay')?.value || '',
            Number(document.getElementById('selectedHour')?.value || 0),
            Number(getEntryByKey(editingEntryKey || '')?.routine_id || 0)
          );
          populateQuickCourseOptions();
          renderQuickAssignmentInfo(document.getElementById('quickCourseSelect')?.value || '');
        });
      }
      if (programTypeSelect) {
        programTypeSelect.addEventListener('change', function() {
          populateTeachingAssignmentOptions(
            document.getElementById('modalTeachingAssignment')?.value || '',
            document.getElementById('selectedDay')?.value || '',
            Number(document.getElementById('selectedHour')?.value || 0),
            Number(getEntryByKey(editingEntryKey || '')?.routine_id || 0)
          );
          populateQuickCourseOptions();
          renderQuickAssignmentInfo(document.getElementById('quickCourseSelect')?.value || '');
          loadTimetable();
        });
      }

      const shiftSelect = document.getElementById('shiftSelect');
      if (shiftSelect) {
        shiftSelect.addEventListener('change', function() {
          const quickShift = document.getElementById('quickShiftSelect');
          if (quickShift) {
            const matchingOption = Array.from(quickShift.options).find(option => (option.dataset.slug || '').toLowerCase() === String(this.value || '').toLowerCase());
            if (matchingOption) {
              quickShift.value = matchingOption.value;
            }
            populateQuickHourOptions();
          }
        });
      }
    });
  </script>

  @include('includes.footer')
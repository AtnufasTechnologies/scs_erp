@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  <h4 class="text-capitalize">{{$subject->title}} - Teaching Assignment</h4>

  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="teachingAssignmentToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="teachingAssignmentToastBody">Saved successfully.</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
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

  <div class="card mt-3 mb-4 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0">Teaching Allocation Settings</h5>
      <span class="badge bg-light text-dark border">Department Level</span>
    </div>
    <div class="card-body">
      <p class="text-muted mb-3">Enable this only for departments that need multiple primary faculty selection in teaching assignment.</p>

      @if($teachingAllocationSetting)
      <form action="{{ route('department.teaching.allocation.settings.update', $teachingAllocationSetting->id) }}" method="post" class="row g-3 align-items-end">
        @csrf
        @method('PUT')
        <div class="col-lg-6">
          <label class="form-label fw-semibold">Multiple Primary Faculty</label>
          <select name="allow_multiple_primary_faculty" class="form-control">
            <option value="0" {{ !$allowMultiplePrimaryFaculty ? 'selected' : '' }}>Disabled</option>
            <option value="1" {{ $allowMultiplePrimaryFaculty ? 'selected' : '' }}>Enabled</option>
          </select>
        </div>
        <div class="col-lg-6 d-flex justify-content-lg-end gap-2">
          <button type="submit" class="btn btn-primary">Update Setting</button>
        </div>
      </form>
      <div class="mt-2 d-flex justify-content-lg-end">
        <form action="{{ route('department.teaching.allocation.settings.delete', $teachingAllocationSetting->id) }}" method="post" onsubmit="return confirm('Delete this setting?');" class="d-inline-block">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger">Delete Setting</button>
        </form>
      </div>
      @else
      <form action="{{ route('department.teaching.allocation.settings.store', $subject->id) }}" method="post" class="row g-3 align-items-end">
        @csrf
        <div class="col-lg-6">
          <label class="form-label fw-semibold">Multiple Primary Faculty</label>
          <select name="allow_multiple_primary_faculty" class="form-control">
            <option value="0">Disabled</option>
            <option value="1" selected>Enabled</option>
          </select>
        </div>
        <div class="col-lg-6 d-flex justify-content-lg-end">
          <button type="submit" class="btn btn-primary">Create Setting</button>
        </div>
      </form>
      @endif
    </div>
  </div>

  <div class="card mt-3 mb-4 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0" id="teachingAssignmentFormTitle">New Teaching Assignment</h5>
      <span class="badge bg-light text-dark border">Dynamic Group Allocation</span>
    </div>
    <div class="card-body">
      <form id="teachingAssignmentForm" action="{{ route('department.teaching.assignment.store', $subject->id) }}" method="post">
        @csrf
        <input type="hidden" name="assignment_id" id="assignment_id" value="">
        <input type="hidden" name="subject_id" value="{{$subject->id}}">

        <div class="row g-3">
          <div class="col-lg-6">
            <label class="form-label fw-semibold">My Courses <span class="text-danger">*</span></label>
            <select name="course_id" class="dselect-example" required>
              <option value="">--Select--</option>
              @foreach($courses as $course)
              @if($course->courseMaster)
              <option value="{{ $course->courseMaster->id }}">
                {{ $course->courseMaster->course_code }} - {{ $course->courseMaster->course_title }}
              </option>
              @endif
              @endforeach
            </select>
          </div>

          <div class="col-lg-6">
            <label class="form-label fw-semibold">Primary Faculty <span class="text-danger">*</span></label>
            @if($allowMultiplePrimaryFaculty)
            <select name="primary_faculty_ids[]" class="dselect-example" multiple required>
              @foreach($faculties as $faculty)
              @if($faculty->faculty)
              <option value="{{ $faculty->faculty->id }}">
                {{ $faculty->faculty->USER_CODE }} - {{ $faculty->faculty->FIRST_NAME }} {{ $faculty->faculty->LAST_NAME }}
              </option>
              @endif
              @endforeach
            </select>
            <small class="text-muted d-block mt-1">Multiple primary faculty is enabled for this department.</small>
            @else
            <select name="faculty_id" class="dselect-example" required>
              <option value="">--Select--</option>
              @foreach($faculties as $faculty)
              @if($faculty->faculty)
              <option value="{{ $faculty->faculty->id }}">
                {{ $faculty->faculty->USER_CODE }} - {{ $faculty->faculty->FIRST_NAME }} {{ $faculty->faculty->LAST_NAME }}
              </option>
              @endif
              @endforeach
            </select>
            @endif
          </div>

          <div class="col-lg-6">
            <label class="form-label fw-semibold">Co-Faculty</label>
            <select name="co_faculty_ids[]" class="dselect-example" multiple>
              @foreach($faculties as $faculty)
              @if($faculty->faculty)
              <option value="{{ $faculty->faculty->id }}">
                {{ $faculty->faculty->USER_CODE }} - {{ $faculty->faculty->FIRST_NAME }} {{ $faculty->faculty->LAST_NAME }}
              </option>
              @endif
              @endforeach
            </select>
            <small class="text-muted d-block mt-1">Optional. Co-faculty share the same students, timetable slot and attendance access.</small>
          </div>

          <div class="col-lg-3">
            <label class="form-label fw-semibold">Delivery Type <span class="text-danger">*</span></label>
            <select name="delivery_type" class="form-control" required>
              <option value="">--Select course first--</option>
            </select>
            <small class="text-muted d-block mt-1" id="deliveryTypeHelpText">Delivery type is picked from curriculum mapping.</small>
          </div>

          <div class="col-lg-3">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-control">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="col-lg-3">
            <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
            <select name="shift_id" class="form-control" required>
              <option value="">--Select--</option>
              @foreach($shiftOptions as $shift)
              <option value="{{ $shift->id }}">{{ $shift->title ?? strtoupper($shift->slug) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3">
            <label class="form-label fw-semibold">Room Allocation</label>
            <input type="text" name="room" class="form-control" placeholder="Room no / Lab">
          </div>

          <div class="col-lg-12">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
          </div>

          <div class="col-lg-12 d-flex gap-2 justify-content-end pt-1">
            <button type="button" class="btn btn-light border" id="teachingAssignmentCancelBtn" style="display:none;">Cancel Edit</button>
            <button type="submit" class="btn btn-primary" id="teachingAssignmentSubmitBtn">Save Assignment</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-header">
      <h5 class="mb-0">Teaching Assignments</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>Faculty</th>
              <th>Co-Faculty</th>
              <th>Delivery Type</th>
              <th>Shift</th>
              <th>Allocation Group</th>
              <th>Status</th>
              <th>Room</th>
              <th>Remarks</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="teachingAssignmentTableBody">
            @forelse($assignments as $assignment)
            <tr id="assignment-row-{{ $assignment->id }}">
              <td>{{ $loop->iteration }}</td>
              <td>{{ $assignment->course->course_code ?? '-' }} - {{ $assignment->course->course_title ?? '-' }}</td>
              <td>
                @php
                $primaryFacultyLabels = ($assignment->primaryFacultyMembers ?? collect())
                ->map(fn($faculty) => trim(($faculty->USER_CODE ?? '-') . ' - ' . ($faculty->FIRST_NAME ?? '-') . ' ' . ($faculty->LAST_NAME ?? '')))
                ->filter()
                ->values();
                @endphp
                @if($primaryFacultyLabels->isNotEmpty())
                {{ $primaryFacultyLabels->implode(', ') }}
                @else
                {{ $assignment->faculty->USER_CODE ?? '-' }} - {{ $assignment->faculty->FIRST_NAME ?? '-' }} {{ $assignment->faculty->LAST_NAME ?? '' }}
                @endif
              </td>
              <td>
                @php
                $coFacultyLabels = ($assignment->coFacultyMembers ?? collect())
                ->map(fn($faculty) => trim(($faculty->USER_CODE ?? '-') . ' - ' . ($faculty->FIRST_NAME ?? '-') . ' ' . ($faculty->LAST_NAME ?? '')))
                ->filter()
                ->values();
                @endphp
                {{ $coFacultyLabels->isNotEmpty() ? $coFacultyLabels->implode(', ') : '-' }}
              </td>
              <td>{{ $assignment->delivery_type }}</td>
              <td>{{ $assignment->shiftmaster->title ?? $assignment->shiftmaster->slug ?? '-' }}</td>
              <td>{{ $assignment->allocation_group_label }}</td>
              <td>
                @if($assignment->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
              <td>{{ $assignment->room ?: '-' }}</td>
              <td>{{ $assignment->remarks ?: '-' }}</td>
              <td>
                <button
                  type="button"
                  class="btn btn-sm btn-primary js-edit-assignment"
                  data-id="{{ $assignment->id }}"
                  data-course_id="{{ $assignment->course_id }}"
                  data-faculty_id="{{ $assignment->faculty_id }}"
                  data-primary_faculty_ids='@json(($assignment->primaryFacultyMembers ?? collect())->pluck("id")->map(fn($id) => (int) $id)->values()->all())'
                  data-co_faculty_ids='@json(($assignment->coFacultyMembers ?? collect())->pluck("id")->map(fn($id) => (int) $id)->values()->all())'
                  data-delivery_type="{{ $assignment->delivery_type }}"
                  data-shift_id="{{ (int) ($assignment->shift_id ?? 0) }}"
                  data-status="{{ $assignment->is_active }}"
                  data-room="{{ $assignment->room }}"
                  data-remarks="{{ $assignment->remarks }}">Edit</button>
                <form action="{{ route('department.teaching.assignment.delete', $assignment->id) }}" method="post" onsubmit="return confirm('Delete this assignment?');" style="display:inline-block;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
            @empty
            <tr id="teachingAssignmentEmptyRow">
              <td colspan="11" class="text-center">No teaching assignments found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script type="application/json" id="deliveryTypeMapJson">
  @json($deliveryTypeMap ?? [])
</script>
<script>
  (function() {
    const form = document.getElementById('teachingAssignmentForm');
    const tableBody = document.getElementById('teachingAssignmentTableBody');
    const assignmentIdInput = document.getElementById('assignment_id');
    const submitBtn = document.getElementById('teachingAssignmentSubmitBtn');
    const formTitle = document.getElementById('teachingAssignmentFormTitle');
    const cancelEditBtn = document.getElementById('teachingAssignmentCancelBtn');
    const toastElement = document.getElementById('teachingAssignmentToast');
    const toastBodyElement = document.getElementById('teachingAssignmentToastBody');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const createUrl = "{{ route('department.teaching.assignment.store', $subject->id) }}";
    const updateUrlTemplate = "{{ route('department.teaching.assignment.update', ['id' => '__ID__']) }}";
    const deliveryTypeMapSource = document.getElementById('deliveryTypeMapJson');
    const deliveryTypeMap = deliveryTypeMapSource ? JSON.parse(deliveryTypeMapSource.textContent || '{}') : {};
    const deliveryTypeSelect = form.querySelector('[name="delivery_type"]');
    const courseSelect = form.querySelector('[name="course_id"]');
    const deliveryTypeHelpText = document.getElementById('deliveryTypeHelpText');
    const singlePrimarySelect = form.querySelector('[name="faculty_id"]');
    const multiPrimarySelect = form.querySelector('[name="primary_faculty_ids[]"]');
    const multiPrimaryEnabled = !!multiPrimarySelect;

    function deliveryTypeLabel(type) {
      const normalized = String(type || '').trim().toUpperCase().replace(/[_-]+/g, ' ');
      if (['COMBO1', 'COMBO 1', 'CORE A', 'COREA', 'MAJOR COMBO1'].includes(normalized)) return 'COMBO1';
      if (['COMBO2', 'COMBO 2', 'CORE B', 'COREB', 'MAJOR COMBO2'].includes(normalized)) return 'COMBO2';
      if (normalized === 'COMMON') return 'COMMON';
      if (normalized === 'MDC') return 'MDC';
      return normalized;
    }

    function setDeliveryOptionsForCourse(courseId, preferredValue = '') {
      const key = String(courseId || '').trim();
      const options = key !== '' && Array.isArray(deliveryTypeMap[key]) ? deliveryTypeMap[key] : [];
      const normalizedPreferred = (preferredValue || '').trim().toUpperCase().replace(/\s+/g, '-');

      deliveryTypeSelect.innerHTML = '';

      if (options.length === 0) {
        deliveryTypeSelect.appendChild(new Option('--No curriculum delivery type found--', ''));
        deliveryTypeSelect.value = '';
        if (deliveryTypeHelpText) {
          deliveryTypeHelpText.textContent = 'No curriculum delivery type mapping found for this course.';
          deliveryTypeHelpText.classList.add('text-danger');
        }
        return;
      }

      if (options.length > 1) {
        deliveryTypeSelect.appendChild(new Option('--Select--', ''));
      }

      options.forEach((value) => {
        deliveryTypeSelect.appendChild(new Option(deliveryTypeLabel(value), value));
      });

      if (normalizedPreferred !== '' && options.includes(normalizedPreferred)) {
        deliveryTypeSelect.value = normalizedPreferred;
      } else if (options.length === 1) {
        deliveryTypeSelect.value = options[0];
      } else {
        deliveryTypeSelect.value = '';
      }

      if (deliveryTypeHelpText) {
        deliveryTypeHelpText.textContent = options.length > 1 ?
          'Delivery types available from curriculum mapping for this course.' :
          'Delivery type auto-selected from curriculum mapping.';
        deliveryTypeHelpText.classList.remove('text-danger');
      }
    }

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = value ?? '';
      return div.innerHTML;
    }

    function showToast(message, type = 'success') {
      if (!toastElement || !toastBodyElement || typeof bootstrap === 'undefined') {
        return;
      }

      toastElement.classList.remove('bg-success', 'bg-danger');
      toastElement.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
      toastBodyElement.textContent = message;
      bootstrap.Toast.getOrCreateInstance(toastElement).show();
    }

    function renumberRows() {
      const rows = tableBody.querySelectorAll('tr[id^="assignment-row-"]');
      rows.forEach((row, index) => {
        const firstCell = row.querySelector('td');
        if (firstCell) {
          firstCell.textContent = index + 1;
        }
      });
    }

    function getStatusBadge(status) {
      if (Number(status) === 1) {
        return '<span class="badge bg-success">Active</span>';
      }
      return '<span class="badge bg-secondary">Inactive</span>';
    }

    function buildRowHtml(assignment) {
      const safeCourse = escapeHtml(assignment.course_text || '-');
      const safeFaculty = Array.isArray(assignment.primary_faculty_text) && assignment.primary_faculty_text.length ?
        assignment.primary_faculty_text.map((label) => escapeHtml(label)).join(', ') :
        escapeHtml(assignment.faculty_text || '-');
      const safeCoFaculty = Array.isArray(assignment.co_faculty_text) && assignment.co_faculty_text.length ?
        assignment.co_faculty_text.map((label) => escapeHtml(label)).join(', ') : '-';
      const safeDelivery = escapeHtml(assignment.delivery_type || '-');
      const safeShift = escapeHtml(assignment.shift_text || '-');
      const safeGroup = escapeHtml(assignment.allocation_group_label || '-');
      const safeRoom = escapeHtml(assignment.room || '-');
      const safeRemarks = escapeHtml(assignment.remarks || '-');
      const safeRoomData = escapeHtml(assignment.room_raw || '');
      const safeRemarksData = escapeHtml(assignment.remarks_raw || '');

      return `
        <tr id="assignment-row-${assignment.id}">
          <td>0</td>
          <td>${safeCourse}</td>
          <td>${safeFaculty}</td>
          <td>${safeCoFaculty}</td>
          <td>${safeDelivery}</td>
          <td>${safeShift}</td>
          <td>${safeGroup}</td>
          <td>${getStatusBadge(assignment.is_active)}</td>
          <td>${safeRoom}</td>
          <td>${safeRemarks}</td>
          <td>
            <button
              type="button"
              class="btn btn-sm btn-primary js-edit-assignment"
              data-id="${assignment.id}"
              data-course_id="${assignment.course_id}"
              data-faculty_id="${assignment.faculty_id}"
              data-primary_faculty_ids='${escapeHtml(JSON.stringify(Array.isArray(assignment.primary_faculty_ids) ? assignment.primary_faculty_ids : []))}'
              data-co_faculty_ids='${escapeHtml(JSON.stringify(Array.isArray(assignment.co_faculty_ids) ? assignment.co_faculty_ids : []))}'
              data-delivery_type="${escapeHtml(assignment.delivery_type || '')}"
              data-shift_id="${assignment.shift_id || ''}"
              data-status="${assignment.is_active}"
              data-room="${safeRoomData}"
              data-remarks="${safeRemarksData}"
            >Edit</button>
            <form action="{{ route('department.teaching.assignment.delete', 0) }}" method="post" onsubmit="return confirm('Delete this assignment?');" style="display:inline-block;">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="hidden" name="_method" value="DELETE">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      `;
    }

    function updateDeleteFormAction(row, assignmentId) {
      const form = row.querySelector('form[action]');
      if (!form) return;
      form.action = form.action.replace(/\/(\d+)$/, '/' + assignmentId);
    }

    function resetInlineForm() {
      form.reset();
      assignmentIdInput.value = '';
      submitBtn.textContent = 'Save Assignment';
      formTitle.textContent = 'New Teaching Assignment';
      cancelEditBtn.style.display = 'none';
      form.querySelector('[name="course_id"]').dispatchEvent(new Event('change'));
      if (singlePrimarySelect) {
        singlePrimarySelect.dispatchEvent(new Event('change'));
      }
      if (multiPrimarySelect) {
        Array.from(multiPrimarySelect.options).forEach((option) => {
          option.selected = false;
        });
      }
      const coFacultySelect = form.querySelector('[name="co_faculty_ids[]"]');
      if (coFacultySelect) {
        Array.from(coFacultySelect.options).forEach((option) => {
          option.selected = false;
        });
      }
      setDeliveryOptionsForCourse(form.querySelector('[name="course_id"]').value);
    }

    async function submitForm(event) {
      event.preventDefault();

      submitBtn.disabled = true;
      const assignmentId = assignmentIdInput.value;
      const isUpdate = assignmentId !== '';
      const url = isUpdate ? updateUrlTemplate.replace('__ID__', assignmentId) : createUrl;
      const formData = new FormData(form);

      if (isUpdate) {
        formData.append('_method', 'PUT');
      }

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: formData,
        });

        const payload = await response.json();
        if (!response.ok) {
          throw new Error(payload.message || 'Unable to save teaching assignment.');
        }

        const assignment = payload.assignment;
        const assignments = Array.isArray(payload.assignments) && payload.assignments.length > 0 ? payload.assignments : [assignment];
        const emptyRow = document.getElementById('teachingAssignmentEmptyRow');
        if (emptyRow) {
          emptyRow.remove();
        }

        if (isUpdate) {
          const existingRow = document.getElementById('assignment-row-' + assignment.id);
          if (existingRow) {
            existingRow.outerHTML = buildRowHtml(assignment);
          }
        } else {
          tableBody.insertAdjacentHTML('afterbegin', buildRowHtml(assignment));
        }

        const affectedRow = document.getElementById('assignment-row-' + assignment.id);
        if (affectedRow) {
          updateDeleteFormAction(affectedRow, assignment.id);
        }

        renumberRows();
        showToast(payload.message || 'Saved successfully.', 'success');
        resetInlineForm();
      } catch (error) {
        showToast(error.message || 'Unable to save teaching assignment.', 'danger');
      } finally {
        submitBtn.disabled = false;
      }
    }

    tableBody.addEventListener('click', function(event) {
      const editButton = event.target.closest('.js-edit-assignment');
      if (!editButton) {
        return;
      }

      assignmentIdInput.value = editButton.dataset.id || '';
      form.querySelector('[name="course_id"]').value = editButton.dataset.course_id || '';
      if (singlePrimarySelect) {
        singlePrimarySelect.value = editButton.dataset.faculty_id || '';
      }
      if (multiPrimarySelect) {
        let selectedPrimaryIds = [];
        try {
          selectedPrimaryIds = JSON.parse(editButton.dataset.primary_faculty_ids || '[]');
          if (!Array.isArray(selectedPrimaryIds)) {
            selectedPrimaryIds = [];
          }
        } catch (error) {
          selectedPrimaryIds = [];
        }

        if (selectedPrimaryIds.length === 0 && editButton.dataset.faculty_id) {
          selectedPrimaryIds = [Number(editButton.dataset.faculty_id)];
        }

        const selectedPrimarySet = new Set(selectedPrimaryIds.map((value) => String(value)));
        Array.from(multiPrimarySelect.options).forEach((option) => {
          option.selected = selectedPrimarySet.has(String(option.value));
        });
      }
      const coFacultySelect = form.querySelector('[name="co_faculty_ids[]"]');
      if (coFacultySelect) {
        let selectedCoFacultyIds = [];
        try {
          selectedCoFacultyIds = JSON.parse(editButton.dataset.co_faculty_ids || '[]');
          if (!Array.isArray(selectedCoFacultyIds)) {
            selectedCoFacultyIds = [];
          }
        } catch (error) {
          selectedCoFacultyIds = [];
        }

        const selectedSet = new Set(selectedCoFacultyIds.map((value) => String(value)));
        Array.from(coFacultySelect.options).forEach((option) => {
          option.selected = selectedSet.has(String(option.value));
        });
      }
      form.querySelector('[name="shift_id"]').value = editButton.dataset.shift_id || '';
      form.querySelector('[name="status"]').value = editButton.dataset.status || '1';
      form.querySelector('[name="room"]').value = editButton.dataset.room || '';
      form.querySelector('[name="remarks"]').value = editButton.dataset.remarks || '';

      form.querySelector('[name="course_id"]').dispatchEvent(new Event('change'));
      setDeliveryOptionsForCourse(editButton.dataset.course_id || '', editButton.dataset.delivery_type || '');
      if (singlePrimarySelect) {
        singlePrimarySelect.dispatchEvent(new Event('change'));
      }

      submitBtn.textContent = 'Update Assignment';
      formTitle.textContent = 'Edit Teaching Assignment';
      cancelEditBtn.style.display = 'inline-block';
      window.scrollTo({
        top: form.offsetTop - 80,
        behavior: 'smooth'
      });
    });

    cancelEditBtn.addEventListener('click', function() {
      resetInlineForm();
    });

    courseSelect.addEventListener('change', function() {
      setDeliveryOptionsForCourse(this.value);
    });

    setDeliveryOptionsForCourse(courseSelect.value);
    form.addEventListener('submit', submitForm);
  })();
</script>

@include('includes.footer')
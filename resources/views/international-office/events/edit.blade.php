@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Event</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('international-office.events.iqac-reports.index', $event->id) }}" class="btn btn-outline-secondary btn-sm">IQAC Report</a>
      <a href="{{ route('international-office.events.finances.index', $event->id) }}" class="btn btn-outline-info btn-sm">Manage Finances</a>
      <a href="{{ route('international-office.events.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
  </div>

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Update Event Details</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.events.update', $event->id) }}" enctype="multipart/form-data" id="ioEventEditForm">
        @csrf
        @method('PUT')
        <ul class="nav nav-tabs" id="eventEditTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="edit-tab-basic-tab" data-bs-toggle="tab" data-bs-target="#edit-tab-basic" type="button" role="tab" aria-controls="edit-tab-basic" aria-selected="true">1. Basic Info</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="edit-tab-trip-tab" data-bs-toggle="tab" data-bs-target="#edit-tab-trip" type="button" role="tab" aria-controls="edit-tab-trip" aria-selected="false">2. Trip & MoU</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="edit-tab-members-tab" data-bs-toggle="tab" data-bs-target="#edit-tab-members" type="button" role="tab" aria-controls="edit-tab-members" aria-selected="false">3. Members</button>
          </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-3">
          <div class="tab-pane fade show active" id="edit-tab-basic" role="tabpanel" aria-labelledby="edit-tab-basic-tab">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Activity Type <span class="text-danger">*</span></label>
                <select class="form-select" name="activity_type_master_id" required>
                  @foreach($activityTypes as $type)
                  <option value="{{ $type->id }}" {{ (old('activity_type_master_id', $event->activity_type_master_id) == $type->id) ? 'selected' : '' }}>{{ $type->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nature of Activity <span class="text-danger">*</span></label>
                <select class="form-select" name="nature_of_activity" required>
                  <option value="student" {{ old('nature_of_activity', $event->nature_of_activity) === 'student' ? 'selected' : '' }}>Student</option>
                  <option value="faculty" {{ old('nature_of_activity', $event->nature_of_activity) === 'faculty' ? 'selected' : '' }}>Faculty</option>
                  <option value="both" {{ old('nature_of_activity', $event->nature_of_activity) === 'both' ? 'selected' : '' }}>Both</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Approval Type <span class="text-danger">*</span></label>
                <select class="form-select" name="approval_type" required>
                  <option value="personal" {{ old('approval_type', $event->approval_type) === 'personal' ? 'selected' : '' }}>Personal</option>
                  <option value="institutional" {{ old('approval_type', $event->approval_type) === 'institutional' ? 'selected' : '' }}>Institutional</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Department Scope <span class="text-danger">*</span></label>
                <select class="form-select" name="department_scope" id="department_scope" required>
                  <option value="one" {{ old('department_scope', $event->department_scope) === 'one' ? 'selected' : '' }}>One Department</option>
                  <option value="multiple" {{ old('department_scope', $event->department_scope) === 'multiple' ? 'selected' : '' }}>Multiple Departments</option>
                </select>
              </div>
              <div class="col-md-9">
                <label class="form-label">Departments Involved <span class="text-danger">*</span></label>
                @php
                $selectedDepartments = collect(old('department_subject_ids', $event->department_subject_ids ?? []))->map(fn($id) => (int) $id);
                @endphp
                <select class="form-select" name="department_subject_ids[]" id="department_subject_ids" multiple required>
                  @foreach($subjects as $subject)
                  @php
                  $label = trim((string) ($subject->title ?? ''));
                  if ($label === '') {
                  $label = 'Department #' . $subject->id;
                  }
                  @endphp
                  <option value="{{ $subject->id }}" {{ $selectedDepartments->contains((int) $subject->id) ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Visiting Institution Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="visiting_institution_name" value="{{ old('visiting_institution_name', $event->visiting_institution_name) }}" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Institution Contact</label>
                <input type="text" class="form-control" name="visiting_institution_contact" value="{{ old('visiting_institution_contact', $event->visiting_institution_contact) }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Institution Email</label>
                <input type="email" class="form-control" name="visiting_institution_email" value="{{ old('visiting_institution_email', $event->visiting_institution_email) }}">
              </div>
              <div class="col-md-12">
                <label class="form-label">Institution Address</label>
                <textarea class="form-control" name="visiting_institution_address" rows="2" maxlength="1000">{{ old('visiting_institution_address', $event->visiting_institution_address) }}</textarea>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="edit-tab-trip" role="tabpanel" aria-labelledby="edit-tab-trip-tab">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Having Any MoU?</label>
                <select class="form-select" name="has_mou" id="has_mou">
                  <option value="0" {{ old('has_mou', $event->has_mou ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                  <option value="1" {{ old('has_mou', $event->has_mou ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
              </div>
              <div class="col-md-9" id="mou_document_wrap">
                <label class="form-label">Replace MoU Upload</label>
                <input type="file" class="form-control" name="mou_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              </div>

              <div class="col-md-3">
                <label class="form-label">Trip Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="trip_start_date" value="{{ old('trip_start_date', optional($event->trip_start_date)->format('Y-m-d')) }}" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Trip End Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="trip_end_date" value="{{ old('trip_end_date', optional($event->trip_end_date)->format('Y-m-d')) }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Add Geotag Photos (Multiple)</label>
                <input type="file" class="form-control" name="geotag_photos[]" accept=".jpg,.jpeg,.png" multiple>
                <small class="text-muted">Existing: {{ count((array) $event->geotagged_photo_paths) }}</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Add Visit Photos (Multiple)</label>
                <input type="file" class="form-control" name="visit_photos[]" accept=".jpg,.jpeg,.png" multiple>
                <small class="text-muted">Existing: {{ count((array) $event->visit_photo_paths) }}</small>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="edit-tab-members" role="tabpanel" aria-labelledby="edit-tab-members-tab">
            <div class="row g-3">
              <div class="col-12">
                <div class="alert alert-info py-2 mb-0">
                  Finances are managed separately through the Finance Ledger for this event.
                </div>
              </div>

              <div class="col-md-12">
                <label class="form-label">List of Members with Details <span class="text-danger">*</span></label>
                @php
                $members = old('members', is_array($event->members_json) && !empty($event->members_json) ? $event->members_json : [['name' => '', 'designation' => '', 'department' => '', 'contact' => '', 'email' => '']]);
                @endphp
                <div class="table-responsive">
                  <table class="table table-bordered" id="membersTable">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th style="width: 70px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($members as $idx => $member)
                      <tr>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][name]" value="{{ $member['name'] ?? '' }}" data-key="name" required></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][designation]" value="{{ $member['designation'] ?? '' }}" data-key="designation"></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][department]" value="{{ $member['department'] ?? '' }}" data-key="department"></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][contact]" value="{{ $member['contact'] ?? '' }}" data-key="contact"></td>
                        <td><input type="email" class="form-control" name="members[{{ $idx }}][email]" value="{{ $member['email'] ?? '' }}" data-key="email"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-member-btn">X</button></td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addMemberBtn"><i class="fa fa-plus"></i> Add Member</button>
              </div>

              <div class="col-md-12">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="2" maxlength="1000">{{ old('remarks', $event->remarks) }}</textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <button type="button" class="btn btn-outline-secondary" id="prevTabBtn">Previous</button>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="nextTabBtn">Next</button>
            <button type="submit" class="btn btn-primary" id="updateEventBtn"><i class="fa fa-save"></i> Update Event</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  (function() {
    const membersTableBody = document.querySelector('#membersTable tbody');
    const addMemberBtn = document.getElementById('addMemberBtn');
    const departmentScopeEl = document.getElementById('department_scope');
    const departmentsEl = document.getElementById('department_subject_ids');
    const hasMouEl = document.getElementById('has_mou');
    const mouWrap = document.getElementById('mou_document_wrap');
    const tabButtons = Array.from(document.querySelectorAll('#eventEditTabs button[data-bs-toggle="tab"]'));
    const prevTabBtn = document.getElementById('prevTabBtn');
    const nextTabBtn = document.getElementById('nextTabBtn');
    const updateEventBtn = document.getElementById('updateEventBtn');
    let activeTabIndex = 0;

    function reindexMemberRows() {
      const rows = membersTableBody.querySelectorAll('tr');
      rows.forEach((row, index) => {
        row.querySelectorAll('input').forEach((input) => {
          const key = input.getAttribute('data-key');
          if (key) {
            input.name = `members[${index}][${key}]`;
          }
        });
      });
    }

    function updateDepartmentSelectionMode() {
      const scope = departmentScopeEl.value;
      if (scope === 'one') {
        departmentsEl.removeAttribute('multiple');
        if (departmentsEl.selectedOptions.length > 1) {
          const firstVal = departmentsEl.selectedOptions[0].value;
          Array.from(departmentsEl.options).forEach((opt) => {
            opt.selected = opt.value === firstVal;
          });
        }
      } else {
        departmentsEl.setAttribute('multiple', 'multiple');
      }
    }

    function updateMouUploadVisibility() {
      const isYes = hasMouEl.value === '1';
      mouWrap.style.display = isYes ? '' : 'none';
    }

    function showTab(index) {
      if (index < 0 || index >= tabButtons.length) {
        return;
      }

      const tab = new bootstrap.Tab(tabButtons[index]);
      tab.show();
    }

    function updateStepButtons() {
      prevTabBtn.disabled = activeTabIndex === 0;
      nextTabBtn.style.display = activeTabIndex === tabButtons.length - 1 ? 'none' : '';
      updateEventBtn.style.display = activeTabIndex === tabButtons.length - 1 ? '' : 'none';
    }

    addMemberBtn.addEventListener('click', function() {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input type="text" class="form-control" data-key="name" required></td>
        <td><input type="text" class="form-control" data-key="designation"></td>
        <td><input type="text" class="form-control" data-key="department"></td>
        <td><input type="text" class="form-control" data-key="contact"></td>
        <td><input type="email" class="form-control" data-key="email"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-member-btn">X</button></td>
      `;
      membersTableBody.appendChild(row);
      reindexMemberRows();
    });

    document.addEventListener('click', function(e) {
      if (!e.target.classList.contains('remove-member-btn')) {
        return;
      }

      const rows = membersTableBody.querySelectorAll('tr');
      if (rows.length <= 1) {
        return;
      }

      e.target.closest('tr').remove();
      reindexMemberRows();
    });

    departmentScopeEl.addEventListener('change', updateDepartmentSelectionMode);
    hasMouEl.addEventListener('change', updateMouUploadVisibility);
    prevTabBtn.addEventListener('click', function() {
      showTab(activeTabIndex - 1);
    });
    nextTabBtn.addEventListener('click', function() {
      showTab(activeTabIndex + 1);
    });

    tabButtons.forEach((btn, index) => {
      btn.addEventListener('shown.bs.tab', function() {
        activeTabIndex = index;
        updateStepButtons();
      });
    });

    updateDepartmentSelectionMode();
    updateMouUploadVisibility();
    updateStepButtons();
  })();
</script>

@include('includes.footer')
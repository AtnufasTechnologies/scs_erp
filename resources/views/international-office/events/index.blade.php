@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Add International Event</h4>
      <small class="text-muted">Create event records with departments, members, photos, MoU, and finances.</small>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

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

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">Event Details</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.events.store') }}" enctype="multipart/form-data" id="ioEventForm">
        @csrf
        <ul class="nav nav-tabs" id="eventEntryTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-basic-tab" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab" aria-controls="tab-basic" aria-selected="true">1. Basic Info</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-trip-tab" data-bs-toggle="tab" data-bs-target="#tab-trip" type="button" role="tab" aria-controls="tab-trip" aria-selected="false">2. Trip & MoU</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-members-tab" data-bs-toggle="tab" data-bs-target="#tab-members" type="button" role="tab" aria-controls="tab-members" aria-selected="false">3. Members</button>
          </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-3">
          <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-tab">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Activity Type <span class="text-danger">*</span></label>
                <select class="form-select" name="activity_type_master_id" required>
                  <option value="">Select activity type</option>
                  @foreach($activityTypes as $type)
                  <option value="{{ $type->id }}" {{ old('activity_type_master_id') == $type->id ? 'selected' : '' }}>{{ $type->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nature of Activity <span class="text-danger">*</span></label>
                <select class="form-select" name="nature_of_activity" required>
                  <option value="">Select nature</option>
                  <option value="student" {{ old('nature_of_activity') === 'student' ? 'selected' : '' }}>Student</option>
                  <option value="faculty" {{ old('nature_of_activity') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                  <option value="both" {{ old('nature_of_activity') === 'both' ? 'selected' : '' }}>Both</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Approval Type <span class="text-danger">*</span></label>
                <select class="form-select" name="approval_type" required>
                  <option value="">Select approval type</option>
                  <option value="personal" {{ old('approval_type') === 'personal' ? 'selected' : '' }}>Personal</option>
                  <option value="institutional" {{ old('approval_type') === 'institutional' ? 'selected' : '' }}>Institutional</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Department Scope <span class="text-danger">*</span></label>
                <select class="form-select" name="department_scope" id="department_scope" required>
                  <option value="one" {{ old('department_scope') === 'one' ? 'selected' : '' }}>One Department</option>
                  <option value="multiple" {{ old('department_scope', 'multiple') === 'multiple' ? 'selected' : '' }}>Multiple Departments</option>
                </select>
              </div>
              <div class="col-md-9">
                <label class="form-label">Departments Involved <span class="text-danger">*</span></label>
                <select class="select-multiple" name="department_subject_ids[]" id="department_subject_ids" multiple required>
                  @foreach($subjects as $subject)
                  @php
                  $label = trim((string) ($subject->title ?? ''));
                  if ($label === '') {
                  $label = 'Department #' . $subject->id;
                  }
                  @endphp
                  <option value="{{ $subject->id }}" {{ collect(old('department_subject_ids', []))->contains($subject->id) ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple departments.</small>
              </div>

              <div class="col-md-6">
                <label class="form-label">Visiting Institution Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="visiting_institution_name" value="{{ old('visiting_institution_name') }}" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Institution Contact</label>
                <input type="text" class="form-control" name="visiting_institution_contact" value="{{ old('visiting_institution_contact') }}">
              </div>
              <div class="col-md-3">
                <label class="form-label">Institution Email</label>
                <input type="email" class="form-control" name="visiting_institution_email" value="{{ old('visiting_institution_email') }}">
              </div>
              <div class="col-md-12">
                <label class="form-label">Institution Address</label>
                <textarea class="form-control" name="visiting_institution_address" rows="2" maxlength="1000">{{ old('visiting_institution_address') }}</textarea>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-trip" role="tabpanel" aria-labelledby="tab-trip-tab">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Having Any MoU?</label>
                <select class="form-select" name="has_mou" id="has_mou">
                  <option value="0" {{ old('has_mou', '0') === '0' ? 'selected' : '' }}>No</option>
                  <option value="1" {{ old('has_mou') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
              </div>
              <div class="col-md-9" id="mou_document_wrap">
                <label class="form-label">MoU Upload</label>
                <input type="file" class="form-control" name="mou_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="text-muted">Required when MoU is Yes.</small>
              </div>

              <div class="col-md-3">
                <label class="form-label">Trip Start Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="trip_start_date" value="{{ old('trip_start_date') }}" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Trip End Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="trip_end_date" value="{{ old('trip_end_date') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Geotag Photos (Multiple)</label>
                <input type="file" class="form-control" name="geotag_photos[]" accept=".jpg,.jpeg,.png" multiple>
              </div>

              <div class="col-md-6">
                <label class="form-label">Visit Photos (Multiple)</label>
                <input type="file" class="form-control" name="visit_photos[]" accept=".jpg,.jpeg,.png" multiple>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-members" role="tabpanel" aria-labelledby="tab-members-tab">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">List of Members with Details <span class="text-danger">*</span></label>
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
                      @php
                      $oldMembers = old('members', [['name' => '', 'designation' => '', 'department' => '', 'contact' => '', 'email' => '']]);
                      @endphp
                      @foreach($oldMembers as $idx => $member)
                      <tr>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][name]" data-key="name" value="{{ $member['name'] ?? '' }}" required></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][designation]" data-key="designation" value="{{ $member['designation'] ?? '' }}"></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][department]" data-key="department" value="{{ $member['department'] ?? '' }}"></td>
                        <td><input type="text" class="form-control" name="members[{{ $idx }}][contact]" data-key="contact" value="{{ $member['contact'] ?? '' }}"></td>
                        <td><input type="email" class="form-control" name="members[{{ $idx }}][email]" data-key="email" value="{{ $member['email'] ?? '' }}"></td>
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
                <textarea class="form-control" name="remarks" rows="2" maxlength="1000">{{ old('remarks') }}</textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
          <button type="button" class="btn btn-outline-secondary" id="prevTabBtn">Previous</button>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="nextTabBtn">Next</button>
            <button type="submit" class="btn btn-primary" id="saveEventBtn"><i class="fa fa-save"></i> Save Event</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Existing Events</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Trip Dates</th>
              <th>Activity Type</th>
              <th>Institution</th>
              <th>Departments</th>
              <th>Members</th>
              <th>Total Debit</th>
              <th>Total Credit</th>
              <th>Net Expense</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($events as $event)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ optional($event->trip_start_date)->format('d M Y') }} - {{ optional($event->trip_end_date)->format('d M Y') }}</td>
              <td>{{ optional($event->activityType)->title ?: '-' }}</td>
              <td>{{ $event->visiting_institution_name }}</td>
              <td>{{ count((array) $event->department_subject_ids) }}</td>
              <td>{{ count((array) $event->members_json) }}</td>
              <td>{{ number_format((float) ($event->total_debit ?? 0), 2) }}</td>
              <td>{{ number_format((float) ($event->total_credit ?? 0), 2) }}</td>
              <td>{{ number_format((float) ($event->net_expense ?? 0), 2) }}</td>
              <td class="d-flex gap-2">
                <a href="{{ route('international-office.events.iqac-reports.index', $event->id) }}" class="btn btn-sm btn-secondary">IQAC</a>
                <a href="{{ route('international-office.events.finances.index', $event->id) }}" class="btn btn-sm btn-primary ">Finance</a>
                <a href="{{ route('international-office.events.edit', $event->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('international-office.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Delete this event?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center text-muted">No events found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
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
    const tabButtons = Array.from(document.querySelectorAll('#eventEntryTabs button[data-bs-toggle="tab"]'));
    const prevTabBtn = document.getElementById('prevTabBtn');
    const nextTabBtn = document.getElementById('nextTabBtn');
    const saveEventBtn = document.getElementById('saveEventBtn');
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
      saveEventBtn.style.display = activeTabIndex === tabButtons.length - 1 ? '' : 'none';
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

    Array.from(membersTableBody.querySelectorAll('tr')).forEach((row) => {
      row.querySelectorAll('input').forEach((input) => {
        const match = input.name.match(/members\[\d+\]\[(.+)\]/);
        if (match && match[1]) {
          input.setAttribute('data-key', match[1]);
        }
      });
    });

    updateDepartmentSelectionMode();
    updateMouUploadVisibility();
    updateStepButtons();
  })();
</script>

@include('includes.footer')
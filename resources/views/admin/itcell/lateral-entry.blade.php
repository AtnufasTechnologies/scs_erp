@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">ITCELL Lateral Entry</h4>
      <p class="text-muted mb-0">Add a student directly to the master, enroll into a program, generate roll number, and keep an audit trail.</p>
    </div>
    <a href="{{ route('itcell.lateral-entry.audit') }}" class="btn btn-outline-secondary">View Audit Trail</a>
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
      <form action="{{ route('itcell.lateral-entry.store') }}" method="POST" enctype="multipart/form-data" id="lateralEntryForm">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-12">
            <h5 class="mb-1">Use Existing Admission Application</h5>
            <p class="text-muted mb-2">Enter application code to fetch available details automatically.</p>
          </div>
          <div class="col-md-4">
            <label class="form-label">Application Code</label>
            <input type="text" class="form-control" id="applicationCodeLookup" placeholder="Enter application code" value="{{ old('application_code') }}">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-outline-primary w-100" id="fetchApplicationBtn">Fetch Data</button>
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="small text-muted" id="applicationFetchStatus">No application selected yet.</div>
          </div>

          <div class="col-12">
            <div class="card border-0 bg-light" id="applicationPreviewCard" style="display:none;">
              <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h6 class="mb-0">Fetched Application Preview</h6>
                  <span class="badge bg-success" id="previewApplicationCode">-</span>
                </div>
                <div class="row g-2 small">
                  <div class="col-md-4"><strong>Name:</strong> <span id="previewName">-</span></div>
                  <div class="col-md-4"><strong>Phone:</strong> <span id="previewPhone">-</span></div>
                  <div class="col-md-4"><strong>Email:</strong> <span id="previewEmail">-</span></div>
                  <div class="col-md-4"><strong>Nationality:</strong> <span id="previewNationality">-</span></div>
                  <div class="col-md-4"><strong>Campus:</strong> <span id="previewCampus">-</span></div>
                  <div class="col-md-4"><strong>Batch:</strong> <span id="previewBatch">-</span></div>
                  <div class="col-md-4"><strong>Program Type:</strong> <span id="previewProgramType">-</span></div>
                  <div class="col-md-6"><strong>Department:</strong> <span id="previewDepartment">-</span></div>
                  <div class="col-md-6"><strong>Program:</strong> <span id="previewProgram">-</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <input type="hidden" name="application_code" id="applicationCode" value="{{ old('application_code') }}">

        <hr>
        <h5 class="mb-3">Personal Information</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-control" required>
              <option value="">Select gender</option>
              <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Male</option>
              <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile_no" class="form-control" value="{{ old('mobile_no') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Email</label>
            <input type="email" name="mail_id" class="form-control" value="{{ old('mail_id') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Blood Group</label>
            <select name="blood_group_id" class="form-select">
              <option value="">Select blood group</option>
              @foreach($bloodGroups as $blood)
              <option value="{{ $blood->id }}" {{ old('blood_group_id') == $blood->id ? 'selected' : '' }}>{{ $blood->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Religion</label>
            <select name="religion" class="form-select">
              <option value="">Select religion</option>
              @foreach($religions as $religion)
              <option value="{{ $religion->id }}" {{ old('religion') == $religion->id ? 'selected' : '' }}>{{ $religion->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Nationality</label>
            <select name="nationality" class="dselect-example" id="nationalitySelect">
              <option value="">Select nationality</option>
              @foreach($nationalities as $nationality)
              <option value="{{ $nationality->id }}" {{ old('nationality') == $nationality->id ? 'selected' : '' }}>{{ $nationality->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Tongue</label>
            <input type="text" name="mother_tongue" class="form-control" value="{{ old('mother_tongue') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Caste</label>
            <input type="text" name="caste" class="form-control" value="{{ old('caste') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Aadhaar No</label>
            <input type="text" name="aadhar_no" class="form-control" value="{{ old('aadhar_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Upload Application Form (Optional)</label>
            <input type="file" name="application_form_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">Supported: PDF/JPG/JPEG/PNG, max 5MB.</small>
          </div>
        </div>

        <hr>
        <h5 class="mb-3">Academic Information (Lateral Controls)</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Department *</label>
            <select name="department" id="departmentSelect" class="dselect-example">
              <option value="">Select department</option>
              @foreach($departments as $department)
              <option value="{{ $department->id }}" {{ old('department') == $department->id ? 'selected' : '' }}>{{ $department->title }} - {{$department->campus_id == 1 ? 'Sonada' : 'Siliguri'}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Campus *</label>
            <select name="campus_id" id="campusSelect" class="form-control" required>
              <option value="">Select campus</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Program Type *</label>
            <select name="program_type" id="programTypeSelect" class="form-control" required>
              <option value="">Program Type</option>
              @foreach($programstype as $prog)
              <option value="{{ $prog->id }}" {{ old('program_type') == $prog->id ? 'selected' : '' }}>{{ $prog->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Batch *</label>
            <select name="batch" id="batchSelect" class="form-control" required>
              <option value="">Select batch</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ old('batch') == $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Enrolled Program *</label>
            <select name="new_program_id" id="programSelect" class="dselect-example" required>
              <option value="">Select campus and batch first</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Admission Date</label>
            <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date') }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">Current Year</label>
            <input type="number" name="current_year" class="form-control" min="1" max="6" value="{{ old('current_year', 2) }}">
          </div>

          <div class="col-md-2">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select">
              @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}" {{ old('semester') == $sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-5">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" rows="1" class="form-control" placeholder="Why this student is being added via lateral entry">{{ old('remarks') }}</textarea>
          </div>
        </div>

        <hr>
        <h5 class="mb-3">Parent and Guardian Information</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Father Name</label>
            <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Father Contact</label>
            <input type="text" name="fr_mobile_no" class="form-control" value="{{ old('fr_mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Father Occupation</label>
            <input type="text" name="fr_occupation" class="form-control" value="{{ old('fr_occupation') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Name</label>
            <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Mother Contact</label>
            <input type="text" name="mr_mobile_no" class="form-control" value="{{ old('mr_mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Occupation</label>
            <input type="text" name="mr_occupation" class="form-control" value="{{ old('mr_occupation') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Guardian Name</label>
            <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Guardian Contact</label>
            <input type="text" name="guardian_mobile_no" class="form-control" value="{{ old('guardian_mobile_no') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Annual Income</label>
            <input type="number" min="0" name="annual_income" class="form-control" value="{{ old('annual_income') }}">
          </div>
        </div>

        <hr>
        <h5 class="mb-3">Address Information</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Permanent Address</label>
            <textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">District</label>
            <input type="text" name="district" class="form-control" value="{{ old('district') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Pincode</label>
            <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}">
          </div>
        </div>

        <hr>
        <h5 class="mb-3">Marks Information</h5>
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">Class X %</label>
            <input type="number" step="0.01" min="0" max="100" name="x_percentage" class="form-control" value="{{ old('x_percentage') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Class XII %</label>
            <input type="number" step="0.01" min="0" max="100" name="xii_percentage" class="form-control" value="{{ old('xii_percentage') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">UG %</label>
            <input type="number" step="0.01" min="0" max="100" name="ug_percentage" class="form-control" value="{{ old('ug_percentage') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 1</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa1" class="form-control" value="{{ old('sgpa1') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 2</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa2" class="form-control" value="{{ old('sgpa2') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 3</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa3" class="form-control" value="{{ old('sgpa3') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 4</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa4" class="form-control" value="{{ old('sgpa4') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 5</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa5" class="form-control" value="{{ old('sgpa5') }}">
          </div>
          <div class="col-md-1">
            <label class="form-label">SGPA 6</label>
            <input type="number" step="0.01" min="0" max="10" name="sgpa6" class="form-control" value="{{ old('sgpa6') }}">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-success">Create Lateral Entry Student</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">Recent Audit Trail</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Added By</th>
              <th>Type</th>
              <th>Remarks</th>
              <th>Application Form</th>
              <th>Snapshot</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            @forelse($auditLogs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->student->first_name ?? '' }} {{ $log->student->last_name ?? '' }} ({{ $log->student->roll_no ?? 'N/A' }})</td>
              <td>{{ $log->user->name ?? 'System' }}</td>
              <td>{{ ucfirst(str_replace('-', ' ', $log->entry_type)) }}</td>
              <td>{{ $log->remarks }}</td>
              <td>
                @if(!empty($log->application_form_path))
                <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ Storage::disk('public')->url($log->application_form_path) }}">View File</a>
                @elseif(!empty($log->sourced_application_code))
                <span class="badge bg-light text-dark">{{ $log->sourced_application_code }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @php($snapshot = $log->application_snapshot ?? [])
                @if(!empty($snapshot))
                <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#snapRow{{ $log->id }}" aria-expanded="false" aria-controls="snapRow{{ $log->id }}">View</button>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '' }}</td>
            </tr>
            @if(!empty($snapshot))
            <tr class="collapse" id="snapRow{{ $log->id }}">
              <td colspan="8" class="bg-light">
                <div class="row g-2 small">
                  <div class="col-md-3"><strong>City:</strong> {{ data_get($snapshot, 'address.city', '-') }}</div>
                  <div class="col-md-3"><strong>District:</strong> {{ data_get($snapshot, 'address.district', '-') }}</div>
                  <div class="col-md-3"><strong>State:</strong> {{ data_get($snapshot, 'address.state', '-') }}</div>
                  <div class="col-md-3"><strong>Pincode:</strong> {{ data_get($snapshot, 'address.pincode', '-') }}</div>
                  <div class="col-md-2"><strong>X%:</strong> {{ data_get($snapshot, 'academic.x_percentage', '-') }}</div>
                  <div class="col-md-2"><strong>XII%:</strong> {{ data_get($snapshot, 'academic.xii_percentage', '-') }}</div>
                  <div class="col-md-2"><strong>UG%:</strong> {{ data_get($snapshot, 'academic.ug_percentage', '-') }}</div>
                  <div class="col-md-1"><strong>S1:</strong> {{ data_get($snapshot, 'academic.sgpa1', '-') }}</div>
                  <div class="col-md-1"><strong>S2:</strong> {{ data_get($snapshot, 'academic.sgpa2', '-') }}</div>
                  <div class="col-md-1"><strong>S3:</strong> {{ data_get($snapshot, 'academic.sgpa3', '-') }}</div>
                  <div class="col-md-1"><strong>S4:</strong> {{ data_get($snapshot, 'academic.sgpa4', '-') }}</div>
                  <div class="col-md-1"><strong>S5:</strong> {{ data_get($snapshot, 'academic.sgpa5', '-') }}</div>
                  <div class="col-md-1"><strong>S6:</strong> {{ data_get($snapshot, 'academic.sgpa6', '-') }}</div>
                </div>
              </td>
            </tr>
            @endif
            @empty
            <tr>
              <td colspan="8" class="text-muted">No audit records yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('lateralEntryForm');
    const campusSelect = document.getElementById('campusSelect');
    const batchSelect = document.getElementById('batchSelect');
    const subjectSelect = document.getElementById('departmentSelect');
    const programSelect = document.getElementById('programSelect');
    const programTypeSelect = document.getElementById('programTypeSelect');
    const fetchBtn = document.getElementById('fetchApplicationBtn');
    const applicationCodeLookup = document.getElementById('applicationCodeLookup');
    const applicationCodeHidden = document.getElementById('applicationCode');
    const fetchStatus = document.getElementById('applicationFetchStatus');
    const previewCard = document.getElementById('applicationPreviewCard');
    const previewApplicationCode = document.getElementById('previewApplicationCode');
    const previewName = document.getElementById('previewName');
    const previewPhone = document.getElementById('previewPhone');
    const previewEmail = document.getElementById('previewEmail');
    const previewNationality = document.getElementById('previewNationality');
    const previewCampus = document.getElementById('previewCampus');
    const previewBatch = document.getElementById('previewBatch');
    const previewProgramType = document.getElementById('previewProgramType');
    const previewDepartment = document.getElementById('previewDepartment');
    const previewProgram = document.getElementById('previewProgram');

    if (!campusSelect || !batchSelect || !subjectSelect || !programSelect || !programTypeSelect || !form) {
      return;
    }

    const initProgramSearchSelect = () => {
      if (typeof window.dselect !== 'function') {
        return;
      }

      const nextEl = programSelect.nextElementSibling;
      if (nextEl && nextEl.classList.contains('dselect-wrapper')) {
        nextEl.remove();
      }

      window.dselect(programSelect, {
        search: true,
        creatable: false,
        clearable: true,
        maxHeight: '300px',
        size: 'sm'
      });
    };

    const resetPrograms = () => {
      programSelect.innerHTML = '<option value="">Select campus and batch first</option>';
      initProgramSearchSelect();
    };

    const loadPrograms = async (preselectedProgramId = null) => {
      const campusId = campusSelect.value;
      const batchId = batchSelect.value;

      if (!campusId || !batchId) {
        resetPrograms();
        return;
      }

      programSelect.innerHTML = '<option value="">Loading programs...</option>';

      try {
        const response = await fetch("{{ route('itcell.lateral-entry.programs') }}?campus_id=" + encodeURIComponent(campusId) + "&batch_id=" + encodeURIComponent(batchId), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Unable to load programs');
        }

        const data = await response.json();
        programSelect.innerHTML = '';

        if (!data.success || !Array.isArray(data.programs) || data.programs.length === 0) {
          programSelect.innerHTML = '<option value="">No enrolled programs found</option>';
          initProgramSearchSelect();
          return;
        }

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select program';
        programSelect.appendChild(defaultOption);

        data.programs.forEach(function(program) {
          const option = document.createElement('option');
          option.value = program.id;
          option.textContent = (program.code ? program.code + ' - ' : '') + (program.name || '');
          if (preselectedProgramId && String(preselectedProgramId) === String(program.id)) {
            option.selected = true;
          }
          programSelect.appendChild(option);
        });

        initProgramSearchSelect();
      } catch (error) {
        programSelect.innerHTML = '<option value="">Unable to load programs</option>';
        initProgramSearchSelect();
      }
    };

    const setField = (name, value) => {
      if (value === null || value === undefined) {
        return;
      }
      const input = form.querySelector('[name="' + name + '"]');
      if (!input) {
        return;
      }
      input.value = value;
      input.dispatchEvent(new Event('change', {
        bubbles: true
      }));
    };

    const getSelectedText = (selectEl) => {
      if (!selectEl || !selectEl.options || selectEl.selectedIndex < 0) {
        return '-';
      }
      const text = (selectEl.options[selectEl.selectedIndex] || {}).text || '';
      return text.trim() || '-';
    };

    const setPreviewValue = (el, value) => {
      if (!el) {
        return;
      }
      el.textContent = (value && String(value).trim() !== '') ? String(value).trim() : '-';
    };

    const showPreviewFromData = (app, appCode) => {
      setPreviewValue(previewApplicationCode, app.application_code || appCode || '-');
      setPreviewValue(previewName, ((app.first_name || '') + ' ' + (app.last_name || '')).trim());
      setPreviewValue(previewPhone, app.mobile_no || '-');
      setPreviewValue(previewEmail, app.mail_id || '-');
      setPreviewValue(previewNationality, getSelectedText(document.getElementById('nationalitySelect')));
      setPreviewValue(previewCampus, getSelectedText(campusSelect));
      setPreviewValue(previewBatch, getSelectedText(batchSelect));
      setPreviewValue(previewProgramType, getSelectedText(programTypeSelect));
      setPreviewValue(previewDepartment, getSelectedText(document.getElementById('departmentSelect')));
      setPreviewValue(previewProgram, getSelectedText(programSelect));
      if (previewCard) {
        previewCard.style.display = 'block';
      }
    };

    const hidePreview = () => {
      if (previewCard) {
        previewCard.style.display = 'none';
      }
    };

    const fetchApplicationData = async () => {
      const appCode = (applicationCodeLookup ? applicationCodeLookup.value : '').trim();
      if (!appCode) {
        fetchStatus.textContent = 'Please enter an application code.';
        fetchStatus.classList.add('text-danger');
        fetchStatus.classList.remove('text-success');
        return;
      }

      fetchStatus.textContent = 'Fetching application data...';
      fetchStatus.classList.remove('text-danger', 'text-success');

      try {
        const response = await fetch("{{ route('itcell.lateral-entry.application-data') }}?application_code=" + encodeURIComponent(appCode), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const payload = await response.json();
        if (!response.ok || !payload.success || !payload.application) {
          throw new Error(payload.message || 'Unable to fetch application data');
        }

        const app = payload.application;
        setField('first_name', app.first_name || '');
        setField('last_name', app.last_name || '');
        setField('gender', app.gender || '');
        setField('mobile_no', app.mobile_no || '');
        setField('mail_id', app.mail_id || '');
        setField('dob', app.dob || '');
        setField('blood_group_id', app.blood_group_id || '');
        setField('religion', app.religion || '');
        setField('nationality', app.nationality || '');
        setField('mother_tongue', app.mother_tongue || '');
        setField('caste', app.caste || '');
        setField('aadhar_no', app.aadhar_no || '');
        setField('father_name', app.father_name || '');
        setField('mother_name', app.mother_name || '');
        setField('guardian_name', app.guardian_name || '');
        setField('fr_mobile_no', app.fr_mobile_no || '');
        setField('mr_mobile_no', app.mr_mobile_no || '');
        setField('guardian_mobile_no', app.guardian_mobile_no || '');
        setField('fr_occupation', app.fr_occupation || '');
        setField('mr_occupation', app.mr_occupation || '');
        setField('annual_income', app.annual_income || '');
        setField('address', app.address || '');
        setField('city', app.city || '');
        setField('district', app.district || '');
        setField('state', app.state || '');
        setField('pincode', app.pincode || '');
        setField('x_percentage', app.x_percentage || '');
        setField('xii_percentage', app.xii_percentage || '');
        setField('ug_percentage', app.ug_percentage || '');
        setField('sgpa1', app.sgpa1 || '');
        setField('sgpa2', app.sgpa2 || '');
        setField('sgpa3', app.sgpa3 || '');
        setField('sgpa4', app.sgpa4 || '');
        setField('sgpa5', app.sgpa5 || '');
        setField('sgpa6', app.sgpa6 || '');

        setField('department', app.department || '');
        setField('campus_id', app.campus_id || '');
        setField('program_type', app.program_type || '');
        setField('batch', app.batch || '');

        if (applicationCodeHidden) {
          applicationCodeHidden.value = app.application_code || appCode;
        }

        await loadPrograms(app.new_program_id || null);
        showPreviewFromData(app, appCode);

        fetchStatus.textContent = 'Application data loaded successfully.';
        fetchStatus.classList.add('text-success');
        fetchStatus.classList.remove('text-danger');
      } catch (error) {
        hidePreview();
        fetchStatus.textContent = error.message || 'Unable to fetch application data.';
        fetchStatus.classList.add('text-danger');
        fetchStatus.classList.remove('text-success');
      }
    };

    campusSelect.addEventListener('change', () => loadPrograms());
    subjectSelect.addEventListener('change', () => loadPrograms());
    programTypeSelect.addEventListener('change', () => loadPrograms());
    batchSelect.addEventListener('change', () => loadPrograms());

    if (fetchBtn) {
      fetchBtn.addEventListener('click', fetchApplicationData);
    }

    initProgramSearchSelect();
    loadPrograms("{{ old('new_program_id') }}");
  });
</script>
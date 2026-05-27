@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FDP Programs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.index') }}">FDP List</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.show', $fdpProgram->id) }}">{{ $fdpProgram->program_code }}</a></li>
            <li class="breadcrumb-item active">Add Participant</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.fdp.show', $fdpProgram->id) }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back to Program
        </a>
      </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Program Summary --}}
    <div class="card mb-4">
      <div class="card-body py-3">
        <div class="row align-items-center">
          <div class="col-md-4">
            <strong>{{ $fdpProgram->program_title }}</strong><br>
            <small class="text-muted">{{ $fdpProgram->program_code }} &bull; {{ ucfirst($fdpProgram->program_type) }}</small>
          </div>
          <div class="col-md-4">
            <small class="text-muted">
              <i class="fas fa-calendar-alt me-1"></i>
              {{ $fdpProgram->start_date ? \Carbon\Carbon::parse($fdpProgram->start_date)->format('d M Y') : 'N/A' }}
              &mdash;
              {{ $fdpProgram->end_date ? \Carbon\Carbon::parse($fdpProgram->end_date)->format('d M Y') : 'N/A' }}
            </small>
          </div>
          <div class="col-md-4">
            @if($fdpProgram->max_participants)
            <small class="text-muted">
              <i class="fas fa-users me-1"></i>
              {{ $fdpProgram->participants()->count() }} / {{ $fdpProgram->max_participants }} registered
            </small>
            @else
            <small class="text-muted">
              <i class="fas fa-users me-1"></i>
              {{ $fdpProgram->participants()->count() }} registered
            </small>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Add Participant Form --}}
    <div class="card">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Participants</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('hr.fdp.participants.store', $fdpProgram->id) }}" method="POST">
          @csrf

          {{-- Options Row --}}
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Participant Type <span class="text-danger">*</span></label>
              <select name="participant_type" id="participant_type"
                class="form-select @error('participant_type') is-invalid @enderror" required>
                <option value="faculty" {{ old('participant_type', 'faculty') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                <option value="staff" {{ old('participant_type') == 'staff' ? 'selected' : '' }}>Staff</option>
              </select>
              @error('participant_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Initial Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="registered" {{ old('status', 'registered') == 'registered' ? 'selected' : '' }}>Registered</option>
                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                rows="1" maxlength="500"
                placeholder="Optional notes applied to all selected participants...">{{ old('remarks') }}</textarea>
              @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          @error('faculty_ids')
          <div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
          @enderror

          {{-- Toolbar --}}
          <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <input type="text" id="searchBox" class="form-control form-control-sm" style="max-width:240px;"
              placeholder="Search by name or code...">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllFaculty()">
              <i class="fas fa-check-double me-1"></i>Select All Faculty
            </button>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="selectAllStaff()">
              <i class="fas fa-check-double me-1"></i>Select All Staff
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
              <i class="fas fa-times me-1"></i>Clear All
            </button>
            <span class="ms-auto text-muted small" id="selectedCount">0 selected</span>
          </div>

          {{-- Faculty Checkbox Table --}}
          <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
            <table class="table table-hover table-sm mb-0" id="facultyTable">
              <thead class="table-light sticky-top">
                <tr>
                  <th width="40">
                    <input type="checkbox" id="masterCheck" title="Toggle all visible"
                      onchange="toggleVisible(this.checked)">
                  </th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Email</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($faculties as $faculty)
                @php $alreadyIn = in_array($faculty->id, $registeredFacultyIds); @endphp
                <tr class="faculty-row {{ $alreadyIn ? 'table-light text-muted' : '' }}"
                  data-name="{{ strtolower($faculty->FIRST_NAME . ' ' . $faculty->LAST_NAME) }}"
                  data-code="{{ strtolower($faculty->FACULTY_CODE ?? $faculty->USER_CODE ?? '') }}">
                  <td>
                    <input type="checkbox" name="faculty_ids[]" value="{{ $faculty->id }}"
                      class="faculty-check"
                      {{ in_array($faculty->id, old('faculty_ids', [])) ? 'checked' : '' }}
                      {{ $alreadyIn ? 'disabled' : '' }}
                      onchange="updateCount()">
                  </td>
                  <td>
                    {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                    @if($alreadyIn)<span class="badge bg-secondary ms-1" style="font-size:.65rem;">Registered</span>@endif
                  </td>
                  <td><small class="text-muted">{{ $faculty->FACULTY_CODE ?? $faculty->USER_CODE ?? 'N/A' }}</small></td>
                  <td><small class="text-muted">{{ $faculty->EMAIL ?? '' }}</small></td>
                  <td>
                    @if($alreadyIn)
                    <span class="badge bg-secondary">Already In</span>
                    @else
                    <span class="badge bg-success">Available</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-user-plus me-1"></i>Add Selected Participants
            </button>
            <a href="{{ route('hr.fdp.show', $fdpProgram->id) }}" class="btn btn-secondary">
              <i class="fas fa-times me-1"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>

    {{-- Already Registered Participants --}}
    @if($fdpProgram->participants()->count() > 0)
    <div class="card mt-4">
      <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="fas fa-users me-1"></i>Already Registered ({{ $fdpProgram->participants()->count() }})</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Type</th>
                <th>Status</th>
                <th>Registered On</th>
              </tr>
            </thead>
            <tbody>
              @foreach($fdpProgram->participants as $participant)
              <tr>
                <td>
                  @if($participant->faculty)
                  {{ $participant->faculty->FIRST_NAME }} {{ $participant->faculty->LAST_NAME }}
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td><small class="text-muted">{{ $participant->faculty->FACULTY_CODE ?? $participant->faculty->USER_CODE ?? 'N/A' }}</small></td>
                <td><span class="badge bg-light text-dark">{{ ucfirst($participant->participant_type) }}</span></td>
                <td>
                  @php $pColors = ['registered'=>'warning','approved'=>'info','completed'=>'success','cancelled'=>'danger']; @endphp
                  <span class="badge bg-{{ $pColors[$participant->status] ?? 'secondary' }}">{{ ucfirst($participant->status) }}</span>
                </td>
                <td><small>{{ $participant->registration_date ? \Carbon\Carbon::parse($participant->registration_date)->format('d M Y') : 'N/A' }}</small></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

  </main>
</div>

<script>
  // --- Search ---
  document.getElementById('searchBox').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.faculty-row').forEach(row => {
      const match = row.dataset.name.includes(q) || row.dataset.code.includes(q);
      row.style.display = match ? '' : 'none';
    });
    updateMasterCheck();
  });

  // --- Select All Faculty (checks available rows whose "Status" cell says Available) ---
  function selectAllFaculty() {
    document.querySelectorAll('.faculty-row:not([style*="display: none"])').forEach(row => {
      const cb = row.querySelector('.faculty-check');
      if (cb && !cb.disabled) cb.checked = true;
    });
    updateCount();
    updateMasterCheck();
  }

  // --- Select All Staff ---
  // (faculty list from controller doesn't separate staff; this selects all visible available rows)
  // If you later add a data-type attribute per row, filter on that here.
  function selectAllStaff() {
    document.querySelectorAll('.faculty-row:not([style*="display: none"])').forEach(row => {
      const cb = row.querySelector('.faculty-check');
      if (cb && !cb.disabled) cb.checked = true;
    });
    // Also switch participant type to staff
    document.getElementById('participant_type').value = 'staff';
    updateCount();
    updateMasterCheck();
  }

  // --- Clear All ---
  function clearAll() {
    document.querySelectorAll('.faculty-check').forEach(cb => {
      if (!cb.disabled) cb.checked = false;
    });
    updateCount();
    updateMasterCheck();
  }

  // --- Toggle visible rows via master checkbox ---
  function toggleVisible(checked) {
    document.querySelectorAll('.faculty-row:not([style*="display: none"]) .faculty-check').forEach(cb => {
      if (!cb.disabled) cb.checked = checked;
    });
    updateCount();
  }

  // --- Update selected counter ---
  function updateCount() {
    const n = document.querySelectorAll('.faculty-check:checked').length;
    document.getElementById('selectedCount').textContent = n + ' selected';
  }

  function updateMasterCheck() {
    const visible = document.querySelectorAll('.faculty-row:not([style*="display: none"]) .faculty-check:not(:disabled)');
    const checked = document.querySelectorAll('.faculty-row:not([style*="display: none"]) .faculty-check:checked');
    const master = document.getElementById('masterCheck');
    master.indeterminate = checked.length > 0 && checked.length < visible.length;
    master.checked = visible.length > 0 && checked.length === visible.length;
  }
</script>

@include('includes.footer')
@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">ITCELL Student Campus Transfer</h4>
      <p class="text-muted mb-0">Transfer enrolled students between Sonada and Siliguri without changing roll number, while preserving transfer history.</p>
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

  <div class="alert alert-info">
    <strong>Rules enforced:</strong> Roll number is never changed. Only campus and enrolled program are updated, and every transfer is logged.
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.student-campus-transfer.index') }}" class="row g-3 align-items-end" id="studentTransferFilterForm">
        <div class="col-md-3">
          <label class="form-label">Current Campus</label>
          <select name="campus_id" class="form-select" id="filterCampusId" required>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (int)$selectedCampusId === (int)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Current Program</label>
          <select name="program_id" class="dselect-example" id="filterProgramId">
            <option value="">All Enrolled Programs</option>
            @foreach(($enrolledPrograms ?? collect()) as $program)
            <option value="{{ $program->id }}" {{ (int)$selectedProgramId === (int)$program->id ? 'selected' : '' }}>
              {{ ($program->code ?? '') }} - {{ $program->name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Search Student</label>
          <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Roll no, register no, application code, or name">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Generate List</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-transparent">
      <h5 class="mb-0">Eligible Students</h5>
    </div>
    <div class="card-body">
      @if($students->isEmpty())
      <div class="alert alert-warning mb-0">No students found for the selected filters.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Roll No</th>
              <th>Student</th>
              <th>Batch</th>
              <th>Current Campus</th>
              <th>Current Program</th>
              <th>Transfer Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($students as $index => $student)
            <tr>
              <td>{{ $students->firstItem() + $index }}</td>
              <td><strong>{{ $student->roll_no ?: 'N/A' }}</strong></td>
              <td>
                {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                @if(!empty($student->register_no))
                <div class="small text-muted">Reg: {{ $student->register_no }}</div>
                @endif
              </td>
              <td>{{ $student->batchmaster->batch_name ?? '-' }}</td>
              <td>{{ $student->campusmaster->name ?? 'N/A' }}</td>
              <td>
                @if($student->stdprogramenrolled)
                {{ $student->stdprogramenrolled->code }} - {{ $student->stdprogramenrolled->name }}
                @else
                <span class="text-muted">Not set</span>
                @endif
              </td>
              <td>
                <form method="POST" action="{{ route('itcell.student-campus-transfer.store') }}" class="row g-2 transfer-form">
                  @csrf
                  <input type="hidden" name="student_id" value="{{ $student->id }}">
                  <div class="col-12 col-lg-4">
                    <select name="to_campus_id" class="form-select form-select-sm target-campus-select" required>
                      <option value="">Target Campus</option>
                      @foreach($campuses as $campus)
                      @if((int)$campus->id !== (int)$student->campus_id)
                      <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-lg-3">
                    <select name="to_batch_id" class="form-select form-select-sm target-batch-select" required>
                      <option value="">Target Batch</option>
                      @foreach(($batches ?? collect()) as $batch)
                      <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-lg-3">
                    <select name="to_program_id" class="form-select form-select-sm dselect-example target-program-select" required>
                      <option value="">Target Program</option>
                    </select>
                  </div>

                  <div class="col-12 col-lg-1 d-grid">
                    <button type="submit" class="btn btn-sm btn-success">Move</button>
                  </div>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $students->links('vendor.pagination.bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-transparent">
      <h5 class="mb-0">Recent Transfer History</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Roll No</th>
              <th>From</th>
              <th>To</th>
              <th>Changed By</th>
              <th>Reason</th>
              <th>When</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentTransfers as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ trim(($log->student->first_name ?? '') . ' ' . ($log->student->last_name ?? '')) }}</td>
              <td><strong>{{ $log->roll_no ?: 'N/A' }}</strong></td>
              <td>
                {{ $log->fromCampus->name ?? 'N/A' }}
                <div class="small text-muted">{{ ($log->fromProgram->code ?? '') }} {{ ($log->fromProgram->name ?? '') }}</div>
              </td>
              <td>
                {{ $log->toCampus->name ?? 'N/A' }}
                <div class="small text-muted">{{ ($log->toProgram->code ?? '') }} {{ ($log->toProgram->name ?? '') }}</div>
              </td>
              <td>{{ $log->changedByUser->name ?? 'System' }}</td>
              <td>{{ $log->reason ?: '-' }}</td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-muted">No transfer history found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const enrolledProgramEndpoint = "{{ route('itcell.student-campus-transfer.programs') }}";

    function initDselectForSelect(selectEl) {
      if (!selectEl || typeof window.dselect !== 'function' || !selectEl.classList.contains('dselect-example')) {
        return;
      }

      // dselect builds a sibling wrapper; remove it before re-init after option repaints.
      const nextEl = selectEl.nextElementSibling;
      if (nextEl && nextEl.classList.contains('dselect-wrapper')) {
        nextEl.remove();
      }

      window.dselect(selectEl, {
        search: true,
        creatable: true,
        clearable: true,
        maxHeight: '300px',
        size: 'sm',
      });
    }

    function renderFilterProgramOptions(programSelect, programs, selectedProgramId) {
      const selectedValue = String(selectedProgramId || '');
      let hasSelectedValue = false;

      programSelect.innerHTML = '<option value="">All Enrolled Programs</option>';

      (programs || []).forEach(function(program) {
        const value = String(program.id || '');
        const option = document.createElement('option');
        option.value = value;
        option.textContent = ((program.code || '') + ' - ' + (program.name || '')).trim();
        if (selectedValue !== '' && value === selectedValue) {
          option.selected = true;
          hasSelectedValue = true;
        }
        programSelect.appendChild(option);
      });

      if (selectedValue !== '' && !hasSelectedValue) {
        programSelect.value = '';
      }
    }

    function renderTransferProgramOptions(programSelect, programs, selectedProgramId) {
      const selectedValue = String(selectedProgramId || '');
      let hasSelectedValue = false;

      programSelect.innerHTML = '<option value="">Target Program</option>';

      (programs || []).forEach(function(program) {
        const value = String(program.id || '');
        const option = document.createElement('option');
        option.value = value;
        option.textContent = ((program.code || '') + ' - ' + (program.name || '')).trim();
        if (selectedValue !== '' && value === selectedValue) {
          option.selected = true;
          hasSelectedValue = true;
        }
        programSelect.appendChild(option);
      });

      if (selectedValue !== '' && !hasSelectedValue) {
        programSelect.value = '';
      }

      initDselectForSelect(programSelect);
    }

    async function refreshFilterPrograms() {
      const campusSelect = document.getElementById('filterCampusId');
      const programSelect = document.getElementById('filterProgramId');

      if (!campusSelect || !programSelect) {
        return;
      }

      const selectedProgramId = programSelect.value || '';
      const campusId = String(campusSelect.value || '');

      if (campusId === '') {
        renderFilterProgramOptions(programSelect, [], '');
        return;
      }

      const params = new URLSearchParams({
        campus_id: campusId,
      });

      try {
        const response = await fetch(enrolledProgramEndpoint + '?' + params.toString(), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        renderFilterProgramOptions(programSelect, payload.programs || [], selectedProgramId);
      } catch (error) {
        // Keep existing options if request fails.
      }
    }

    document.querySelectorAll('.transfer-form').forEach(function(form) {
      const campusSelect = form.querySelector('.target-campus-select');
      const batchSelect = form.querySelector('.target-batch-select');
      const programSelect = form.querySelector('.target-program-select');

      if (!campusSelect || !batchSelect || !programSelect) {
        return;
      }

      async function refreshTransferPrograms() {
        const selectedProgramId = programSelect.value || '';
        const targetCampusId = String(campusSelect.value || '');
        const targetBatchId = String(batchSelect.value || '');

        if (targetCampusId === '' || targetBatchId === '') {
          renderTransferProgramOptions(programSelect, [], '');
          return;
        }

        const params = new URLSearchParams({
          campus_id: targetCampusId,
          batch_id: targetBatchId,
        });

        try {
          const response = await fetch(enrolledProgramEndpoint + '?' + params.toString(), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
          });

          if (!response.ok) {
            return;
          }

          const payload = await response.json();
          renderTransferProgramOptions(programSelect, payload.programs || [], selectedProgramId);
        } catch (error) {
          // Keep existing options if request fails.
        }
      }

      campusSelect.addEventListener('change', refreshTransferPrograms);
      batchSelect.addEventListener('change', refreshTransferPrograms);
    });

    const filterCampusSelect = document.getElementById('filterCampusId');

    if (filterCampusSelect) {
      filterCampusSelect.addEventListener('change', refreshFilterPrograms);
    }
  });
</script>

@include('includes.footer')
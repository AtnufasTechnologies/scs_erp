@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4 class="mb-3">ITCELL Integrated Student Shift</h4>

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
      <form method="GET" action="{{ route('itcell.integrated-student-shift.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Batch</label>
          <select name="batch_id" class="form-select" required>
            <option value="">Select batch</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ (int) $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Integrated Program</label>
          <select name="integrated_program_id" id="integratedProgramSelect" class="form-select">
            <option value="">All Integrated Programs</option>
            @foreach($integratedPrograms as $program)
            <option value="{{ $program->id }}" {{ (int) $selectedIntegratedProgramId === (int) $program->id ? 'selected' : '' }}>
              {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Search Student</label>
          <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Roll no / Reg no / Name">
        </div>

      </form>
    </div>
  </div>

  @if($selectedBatchId > 0)
  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('itcell.integrated-student-shift.store') }}" id="integratedShiftForm">
        @csrf
        <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">
        <input type="hidden" name="integrated_program_id" value="{{ $selectedIntegratedProgramId }}">
        <input type="hidden" name="search" value="{{ $search }}">

        <div class="row g-3 mb-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Target UG Program Combination (same batch + same offered subject)</label>
            <select name="target_combination_id" class="form-select" required>
              <option value="">Select target combination</option>
              @foreach($targetCombinations as $combo)
              <option value="{{ $combo->id }}">
                {{ $combo->studentprograminfo?->code ? $combo->studentprograminfo->code . ' - ' : '' }}{{ $combo->studentprograminfo?->name ?? 'Program' }}
                | Subject: {{ $combo->subjectmaster?->code ? $combo->subjectmaster->code . ' - ' : '' }}{{ $combo->subjectmaster?->title ?? '-' }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-5">
            <label class="form-label">Remarks</label>
            <input type="text" name="remarks" class="form-control" placeholder="Optional note for this shift operation">
          </div>

          <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100" {{ $students->isEmpty() || (int) $selectedIntegratedProgramId <= 0 ? 'disabled' : '' }}>
              Shift Selected
            </button>
          </div>
        </div>

        @if((int) $selectedIntegratedProgramId <= 0)
          <div class="alert alert-warning">
          Select a specific Integrated Program to run shift. This ensures source tracking is accurate.
    </div>
    @endif

    <div class="alert alert-info">
      Shifting updates program/combo mapping but keeps roll number unchanged. A persistent integrated-origin flag is retained on shifted students.
    </div>

    <div class="alert alert-secondary">
      Students already shifted from selected integrated source remain visible below with their actual current program.
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllVisible">Select All Visible</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelection">Clear</button>
        <span class="ms-2 text-muted">Selected: <strong id="selectedCount">0</strong></span>
      </div>
      <div class="small text-muted">Total students: {{ $students->count() }}</div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-sm align-middle" id="integratedShiftTable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;"><input type="checkbox" id="masterCheck"></th>
            <th style="width: 70px;">#</th>
            <th style="width: 160px;">Roll No</th>
            <th style="width: 160px;">Reg No</th>
            <th>Student Name</th>
            <th style="width: 120px;">Year</th>
            <th style="width: 260px;">Current Program (Actual)</th>
            <th style="width: 260px;">Source Integrated Program</th>
            <th style="width: 150px;">Shift Status</th>
            <th style="width: 150px;">Origin Flag</th>
          </tr>
        </thead>
        <tbody>
          @forelse($students as $index => $student)
          @php
          $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
          $isOriginFlagged = (int) ($student->is_integrated_program_origin ?? 0) === 1;
          $isShiftedRow = (int) ($student->is_shifted_from_integrated ?? 0) === 1;
          $canShiftNow = (int) ($student->can_shift_now ?? 0) === 1;
          @endphp
          <tr class="student-row" data-search="{{ strtolower(trim(($student->roll_no ?? '') . ' ' . ($student->register_no ?? '') . ' ' . $studentName)) }}">
            <td>
              <input type="checkbox" class="student-check" name="student_ids[]" value="{{ $student->id }}" {{ $canShiftNow ? '' : 'disabled' }}>
            </td>
            <td>{{ $index + 1 }}</td>
            <td>{{ $student->roll_no ?: '-' }}</td>
            <td>{{ $student->register_no ?: '-' }}</td>
            <td>{{ $studentName !== '' ? $studentName : '-' }}</td>
            <td>{{ (int) ($student->current_year ?? 0) > 0 ? (int) $student->current_year : '-' }}</td>
            <td>{{ $student->actual_program_label ?? '-' }}</td>
            <td>{{ $student->source_integrated_program_label ?? '-' }}</td>
            <td>
              @if($isShiftedRow)
              <span class="badge bg-info text-dark">Already Shifted</span>
              @else
              <span class="badge bg-warning text-dark">Pending Shift</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $isOriginFlagged ? 'bg-success' : 'bg-secondary' }}">{{ $isOriginFlagged ? 'Yes' : 'No' }}</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="10" class="text-center text-muted">No integrated-program students found for selected filters.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    </form>
  </div>
</div>
@endif
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const integratedProgramSelect = document.getElementById('integratedProgramSelect');
    const batchSelect = document.querySelector('select[name="batch_id"]');
    const masterCheck = document.getElementById('masterCheck');
    const rowChecks = Array.from(document.querySelectorAll('.student-check'));
    const selectedCount = document.getElementById('selectedCount');
    const selectAllVisible = document.getElementById('selectAllVisible');
    const clearSelection = document.getElementById('clearSelection');
    const form = document.getElementById('integratedShiftForm');

    function updateCount() {
      if (!selectedCount) {
        return;
      }
      const count = rowChecks.filter(function(check) {
        return !check.disabled && check.checked;
      }).length;
      selectedCount.textContent = String(count);
    }

    if (masterCheck) {
      masterCheck.addEventListener('change', function() {
        rowChecks.forEach(function(check) {
          if (!check.disabled) {
            check.checked = masterCheck.checked;
          }
        });
        updateCount();
      });
    }

    if (integratedProgramSelect) {
      integratedProgramSelect.addEventListener('change', function() {
        const formEl = integratedProgramSelect.closest('form');
        if (formEl) {
          formEl.submit();
        }
      });
    }

    if (batchSelect) {
      batchSelect.addEventListener('change', function() {
        const formEl = batchSelect.closest('form');
        if (formEl) {
          formEl.submit();
        }
      });
    }

    rowChecks.forEach(function(check) {
      check.addEventListener('change', updateCount);
    });

    if (selectAllVisible) {
      selectAllVisible.addEventListener('click', function() {
        rowChecks.forEach(function(check) {
          const row = check.closest('tr');
          if (row && row.style.display !== 'none' && !check.disabled) {
            check.checked = true;
          }
        });
        updateCount();
      });
    }

    if (clearSelection) {
      clearSelection.addEventListener('click', function() {
        rowChecks.forEach(function(check) {
          if (!check.disabled) {
            check.checked = false;
          }
        });
        if (masterCheck) {
          masterCheck.checked = false;
        }
        updateCount();
      });
    }

    if (form) {
      form.addEventListener('submit', function(event) {
        const hasSelected = rowChecks.some(function(check) {
          return !check.disabled && check.checked;
        });

        if (!hasSelected) {
          event.preventDefault();
          alert('Please select at least one student to shift.');
          return;
        }

        var integratedProgramInput = form.querySelector('input[name="integrated_program_id"]');
        var integratedProgramId = parseInt((integratedProgramInput && integratedProgramInput.value) ? integratedProgramInput.value : '0', 10);
        if (!integratedProgramId || integratedProgramId <= 0) {
          event.preventDefault();
          alert('Please select a specific Integrated Program before shifting students.');
          return;
        }

        if (!confirm('Shift selected students to chosen combination? Roll numbers will remain unchanged.')) {
          event.preventDefault();
        }
      });
    }

    updateCount();
  });
</script>
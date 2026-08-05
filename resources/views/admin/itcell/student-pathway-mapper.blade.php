@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4 class="mb-3">ITCELL Student Pathway Mapper</h4>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('itcell.pathway.mapper') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
          <label for="batch_id" class="form-label">Batch</label>
          <select id="batch_id" name="batch_id" class="form-control">
            <option value="">-- All / Not Selected --</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ (string)($filters['batch_id'] ?? '') === (string)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
            @endforeach
          </select>

        </div>

        <div class="col-md-6">
          <label for="program_ids" class="form-label">Enrolled Program(s) <small class="text-muted">Used when batch is empty; can also be combined with batch for narrower results.</small></label>
          <select id="program_ids" name="program_ids[]" class=" dselect-example" multiple>
            @foreach($enrolledPrograms as $program)
            <option value="{{ $program->id }}" {{ in_array((int)$program->id, array_map('intval', (array)($filters['program_ids'] ?? [])), true) ? 'selected' : '' }}>
              {{ $program->code ?? 'N/A' }} - {{ $program->name }}
            </option>
            @endforeach
          </select>

        </div>

        <div class="col-md-1">
          <label for="pathway_type" class="form-label">Single / Dual</label>
          <select id="pathway_type" name="pathway_type" class="form-control">
            <option value="">All</option>
            <option value="1" {{ (string)($filters['pathway_type'] ?? '') === '1' ? 'selected' : '' }}>Single</option>
            <option value="2" {{ (string)($filters['pathway_type'] ?? '') === '2' ? 'selected' : '' }}>Dual</option>
          </select>
        </div>

        <div class="col-md-1">
          <label for="current_semester" class="form-label">Current Semester</label>
          <input type="number" min="1" id="current_semester" name="current_semester" class="form-control" value="{{ $filters['current_semester'] ?? '' }}" placeholder="e.g. 3">
        </div>

        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            Generate List
          </button>
        </div>
      </form>
    </div>
  </div>

  @if(!empty($filters['batch_id']) || !empty($filters['program_ids']))
  <div class="card shadow-sm">
    <div class="card-body">
      @if($students->isEmpty())
      <div class="alert alert-warning mb-0">No students found for the selected filters.</div>
      @else
      <form action="{{ route('itcell.pathway.mapper.bulk-update') }}" method="POST" id="pathwayBulkForm">
        @csrf
        <input type="hidden" name="batch_id" value="{{ $filters['batch_id'] ?? '' }}">
        @foreach((array)($filters['program_ids'] ?? []) as $programId)
        <input type="hidden" name="program_ids[]" value="{{ $programId }}">
        @endforeach
        <input type="hidden" name="pathway_type" value="{{ $filters['pathway_type'] ?? '' }}">
        <input type="hidden" name="current_semester" value="{{ $filters['current_semester'] ?? '' }}">

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Update Academic Pathway</label>
            <select name="academic_pathway_id" class="form-control">
              <option value="">No Change</option>
              @foreach($pathways as $pathway)
              <option value="{{ $pathway->id }}">{{ $pathway->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Update Degree Track</label>
            <select name="degree_track_id" class="form-control">
              <option value="">No Change</option>
              @foreach($degreeTracks as $track)
              <option value="{{ $track->id }}">{{ $track->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Update Selected Combo</label>
            <select name="selected_combo_id" class="form-control dselect-example">
              <option value="">No Change</option>
              @foreach($subjects as $subject)
              <option value="{{ $subject->id }}">{{ $subject->title }} | {{ $subject->campus_id == 1 ? 'Sonada' : 'Siliguri' }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllVisible">Select All Visible</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelection">Clear Selection</button>
            <span class="ms-2 text-muted">Selected: <strong id="selectedCount">0</strong></span>
          </div>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-save me-1"></i> Bulk Update Selected Students
          </button>
        </div>

        <div class="mb-3">
          <input type="text" id="studentSearch" class="form-control" placeholder="Search by name, roll no, register no">
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="studentsTable">
            <thead class="table-light">
              <tr>
                <th width="5%">
                  <input type="checkbox" id="masterCheck">
                </th>
                <th>#</th>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Current Sem</th>
                <th>Academic Pathway</th>
                <th>Degree Track</th>
                <th>Selected Combo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($students as $index => $student)
              <tr class="student-row" data-search="{{ strtolower(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->roll_no ?? '') . ' ' . ($student->register_no ?? ''))) }}">
                <td>
                  <input type="checkbox" class="student-check" name="student_ids[]" value="{{ $student->id }}">
                </td>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->roll_no ?? 'N/A' }}</td>
                <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}</td>
                <td>{{ ($student->stdprogramenrolled->code ?? '') }} - {{ $student->stdprogramenrolled->name ?? 'N/A' }}</td>
                <td>{{ $student->activeSemesterConfig->semester_id ?? 'N/A' }}</td>
                <td>{{ $student->academicpathway->name ?? 'Not Set' }}</td>
                <td>{{ $student->degreetrack->name ?? 'Not Set' }}</td>
                <td>{{ $student->singleselection->title ?? 'Not Set' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </form>
      @endif
    </div>
  </div>
  @else
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="alert alert-info mb-0">
        Select a batch, or leave batch empty and select enrolled program(s), then click Generate List.
      </div>
    </div>
  </div>
  @endif
</div>

@include('includes.footer')

<script type="application/json" id="batchProgramMapData">
  @json($batchProgramMap ?? [])
</script>
<script type="application/json" id="selectedProgramIdsData">
  @json(array_values(array_map('intval', (array)($filters['program_ids'] ?? []))))
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    function safeJsonParse(raw, fallback) {
      try {
        return JSON.parse(raw);
      } catch (e) {
        return fallback;
      }
    }

    const batchProgramMap = safeJsonParse(document.getElementById('batchProgramMapData')?.textContent || '{}', {});
    const initialSelectedPrograms = safeJsonParse(document.getElementById('selectedProgramIdsData')?.textContent || '[]', []);
    const batchSelect = document.getElementById('batch_id');
    const programSelect = document.getElementById('program_ids');

    if (batchSelect && programSelect) {
      const allProgramOptions = Array.from(programSelect.options).map(option => ({
        value: parseInt(option.value, 10),
        text: option.textContent,
      })).filter(option => !Number.isNaN(option.value));

      const renderProgramOptions = () => {
        const selectedBatch = batchSelect.value;
        const allowedPrograms = selectedBatch ?
          ((batchProgramMap[selectedBatch] || []).map(Number)) :
          allProgramOptions.map(option => option.value);

        const currentSelected = Array.from(programSelect.selectedOptions).map(option => parseInt(option.value, 10));
        const keepSelected = currentSelected.length ? currentSelected : initialSelectedPrograms;

        const fragment = document.createDocumentFragment();
        allProgramOptions.forEach(option => {
          if (allowedPrograms.includes(option.value)) {
            const optionEl = document.createElement('option');
            optionEl.value = String(option.value);
            optionEl.textContent = option.text;
            if (keepSelected.includes(option.value)) {
              optionEl.selected = true;
            }
            fragment.appendChild(optionEl);
          }
        });

        programSelect.innerHTML = '';
        programSelect.appendChild(fragment);
        programSelect.dispatchEvent(new Event('change'));
      };

      batchSelect.addEventListener('change', renderProgramOptions);
      renderProgramOptions();
    }

    const searchInput = document.getElementById('studentSearch');
    const rows = Array.from(document.querySelectorAll('.student-row'));
    const masterCheck = document.getElementById('masterCheck');
    const studentChecks = Array.from(document.querySelectorAll('.student-check'));
    const selectedCount = document.getElementById('selectedCount');
    const selectAllVisibleBtn = document.getElementById('selectAllVisible');
    const clearSelectionBtn = document.getElementById('clearSelection');

    function visibleRows() {
      return rows.filter(row => row.style.display !== 'none');
    }

    function updateSelectedCount() {
      const checked = studentChecks.filter(cb => cb.checked).length;
      if (selectedCount) selectedCount.textContent = checked;
    }

    function updateMasterState() {
      const visibleChecks = visibleRows().map(row => row.querySelector('.student-check')).filter(Boolean);
      const totalVisible = visibleChecks.length;
      const checkedVisible = visibleChecks.filter(cb => cb.checked).length;

      if (!masterCheck || totalVisible === 0) return;
      masterCheck.checked = checkedVisible > 0 && checkedVisible === totalVisible;
      masterCheck.indeterminate = checkedVisible > 0 && checkedVisible < totalVisible;
    }

    searchInput?.addEventListener('input', function() {
      const term = this.value.toLowerCase().trim();
      rows.forEach(row => {
        const searchData = row.dataset.search || '';
        row.style.display = searchData.includes(term) ? '' : 'none';
      });
      updateMasterState();
    });

    masterCheck?.addEventListener('change', function() {
      visibleRows().forEach(row => {
        const checkbox = row.querySelector('.student-check');
        if (checkbox) checkbox.checked = this.checked;
      });
      updateSelectedCount();
      updateMasterState();
    });

    studentChecks.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        updateSelectedCount();
        updateMasterState();
      });
    });

    selectAllVisibleBtn?.addEventListener('click', function() {
      visibleRows().forEach(row => {
        const checkbox = row.querySelector('.student-check');
        if (checkbox) checkbox.checked = true;
      });
      updateSelectedCount();
      updateMasterState();
    });

    clearSelectionBtn?.addEventListener('click', function() {
      studentChecks.forEach(cb => cb.checked = false);
      updateSelectedCount();
      updateMasterState();
    });

    document.getElementById('pathwayBulkForm')?.addEventListener('submit', function(e) {
      const anySelected = studentChecks.some(cb => cb.checked);
      if (!anySelected) {
        e.preventDefault();
        alert('Please select at least one student for bulk update.');
      }
    });

    updateSelectedCount();
    updateMasterState();
  });
</script>
@include('includes.header')
@include('admin.sidebar')
<style>
  .bulk-wrap {
    min-height: 100vh;
    background: radial-gradient(circle at top right, #eef8ff, #f7fafc 40%, #f5f7fb 100%);
    padding: 1.25rem;
  }

  .bulk-card {
    border: 1px solid #d8e2ef;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(18, 38, 63, 0.06);
  }

  .bulk-badge,
  .status-badge {
    display: inline-block;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.28rem 0.65rem;
  }

  .badge-ready {
    background: #dcfce7;
    color: #166534;
  }

  .badge-pending {
    background: #fee2e2;
    color: #991b1b;
  }

  .badge-program-type {
    background: #e0e7ff;
    color: #3730a3;
  }

  .badge-combination-id {
    background: #ecfeff;
    color: #155e75;
  }

  .stat-pill {
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    background: #f8fbff;
    border: 1px solid #dce8f8;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .mini-title {
    letter-spacing: 0.04rem;
    font-weight: 700;
    color: #1f3a56;
    text-transform: uppercase;
    font-size: 0.8rem;
  }

  .empty-box {
    border: 1px dashed #bfd2e8;
    border-radius: 12px;
    background: #f8fbff;
    color: #4d6480;
    padding: 1.2rem;
  }

  .program-row-disabled {
    background: #f8fafc;
    color: #7b8794;
  }

  .program-row-disabled td {
    color: #7b8794;
  }

  .count-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #dbe7f5;
    background: #f8fbff;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
    color: #1e3a5f;
  }

  .btn-view-program {
    border: 1px solid #c9d9ec;
    background: #f4f8fd;
    color: #174a7c;
    font-weight: 600;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 8px;
  }

  .btn-view-program:hover {
    background: #e9f2fb;
    color: #123d67;
  }

  .rollno-same {
    color: #166534;
    font-weight: 700;
    font-size: 11px;
  }

  .rollno-change {
    color: #9a3412;
    font-weight: 700;
    font-size: 11px;
  }
</style>

@php
$selectedBatchId = (int) ($selectedBatchId ?? request('batch_id', 0));
$oldProgramIds = collect(old('program_combination_ids', []))->map(fn($id) => (int) $id)->toArray();
$programRowsCollection = collect($programRows ?? [])->values();
$totalProgramCount = $programRowsCollection->count();
$totalStudentsCount = (int) $programRowsCollection->sum(fn($row) => (int) ($row->students_count ?? 0));
@endphp

<div class="main-content bulk-wrap">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div class="alert alert-info">
        <h4 class="mb-1">Bulk Roll Number Reconfiguration</h4>
        <p class="text-muted mb-0">Select batch, choose program combinations, and reconfigure student roll numbers in one run.</p>
        <div class="col-lg-3">
          <form action="{{ route('itcell.bulk.rollno.reconfigure') }}" method="GET" class="row g-3 align-items-end mb-3" id="batchFilterForm">
            <div class="input-group">
              <select name="batch_id" class="form-control" id="batchSelect" required>
                <option value="">Select batch</option>
                @foreach(($batches ?? collect()) as $batch)
                <option value="{{ $batch->id }}" {{ $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>
                  {{ $batch->batch_name }}
                </option>
                @endforeach
              </select>
              @error('batch_id')
              <span class="text-danger small">{{ $message }}</span>
              @enderror
            </div>
          </form>
        </div>

      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif



    @if($selectedBatchId > 0)
    <div class="bulk-card p-3 mb-3">
      <div class="mini-title mb-2">Selection Summary</div>
      <div class="row g-2">
        <div class="col-md-6">
          <div class="stat-pill">Total Programs: <strong>{{ $totalProgramCount }}</strong></div>
        </div>
        <div class="col-md-6">
          <div class="stat-pill">Total Students: <strong>{{ $totalStudentsCount }}</strong></div>
        </div>
      </div>
    </div>

    <form action="{{ route('itcell.bulk.rollno.reconfigure.store') }}" method="POST">
      @csrf
      <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">

      <div class="bulk-card p-3 mb-3">

        <div class="row g-3 align-items-end mb-3">
          <div class="col-lg-8">

            <div class="">
              <label lass="form-label">Search</label>
              <input type="text" id="programSearchInput" class="form-control" placeholder="Program code, name or campus">
            </div>
          </div>
          <div class="col-lg-4 text-lg-end">
            <button type="submit" class="btn btn-success w-100" id="reconfigureBtn">Reconfigure Roll Numbers</button>
          </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
          <div class="mini-title mb-0">Programs In Selected Batch <span class="small text-muted mt-2">Select one or more program combinations to reconfigure roll numbers.</span></div>
          <div class="count-pill">Visible Rows: <span id="visibleRowsCount">{{ $totalProgramCount }}</span></div>
        </div>



        @if($programRowsCollection->isEmpty())
        <div class="empty-box">No programs found for this batch.</div>
        @else
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 70px;">
                  <input type="checkbox" id="selectAllPrograms"> All
                </th>
                <th>Program</th>

                <th style="width: 180px;">Campus</th>
                <th style="width: 140px;">Students</th>
                <th style="width: 120px;">Action</th>
              </tr>
            </thead>
            <tbody id="programRowsBody">
              @foreach($programRowsCollection as $row)
              @php
              $isChecked = in_array((int) $row->combination_id, $oldProgramIds, true);
              $searchText = strtolower(trim(($row->program_code ?? '') . ' ' . ($row->program_name ?? '') . ' ' . ($row->campus_name ?? '')));
              @endphp
              <tr data-search="{{ $searchText }}">
                <td>
                  <input
                    type="checkbox"
                    class="program-checkbox"
                    name="program_combination_ids[]"
                    value="{{ (int) $row->combination_id }}"
                    {{ $isChecked ? 'checked' : '' }}>
                </td>
                <td>
                  <strong>{{ $row->program_code }}</strong>
                  @if(!empty($row->program_type))
                  <span class="status-badge badge-program-type ms-1">{{ $row->program_type }}</span>
                  @endif
                  <div class="text-muted small">{{ $row->program_name }}</div>
                </td>
                <td>{{ $row->campus_name }}</td>
                <td>{{ $row->students_count }}</td>
                <td>
                  <button
                    type="button"
                    class="btn-view-program js-view-program"
                    data-combination-id="{{ (int) $row->combination_id }}"
                    data-program-label="{{ trim(($row->program_code ?? '-') . ' - ' . ($row->program_name ?? '-')) }} | CID {{ (int) $row->combination_id }}">
                    View
                  </button>
                </td>
              </tr>
              @endforeach
              <tr id="noProgramSearchResult" style="display: none;">
                <td colspan="5" class="text-center text-muted">No matching programs found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        @error('program_combination_ids')
        <span class="text-danger small d-block mt-2">{{ $message }}</span>
        @enderror
        @error('program_combination_ids.*')
        <span class="text-danger small d-block mt-2">{{ $message }}</span>
        @enderror
        @endif
      </div>


    </form>

    @php
    $rollnoUpdates = collect(session('rollno_updates', []));
    $rollnoUpdatesTotal = (int) session('rollno_updates_total', 0);
    $rollnoUpdatesTruncated = (int) session('rollno_updates_truncated', 0);
    @endphp

    @if($rollnoUpdates->isNotEmpty())
    <div class="bulk-card p-3 mb-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <div class="mini-title mb-0">Reconfigured Roll Number List</div>
        <div class="count-pill">Showing {{ $rollnoUpdates->count() }} of {{ $rollnoUpdatesTotal }}</div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 80px;">#</th>
              <th style="width: 120px;">Student ID</th>
              <th>Program</th>
              <th style="width: 160px;">Campus</th>
              <th style="width: 220px;">Old Roll No</th>
              <th style="width: 220px;">New Roll No</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rollnoUpdates as $index => $update)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $update['student_id'] ?? '-' }}</td>
              <td>
                <strong>{{ $update['program_code'] ?? '-' }}</strong>
                <div class="text-muted small">{{ $update['program_name'] ?? '-' }}</div>
              </td>
              <td>{{ $update['campus_name'] ?? '-' }}</td>
              <td>{{ $update['old_roll_no'] !== '' ? $update['old_roll_no'] : '-' }}</td>
              <td><strong>{{ $update['new_roll_no'] ?? '-' }}</strong></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($rollnoUpdatesTruncated > 0)
      <div class="alert alert-warning mt-3 mb-0">
        Showing first {{ $rollnoUpdates->count() }} entries. {{ $rollnoUpdatesTruncated }} additional record(s) were processed but not displayed.
      </div>
      @endif
    </div>
    @endif
    @endif
  </div>
</div>

<div class="modal fade" id="programStudentsModal" tabindex="-1" aria-labelledby="programStudentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="programStudentsModalLabel">Program Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="text-muted" id="programStudentsMeta">Loading...</div>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead>
              <tr>
                <th style="width:70px;">#</th>
                <th style="width:120px;">Student ID</th>
                <th>Student Name</th>
                <th style="width:210px;">Current Roll No</th>
                <th style="width:210px;">Preview Roll No</th>
                <th style="width:120px;">Status</th>
              </tr>
            </thead>
            <tbody id="programStudentsBody">
              <tr>
                <td colspan="6" class="text-center text-muted">Click View to load students.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    const batchSelect = document.getElementById('batchSelect');
    if (batchSelect) {
      batchSelect.addEventListener('change', function() {
        const form = document.getElementById('batchFilterForm');
        if (form) {
          form.submit();
        }
      });
    }

    const selectAll = document.getElementById('selectAllPrograms');
    const programCheckboxes = Array.from(document.querySelectorAll('.program-checkbox:not(:disabled)'));
    const reconfigureBtn = document.getElementById('reconfigureBtn');
    const searchInput = document.getElementById('programSearchInput');
    const rowBody = document.getElementById('programRowsBody');
    const noResultRow = document.getElementById('noProgramSearchResult');
    const visibleRowsCount = document.getElementById('visibleRowsCount');
    const viewButtons = Array.from(document.querySelectorAll('.js-view-program'));
    const studentsModalEl = document.getElementById('programStudentsModal');
    const studentsModalTitle = document.getElementById('programStudentsModalLabel');
    const studentsMeta = document.getElementById('programStudentsMeta');
    const studentsBody = document.getElementById('programStudentsBody');
    const selectedBatchId = Number('{{ (int) $selectedBatchId }}');
    const previewUrlTemplate = '{{ route("itcell.bulk.rollno.reconfigure.program-students", ["combinationId" => "__COMBINATION_ID__"]) }}';

    function openStudentsModal() {
      if (!studentsModalEl) return;
      if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const modal = window.bootstrap.Modal.getOrCreateInstance(studentsModalEl);
        modal.show();
        return;
      }
      studentsModalEl.style.display = 'block';
      studentsModalEl.classList.add('show');
      studentsModalEl.removeAttribute('aria-hidden');
    }

    function escapeHtml(text) {
      return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    async function loadProgramStudents(combinationId, programLabel) {
      if (!studentsBody || !studentsMeta) return;

      studentsModalTitle.textContent = `Program Students: ${programLabel || '-'}`;
      studentsMeta.textContent = 'Loading student list...';
      studentsBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>';
      openStudentsModal();

      const requestUrl = previewUrlTemplate.replace('__COMBINATION_ID__', encodeURIComponent(String(combinationId))) + `?batch_id=${encodeURIComponent(String(selectedBatchId || 0))}`;

      try {
        const response = await fetch(requestUrl, {
          method: 'GET',
          headers: {
            Accept: 'application/json',
          },
        });

        const payload = await response.json();
        if (!payload || !payload.success) {
          const message = payload && payload.message ? payload.message : 'Failed to fetch student list.';
          studentsMeta.textContent = message;
          studentsBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${escapeHtml(message)}</td></tr>`;
          return;
        }

        const students = Array.isArray(payload.students) ? payload.students : [];
        const totalStudents = Number(payload.total_students || students.length || 0);
        const changedCount = students.filter((row) => !!row.needs_change).length;
        const program = payload.program || {};
        const programTitle = [program.program_code || '-', program.program_name || '-'].join(' - ');

        studentsMeta.textContent = `${programTitle} | ${program.campus_name || '-'} | Batch ${program.batch_name || '-'} | ${totalStudents} student(s), ${changedCount} change(s)`;

        if (!students.length) {
          studentsBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No students found for this program.</td></tr>';
          return;
        }

        studentsBody.innerHTML = students.map((row, idx) => {
          const statusText = row.needs_change ? 'Will Change' : 'No Change';
          const statusClass = row.needs_change ? 'rollno-change' : 'rollno-same';
          return `<tr>
            <td>${idx + 1}</td>
            <td>${escapeHtml(row.student_id)}</td>
            <td>${escapeHtml(row.student_name || '-')}</td>
            <td>${escapeHtml(row.current_roll_no || '-')}</td>
            <td><strong>${escapeHtml(row.preview_roll_no || '-')}</strong></td>
            <td><span class="${statusClass}">${statusText}</span></td>
          </tr>`;
        }).join('');
      } catch (error) {
        studentsMeta.textContent = 'Failed to load student list.';
        studentsBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Unable to fetch student list right now.</td></tr>';
      }
    }

    function syncEnrollButtonState() {
      if (!reconfigureBtn) return;
      const anyChecked = programCheckboxes.some((cb) => cb.checked);
      reconfigureBtn.disabled = !anyChecked;
    }

    function syncSelectAllState() {
      if (!selectAll) return;
      const checkedCount = programCheckboxes.filter((cb) => cb.checked).length;
      selectAll.checked = programCheckboxes.length > 0 && checkedCount === programCheckboxes.length;
      selectAll.indeterminate = checkedCount > 0 && checkedCount < programCheckboxes.length;
      syncEnrollButtonState();
    }

    if (selectAll && programCheckboxes.length > 0) {
      selectAll.addEventListener('change', function() {
        programCheckboxes.forEach((cb) => {
          cb.checked = this.checked;
        });
        syncSelectAllState();
      });

      programCheckboxes.forEach((cb) => {
        cb.addEventListener('change', syncSelectAllState);
      });

      syncSelectAllState();
    } else {
      syncEnrollButtonState();
    }

    if (searchInput && rowBody) {
      const searchableRows = Array.from(rowBody.querySelectorAll('tr[data-search]'));

      searchInput.addEventListener('input', function() {
        const query = (this.value || '').toLowerCase().trim();
        let visibleCount = 0;

        searchableRows.forEach((row) => {
          const haystack = row.getAttribute('data-search') || '';
          const matched = query === '' || haystack.includes(query);
          row.style.display = matched ? '' : 'none';
          if (matched) {
            visibleCount++;
          }
        });

        if (visibleRowsCount) {
          visibleRowsCount.textContent = String(visibleCount);
        }

        if (noResultRow) {
          noResultRow.style.display = visibleCount === 0 ? '' : 'none';
        }
      });
    }

    if (viewButtons.length) {
      viewButtons.forEach((button) => {
        button.addEventListener('click', function() {
          const combinationId = Number(this.getAttribute('data-combination-id') || 0);
          const programLabel = this.getAttribute('data-program-label') || 'Program';
          if (!combinationId) return;
          loadProgramStudents(combinationId, programLabel);
        });
      });
    }
  })();
</script>


@include('includes.footer')
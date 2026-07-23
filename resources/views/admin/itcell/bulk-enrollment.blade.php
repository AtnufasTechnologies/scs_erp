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
</style>

@php
$selectedBatchId = (int) ($selectedBatchId ?? request('batch_id', 0));
$oldProgramIds = collect(old('program_combination_ids', []))->map(fn($id) => (int) $id)->toArray();
$rollnoAction = old('rollno_action', 'dont_reconfigure');
$programRowsCollection = collect($programRows ?? [])->sortByDesc(fn($row) => (int) ($row->curriculum_done ?? 0))->values();
$totalProgramCount = $programRowsCollection->count();
$doneProgramCount = $programRowsCollection->where('curriculum_done', true)->count();
$pendingProgramCount = $totalProgramCount - $doneProgramCount;
$donePercentage = $totalProgramCount > 0 ? round(($doneProgramCount / $totalProgramCount) * 100) : 0;
@endphp

<div class="main-content bulk-wrap">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div class="alert alert-info">
        <h4 class="mb-1">Bulk Course Enrollment</h4>
        <p class="text-muted mb-0">Select batch, choose curriculum-ready programs, and enroll COMPULSORY curriculum courses in one run.</p>
        <div class="col-lg-3">
          <form action="{{ route('bulk.student.course.enrollment') }}" method="GET" class="row g-3 align-items-end mb-3" id="batchFilterForm">
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
      <div class="mini-title mb-2">Curriculam Analytics</div>
      <div class="row g-2">
        <div class="col-md-3">
          <div class="stat-pill">Total Programs: <strong>{{ $totalProgramCount }}</strong></div>
        </div>
        <div class="col-md-3">
          <div class="stat-pill">Curriculam Done: <strong>{{ $doneProgramCount }}</strong></div>
        </div>
        <div class="col-md-3">
          <div class="stat-pill">Curriculam Pending: <strong>{{ $pendingProgramCount }}</strong></div>
        </div>
        <div class="col-md-3">
          <div class="stat-pill">Done Rate: <strong>{{ $donePercentage }}%</strong></div>
        </div>
      </div>
    </div>

    <form action="{{ route('bulk.student.course.enrollment.store') }}" method="POST">
      @csrf
      <input type="hidden" name="batch_id" value="{{ $selectedBatchId }}">

      <div class="bulk-card p-3 mb-3">

        <div class="row g-3 align-items-end mb-3">
          <div class="col-lg-4">

            <div class="">
              <label lass="form-label">Search</label>
              <input type="text" id="programSearchInput" class="form-control" placeholder="Program code, name or campus">
            </div>
          </div>
          <div class="col-lg-4">
            <label class="form-label">Roll No Handling
            </label>
            <select class="form-control" name="rollno_action" required>
              <option value="dont_reconfigure" {{ $rollnoAction === 'dont_reconfigure' ? 'selected' : '' }}>Dont Reconfigure RollNo</option>
              <option value="reconfigure" {{ $rollnoAction === 'reconfigure' ? 'selected' : '' }}>ReConfigure RollNo</option>
            </select>
            @error('rollno_action')
            <span class="text-danger small">{{ $message }}</span>
            @enderror
          </div>

          <div class="col-lg-4 text-lg-end">
            <button type="submit" class="btn btn-success w-100" id="enrollNowBtn">EnrollNow</button>
          </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
          <div class="mini-title mb-0">Programs In Selected Batch <span class="small text-muted mt-2">Only programs with AUTO curriculum can be selected and processed.</span></div>
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
                <th style="width: 150px;">COMPULSORY Courses</th>
                <th style="width: 170px;">Curriculum Status</th>
              </tr>
            </thead>
            <tbody id="programRowsBody">
              @foreach($programRowsCollection as $row)
              @php
              $isDisabled = !$row->curriculum_done;
              $isChecked = in_array((int) $row->combination_id, $oldProgramIds, true);
              $searchText = strtolower(trim(($row->program_code ?? '') . ' ' . ($row->program_name ?? '') . ' ' . ($row->campus_name ?? '')));
              @endphp
              <tr class="{{ $isDisabled ? 'program-row-disabled' : '' }}" data-search="{{ $searchText }}">
                <td>
                  <input
                    type="checkbox"
                    class="program-checkbox"
                    name="program_combination_ids[]"
                    value="{{ (int) $row->combination_id }}"
                    {{ $isChecked ? 'checked' : '' }}
                    {{ $isDisabled ? 'disabled' : '' }}>
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
                <td>{{ $row->auto_courses_count }}</td>
                <td>
                  @if($row->curriculum_done)
                  <span class="status-badge badge-ready">Curriculum Done</span>
                  @else
                  <span class="status-badge badge-pending">Curriculum Pending</span>
                  @endif
                </td>
              </tr>
              @endforeach
              <tr id="noProgramSearchResult" style="display: none;">
                <td colspan="6" class="text-center text-muted">No matching programs found.</td>
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
    @endif
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
    const enrollBtn = document.getElementById('enrollNowBtn');
    const searchInput = document.getElementById('programSearchInput');
    const rowBody = document.getElementById('programRowsBody');
    const noResultRow = document.getElementById('noProgramSearchResult');
    const visibleRowsCount = document.getElementById('visibleRowsCount');

    function syncEnrollButtonState() {
      if (!enrollBtn) return;
      const anyChecked = programCheckboxes.some((cb) => cb.checked);
      enrollBtn.disabled = !anyChecked;
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
  })();
</script>


@include('includes.footer')
@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">Subject Program Enrollment Inspector</h4>
      <p class="text-muted mb-0">Select batch and department to view COMBO1 vs COMBO2 program counts.</p>
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

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.subject-program-enrollment-inspector.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Batch <span class="text-danger">*</span></label>
          <select name="batch_id" class="form-select" required>
            <option value="">Select batch</option>
            @foreach(($batches ?? collect()) as $batch)
            <option value="{{ $batch->id }}" {{ (int) $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-5">
          <label class="form-label">Department (Subject)</label>
          <select name="department_id" class="dselect-example">
            <option value="">All Departments</option>
            @foreach(($departments ?? collect()) as $department)
            <option value="{{ $department->id }}" {{ (int) $selectedDepartmentId === (int) $department->id ? 'selected' : '' }}>
              {{ $department->code ? ($department->code . ' - ') : '' }}{{ $department->title }}
              {{ !empty($department->campus_name) ? (' (' . $department->campus_name . ')') : '' }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-grid">
          <button type="submit" class="btn btn-primary">Inspect Linkage</button>
        </div>
      </form>
    </div>
  </div>

  @if((int) ($selectedBatchId ?? 0) > 0 && (int) ($selectedDepartmentId ?? 0) > 0 && !empty($selectedDepartmentComboInsights))
  <div class="card shadow-sm mb-4 border-primary">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Simple Combo View: {{ $selectedDepartmentComboInsights['department_name'] ?? 'Selected Department' }}</h5>
    </div>
    <div class="card-body">
      @php
      $combo1Programs = collect($selectedDepartmentComboInsights['combo1_programs'] ?? [])->values();
      $combo2Programs = collect($selectedDepartmentComboInsights['combo2_programs'] ?? [])->values();
      $maxRows = max($combo1Programs->count(), $combo2Programs->count());
      @endphp

      <div class="mb-2 fw-bold">{{ $selectedDepartmentComboInsights['batch_name'] ?? $selectedBatchId }}</div>

      <div class="row g-2 mb-3">
        <div class="col-md-4">
          <div class="alert alert-info py-2 mb-0">
            <strong>COMBO1 Total Students:</strong> {{ (int) ($selectedDepartmentComboInsights['combo1_total_students'] ?? 0) }}
          </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-warning py-2 mb-0">
            <strong>COMBO2 Total Students:</strong> {{ (int) ($selectedDepartmentComboInsights['combo2_total_students'] ?? 0) }}
          </div>
        </div>
        <div class="col-md-4">
          <div class="alert alert-success py-2 mb-0">
            <strong>Unique Total Students:</strong> {{ (int) ($selectedDepartmentComboInsights['unique_total_students'] ?? 0) }}
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>COMBO1</th>
              <th>COMBO2</th>
            </tr>
          </thead>
          <tbody>
            @if($maxRows === 0)
            <tr>
              <td colspan="2" class="text-muted">No combo programs found for selected department.</td>
            </tr>
            @else
            @for($i = 0; $i < $maxRows; $i++)
              @php
              $combo1=$combo1Programs->get($i);
              $combo2 = $combo2Programs->get($i);
              @endphp
              <tr>
                <td>
                  @if($combo1)
                  <div><strong>{{ $combo1['program_code'] ?: '-' }}</strong> - {{ (int) ($combo1['students_count'] ?? 0) }}</div>
                  <div class="small text-muted">{{ $combo1['program_name'] ?? '-' }}</div>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($combo2)
                  <div><strong>{{ $combo2['program_code'] ?: '-' }}</strong> - {{ (int) ($combo2['students_count'] ?? 0) }}</div>
                  <div class="small text-muted">{{ $combo2['program_name'] ?? '-' }}</div>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              @endfor
              @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif
</div>

@include('includes.footer')
@extends('layouts.master')

@section('title')
Generate Payroll
@endsection

@section('content')
@include('includes.hr-sidebar')

<!--start main wrapper-->
<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">HR</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active" aria-current="page">Generate</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

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

    <!-- Step 1: Assign Pay Matrix to Faculty -->
    @if($facultiesWithoutSalary->count() > 0)
    <div class="card mb-4">
      <div class="card-header bg-warning">
        <h5 class="mb-0 text-white">
          <i class="material-icons-outlined">warning</i>
          Step 1: Assign Pay Matrix to Faculty ({{ $facultiesWithoutSalary->count() }} pending)
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted">The following faculty members do not have an active salary structure assigned. Please
          assign a pay matrix to them before generating payroll.</p>

        <form action="{{ route('hr.payroll.assign-pay-matrix') }}" method="POST">
          @csrf
          <div class="table-responsive mb-3">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Faculty</th>
                  <th>Employee Code</th>
                  <th>Email</th>
                  <th>Select Pay Matrix</th>
                  <th>Effective From</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($facultiesWithoutSalary as $faculty)
                <tr>
                  <td>{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</td>
                  <td>{{ $faculty->FACULTY_CODE }}</td>
                  <td>{{ $faculty->EMAIL }}</td>
                  <td>
                    <select name="pay_matrix_id_{{ $faculty->id }}" class="form-select">
                      <option value="">Select Pay Matrix</option>
                      @foreach($payMatrices as $matrix)
                      <option value="{{ $matrix->id }}">
                        {{ $matrix->matrix_code }} - {{ $matrix->designation }} ({{ $matrix->grade_level }}) -
                        ₹{{ number_format($matrix->basic_salary, 2) }}
                      </option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="date" name="effective_from_{{ $faculty->id }}" class="form-control"
                      value="{{ date('Y-m-d') }}">
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="assignMatrix({{ $faculty->id }})">
                      <i class="material-icons-outlined">assignment</i> Assign
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </form>
      </div>
    </div>
    @endif

    <!-- Step 2: Bulk Generate Salary Slips -->
    <div class="card">
      <div class="card-header bg-primary">
        <h5 class="mb-0 text-white">
          <i class="material-icons-outlined">account_balance_wallet</i>
          Step 2: Bulk Generate Salary Slips
        </h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Generate salary slips for faculty members who have an active pay matrix assigned.</p>

        <form action="{{ route('hr.payroll.bulk-generate') }}" method="POST">
          @csrf
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label">Month <span class="text-danger">*</span></label>
              <select name="month" class="form-select" required>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                  {{ date('m') == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                  {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                  </option>
                  @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Year <span class="text-danger">*</span></label>
              <select name="year" class="form-select" required>
                @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Working Days <span class="text-danger">*</span></label>
              <input type="number" name="working_days" class="form-control" value="26" min="1" max="31"
                required>
            </div>
            <div class="col-md-5">
              <label class="form-label">&nbsp;</label>
              <div>
                <button type="submit" class="btn btn-primary">
                  <i class="material-icons-outlined">play_arrow</i> Generate Salary Slips
                </button>
                <button type="button" class="btn btn-secondary" onclick="toggleFacultySelection()">
                  <i class="material-icons-outlined">checklist</i> Select Specific Faculty
                </button>
              </div>
            </div>
          </div>

          <!-- Faculty Selection (Hidden by default) -->
          <div id="facultySelection" style="display: none;">
            <hr>
            <h6 class="mb-3">Select Faculty Members</h6>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="50">
                      <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()">
                    </th>
                    <th>Faculty</th>
                    <th>Employee Code</th>
                    <th>Pay Matrix</th>
                    <th>Basic Salary</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($facultiesWithSalary as $faculty)
                  <tr>
                    <td>
                      <input type="checkbox" name="faculty_ids[]" value="{{ $faculty->id }}"
                        class="faculty-checkbox">
                    </td>
                    <td>{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</td>
                    <td>{{ $faculty->FACULTY_CODE }}</td>
                    <td>
                      @if($faculty->salaryMaster && $faculty->salaryMaster->payMatrix)
                      {{ $faculty->salaryMaster->payMatrix->matrix_code }} -
                      {{ $faculty->salaryMaster->payMatrix->full_designation }}
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td>₹{{ number_format($faculty->salaryMaster->basic_salary ?? 0, 2) }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Faculty with Active Salary Master -->
    <div class="card mt-4">
      <div class="card-header bg-success">
        <h5 class="mb-0 text-white">
          <i class="material-icons-outlined">check_circle</i>
          Faculty with Active Salary Structure ({{ $facultiesWithSalary->count() }})
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Faculty</th>
                <th>Employee Code</th>
                <th>Pay Matrix</th>
                <th>Designation</th>
                <th>Basic Salary</th>
                <th>Effective From</th>
              </tr>
            </thead>
            <tbody>
              @foreach($facultiesWithSalary as $faculty)
              <tr>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</td>
                <td>{{ $faculty->FACULTY_CODE }}</td>
                <td>
                  @if($faculty->salaryMaster && $faculty->salaryMaster->payMatrix)
                  <span class="badge bg-info">{{ $faculty->salaryMaster->payMatrix->matrix_code }}</span>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  @if($faculty->salaryMaster && $faculty->salaryMaster->payMatrix)
                  {{ $faculty->salaryMaster->payMatrix->full_designation }}
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>₹{{ number_format($faculty->salaryMaster->basic_salary ?? 0, 2) }}</td>
                <td>{{ $faculty->salaryMaster->effective_from ?? 'N/A' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>
<!--end main wrapper-->

@include('includes.footer')
@endsection

@section('scripts')
<script>
  function assignMatrix(facultyId) {
    const payMatrixId = document.querySelector(`select[name="pay_matrix_id_${facultyId}"]`).value;
    const effectiveFrom = document.querySelector(`input[name="effective_from_${facultyId}"]`).value;

    if (!payMatrixId) {
      alert('Please select a pay matrix');
      return;
    }

    if (!effectiveFrom) {
      alert('Please select effective from date');
      return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('
    hr.payroll.assign - pay - matrix ') }}';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';

    const facultyInput = document.createElement('input');
    facultyInput.type = 'hidden';
    facultyInput.name = 'faculty_id';
    facultyInput.value = facultyId;

    const matrixInput = document.createElement('input');
    matrixInput.type = 'hidden';
    matrixInput.name = 'pay_matrix_id';
    matrixInput.value = payMatrixId;

    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'effective_from';
    dateInput.value = effectiveFrom;

    form.appendChild(csrfInput);
    form.appendChild(facultyInput);
    form.appendChild(matrixInput);
    form.appendChild(dateInput);

    document.body.appendChild(form);
    form.submit();
  }

  function toggleFacultySelection() {
    const element = document.getElementById('facultySelection');
    element.style.display = element.style.display === 'none' ? 'block' : 'none';
  }

  function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.faculty-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.checked = selectAll.checked;
    });
  }
</script>
@endsection
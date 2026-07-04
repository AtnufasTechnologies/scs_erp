@include('includes.header')
@include('hr.sidebar')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">HR</div>
  <div class="ps-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('hr.pay-matrix.index') }}">Pay Matrix</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $payMatrix->matrix_code }}</li>
      </ol>
    </nav>
  </div>
  <div class="ms-auto d-flex gap-2">
    <a href="{{ route('hr.pay-matrix.edit', $payMatrix->id) }}" class="btn btn-warning btn-sm">
      <i class="fas fa-edit me-1"></i>Update
    </a>
    <a href="{{ route('hr.pay-matrix.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back
    </a>
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

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <small class="text-muted">Matrix Code</small>
        <h5 class="mb-0">{{ $payMatrix->matrix_code }}</h5>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <small class="text-muted">Designation / Grade</small>
        <h6 class="mb-0">{{ $payMatrix->designation }} / {{ $payMatrix->grade_level }}</h6>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <small class="text-muted">Status</small>
        <h6 class="mb-0"><span class="badge bg-{{ $payMatrix->status_color }}">{{ ucfirst($payMatrix->status) }}</span></h6>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0">Basic Details</h5>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4"><strong>Name:</strong> {{ $payMatrix->matrix_name }}</div>
      <div class="col-md-4"><strong>Employment Type:</strong> {{ ucfirst($payMatrix->employment_type) }}</div>
      <div class="col-md-4"><strong>Working Days:</strong> {{ $payMatrix->default_working_days }}</div>
      <div class="col-md-4"><strong>Pay Band:</strong> {{ $payMatrix->pay_band ?? '-' }}</div>
      <div class="col-md-4"><strong>Grade Pay:</strong> {{ $payMatrix->grade_pay ?? '-' }}</div>
      <div class="col-md-4"><strong>Faculty Assigned:</strong> {{ $facultyCount }}</div>
      <div class="col-md-4"><strong>Effective From:</strong> {{ optional($payMatrix->effective_from)->format('d-m-Y') ?? '-' }}</div>
      <div class="col-md-4"><strong>Effective To:</strong> {{ optional($payMatrix->effective_to)->format('d-m-Y') ?? '-' }}</div>
      <div class="col-md-4"><strong>Increment Month:</strong>
        {{ $payMatrix->increment_month ? DateTime::createFromFormat('!m', $payMatrix->increment_month)->format('F') : '-' }}
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-warning text-dark">
    <h6 class="mb-0">Assign Pay Matrix To Staff</h6>
  </div>
  <div class="card-body">
    <form action="{{ route('hr.pay-matrix.apply-to-faculty', $payMatrix->id) }}" method="POST">
      @csrf
      <div class="row g-3 align-items-end">
        <div class="col-md-7">
          <label class="form-label">Select Staff <span class="text-danger">*</span></label>
          <select name="faculty_ids[]" class="form-select @error('faculty_ids') is-invalid @enderror select-multiple" multiple>
            @foreach($faculties as $faculty)
            <option value="{{ $faculty->id }}">
              {{ $faculty->full_name }} @if(!empty($faculty->USER_CODE)) ({{ $faculty->USER_CODE }}) @endif
            </option>
            @endforeach
          </select>
          @error('faculty_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
          @error('faculty_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

        </div>
        <div class="col-md-3">
          <label class="form-label">Effective From <span class="text-danger">*</span></label>
          <input type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" class="form-control @error('effective_from') is-invalid @enderror" required>
          @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-user-check me-1"></i>Assign
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-light">
    <h6 class="mb-0">Currently Assigned Staff ({{ $assignedSalaryMasters->count() }})</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Code</th>
            <th>Effective From</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($assignedSalaryMasters as $assignment)
          <tr>
            <td>{{ optional($assignment->faculty)->full_name ?? '-' }}</td>
            <td>{{ optional($assignment->faculty)->USER_CODE ?? '-' }}</td>
            <td>{{ optional($assignment->effective_from)->format('d-m-Y') ?? '-' }}</td>
            <td><span class="badge bg-success">{{ ucfirst($assignment->status) }}</span></td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="text-center py-3">No staff assigned to this pay matrix yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-header bg-success text-white">
        <h6 class="mb-0">Earnings</h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between"><span>Basic Salary</span><strong>Rs {{ number_format($components['earnings']['basic_salary'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>DA</span><strong>Rs {{ number_format($components['earnings']['da'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>HRA</span><strong>Rs {{ number_format($components['earnings']['hra'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>TA</span><strong>Rs {{ number_format($components['earnings']['ta'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Medical</span><strong>Rs {{ number_format($components['earnings']['medical_allowance'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Special</span><strong>Rs {{ number_format($components['earnings']['special_allowance'], 2) }}</strong></div>
        <div class="d-flex justify-content-between"><span>Other</span><strong>Rs {{ number_format($components['earnings']['other_allowances'], 2) }}</strong></div>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-header bg-danger text-white">
        <h6 class="mb-0">Deductions</h6>
      </div>
      <div class="card-body">
        <div class="alert alert-warning mb-0">
          Deductions are not part of HR Pay Matrix. Accounts team adds deductions during payroll generation.
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header bg-dark text-white">
    <h6 class="mb-0">Salary Summary</h6>
  </div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4"><strong>Gross Salary:</strong> Rs {{ number_format($components['summary']['gross_salary'], 2) }}</div>
      <div class="col-md-4"><strong>Total Deductions:</strong> Rs {{ number_format($components['summary']['total_deductions'], 2) }}</div>
      <div class="col-md-4"><strong>Net Salary:</strong> Rs {{ number_format($components['summary']['net_salary'], 2) }}</div>
    </div>
  </div>
</div>

@if(!empty($payMatrix->description) || !empty($payMatrix->remarks))
<div class="card mb-4">
  <div class="card-header">
    <h6 class="mb-0">Notes</h6>
  </div>
  <div class="card-body">
    @if(!empty($payMatrix->description))
    <p class="mb-2"><strong>Description:</strong> {{ $payMatrix->description }}</p>
    @endif
    @if(!empty($payMatrix->remarks))
    <p class="mb-0"><strong>Remarks:</strong> {{ $payMatrix->remarks }}</p>
    @endif
  </div>
</div>
@endif

@include('includes.footer')
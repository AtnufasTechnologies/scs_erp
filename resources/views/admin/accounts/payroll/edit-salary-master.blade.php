@include('includes.header')
@include('admin.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Edit Salary Master</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.salary-masters') }}">Salary Masters</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </nav>
      </div>
    </div>

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
      <i class="fas fa-info-circle"></i> <strong>Note:</strong> Changes to this salary master will be applied to future salary slip generations.
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.salary-masters.update', $salaryMaster->id) }}">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label">Faculty</label>
              <input type="text" class="form-control" value="{{ $salaryMaster->faculty->USER_CODE ?? '' }} - {{ $salaryMaster->faculty->FIRST_NAME ?? '' }} {{ $salaryMaster->faculty->LAST_NAME ?? '' }}" readonly>
              <small class="text-muted">Faculty cannot be changed</small>
            </div>
            <div class="col-md-3">
              <label class="form-label">Effective From</label>
              <input type="date" name="effective_from" class="form-control" value="{{ $salaryMaster->effective_from ? $salaryMaster->effective_from->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Working Days</label>
              <input type="number" name="working_days" class="form-control" value="{{ $salaryMaster->working_days }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3 text-success"><i class="fas fa-plus-circle"></i> Earnings</h6>

              <div class="mb-2">
                <label class="form-label">Basic Salary*</label>
                <input type="number" name="basic_salary" class="form-control" step="0.01" value="{{ $salaryMaster->basic_salary }}" required>
              </div>
              <div class="mb-2">
                <label class="form-label">DA</label>
                <input type="number" name="da" class="form-control" step="0.01" value="{{ $salaryMaster->da }}">
              </div>
              <div class="mb-2">
                <label class="form-label">HRA</label>
                <input type="number" name="hra" class="form-control" step="0.01" value="{{ $salaryMaster->hra }}">
              </div>
              <div class="mb-2">
                <label class="form-label">TA</label>
                <input type="number" name="ta" class="form-control" step="0.01" value="{{ $salaryMaster->ta }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Medical Allowance</label>
                <input type="number" name="medical_allowance" class="form-control" step="0.01" value="{{ $salaryMaster->medical_allowance }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Special Allowance</label>
                <input type="number" name="special_allowance" class="form-control" step="0.01" value="{{ $salaryMaster->special_allowance }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Other Allowances</label>
                <input type="number" name="other_allowances" class="form-control" step="0.01" value="{{ $salaryMaster->other_allowances }}">
              </div>

              <div class="alert alert-light mt-3">
                <strong>Total Earnings:</strong> ₹{{ number_format($salaryMaster->total_earnings, 2) }}
              </div>
            </div>

            <div class="col-md-6">
              <h6 class="mb-3 text-danger"><i class="fas fa-minus-circle"></i> Deductions</h6>

              <div class="mb-2">
                <label class="form-label">PF</label>
                <input type="number" name="pf" class="form-control" step="0.01" value="{{ $salaryMaster->pf }}">
              </div>
              <div class="mb-2">
                <label class="form-label">ESI</label>
                <input type="number" name="esi" class="form-control" step="0.01" value="{{ $salaryMaster->esi }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Professional Tax</label>
                <input type="number" name="professional_tax" class="form-control" step="0.01" value="{{ $salaryMaster->professional_tax }}">
              </div>
              <div class="mb-2">
                <label class="form-label">TDS</label>
                <input type="number" name="tds" class="form-control" step="0.01" value="{{ $salaryMaster->tds }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Other Deductions</label>
                <input type="number" name="other_deductions" class="form-control" step="0.01" value="{{ $salaryMaster->other_deductions }}">
              </div>

              <div class="alert alert-light mt-3">
                <strong>Total Deductions:</strong> ₹{{ number_format($salaryMaster->total_deductions, 2) }}
              </div>

              <div class="alert alert-info mt-2">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> Loan deductions will be added automatically when generating monthly salary slips based on active loans.
              </div>
            </div>
          </div>

          <hr>

          <div class="alert alert-success">
            <h6 class="mb-0"><strong>Net Salary (excluding loans):</strong> ₹{{ number_format($salaryMaster->net_salary, 2) }}</h6>
          </div>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Any notes about this salary structure">{{ $salaryMaster->remarks }}</textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ route('admin.payroll.salary-masters') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Salary Master</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
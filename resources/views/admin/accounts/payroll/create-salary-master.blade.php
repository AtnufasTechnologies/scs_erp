@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Create Salary Master</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.salary-masters') }}">Salary Masters</a></li>
            <li class="breadcrumb-item active">Create</li>
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
      <i class="fas fa-info-circle"></i> <strong>Note:</strong> This salary master will be used to automatically generate monthly salary slips for this faculty. Set it once and generate salary slips for 250+ faculty with one click!
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.salary-masters.store') }}">
          @csrf

          <div class="row mb-4">
            <div class="col-md-6">
              <label class="form-label">Faculty*</label>
              <select name="faculty_id" class="form-select dselect-example" required>
                <option value="">Select Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
                @endforeach
              </select>
              <small class="text-muted">Only faculty without active salary masters are shown</small>
            </div>
            <div class="col-md-3">
              <label class="form-label">Effective From</label>
              <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label">Working Days</label>
              <input type="number" name="working_days" class="form-control" value="26">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3 text-success"><i class="fas fa-plus-circle"></i> Earnings</h6>

              <div class="mb-2">
                <label class="form-label">Basic Salary*</label>
                <input type="number" name="basic_salary" class="form-control" step="0.01" required>
              </div>
              <div class="mb-2">
                <label class="form-label">DA</label>
                <input type="number" name="da" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">HRA</label>
                <input type="number" name="hra" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">TA</label>
                <input type="number" name="ta" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">Medical Allowance</label>
                <input type="number" name="medical_allowance" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">Special Allowance</label>
                <input type="number" name="special_allowance" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">Other Allowances</label>
                <input type="number" name="other_allowances" class="form-control" step="0.01" value="0">
              </div>
            </div>

            <div class="col-md-6">
              <h6 class="mb-3 text-danger"><i class="fas fa-minus-circle"></i> Deductions</h6>

              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Auto Applied:</strong> TDS, EPF, PT, LWF and ESIC are pulled automatically from active deduction master assignments for the selected faculty.
              </div>

              <div class="alert alert-light mt-3">
                <strong>Important:</strong> Create/assign deduction masters first, then create salary master. Assigned deduction parameters will be applied automatically.
              </div>

              <div class="alert alert-secondary mt-3 mb-0">
                <i class="fas fa-info-circle"></i> Loan deductions are added separately during monthly payroll generation.
              </div>
            </div>
          </div>

          <hr>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2" placeholder="Any notes about this salary structure"></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ route('admin.payroll.salary-masters') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Salary Master</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
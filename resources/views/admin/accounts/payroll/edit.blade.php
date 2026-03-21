@include('includes.header')
@include('admin.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Edit Salary Slip</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
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

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.update', $salarySlip->id) }}">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <div class="col-md-4">
              <label class="form-label">Faculty</label>
              <input type="text" class="form-control" value="{{ $salarySlip->faculty->FIRST_NAME ?? '' }} {{ $salarySlip->faculty->LAST_NAME ?? '' }}" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Month</label>
              <input type="text" class="form-control" value="{{ $salarySlip->month_year }}" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label">Slip Number</label>
              <input type="text" class="form-control" value="{{ $salarySlip->salary_slip_number }}" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <input type="text" class="form-control" value="{{ ucfirst($salarySlip->status) }}" readonly>
            </div>
          </div>

          @php
          $activeLoan = \App\Models\FacultyLoan::where('faculty_id', $salarySlip->faculty_id)
          ->where('status', 'active')
          ->first();
          @endphp

          @if($activeLoan)
          <!-- Active Loan Info Alert -->
          <div class="alert alert-info mb-4" style="border-left: 4px solid #0dcaf0;">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle fa-2x me-3"></i>
              <div>
                <h6 class="mb-1"><strong>Active Loan Detected</strong></h6>
                <p class="mb-1"><strong>Loan Type:</strong> {{ $activeLoan->loan_type }} | <strong>Loan No:</strong> {{ $activeLoan->loan_number }}</p>
                <p class="mb-1"><strong>EMI Amount:</strong> ₹{{ number_format($activeLoan->emi_amount, 2) }} | <strong>Remaining:</strong> ₹{{ number_format($activeLoan->remaining_amount, 2) }}</p>
                <p class="mb-0"><strong>Installments:</strong> {{ $activeLoan->paid_installments }}/{{ $activeLoan->total_installments }} paid</p>
                <small class="text-muted"><i class="fas fa-exclamation-triangle"></i> Current loan deduction: ₹{{ number_format($salarySlip->loan_deduction, 2) }}</small>
              </div>
            </div>
          </div>
          @endif

          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3 text-success"><i class="fas fa-plus-circle"></i> Earnings</h6>

              <div class="mb-2">
                <label class="form-label">Basic Salary*</label>
                <input type="number" name="basic_salary" class="form-control" step="0.01" value="{{ $salarySlip->basic_salary }}" required>
              </div>
              <div class="mb-2">
                <label class="form-label">DA</label>
                <input type="number" name="da" class="form-control" step="0.01" value="{{ $salarySlip->da }}">
              </div>
              <div class="mb-2">
                <label class="form-label">HRA</label>
                <input type="number" name="hra" class="form-control" step="0.01" value="{{ $salarySlip->hra }}">
              </div>
              <div class="mb-2">
                <label class="form-label">TA</label>
                <input type="number" name="ta" class="form-control" step="0.01" value="{{ $salarySlip->ta }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Medical Allowance</label>
                <input type="number" name="medical_allowance" class="form-control" step="0.01" value="{{ $salarySlip->medical_allowance }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Special Allowance</label>
                <input type="number" name="special_allowance" class="form-control" step="0.01" value="{{ $salarySlip->special_allowance }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Other Allowances</label>
                <input type="number" name="other_allowances" class="form-control" step="0.01" value="{{ $salarySlip->other_allowances }}">
              </div>
            </div>

            <div class="col-md-6">
              <h6 class="mb-3 text-danger"><i class="fas fa-minus-circle"></i> Deductions</h6>

              <div class="mb-2">
                <label class="form-label">PF</label>
                <input type="number" name="pf" class="form-control" step="0.01" value="{{ $salarySlip->pf }}">
              </div>
              <div class="mb-2">
                <label class="form-label">ESI</label>
                <input type="number" name="esi" class="form-control" step="0.01" value="{{ $salarySlip->esi }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Professional Tax</label>
                <input type="number" name="professional_tax" class="form-control" step="0.01" value="{{ $salarySlip->professional_tax }}">
              </div>
              <div class="mb-2">
                <label class="form-label">TDS</label>
                <input type="number" name="tds" class="form-control" step="0.01" value="{{ $salarySlip->tds }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Loan Deduction</label>
                <input type="number" name="loan_deduction" class="form-control" step="0.01" value="{{ $salarySlip->loan_deduction }}">
              </div>
              <div class="mb-2">
                <label class="form-label">Other Deductions</label>
                <input type="number" name="other_deductions" class="form-control" step="0.01" value="{{ $salarySlip->other_deductions }}">
              </div>
            </div>
          </div>

          <hr>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Working Days</label>
              <input type="number" name="working_days" class="form-control" value="{{ $salarySlip->working_days }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Present Days</label>
              <input type="number" name="present_days" class="form-control" value="{{ $salarySlip->present_days }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Leave Days</label>
              <input type="number" name="leave_days" class="form-control" value="{{ $salarySlip->leave_days }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2">{{ $salarySlip->remarks }}</textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ route('admin.payroll.show', $salarySlip->id) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Salary Slip</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
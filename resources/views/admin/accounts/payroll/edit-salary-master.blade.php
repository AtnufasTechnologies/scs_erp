@include('includes.header')
@include('admin.accounts.sidebar')

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
              <h6 class="mb-3 text-danger"><i class="fas fa-minus-circle"></i> Deductions (Auto Applied)</h6>

              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Code</th>
                      <th>Name</th>
                      <th class="text-end">Resolved Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($assignedDeductions as $deduction)
                    <tr>
                      <td><span class="badge bg-secondary">{{ $deduction['code'] }}</span></td>
                      <td>{{ $deduction['name'] }}</td>
                      <td class="text-end">₹{{ number_format((float) ($deduction['resolved_value'] ?? 0), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted">No active deduction assignments found for this faculty.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <div class="row g-2 mt-2">
                <div class="col-md-6">
                  <div class="alert alert-light mb-0">
                    <small class="d-block text-muted">EPF to PF</small>
                    <strong>₹{{ number_format((float) ($assignedDeductionComponents['pf'] ?? 0), 2) }}</strong>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="alert alert-light mb-0">
                    <small class="d-block text-muted">ESIC to ESI</small>
                    <strong>₹{{ number_format((float) ($assignedDeductionComponents['esi'] ?? 0), 2) }}</strong>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="alert alert-light mb-0">
                    <small class="d-block text-muted">PT to Professional Tax</small>
                    <strong>₹{{ number_format((float) ($assignedDeductionComponents['professional_tax'] ?? 0), 2) }}</strong>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="alert alert-light mb-0">
                    <small class="d-block text-muted">TDS</small>
                    <strong>₹{{ number_format((float) ($assignedDeductionComponents['tds'] ?? 0), 2) }}</strong>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="alert alert-light mb-0">
                    <small class="d-block text-muted">LWF to Other Deductions</small>
                    <strong>₹{{ number_format((float) ($assignedDeductionComponents['other_deductions'] ?? 0), 2) }}</strong>
                  </div>
                </div>
              </div>

              <div class="alert alert-light mt-3">
                <strong>Total Deductions:</strong> ₹{{ number_format((
                  (float) ($assignedDeductionComponents['pf'] ?? 0) +
                  (float) ($assignedDeductionComponents['esi'] ?? 0) +
                  (float) ($assignedDeductionComponents['professional_tax'] ?? 0) +
                  (float) ($assignedDeductionComponents['tds'] ?? 0) +
                  (float) ($assignedDeductionComponents['other_deductions'] ?? 0)
                ), 2) }}
              </div>

              <div class="alert alert-info mt-2">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> These values are auto-calculated from active deduction master assignments. Loan deductions are added during payroll generation.
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
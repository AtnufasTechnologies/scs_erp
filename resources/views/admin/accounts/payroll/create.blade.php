@include('includes.header')
@include('admin.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Create Salary Slip</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
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

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.store') }}">
          @csrf

          <div class="row mb-4">
            <div class="col-md-4">
              <label class="form-label">Faculty*</label>
              <select name="faculty_id" id="faculty_id" class="form-select dselect-example" required onchange="loadFacultyInfo(this.value)">
                <option value="">Select Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Month*</label>
              <select name="month" class="form-select" required>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $m == date('n') ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                  </option>
                  @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Year*</label>
              <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Annual Session</label>
              <select name="annual_session_id" class="form-select">
                <option value="">None</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}">{{ $session->title }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Active Loan Info Alert -->
          <div id="loan-info-box" class="alert alert-info d-none mb-4" style="border-left: 4px solid #0dcaf0;">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle fa-2x me-3"></i>
              <div>
                <h6 class="mb-1"><strong>Active Loan Detected</strong></h6>
                <div id="loan-details">
                  <p class="mb-1"><strong>Loan Type:</strong> <span id="loan-type"></span> | <strong>Loan No:</strong> <span id="loan-number"></span></p>
                  <p class="mb-1"><strong>EMI Amount:</strong> ₹<span id="emi-amount"></span> | <strong>Remaining:</strong> ₹<span id="remaining-amount"></span></p>
                  <p class="mb-0"><strong>Installments:</strong> <span id="paid-installments"></span>/<span id="total-installments"></span> paid</p>
                </div>
                <small class="text-muted"><i class="fas fa-exclamation-triangle"></i> This EMI will be automatically deducted from the salary</small>
              </div>
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

              <div class="mb-2">
                <label class="form-label">PF</label>
                <input type="number" name="pf" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">ESI</label>
                <input type="number" name="esi" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">Professional Tax</label>
                <input type="number" name="professional_tax" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">TDS</label>
                <input type="number" name="tds" class="form-control" step="0.01" value="0">
              </div>
              <div class="mb-2">
                <label class="form-label">Additional Loan Deduction</label>
                <input type="number" name="additional_loan_deduction" class="form-control" step="0.01" value="0">
                <small class="text-muted">Active loan EMI will be added automatically</small>
              </div>
              <div class="mb-2">
                <label class="form-label">Other Deductions</label>
                <input type="number" name="other_deductions" class="form-control" step="0.01" value="0">
              </div>
            </div>
          </div>

          <hr>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Working Days</label>
              <input type="number" name="working_days" class="form-control" value="26">
            </div>
            <div class="col-md-4">
              <label class="form-label">Present Days</label>
              <input type="number" name="present_days" class="form-control" value="26">
            </div>
            <div class="col-md-4">
              <label class="form-label">Leave Days</label>
              <input type="number" name="leave_days" class="form-control" value="0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2"></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Salary Slip</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function loadFacultyInfo(facultyId) {
    if (!facultyId) {
      document.getElementById('loan-info-box').classList.add('d-none');
      return;
    }

    // Show loading
    const loanBox = document.getElementById('loan-info-box');
    loanBox.classList.remove('d-none');
    document.getElementById('loan-details').innerHTML = '<p class="mb-0"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';

    // Fetch faculty info
    fetch(`{{ url('/erp/admin/accounts/payroll/faculty-info') }}/${facultyId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Display loan info if exists
          if (data.loan) {
            document.getElementById('loan-type').textContent = data.loan.loan_type;
            document.getElementById('loan-number').textContent = data.loan.loan_number;
            document.getElementById('emi-amount').textContent = parseFloat(data.loan.emi_amount).toFixed(2);
            document.getElementById('remaining-amount').textContent = parseFloat(data.loan.remaining_amount).toFixed(2);
            document.getElementById('paid-installments').textContent = data.loan.paid_installments;
            document.getElementById('total-installments').textContent = data.loan.total_installments;
            document.getElementById('loan-details').innerHTML = `
            <p class="mb-1"><strong>Loan Type:</strong> ${data.loan.loan_type} | <strong>Loan No:</strong> ${data.loan.loan_number}</p>
            <p class="mb-1"><strong>EMI Amount:</strong> ₹${parseFloat(data.loan.emi_amount).toFixed(2)} | <strong>Remaining:</strong> ₹${parseFloat(data.loan.remaining_amount).toFixed(2)}</p>
            <p class="mb-0"><strong>Installments:</strong> ${data.loan.paid_installments}/${data.loan.total_installments} paid</p>
          `;
            loanBox.classList.remove('d-none');
          } else {
            loanBox.classList.add('d-none');
          }

          // Auto-fill last salary data if exists
          if (data.lastSalary) {
            document.querySelector('[name="basic_salary"]').value = data.lastSalary.basic_salary || '';
            document.querySelector('[name="da"]').value = data.lastSalary.da || 0;
            document.querySelector('[name="hra"]').value = data.lastSalary.hra || 0;
            document.querySelector('[name="ta"]').value = data.lastSalary.ta || 0;
            document.querySelector('[name="medical_allowance"]').value = data.lastSalary.medical_allowance || 0;
            document.querySelector('[name="special_allowance"]').value = data.lastSalary.special_allowance || 0;
            document.querySelector('[name="other_allowances"]').value = data.lastSalary.other_allowances || 0;
            document.querySelector('[name="pf"]').value = data.lastSalary.pf || 0;
            document.querySelector('[name="esi"]').value = data.lastSalary.esi || 0;
            document.querySelector('[name="professional_tax"]').value = data.lastSalary.professional_tax || 0;
            document.querySelector('[name="tds"]').value = data.lastSalary.tds || 0;
            document.querySelector('[name="other_deductions"]').value = data.lastSalary.other_deductions || 0;
          }
        }
      })
      .catch(error => {
        console.error('Error loading faculty info:', error);
        loanBox.classList.add('d-none');
      });
  }
</script>

@include('includes.footer')
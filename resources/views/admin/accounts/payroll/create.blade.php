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

          <!-- Active Loans Selection -->
          <div id="loans-section" class="card mb-4 d-none" style="border-left: 4px solid #0dcaf0;">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-hand-holding-usd"></i> Active Loans - Select EMIs to Deduct</h6>
            </div>
            <div class="card-body">
              <div id="loans-loading" class="text-center py-3">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading active loans...</p>
              </div>
              <div id="loans-content" class="d-none">
                <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> Select the loans for which you want to deduct EMI in this salary slip</p>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead class="table-light">
                      <tr>
                        <th width="50">
                          <input type="checkbox" id="select-all-loans" class="form-check-input" onchange="toggleAllLoans(this)">
                        </th>
                        <th>Loan Number</th>
                        <th>Loan Type</th>
                        <th>Total Amount</th>
                        <th>EMI Amount</th>
                        <th>Remaining</th>
                        <th>Progress</th>
                      </tr>
                    </thead>
                    <tbody id="loans-table-body">
                      <!-- Loans will be populated here -->
                    </tbody>
                  </table>
                </div>
                <div class="row mt-3">
                  <div class="col-md-6">
                    <p class="mb-0"><strong>Total Selected Loans:</strong> <span id="selected-count" class="badge bg-primary">0</span></p>
                  </div>
                  <div class="col-md-6 text-end">
                    <h6 class="mb-0"><strong>Total EMI Deduction:</strong> <span id="total-emi" class="text-danger">₹0.00</span></h6>
                  </div>
                </div>
              </div>
              <div id="no-loans" class="alert alert-info mb-0 d-none">
                <i class="fas fa-info-circle"></i> No active loans found for this faculty.
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
                <small class="text-muted">Any extra loan amount beyond selected EMIs above</small>
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
  let facultyLoans = [];

  function loadFacultyInfo(facultyId) {
    if (!facultyId) {
      document.getElementById('loans-section').classList.add('d-none');
      facultyLoans = [];
      return;
    }

    // Show loading
    const loansSection = document.getElementById('loans-section');
    const loansLoading = document.getElementById('loans-loading');
    const loansContent = document.getElementById('loans-content');
    const noLoans = document.getElementById('no-loans');

    loansSection.classList.remove('d-none');
    loansLoading.classList.remove('d-none');
    loansContent.classList.add('d-none');
    noLoans.classList.add('d-none');

    // Fetch faculty info
    fetch(`{{ url('/erp/admin/accounts/payroll/faculty-info') }}/${facultyId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          facultyLoans = data.loans || [];

          // Display loans if exist
          if (facultyLoans.length > 0) {
            loansLoading.classList.add('d-none');
            loansContent.classList.remove('d-none');

            const tbody = document.getElementById('loans-table-body');
            tbody.innerHTML = '';

            facultyLoans.forEach(loan => {
              const row = `
              <tr>
                <td class="text-center">
                  <input type="checkbox" name="selected_loans[]" value="${loan.id}" 
                         class="form-check-input loan-checkbox" 
                         data-emi="${loan.emi_amount}" 
                         onchange="calculateTotalEMI()">
                </td>
                <td><strong>${loan.loan_number}</strong></td>
                <td><span class="badge bg-info">${loan.loan_type}</span></td>
                <td>₹${parseFloat(loan.loan_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td><strong class="text-primary">₹${parseFloat(loan.emi_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                <td class="text-danger">₹${parseFloat(loan.remaining_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: ${loan.progress_percentage}%" 
                         aria-valuenow="${loan.progress_percentage}" 
                         aria-valuemin="0" aria-valuemax="100">
                      ${loan.progress_percentage}%
                    </div>
                  </div>
                  <small class="text-muted">${loan.paid_installments}/${loan.total_installments} paid</small>
                </td>
              </tr>
            `;
              tbody.innerHTML += row;
            });

            // Auto-select all loans by default
            document.getElementById('select-all-loans').checked = true;
            document.querySelectorAll('.loan-checkbox').forEach(cb => cb.checked = true);
            calculateTotalEMI();
          } else {
            loansLoading.classList.add('d-none');
            noLoans.classList.remove('d-none');
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
        loansLoading.classList.add('d-none');
        loansSection.classList.add('d-none');
      });
  }

  function toggleAllLoans(checkbox) {
    const loanCheckboxes = document.querySelectorAll('.loan-checkbox');
    loanCheckboxes.forEach(cb => {
      cb.checked = checkbox.checked;
    });
    calculateTotalEMI();
  }

  function calculateTotalEMI() {
    const selectedCheckboxes = document.querySelectorAll('.loan-checkbox:checked');
    let totalEMI = 0;
    let selectedCount = 0;

    selectedCheckboxes.forEach(cb => {
      totalEMI += parseFloat(cb.getAttribute('data-emi'));
      selectedCount++;
    });

    document.getElementById('total-emi').textContent = '₹' + totalEMI.toLocaleString('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
    document.getElementById('selected-count').textContent = selectedCount;

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.loan-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-loans');
    if (selectAllCheckbox && allCheckboxes.length > 0) {
      selectAllCheckbox.checked = (selectedCheckboxes.length === allCheckboxes.length);
    }
  }
</script>

@include('includes.footer')
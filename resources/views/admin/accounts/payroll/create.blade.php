@include('includes.header')
@include('admin.accounts.sidebar')

<style>
  .payroll-toolbar {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px;
  }

  .payroll-table {
    min-width: 1650px;
  }

  .payroll-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f1f5f9;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #475569;
    vertical-align: middle;
    white-space: nowrap;
  }

  .payroll-table tbody td {
    vertical-align: top;
  }

  .payroll-table tbody tr:hover {
    background: #f8fafc;
  }

  .staff-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #0f172a;
    font-weight: 700;
    font-size: 12px;
    margin-right: 10px;
  }

  .payroll-input-group .input-group-text {
    background: #f8fafc;
    color: #475569;
    font-size: 12px;
  }

  .payroll-net-pill {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    background: #ecfdf3;
    border: 1px solid #86efac;
  }

  .payroll-net-pill.text-danger {
    background: #fef2f2;
    border-color: #fca5a5;
  }

  .payroll-action-btn {
    min-width: 150px;
  }

  .payroll-filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 24px;
  }
</style>

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Create Monthly Payroll</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active">Monthly Payroll</li>
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
        @if($salaryMasters->isEmpty())
        <div class="alert alert-warning mb-0">
          No active salary masters found. Please ask HR to assign pay matrix/salary master before creating monthly payroll.
        </div>
        @else
        <form method="POST" action="{{ route('admin.payroll.store') }}" id="monthlyPayrollForm">
          @csrf
          <input type="hidden" name="individual_faculty_id" id="individualFacultyId" value="">
          <input type="hidden" name="publish_individual" id="publishIndividual" value="0">
          <input type="hidden" name="year" value="{{ date('Y') }}">
          <input type="hidden" name="annual_session_id" value="{{ old('annual_session_id', $activeAnnualSession->id ?? '') }}">

          <div class="row mb-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Month*</label>
              <select name="month" class="form-select" required>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $m == date('n') ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                  </option>
                  @endfor
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Current Active Financial Year</label>
              <input type="text" class="form-control" value="{{ $activeFinancialYear->title ?? 'No active financial year configured' }}" readonly>
            </div>
          </div>

          @if(!$activeFinancialYear)
          <div class="alert alert-warning">
            No active financial year found. Please set one from Accounts Financial Year settings before creating payroll.
          </div>
          @endif

          @if($financialYearSessionMismatch)
          <div class="alert alert-warning">
            Active financial year is {{ $activeFinancialYear->title }}, but no matching Annual Session exists.
            Payroll creation and publish are still allowed using financial year tracking.
            Create a matching Annual Session only if you want strict session mapping.
          </div>
          @endif

          <div class="alert alert-info">
            Salary is pulled from active HR pay matrix salary masters. You can add manual deductions for PF, PT, late attendance, leaves, loan EMI adjustment, and other deductions for each staff below.
          </div>

          <div class="alert alert-primary">
            <div class="fw-semibold mb-1">Draft and Final Publish Workflow</div>
            <ul class="mb-0 ps-3">
              <li><strong>Save All as Draft</strong> creates or updates monthly slips in draft status for selected month.</li>
              <li><strong>Create and Publish (Final)</strong> in a staff row creates or updates only that staff slip and marks it approved.</li>
              <li>Draft slips can be reviewed and finalized later from Payroll list using approve actions.</li>
            </ul>
          </div>

          <div class="payroll-toolbar mb-3">
            <div class="row g-2 align-items-center">
              <div class="col-lg-6">
                <label for="payrollSearch" class="form-label mb-1">Search Staff</label>
                <input type="text" id="payrollSearch" class="form-control" placeholder="Search by code, name, pay matrix, or designation">
              </div>
              <div class="col-lg-3">
                <label class="form-label mb-1">Visible Staff</label>
                <div class="form-control bg-white" id="payrollVisibleCount">0</div>
              </div>
              <div class="col-lg-3">
                <label class="form-label mb-1">Total Staff</label>
                <div class="form-control bg-white" id="payrollTotalCount">{{ $salaryMasters->count() }}</div>
              </div>
              <div class="col-12">
                <div class="payroll-filter-actions">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="filterActiveLoan">
                    <label class="form-check-label" for="filterActiveLoan">Only Active Loan</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="filterNegativeNet">
                    <label class="form-check-label" for="filterNegativeNet">Only Negative Net Salary</label>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="clearPayrollFilters">Clear Filters</button>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle payroll-table">
              <thead>
                <tr>
                  <th>Staff</th>
                  <th>Pay Matrix</th>
                  <th class="text-end">Gross (HR)</th>
                  <th style="min-width: 140px;">Present Days</th>
                  <th style="min-width: 130px;">Absent Days</th>
                  <th style="min-width: 120px;">EPFO </th>
                  <th style="min-width: 120px;">P.TAX </th>
                  <th style="min-width: 140px;">Late Deduction</th>
                  <th style="min-width: 140px;">Leave Deduction</th>
                  <th style="min-width: 150px;">EMI Deduction</th>
                  <th style="min-width: 260px;">Loan Status</th>
                  <th style="min-width: 160px;">Other Deduction</th>
                  <th class="text-end" style="min-width: 170px;">Net Salary In Hand</th>
                  <th style="min-width: 180px;">Action</th>
                  <th style="min-width: 220px;">Remarks</th>
                </tr>
              </thead>
              <tbody>
                @foreach($salaryMasters as $salaryMaster)
                @php
                $faculty = $salaryMaster->faculty;
                $grossSalary = (float) $salaryMaster->basic_salary
                + (float) $salaryMaster->da
                + (float) $salaryMaster->hra
                + (float) $salaryMaster->ta
                + (float) $salaryMaster->medical_allowance
                + (float) $salaryMaster->special_allowance
                + (float) $salaryMaster->other_allowances;
                @endphp
                <tr class="js-payroll-row"
                  data-gross="{{ number_format($grossSalary, 2, '.', '') }}"
                  data-search="{{ strtolower(trim(($faculty->USER_CODE ?? '') . ' ' . ($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? '') . ' ' . ($salaryMaster->payMatrix->matrix_code ?? '') . ' ' . ($salaryMaster->payMatrix->full_designation ?? ''))) }}"
                  data-net="{{ number_format($grossSalary - ((float) ($salaryMaster->pf ?? 0) + (float) ($salaryMaster->professional_tax ?? 0) + (float) ($salaryMaster->esi ?? 0) + (float) ($salaryMaster->tds ?? 0) + (float) ($salaryMaster->other_deductions ?? 0) + (float) ($loanDeductionMap[$faculty->id] ?? 0)), 2, '.', '') }}"
                  data-has-active-loan="{{ ((float) ($loanDeductionMap[$faculty->id] ?? 0) > 0) ? '1' : '0' }}"
                  data-base-pf="{{ number_format((float) ($salaryMaster->pf ?? 0), 2, '.', '') }}"
                  data-base-pt="{{ number_format((float) ($salaryMaster->professional_tax ?? 0), 2, '.', '') }}"
                  data-base-esi="{{ number_format((float) ($salaryMaster->esi ?? 0), 2, '.', '') }}"
                  data-base-tds="{{ number_format((float) ($salaryMaster->tds ?? 0), 2, '.', '') }}"
                  data-base-other="{{ number_format((float) ($salaryMaster->other_deductions ?? 0), 2, '.', '') }}"
                  data-auto-loan="{{ number_format((float) ($loanDeductionMap[$faculty->id] ?? 0), 2, '.', '') }}"
                  data-faculty-id="{{ $faculty->id }}">
                  <td>
                    <input type="hidden" name="faculty_ids[]" value="{{ $faculty->id }}">
                    <div class="d-flex align-items-start">
                      <span class="staff-chip">{{ strtoupper(substr($faculty->FIRST_NAME ?? 'F', 0, 1) . substr($faculty->LAST_NAME ?? 'A', 0, 1)) }}</span>
                      <div>
                        <div class="fw-semibold">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</div>
                        <small class="text-muted">Code: {{ $faculty->USER_CODE }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($salaryMaster->payMatrix)
                    <div class="fw-semibold">{{ $salaryMaster->payMatrix->matrix_code }}</div>
                    <small class="text-muted">{{ $salaryMaster->payMatrix->full_designation }}</small>
                    @else
                    <span class="text-muted">Not linked</span>
                    @endif
                  </td>
                  <td class="text-end">₹{{ number_format($grossSalary, 2) }}</td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      step="1"
                      name="present_days[{{ $faculty->id }}]"
                      class="form-control form-control-sm"
                      value="{{ (int) old('present_days.' . $faculty->id, 0) }}">
                  </td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      step="1"
                      name="absent_days[{{ $faculty->id }}]"
                      class="form-control form-control-sm"
                      value="{{ (int) old('absent_days.' . $faculty->id, 0) }}">
                  </td>
                  <td>
                    <div class="input-group input-group-sm payroll-input-group">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" name="manual_pf_deductions[{{ $faculty->id }}]" class="form-control js-deduction-input js-manual-pf" value="0">
                    </div>
                    <small class="text-muted d-block">Base: ₹{{ number_format((float) ($salaryMaster->pf ?? 0), 2) }}</small>
                  </td>
                  <td>
                    <div class="input-group input-group-sm payroll-input-group">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" name="manual_pt_deductions[{{ $faculty->id }}]" class="form-control js-deduction-input js-manual-pt" value="0">
                    </div>
                    <small class="text-muted d-block">Base: ₹{{ number_format((float) ($salaryMaster->professional_tax ?? 0), 2) }}</small>
                  </td>
                  <td>
                    <div class="input-group input-group-sm payroll-input-group">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" name="late_attendance_deductions[{{ $faculty->id }}]" class="form-control js-deduction-input js-late" value="0">
                    </div>
                  </td>
                  <td>
                    <div class="input-group input-group-sm payroll-input-group">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" name="leave_deductions[{{ $faculty->id }}]" class="form-control js-deduction-input js-leave" value="0">
                    </div>
                  </td>
                  <td>
                    @php
                    $loanSummary = $loanSummaryMap[$faculty->id] ?? null;
                    $pendingEmiCount = (int) ($loanSummary['pending_emis'] ?? 0);
                    $defaultEmiCount = $pendingEmiCount > 0 ? 1 : 0;
                    @endphp
                    <div class="form-check mb-1">
                      <input
                        class="form-check-input js-deduction-input js-skip-loan"
                        type="checkbox"
                        name="skip_loan_emi[]"
                        value="{{ $faculty->id }}"
                        id="skipLoan{{ $faculty->id }}">
                      <label class="form-check-label" for="skipLoan{{ $faculty->id }}">Skip this month</label>
                    </div>
                    <input
                      type="number"
                      min="1"
                      max="{{ $pendingEmiCount }}"
                      step="1"
                      name="emi_deduction_counts[{{ $faculty->id }}]"
                      class="form-control form-control-sm js-deduction-input js-emi-count"
                      value="{{ (int) old('emi_deduction_counts.' . $faculty->id, $defaultEmiCount) }}"
                      {{ $pendingEmiCount === 0 ? 'readonly' : '' }}>
                    <small class="text-muted d-block">Max: {{ $pendingEmiCount }}</small>
                  </td>
                  <td>
                    @if($loanSummary)
                    <small class="d-block text-muted">Active Loans: <strong>{{ $loanSummary['loan_count'] }}</strong></small>
                    <small class="d-block text-muted">EMI Paid: <strong>{{ $loanSummary['paid_emis'] }}</strong> | Pending: <strong>{{ $loanSummary['pending_emis'] }}</strong></small>
                    <small class="d-block text-muted">Pending Amount: <strong>₹{{ number_format((float) $loanSummary['pending_amount'], 2) }}</strong></small>
                    @else
                    <small class="text-success">No active loan pending</small>
                    @endif
                  </td>
                  <td>
                    <div class="input-group input-group-sm payroll-input-group">
                      <span class="input-group-text">₹</span>
                      <input type="number" step="0.01" min="0" name="manual_deductions[{{ $faculty->id }}]" class="form-control js-deduction-input js-other" value="0">
                    </div>
                  </td>
                  <td class="text-end">
                    <strong class="payroll-net-pill text-success js-net-salary">₹{{ number_format($grossSalary - ((float) ($salaryMaster->pf ?? 0) + (float) ($salaryMaster->professional_tax ?? 0) + (float) ($salaryMaster->esi ?? 0) + (float) ($salaryMaster->tds ?? 0) + (float) ($salaryMaster->other_deductions ?? 0) + (float) ($loanDeductionMap[$faculty->id] ?? 0)), 2) }}</strong>
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm btn-outline-success payroll-action-btn js-create-publish-individual-payroll" {{ !$activeFinancialYear ? 'disabled' : '' }}>
                      <i class="fas fa-upload"></i> Publish Final
                    </button>
                  </td>
                  <td>
                    <input type="text" name="remarks[{{ $faculty->id }}]" class="form-control" placeholder="Optional note">
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" id="createMonthlyPayrollBtn" {{ !$activeFinancialYear ? 'disabled' : '' }}><i class="fas fa-save"></i> Save All as Draft</button>
          </div>
        </form>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const money = new Intl.NumberFormat('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const parseNumber = (value) => {
      const n = parseFloat(value);
      return Number.isFinite(n) ? n : 0;
    };

    const calculateRowNet = (row) => {
      const gross = parseNumber(row.dataset.gross);
      const basePf = parseNumber(row.dataset.basePf);
      const basePt = parseNumber(row.dataset.basePt);
      const baseEsi = parseNumber(row.dataset.baseEsi);
      const baseTds = parseNumber(row.dataset.baseTds);
      const baseOther = parseNumber(row.dataset.baseOther);
      const autoLoan = parseNumber(row.dataset.autoLoan);

      const manualPf = parseNumber(row.querySelector('.js-manual-pf')?.value);
      const manualPt = parseNumber(row.querySelector('.js-manual-pt')?.value);
      const lateDeduction = parseNumber(row.querySelector('.js-late')?.value);
      const leaveDeduction = parseNumber(row.querySelector('.js-leave')?.value);
      const skipLoan = row.querySelector('.js-skip-loan')?.checked === true;
      const emiCountInput = row.querySelector('.js-emi-count');
      const maxEmiCount = Math.max(parseInt(emiCountInput?.max || '0', 10) || 0, 0);
      let emiCount = parseInt(emiCountInput?.value || '0', 10);
      if (!Number.isFinite(emiCount)) {
        emiCount = 0;
      }
      if (skipLoan) {
        emiCount = 0;
      }
      emiCount = Math.max(0, Math.min(emiCount, maxEmiCount));
      if (emiCountInput) {
        emiCountInput.disabled = skipLoan;
        emiCountInput.value = String(emiCount);
      }
      const otherManual = parseNumber(row.querySelector('.js-other')?.value);

      const effectiveLoanDeduction = autoLoan * emiCount;

      const totalDeductions =
        (basePf + manualPf) +
        (basePt + manualPt) +
        baseEsi +
        baseTds +
        effectiveLoanDeduction +
        (baseOther + lateDeduction + leaveDeduction + otherManual);

      const net = gross - totalDeductions;
      row.dataset.net = String(net);
      const netCell = row.querySelector('.js-net-salary');

      if (netCell) {
        netCell.textContent = '₹' + money.format(net);
        netCell.classList.toggle('text-danger', net < 0);
        netCell.classList.toggle('text-success', net >= 0);
      }
    };

    const rows = document.querySelectorAll('.js-payroll-row');
    const form = document.getElementById('monthlyPayrollForm');
    const individualFacultyIdInput = document.getElementById('individualFacultyId');
    const publishIndividualInput = document.getElementById('publishIndividual');
    const createMonthlyPayrollBtn = document.getElementById('createMonthlyPayrollBtn');
    const payrollSearchInput = document.getElementById('payrollSearch');
    const payrollVisibleCount = document.getElementById('payrollVisibleCount');
    const payrollTotalCount = document.getElementById('payrollTotalCount');
    const filterActiveLoan = document.getElementById('filterActiveLoan');
    const filterNegativeNet = document.getElementById('filterNegativeNet');
    const clearPayrollFilters = document.getElementById('clearPayrollFilters');

    const updateVisibleCount = () => {
      if (!payrollVisibleCount) {
        return;
      }
      const visible = Array.from(rows).filter((row) => row.style.display !== 'none').length;
      payrollVisibleCount.textContent = String(visible);
    };

    const applySearchFilter = () => {
      const term = (payrollSearchInput?.value || '').trim().toLowerCase();
      rows.forEach((row) => {
        const haystack = (row.dataset.search || '').toLowerCase();
        const matchesSearch = term === '' || haystack.includes(term);
        const matchesActiveLoan = !filterActiveLoan?.checked || row.dataset.hasActiveLoan === '1';
        const rowNet = parseNumber(row.dataset.net);
        const matchesNegativeNet = !filterNegativeNet?.checked || rowNet < 0;
        row.style.display = (matchesSearch && matchesActiveLoan && matchesNegativeNet) ? '' : 'none';
      });
      updateVisibleCount();
    };

    if (payrollTotalCount) {
      payrollTotalCount.textContent = String(rows.length);
    }

    if (payrollSearchInput) {
      payrollSearchInput.addEventListener('input', applySearchFilter);
    }

    if (filterActiveLoan) {
      filterActiveLoan.addEventListener('change', applySearchFilter);
    }

    if (filterNegativeNet) {
      filterNegativeNet.addEventListener('change', applySearchFilter);
    }

    if (clearPayrollFilters) {
      clearPayrollFilters.addEventListener('click', function() {
        if (payrollSearchInput) {
          payrollSearchInput.value = '';
        }
        if (filterActiveLoan) {
          filterActiveLoan.checked = false;
        }
        if (filterNegativeNet) {
          filterNegativeNet.checked = false;
        }
        applySearchFilter();
      });
    }

    if (createMonthlyPayrollBtn && individualFacultyIdInput && publishIndividualInput) {
      createMonthlyPayrollBtn.addEventListener('click', function() {
        individualFacultyIdInput.value = '';
        publishIndividualInput.value = '0';
      });
    }

    rows.forEach((row) => {
      row.querySelectorAll('.js-deduction-input').forEach((input) => {
        input.addEventListener('input', () => {
          calculateRowNet(row);
          applySearchFilter();
        });
      });

      const skipLoanInput = row.querySelector('.js-skip-loan');
      const emiCountInput = row.querySelector('.js-emi-count');
      if (skipLoanInput && emiCountInput) {
        skipLoanInput.addEventListener('change', () => {
          if (!skipLoanInput.checked) {
            const maxEmiCount = Math.max(parseInt(emiCountInput.max || '0', 10) || 0, 0);
            if (maxEmiCount > 0) {
              emiCountInput.value = '1';
            }
          }
          calculateRowNet(row);
          applySearchFilter();
        });
      }

      const publishBtn = row.querySelector('.js-create-publish-individual-payroll');
      if (publishBtn && form && individualFacultyIdInput && publishIndividualInput) {
        publishBtn.addEventListener('click', function() {
          individualFacultyIdInput.value = row.dataset.facultyId || '';
          publishIndividualInput.value = '1';
          form.submit();
        });
      }

      calculateRowNet(row);
    });

    updateVisibleCount();
  });
</script>

@include('includes.footer')
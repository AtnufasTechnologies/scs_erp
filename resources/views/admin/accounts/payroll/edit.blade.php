@include('includes.header')
@include('admin.accounts.sidebar')

<style>
  .payroll-table {
    min-width: 1450px;
  }

  .payroll-table thead th {
    background: #f1f5f9;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #475569;
    white-space: nowrap;
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
</style>

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
        @php
        $salaryMaster = \App\Models\FacultySalaryMaster::with('payMatrix')
        ->where('faculty_id', $salarySlip->faculty_id)
        ->active()
        ->first();

        $baseBasic = (float) ($salaryMaster->basic_salary ?? $salarySlip->basic_salary);
        $baseDa = (float) ($salaryMaster->da ?? $salarySlip->da);
        $baseHra = (float) ($salaryMaster->hra ?? $salarySlip->hra);
        $baseTa = (float) ($salaryMaster->ta ?? $salarySlip->ta);
        $baseMedical = (float) ($salaryMaster->medical_allowance ?? $salarySlip->medical_allowance);
        $baseSpecial = (float) ($salaryMaster->special_allowance ?? $salarySlip->special_allowance);
        $baseAllowances = (float) ($salaryMaster->other_allowances ?? $salarySlip->other_allowances);
        $grossSalary = $baseBasic + $baseDa + $baseHra + $baseTa + $baseMedical + $baseSpecial + $baseAllowances;

        $basePf = (float) ($salaryMaster->pf ?? 0);
        $basePt = (float) ($salaryMaster->professional_tax ?? 0);
        $baseEsi = (float) ($salaryMaster->esi ?? $salarySlip->esi);
        $baseTds = (float) ($salaryMaster->tds ?? $salarySlip->tds);
        $baseOther = (float) ($salaryMaster->other_deductions ?? 0);

        $manualPf = max((float) $salarySlip->pf - $basePf, 0);
        $manualPt = max((float) $salarySlip->professional_tax - $basePt, 0);
        $savedLateDeduction = !is_null($salarySlip->late_attendance_deduction) ? (float) $salarySlip->late_attendance_deduction : 0;
        $savedLeaveDeduction = !is_null($salarySlip->leave_deduction_amount) ? (float) $salarySlip->leave_deduction_amount : 0;
        $savedManualOtherDeduction = !is_null($salarySlip->manual_other_deduction) ? (float) $salarySlip->manual_other_deduction : 0;

        $legacyUnsplitExtra = 0;
        $storedOtherExtra = max((float) $salarySlip->other_deductions - $baseOther, 0);
        if ($storedOtherExtra > 0 && $savedLateDeduction == 0.0 && $savedLeaveDeduction == 0.0 && $savedManualOtherDeduction == 0.0) {
        $legacyUnsplitExtra = $storedOtherExtra;
        }

        $manualOther = $savedManualOtherDeduction;
        if ($legacyUnsplitExtra > 0) {
        // Legacy slips stored only combined other deduction; map it to leave deduction for clearer edit behavior.
        $savedLeaveDeduction = $legacyUnsplitExtra;
        }

        $activeLoans = \App\Models\FacultyLoan::where('faculty_id', $salarySlip->faculty_id)
        ->where('status', 'active')
        ->get();
        $loanPerCycle = (float) $activeLoans->sum('emi_amount');
        $pendingEmis = (int) $activeLoans->sum(function ($loan) {
        return max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);
        });

        $currentEmiCount = (int) ($salarySlip->emi_deduction_count ?? 0);
        if ($currentEmiCount === 0 && $loanPerCycle > 0 && (float) $salarySlip->loan_deduction > 0) {
        $currentEmiCount = (int) round(((float) $salarySlip->loan_deduction) / $loanPerCycle);
        }
        $currentEmiCount = max(0, min($currentEmiCount, $pendingEmis));
        @endphp

        <form method="POST" action="{{ route('admin.payroll.update', $salarySlip->id) }}">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <div class="col-md-3">
              <label class="form-label">Month</label>
              <input type="text" class="form-control" value="{{ $salarySlip->month_year }}" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label">Slip Number</label>
              <input type="text" class="form-control" value="{{ $salarySlip->salary_slip_number }}" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <input type="text" class="form-control" value="{{ ucfirst($salarySlip->status) }}" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">Faculty</label>
              <input type="text" class="form-control" value="{{ $salarySlip->faculty->FIRST_NAME ?? '' }} {{ $salarySlip->faculty->LAST_NAME ?? '' }}" readonly>
            </div>
          </div>

          <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle payroll-table">
              <thead>
                <tr>
                  <th>Staff</th>
                  <th>Pay Matrix</th>
                  <th class="text-end">Gross (HR)</th>
                  <th style="min-width: 130px;">Present Days</th>
                  <th style="min-width: 130px;">Absent Days</th>
                  <th style="min-width: 120px;">EPFO</th>
                  <th style="min-width: 120px;">P.TAX</th>
                  <th style="min-width: 140px;">Late Deduction</th>
                  <th style="min-width: 140px;">Leave Deduction</th>
                  <th style="min-width: 160px;">EMI Deduction</th>
                  <th style="min-width: 240px;">Loan Status</th>
                  <th style="min-width: 160px;">Other Deduction</th>
                  <th class="text-end" style="min-width: 170px;">Net Salary In Hand</th>
                  <th style="min-width: 220px;">Remarks</th>
                </tr>
              </thead>
              <tbody>
                <tr class="js-payroll-row"
                  data-gross="{{ number_format($grossSalary, 2, '.', '') }}"
                  data-base-pf="{{ number_format($basePf, 2, '.', '') }}"
                  data-base-pt="{{ number_format($basePt, 2, '.', '') }}"
                  data-base-esi="{{ number_format($baseEsi, 2, '.', '') }}"
                  data-base-tds="{{ number_format($baseTds, 2, '.', '') }}"
                  data-base-other="{{ number_format($baseOther, 2, '.', '') }}"
                  data-auto-loan="{{ number_format($loanPerCycle, 2, '.', '') }}">
                  <td>
                    <div class="d-flex align-items-start">
                      <span class="staff-chip">{{ strtoupper(substr($salarySlip->faculty->FIRST_NAME ?? 'F', 0, 1) . substr($salarySlip->faculty->LAST_NAME ?? 'A', 0, 1)) }}</span>
                      <div>
                        <div class="fw-semibold">{{ $salarySlip->faculty->FIRST_NAME ?? '' }} {{ $salarySlip->faculty->LAST_NAME ?? '' }}</div>
                        <small class="text-muted">Code: {{ $salarySlip->faculty->USER_CODE ?? 'N/A' }}</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($salaryMaster && $salaryMaster->payMatrix)
                    <div class="fw-semibold">{{ $salaryMaster->payMatrix->matrix_code }}</div>
                    <small class="text-muted">{{ $salaryMaster->payMatrix->full_designation }}</small>
                    @else
                    <span class="text-muted">Not linked</span>
                    @endif
                  </td>
                  <td class="text-end">₹{{ number_format($grossSalary, 2) }}</td>
                  <td>
                    <input type="number" min="0" step="1" name="present_days" class="form-control form-control-sm" value="{{ old('present_days', $salarySlip->present_days) }}">
                  </td>
                  <td>
                    <input type="number" min="0" step="1" name="absent_days" class="form-control form-control-sm" value="{{ old('absent_days', $salarySlip->leave_days) }}">
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" step="0.01" name="manual_pf_deduction" class="form-control js-deduction-input js-manual-pf" value="{{ old('manual_pf_deduction', number_format($manualPf, 2, '.', '')) }}">
                    </div>
                    <small class="text-muted d-block">Base: ₹{{ number_format($basePf, 2) }}</small>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" step="0.01" name="manual_pt_deduction" class="form-control js-deduction-input js-manual-pt" value="{{ old('manual_pt_deduction', number_format($manualPt, 2, '.', '')) }}">
                    </div>
                    <small class="text-muted d-block">Base: ₹{{ number_format($basePt, 2) }}</small>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" step="0.01" name="late_attendance_deduction" class="form-control js-deduction-input js-late" value="{{ old('late_attendance_deduction', number_format($savedLateDeduction, 2, '.', '')) }}">
                    </div>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" step="0.01" name="leave_deduction" class="form-control js-deduction-input js-leave" value="{{ old('leave_deduction', number_format($savedLeaveDeduction, 2, '.', '')) }}">
                    </div>
                  </td>
                  <td>
                    <div class="form-check mb-1">
                      <input class="form-check-input js-deduction-input js-skip-loan" type="checkbox" name="skip_loan_emi" value="1" id="skipLoanEdit" {{ old('skip_loan_emi', $currentEmiCount === 0 ? '1' : '0') === '1' ? 'checked' : '' }}>
                      <label class="form-check-label" for="skipLoanEdit">Skip this month</label>
                    </div>
                    <input type="number" min="0" max="{{ $pendingEmis }}" step="1" name="emi_deduction_count" class="form-control form-control-sm js-deduction-input js-emi-count" value="{{ old('emi_deduction_count', $currentEmiCount) }}" {{ $pendingEmis === 0 ? 'readonly' : '' }}>
                    <small class="text-muted d-block">Max: {{ $pendingEmis }}</small>
                  </td>
                  <td>
                    @if($activeLoans->count())
                    <small class="d-block text-muted">Active Loans: <strong>{{ $activeLoans->count() }}</strong></small>
                    <small class="d-block text-muted">EMI per cycle: <strong>₹{{ number_format($loanPerCycle, 2) }}</strong></small>
                    <small class="d-block text-muted">Pending EMIs: <strong>{{ $pendingEmis }}</strong></small>
                    @else
                    <small class="text-success">No active loan pending</small>
                    @endif
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">₹</span>
                      <input type="number" min="0" step="0.01" name="manual_other_deduction" class="form-control js-deduction-input js-other" value="{{ old('manual_other_deduction', number_format($manualOther, 2, '.', '')) }}">
                    </div>
                    <small class="text-muted d-block">Base: ₹{{ number_format($baseOther, 2) }}</small>
                  </td>
                  <td class="text-end">
                    <strong class="payroll-net-pill text-success js-net-salary">₹{{ number_format($salarySlip->net_salary, 2) }}</strong>
                  </td>
                  <td>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional note">{{ old('remarks', $salarySlip->remarks) }}</textarea>
                  </td>
                </tr>
              </tbody>
            </table>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const row = document.querySelector('.js-payroll-row');
    if (!row) {
      return;
    }

    const money = new Intl.NumberFormat('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const parseNumber = (value) => {
      const n = parseFloat(value);
      return Number.isFinite(n) ? n : 0;
    };

    const calculateRowNet = () => {
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
      const netCell = row.querySelector('.js-net-salary');
      if (netCell) {
        netCell.textContent = '₹' + money.format(net);
        netCell.classList.toggle('text-danger', net < 0);
        netCell.classList.toggle('text-success', net >= 0);
      }
    };

    row.querySelectorAll('.js-deduction-input').forEach((input) => {
      input.addEventListener('input', calculateRowNet);
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
        calculateRowNet();
      });
    }

    calculateRowNet();
  });
</script>

@include('includes.footer')
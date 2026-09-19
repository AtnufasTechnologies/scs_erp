@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Active Faculty Loans</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Active Loans</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.loans.cleared') }}" class="btn btn-outline-secondary me-2">
          <i class="fas fa-archive"></i> Cleared Loans
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLoanModal">
          <i class="fas fa-plus"></i> Create Loan
        </button>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Active Loans</p>
            <h4 class="mb-0">{{ $stats['active_loans'] }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Total Disbursed</p>
            <h4 class="mb-0">₹{{ number_format($stats['total_disbursed'], 0) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Total Recovered</p>
            <h4 class="mb-0 text-success">₹{{ number_format($stats['total_recovered'], 0) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Pending Recovery</p>
            <h4 class="mb-0 text-warning">₹{{ number_format($stats['pending_recovery'], 0) }}</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <select name="faculty_id" class="form-select form-select-sm">
              <option value="">All Faculty</option>
              @foreach($faculties as $faculty)
              <option value="{{ $faculty->id }}" {{ $facultyId == $faculty->id ? 'selected' : '' }}>
                {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="loan_number" class="form-control form-control-sm" value="{{ $loanNumber ?? '' }}" placeholder="Loan Number">
          </div>
          <div class="col-md-3">
            <select name="loan_type" class="form-select form-select-sm">
              <option value="">All Loan Types</option>
              @foreach(($loanTypeOptions ?? []) as $typeKey => $typeLabel)
              <option value="{{ $typeKey }}" {{ ($loanType ?? '') === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-center">
            <small class="text-muted">Showing ongoing active loans only.</small>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Loans Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Loan Number</th>
                <th>Faculty</th>
                <th>Type</th>
                <th>Months</th>
                <th>Amount</th>
                <th>EMI</th>
                <th>Progress</th>
                <th>Remaining</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($loans as $loan)
              @php
              $pendingEmis = max(((int) $loan->total_installments - (int) $loan->paid_installments), 0);
              $progressPercent = (int) round((float) ($loan->progress_percentage ?? 0));
              $loanTransactions = $loan->transactions ?? collect();
              @endphp
              <tr>
                <td>
                  <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#emiDataModal{{ $loan->id }}" title="View EMI Data">
                    <small class="text-primary fw-semibold">{{ $loan->loan_number }}</small>
                  </button>
                </td>
                <td>{{ $loan->faculty->FIRST_NAME ?? '' }} {{ $loan->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ ($loanTypeOptions[$loan->loan_type] ?? ucwords(str_replace('_', ' ', $loan->loan_type))) }}</td>
                <td>{{ (int) ($loan->advance_months ?? 1) }}</td>
                <td>₹{{ number_format($loan->loan_amount, 0) }}</td>
                <td>₹{{ number_format($loan->emi_amount, 0) }}</td>
                <td>
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success js-loan-progress" data-progress="{{ $loan->progress_percentage }}"></div>
                  </div>
                  <small>{{ $loan->paid_installments }}/{{ $loan->total_installments }}</small>
                </td>
                <td>₹{{ number_format($loan->remaining_amount, 0) }}</td>
                <td><span class="badge bg-{{ $loan->status_badge }}">{{ ucfirst($loan->status) }}</span></td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $loan->id }}" title="Update Status">
                      <i class="fas fa-edit"></i>
                    </button>
                    @if($loan->status === 'active' && (float) $loan->remaining_amount > 0 && $pendingEmis > 0)
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#repayEmiModal{{ $loan->id }}" title="Repay EMI">
                      <i class="fas fa-coins"></i>
                    </button>
                    @endif
                    @if($loan->status !== 'completed' && (float) $loan->remaining_amount > 0)
                    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#manualClearModal{{ $loan->id }}" title="Manual Clear Loan">
                      <i class="fas fa-hand-holding-usd"></i>
                    </button>
                    @endif
                    <form method="POST" action="{{ route('admin.payroll.loans.delete', $loan->id) }}" onsubmit="return confirm('Delete this loan record? This action cannot be undone.');" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete Loan">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              <!-- EMI Data Modal -->
              <div class="modal fade" id="emiDataModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog modal-md">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h6 class="modal-title">EMI Data - {{ $loan->loan_number }}</h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-2 mb-3">
                        <div class="col-md-6">
                          <small class="text-muted d-block">Faculty</small>
                          <strong>{{ $loan->faculty->FIRST_NAME ?? '' }} {{ $loan->faculty->LAST_NAME ?? '' }}</strong>
                        </div>
                        <div class="col-md-6">
                          <small class="text-muted d-block">Loan Type</small>
                          <strong>{{ ($loanTypeOptions[$loan->loan_type] ?? ucwords(str_replace('_', ' ', $loan->loan_type))) }}</strong>
                        </div>
                      </div>

                      <div class="row g-2">
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">EMI Amount</small>
                            <strong>₹{{ number_format((float) $loan->emi_amount, 2) }}</strong>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Installments</small>
                            <strong>{{ (int) $loan->paid_installments }}/{{ (int) $loan->total_installments }}</strong>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Pending EMI</small>
                            <strong>{{ $pendingEmis }}</strong>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Progress</small>
                            <strong>{{ $progressPercent }}%</strong>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Total Paid</small>
                            <strong>₹{{ number_format((float) $loan->total_paid, 2) }}</strong>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Remaining</small>
                            <strong>₹{{ number_format((float) $loan->remaining_amount, 2) }}</strong>
                          </div>
                        </div>
                      </div>

                      <div class="progress mt-3" style="height: 12px;">
                        <div class="progress-bar bg-success js-loan-progress" role="progressbar" data-progress="{{ $progressPercent }}" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>

                      <hr>
                      <h6 class="mb-2">Manual Payment Transactions</h6>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Type</th>
                              <th>Mode</th>
                              <th>Receipt No.</th>
                              <th class="text-end">Amount</th>
                              <th>EMI</th>
                              <th>Remarks</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse($loanTransactions as $transaction)
                            <tr>
                              <td>{{ optional($transaction->payment_date)->format('d M Y') ?? '-' }}</td>
                              <td>{{ ucwords(str_replace('_', ' ', (string) $transaction->transaction_type)) }}</td>
                              <td>{{ ucwords(str_replace('_', ' ', (string) $transaction->payment_mode)) }}</td>
                              <td>{{ $transaction->receipt_number ?: '-' }}</td>
                              <td class="text-end">₹{{ number_format((float) $transaction->amount, 2) }}</td>
                              <td>{{ $transaction->emi_count ? (int) $transaction->emi_count : '-' }}</td>
                              <td>{{ $transaction->remarks ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                              <td colspan="7" class="text-center text-muted">No manual transaction entries</td>
                            </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Update Status Modal -->
              <div class="modal fade" id="updateStatusModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.payroll.loans.update-status', $loan->id) }}">
                      @csrf
                      <div class="modal-header">
                        <h6 class="modal-title">Update Status</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <select name="status" class="form-select">
                          <option value="active" {{ $loan->status == 'active' ? 'selected' : '' }}>Active</option>
                          <option value="completed" {{ $loan->status == 'completed' ? 'selected' : '' }}>Completed</option>
                          <option value="suspended" {{ $loan->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Repay EMI Modal -->
              <div class="modal fade" id="repayEmiModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.payroll.loans.repay-emi', $loan->id) }}" onsubmit="return confirm('Post manual EMI repayment for this loan?');">
                      @csrf
                      <div class="modal-header">
                        <h6 class="modal-title">Repay EMI</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-2"><strong>EMI Amount:</strong> ₹{{ number_format((float) $loan->emi_amount, 2) }}</p>
                        <p class="mb-2"><strong>Pending EMI:</strong> {{ $pendingEmis }}</p>
                        <label class="form-label">No. of EMI to repay*</label>
                        <input type="number" name="emi_count" class="form-control" min="1" max="{{ $pendingEmis }}" value="1" required>
                        <label class="form-label mt-2">Payment Date*</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <label class="form-label mt-2">Payment Mode*</label>
                        <select name="payment_mode" class="form-select" required>
                          <option value="">Select payment mode</option>
                          @foreach(($paymentModeOptions ?? []) as $modeKey => $modeLabel)
                          <option value="{{ $modeKey }}">{{ $modeLabel }}</option>
                          @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Receipt number will be auto-generated on submit.</small>
                        <label class="form-label mt-2">Remarks (optional)</label>
                        <textarea name="repay_remarks" class="form-control" rows="2" placeholder="Manual repayment note"></textarea>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Repay EMI</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Manual Clear Loan Modal -->
              <div class="modal fade" id="manualClearModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('admin.payroll.loans.manual-clear', $loan->id) }}" onsubmit="return confirm('Manually clear this loan now? Remaining amount will be settled and loan marked completed.');">
                      @csrf
                      <div class="modal-header">
                        <h6 class="modal-title">Manual Loan Clearance</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-2"><strong>Remaining:</strong> ₹{{ number_format((float) $loan->remaining_amount, 2) }}</p>
                        <label class="form-label">Payment Date*</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        <label class="form-label mt-2">Payment Mode*</label>
                        <select name="payment_mode" class="form-select" required>
                          <option value="">Select payment mode</option>
                          @foreach(($paymentModeOptions ?? []) as $modeKey => $modeLabel)
                          <option value="{{ $modeKey }}">{{ $modeLabel }}</option>
                          @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Receipt number will be auto-generated on submit.</small>
                        <label class="form-label">Remarks (optional)</label>
                        <textarea name="clear_remarks" class="form-control" rows="2" placeholder="Reason for manual closure"></textarea>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-info btn-sm text-white">Clear Loan</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted">No loans found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $loans->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Create Loan Modal -->
<div class="modal fade" id="createLoanModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.payroll.loans.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create Faculty Loan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Faculty*</label>
            <select name="faculty_id" class="form-select dselect-example" id="loanFacultySelect" required>
              <option value="">Select Faculty</option>
              @foreach($faculties as $faculty)
              <option value="{{ $faculty->id }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Loan Type*</label>
            <select name="loan_type" class="form-select" required>
              @foreach(($loanTypeOptions ?? []) as $typeKey => $typeLabel)
              <option value="{{ $typeKey }}" {{ old('loan_type', 'advance') === $typeKey ? 'selected' : '' }}>{{ $typeLabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Loan Amount*</label>
              <input type="number" name="loan_amount" id="loanAmountInput" class="form-control" step="0.01" min="1" required>
              <small class="text-muted">Enter total loan amount manually.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">EMI Amount*</label>
              <input type="number" name="emi_amount" id="emiAmountInput" class="form-control" step="0.01" min="1" required>
              <small class="text-muted">Enter monthly deduction amount.</small>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">No. of EMI*</label>
              <input type="number" name="total_installments" id="totalInstallmentsInput" class="form-control" value="1" min="1" readonly required>
              <small class="text-muted">Auto-calculated as Loan amount / EMI amount.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Date*</label>
              <input type="date" name="start_date" id="loanStartDateInput" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>

          <div class="alert alert-warning d-none" id="loanFyWarning" role="alert"></div>
          <div id="loanFyMeta"
            data-fy-start="{{ optional($activeFinancialYear)->start_date ? $activeFinancialYear->start_date->format('Y-m-d') : '' }}"
            data-fy-end="{{ optional($activeFinancialYear)->end_date ? $activeFinancialYear->end_date->format('Y-m-d') : '' }}"
            data-fy-title="{{ optional($activeFinancialYear)->title ?? 'active financial year' }}"
            hidden></div>
          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Loan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-loan-progress').forEach((bar) => {
      const raw = parseFloat(bar.getAttribute('data-progress') || '0');
      const progress = Number.isFinite(raw) ? Math.max(0, Math.min(raw, 100)) : 0;
      bar.style.width = progress + '%';
    });

    const facultySelect = document.getElementById('loanFacultySelect');
    const loanAmountInput = document.getElementById('loanAmountInput');
    const totalInstallmentsInput = document.getElementById('totalInstallmentsInput');
    const emiAmountInput = document.getElementById('emiAmountInput');
    const loanStartDateInput = document.getElementById('loanStartDateInput');
    const loanFyWarning = document.getElementById('loanFyWarning');
    const loanFyMeta = document.getElementById('loanFyMeta');

    if (!facultySelect || !loanAmountInput || !totalInstallmentsInput || !emiAmountInput || !loanStartDateInput || !loanFyWarning || !loanFyMeta) {
      return;
    }

    const fyStartText = loanFyMeta.dataset.fyStart || null;
    const fyEndText = loanFyMeta.dataset.fyEnd || null;
    const fyTitle = loanFyMeta.dataset.fyTitle || 'active financial year';

    const parseDate = (raw) => {
      if (!raw) {
        return null;
      }
      const date = new Date(raw + 'T00:00:00');
      return Number.isNaN(date.getTime()) ? null : date;
    };

    const addMonthsSafe = (date, months) => {
      const next = new Date(date.getTime());
      next.setMonth(next.getMonth() + months);
      return next;
    };

    const toYmd = (date) => {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + d;
    };

    const recalculateLoanAndEmi = () => {
      const loanAmount = parseFloat(loanAmountInput.value || '0');
      const emiAmount = parseFloat(emiAmountInput.value || '0');

      let installments = 1;
      if (Number.isFinite(loanAmount) && loanAmount > 0 && Number.isFinite(emiAmount) && emiAmount > 0) {
        installments = Math.ceil(loanAmount / emiAmount);
      }
      totalInstallmentsInput.value = String(Math.max(1, installments));

      loanFyWarning.classList.add('d-none');
      loanFyWarning.textContent = '';

      const startDate = parseDate(loanStartDateInput.value);
      const fyStart = parseDate(fyStartText);
      const fyEnd = parseDate(fyEndText);

      if (!startDate || !fyStart || !fyEnd) {
        return;
      }

      if (startDate < fyStart || startDate > fyEnd) {
        loanFyWarning.classList.remove('d-none');
        loanFyWarning.textContent = 'Loan start date must be within ' + fyTitle + ' (' + toYmd(fyStart) + ' to ' + toYmd(fyEnd) + ').';
        return;
      }

      const projectedEnd = addMonthsSafe(startDate, Math.max(installments - 1, 0));
      if (projectedEnd > fyEnd) {
        loanFyWarning.classList.remove('d-none');
        loanFyWarning.textContent = 'Repayment will end on ' + toYmd(projectedEnd) + ', which is beyond ' + fyTitle + ' ending ' + toYmd(fyEnd) + '. Increase EMI or reduce loan amount.';
      }

      if (Number.isFinite(loanAmount) && Number.isFinite(emiAmount) && emiAmount > loanAmount) {
        loanFyWarning.classList.remove('d-none');
        loanFyWarning.textContent = 'EMI amount cannot be greater than loan amount.';
      }
    };

    facultySelect.addEventListener('change', recalculateLoanAndEmi);
    loanAmountInput.addEventListener('input', recalculateLoanAndEmi);
    emiAmountInput.addEventListener('input', recalculateLoanAndEmi);
    loanStartDateInput.addEventListener('change', recalculateLoanAndEmi);
    recalculateLoanAndEmi();
  });
</script>

@include('includes.footer')
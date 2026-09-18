@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Faculty Loans Management</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Faculty Loans</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
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
          <div class="col-md-4">
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
            <select name="status" class="form-select form-select-sm">
              <option value="">All Status</option>
              <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
              <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="suspended" {{ $status == 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
          </div>
          <div class="col-md-2">
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
              <tr>
                <td><small class="text-primary">{{ $loan->loan_number }}</small></td>
                <td>{{ $loan->faculty->FIRST_NAME ?? '' }} {{ $loan->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ ucfirst($loan->loan_type) }}</td>
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
                  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $loan->id }}">
                    <i class="fas fa-edit"></i>
                  </button>
                </td>
              </tr>

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
              <option value="{{ $faculty->id }}" data-full-salary="{{ number_format((float) ($fullSalaryMap[$faculty->id] ?? 0), 2, '.', '') }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Loan Type*</label>
            <select name="loan_type" class="form-select" required>
              <option value="advance" selected>Advance</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Advance Months*</label>
              <input type="number" name="advance_months" id="advanceMonthsInput" class="form-control" value="1" min="1" max="24" required>
              <small class="text-muted">Loan amount = Full salary × advance months.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Loan Amount*</label>
              <input type="number" name="loan_amount" id="loanAmountInput" class="form-control" step="0.01" readonly required>
              <small class="text-muted">Auto-calculated from selected faculty full salary.</small>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">No. of EMI*</label>
              <input type="number" name="total_installments" id="totalInstallmentsInput" class="form-control" value="1" min="1" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">EMI Amount*</label>
              <input type="number" name="emi_amount" id="emiAmountInput" class="form-control" step="0.01" readonly required>
              <small class="text-muted">Auto-calculated as Loan amount / No. of EMI.</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Date*</label>
              <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>
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
    const advanceMonthsInput = document.getElementById('advanceMonthsInput');
    const totalInstallmentsInput = document.getElementById('totalInstallmentsInput');
    const emiAmountInput = document.getElementById('emiAmountInput');

    if (!facultySelect || !loanAmountInput || !advanceMonthsInput || !totalInstallmentsInput || !emiAmountInput) {
      return;
    }

    const recalculateLoanAndEmi = () => {
      const selectedOption = facultySelect.options[facultySelect.selectedIndex];
      const fullSalary = selectedOption ? parseFloat(selectedOption.getAttribute('data-full-salary') || '0') : 0;
      const monthsRaw = parseInt(advanceMonthsInput.value || '1', 10);
      const months = Number.isFinite(monthsRaw) ? Math.max(1, Math.min(monthsRaw, 24)) : 1;
      if (String(months) !== advanceMonthsInput.value) {
        advanceMonthsInput.value = String(months);
      }

      const installmentsRaw = parseInt(totalInstallmentsInput.value || '1', 10);
      const installments = Number.isFinite(installmentsRaw) ? Math.max(1, installmentsRaw) : 1;
      if (String(installments) !== totalInstallmentsInput.value) {
        totalInstallmentsInput.value = String(installments);
      }

      const totalAmount = fullSalary * months;
      loanAmountInput.value = Number.isFinite(totalAmount) ? totalAmount.toFixed(2) : '0.00';
      const emiAmount = installments > 0 ? (totalAmount / installments) : 0;
      emiAmountInput.value = Number.isFinite(emiAmount) ? emiAmount.toFixed(2) : '0.00';
    };

    facultySelect.addEventListener('change', recalculateLoanAndEmi);
    advanceMonthsInput.addEventListener('input', recalculateLoanAndEmi);
    totalInstallmentsInput.addEventListener('input', recalculateLoanAndEmi);
    recalculateLoanAndEmi();
  });
</script>

@include('includes.footer')
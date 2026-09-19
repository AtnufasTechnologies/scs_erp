@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Cleared Faculty Loans</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.loans') }}">Active Loans</a></li>
            <li class="breadcrumb-item active">Cleared Loans</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.loans') }}" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i> Active Loans
        </a>
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

    <!-- Statistics -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Cleared Loans</p>
            <h4 class="mb-0">{{ $stats['cleared_count'] }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Disbursed (Cleared)</p>
            <h4 class="mb-0">₹{{ number_format($stats['total_disbursed'], 0) }}</h4>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <p class="mb-0 text-muted">Recovered (Cleared)</p>
            <h4 class="mb-0 text-success">₹{{ number_format($stats['total_recovered'], 0) }}</h4>
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
          <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Cleared Loans Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Loan Number</th>
                <th>Faculty</th>
                <th>Type</th>
                <th>Amount</th>
                <th>EMI</th>
                <th>Recovered</th>
                <th>Installments</th>
                <th>Closed On</th>
                <th>Details</th>
              </tr>
            </thead>
            <tbody>
              @forelse($loans as $loan)
              @php
              $progressPercent = (int) round((float) ($loan->progress_percentage ?? 0));
              $loanTransactions = $loan->transactions ?? collect();
              @endphp
              <tr>
                <td><strong>{{ $loan->loan_number }}</strong></td>
                <td>{{ $loan->faculty->FIRST_NAME ?? '' }} {{ $loan->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ ($loanTypeOptions[$loan->loan_type] ?? ucwords(str_replace('_', ' ', $loan->loan_type))) }}</td>
                <td>₹{{ number_format((float) $loan->loan_amount, 0) }}</td>
                <td>₹{{ number_format((float) $loan->emi_amount, 0) }}</td>
                <td class="text-success">₹{{ number_format((float) $loan->total_paid, 0) }}</td>
                <td>{{ (int) $loan->paid_installments }}/{{ (int) $loan->total_installments }}</td>
                <td>{{ optional($loan->end_date)->format('d M Y') ?? '-' }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#clearedLoanDataModal{{ $loan->id }}">
                    View
                  </button>
                </td>
              </tr>

              <div class="modal fade" id="clearedLoanDataModal{{ $loan->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h6 class="modal-title">Cleared Loan Details - {{ $loan->loan_number }}</h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-2 mb-3">
                        <div class="col-md-4">
                          <small class="text-muted d-block">Faculty</small>
                          <strong>{{ $loan->faculty->FIRST_NAME ?? '' }} {{ $loan->faculty->LAST_NAME ?? '' }}</strong>
                        </div>
                        <div class="col-md-4">
                          <small class="text-muted d-block">Loan Type</small>
                          <strong>{{ ($loanTypeOptions[$loan->loan_type] ?? ucwords(str_replace('_', ' ', $loan->loan_type))) }}</strong>
                        </div>
                        <div class="col-md-4">
                          <small class="text-muted d-block">Closed On</small>
                          <strong>{{ optional($loan->end_date)->format('d M Y') ?? '-' }}</strong>
                        </div>
                      </div>

                      <div class="row g-2">
                        <div class="col-md-3">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Loan Amount</small>
                            <strong>₹{{ number_format((float) $loan->loan_amount, 2) }}</strong>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">EMI Amount</small>
                            <strong>₹{{ number_format((float) $loan->emi_amount, 2) }}</strong>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Total Paid</small>
                            <strong>₹{{ number_format((float) $loan->total_paid, 2) }}</strong>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="border rounded p-2 h-100">
                            <small class="text-muted d-block">Installments</small>
                            <strong>{{ (int) $loan->paid_installments }}/{{ (int) $loan->total_installments }}</strong>
                          </div>
                        </div>
                      </div>

                      <div class="progress mt-3" style="height: 12px;">
                        <div class="progress-bar bg-success js-loan-progress" data-progress="{{ $progressPercent }}"></div>
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
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No cleared loans found</td>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-loan-progress').forEach((bar) => {
      const raw = parseFloat(bar.getAttribute('data-progress') || '0');
      const progress = Number.isFinite(raw) ? Math.max(0, Math.min(raw, 100)) : 0;
      bar.style.width = progress + '%';
    });
  });
</script>

@include('includes.footer')
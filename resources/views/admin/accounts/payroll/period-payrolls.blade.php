@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Payrolls</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active">{{ \Carbon\Carbon::create()->month((int) $month)->format('F') }} {{ $year }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <a href="{{ route('admin.payroll.period.acceptance-sheet', ['month' => $month, 'year' => $year]) }}" class="btn btn-success"><i class="fas fa-file-pdf me-1"></i>Export Acceptance Sheet</a>
        <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Monthly Payroll</a>
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

    <div class="card mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-1">{{ \Carbon\Carbon::create()->month((int) $month)->format('F') }} {{ $year }}</h5>
          <p class="mb-0 text-muted">Financial Year: {{ $activeFinancialYear->title ?? ($periodSession->title ?? 'N/A') }}</p>
        </div>
        <div class="text-muted">Generated Slips: <strong>{{ $salarySlips->total() }}</strong></div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.period.bulk-approve-paid') }}" id="bulkApprovePaidForm" onsubmit="return confirmBulkApprovePaid();" class="d-none">
          @csrf
          <input type="hidden" name="month" value="{{ $month }}">
          <input type="hidden" name="year" value="{{ $year }}">
        </form>

        <div class="row g-2 mb-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label mb-1">Payment Date *</label>
            <input type="date" name="payment_date" form="bulkApprovePaidForm" class="form-control form-control-sm" value="{{ old('payment_date', date('Y-m-d')) }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1">Payment Mode *</label>
            <select name="payment_mode" form="bulkApprovePaidForm" class="form-select form-select-sm" required>
              <option value="">Select Mode</option>
              <option value="bank_transfer" {{ old('payment_mode') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
              <option value="cash" {{ old('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
              <option value="cheque" {{ old('payment_mode') === 'cheque' ? 'selected' : '' }}>Cheque</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Payment Reference</label>
            <input type="text" name="payment_reference" form="bulkApprovePaidForm" class="form-control form-control-sm" value="{{ old('payment_reference') }}" placeholder="Txn/Cheque reference (optional)">
          </div>
          <div class="col-md-2 d-grid">
            <button type="submit" form="bulkApprovePaidForm" class="btn btn-sm btn-success"><i class="fas fa-check-circle me-1"></i>Mark Approved & Paid</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th style="width: 40px;">
                  <input type="checkbox" id="selectAllSlips" class="form-check-input" title="Select all unpaid slips">
                </th>
                <th>Slip Number</th>
                <th>Faculty</th>
                <th>Month/Year</th>
                <th>Financial Year</th>
                <th>Gross</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($salarySlips as $slip)
              <tr>
                <td>
                  @if($slip->status !== 'paid')
                  <input type="checkbox" name="slip_ids[]" value="{{ $slip->id }}" form="bulkApprovePaidForm" class="form-check-input js-slip-checkbox">
                  @else
                  <input type="checkbox" class="form-check-input" disabled>
                  @endif
                </td>
                <td><small class="text-primary fw-bold">{{ $slip->salary_slip_number }}</small></td>
                <td>{{ $slip->faculty->FIRST_NAME ?? '' }} {{ $slip->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ $slip->month_year }}</td>
                <td>{{ $slip->financialYear->title ?? ($slip->annualSession->title ?? '-') }}</td>
                <td>₹{{ number_format($slip->gross_salary, 0) }}</td>
                <td>₹{{ number_format($slip->total_deductions, 0) }}</td>
                <td><strong>₹{{ number_format($slip->net_salary, 0) }}</strong></td>
                <td><span class="badge bg-{{ $slip->status_badge }}">{{ ucfirst($slip->status) }}</span></td>
                <td>
                  <a href="{{ route('admin.payroll.show', $slip->id) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                  @if($slip->status !== 'paid')
                  <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-warning" title="Update"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('admin.payroll.destroy', $slip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary slip? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center text-muted">No payroll slips found for this period.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $salarySlips->links() }}
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllSlips');
    const checkboxes = Array.from(document.querySelectorAll('.js-slip-checkbox'));

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        checkboxes.forEach((checkbox) => {
          checkbox.checked = selectAll.checked;
        });
      });
    }

    checkboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', function() {
        if (!selectAll) {
          return;
        }
        const checkedCount = checkboxes.filter((item) => item.checked).length;
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
      });
    });
  });

  function confirmBulkApprovePaid() {
    const selected = document.querySelectorAll('.js-slip-checkbox:checked').length;
    if (selected === 0) {
      alert('Please select at least one unpaid salary slip.');
      return false;
    }
    return confirm('Mark ' + selected + ' selected salary slip(s) as approved and paid?');
  }
</script>

@include('includes.footer')
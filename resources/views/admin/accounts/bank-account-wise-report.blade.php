@include('includes.header')
@include('admin.accounts.sidebar')

<div class="container-fluid">
  <h3 class="mb-4">Bank Account Wise Payment Report</h3>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('accounts.bank-account-wise-report') }}">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search me-1"></i>Filter</button>
            <a href="{{ route('accounts.bank-account-wise-report') }}" class="btn btn-secondary"><i class="fa fa-times me-1"></i>Clear</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4 ms-auto">
      <div class="card text-white bg-primary">
        <div class="card-body p-3">
          <h6 class="card-title mb-0">Total Collected</h6>
          <p class="card-text fs-4 mb-0">₹{{ number_format($totalCollected, 2) }}</p>
        </div>
      </div>
    </div>
  </div>

  @if($report->count())
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered table-hover" id="exportTable">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Account Label</th>
            <th>Account Name</th>
            <th>Account No.</th>
            <th>Bank</th>
            <th>Branch</th>
            <th>Transactions</th>
            <th>Total Collected (₹)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($report as $row)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->acc_label ?? '—' }}</td>
            <td>{{ $row->acc_name ?? '—' }}</td>
            <td>{{ $row->acc_no ?? '—' }}</td>
            <td>{{ $row->bank_name ?? '—' }}</td>
            <td>{{ $row->branch ?? '—' }}</td>
            <td>{{ number_format($row->payment_count) }}</td>
            <td>₹{{ number_format($row->total_collected, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="7" class="text-end">Grand Total</td>
            <td>₹{{ number_format($totalCollected, 2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @else
  <div class="alert alert-info">No payments found for the selected criteria.</div>
  @endif
</div>

@include('includes.footer')
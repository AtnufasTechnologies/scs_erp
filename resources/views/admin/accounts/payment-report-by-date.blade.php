@include('includes.header')
@include('admin.accounts.sidebar')

<div class="container-fluid">
  <h3 class="mb-4">Payment Report by Date Range</h3>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('accounts.payment-report-by-date') }}">
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
            <a href="{{ route('accounts.payment-report-by-date') }}" class="btn btn-secondary"><i class="fa fa-times me-1"></i>Clear</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4 ms-auto">
      <div class="card text-white bg-success">
        <div class="card-body p-3">
          <h6 class="card-title mb-0">Total Collected ({{ $payments->count() }} payments)</h6>
          <p class="card-text fs-4 mb-0">₹{{ number_format($totalAmount, 2) }}</p>
        </div>
      </div>
    </div>
  </div>

  @if($payments->count())
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered table-hover" id="exportTable">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Invoice ID</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Fee Structure</th>
            <th>Amount (₹)</th>
            <th>Gateway Ref</th>
            <th>Mode</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $payment)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $payment->transaction_date ? date('d-M-Y', strtotime($payment->transaction_date)) : '—' }}</td>
            <td>
              <a href="{{ route('transaction.info', $payment->invoice_id) }}" class="text-decoration-none">
                {{ $payment->invoice_id }}
              </a>
            </td>
            <td class="text-uppercase">{{ $payment->studentmaster->roll_no ?? '—' }}</td>
            <td class="text-capitalize">
              {{ ($payment->studentmaster->first_name ?? '') . ' ' . ($payment->studentmaster->last_name ?? '') }}
            </td>
            <td>{{ $payment->feepaymentinfo->quarter_title ?? '—' }}</td>
            <td>₹{{ number_format($payment->amount, 2) }}</td>
            <td>{{ $payment->gateway_ref_code ?? '—' }}</td>
            <td>
              @php
              $modeMap = [1 => 'Easebuzz', 2 => 'BillDesk', 3 => 'Cash', 4 => 'Offline'];
              $modeBadge = [1 => 'bg-info', 2 => 'bg-primary', 3 => 'bg-warning text-dark', 4 => 'bg-secondary'];
              @endphp
              <span class="badge rounded-pill {{ $modeBadge[$payment->gateway_type_id] ?? 'bg-light text-dark' }}">
                {{ $modeMap[$payment->gateway_type_id] ?? 'Unknown' }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="6" class="text-end">Total</td>
            <td>₹{{ number_format($totalAmount, 2) }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @else
  <div class="alert alert-info">No payments found for the selected date range.</div>
  @endif
</div>

@include('includes.footer')
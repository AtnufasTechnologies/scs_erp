@include('includes.header')
@include('admin.accounts.sidebar')

<div class="container-fluid">
  <h3 class="mb-4">Payment Report by Type</h3>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('accounts.payment-type-report') }}">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Payment Type</label>
            <select name="payment_type" class="form-select">
              <option value="">All Types</option>
              <option value="ONLINE" {{ request('payment_type') === 'ONLINE' ? 'selected' : '' }}>Online</option>
              <option value="CASH" {{ request('payment_type') === 'CASH' ? 'selected' : '' }}>Cash / Offline</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search me-1"></i>Filter</button>
            <a href="{{ route('accounts.payment-type-report') }}" class="btn btn-secondary"><i class="fa fa-times me-1"></i>Clear</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-white bg-info">
        <div class="card-body p-3">
          <h6 class="card-title mb-0"><i class="fas fa-wifi me-1"></i> Online Payments</h6>
          <p class="card-text fs-4 mb-0">₹{{ number_format($onlineTotal, 2) }}</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-warning text-dark">
        <div class="card-body p-3">
          <h6 class="card-title mb-0"><i class="fas fa-money-bill-wave me-1"></i> Cash / Offline Payments</h6>
          <p class="card-text fs-4 mb-0">₹{{ number_format($cashTotal, 2) }}</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-success">
        <div class="card-body p-3">
          <h6 class="card-title mb-0"><i class="fas fa-coins me-1"></i> Grand Total</h6>
          <p class="card-text fs-4 mb-0">₹{{ number_format($grandTotal, 2) }}</p>
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
            <th>Payment Mode</th>
            <th>Type</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payments as $payment)
          @php
          $isOnline = in_array($payment->gateway_type_id, [1, 2]);
          $modeMap = [1 => 'Easebuzz', 2 => 'BillDesk', 3 => 'Cash', 4 => 'Offline'];
          $modeBadge = [1 => 'bg-info', 2 => 'bg-primary', 3 => 'bg-warning text-dark', 4 => 'bg-secondary'];
          @endphp
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
            <td>
              <span class="badge rounded-pill {{ $modeBadge[$payment->gateway_type_id] ?? 'bg-light text-dark' }}">
                {{ $modeMap[$payment->gateway_type_id] ?? 'Unknown' }}
              </span>
            </td>
            <td>
              <span class="badge rounded-pill {{ $isOnline ? 'bg-info' : 'bg-warning text-dark' }}">
                {{ $isOnline ? 'Online' : 'Cash/Offline' }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="6" class="text-end">Total</td>
            <td>₹{{ number_format($grandTotal, 2) }}</td>
            <td colspan="2"></td>
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
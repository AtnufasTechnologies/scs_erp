@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid">
  <h3 class="mb-4">Late Fee Revenue Report</h3>

  <div class="row mb-3">
    <div class="col-md-4">
      <form method="GET" action="">
        <div class="input-group">
          <select name="batch" class="form-select">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ (isset($selectedBatch) && $selectedBatch == $batch->id) ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
            @endforeach
          </select>
          <select name="fee_structure" class="form-select ms-2">
            <option value="">All Fee Structures</option>
            @foreach($feeStructures as $fs)
            <option value="{{ $fs->id }}" {{ (isset($selectedFeeStructure) && $selectedFeeStructure == $fs->id) ? 'selected' : '' }}>{{ $fs->quarter_title }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary ms-2" type="submit">Filter</button>
        </div>
      </form>
    </div>
    <div class="col-md-4 offset-md-4">
      <div class="card text-white bg-success mb-3">
        <div class="card-body p-2">
          <h5 class="card-title mb-0">Total Late Fee Revenue</h5>
          <p class="card-text fs-4 mb-0">₹{{ number_format($totalRevenue, 2) }}</p>
        </div>
      </div>
    </div>
  </div>
  @if(count($lateFeePayments))
  <div class="card">
    <div class="card-body">
      <table class="table table-bordered table-hover" id="exportTable">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Batch Name</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Fee Structure</th>
            <th>Late Fee Amount (₹)</th>
            <th>Paid On</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lateFeePayments as $index => $payment)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $payment->studentmaster->batchmaster->batch_name ?? 'N/A' }}</td>
            <td>{{ $payment->studentmaster->roll_no ?? 'N/A' }}</td>
            <td>{{ ($payment->studentmaster->first_name ?? '') . ' ' . ($payment->studentmaster->last_name ?? '') }}</td>
            <td>{{ $payment->feepaymentinfo->quarter_title ?? 'N/A' }}</td>
            <td>₹{{ number_format($payment->late_fee_amount, 2) }}</td>
            <td>{{ $payment->transaction_date ? date('d-M-Y', strtotime($payment->transaction_date)) : 'N/A' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center">No late fee payments found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="alert alert-info">
    No late fee payments found for the selected criteria.
  </div>
  @endif
</div>

@include('includes.footer')
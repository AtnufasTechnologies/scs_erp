@include('includes.header')
@include('admin.accounts.sidebar')

<h3><span class="text-uppercase">All Payments </span></h3>

@if(!empty($selectedFinancialYear))
<div class="alert alert-info py-2">
  Showing records for financial year:
  <strong>{{ $selectedFinancialYear->title }}</strong>
  ({{ $selectedFinancialYear->start_date?->format('d-M-Y') }} to {{ $selectedFinancialYear->end_date?->format('d-M-Y') }})
</div>
@endif

<div class="row">
  <div class="col-lg-1">
    <label for="">Refresh</label>
    <a href="{{ route('all.payments') }}"><button class="btn btn-success"><i class="fa fa-recycle"></i></button></a>

  </div>

  <div class="col-lg-8">
    <form action="{{ route('all.payments') }}" method="get">
      <div class="row">
        <div class="col-lg-3">
          <label for="financial_year_id">Financial Year</label>
          <select name="financial_year_id" id="financial_year_id" class="form-control">
            <option value="">Active Financial Year</option>
            @foreach(($financialYears ?? collect()) as $fy)
            <option value="{{ $fy->id }}" {{ (string) request('financial_year_id') === (string) $fy->id ? 'selected' : '' }}>
              {{ $fy->title }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3">
          <label for="">From</label>
          <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request()->get('from_date') }}">
        </div>
        <div class="col-lg-3 ">
          <label>To</label>
          <div class="input-group">

            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request()->get('to_date') }}">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
          </div>

        </div>
        <div class="col-lg-3 d-flex align-items-end">
          <a href="{{ route('all.payments') }}" class="btn btn-secondary w-100">Clear</a>
        </div>
      </div>
    </form>
  </div>
</div>

<table class="table table-bordered" id="exportTable">
  <thead>
    <tr>
      <th>#</th>
      <th>Transaction Date</th>
      <th>Transaction ID</th>
      <th>Description</th>
      <th>Roll No</th>
      <th>Student Name</th>
      <th>Base Amount</th>
      <th>Captured Amount</th>
      <th>Gateway Ref #</th>
      <th>Gateway Type</th>
      <th>Status</th>
      <th>Verify</th>

    </tr>
  </thead>
  <tbody>
    @if (count($payments))
    @foreach($payments as $payment)
    @php
    $student = $payment->studentmaster;
    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
    $studentName = $studentName !== '' ? $studentName : 'Unknown Student';
    $rollNo = $student->roll_no ?? 'N/A';
    $quarterTitle = $payment->feepaymentinfo->quarter_title ?? 'N/A';
    $transactionDate = $payment->transaction_date ? date('Y-m-d', strtotime($payment->transaction_date)) : '';
    $transactionDateDisplay = $payment->transaction_date ? date('d-m-Y', strtotime($payment->transaction_date)) : 'N/A';
    @endphp
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>
        <span data-bs-toggle="modal" data-bs-target="#editModal"
          data-id="{{$payment->id}}"
          data-name="{{ $studentName }}"
          data-transaction-date="{{ $transactionDate }}">
          {{ $transactionDateDisplay }}
          <span data-bs-toggle="tooltip" data-bs-title="Edit Transaction Date">
            <i class="fa fa-edit "></i>
          </span>
        </span>

      </td>
      <td><a href=" {{ route('transaction.info', ['id' => $payment->invoice_id]) }}"> <span class="btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-title="View Invoice"> {{ $payment->invoice_id }}</span></a></td>
      <td>{{ $quarterTitle }}</td>
      <td class="text-uppercase">{{ $rollNo }}</td>
      <td class="text-capitalize">{{ $studentName }}</td>
      <td>{{ $payment->amount }}</td>
      <td>{{ $payment->captured_amount }}</td>
      <td>{{ $payment->gateway_ref_code }}</td>
      <td> <span class="badge rounded-pill bg-info">
          @if ($payment->gateway_type_id == 1)
          Easebuzz
          @elseif ($payment->gateway_type_id == 2)
          BillDesk
          @elseif ($payment->gateway_type_id == 3)
          Cash
          @else
          Offline
          @endif
        </span>
      </td>
      <td>
        <span class="badge rounded-pill {{$payment->status == 'success' ? 'bg-success' :  'bg-warning'}}">{{$payment->status}}</span>
      </td>
      <td>
        @if($payment->gateway_ref_code != null)
        <a href="{{url('erp/admin/accounts/verify-transaction/'.$payment->invoice_id)}}">
          <button class="btn-sm btn-primary">Check</button>
        </a>
        @endif

      </td>


      <!-- Modal -->
      <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Transaction Date</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('update.transaction.date')}}" method="post">
              @csrf
              <input type="hidden" name="id" id="id">
              <div class="modal-body">
                <div class="form-group">
                  <label for="">Student Name</label>
                  <input type="text" name="student_name" class="form-control" id="student_name" readonly>
                </div>
                <label for="">Transaction Date</label>
                <input type="date" name="transaction_date" class="form-control" id="transaction_date">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Save changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </tr>
    @endforeach
    @else

    <span class="text-center">No payments found.<span>

        @endif

  </tbody>
</table>

@include('includes.footer')

<script>
  const editModal = document.getElementById('editModal');
  editModal.addEventListener('show.bs.modal', function(event) {

    // button which opened modal
    let button = event.relatedTarget;

    // get data attributes
    let id = button.getAttribute('data-id');
    let name = button.getAttribute('data-name');
    let transaction_date = button.getAttribute('data-transaction-date');

    // fill modal inputs
    document.getElementById('id').value = id;
    document.getElementById('student_name').value = name;
    document.getElementById('transaction_date').value = transaction_date;


  });
</script>
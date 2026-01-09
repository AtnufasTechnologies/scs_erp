@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">All Payments </span></h3>

<div class="row">
  <div class="col-lg-1">
    <label for="">Refresh</label>
    <a href="{{ route('all.payments') }}"><button class="btn btn-success"><i class="fa fa-recycle"></i></button></a>

  </div>

  <div class="col-lg-5">
    <form action="{{ route('all.payments') }}" method="get">
      <div class="row">
        <div class="col-lg-6">
          <label for="">From</label>
          <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request()->get('from_date') }}">
        </div>
        <div class="col-lg-6 ">
          <label>To</label>
          <div class="input-group">

            <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request()->get('to_date') }}">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
          </div>

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
      <th>Captured Amount</th>
      <th>Gateway Ref #</th>
      <th>Gateway Type</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @if (count($payments))
    @foreach($payments as $payment)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ date('d-m-Y', strtotime($payment->transaction_date)) }}</td>
      <td><a href="{{ route('transaction.info', ['id' => $payment->invoice_id]) }}"> <span class="btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-title="View Invoice"> {{ $payment->invoice_id }}</span></a></td>
      <td>{{$payment->feepaymentinfo->quarter_title}}</td>
      <td class="text-uppercase">{{ $payment->studentmaster->roll_no }}</td>
      <td class="text-capitalize">{{ $payment->studentmaster->first_name }} {{ $payment->studentmaster->last_name }}</td>
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
        <a href="{{url('erp/admin/accounts/verify-transaction/'.$payment->invoice_id)}}">
          <button class="btn-sm {{$payment->status == 'success' ? 'btn-success' :  'btn-warning'}}">{{$payment->status}}</button></a>
      </td>
    </tr>
    @endforeach
    @else

    <span class="text-center">No payments found.<span>

        @endif

  </tbody>
</table>

@include('includes.footer')
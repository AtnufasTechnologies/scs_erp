@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Admission Fee Collection </span></h3>
<h4>Total Fee Collected: <i class="fa fa-rupee-sign"></i> {{ $data->where('status', 'success')->sum('amount') }} /-</h4>
<h5>Daily Collection: <i class="fa fa-rupee-sign"></i> {{ $data->filter(function($item) { return \Carbon\Carbon::parse($item->created_at)->isToday(); })->where('status', 'success')->sum('amount') }} /-</h5>
<div class="row">
  <div class="col-lg-4">
    <div class="card mb-3" style="background: linear-gradient(135deg, #29a69a 0%, #12dac6 100%); ">
      <div class="card-body">
        <strong class="text-white"> Successful :</strong>
        <p class="text-white">{{ $data->where('status', 'success')->count() }}</p>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="card-body">
        <strong class="text-white">User Cancelled :</strong>
        <p class="text-white">{{ $data->where('status', 'userCancelled')->count() }}</p>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card mb-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
      <div class="card-body">
        <strong class="text-white">Failed Transactions:</strong>
        <p class="text-white">{{ $data->where('status','!=' ,'success')->where('status','!=' ,'userCancelled')->count() }}</p>
      </div>
    </div>
  </div>
</div>



<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="exportTable" class="table table-striped table-bordered" style="width:100%">
          <thead>
            <tr>
              <th>SL</th>
              <th>Program</th>
              <th>Campus</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>Application#</th>
              <th>Gateway#</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Message</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($data as $key => $item)
            <tr>
              <td>{{$key+1}}</td>
              <td>{{$item->applicationmaster->registrationmaster->application_type ?? ''}}</td>
              <td>{{$item->applicationmaster->registrationmaster->campusmaster->name ?? ''}}</td>
              <td>{{$item->applicationmaster->registrationmaster->name ?? ''}}</td>
              <td>{{$item->applicationmaster->registrationmaster->mobile ?? ''}}</td>
              <td>{{$item->txnid}}</td>
              <td>{{$item->easepayid}}</td>
              <td>{{$item->amount}}</td>
              <td>{{$item->status}}</td>
              <td>{{$item->msg}}</td>
              <td>{{date('d M Y', strtotime($item->created_at))}}</td>
            </tr>
            @endforeach
          </tbody>

        </table>
      </div>

    </div>
  </div>
</div>



@include('includes.footer')
@include('includes.header')
@include('admin.admission.sidebar')

<h3>Admission Search Engine</h3>
<p>{{$data->count()}} results found</p>

<div class="container-fluid ">
  <div class="row">
    @if(count($data))
    @foreach ($data as $item)
    <div class="col-lg-3">
      <div class="card shadow p-3">
        <div class="card-header">
          {{$item->batch}} - {{$item->application_type}} <br><span class="text-capitalize text-primary"> <strong>{{$item->first_name}} {{$item->last_name}}</strong>
          </span> <br>
          <span><i class="fa fa-envelope"></i> {{$item->mail_id}}</span> <br>
          <span><i class="fa fa-phone"></i> {{$item->mobile_no}}</span>

        </div>
        <div class="card-body">

          @if($item->applicationmaster != null && $item->applicationmaster->stdCourseMaster != null)
          <div class="alert alert-success"> {{ $item->applicationmaster->stdCourseMaster->name }}
            <hr>
            Application Code # <a href="{{route('admission.edit.application', ['id' => $item->applicationmaster->id])}}"><strong>{{$item->applicationmaster->application_code}}</strong></a>
          </div>
          @else
          <div class="alert alert-danger"> Yet to Fill Application Form</div>
          @endif
          <p><strong>Country:</strong> {{$item->countrymaster != null ? $item->countrymaster->name : '-'}}</p>
          <p><strong>OTP Verification :</strong> {!! $item->otp_verification == 1 ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>' !!}</p>
          <p><strong>Payment Status:</strong> {!! $item->applicationmaster != null ? ($item->applicationmaster->payment_gateway_status == "success" ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>') : '<i class="fa fa-exclamation-circle text-danger"></i>' !!}</p>
          <hr>
          <p><strong> Final Status:</strong> {{$item->selectioninfo != null ? $item->selectioninfo->final_status == 1 ? 'Selected' : 'Pending' : 'Awaiting Selection'}}</p>
          <p><strong>Enrollment Status:</strong> {{$item->enrollmentinfo != null ? $item->enrollmentinfo->status == 1 ? 'Enrolled' : 'Pending' : 'Not Enrolled'}}</p>
        </div>

      </div>
    </div>
    @endforeach

    @else
    <p class="text-center">No Records Found ... Try Another Search</p>
    @endif
  </div>
</div>

@include('includes.footer')
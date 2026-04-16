@include('includes.header')
@include('admin.sidebar')
<h3>New Registrations </h3>

<div class="container-fluid ">
  <table class="table table-hover" id="exportTable">
    <thead class="bg-dark text-light">
      <tr>
        <th>#</th>
        <th>Batch </th>
        <th>Campus</th>
        <th>Program Applied</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>mail ID</th>
        <th>Phone</th>
        <th>Country</th>
        <th>OTP Verification Status</th>
        <th>Application Filled Status</th>
        <th>Payment Status</th>
        <th>Account Status</th>
        <th>Created </th>
        <th>Edit</th>
      </tr>

    </thead>
    <tbody>
      @if (count($registrations))
      @foreach ($registrations as $item)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td><span class="badge bg-primary">{{$item->batch}}</span></td>
        <td><span class="badge bg-primary">{{$item->campusmaster->name}}</span></td>
        <td>
          <span class="badge bg-primary">
            {{$item->application_type }}
          </span>
        </td>
        <td class="text-capitalize fw-semibold">{{$item->first_name}}</td>
        <td class="text-capitalize fw-semibold">{{$item->last_name}}</td>
        <td>
          <a href="mailto:{{$item->mail_id}}" class="text-decoration-none ">
            <i class="bi bi-envelope"></i> {{$item->mail_id}}
          </a>
        </td>
        <td><i class="bi bi-telephone"></i> {{$item->mobile_no}}</td>
        <td class="text-capitalize">{{$item->countrymaster != null ? $item->countrymaster->name : '-'}}</td>

        <td>
          @if($item->otp_verification == '1')
          <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
          @else
          <a href="{{route('otp.status.update', ['id' => $item->id])}}"><span class="badge bg-danger"><i class="bi bi-x-circle"></i> Not Verified</span></a>
          @endif
        </td>
        <td>
          @if($item->applicationmaster == null)
          <span class="badge bg-warning text-dark">Pending</span>
          <!-- <a href="{{route('admin.fill.student.application.ug', ['id' => $item->id])}}"><span class="badge bg-primary">Fill Application</span></a> -->
          @else
          <span class="badge bg-success">Filled</span>
          @endif
        </td>
        <td>
          @if($item->applicationmaster == null)
          <span class="badge bg-danger">Not Paid</span>
          @else
          @if($item->applicationmaster->payment_gateway_status == 'success')
          <span class="badge bg-success">Paid</span>
          @else
          <span class="badge bg-danger">Not Paid</span>
          @endif
          @endif
        </td>
        <td>
          @if($item->account_status == '1')
          <span class="badge bg-success">Active</span>
          @else
          <span class="badge bg-secondary">Inactive</span>
          @endif
        </td>
        <td>{{date('d-m-Y', strtotime($item->created_at))}}</td>
        <td>
          @if($item->applicationmaster)
          <a href="{{ route('admission.edit.application', ['id' => $item->applicationmaster->id]) }}" class="btn btn-sm btn-outline-primary me-1">
            <i class="fa fa-edit"></i> Application
          </a>
          @endif
          <a href="{{ route('admin.registration.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-user-cog"></i> Details
          </a>
        </td>
      </tr>
      @endforeach
      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>
  </table>
</div>



@include('includes.footer')
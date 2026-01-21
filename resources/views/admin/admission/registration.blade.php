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
      </tr>

    </thead>
    <tbody>
      @if (count($registrations))
      @foreach ($registrations as $item)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td><span class="badge bg-primary">{{$item->batch}}</span></td>
        <td><span class="badge bg-primary">{{$item->programinfo->campus->name}}</span></td>
        <td>
          <span class="badge bg-primary">
            {{$item->programinfo != null ? $item->programinfo->name : '-'}}
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
          <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Not Verified</span>
          @endif
        </td>
        <td>
          @if($item->applicationmaster == null)
          <span class="badge bg-warning text-dark">Pending</span>
          @else
          @if($item->applicationmaster->application_status == 1)
          <span class="badge bg-success">Filled</span>
          @else
          <span class="badge bg-warning text-dark">Not Filled</span>
          @endif
          @endif
        </td>
        <td>
          @if($item->applicationmaster == null)
          <span class="badge bg-danger">Not Paid</span>
          @else
          @if($item->applicationmaster->application_status == 1)
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
      </tr>
      @endforeach
      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>
  </table>
</div>



@include('includes.footer')
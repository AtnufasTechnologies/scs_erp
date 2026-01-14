@include('includes.header')
@include('admin.sidebar')
<h3>New Registrations </h3>

<div class="container-fluid ">
  <table class="table table-hover" id="exportTable">
    <thead class="bg-dark text-light">
      <tr>
        <th>#</th>
        <th>Batch </th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>mail ID</th>
        <th>Phone</th>
        <th>Country</th>
        <th>Program Applied</th>
        <th>OTP Verification Status</th>
        <th>Application Filled Status</th>
        <th>Payment Status</th>
        <th>Account Status</th>
      </tr>

    </thead>
    <tbody>
      @if (count($registrations))

      @foreach ($registrations as $item)
      <tr>
        <td>{{$loop->iterations}}</td>
        <td>{{$item->batch}}</td>
        <td class="text-capitalize">{{$item->first_name}}</td>
        <td class="text-capitalize">{{$item->last_name}}</td>
        <td><a href="mailto:{{$item->mail_id}}">{{$item->mail_id}}</a></td>
        <td>{{$item->mobile_no}}</td>
        <td class="text-capitalize">{{$item->countrymaster != null ? $item->countrymaster->name : ''}}</td>
        <td>{{$item->programmaster != null ? $item->programmaster->program_code : ''}}</td>
        <td>{{$item->otp_verified == '1' ? 'Verified' : 'Not Verified'}}</td>
        <td>{{$item->application_filled == '1' ? 'Filled' : 'Not Filled'}}</td>
        <td>{{$item->payment_status == '1' ? 'Paid' : 'Not Paid'}}</td>
        <td>{{$item->account_status == '1' ? 'Active' : 'Inactive'}}</td>
      </tr>
      @endforeach
      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>
  </table>
</div>



@include('includes.footer')
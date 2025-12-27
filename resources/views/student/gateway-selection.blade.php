@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Fee Payment</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:" aria-label="">
          <i class="fas fa-envelope"></i> accounts.office@salesiancollege.net
        </a>
        <a href="tel:" target="_blank">
          <i class="fas fa-phone"></i> +91 0000000095
        </a>


      </div>
    </div>
  </div>
</header>


<div class="container mt-5">

  <p class="text-center">For any discrepancies, please contact the Accounts Department within 7 working days.</p>
  <p class="text-center">Do Not press back or Refresh the Screen while Payment is in Process</p>

  <div class="d-flex justify-content-center">

    <div class="card shadow p-3 center-card">
      <h3 class="text-center text-capitalize">{{ $data['studentinfo']['fullname'] }}</h3> <br>
      <form action="{{url('erp/student/fee-payment')}}" method="post">
        @csrf

        <input type="hidden" name="studentId" class="form-control mb-4 text-uppercase" value="{{ $data['studentinfo']['id'] }}" readonly>

        <p><strong>Roll No:</strong><span class="text-uppercase"> {{ $data['studentinfo']['rollno'] }}</span></p>
        <p><strong>{{$data['programinfo']}}</strong></p>
        <p><strong>Mobile:</strong> {{ $data['studentinfo']['mobile'] }}</p>
        <p><strong>Email:</strong> {{ $data['studentinfo']['email'] }}</p>
        <hr>

        <div class="card  border-0 mb-4">
          <div class="card-body">
            <h4 class="mb-3">Pending Fees *</h4>
            @foreach($data['feesinfo'] as $fee)
            <div class="border rounded p-3 mb-3 bg-light">
              <input type="checkbox" name="fee_structure_id[]" value="{{ $fee['fee_structure_id'] }}">
              <div class="row">
                <div class="col-md-8">
                  <strong>{{ $fee['fee_structure_name'] }}</strong><br>
                </div>
                <div class="col-md-4 text-end">
                  <h5 class="text-danger">₹ {{ number_format($fee['total_amount']) }}</h5>
                </div>
              </div>
            </div>
            @endforeach

          </div>
        </div>
        <div class="row mb-4">
          <p>Select Payment Gateway *</p>
          <div class="d-flex gap-4">

            <label class="gateway-option">
              <input type="radio" name="gateway" value="easebuzz" required>
              <img src="{{ asset('admin/images/easebuzz.jpg') }}" alt="Easebuzz">
            </label>

            <label class="gateway-option">
              <input type="radio" name="gateway" value="billdesk">
              <img src="{{ asset('admin/images/billdesk.jpg') }}" alt="BillDesk">
            </label>

          </div>


        </div>
        <div class="d-flex justify-content-center">
          <button type="submit" class="btn btn-primary" id="payBtn" disabled>Proceed to Payment</button>
        </div>
      </form>
    </div>
  </div>

</div>



@include('includes.footer')
@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Checkout</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:" aria-label="">
          <i class="fas fa-envelope"></i> admissionenquiry@salesiancollege.net
        </a>
        <a href="tel:" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622
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
      <h5 class="text-capitalize">{{ $data->full_name }}</h5>
      <p>{{$data->mail_id}}</p>
      <p>{{$data->mobile_no}}</p>
      <p><strong>Application For :</strong> {{$data->campusmaster->name}} - {{$data->application_type}}</p>
      <p><strong>Selected Degree :</strong> {{$data->applicationmaster->stdCourseMaster->name}}</p>
      <hr>
      <form action="{{route('admission.payment.process')}}" method="post">
        @csrf

        <div class="shadow card payment-card border-0 mb-4" role="region" aria-label="Payment summary" style="overflow:hidden;border-radius:12px;box-shadow:0 6px 20px rgba(24,39,75,0.08);background:linear-gradient(135deg,#f8fbff 0%,#eaf3ff 100%);">
          <div class="d-flex align-items-center justify-content-between p-3 p-md-4" style="gap:1rem;background:transparent;">
            <div class="d-flex align-items-center gap-3">

              <div>
                <h5 class="mb-1 fw-bold">Application Amount</h5>
                <p class="mb-1 text-muted small">Amount to be paid for application processing · Secure checkout</p>
                <div class="mt-2">
                  <span class="badge bg-light text-dark me-2" style="font-weight:600;">No hidden fees</span>
                  <span class="badge bg-success text-white">PCI Secure</span>
                </div>
              </div>
            </div>
            <div class="text-end">
              <p class="mb-1 text-muted small">Payable</p>
              <p class="display-6 fw-bold mb-0">₹ {{ number_format($amount) }}</p>
            </div>
          </div>
        </div>


        <div class="row mb-4">
          <p>Select Payment Gateway *</p>
          <div class="d-flex gap-4">

            <label class="gateway-option">
              <input type="radio" name="gateway" value="easebuzz" required checked>
              <img src="{{ asset('admin/images/easebuzz.jpg') }}" alt="Easebuzz">
            </label>

            <!-- <label class="gateway-option">
              <input type="radio" name="gateway" value="billdesk">
              <img src="{{ asset('admin/images/billdesk.jpg') }}" alt="BillDesk">
            </label> -->

          </div>


        </div>
        <div class="d-flex justify-content-center">
          <button type="submit" class="btn btn-main">PAY NOW</button>

        </div>
      </form>
      <a href="{{route('admission.apply.logout')}}" class="text-danger">Cancel and Logout</a>
    </div>
  </div>

</div>
@include('includes.footer')
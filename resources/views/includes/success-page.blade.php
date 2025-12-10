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



  <div class="d-flex justify-content-center">

    <div class="card shadow p-3 center-card">
      <h3 class="text-center text-capitalize">{{ $studentinfo->fullname }}</h3> <br>
      <p><strong>Roll No:</strong><span class="text-uppercase"> {{ $studentinfo->rollno}}</span></p>
      <p><strong>{{$data['programinfo']}}</strong></p>
      <p><strong>Mobile:</strong> {{ $studentinfo->mobile }}</p>
      <p><strong>Email:</strong> {{ $studentinfo->email }}</p>
      <p><strong>Invoice:</strong> {{ $txnid }}</p>
      <p><strong>Gateway ID# {{$gateway_id}}</strong></p>
      <p><strong>Amount:</strong> {{ $amount }}</p>
      <p><strong>{{$status}}</strong></p>

      <p class="text-center">For any discrepancies, please contact the Accounts Department within 7 working days.</p>
      <p class="text-center">Keep note of the Invoice No # {{ $txnid }}</p>
    </div>
  </div>

</div>


@include('includes.footer')
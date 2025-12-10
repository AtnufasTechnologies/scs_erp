@include('includes.header')



<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Fee payment</span></h6>
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

  <div class="d-flex justify-content-center">

    <div class="card shadow p-3 center-card">
      <h1>Student Zone</h1> <br>
      <form action="{{url('erp/student/fee-status')}}" method="post">
        @csrf
        <label for="">Enter Your Rollno <span class="text-danger">*</span></label> <br>
        <input type="text" name="rollno" class="form-control mb-4 text-uppercase">
        @error('rollno')
        <p><span class="text-danger">{{$message}}</span></p>
        @enderror
        <button type="submit" class="btn btn-primary">Submit</button>


      </form>
    </div>
  </div>
</div>

@include('includes.footer')
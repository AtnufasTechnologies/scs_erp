@include('includes.header')



<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">OTP Validation</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:admissionenquiry@salesiancollege.net" aria-label="Email">
          <i class="fas fa-envelope"></i> admissionenquiry@salesiancollege.net
        </a>
        <a href="tel:+919933402478" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622 (Siliguri Campus)
        </a>
      </div>
      <div class="contact-links">
        <a href="mailto:salesiancollegesonada@gmail.com" aria-label="">
          <i class="fas fa-envelope"></i> salesiancollegesonada@gmail.com
        </a>
        <a href="tel:+917602032968" target="_blank">
          <i class="fas fa-phone"></i> 76020 32968 / 99336 40168 (Sonada)
        </a>
      </div>
    </div>
  </div>
</header>



<div class="container mt-5">

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="d-flex justify-content-center">
    <form method="POST" action="{{ route('otp.verify') }}" class="form-card">
      @csrf
      <div class="form-group mb-3 ">
        <p class="form-card-title">Verification Code</p>
        <p class="form-card-prompt">Enter 6 digits OTP sent on Mobile Number and Email</p>
        <div class="form-card-input-wrapper">
          <input class="form-card-input" placeholder="______" maxlength="6" type="tel" name="otp">
          <div class="form-card-input-bg"></div>
        </div>
        @error('otp')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
      </div>

      <button type="submit" class="btn btn-main w-100">Submit </button>
    </form>
  </div>
  <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
    @csrf
    <button type="submit" id="resendBtn" class="btn btn-link w-100" disabled>Resend OTP <span id="timer">(02:00)</span></button>
  </form>
  <a href="{{route('admission.apply.logout')}}">
    <button class="mb-3 btn btn-dark">Logout</button>
  </a>
</div>

<script>
  let timer = 120;
  const resendBtn = document.getElementById('resendBtn');
  const timerSpan = document.getElementById('timer');

  function updateTimer() {
    let minutes = String(Math.floor(timer / 60)).padStart(2, '0');
    let seconds = String(timer % 60).padStart(2, '0');
    timerSpan.textContent = `(${minutes}:${seconds})`;
    if (timer > 0) {
      timer--;
      setTimeout(updateTimer, 1000);
    } else {
      resendBtn.disabled = false;
      timerSpan.textContent = '';
    }
  }

  updateTimer();
</script>


@include('includes.footer')
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
        <a href="mailto:" aria-label="">
          <i class="fas fa-envelope"></i> admissions@salesiancollege.net
        </a>
        <a href="tel:" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622
        </a>


      </div>
    </div>
  </div>
</header>



<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">

        <div class="card-body">
          @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <form method="POST" action="{{ route('otp.verify') }}">
            @csrf
            <div class="form-group mb-3">
              <label for="otp">OTP</label>
              <input type="text" name="otp" id="otp" class="form-control @error('otp') is-invalid @enderror" required autofocus>
              @error('otp')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>
            <input type="hidden" name="applicantId" value="{{$userId}}">
            <button type="submit" class="btn btn-main w-100">Verify OTP</button>
          </form>
          <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
            @csrf
            <input type="hidden" name="applicantId" value="{{$userId}}">
            <button type="submit" id="resendBtn" class="btn btn-link w-100" disabled>Resend OTP <span id="timer">(02:00)</span></button>
          </form>
        </div>
      </div>
    </div>
  </div>
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
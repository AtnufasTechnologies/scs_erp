@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{ asset('admin/images/logo.png') }}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Examination Results</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:coe@salesiancollege.net" aria-label="Email">
          <i class="fas fa-envelope"></i> coe@salesiancollege.net
        </a>
      </div>
    </div>
  </div>
</header>

<div class="container mt-5 mb-5">
  <p class="text-center text-muted">View your semester-wise examination results by entering your enrollment number below.</p>

  <div class="d-flex justify-content-center">
    <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
      <h4 class="text-center mb-4"><i class="fas fa-trophy me-2 text-primary"></i>Result Lookup</h4>

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <form action="{{ url('erp/student/results/search') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label for="enrollment_no" class="form-label fw-semibold">Enrollment Number <span class="text-danger">*</span></label>
          <input type="text" name="enrollment_no" id="enrollment_no" class="form-control text-uppercase"
            placeholder="Enter your enrollment number" value="{{ old('enrollment_no') }}" required>
          @error('enrollment_no')
          <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-search me-2"></i>View Results
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
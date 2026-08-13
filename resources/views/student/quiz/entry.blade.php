@include('includes.header')

<style>
  .fa1-login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: linear-gradient(135deg, #6a11cb 30%, #2575fc 100%);
    overflow: hidden;
    padding: 32px 12px;
  }

  .fa1-login-page::before,
  .fa1-login-page::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.6;
  }

  .fa1-login-page::before {
    width: 420px;
    height: 420px;
    background: rgba(255, 255, 255, 0.12);
    top: -120px;
    left: -120px;
  }

  .fa1-login-page::after {
    width: 360px;
    height: 360px;
    background: rgba(255, 255, 255, 0.16);
    bottom: -120px;
    right: -100px;
  }

  .fa1-login-shell {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 520px;
  }

  .fa1-brand-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 0.75rem;
    background: #ffffff;
    border-radius: 16px;
    padding: 8px;
  }

  .fa1-entry-card {
    border-radius: 26px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
    overflow: hidden;
  }

  .fa1-entry-card .card-header {
    border-bottom: 0;
    padding: 1.75rem 1.5rem 0.5rem;
  }

  .fa1-entry-card .card-body {
    padding: 1rem 1.5rem 1.75rem;
  }

  .fa1-entry-card .form-label {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
  }

  .fa1-entry-card .form-control {
    border: 2px solid #e8e8e8;
    border-radius: 14px;
    padding: 0.7rem 0.85rem;
    background: #f8f9fa;
    color: #1a1a2e;
    font-weight: 500;
  }

  .fa1-entry-card .form-control:focus {
    border-color: #667eea;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .fa1-entry-card .btn-success {
    min-width: 230px;
    border: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 0.72rem 1.2rem;
    font-weight: 600;
  }

  .fa1-title {
    color: #1a1a2e;
    font-size: 1.6rem;
  }

  .fa1-subtitle {
    color: #666;
    font-size: 0.92rem;
  }
</style>

<div class="wrapper">
  <main class="page-content">
    <div class="fa1-login-page">
      <div class="fa1-login-shell">
        <div class="card fa1-entry-card border-0">

          <div class="card-header bg-white">
            <h4 class="fw-bold mb-0 text-center fa1-title">Online Examinations</h4>
            <p class="text-center mb-0 mt-1 fa1-subtitle">Salesian College Autonomous - FA 1 Access</p>
          </div>
          <div class="card-body">
            <div class="text-center mb-3">
              <img src="{{ asset('admin/images/logo.png') }}" alt="Salesian College Autonomous" class="fa1-brand-logo">

            </div>
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
              {{ session('info') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif
            <hr>
            <form method="POST" action="{{ route('student.fa1.access.verify') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label fw-bold">Enter Your Roll Number</label>
                <input type="text" name="roll_no" class="form-control" value="{{ old('roll_no') }}" placeholder="e.g. BSC24001" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Enter Password</label>
                <input type="password" name="password" class="form-control" placeholder="Your student account password" required>
              </div>
              <div class="row justify-content-center">
                <button type="submit" class="btn btn-success">Verify & Enter Portal</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>


@include('includes.footer')
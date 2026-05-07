@include('includes.header')

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    overflow-x: hidden;
  }

  .login-page {
    min-height: 100vh;
    display: flex;
    position: relative;
    background: linear-gradient(135deg, #6a11cb 30%, #2575fc 100%);
    overflow: hidden;
  }

  /* Animated Background Blobs */
  .login-page::before,
  .login-page::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.7;
    animation: float 20s ease-in-out infinite;
  }

  .login-page::before {
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.1);
    top: -100px;
    left: -100px;
    animation-delay: 0s;
  }

  .login-page::after {
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.15);
    bottom: -100px;
    right: -100px;
    animation-delay: 10s;
  }

  @keyframes float {

    0%,
    100% {
      transform: translate(0, 0) rotate(0deg);
    }

    25% {
      transform: translate(50px, 50px) rotate(90deg);
    }

    50% {
      transform: translate(0, 100px) rotate(180deg);
    }

    75% {
      transform: translate(-50px, 50px) rotate(270deg);
    }
  }

  /* Left Side - Branding */
  .login-left {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
    position: relative;
    z-index: 1;
  }

  .branding-content {
    max-width: 500px;
    color: white;
    animation: fadeInLeft 1s ease;
  }

  @keyframes fadeInLeft {
    from {
      opacity: 0;
      transform: translateX(-50px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .college-logo {
    width: 100px;
    height: 100px;
    background: rgb(255, 255, 255);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    /* box-shadow: 0 10px 10px rgb(254, 186, 91); */
    /* backdrop-filter: blur(10px); */
    animation: float-logo 3s ease-in-out infinite;
  }

  @keyframes float-logo {

    0%,
    100% {
      transform: translateY(0px);
    }

    50% {
      transform: translateY(-10px);
    }
  }

  .college-logo i {
    font-size: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .branding-content h1 {
    font-size: 48px;
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
    text-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
  }

  .branding-content p {
    font-size: 18px;
    opacity: 0.95;
    line-height: 1.8;
    margin-bottom: 30px;
  }

  .features-list {
    list-style: none;
    margin-top: 40px;
  }

  .features-list li {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    font-size: 16px;
    opacity: 0.9;
  }

  .features-list li i {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    backdrop-filter: blur(10px);
  }

  /* Right Side - Login Form */
  .login-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
    position: relative;
    z-index: 1;
  }

  .login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 50px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    animation: fadeInRight 1s ease;
  }

  @keyframes fadeInRight {
    from {
      opacity: 0;
      transform: translateX(50px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  .login-card-header {
    text-align: center;
    margin-bottom: 40px;
  }

  .login-card-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 10px;
  }

  .login-card-header p {
    font-size: 15px;
    color: #666;
  }

  /* Alert Styles */
  .alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 30px;
    border: none;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .alert-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
  }

  .alert ul {
    margin: 0;
    padding-left: 20px;
    list-style: none;
  }

  .alert li {
    position: relative;
    padding-left: 24px;
    margin-bottom: 5px;
  }

  .alert li::before {
    content: '⚠️';
    position: absolute;
    left: 0;
  }

  /* Form Styles */
  .form-group {
    margin-bottom: 28px;
    position: relative;
  }

  .form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 10px;
    transition: all 0.3s ease;
  }

  .input-group {
    position: relative;
  }

  .input-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 20px;
    transition: all 0.3s ease;
    z-index: 1;
  }

  .form-input {
    width: 100%;
    padding: 16px 20px 16px 55px;
    border: 2px solid #e8e8e8;
    border-radius: 16px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: #1a1a2e;
    font-weight: 500;
  }

  .form-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .form-input:focus~.input-icon {
    color: #667eea;
    transform: translateY(-50%) scale(1.1);
  }

  .form-input::placeholder {
    color: #aaa;
  }

  /* Password Toggle */
  .password-toggle {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s ease;
    z-index: 1;
    padding: 5px;
  }

  .password-toggle:hover {
    color: #667eea;
  }

  /* Remember Me & Forgot Password */
  .form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
    font-size: 14px;
  }

  .remember-me {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
  }

  .remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    cursor: pointer;
    accent-color: #667eea;
  }

  .remember-me label {
    cursor: pointer;
    color: #666;
    font-weight: 500;
  }

  .forgot-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .forgot-link:hover {
    color: #764ba2;
  }

  /* Login Button */
  .btn-login {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #6a11cb 30%, #2575fc 100%);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
  }

  .btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
  }

  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
  }

  .btn-login:hover::before {
    left: 100%;
  }

  .btn-login:active {
    transform: translateY(0);
  }

  .btn-login i {
    margin-left: 8px;
    transition: transform 0.3s ease;
  }

  .btn-login:hover i {
    transform: translateX(5px);
  }

  /* Footer */
  .login-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #e8e8e8;
  }

  .login-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .login-footer a:hover {
    color: #764ba2;
  }

  /* Loading State */
  .btn-login.loading {
    pointer-events: none;
    opacity: 0.7;
  }

  .btn-login.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .login-left {
      display: none;
    }

    .login-right {
      flex: 1;
      padding: 30px 20px;
    }

    .login-card {
      padding: 40px 30px;
    }
  }

  @media (max-width: 576px) {
    .login-card {
      padding: 30px 20px;
      border-radius: 24px;
    }

    .login-card-header h2 {
      font-size: 26px;
    }

    .btn-login {
      padding: 16px;
    }

    .form-options {
      flex-direction: column;
      align-items: flex-start;
      gap: 15px;
    }
  }

  /* Dark particles effect */
  .particles {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    top: 0;
    left: 0;
    pointer-events: none;
  }

  .particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    animation: particle-float 15s infinite ease-in-out;
  }

  @keyframes particle-float {

    0%,
    100% {
      transform: translateY(0) translateX(0);
      opacity: 0;
    }

    10% {
      opacity: 1;
    }

    90% {
      opacity: 1;
    }

    100% {
      transform: translateY(-100vh) translateX(100px);
      opacity: 0;
    }
  }
</style>

<div class="login-page">
  <!-- Animated Particles -->
  <div class="particles">
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
    <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
    <div class="particle" style="left: 50%; animation-delay: 8s;"></div>
    <div class="particle" style="left: 60%; animation-delay: 10s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
    <div class="particle" style="left: 80%; animation-delay: 14s;"></div>
    <div class="particle" style="left: 90%; animation-delay: 3s;"></div>
  </div>

  <!-- Left Side - Branding -->
  <div class="login-left">
    <div class="branding-content">
      <div class="college-logo">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Salesian College Logo" style="width:72px; height:72px; border-radius:16px; box-shadow:0 2px 12px rgba(102,126,234,0.12); background:#fff; object-fit:contain;" />
      </div>
      <h1 class="text-light">Salesian College Autonomous<br> <span style="font-size: 26px; color:#f6d365">Sonada & Siliguri</span></h1>
      <p>Experience the future of education management with our comprehensive ERP system. Streamline operations, enhance communication, and empower excellence.</p>

      <ul class="features-list">
        <li>
          <i class="fas fa-check-circle"></i>
          <span>Comprehensive Student Management</span>
        </li>
        <li>
          <i class="fas fa-check-circle"></i>
          <span>Real-time Attendance Tracking</span>
        </li>
        <li>
          <i class="fas fa-check-circle"></i>
          <span>Advanced Timetable System</span>
        </li>
        <li>
          <i class="fas fa-check-circle"></i>
          <span>Secure & Cloud-based Platform</span>
        </li>
      </ul>
    </div>
  </div>

  <!-- Right Side - Login Form -->
  <div class="login-right">
    <div class="login-card">
      <div class="login-card-header">
        <h2>Student | Alumni Zone!</h2>
        <p>Sign in to access your dashboard</p>
      </div>

      @if($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <form action="{{ route('student.login.submit') }}" method="POST" id="loginForm" autocomplete="off">
        @csrf

        <div class="form-group">
          <label for="roll_number" class="form-label">Roll Number</label>
          <div class="input-group">
            <i class="fas fa-text input-icon"></i>
            <input
              type="text"
              class="form-input text-uppercase"
              id="roll_number"
              name="roll_no"
              placeholder="Enter your roll number"
              value="{{ old('roll_no') }}">
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input
              type="password"
              class="form-input"
              id="password"
              name="password"
              placeholder="Enter your password">
            <button type="button" class="password-toggle" onclick="togglePassword()">
              <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
          </div>
        </div>



        <button type="submit" class="btn-login">
          Sign In <i class="fas fa-arrow-right"></i>
        </button>
      </form>
      <a href="{{ route('student.forgot.password') }}" class="forgot-link ">Forgot Password?</a>


      <div class="login-footer">
        <a href="https://salesiancollege.ac.in/tnc" target="_blank">Terms and Conditions</a> • <a href="https://salesiancollege.ac.in/privacy-policy" target="_blank">Privacy Policy</a>
        <hr>
        <div style="margin-top:12px;font-size:13px;color:#888;">
          Developed by <a href="https://www.atnufas.com" target="_blank" style="color:#667eea;text-decoration:none;font-weight:600;">Atnufas Technologies Pvt Ltd</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Password Toggle
  function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('fa-eye');
      toggleIcon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('fa-eye-slash');
      toggleIcon.classList.add('fa-eye');
    }
  }

  // Form Submit Loading State
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn-login');
    btn.classList.add('loading');
    btn.innerHTML = '';
  });

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });
</script>

@include('includes.footer')
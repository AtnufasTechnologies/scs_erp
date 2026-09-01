<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salesian College Autonomous| Sonada and Siliguri </title>
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>
  <div class="container">
    @include('includes.alert')
    <!-- FORM SECTION -->
    <div class="row">


      <!-- SIGN IN -->
      <div class="col align-items-center flex-col ">
        <div class="form-wrapper align-items-center" id="authCardWrapper" data-open-forgot="{{ request()->query('panel') === 'forgot' || old('auth_panel') === 'forgot' ? '1' : '0' }}">
          <div class="flip-card" id="flipCard">
            <div class="flip-card-inner" id="flipCardInner">
              <div class="flip-face flip-front">
                <div class="form sign-in">
                  <form action="{{url('login')}}" method="post">
                    @csrf
                    <img src="{{asset('admin/images/logo.png')}}" alt="logo">
                    <h3>Salesian College Autonomous</h3>
                    <h5>Sonada and Siliguri</h5><br>
                    <h3>Management System</h3>

                    <div class="input-group">
                      <i class='fa fa-envelope'></i>
                      <input type="text" placeholder="Email" name="email" value="{{ old('email') }}">
                      @error('email')
                      <span class="text-danger">{{$message}}</span>
                      @enderror
                    </div>

                    <div class="input-group">
                      <i class='fa fa-lock-alt'></i>
                      <input type="password" placeholder="Password" name="password" id="password">
                      <i class='fa fa-eye toggle-password' id="togglePassword"></i>
                      @error('password')
                      <span class="text-danger">{{$message}}</span>
                      @enderror
                    </div>

                    <button type="submit">Sign in</button>
                    <p>
                      <b>
                        <a href="{{route('scms.forgot.password')}}" id="openForgotPassword">Forgot password?</a>
                      </b>
                    </p>
                  </form>
                </div>
              </div>

              <div class="flip-face flip-back">
                <div class="form forgot-form">
                  <form action="{{ url('forgot-password') }}" method="post">
                    @csrf
                    <input type="hidden" name="auth_panel" value="forgot">
                    <img src="{{asset('admin/images/logo.png')}}" alt="logo">
                    <h3>Reset Password</h3>
                    <h5>Enter your registered email</h5><br>

                    <div class="input-group">
                      <i class='fa fa-envelope'></i>
                      <input type="text" placeholder="Registered Email" name="email" value="{{ old('email') }}">
                    </div>

                    <button type="submit">Send Reset Link</button>
                    <p>
                      <b>
                        <a href="#" id="backToLogin">Back to Sign in</a>
                      </b>
                    </p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END SIGN IN -->
    </div>
  </div>
</body>

</html>

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    overflow-x: hidden;
    background: linear-gradient(135deg, #6f38dc 0%, #000 100%);
    min-height: 100vh;
  }

  .parallax-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background: linear-gradient(135deg, #6f38dc 0%, #000 100%);
    overflow: hidden;
  }

  .particle-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
  }

  .parallax-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.9;
    animation: float 20s infinite ease-in-out;
    z-index: 1;
    overflow: visible;
    box-shadow: 0 0 35px rgba(255, 255, 255, 0.2);
  }

  .parallax-shape::before,
  .parallax-shape::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
  }

  .shape-1 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle at 30% 28%, #d4f3ff 0%, #78b6ff 35%, #3e62db 68%, #222f75 100%);
    top: 10%;
    left: 10%;
    animation-delay: 0s;
  }

  .shape-1::before {
    width: 62%;
    height: 62%;
    top: 19%;
    left: 18%;
    background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.35), rgba(255, 255, 255, 0));
  }

  .shape-1::after {
    width: 140%;
    height: 28%;
    left: -20%;
    top: 38%;
    border-radius: 50%;
    border: 3px solid rgba(214, 234, 255, 0.72);
    transform: rotate(-16deg);
    box-shadow: 0 0 14px rgba(180, 220, 255, 0.45);
  }

  .shape-2 {
    width: 200px;
    height: 200px;
    background: radial-gradient(circle at 35% 32%, #ffd7b6 0%, #f08a5c 40%, #a9442a 78%, #5a2019 100%);
    top: 60%;
    right: 15%;
    animation-delay: 5s;
  }

  .shape-2::before {
    width: 22px;
    height: 22px;
    top: 34%;
    left: 48%;
    background: rgba(125, 40, 22, 0.35);
    box-shadow:
      -44px -12px 0 -2px rgba(107, 35, 23, 0.3),
      -22px 34px 0 0 rgba(128, 48, 26, 0.28),
      28px -24px 0 -3px rgba(110, 34, 18, 0.32),
      38px 22px 0 -1px rgba(115, 40, 23, 0.26);
  }

  .shape-2::after {
    inset: 10%;
    background: radial-gradient(circle at 28% 30%, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0));
  }

  .shape-3 {
    width: 150px;
    height: 150px;
    background: radial-gradient(circle at 30% 30%, #ccfff8 0%, #53d7ca 46%, #218b92 76%, #114b62 100%);
    bottom: 10%;
    left: 20%;
    animation-delay: 10s;
  }

  .shape-3::before {
    width: 74%;
    height: 74%;
    top: 13%;
    left: 13%;
    border: 1px solid rgba(255, 255, 255, 0.34);
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.16);
  }

  .shape-3::after {
    width: 170%;
    height: 170%;
    top: -35%;
    left: -35%;
    border-radius: 50%;
    border: 1px solid rgba(120, 255, 243, 0.35);
  }

  .rocket-orbit {
    position: absolute;
    top: 28%;
    left: -120px;
    width: 0;
    height: 0;
    z-index: 2;
    pointer-events: none;
    opacity: 0;
    transform: translate3d(0, 0, 0);
  }

  .rocket-orbit.is-flying {
    animation-duration: 8.5s;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
    animation-iteration-count: 1;
  }

  .rocket-orbit.from-left.is-flying {
    animation-name: rocket-flyby-right;
  }

  .rocket-orbit.from-right.is-flying {
    animation-name: rocket-flyby-left;
  }

  .rocket-icon {
    position: absolute;
    left: 0;
    top: 0;
    font-size: 24px;
    color: #ffe7aa;
    text-shadow:
      0 0 10px rgba(255, 205, 107, 0.85),
      0 0 22px rgba(255, 161, 56, 0.45);
    transform: rotate(22deg);
  }

  .rocket-orbit.from-right .rocket-icon {
    transform: rotate(202deg);
  }

  .rocket-trail {
    position: absolute;
    left: -48px;
    top: 8px;
    width: 42px;
    height: 8px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(255, 96, 41, 0.95) 0%, rgba(255, 194, 99, 0.65) 55%, rgba(255, 255, 255, 0) 100%);
    filter: blur(1px);
    transform: rotate(22deg);
    opacity: 0;
  }

  .rocket-orbit.from-right .rocket-trail {
    left: 16px;
    transform: rotate(202deg);
  }

  .rocket-orbit.is-flying .rocket-trail {
    animation: thruster-flicker 0.16s infinite alternate;
  }

  @keyframes rocket-flyby-right {
    0% {
      opacity: 0;
      transform: translate3d(-120px, 20px, 0);
    }

    8%,
    88% {
      opacity: 1;
    }

    100% {
      opacity: 0;
      transform: translate3d(calc(100vw + 120px), -16px, 0);
    }
  }

  @keyframes rocket-flyby-left {
    0% {
      opacity: 0;
      transform: translate3d(calc(100vw + 120px), -16px, 0);
    }

    8%,
    88% {
      opacity: 1;
    }

    100% {
      opacity: 0;
      transform: translate3d(-120px, 20px, 0);
    }
  }

  @keyframes thruster-flicker {
    from {
      opacity: 0.35;
      width: 36px;
    }

    to {
      opacity: 0.95;
      width: 46px;
    }
  }

  @keyframes float {

    0%,
    100% {
      transform: translateY(0) translateX(0) scale(1);
    }

    33% {
      transform: translateY(-30px) translateX(30px) scale(1.1);
    }

    66% {
      transform: translateY(30px) translateX(-30px) scale(0.9);
    }
  }

  .container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    position: relative;
  }

  .form-wrapper {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.7));
    backdrop-filter: blur(18px) saturate(140%);
    -webkit-backdrop-filter: blur(18px) saturate(140%);
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 20px;
    padding: 40px;
    box-shadow:
      0 24px 60px rgba(0, 0, 0, 0.4),
      inset 0 1px 0 rgba(255, 255, 255, 0.35);
    max-width: 450px;
    width: 100%;
    transform: translateY(0);
    transition: all 0.3s ease;
  }

  .flip-card {
    width: 100%;
    perspective: 1400px;
  }

  .flip-card-inner {
    position: relative;
    width: 100%;
    transition: transform 0.75s cubic-bezier(0.4, 0.2, 0.2, 1);
    transform-style: preserve-3d;
  }

  .flip-card-inner.is-flipped {
    transform: rotateY(180deg);
  }

  .flip-face {
    width: 100%;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
  }

  .flip-back {
    position: absolute;
    inset: 0;
    transform: rotateY(180deg);
  }

  .form-wrapper:hover {
    transform: translateY(-10px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
  }

  .form img {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    display: block;

  }



  .form h3,
  .form h5 {
    text-align: center;
    color: #eef6ff;
    margin: 10px 0;
    text-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
  }

  .form h3 {
    font-size: 24px;
    font-weight: 600;
  }

  .form h5 {
    font-size: 16px;
    color: #c7d6ff;
  }

  .input-group {
    position: relative;
    margin: 25px 0;
  }

  .input-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #dce8ff;
    font-size: 18px;
  }

  .input-group .toggle-password {
    left: auto;
    right: 15px;
    cursor: pointer;
  }

  .input-group input[type="password"] {
    padding-right: 50px;
  }

  .input-group input {
    width: 100%;
    padding: 15px 15px 15px 50px;
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.2);
    color: #f8fbff;
  }

  .input-group input::placeholder {
    color: rgba(240, 245, 255, 0.8);
  }

  .input-group input:focus {
    outline: none;
    border-color: rgba(255, 255, 255, 0.85);
    box-shadow: 0 0 0 4px rgba(167, 194, 255, 0.25);
  }

  .text-danger {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: block;
  }

  button[type="submit"] {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, rgba(130, 179, 255, 0.95) 0%, rgba(111, 56, 220, 0.95) 100%);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 10px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 10px;
  }

  button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
  }

  .form p {
    text-align: center;
    margin-top: 20px;
    color: #dbe6ff;
  }

  .form p b {
    color: #f2f6ff;
    cursor: pointer;
  }

  .form p a {
    color: #ffffff;
    text-decoration: none;
    text-shadow: 0 1px 8px rgba(0, 0, 0, 0.35);
  }

  .form p a:hover {
    text-decoration: underline;
  }

  @media (max-width: 768px) {
    .shape-1 {
      width: 180px;
      height: 180px;
      top: 8%;
      left: 4%;
    }

    .shape-2 {
      width: 130px;
      height: 130px;
      top: 66%;
      right: 4%;
    }

    .shape-3 {
      width: 95px;
      height: 95px;
      bottom: 8%;
      left: 12%;
    }
  }
</style>

<div class="parallax-bg">
  <canvas class="particle-canvas" id="particleCanvas"></canvas>
  <div class="rocket-orbit" id="rocketOrbit">
    <span class="rocket-trail" aria-hidden="true"></span>
    <i class="fa fa-rocket rocket-icon" aria-hidden="true"></i>
  </div>
  <div class="parallax-shape shape-1"></div>
  <div class="parallax-shape shape-2"></div>
  <div class="parallax-shape shape-3"></div>
</div>

<script>
  (function() {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const particles = [];
    const maxParticles = Math.min(90, Math.floor(window.innerWidth / 16));
    let mouseX = -9999;
    let mouseY = -9999;

    function resizeCanvas() {
      const dpr = window.devicePixelRatio || 1;
      const width = window.innerWidth;
      const height = window.innerHeight;
      canvas.width = Math.floor(width * dpr);
      canvas.height = Math.floor(height * dpr);
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function createParticle() {
      return {
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        vx: (Math.random() - 0.5) * 0.5,
        vy: (Math.random() - 0.5) * 0.5,
        r: Math.random() * 2 + 0.7,
        a: Math.random() * 0.35 + 0.12
      };
    }

    function initParticles() {
      particles.length = 0;
      for (let i = 0; i < maxParticles; i++) {
        particles.push(createParticle());
      }
    }

    function drawLink(p1, p2, dist) {
      const maxDist = 115;
      if (dist > maxDist) return;

      const alpha = (1 - dist / maxDist) * 0.16;
      ctx.strokeStyle = `rgba(255,255,255,${alpha})`;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(p1.x, p1.y);
      ctx.lineTo(p2.x, p2.y);
      ctx.stroke();
    }

    function animate() {
      ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);

      for (let i = 0; i < particles.length; i++) {
        const p = particles[i];

        const dxMouse = p.x - mouseX;
        const dyMouse = p.y - mouseY;
        const mouseDist = Math.sqrt(dxMouse * dxMouse + dyMouse * dyMouse);

        if (mouseDist < 120) {
          const force = (120 - mouseDist) / 120;
          p.vx += (dxMouse / (mouseDist || 1)) * force * 0.025;
          p.vy += (dyMouse / (mouseDist || 1)) * force * 0.025;
        }

        p.x += p.vx;
        p.y += p.vy;

        p.vx *= 0.994;
        p.vy *= 0.994;

        if (p.x < -15) p.x = window.innerWidth + 15;
        if (p.x > window.innerWidth + 15) p.x = -15;
        if (p.y < -15) p.y = window.innerHeight + 15;
        if (p.y > window.innerHeight + 15) p.y = -15;

        ctx.fillStyle = `rgba(255,255,255,${p.a})`;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fill();

        for (let j = i + 1; j < particles.length; j++) {
          const q = particles[j];
          const dx = p.x - q.x;
          const dy = p.y - q.y;
          drawLink(p, q, Math.sqrt(dx * dx + dy * dy));
        }
      }

      requestAnimationFrame(animate);
    }

    resizeCanvas();
    initParticles();
    animate();

    window.addEventListener('resize', () => {
      resizeCanvas();
      initParticles();
    });

    window.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
    });

    window.addEventListener('mouseleave', () => {
      mouseX = -9999;
      mouseY = -9999;
    });
  })();

  // Parallax effect on mouse move
  document.addEventListener('mousemove', (e) => {
    const shapes = document.querySelectorAll('.parallax-shape');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;

    shapes.forEach((shape, index) => {
      const speed = (index + 1) * 20;
      shape.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
    });
  });

  // Form wrapper parallax effect on scroll
  window.addEventListener('scroll', () => {
    const formWrapper = document.querySelector('.form-wrapper');
    const scrolled = window.pageYOffset;
    formWrapper.style.transform = `translateY(${scrolled * 0.5}px)`;
  });

  (function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        this.classList.toggle('fa-eye', !isPassword);
        this.classList.toggle('fa-eye-slash', isPassword);
      });
    }

    const openForgotPassword = document.getElementById('openForgotPassword');
    const backToLogin = document.getElementById('backToLogin');
    const flipCardInner = document.getElementById('flipCardInner');
    const authCardWrapper = document.getElementById('authCardWrapper');
    const shouldOpenForgot = !!(authCardWrapper && authCardWrapper.dataset.openForgot === '1');

    if (flipCardInner && shouldOpenForgot) {
      flipCardInner.classList.add('is-flipped');
    }

    if (openForgotPassword && flipCardInner) {
      openForgotPassword.addEventListener('click', function(e) {
        e.preventDefault();
        flipCardInner.classList.add('is-flipped');
      });
    }

    if (backToLogin && flipCardInner) {
      backToLogin.addEventListener('click', function(e) {
        e.preventDefault();
        flipCardInner.classList.remove('is-flipped');
      });
    }

    const rocket = document.getElementById('rocketOrbit');
    if (!rocket) return;

    const flightDurationMs = 8500;
    const launchIntervalMs = 15000;
    let flyFromLeft = true;

    const launchRocket = () => {
      const randomTop = 12 + Math.random() * 70;
      rocket.style.top = randomTop.toFixed(2) + '%';

      rocket.classList.remove('from-left', 'from-right');
      rocket.classList.add(flyFromLeft ? 'from-left' : 'from-right');
      flyFromLeft = !flyFromLeft;

      rocket.classList.remove('is-flying');
      void rocket.offsetWidth;
      rocket.classList.add('is-flying');
      window.setTimeout(() => {
        rocket.classList.remove('is-flying');
      }, flightDurationMs);
    };

    window.setTimeout(launchRocket, 1800);
    window.setInterval(launchRocket, launchIntervalMs);
  })();
</script>
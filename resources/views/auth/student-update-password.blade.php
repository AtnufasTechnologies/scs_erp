<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salesian College Autonomous - Update Password</title>
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

      <!-- UPDATE PASSWORD -->
      <div class="col align-items-center flex-col ">
        <div class="form-wrapper align-items-center">
          <div class="form sign-in">
            <form action="{{route('student.password.update')}}" method="post">
              @csrf
              <img src="{{asset('admin/images/logo.png')}}" alt="logo">
              <h3>Salesian College Autonomous</h3>
              <h5>Sonada and Siliguri</h5><br>

              <h3>Update Your Password</h3>

              <div class="input-group">
                <i class='fa fa-lock-alt'></i>
                <input type="password" placeholder="New Password" name="password" id="password">
                <i class='fa fa-eye toggle-password' style="right: 15px; left: auto; cursor: pointer;" data-target="password"></i>
                @error('password')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class='fa fa-lock-alt'></i>
                <input type="password" placeholder="Confirm Password" name="confirm_password" id="confirm_password">
                <i class='fa fa-eye toggle-password-confirm' style="right: 15px; left: auto; cursor: pointer;" data-target="confirm_password"></i>
                @error('confirm_password')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <input type="hidden" name="email" value="{{ $data->email }}">

              <style>
                .input-group {
                  position: relative;
                }

                .toggle-password,
                .toggle-password-confirm {
                  position: absolute;
                  right: 15px;
                  top: 50%;
                  transform: translateY(-50%);
                  color: #667eea;
                  font-size: 18px;
                  cursor: pointer;
                }
              </style>

              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  document.querySelector('.toggle-password').addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    if (passwordInput.type === 'password') {
                      passwordInput.type = 'text';
                      this.classList.remove('fa-eye');
                      this.classList.add('fa-eye-slash');
                    } else {
                      passwordInput.type = 'password';
                      this.classList.remove('fa-eye-slash');
                      this.classList.add('fa-eye');
                    }
                  });

                  document.querySelector('.toggle-password-confirm').addEventListener('click', function() {
                    const confirmPasswordInput = document.getElementById('confirm_password');
                    if (confirmPasswordInput.type === 'password') {
                      confirmPasswordInput.type = 'text';
                      this.classList.remove('fa-eye');
                      this.classList.add('fa-eye-slash');
                    } else {
                      confirmPasswordInput.type = 'password';
                      this.classList.remove('fa-eye-slash');
                      this.classList.add('fa-eye');
                    }
                  });
                });
              </script>

              <button type="submit">
                Update Password
              </button>

              <p>
                <b>
                  <a href="{{route('student.login')}}">Back to Sign In</a>
                </b>
              </p>

            </form>
          </div>
        </div>

      </div>
      <!-- END UPDATE PASSWORD -->
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
  }

  .parallax-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .parallax-shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    animation: float 20s infinite ease-in-out;
  }

  .shape-1 {
    width: 300px;
    height: 300px;
    background: white;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
  }

  .shape-2 {
    width: 200px;
    height: 200px;
    background: white;
    top: 60%;
    right: 15%;
    animation-delay: 5s;
  }

  .shape-3 {
    width: 150px;
    height: 150px;
    background: white;
    bottom: 10%;
    left: 20%;
    animation-delay: 10s;
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
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 450px;
    width: 100%;
    transform: translateY(0);
    transition: all 0.3s ease;
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
    color: #333;
    margin: 10px 0;
  }

  .form h3 {
    font-size: 24px;
    font-weight: 600;
  }

  .form h5 {
    font-size: 16px;
    color: #666;
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
    color: #667eea;
    font-size: 18px;
  }

  .input-group input {
    width: 100%;
    padding: 15px 15px 15px 50px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
  }

  .input-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
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
    color: #666;
  }

  .form p b {
    color: #667eea;
    cursor: pointer;
  }

  .form p a {
    color: #667eea;
    text-decoration: none;
  }

  .form p a:hover {
    text-decoration: underline;
  }
</style>

<div class="parallax-bg">
  <div class="parallax-shape shape-1"></div>
  <div class="parallax-shape shape-2"></div>
  <div class="parallax-shape shape-3"></div>
</div>

<script>
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
</script>
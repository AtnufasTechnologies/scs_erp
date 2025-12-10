<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{asset('admin/css/login.css')}}">
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <link href="{{asset('admin/css/bootstrap.min.css')}}" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Erp Login | Salesian College Autonomous</title>
</head>

<body>
  @include('includes.alert')
  <div class="container">
    <div class="screen">
      <div class="screen__content">

        <form class="login" action="{{url('login')}}" method="POST">
          @csrf

          <img src="{{asset('admin/images/scslogo.png')}}" alt="logo" class="logo">

          <div class="login__field">
            <i class="login__icon fas fa-envelope"></i>
            <input type="text" class="login__input" name="email" placeholder="Email" value="{{old('email')}}">
            @error('email')
            <span class="text-danger">{{$message}}</span>
            @enderror
          </div>
          <div class="login__field">
            <i class="login__icon fas fa-eye" id="eye"></i>

            <input type="password" class="login__input" placeholder="Password" id="pwd" name="password">
            @error('password')
            <span class="text-danger">{{$message}}</span>
            @enderror

          </div>
          <button class="button login__submit">
            <span class="button__text">Log In Now</span>
            <i class="button__icon fas fa-chevron-right"></i>
          </button>
        </form>
        <div class="social-login">
          <a href="{{url('forgot-password')}}">
            <h6 class="forgot_password">Forgot Password?</h6>
          </a>

        </div>
      </div>
      <div class="screen__background">
        <span class="screen__background__shape screen__background__shape4"></span>
        <span class="screen__background__shape screen__background__shape3"></span>
        <span class="screen__background__shape screen__background__shape2"></span>
        <span class="screen__background__shape screen__background__shape1"></span>
      </div>
    </div>
  </div>
  <script src="{{asset('admin/js/jquery.min.js')}}"></script>
  <script src="{{asset('admin/js/bootstrap.bundle.min.js')}}"></script>

</body>

<script>
  // Show/hide password onClick of button using Javascript only

  // https://stackoverflow.com/questions/31224651/show-hide-password-onclick-of-button-using-javascript-only

  function show() {
    var p = document.getElementById('pwd');
    p.setAttribute('type', 'text');
  }

  function hide() {
    var p = document.getElementById('pwd');
    p.setAttribute('type', 'password');
  }

  var pwShown = 0;

  document.getElementById("eye").addEventListener("click", function() {
    if (pwShown == 0) {
      pwShown = 1;
      show();
    } else {
      pwShown = 0;
      hide();
    }
  }, false);
</script>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="{{asset('admin/css/login.css')}}">
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <link href="{{asset('admin/css/bootstrap.min.css')}}" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <title>Forgot Passowrd | ERP Salesian College Autonomous</title>
</head>

<body>
  @include('includes.alert')
  <div class="container">
    <div class="screen">
      <div class="screen__content">

        <form class="login" action="{{url('forgot-password')}}" method="POST">
          @csrf
          <img src="{{asset('admin/images/scslogo.png')}}" alt="logo" class="logo">
          <div class="login__field">
            <i class="login__icon fas fa-envelope"></i>
            <input type="text" class="login__input" placeholder="Registered Email" name="email" value="{{old('email')}}">
            @error('email')
            <span class="text-danger">{{$message}}</span>
            @enderror
          </div>

          <button class="button login__submit">
            <span class="button__text">Reset</span>
            <i class="button__icon fas fa-chevron-right"></i>
          </button>
        </form>
        <div class="social-login">
          <a href="{{url('/')}}">
            <h6 class="forgot_password">Sign In</h6>
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


</html>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salesian College Autonomous| Sonada and Siliguri - Admission Portal</title>
  <link rel="stylesheet" href="{{asset('admin/css/admission.css')}}">
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
</head>

<body>
  <div id="container" class="container">
    <!-- FORM SECTION -->
    <div class="row">
      <!-- SIGN UP -->
      <div class="col align-items-center flex-col sign-up">
        <div class="form-wrapper align-items-center">

          <div class="form sign-up">
            <form action="{{route('admission.registration.submit')}}" method="post">
              @csrf
              <img src="{{asset('admin/images/logo.png')}}" alt="logo" width="80">
              <h3>Salesain College Autonomous</h3>
              <h5>Sonada and Siliguri</h5>
              <br>
              <h3>Admission Portal</h3>


              <div class="input-group">
                <i class="fa fa-home"></i>
                <select name="campus" class="form-control mb-3 " id="regcampusId">
                  <option value="">Select Campus *</option>
                  @foreach ($campuses as $campus)
                  <option value="{{$campus->id}}">{{$campus->name}}</option>
                  @endforeach
                </select>
                @error('campus')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group" id="dynamicProg">
                <i class="fa fa-bookmark"></i>
                <select class="form-control mb-3" id="mainPrograms" name="applicationType">

                </select>
                @error('applicationType')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class='fa fa-text'></i>
                <input type="text" placeholder="First Name *" name="firstname">
                @error('firstname')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>
              <div class="input-group">
                <i class='fa fa-text'></i>
                <input type="text" placeholder="Last Name *" name="lastname">
                @error('lastname')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class='fa fa-mobile-alt'></i>
                <input type="text" placeholder="Mobile Number *" name="mobile_no">
                @error('mobile_no')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <input type="password" placeholder="Password *" name="password" id="password">
                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer">
                  <i class="fa fa-eye toggle-password" data-target="password"></i>
                </span>
                @error('password')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="text" placeholder="Email *" name="mail_id">
                @error('mail_id')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fa fa-globe"></i>
                <select name="country" id="">
                  <option value="">Country *</option>
                  @foreach ($countries as $country)
                  <option value="{{$country->id}}">{{$country->name}} {{$country->phone_code}}</option>
                  @endforeach
                </select>
                @error('country')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <button type="submit">
                Sign up
              </button>
              <p>
                <span>
                  Already have an account?
                </span>
                <b onclick="toggle()" class="pointer">
                  Log in here
                </b>
              </p>
            </form>
          </div>

        </div>

      </div>
      <!-- END SIGN UP -->
      <!-- SIGN IN -->
      <div class="col align-items-center flex-col sign-in">
        <div class="form-wrapper align-items-center">
          <div class="form sign-in">
            <form action="{{route('applicant.login')}}" method="post">
              @csrf
              <img src="{{asset('admin/images/logo.png')}}" alt="logo">
              <h3>Salesain College Autonomous</h3>
              <h5>Sonada and Siliguri</h5><br>
              <h3>Applicant Login</h3>
              <div class="input-group">
                <i class='fa fa-mobile-alt'></i>
                <input type="text" placeholder="Registered Number" name="registered_no">
                @error('registered_no')
                <span class="text-danger">{{$message}}</span>
                @enderror

              </div>
              <div class="input-group">
                <i class='fa fa-lock-alt'></i>
                <input type="password" placeholder="Password" name="registered_password">
                @error('registered_password')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>
              <button type="submit">
                Sign in
              </button>
              <p>
                <b>
                  Forgot password?
                </b>
              </p>
              <p>
                <span>
                  Don't have an account?
                </span>
                <b onclick="toggle()" class="pointer">
                  Sign up here
                </b>
              </p>
            </form>
          </div>
        </div>
        <div class="form-wrapper">

        </div>
      </div>
      <!-- END SIGN IN -->
    </div>
    <!-- END FORM SECTION -->
    <!-- CONTENT SECTION -->
    <div class="row content-row">
      <!-- SIGN IN CONTENT -->
      <div class="col align-items-center flex-col">
        <div class="text sign-in">

          <h2>
            Admission Portal
          </h2>

        </div>
        <div class="img sign-in">

        </div>
      </div>
      <!-- END SIGN IN CONTENT -->
      <!-- SIGN UP CONTENT -->
      <div class="col align-items-center flex-col">
        <div class="img sign-up">

        </div>
        <div class="text sign-up">

          <h2>
            New Applicant <br> Registration
          </h2>


        </div>
      </div>
      <!-- END SIGN UP CONTENT -->
    </div>
    <!-- END CONTENT SECTION -->
  </div>
  <script src="{{asset('admin/js/jquery.min.js')}}"></script>
  <script>
    let container = document.getElementById('container')

    toggle = () => {
      container.classList.toggle('sign-in')
      container.classList.toggle('sign-up')
    }

    setTimeout(() => {
      container.classList.add('sign-up')
    }, 200)
  </script>
  <script src="{{asset('admin/js/admission.js')}}"></script>
</body>

</html>
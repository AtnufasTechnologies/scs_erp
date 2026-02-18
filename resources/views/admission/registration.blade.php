<?php

use App\Models\BatchMaster;

$batch = BatchMaster::where('admission_active_batch', 1)->value('batch_name');


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salesian College Autonomous| Sonada and Siliguri - Admission Portal</title>
  <link rel="stylesheet" href="{{asset('admin/css/admission.css')}}">
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>

  </style>
</head>

<body>
  <div class="parallax-container">

    <div class="main-container">
      <div class="auth-wrapper" id="authWrapper">

        <!-- Sign Up Form -->
        <div class="form-section" id="signUpSection">
          <div class="scroll-container">
            <div class="form-header">
              <img src="{{asset('admin/images/scslogo.png')}}" alt="logo">
              <h3>Admission Portal - {{$batch}}</h3>
            </div>

            <form action="{{route('admission.registration.submit')}}" method="post" id="registrationForm">
              @csrf
              <div class="input-group">
                <i class="fa fa-home"></i>
                <select name="campus" class="form-control" id="regcampusId">
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
                <select class="form-control" id="mainPrograms" name="applicationType">
                </select>
                @error('applicationType')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fas fa-user-circle"></i>
                <input type="text" placeholder="First Name *" name="firstname" value="{{old('firstname')}}">
                @error('firstname')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fas fa-user-circle"></i>
                <input type="text" placeholder="Last Name *" name="lastname" value="{{old('lastname')}}">
                @error('lastname')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fas fa-globe-asia"></i>
                <select name="country" id="dselect-example">
                  <option value="">Country *</option>
                  @foreach ($countries as $country)
                  <option value="{{$country->id}}">{{$country->name}} {{$country->phone_code}}</option>
                  @endforeach
                </select>
                @error('country')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class='fa fa-mobile-alt'></i>
                <input type="text" placeholder="Mobile Number *" name="mobile_no" value="{{old('mobile_no')}}">
                @error('mobile_no')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fa fa-eye toggle-password" data-target="password"></i>
                <input type="password" placeholder="Password (min 6 character) *" name="password" id="password">
                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer">

                </span>
                @error('password')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <div class="input-group">
                <i class="fa fa-envelope"></i>
                <input type="text" placeholder="Email *" name="mail_id" value="{{old('mail_id')}}">
                @error('mail_id')
                <span class="text-danger">{{$message}}</span>
                @enderror
              </div>

              <span class="captcha-image">{!! captcha_img('flat') !!}</span>
              <div class="input-group">
                <i class="fas fa-redo-alt" id="refresh-captcha" style="cursor:pointer"></i>
                <input id="captcha_input" type="text" name="captcha_input" placeholder="Enter Captcha">
                @error('captcha_input')
                <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>

              <button type="submit" id="submitBtn">Sign up</button>

              <p style="text-align: center; margin-top: 20px;">
                <span>Already have an account? </span>
                <a href="{{route('new.admission.login')}}"><b>Log in here</b></a>
              </p>
            </form>

            <script>
              document.getElementById('registrationForm').addEventListener('submit', function() {
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                document.getElementById('submitBtn').disabled = true;
              });
            </script>
          </div>
        </div>


        <!-- Content Section -->
        <div class="content-section" id="contentSection">
          <h2 id="contentTitle">Instructions</h2>
          <div style="max-height: 400px; overflow-y: auto; padding-right: 15px; scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.5) transparent;">
            <h3>UG</h3>
            <p><?php echo  $admissionSetting->instructions_ug ?></p>
            <br>
            <hr>
            <br>
            <h3>PG</h3>
            <p><?php echo  $admissionSetting->instructions_pg ?></p>
          </div>
        </div>

      </div>
    </div>
  </div>


  <div class="parallax-bg">
    <div class="parallax-shape shape-1"></div>
    <div class="parallax-shape shape-2"></div>
    <div class="parallax-shape shape-3"></div>
  </div>

  <script src="{{asset('admin/js/jquery.min.js')}}"></script>
  <script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>
  <script src="{{asset('admin/js/admission.js')}}"></script>
  <script>
    function toggle() {
      const signUpSection = document.getElementById('signUpSection');
      const signInSection = document.getElementById('signInSection');
      const contentTitle = document.getElementById('contentTitle');

      signUpSection.classList.toggle('hidden');
      signInSection.classList.toggle('hidden');

      if (signUpSection.classList.contains('hidden')) {
        contentTitle.innerHTML = 'Admission Portal';
      } else {
        contentTitle.innerHTML = 'New Applicant<br>Registration';
      }
    }

    function togglePassword() {
      const passwordField = document.getElementById('password');
      const icon = document.querySelector('.toggle-password');

      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>

</html>
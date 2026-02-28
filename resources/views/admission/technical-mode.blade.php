<?php

use App\Models\BatchMaster;

$batch = BatchMaster::where('admission_active_batch', 1)->value('batch_name');


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salesian College Autonomous| Sonada and Siliguri </title>
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
      <header class="profile-header">
        <div class="header-content">
          <div class="profile-img-container">
            <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
          </div>
          <div class="profile-info">

            <h1 class="text-capitalize">Salesian College Autonomous</h1>
            <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>

          </div>
          <br>

          <div class=" card shadow p-4">
            <div class="card-body text-center">
              <h2 class="mb-4" style="color:aliceblue">System is Under Technical Maintenance</h2><br>
              <p class="mb-4 " style="color:aliceblue">Admission System Has been put on Hold for Maintenance Reason. You can login or Register once we Resume</p>
              <h1 class="mb-4" style="color:aliceblue">We will Be Back Shortly</h1>
              <br>
              <p>For any inquiries, please contact us at:
                <a href="mailto:admissions@salesiancollege.net" class="contact-link" style="color:aliceblue;text-decoration:underline;">
                  <i class="fas fa-envelope"></i>
                  admissionenquiry@salesiancollege.net
                </a>
              </p>
              <br>

              <p>You can also reach us by phone at:
                <a href="tel:+919933402478" class="contact-link" style="color:yellow;text-decoration:underline;">
                  <i class="fas fa-phone"></i>
                  +91 99334 02478
                </a>
              </p>
            </div>
          </div>
        </div>


        <div class="parallax-bg">
          <div class="parallax-shape shape-1"></div>
          <div class="parallax-shape shape-2"></div>
          <div class="parallax-shape shape-3"></div>
        </div>

    </div>

    <script src="{{asset('admin/js/jquery.min.js')}}"></script>
    <script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>
    <script src="{{asset('admin/js/admission.js')}}"></script>

</body>

</html>
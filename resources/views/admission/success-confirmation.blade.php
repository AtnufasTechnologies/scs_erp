@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Application Submitted Successfully</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:admissions@salesiancollege.net" aria-label="Email">
          <i class="fas fa-envelope"></i> admissions@salesiancollege.net
        </a>
        <a href="tel:+919933402478" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622
        </a>
      </div>
    </div>
  </div>
</header>

<div class="container">
  <div class="col-lg-12">
    <div class="card shadow p-5 application-card radius-30">
      <div class="text-center">
        <div class="mb-4">
          <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
        </div>

        <h2 class="text-success mb-3">Application Submitted Successfully!</h2>

        <p class="lead mb-4">
          Thank you for submitting your application for UG admission.
        </p>

        <div class="alert alert-info" role="alert">
          <h5 class="alert-heading">Application Reference Number</h5>
          <h3 class="mb-0"><strong>{{$application_id ?? 'N/A'}}</strong></h3>
          <small>Please save this reference number for future correspondence.</small>
        </div>

        <div class="card mt-4 mb-4">
          <div class="card-body text-start">
            <h5 class="card-title mb-3">Application Details</h5>
            <div class="row">
              <div class="col-md-6">
                <p><strong>Name:</strong> {{$data->first_name ?? ''}}</p>
                <p><strong>Email:</strong> {{$data->mail_id ?? ''}}</p>
                <p><strong>Mobile:</strong> {{$data->mobile_no ?? ''}}</p>
              </div>
              <div class="col-md-6">
                <p><strong>Program:</strong> {{$data->programInfo->name ?? ''}}</p>
                <p><strong>Campus:</strong> {{$data->programInfo->campus->name ?? ''}}</p>
                <p><strong>Batch:</strong> {{$data->batch_name ?? ''}}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="alert alert-warning" role="alert">
          <h6><i class="fas fa-info-circle"></i> Next Steps</h6>
          <ul class="text-start mb-0">
            <li>You will receive a confirmation email shortly.</li>
            <li>The admission committee will review your application.</li>
            <li>Further instructions will be sent to your registered email.</li>
            <li>Please check your email regularly for updates.</li>
          </ul>
        </div>

        <div class="mt-4">

          <a href="{{route('admission.download-pdf', ['id' => $application_id])}}" class="btn btn-success radius-20 me-2">
            <i class="fas fa-file-pdf"></i> Download PDF
          </a>
          <a href="{{route('admission.apply.logout')}}" class="btn btn-secondary radius-20">
            <i class="fas fa-sign-out-alt"></i> Logout
        </div>

        <hr class="my-4">

        <p class="text-muted">
          <small>
            For any queries, please contact the admission office at
            <a href="mailto:admissions@salesiancollege.net">admissions@salesiancollege.net</a>
          </small>
        </p>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
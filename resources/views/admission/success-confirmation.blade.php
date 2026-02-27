@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img" style="background-color: white;">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Application Submitted Successfully</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:admissions@salesiancollege.net" aria-label="Email">
          <i class="fas fa-envelope"></i> admissionenquiry@salesiancollege.net
        </a>
        <a href="tel:+919933402478" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622 (Siliguri Campus)
        </a>
      </div>
      <div class="contact-links">
        <a href="mailto:salesiancollegesonada@gmail.com" aria-label="">
          <i class="fas fa-envelope"></i> salesiancollegesonada@gmail.com (Sonada)
        </a>
        <a href="tel:+917602032968" target="_blank">
          <i class="fas fa-phone"></i> 76020 32968 / 99336 40168 (Sonada)
        </a>
      </div>
    </div>
  </div>
</header>

<div class="container mt-5 mb-5">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <p class="display-4">Hi, <span class="text-capitalize">{{ $data->registrationmaster->first_name }} {{ $data->registrationmaster->last_name }}</span></p>
          <p><i class="fa fa-envelope"></i> {{ $data->registrationmaster->mail_id }} | <i class="fa fa-phone"></i> {{ $data->registrationmaster->mobile_no }}</p>

          <p class="mb-4">Welcome! Your application has been received successfully. We will contact you shortly with details about your interview.</p>
          <p class="text-capitalize"> <b>{{$data->academicDeptMaster->title}}</b> <br>{{$data->stdCourseMaster->name}}</p>
          <h4>Your Application Number is</h4>
          <div class="d-inline-block mt-3 mb-4 p-3 bg-success text-white rounded" style="font-size: 24px; font-weight: 700; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            # {{ $data->application_code }}
          </div>
          <p>You Download the application form and invoice.</p>

          <a href="{{route('download.admission.application-form',$data->application_code)}}" class="btn btn-primary">View Application Form</a>
          <a href="{{route('download.admission.payment-invoice',$data->application_code)}}" class="btn btn-primary">View Invoice</a>
          <a href="{{route('admission.apply.logout')}}" class="btn btn-secondary">Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="container mb-5">
  <div class="row ">
    @if ($data->phaseoneinfo != null)
    <div class="col-md-6">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <h3 class="mb-4">Interview Details</h3>
          <p>Your interview is scheduled on <strong>{{$data->phaseoneinfo->interview_datetime}}</strong> at
            <strong>Salesian College {{ $data->registrationmaster->campusmaster->name }}</strong>.
          </p>
          <p>Please be prepared and bring all necessary documents.</p>
          <a href="" class="btn btn-primary">Instructions for Interview</a><br>
        </div>
      </div>
    </div>
    @endif
    @if($data->phasetwoinfo != null)
    <div class="col-md-6">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <h3 class="mb-4">Admission Details</h3>
          <p><strong class="text-success">Congratulations! </strong> you have been <strong class="text-success">selected</strong> for admission.</p>
          <p>Visit College Office within 5 Days to Make Payment and Reserve Your Slot.
            <strong>Salesian College {{ $data->registrationmaster->campusmaster->name }}</strong>.
          </p>
          <a href="" class="btn btn-primary">Instructions for Admission</a><br>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>





@include('includes.footer')
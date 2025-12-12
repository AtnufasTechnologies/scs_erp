@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">STUDENT PROFILE</span></h3>

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="https://media.gettyimages.com/id/1487465664/photo/portrait-employee-and-asian-woman-with-happiness-selfie-and-confident-entrepreneur-with.jpg?s=2048x2048&w=gi&k=20&c=RIO9RHVRQBf98Gg15xgB7qzGYbRqH3M5gRYA6eIq_uE=" alt="ProfilePicture" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">{{$data->roll_no}} | {{$data->campusmaster != null ? $data->campusmaster->name : ''}}</span></h6>
      <h1 class="text-capitalize">{{$data->first_name}} {{$data->last_name}}</h1>
      <h2 class="text-capitalize">{{$data->deptmaster != null ? $data->deptmaster->name : ''}}</h2>
      <div class="contact-links">
        <a href="mailto:{{$data->mail_id}}" aria-label="{{$data->mail_id}}">
          <i class="fas fa-envelope"></i> {{$data->mail_id}}
        </a>
        <a href="tel:{{$data->mobile_no}}" target="_blank">
          <i class="fas fa-phone"></i> {{$data->mobile_no}}
        </a>


      </div>
    </div>
  </div>
</header>

<div class="main-wrapper container">

  <section class="about-me card">
    <h3>👋 About Me</h3>
    <p><i>Wish me on</i> <b>{{date('d M Y',strtotime($data->dob))}} </b></p>
    <p>Gender <strong>{{$data->gender == '1' ? 'Male' :'Female'}}</strong> </p>
    <p><strong class="text-capitalize">{{$data->religionmaster != null ? $data->religionmaster->name : ''}}</strong> by Faith </p>
    <p>I'm a {{$data->current_year}}year {{$data->programgroup->programInfo->name}} student from the department of {{$data->deptmaster->name}}.
    </p>
    <label for=""><strong>Address</strong></label>
    <p class="text-capitalize">{{$data->address}}</p>

  </section>

  <section class="skills-section card">
    <h3>🛠️ Associations</h3>
    <div class="skills-grid">
      <span class="skill-tag">TimeTable</span>
      <span class="skill-tag">Attendance</span>
      <span class="skill-tag">Library</span>
      <span class="skill-tag">Examination</span>
      <span class="skill-tag">Activities</span>
      <span class="skill-tag">Internship</span>
    </div>
  </section>

  <section class="projects-section">
    <h3>✨ Fee Payments</h3>
    <div class="projects-grid">

      @if (count($data->feepayment))
      @foreach ($data->feepayment as $pay)
      <div class="project-card card">

        <span class="float-right">{{date('d-m-Y',strtotime($pay->transaction_date))}}</span>

        <h4>#{{$pay->invoice_id}}
          <span class="badge rounded-pill bg-success">{{$pay->gateway_type_id != 3 ? $pay->gatewaytype->title : 'Cash'}}</span>
        </h4>

        <p>{{$pay->feepaymentinfo->quarter_title }}</p>
        <h5><i class="fa fa-rupee-sign"></i> {{$pay->amount}}</h5>
      </div>
      @endforeach

      @else
      <p class="mx-5">
        <strong class="text-danger">No Records</strong>
        <span>Found on Fee Payment</span>
      </p>
      @endif

    </div>
  </section>
</div>


@include('includes.footer')
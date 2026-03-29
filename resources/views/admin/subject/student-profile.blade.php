@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  <div class="container-fluid">
    <h3><span class="text-uppercase">STUDENT PROFILE</span></h3>

    <header class="profile-header">
      <div class="header-content">
        <div class="profile-img-container">
          @if ($data->gender == 1)
          <img src="{{asset('admin/images/male.png')}}" alt="ProfilePicture" class="profile-img">
          @else
          <img src="{{asset('admin/images/female.png')}}" alt="ProfilePicture" class="profile-img">
          @endif

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


    </div>
  </div>
</div>


@include('includes.footer')
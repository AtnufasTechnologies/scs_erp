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
        <a>
          {{$data->dob}} | {{$data->gender == '1' ? 'Male' :'Female'}}
        </a>
        <a>
          Religion - <span class="text-capitalize">{{$data->religionmaster != null ? $data->religionmaster->name : ''}}</span>
      </div>
    </div>
  </div>
</header>

<div class="main-wrapper container">

  <section class="about-me card">
    <h3>👋 About Me</h3>
    <p>I'm a third-year Computer Science student passionate about **front-end development** and **data visualization**. I thrive in collaborative environments and am always looking to learn new technologies. Currently focusing on mastering React and exploring cloud computing.</p>
  </section>

  <section class="skills-section card">
    <h3>🛠️ Technical Skills</h3>
    <div class="skills-grid">
      <span class="skill-tag">HTML5 & CSS3</span>
      <span class="skill-tag">JavaScript (ES6+)</span>
      <span class="skill-tag">React & Vue.js</span>
      <span class="skill-tag">Python & Pandas</span>
      <span class="skill-tag">Node.js</span>
      <span class="skill-tag">SQL & MongoDB</span>
      <span class="skill-tag">Git/GitHub</span>
      <span class="skill-tag">UI/UX Design</span>
    </div>
  </section>

  <section class="projects-section">
    <h3>✨ Key Projects</h3>
    <div class="projects-grid">
      <div class="project-card card">
        <h4>E-Commerce Dashboard</h4>
        <p>Built a responsive dashboard using **React** and **D3.js** for real-time sales analytics. (View on GitHub)</p>
      </div>
      <div class="project-card card">
        <h4>University Capstone Portal</h4>
        <p>Designed and developed a **full-stack** application using Node.js and MongoDB to manage student projects.</p>
      </div>
      <div class="project-card card">
        <h4>AI Chatbot Interface</h4>
        <p>Developed an intuitive front-end for a machine learning powered chatbot using Vue.js and WebSockets.</p>
      </div>
    </div>
  </section>
</div>


@include('includes.footer')
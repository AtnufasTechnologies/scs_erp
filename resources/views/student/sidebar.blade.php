<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      SCMS
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation STUDENT -->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('student.console.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.my-profile') }}">
        <div class="parent-icon"><i class="fas fa-user-circle"></i></div>
        <div class="menu-title">My Profile</div>
      </a>
    </li>

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="menu-title">Academics</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-timetable" onclick="switchTab('timetable')">
            <i class="fas fa-calendar-week"></i> Timetable
          </a>
        </li>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-courses" onclick="switchTab('courses')">
            <i class="fas fa-book-open"></i> My Courses
          </a>
        </li>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-attendance" onclick="switchTab('attendance')">
            <i class="fas fa-user-check"></i> Attendance
          </a>
        </li>
        <li>
          <a href="{{ route('student.offerings.index') }}">
            <i class="fas fa-ticket-alt"></i> Course Registration
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="fas fa-file-alt"></i></div>
        <div class="menu-title">Examinations</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-exams" onclick="switchTab('exams')">
            <i class="fas fa-file-signature"></i> Exam Registration
          </a>
        </li>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-exams" onclick="switchTab('exams')">
            <i class="fas fa-poll"></i> Results
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-fees" onclick="switchTab('fees')">
        <div class="parent-icon"><i class="fas fa-wallet"></i></div>
        <div class="menu-title">Fee Payments</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.feedback.list') }}">
        <div class="parent-icon"><i class="fas fa-star"></i></div>
        <div class="menu-title">
          Subject Feedback
          @if(isset($pendingFeedbackCount) && $pendingFeedbackCount > 0)
          <span class="badge bg-danger ms-1">{{ $pendingFeedbackCount }}</span>
          @endif
        </div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-mentorship" onclick="switchTab('mentorship')">
        <div class="parent-icon"><i class="fas fa-hands-helping"></i></div>
        <div class="menu-title">Mentorship</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-activities" onclick="switchTab('activities')">
        <div class="parent-icon"><i class="fas fa-calendar-star"></i></div>
        <div class="menu-title">Activities & Events</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
  </ul>
  <!--end navigation-->
</aside>
<!--end sidebar-->

<script>
  // Tab switching function for sidebar navigation
  function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.std-tab-content').forEach(tab => {
      tab.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.std-tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show selected tab
    const targetTab = document.getElementById('tab-' + tabName);
    if (targetTab) {
      targetTab.classList.add('active');

      // Add active class to corresponding button if on dashboard page
      document.querySelectorAll('.std-tab-btn').forEach(btn => {
        if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabName)) {
          btn.classList.add('active');
        }
      });
    }
  }
</script>

<!-- start page content wrapper-->
<div class="page-content-wrapper">
  <!-- start page content-->
  <div class="page-content">

    @if ($errors->any())

    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    @endif
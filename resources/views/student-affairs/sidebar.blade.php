<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      <span class="logo-text">Dean Affairs Panel</span>
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('dean.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.student-council.index') }}">
        <div class="parent-icon"><i class="fas fa-users"></i></div>
        <div class="menu-title">Student Council</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.clubs.index') }}">
        <div class="parent-icon"><i class="fas fa-layer-group"></i></div>
        <div class="menu-title">Clubs, Cells</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.events.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="menu-title">Event Monitoring</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.mentoring.index') }}">
        <div class="parent-icon"><i class="fas fa-hand-holding-heart"></i></div>
        <div class="menu-title">Mentoring Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.attendance.monitoring') }}">
        <div class="parent-icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="menu-title">Attendance Monitoring</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.attendance.regularization') }}">
        <div class="parent-icon"><i class="fas fa-user-check"></i></div>
        <div class="menu-title">Regularization</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.discipline.index') }}">
        <div class="parent-icon"><i class="fas fa-gavel"></i></div>
        <div class="menu-title">Discipline</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.counselling.index') }}">
        <div class="parent-icon"><i class="fas fa-comments"></i></div>
        <div class="menu-title">Counselling</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.concern-categories.index') }}">
        <div class="parent-icon"><i class="fas fa-tags"></i></div>
        <div class="menu-title">Concern Categories</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.student360.index') }}">
        <div class="parent-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="menu-title">Student 360</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.reports.index') }}">
        <div class="parent-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="menu-title">Reports</div>
      </a>
    </li>

    <li>
      <a href="{{ route('faculty.workdiary') }}">
        <div class="parent-icon"><i class="fas fa-briefcase"></i></div>
        <div class="menu-title">Work Diary</div>
      </a>
    </li>

    <li>
      <a href="{{ url('logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
  </ul>
</aside>
<!--end sidebar -->

<!--start top header-->
<header class="top-header">
  <nav class="navbar navbar-expand gap-3">
    <div class="mobile-menu-button">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>

    <div class="top-navbar-right ms-auto">
      <ul class="navbar-nav align-items-center"></ul>
    </div>
  </nav>
</header>
<!--end top header-->

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
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">Receptionist Panel</div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('receptionist.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    <li>
      <a href="{{ route('receptionist.faculty.index') }}">
        <div class="parent-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="menu-title">Faculty and Timetable</div>
      </a>
    </li>
    <li>
      <a href="{{ route('receptionist.appointments.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="menu-title">Principal Appointments</div>
      </a>
    </li>
    <li>
      <a href="{{ route('receptionist.work-diary.index') }}">
        <div class="parent-icon"><i class="fas fa-book"></i></div>
        <div class="menu-title">Work Diary</div>
      </a>
    </li>
    <li>
      <a href="{{ route('scms.logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
  </ul>
</aside>

<header class="top-header">
  <nav class="navbar navbar-expand gap-3">
    <div class="mobile-menu-button">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </nav>
</header>


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
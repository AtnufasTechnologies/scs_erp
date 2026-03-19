<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">

    <div class="logo-text">
      SCMS
    </div>
    <div class=" toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation ADMIN -->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{route('faculty.dashboard')}}">
        <div class="parent-icon">
          <i class="fas fa-home"></i>
        </div>
        <div class="menu-title">Menu </div>
      </a>
    </li>

    <li>
      <a href="{{route('faculty.timetable')}}">
        <div class="parent-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="menu-title">Timetable </div>
      </a>
    </li>

    <li>
      <a href="{{route('faculty.subjects')}}">
        <div class="parent-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="menu-title">Subjects </div>
      </a>
    </li>

    <li>
      <a href="{{route('faculty.workdiary')}}">
        <div class="parent-icon">
          <i class="fas fa-briefcase"></i>
        </div>
        <div class="menu-title">Work Diary </div>
      </a>
    </li>

    <li>
      <a href="{{route('faculty.subjects')}}">
        <div class="parent-icon">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="menu-title">Attendance </div>
      </a>
    </li>


    <!-- logout -->
    <li>
      <a href="{{url('logout')}}">
        <div class="parent-icon">
          <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="menu-title">Logout </div>
      </a>
    </li>
    <!--end navigation-->
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

      <ul class="navbar-nav align-items-center">
        <!-- <li class="nav-item mobile-search-button">
              <a class="nav-link" href="javascript:;">
                <div class="">
                  <ion-icon name="search-sharp"></ion-icon>
                </div>
              </a>
            </li> -->
        <li class="nav-item">
          <a class="nav-link dark-mode-icon" href="javascript:;">
            <div class="mode-icon">
              <ion-icon name="moon-sharp"></ion-icon>
            </div>
          </a>
        </li>



      </ul>

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
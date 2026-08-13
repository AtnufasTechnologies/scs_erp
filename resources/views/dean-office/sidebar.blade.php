<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      <span class="logo-text">Dean Office Panel</span>
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('dean.office.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dean Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('dean.office.department.activities') }}">
        <div class="parent-icon"><i class="fas fa-building"></i></div>
        <div class="menu-title">Department Activities</div>
      </a>
    </li>
    <!-- 
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="menu-title">Event Tab</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('dean.office.events.overview') }}">
            <i class="bx bx-radio-circle"></i>Event Overview
          </a>
        </li>
        <li>
          <a href="{{ route('dean.office.events.calendar') }}">
            <i class="bx bx-radio-circle"></i>Event Calendar
          </a>
        </li>
        <li>
          <a href="{{ route('dean.office.events.features') }}">
            <i class="bx bx-radio-circle"></i>Event Features Board
          </a>
        </li>
      </ul>
    </li> -->

    <li>
      <a href="{{ url('logout') }}">
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

    <div class="top-navbar-right ms-auto">
      <ul class="navbar-nav align-items-center"></ul>
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
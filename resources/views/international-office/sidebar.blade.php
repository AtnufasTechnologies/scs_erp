<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">International Office</div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('international-office.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>


    <li>
      <a href="{{ route('activity.type.master') }}">
        <div class="parent-icon"><i class="fas fa-list"></i></div>
        <div class="menu-title">Activity Type Master</div>
      </a>
    </li>
    <li>
      <a href="{{ route('international-office.events.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="menu-title">Event Entry</div>
      </a>
    </li>
    <li>
      <a href="{{ route('international-office.institutions.index') }}">
        <div class="parent-icon"><i class="fas fa-university"></i></div>
        <div class="menu-title">Institution MoU</div>
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

    <div class="top-navbar-right ms-auto">
      <ul class="navbar-nav align-items-center"></ul>
    </div>
  </nav>
</header>

<div class="page-content-wrapper">
  <div class="page-content">
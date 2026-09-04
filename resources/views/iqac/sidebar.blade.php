<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">IQAC Panel</div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('iqac.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    <li>
      <a href="{{ route('iqac.international-office.reports') }}">
        <div class="parent-icon"><i class="fas fa-globe"></i></div>
        <div class="menu-title">International Office</div>
      </a>
    </li>
    <li>
      <a href="{{ route('iqac.departmental-activities.index') }}">
        <div class="parent-icon"><i class="fas fa-building"></i></div>
        <div class="menu-title">Departmental Activities</div>
      </a>
    </li>
    <li>
      <a href="{{ route('iqac.event-controller-reports.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="menu-title">Event Controller Reports</div>
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

<div class="page-content-wrapper">
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
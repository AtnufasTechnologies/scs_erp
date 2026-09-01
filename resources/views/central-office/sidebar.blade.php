<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">Central Office</div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('central-office.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    <li>
      <a href="{{ route('central-office.students.index') }}">
        <div class="parent-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="menu-title">Student List</div>
      </a>
    </li>
    <li>
      <a href="{{ route('central-office.employees.index') }}">
        <div class="parent-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="menu-title">Employee List</div>
      </a>
    </li>
    <li>
      <a href="{{ route('central-office.admissions.batch-wise') }}">
        <div class="parent-icon"><i class="fas fa-layer-group"></i></div>
        <div class="menu-title">Admission Batch Data</div>
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
      <ul class="navbar-nav align-items-center">
        <!-- <li class="nav-item">
          <a class="nav-link" href="{{ route('scms.logout') }}">
            <div class="mode-icon text-light">
              <i class="fas fa-sign-out-alt text-light"></i> Logout
            </div>
          </a>
        </li> -->
      </ul>
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
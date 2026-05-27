<!--start sidebar-->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      SCMS
    </div>
    <div class=" toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <!--navigation-->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('hr.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li class="menu-label">Faculty Management</li>
    <li>
      <a href="{{ route('hr.faculty.index') }}">
        <div class="parent-icon"><i class="fas fa-users"></i></div>
        <div class="menu-title">Faculty List</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.faculty.create') }}">
        <div class="parent-icon"><i class="fas fa-user-plus"></i></div>
        <div class="menu-title">Add Faculty</div>
      </a>
    </li>

    <li class="menu-label">Leave Management</li>
    <li>
      <a href="{{ route('hr.leave.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="menu-title">Leave Applications</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.leave.statistics') }}">
        <div class="parent-icon"><i class="fas fa-chart-line"></i></div>
        <div class="menu-title">Leave Statistics</div>
      </a>
    </li>

    <li class="menu-label">FDP Programs</li>
    <li>
      <a href="{{ route('hr.fdp.index') }}">
        <div class="parent-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="menu-title">All FDP Programs</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.fdp.create') }}">
        <div class="parent-icon"><i class="fas fa-plus-circle"></i></div>
        <div class="menu-title">Create FDP</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.fdp.faculty-tracker') }}">
        <div class="parent-icon"><i class="fas fa-route"></i></div>
        <div class="menu-title">FDP Tracker</div>
      </a>
    </li>

    <li class="menu-label">Recruitment</li>
    <li>
      <a href="{{ route('hr.vacancy.index') }}">
        <div class="parent-icon"><i class="fas fa-briefcase"></i></div>
        <div class="menu-title">Vacancies</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.vacancy.create') }}">
        <div class="parent-icon"><i class="fas fa-plus-square"></i></div>
        <div class="menu-title">Post Vacancy</div>
      </a>
    </li>
    <li>
      <a href="{{ route('vacancies.public.index') }}" target="_blank">
        <div class="parent-icon"><i class="fas fa-globe"></i></div>
        <div class="menu-title">Public Careers Page</div>
      </a>
    </li>

    <li class="menu-label">Payroll & Salary</li>
    <li>
      <a href="{{ route('hr.pay-matrix.index') }}">
        <div class="parent-icon"><i class="fas fa-th"></i></div>
        <div class="menu-title">Pay Matrix</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.pay-matrix.create') }}">
        <div class="parent-icon"><i class="fas fa-plus-square"></i></div>
        <div class="menu-title">Create Pay Matrix</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.payroll.index') }}">
        <div class="parent-icon"><i class="fas fa-money-check-alt"></i></div>
        <div class="menu-title">Payroll</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.payroll.generate') }}">
        <div class="parent-icon"><i class="fas fa-wallet"></i></div>
        <div class="menu-title">Generate Payroll</div>
      </a>
    </li>
    <li>
      <a href="{{ route('hr.payroll.statistics') }}">
        <div class="parent-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="menu-title">Payroll Statistics</div>
      </a>
    </li>

    <li class="menu-label">System</li>
    <li>
      <a href="{{ route('scms.logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
  </ul>
  <!--end navigation-->

</aside>
<!--end sidebar-->

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
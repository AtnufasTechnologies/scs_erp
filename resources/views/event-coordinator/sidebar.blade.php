<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">Event Coordinator</div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation-->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('event-coordinator.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('event-coordinator.events.index') }}">
        <div class="parent-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="menu-title">All Events</div>
      </a>
    </li>

    <li>
      <a href="{{ route('event-coordinator.events.create') }}">
        <div class="parent-icon"><i class="fas fa-plus-circle"></i></div>
        <div class="menu-title">Create Event</div>
      </a>
    </li>
    <hr class="text-light">
    <li>
      <a href="{{ route('scms.logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>


  </ul>
</aside>
<!--end sidebar -->

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
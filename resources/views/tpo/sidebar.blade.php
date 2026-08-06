<?php

use Illuminate\Support\Facades\Auth;
?>

<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text" style="font-size: 13px;">
      TPO Panel
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('tpo.training-placement.dashboard') }}">
        <div class="parent-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.index') }}">
        <div class="parent-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="menu-title">Training</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.placement.index') }}">
        <div class="parent-icon">
          <i class="fas fa-briefcase"></i>
        </div>
        <div class="menu-title">Placement Openings / Applications</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.analytics') }}">
        <div class="parent-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="menu-title">Analytics</div>
      </a>
    </li>

    <li>
      <a href="{{ url('logout') }}">
        <div class="parent-icon">
          <i class="fas fa-sign-out-alt"></i>
        </div>
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
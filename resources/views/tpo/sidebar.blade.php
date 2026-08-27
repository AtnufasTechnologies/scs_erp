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
    <li class="menu-label">Overview</li>
    <li>
      <a href="{{ route('tpo.training-placement.dashboard') }}" class="{{ request()->routeIs('tpo.training-placement.dashboard') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li class="menu-label">Programs</li>
    <li>
      <a href="{{ route('tpo.training-placement.index') }}" class="{{ request()->routeIs('tpo.training-placement.index') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="menu-title">Training</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.job-description.index') }}" class="{{ request()->routeIs('tpo.training-placement.placement.*') || request()->routeIs('tpo.training-placement.job-description.*') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-briefcase"></i>
        </div>
        <div class="menu-title">Job Description</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.events.index') }}" class="{{ request()->routeIs('tpo.training-placement.events.*') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="menu-title">External Facilitator</div>
      </a>
    </li>

    <li class="menu-label">Insights</li>
    <li>
      <a href="{{ route('tpo.training-placement.analytics') }}" class="{{ request()->routeIs('tpo.training-placement.analytics') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="menu-title">Traning Analytics</div>
      </a>
    </li>


    <li>
      <a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="{{ request()->routeIs('tpo.training-placement.student-opt-in-forms.*') || request()->routeIs('tpo.training-placement.opted-students.*') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="menu-title">Student Opt-Ins</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.job-applications.index') }}" class="{{ request()->routeIs('tpo.training-placement.job-applications.*') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="menu-title">Job Applications</div>
      </a>
    </li>


    <li>
      <a href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-bullseye-arrow"></i>
        </div>
        <div class="menu-title">Placement</div>
      </a>
    </li>

    <li class="menu-label">Company Relations</li>
    <li>
      <a href="{{ route('tpo.training-placement.companies.index') }}" class="{{ request()->routeIs('tpo.training-placement.companies.*') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-building"></i>
        </div>
        <div class="menu-title">Connected Companies</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.mailbox.compose.page') }}" class="{{ request()->routeIs('tpo.training-placement.mailbox.compose.page') ? 'active' : '' }}">
        <div class="parent-icon"><i class='fa fa-edit'></i></div>
        <div class="menu-title">Compose </div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="{{ request()->routeIs('tpo.training-placement.mailbox.index') || request()->routeIs('tpo.training-placement.mailbox.show') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <div class="menu-title"> Inbox</div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.mailbox.sent') }}" class="{{ request()->routeIs('tpo.training-placement.mailbox.sent') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-paper-plane"></i>
        </div>
        <div class="menu-title">Sent </div>
      </a>
    </li>

    <li>
      <a href="{{ route('tpo.training-placement.mailbox.trash') }}" class="{{ request()->routeIs('tpo.training-placement.mailbox.trash') ? 'active' : '' }}">
        <div class="parent-icon">
          <i class="fas fa-trash"></i>
        </div>
        <div class="menu-title">Archive</div>
      </a>
    </li>

    <li class="menu-label">System</li>
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
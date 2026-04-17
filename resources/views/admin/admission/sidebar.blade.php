<?php

use App\Http\Controllers\StaticController;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;

$userId   = Auth::user()->id;
$roleType = UserHasRole::where('user_id', $userId)->value('role_name');
$isAdmissionOfficer = $roleType === 'admission-office';
$isAdmissionIncharge = $roleType === 'admission-incharge';
$isSuperAdmin = $roleType === 'super-admin';
$canManageAll = $isAdmissionIncharge || $isSuperAdmin;
?>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text" style="font-size: 12px;">
      {{ Auth::user()->name }}
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">

    {{-- Dashboard --}}
    <li>
      <a href="{{ route('admission.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    {{-- Admission Portal (external) --}}
    <li>
      <a href="https://erpsalesiancollege.sdbinc.org/erp/new-admission/registration" target="_blank">
        <div class="parent-icon"><i class="fas fa-external-link-alt"></i></div>
        <div class="menu-title">Admission Portal</div>
      </a>
    </li>

    {{-- UG Admissions --}}
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon"><i class="fas fa-certificate"></i></div>
        <div class="menu-title">UG Admissions</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admission.registration', ['type' => 'UG']) }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">New Registrations</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admission.ug.applications') }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">Applications</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admission.ug.phase1') }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">Selection Phase 1</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admission.ug.phase2') }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">Selection Phase 2</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- PG Admissions --}}
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon"><i class="fas fa-badge"></i></div>
        <div class="menu-title">PG Admissions</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admission.registration', ['type' => 'PG']) }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">New Registrations</div>
          </a>
        </li>
        <li>
          <a href="{{ route('admission.pg.applications') }}">
            <div class="parent-icon"><i class="fas fa-arrow-alt-circle-right"></i></div>
            <div class="menu-title">Applications</div>
          </a>
        </li>
      </ul>
    </li>

    {{-- Admission Settings (incharge / super-admin only) --}}
    @if($canManageAll)
    <li>
      <a href="{{ route('admission.settings') }}">
        <div class="parent-icon"><i class="fas fa-cog"></i></div>
        <div class="menu-title">Admission Settings</div>
      </a>
    </li>
    @endif

  </ul>
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
        <li class="nav-item mobile-search-button">
          <a class="nav-link" href="javascript:;">
            <div class="">
              <ion-icon name="search-sharp"></ion-icon>
            </div>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link dark-mode-icon" href="javascript:;">
            <div class="mode-icon">
              <ion-icon name="moon-sharp"></ion-icon>
            </div>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{route('scms.logout')}}">
            <div class="mode-icon text-light">
              <i class="fas fa-sign-out-alt text-light"></i> Logout
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
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
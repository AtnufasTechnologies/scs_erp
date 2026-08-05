<?php

use App\Http\Controllers\StaticController;
use App\Models\FacultyLeaveApplication;
use App\Models\UserCampusSetting;
use App\Models\DepartmentMaster;
use App\Models\Faculty;

// Get pending leave count forwarded to principal
$pendingLeaveQuery = FacultyLeaveApplication::where('forwarded_to', 'Principal')
  ->where('dept_action', 'forwarded')
  ->where('status', 'pending');

// Check if user is vice-principal and filter by campus
$userRole = auth()->user()->userroletype->role_name ?? null;
if ($userRole === 'vice-principal') {
  $vpCampusId = UserCampusSetting::where('user_id', auth()->id())->value('campus_id');
  if ($vpCampusId) {
    $deptIds = DepartmentMaster::where('campus_id', $vpCampusId)->pluck('id');
    $facultyIds = Faculty::whereIn('DEPARTMENT', $deptIds)->pluck('id');
    $pendingLeaveQuery->whereIn('faculty_id', $facultyIds);
  }
}

$pendingLeaveCount = $pendingLeaveQuery->count();
?>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      <span class="logo-text">{{ StaticController::isPrincipal() ? 'Principal Panel' : 'Vice-Principal Panel' }}</span>
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation-->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('principal.dashboard') }}">
        <div class="parent-icon">
          <i class="fas fa-home"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.students.index') }}">
        <div class="parent-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="menu-title">Students</div>
      </a>
    </li>

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="menu-title">Faculty</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('principal.faculty.index') }}">
            <i class="bx bx-radio-circle"></i>All Faculty
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a href="{{ route('principal.courses.index') }}">
        <div class="parent-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <div class="menu-title">Courses & CSO</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.syllabus.index') }}">
        <div class="parent-icon">
          <i class="fas fa-scroll"></i>
        </div>
        <div class="menu-title">Subject Syllabus</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.classes.index') }}">
        <div class="parent-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="menu-title">Classes (Hour-wise)</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.work-diary.overview') }}">
        <div class="parent-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="menu-title">Work Diary</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.leaves.index') }}">
        <div class="parent-icon">
          <i class="fas fa-calendar-check"></i>
          @if($pendingLeaveCount > 0)
          <span class="badge bg-danger rounded-pill" style="position: absolute; top: -5px; right: -5px; font-size: 10px;">{{ $pendingLeaveCount }}</span>
          @endif
        </div>
        <div class="menu-title">Leave Management</div>
      </a>
    </li>

    <li>
      <a href="{{ route('principal.admissions.index') }}">
        <div class="parent-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="menu-title">Admissions</div>
      </a>
    </li>

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-binoculars"></i>
        </div>
        <div class="menu-title">Student Affairs Monitor</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('principal.monitoring.mentoring') }}">
            <i class="bx bx-radio-circle"></i>Mentoring Dashboard
          </a>
        </li>
        <li>
          <a href="{{ route('principal.monitoring.student360') }}">
            <i class="bx bx-radio-circle"></i>Student 360
          </a>
        </li>
        <li>
          <a href="{{ route('principal.monitoring.clubs') }}">
            <i class="bx bx-radio-circle"></i>Clubs and Cells
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="menu-title">Fees</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('principal.fees.index') }}">
            <i class="bx bx-radio-circle"></i>Student Fees
          </a>
        </li>
        <li>
          <a href="{{ route('principal.fees.defaulters') }}">
            <i class="bx bx-radio-circle"></i>Fee Defaulters
          </a>
        </li>
      </ul>
    </li>

    @if(StaticController::isPrincipal())
    <li>
      <a href="{{ route('principal.vp.index') }}">
        <div class="parent-icon">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="menu-title">VP Management</div>
      </a>
    </li>
    @endif

    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-sitemap"></i>
        </div>
        <div class="menu-title">Programs &amp; Curriculum</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('principal.dashboard') }}#programs-curriculum">
            <i class="bx bx-radio-circle"></i>Program &amp; Combo Status
          </a>
        </li>
        <li>
          <a href="{{ route('principal.dashboard') }}#subjects-overview">
            <i class="bx bx-radio-circle"></i>Subjects &amp; Specializations
          </a>
        </li>
      </ul>
    </li>

    <li>
      <a href="{{ route('principal.api-scores.reports') }}">
        <div class="parent-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <div class="menu-title">API Score Reports</div>
      </a>
    </li>

    <!-- logout -->
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
<!--end sidebar -->

<!--start top header-->
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
<!--end top header-->

<!-- start page content wrapper-->
<div class="page-content-wrapper">
  <!-- start page content-->
  <div class="page-content">
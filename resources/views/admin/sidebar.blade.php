<?php

use App\Http\Controllers\StaticController;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;

$userId = Auth::user()->id;
$roleType = UserHasRole::where('user_id', $userId)->value('role_name');
?>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">

    <div class="logo-text">
      SCMS
    </div>
    <div class=" toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation ADMIN -->
  <ul class="metismenu" id="menu">

    <li>
      <a href="{{url('erp/admin/dashboard')}}">
        <div class="parent-icon">
          <i class="fal fa-chart-pie"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <!-- Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <ion-icon name="grid-outline"></ion-icon>
        </div>
        <div class="menu-title">Master</div>
      </a>
      <ul>

        <li>
          <a href="{{url('erp/admin/master/batch')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Batches</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/blood-group')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Blood Group</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/campus')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Campuses </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/deanery')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Deneary</div>
          </a>
        </li>

        <li>
          <a href="{{route('stream.master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title"> Main Stream Master </div>
          </a>
        </li>
        <li>
          <a href="{{url('erp/admin/master/programs')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Campus Stream Combination </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/hour')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Hours</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/lecturehalls')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Lecture Halls</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/rooms')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Rooms</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/religion')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Religion</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/semester')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Semester</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/paper-type')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Paper Type</div>
          </a>
        </li>
        <li>
          <a href="{{route('itcell.student-program-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Affiliation Settings</div>
          </a>
        </li>


      </ul>
    </li>

    <!--Academic Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-books"></i>
        </div>
        <div class="menu-title">Academics </div>
      </a>
      <ul>
        <li>
          <a href="{{url('erp/admin/master/shift-master')}}">
            <div class="parent-icon">
              <i class="far fa-layer-plus"></i>
            </div>
            <div class="menu-title">Shifts</div>
          </a>
        </li>
        <li>
          <a href="{{url('erp/admin/master/cognitive-lvl')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">RBT Levels</div>
          </a>
        </li>
        <li>
          <a href="{{url('erp/admin/master/subjects')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Departments </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/subject-type')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Type </div>
          </a>
        </li>

        <li>
          <a href="{{route('admin.course-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Master </div>
          </a>
        </li>


        <li>
          <a href="{{route('admin.student-program-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title"> Program Master </div>
          </a>
        </li>

        <li>
          <a href="{{route('itcell.admission.combination-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Combinations</div>
          </a>
        </li>

        <li>
          <a href="{{route('itcell.semester.engine')}}">
            <div class="parent-icon">
              <i class="fal fa-drafting-compass"></i>
            </div>
            <div class="menu-title">Semester Engine</div>
          </a>
        </li>


      </ul>
    </li>


    <li>
      <a href="{{route('itcell.admission.applications')}}">
        <div class="parent-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="menu-title">Admission Applications</div>
      </a>
    </li>


    <!--Faculty Master -->
    @if (StaticController::subMenuRights('student-master-sonada') || StaticController::subMenuRights('student-master-siliguri') )
    <li>
      <a href="{{url('erp/admin/faculty-master')}}">
        <div class="parent-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="menu-title">Faculty Master</div>
      </a>
    </li>
    @endif


    @if (StaticController::subMenuRights('student-master-sonada') || StaticController::subMenuRights('student-master-siliguri') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fal fa-users-cog"></i>
        </div>
        <div class="menu-title"> Student Master</div>
      </a>
      <ul>
        <!--Student Master -->
        @if (StaticController::subMenuRights('student-master-sonada') )
        <li>
          <a href="{{url('erp/admin/std-master-sonada')}}">
            <div class="parent-icon">
              <i class="fas fa-user-graduate"></i>
            </div>
            <div class="menu-title"> Sonada</div>
          </a>
        </li>

        @endif

        @if (StaticController::subMenuRights('student-master-siliguri') )
        <li>
          <a href="{{url('erp/admin/std-master-siliguri')}}">
            <div class="parent-icon">
              <i class="fad fa-user-graduate"></i>
            </div>
            <div class="menu-title">Siliguri</div>
          </a>
        </li>

        @endif
      </ul>
    </li>
    @endif

    <li>
      <a href="{{route('bulk.student.course.enrollment')}}">
        <div class="parent-icon">
          <i class="far fa-ellipsis-h-alt"></i>
        </div>
        <div class="menu-title">Bulk Course Enrollment </div>
      </a>
    </li>

    <li>
      <a href="{{route('itcell.pathway.mapper')}}">
        <div class="parent-icon">
          <i class="fas fa-project-diagram"></i>
        </div>
        <div class="menu-title">Student Pathway Mapper</div>
      </a>
    </li>

    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-bring-forward"></i>
        </div>
        <div class="menu-title"> Promotion Logs</div>
      </a>
      <ul>
        <li>
          <a href="{{url('erp/admin/annual-promotion-logs/1')}}">
            <div class="parent-icon">
              <i class="fas fa-forward"></i>
            </div>
            <div class="menu-title">Sonada Promotion Logs</div>
          </a>
        </li>
        <li>
          <a href="{{url('erp/admin/annual-promotion-logs/2')}}">
            <div class="parent-icon">
              <i class="fas fa-forward"></i>
            </div>
            <div class="menu-title">Siliguri Promotion Logs</div>
          </a>
        </li>
      </ul>
    </li>



    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-shield"></i>
        </div>
        <div class="menu-title">Authorization </div>
      </a>
      <ul>

        <li>
          <a href="{{route('admin.user.management')}}">
            <div class="parent-icon">
              <i class="far fa-users-cog"></i>
            </div>
            <div class="menu-title">All User Manager </div>
          </a>
        </li>


        <li>
          <a href="{{route('dept.erp.access-list')}}">
            <div class="parent-icon">
              <i class="far fa-users-class"></i>
            </div>
            <div class="menu-title">Department Access </div>
          </a>
        </li>


        <li>
          <a href="{{route('admin.role-master')}}">
            <div class="parent-icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <div class="menu-title">Role Master </div>
          </a>
        </li>

        <li>
          <a href="{{route('admin.menu-access-types')}}">
            <div class="parent-icon">
              <i class="far fa-ellipsis-h-alt"></i>
            </div>
            <div class="menu-title">Menu Manager </div>
          </a>
        </li>



      </ul>

    <li>
      <a href="{{route('admin.sms.templates')}}">
        <div class="parent-icon">
          <i class="fas fa-sms"></i>
        </div>
        <div class="menu-title">SMS Templates </div>
      </a>
    </li>

    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-wave-sine"></i>
        </div>
        <div class="menu-title">System Logs </div>
      </a>
      <ul>
        <li>
          <a href="{{route('admin.activity-logs.dashboard')}}">
            <div class="parent-icon">
              <i class="fas fa-chart-bar"></i>
            </div>
            <div class="menu-title">Activity Dashboard </div>
          </a>
        </li>
        <li>
          <a href="{{route('admin.user.activityapplication-single-logs')}}">
            <div class="parent-icon">
              <i class="fas fa-history"></i>
            </div>
            <div class="menu-title">Activity Logs </div>
          </a>
        </li>
      </ul>
    </li>


    <!-- logout -->
    <li>
      <a href="{{url('logout')}}">
        <div class="parent-icon">
          <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="menu-title">Logout </div>
      </a>
    </li>
    <!--end navigation-->
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
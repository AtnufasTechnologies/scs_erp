<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">

    <div class="logo-text">
      ERP
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
          <a href="{{url('erp/admin/master/cognitive-lvl')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Cognitive Level</div>
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
          <a href="{{url('erp/admin/master/departments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">All Departments</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/academic-dept')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Academic Departments</div>
          </a>
        </li>


        <li>
          <a href="{{url('erp/admin/master/programs')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Programs </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/program-group')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Group </div>
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
          <a href="{{url('erp/admin/master/subjects')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Subjects </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/master/subject-type')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Subject Type </div>
          </a>
        </li>




      </ul>
    </li>

    <li>
      <a href="{{url('erp/admin/faculty-master')}}">
        <div class="parent-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="menu-title">Faculty Master</div>
      </a>
    </li>
    <!--Std Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="menu-title">Student Master</div>
      </a>
      <ul>
        <li>
          <a href="{{url('erp/admin/std-master-sonada')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Sonada</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/std-master-siliguri')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Siliguri</div>
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
          <a href="{{url('erp/admin/academics/program-objectives')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Objectives</div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Questionnaire</div>
          </a>
        </li>

      </ul>
    </li>

    <!--Accounts Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fa fa-calculator"></i>
        </div>
        <div class="menu-title">Accounts Office</div>
      </a>
      <ul>
        <li>
          <a href="{{url('erp/admin/accounts/bankinfo')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Bank Accounts</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/accounts/fee-heads')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Heads </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/accounts/fee-course-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Course Master </div>
          </a>
        </li>


        <li>
          <a href="{{url('erp/admin/accounts/fee-structure')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Structure</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/accounts/std-fee-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Payments</div>
          </a>
        </li>




      </ul>
    </li>


    <!--HR Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="far fa-users-class"></i>
        </div>
        <div class="menu-title">HR Management</div>
      </a>
      <ul>
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">APRs</div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Applications</div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Grievances</div>
          </a>
        </li>



      </ul>
    </li>

    <!--Examination Master -->
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="far fa-analytics"></i>
        </div>
        <div class="menu-title">Examination </div>
      </a>
      <ul>
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Registrations</div>
          </a>
        </li>
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Paper Settup</div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Seatings </div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Evaluation Duty</div>
          </a>
        </li>

        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Promotions </div>
          </a>
        </li>



      </ul>
    </li>

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
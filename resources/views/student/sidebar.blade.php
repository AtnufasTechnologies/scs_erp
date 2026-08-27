<?php

use Illuminate\Support\Facades\Auth;
use App\Models\StudentMaster;
use App\Models\UserHasRole;

$authUser = Auth::user();
$stdId = $authUser?->student_id;
$student = $stdId ? StudentMaster::find($stdId) : null;
$rolename = $authUser ? UserHasRole::where('user_id', $authUser->id)->value('role_name') : null;
$studentGender = (int) ($student?->GENDER ?? $student?->gender ?? 0);
?>

<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      SCMS
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation STUDENT -->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{ route('student.console.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.profile') }}" class="{{ request()->routeIs('student.console.profile') ? 'active' : '' }}">
        <div class="parent-icon"><i class="fas fa-user-circle"></i></div>
        <div class="menu-title">My Doc Vault</div>
      </a>
    </li>


    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-timetable" onclick="switchTab('timetable')">
        <div class="parent-icon">
          <i class="fas fa-calendar-week"></i>
        </div>
        <div class="menu-title">Timetable</div>
      </a>
    </li>
    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-courses" onclick="switchTab('courses')">
        <div class="parent-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <div class="menu-title">My Courses</div>
      </a>
    </li>
    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-attendance" onclick="switchTab('attendance')">
        <div class="parent-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="menu-title">Attendance</div>
      </a>
    </li>
    {{-- Examination --}}
    <!-- <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="fas fa-file-alt"></i></div>
        <div class="menu-title">Examinations</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-exams" onclick="switchTab('exams')">
            <i class="fas fa-file-signature"></i> Exam Registration
          </a>
        </li>
        <li>
          <a href="{{ route('student.console.dashboard') }}#tab-exams" onclick="switchTab('exams')">
            <i class="fas fa-poll"></i> Results
          </a>
        </li>
      </ul>
    </li> -->
    {{-- Fee Payments --}}
    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-fees" onclick="switchTab('fees')">
        <div class="parent-icon"><i class="fas fa-wallet"></i></div>
        <div class="menu-title">Fee Payments</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.training') }}" class="{{ request()->routeIs('student.console.training') ? 'active' : '' }}">
        <div class="parent-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="menu-title">Training</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.console.placement') }}" class="{{ request()->routeIs('student.console.placement') || request()->routeIs('student.console.training-placement') ? 'active' : '' }}">
        <div class="parent-icon"><i class="fas fa-briefcase"></i></div>
        <div class="menu-title">Placement</div>
      </a>
    </li>

    {{-- Syllabus and Feedbacks--}}
    <!-- <li>
      <a href="{{ route('student.feedback.list') }}">
        <div class="parent-icon"><i class="fas fa-star"></i></div>
        <div class="menu-title">
          Subject Feedback
          @if(isset($pendingFeedbackCount) && $pendingFeedbackCount > 0)
          <span class="badge bg-danger ms-1">{{ $pendingFeedbackCount }}</span>
          @endif
        </div>
      </a>
    </li> -->

    {{-- Mentorship --}}
    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-mentorship" onclick="switchTab('mentorship')">
        <div class="parent-icon"><i class="fas fa-hands-helping"></i></div>
        <div class="menu-title">Mentorship</div>
      </a>
    </li>


    {{-- Activities & Events --}}
    <li>
      <a href="{{ route('student.console.dashboard') }}#tab-activities" onclick="switchTab('activities')">
        <div class="parent-icon"><i class="fas fa-calendar-star"></i></div>
        <div class="menu-title">Activities & Events</div>
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
      <ion-icon name="menu-sharp" role="img" class="md hydrated" aria-label="menu sharp"></ion-icon>
    </div>
    <form class="searchbar">
      <div class="position-absolute top-50 translate-middle-y search-icon ms-3">
        <ion-icon name="search-sharp" role="img" class="md hydrated" aria-label="search sharp"></ion-icon>
      </div>
      <input class="form-control" type="text" placeholder="Search for anything">
      <div class="position-absolute top-50 translate-middle-y search-close-icon">
        <ion-icon name="close-sharp" role="img" class="md hydrated" aria-label="close sharp"></ion-icon>
      </div>
    </form>
    <div class="top-navbar-right ms-auto">

      <ul class="navbar-nav align-items-center">
        <li class="nav-item mobile-search-button">
          <a class="nav-link" href="javascript:;">
            <div class="">
              <ion-icon name="search-sharp" role="img" class="md hydrated" aria-label="search sharp"></ion-icon>
            </div>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link dark-mode-icon" href="javascript:;">
            <div class="mode-icon">
              <ion-icon name="moon-sharp" role="img" class="md hydrated" aria-label="moon sharp"></ion-icon>
            </div>
          </a>
        </li>

        <li class="nav-item dropdown dropdown-large">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
            <div class="position-relative">
              <span class="notify-badge">8</span>
              <ion-icon name="notifications-sharp" role="img" class="md hydrated" aria-label="notifications sharp"></ion-icon>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end">
            <a href="javascript:;">
              <div class="msg-header">
                <p class="msg-header-title">Notifications</p>
                <p class="msg-header-clear ms-auto">Marks all as read</p>
              </div>
            </a>
            <div class="header-notifications-list ps">
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-primary">
                    <ion-icon name="cart-outline" role="img" class="md hydrated" aria-label="cart outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">New Orders <span class="msg-time float-end">2 min
                        ago</span></h6>
                    <p class="msg-info">You have recived new orders</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-danger">
                    <ion-icon name="person-outline" role="img" class="md hydrated" aria-label="person outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">New Customers<span class="msg-time float-end">14 Sec
                        ago</span></h6>
                    <p class="msg-info">5 new user registered</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-success">
                    <ion-icon name="document-outline" role="img" class="md hydrated" aria-label="document outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">24 PDF File<span class="msg-time float-end">19 min
                        ago</span></h6>
                    <p class="msg-info">The pdf files generated</p>
                  </div>
                </div>
              </a>

              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-info">
                    <ion-icon name="checkmark-done-outline" role="img" class="md hydrated" aria-label="checkmark done outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">New Product Approved <span class="msg-time float-end">2 hrs ago</span></h6>
                    <p class="msg-info">Your new product has approved</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-warning">
                    <ion-icon name="send-outline" role="img" class="md hydrated" aria-label="send outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">Time Response <span class="msg-time float-end">28 min
                        ago</span></h6>
                    <p class="msg-info">5.1 min avarage time response</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-danger">
                    <ion-icon name="chatbox-ellipses-outline" role="img" class="md hydrated" aria-label="chatbox ellipses outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">New Comments <span class="msg-time float-end">4 hrs
                        ago</span></h6>
                    <p class="msg-info">New customer comments recived</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-primary">
                    <ion-icon name="albums-outline" role="img" class="md hydrated" aria-label="albums outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">New 24 authors<span class="msg-time float-end">1 day
                        ago</span></h6>
                    <p class="msg-info">24 new authors joined last week</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-success">
                    <ion-icon name="shield-outline" role="img" class="md hydrated" aria-label="shield outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">Your item is shipped <span class="msg-time float-end">5 hrs
                        ago</span></h6>
                    <p class="msg-info">Successfully shipped your item</p>
                  </div>
                </div>
              </a>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="notify text-warning">
                    <ion-icon name="cafe-outline" role="img" class="md hydrated" aria-label="cafe outline"></ion-icon>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="msg-name">Defense Alerts <span class="msg-time float-end">2 weeks
                        ago</span></h6>
                    <p class="msg-info">45% less alerts last 4 weeks</p>
                  </div>
                </div>
              </a>
              <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
              </div>
              <div class="ps__rail-y" style="top: 0px; right: 0px;">
                <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
              </div>
            </div>
            <a href="javascript:;">
              <div class="text-center msg-footer">View All Notifications</div>
            </a>
          </div>
        </li>
        <li class="nav-item dropdown dropdown-user-setting">
          <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
            <div class="user-setting">
              @if($studentGender === 1)
              <img src="{{ asset('admin/images/male.png')}}" class="user-img" alt="">
              @else
              <img src="{{ asset('admin/images/female.png')}}" class="user-img" alt="">
              @endif
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex flex-row align-items-center gap-2">
                  @if($studentGender === 1)
                  <img src="{{ asset('admin/images/male.png')}}" alt="" class="rounded-circle" width="54" height="54">
                  @else
                  <img src="{{ asset('admin/images/female.png')}}" alt="" class="rounded-circle" width="54" height="54">
                  @endif
                  <div class="">
                    <h6 class="mb-0 dropdown-user-name">{{ $student?->first_name ?? 'Student' }}</h6>
                    <small class="mb-0 dropdown-user-designation text-secondary">{{ $rolename ?? 'User' }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <!-- <li>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="">
                    <ion-icon name="person-outline" role="img" class="md hydrated" aria-label="person outline"></ion-icon>
                  </div>
                  <div class="ms-3"><span>Profile</span></div>
                </div>
              </a>
            </li>
            <li> 
            <a class="dropdown-item" href="javascript:;">
              <div class="d-flex align-items-center">
                <div class="">
                  <ion-icon name="settings-outline" role="img" class="md hydrated" aria-label="settings outline"></ion-icon>
                </div>
                <div class="ms-3"><span>Setting</span></div>
              </div>
            </a>
        </li>-->
            <li>
              <a class="dropdown-item" href="{{route('student.console.dashboard')}}">
                <div class="d-flex align-items-center">
                  <div class="">
                    <ion-icon name="speedometer-outline" role="img" class="md hydrated" aria-label="speedometer outline"></ion-icon>
                  </div>
                  <div class="ms-3"><span>Dashboard</span></div>
                </div>
              </a>
            </li>
            <!-- <li>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="">
                    <ion-icon name="wallet-outline" role="img" class="md hydrated" aria-label="wallet outline"></ion-icon>
                  </div>
                  <div class="ms-3"><span>Earnings</span></div>
                </div>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:;">
                <div class="d-flex align-items-center">
                  <div class="">
                    <ion-icon name="cloud-download-outline" role="img" class="md hydrated" aria-label="cloud download outline"></ion-icon>
                  </div>
                  <div class="ms-3"><span>Downloads</span></div>
                </div>
              </a>
            </li> -->
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item" href="{{route('student.logout')}}">
                <div class="d-flex align-items-center">
                  <div class="">
                    <ion-icon name="log-out-outline" role="img" class="md hydrated" aria-label="log out outline"></ion-icon>
                  </div>
                  <div class="ms-3"><span>Logout</span></div>
                </div>
              </a>
            </li>
          </ul>
        </li>

      </ul>

    </div>
  </nav>
</header>
<!--end top header-->

<script>
  // Tab switching function for sidebar navigation
  function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.std-tab-content').forEach(tab => {
      tab.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.std-tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show selected tab
    const targetTab = document.getElementById('tab-' + tabName);
    if (targetTab) {
      targetTab.classList.add('active');

      // Add active class to corresponding button if on dashboard page
      document.querySelectorAll('.std-tab-btn').forEach(btn => {
        if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabName)) {
          btn.classList.add('active');
        }
      });
    }
  }
</script>

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
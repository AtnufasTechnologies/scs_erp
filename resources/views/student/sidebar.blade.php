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
      <a href="{{ route('student.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-home"></i></div>
        <div class="menu-title">Home</div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.feedback.list') }}">
        <div class="parent-icon"><i class="fas fa-star"></i></div>
        <div class="menu-title">
          Subject Feedback
          @if(isset($pendingFeedbackCount) && $pendingFeedbackCount > 0)
          <span class="badge bg-danger ms-1">{{ $pendingFeedbackCount }}</span>
          @endif
        </div>
      </a>
    </li>

    <li>
      <a href="{{ route('student.offerings.index') }}">
        <div class="parent-icon"><i class="fas fa-ticket-alt"></i></div>
        <div class="menu-title">Course Registration</div>
      </a>
    </li>

    <li>
      <a href="{{ url('logout') }}">
        <div class="parent-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
  </ul>
  <!--end navigation-->
</aside>
<!--end sidebar-->
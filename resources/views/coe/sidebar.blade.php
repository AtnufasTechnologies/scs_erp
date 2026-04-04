@php
use App\Http\Controllers\StaticController;
@endphp
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text">
      {{ StaticController::isCoe() ? 'COE Panel' : 'D.COE Panel' }}
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation COE -->
  <ul class="metismenu" id="menu">
    <li>
      <a href="{{route('coe.dashboard')}}">
        <div class="parent-icon">
          <i class="fas fa-home"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>

    @if(StaticController::coeMenuAccess('exam-management'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="menu-title">Exam Management</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.exams.index') }}">
            <i class="bx bx-radio-circle"></i>All Exams
          </a>
        </li>
        <li>
          <a href="{{ route('coe.exams.create') }}">
            <i class="bx bx-radio-circle"></i>Create New Exam
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('exam-registrations'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div class="menu-title">Exam Registrations</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.exam-registrations.index') }}">
            <i class="bx bx-radio-circle"></i>All Registrations
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-registrations.create') }}">
            <i class="bx bx-radio-circle"></i>New Registration
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('seating-allocation'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-chair"></i>
        </div>
        <div class="menu-title">Seating Allocation</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.seating-allocation.index') }}">
            <i class="bx bx-radio-circle"></i>All Allocations
          </a>
        </li>
        <li>
          <a href="{{ route('admin.seating-allocation.create') }}">
            <i class="bx bx-radio-circle"></i>Manual Allocation
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('dummy-numbers'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-sort-numeric-down"></i>
        </div>
        <div class="menu-title">Dummy Numbers</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.dummy-numbers.index') }}">
            <i class="bx bx-radio-circle"></i>All Dummy Numbers
          </a>
        </li>
        <li>
          <a href="{{ route('coe.dummy-numbers.create') }}">
            <i class="bx bx-radio-circle"></i>Assign Manually
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('admit-cards'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-id-card"></i>
        </div>
        <div class="menu-title">Admit Cards</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.admit-cards.index') }}">
            <i class="bx bx-radio-circle"></i>All Admit Cards
          </a>
        </li>
        <li>
          <a href="{{ route('coe.admit-cards.generate') }}">
            <i class="bx bx-radio-circle"></i>Generate Cards
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('regulation-master'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="menu-title">Regulation Master</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.regulations.index') }}">
            <i class="bx bx-radio-circle"></i>All Regulations
          </a>
        </li>
        <li>
          <a href="{{ route('coe.regulations.create') }}">
            <i class="bx bx-radio-circle"></i>Add New Regulation
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('attendance'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="menu-title">Attendance</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.attendance.index') }}">
            <i class="bx bx-radio-circle"></i>Attendance Dashboard
          </a>
        </li>
        <li>
          <a href="{{ route('coe.attendance.index') }}">
            <i class="bx bx-radio-circle"></i>Room-wise Marking
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-attendance.create') }}">
            <i class="bx bx-radio-circle"></i>Individual Marking
          </a>
        </li>
        <li>
          <a href="{{ route('coe.attendance.view') }}">
            <i class="bx bx-radio-circle"></i>View Records
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('internal-marks-review'))
    <li>
      <a href="{{ route('coe.internal-marks-review.index') }}">
        <div class="parent-icon">
          <i class="fas fa-history"></i>
        </div>
        <div class="menu-title">FA Marks Review</div>
      </a>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('marks-entry'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-edit"></i>
        </div>
        <div class="menu-title">Marks Entry</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.marks.index') }}">
            <i class="bx bx-radio-circle"></i>All Marks
          </a>
        </li>
        <li>
          <a href="{{ route('coe.marks.entry') }}">
            <i class="bx bx-radio-circle"></i>Enter Marks
          </a>
        </li>
        <li>
          <a href="{{ route('coe.marks.whitelist') }}">
            <i class="bx bx-radio-circle"></i>MAC Whitelist
          </a>
        </li>
        <li>
          <a href="{{ route('coe.marks.locks') }}">
            <i class="bx bx-radio-circle"></i>Marks Locks
          </a>
        </li>
        <li>
          <a href="{{ route('coe.marks.audit-log') }}">
            <i class="bx bx-radio-circle"></i>Audit Log
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('packet-management'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-box-open"></i>
        </div>
        <div class="menu-title">Packet Management</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.packets.index') }}">
            <i class="bx bx-radio-circle"></i>All Packets
          </a>
        </li>
        <li>
          <a href="{{ route('coe.packets.generate') }}">
            <i class="bx bx-radio-circle"></i>Generate Packets
          </a>
        </li>
        <li>
          <a href="{{ route('coe.packets.barcodes.scanner') }}">
            <i class="bx bx-radio-circle"></i>Barcode Scanner
          </a>
        </li>
        <li>
          <a href="{{ route('coe.packets.barcodes.tracking') }}">
            <i class="bx bx-radio-circle"></i>Packet Tracking
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('invigilation-duties'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="menu-title">Invigilation Duties</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.invigilation-duties.index') }}">
            <i class="bx bx-radio-circle"></i>All Duties
          </a>
        </li>
        <li>
          <a href="{{ route('admin.invigilation-duties.create') }}">
            <i class="bx bx-radio-circle"></i>Assign Duty
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('evaluation'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="menu-title">Evaluation</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.evaluation-duties.index') }}">
            <i class="bx bx-radio-circle"></i>All Duties
          </a>
        </li>
        <li>
          <a href="{{ route('admin.evaluation-duties.create') }}">
            <i class="bx bx-radio-circle"></i>Assign Duty
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('moderation'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-check-double"></i>
        </div>
        <div class="menu-title">Moderation</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.moderation-duties.index') }}">
            <i class="bx bx-radio-circle"></i>All Duties
          </a>
        </li>
        <li>
          <a href="{{ route('admin.moderation-duties.create') }}">
            <i class="bx bx-radio-circle"></i>Assign Duty
          </a>
        </li>
        <li>
          <a href="{{ route('admin.moderation-duties.compare') }}">
            <i class="bx bx-radio-circle"></i>Compare Marks
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('results'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <div class="menu-title">Results</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.exam-results.index') }}">
            <i class="bx bx-radio-circle"></i>All Results
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-results.semester-wise') }}">
            <i class="bx bx-radio-circle"></i>Semester-wise
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-results.generate') }}">
            <i class="bx bx-radio-circle"></i>Generate Results
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('backlogs'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-redo"></i>
        </div>
        <div class="menu-title">Backlogs</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('coe.backlogs.index') }}">
            <i class="bx bx-radio-circle"></i>All Backlogs
          </a>
        </li>
        <li>
          <a href="{{ route('coe.backlogs.failed-subjects') }}">
            <i class="bx bx-radio-circle"></i>Failed Subjects
          </a>
        </li>
        <li>
          <a href="{{ route('coe.backlogs.report') }}">
            <i class="bx bx-radio-circle"></i>Backlog Report
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('promotions'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-level-up-alt"></i>
        </div>
        <div class="menu-title">Promotions</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.promotions.index') }}">
            <i class="bx bx-radio-circle"></i>All Promotions
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('exit-certification'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-certificate"></i>
        </div>
        <div class="menu-title">Exit Certification</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.exit-certification.index') }}">
            <i class="bx bx-radio-circle"></i>All Records
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exit-certification.create') }}">
            <i class="bx bx-radio-circle"></i>Add Record
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('student-credits'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-coins"></i>
        </div>
        <div class="menu-title">Student Credits (ABC)</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.student-credits.index') }}">
            <i class="bx bx-radio-circle"></i>All Credits
          </a>
        </li>
        <li>
          <a href="{{ route('admin.student-credits.create') }}">
            <i class="bx bx-radio-circle"></i>Add Credits
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('remuneration'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="menu-title">Remuneration</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.exam-remuneration.index') }}">
            <i class="bx bx-radio-circle"></i>All Remuneration
          </a>
        </li>
        <li>
          <a href="{{ route('admin.payment-batches.index') }}">
            <i class="bx bx-radio-circle"></i>Payment Batches
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-remuneration.create') }}">
            <i class="bx bx-radio-circle"></i>Add Remuneration
          </a>
        </li>
      </ul>
    </li>
    @endif

    @if(StaticController::coeMenuAccess('reports'))
    <li>
      <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
          <i class="fas fa-chart-bar"></i>
        </div>
        <div class="menu-title">Reports</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('admin.exam-reports.dashboard') }}">
            <i class="bx bx-radio-circle"></i>Dashboard
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-reports.registrations') }}">
            <i class="bx bx-radio-circle"></i>Registrations Report
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-reports.attendance') }}">
            <i class="bx bx-radio-circle"></i>Attendance Report
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-reports.results') }}">
            <i class="bx bx-radio-circle"></i>Results Report
          </a>
        </li>
        <li>
          <a href="{{ route('admin.exam-reports.remuneration') }}">
            <i class="bx bx-radio-circle"></i>Remuneration Report
          </a>
        </li>
      </ul>
    </li>
    @endif

    {{-- D.COE Management - Only visible to COE --}}
    @if(StaticController::isCoe())
    <li>
      <a href="{{ route('coe.dcoe.index') }}">
        <div class="parent-icon">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="menu-title">D.COE Management</div>
      </a>
    </li>
    @endif

    <!-- logout -->
    <li>
      <a href="{{url('logout')}}">
        <div class="parent-icon">
          <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="menu-title">Logout</div>
      </a>
    </li>
    <!--end navigation-->
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
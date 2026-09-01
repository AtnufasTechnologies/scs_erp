<?php

use App\Http\Controllers\StaticController;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;

$userId   = Auth::user()->id;
$roleType = UserHasRole::where('user_id', $userId)->value('role_name');
$isIncharge  = $roleType === 'account-office-incharge';
$isAssistant = $roleType === 'account-office-assistant';
?>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-text" style="font-size: 12px;">
      {{Auth::user()->name}}
    </div>
    <div class="toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>

  <ul class="metismenu" id="menu">

    {{-- Dashboard --}}
    @if($isIncharge)
    <li>
      <a href="{{ route('account-office.dashboard') }}">
        <div class="parent-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    @else
    <li>
      <a href="{{ url('erp/admin/dashboard') }}">
        <div class="parent-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    @endif

    {{-- Accounts Office Modules --}}

    {{-- Incharge sees all; assistant sees only permitted --}}
    @if($isIncharge || StaticController::subMenuRights('late-fee-exemption'))
    <li>
      <a href="{{ url('erp/admin/accounts/late-fee-exemptions') }}">
        <div class="parent-icon"><i class="fas fa-percentage"></i></div>
        <div class="menu-title">Late Fee Exemptions</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('late-fee-revenue-report'))
    <li>
      <a href="{{ route('late-fee-revenue-report') }}">
        <div class="parent-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="menu-title">Late Fee Revenue Report</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('bank-master'))
    <li>
      <a href="{{ url('erp/admin/accounts/bankinfo') }}">
        <div class="parent-icon"><i class="fas fa-university"></i></div>
        <div class="menu-title">Bank Accounts</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('fee-head-master'))
    <li>
      <a href="{{ url('erp/admin/accounts/fee-heads') }}">
        <div class="parent-icon"><i class="fas fa-list-alt"></i></div>
        <div class="menu-title">Fee Heads</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('fee-course-master'))
    <li>
      <a href="{{ route('fee.course.master') }}">
        <div class="parent-icon"><i class="fas fa-book-open"></i></div>
        <div class="menu-title">Fee Course Master</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('fee-structure-master'))
    <li>
      <a href="{{ url('erp/admin/accounts/fee-structure') }}">
        <div class="parent-icon"><i class="fas fa-sitemap"></i></div>
        <div class="menu-title">Fee Structure</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('fee-collection-master'))
    <li>
      <a href="{{ url('erp/admin/accounts/std-fee-payments') }}">
        <div class="parent-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="menu-title">Fee Collection</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('fee-allpayments'))
    <li>
      <a href="{{ url('erp/admin/accounts/all-payments') }}">
        <div class="parent-icon"><i class="fas fa-receipt"></i></div>
        <div class="menu-title">All Payments</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('admission-application-fee'))
    <li>
      <a href="{{ url('erp/admin/accounts/admission-application-fee') }}">
        <div class="parent-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="menu-title">Admission Application Fee</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('faculty-pay-roll'))
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon"><i class="far fa-wallet"></i></div>
        <div class="menu-title">Salary Management</div>
      </a>
      <ul>
        <li>
          <a href="{{route('admin.payroll.salary-masters')}}">
            <div class="parent-icon">
              <i class="fas fa-money-check-alt"></i>
            </div>
            <div class="menu-title">Salary Masters</div>
          </a>
        </li>
        <li>
          <a href="{{route('admin.payroll.index')}}">
            <div class="parent-icon">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="menu-title">Faculty Payroll</div>
          </a>
        </li>
        <li>
          <a href="{{route('admin.payroll.loans')}}">
            <div class="parent-icon">
              <i class="fas fa-hand-holding-medical"></i>
            </div>
            <div class="menu-title">Faculty Loans</div>
          </a>
        </li>
        <li>
          <a href="{{route('admin.payroll.deductions')}}">
            <div class="parent-icon">
              <i class="fas fa-minus-circle"></i>
            </div>
            <div class="menu-title">Deduction Masters</div>
          </a>
        </li>

      </ul>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('defaulters-list'))
    <li>
      <a href="{{ route('defaulters-list') }}">
        <div class="parent-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="menu-title">Defaulters List</div>
      </a>
    </li>
    @endif

    {{-- Payment Reports --}}
    @if($isIncharge || StaticController::subMenuRights('fee-head-wise-report'))
    <li>
      <a href="{{ route('accounts.fee-head-wise-report') }}">
        <div class="parent-icon"><i class="fas fa-layer-group"></i></div>
        <div class="menu-title">Fee Head Wise Report</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('bank-account-wise-report'))
    <li>
      <a href="{{ route('accounts.bank-account-wise-report') }}">
        <div class="parent-icon"><i class="fas fa-university"></i></div>
        <div class="menu-title">Bank Account Wise Report</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('payment-report-by-date'))
    <li>
      <a href="{{ route('accounts.payment-report-by-date') }}">
        <div class="parent-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="menu-title">Payment Report by Date</div>
      </a>
    </li>
    @endif

    @if($isIncharge || StaticController::subMenuRights('payment-type-report'))
    <li>
      <a href="{{ route('accounts.payment-type-report') }}">
        <div class="parent-icon"><i class="fas fa-exchange-alt"></i></div>
        <div class="menu-title">Payment Type Report</div>
      </a>
    </li>
    @endif

    {{-- Manage Assistants (Incharge Only) --}}
    @if($isIncharge)
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon"><i class="fas fa-users-cog"></i></div>
        <div class="menu-title">Team Management</div>
      </a>
      <ul>
        <li>
          <a href="{{ route('account-office.assistant-access') }}">
            <div class="parent-icon"><i class="fas fa-user-plus"></i></div>
            <div class="menu-title">Manage Assistants</div>
          </a>
        </li>
      </ul>
    </li>
    @endif

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
<!--end account-office sidebar -->

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
        </li> -->


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
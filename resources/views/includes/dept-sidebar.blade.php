<?php

use App\Http\Controllers\StaticController;
use Illuminate\Support\Facades\Auth;
use App\Models\SubjectHasDeptAdmin;
use App\Models\Subject;

$userId = Auth::user()->id;
$subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
$subject = Subject::with(['semesters', 'courseMasterPivot'])->find($subjectId);
$data = $subject;
?>
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">

<!-- Sidebar -->
<div class="modern-sidebar">

  <div class="sidebar-logo">
    <i class="fas fa-graduation-cap"></i>
  </div>

  <a href="{{route('department.dashboard')}}">
    <div class="sidebar-icon {{ request()->routeIs('department.dashboard') ? 'active' : '' }}">
      <i class="fas fa-th-large fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.course.master',[$data->id,$data->slug])}}">
    <div class="sidebar-icon {{ request()->routeIs('department.course.master') ? 'active' : '' }}" title="Courses">
      <i class="fas fa-book fa-lg"></i>
    </div>
  </a>

  <a href="{{ route('department.activities.index', [$data->id]) }}">
    <div class="sidebar-icon  {{ request()->routeIs('department.activities.index') ? 'active' : '' }}" title="Activities">
      <i class="fas fa-calendar-check fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.faculty.list',[$data->id])}}">
    <div class="sidebar-icon" title="Faculty">
      <i class="fas fa-chalkboard-teacher fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.combo.master',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Combination Master">
      <i class="fas fa-list fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.faculty.access',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Settings">
      <i class="fas fa-cog fa-lg"></i>
    </div>
  </a>

  <div class="mt-auto sidebar-icon" title="Logout">
    <a href="{{ StaticController::fetchUserRole() == 'dept-admin-erp' ? url('logout') : route('admin.dashboard') }}" style="color: inherit;">
      <i class="fas fa-sign-out-alt fa-lg"></i>
    </a>
  </div>
</div>
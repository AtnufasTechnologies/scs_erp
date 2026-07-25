<?php

use App\Http\Controllers\StaticController;
use Illuminate\Support\Facades\Auth;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectFacultyMaster;
use App\Models\FacultyLeaveApplication;
use App\Models\Subject;

$userId = Auth::user()->id;
$subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
$subject = Subject::with(['semesters', 'courseMasterPivot'])->find($subjectId);
$data = $subject;

$pendingLeaveCount = 0;
if ($subjectId) {
  $deptFacultyIds = SubjectFacultyMaster::where('subject_id', $subjectId)->pluck('faculty_id')->toArray();
  $pendingLeaveCount = FacultyLeaveApplication::whereIn('faculty_id', $deptFacultyIds)
    ->where('status', 'pending')
    ->whereNull('dept_action')
    ->count();
}
?>
<link href="{{ asset('admin/css/dashboard-modern.css') }}" rel="stylesheet">

<!-- Sidebar -->
<div class="modern-sidebar" style="overflow-y: auto; max-height: 100vh;">



  <a href="{{route('department.dashboard')}}">
    <div class="sidebar-icon {{ request()->routeIs('department.dashboard') ? 'active' : '' }}" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
      <i class="fas fa-th-large fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.all.students')}}">
    <div class="sidebar-icon {{ request()->routeIs('department.all.students') ? 'active' : '' }}" title="Students" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Students">
      <i class="fas fa-users fa-lg"></i>
    </div>
  </a>



  <a href="{{route('department.course.master',[$data->id,$data->slug])}}">
    <div class="sidebar-icon {{ request()->routeIs('department.course.master') ? 'active' : '' }}" title="Courses" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Courses">
      <i class="fas fa-book fa-lg"></i>
    </div>
  </a>

  <a href="{{ route('department.activities.index', [$data->id]) }}">
    <div class="sidebar-icon  {{ request()->routeIs('department.activities.index') ? 'active' : '' }}" title="Activities" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Activities">
      <i class="fas fa-volleyball-ball fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.faculty.list',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Faculty" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Faculty">
      <i class="fas fa-user fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.teaching.assignment',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Teaching Assignment" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Teaching Assignment">
      <i class="fas fa-user-chart fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.timetable',[$data->id,$data->slug])}}">
    <div class="sidebar-icon {{ request()->routeIs('department.timetable') ? 'active' : '' }}" title="Timetable" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Timetable">
      <i class="fas fa-calendar-check fa-lg"></i>
    </div>
  </a>


  <a href="{{route('department.student.group.allocation',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Student Group Allotment" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Student Group Allotment">
      <i class="fas fa-layer-group fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.faculty.access',[$data->id,$data->slug])}}">
    <div class="sidebar-icon" title="Access" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Access">
      <i class="fas fa-fingerprint fa-lg"></i>
    </div>
  </a>



  <!-- <a href="{{route('department.offerings.index')}}">
    <div class="sidebar-icon {{ request()->routeIs('department.offerings.*') ? 'active' : '' }}" title="Course Offerings" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Course Offerings">
      <i class="fas fa-ticket-alt fa-lg"></i>
    </div>
  </a>

  <a href="{{route('department.seats.index')}}">
    <div class="sidebar-icon {{ request()->routeIs('department.seats.*') ? 'active' : '' }}" title="Course Seat Manager" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Course Seat Manager">
      <i class="fas fa-chair fa-lg"></i>
    </div>
  </a> -->

  <a href="{{route('department.leave.index')}}" style="position: relative;">
    <div class="sidebar-icon {{ request()->routeIs('department.leave.*') ? 'active' : '' }}" title="Leave Sanction" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Leave Sanction">
      <i class="fas fa-clipboard-check fa-lg"></i>
      @if($pendingLeaveCount > 0)
      <span style="position: absolute; top: 2px; right: 2px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; min-width: 18px; height: 18px; line-height: 18px; text-align: center; border-radius: 50%; padding: 0 4px; box-shadow: 0 2px 4px rgba(239,68,68,0.4);">{{ $pendingLeaveCount }}</span>
      @endif
    </div>
  </a>


  <a href="{{url('logout')}}" style="position: relative;">
    <div class="sidebar-icon {{ request()->routeIs('department.leave.*') ? 'active' : '' }}" title="Logout" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Logout">
      <i class="fas fa-sign-out-alt fa-lg text-warning"></i>
    </div>
  </a>


</div>
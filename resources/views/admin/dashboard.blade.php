<?php

use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\StudentSemesterConfig;
use App\Models\SubjectHasStudentProgam;
use App\Models\User;

$totalStudents = StudentMaster::where('is_left', 0)->where('is_deleted', 0)->count();
$totalFaculty = Faculty::where('IS_LEFT', 0)->count();
$totalUsers = User::where('status', 'ACTIVE')->count();
$activeCourses = StudentProgram::count();
$activeBatch = BatchMaster::select('id', 'batch_name')->where('admission_active_batch', 1)->first();
$batchName = $activeBatch->batch_name ?? 'Not Set';
$combinationCount = $activeBatch
  ? SubjectHasStudentProgam::where('batch_id', $activeBatch->id)->count()
  : 0;
$batchwiseStudentCounts = BatchMaster::query()
  ->leftJoin('student_masters as sm', function ($join) {
    $join->on('batch_masters.id', '=', 'sm.batch')
      ->where('sm.is_deleted', 0)
      ->where('sm.is_left', 0);
  })
  ->select('batch_masters.id', 'batch_masters.batch_name')
  ->selectRaw('COUNT(sm.id) as students_count')
  ->groupBy('batch_masters.id', 'batch_masters.batch_name')
  ->orderByDesc('batch_masters.id')
  ->limit(8)
  ->get();
$semesterwiseStudentCounts = StudentSemesterConfig::query()
  ->join('student_masters as sm', function ($join) {
    $join->on('student_semester_configs.student_id', '=', 'sm.id')
      ->where('sm.is_deleted', 0)
      ->where('sm.is_left', 0);
  })
  ->selectRaw('student_semester_configs.semester_id as semester, COUNT(DISTINCT student_semester_configs.student_id) as students_count')
  ->whereNotNull('student_semester_configs.semester_id')
  ->where('student_semester_configs.semester_id', '!=', '')
  ->groupBy('student_semester_configs.semester_id')
  ->orderByRaw('CAST(student_semester_configs.semester_id AS UNSIGNED) ASC')
  ->get();
$roletype = auth()->user()->userroletype;
$roleName = $roletype->role_name ?? 'Admin';
?>
@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid dashboard-wrap">
  <section class="hero-panel mb-5">
    <div>
      <p class="hero-kicker">ADMIN CONSOLE</p>
      <h1>
        <span class="text-capitalize text-light">Hi, {{ auth()->user()->name }}</span>
      </h1>
      <p class="hero-subtitle">Role: {{ $roleName }}</p>

    </div>
    <div class="hero-badge">
      <p>Active Academic Batch</p>
      <h4>{{ $batchName }}</h4>
    </div>
  </section>

  <section class="stats-grid mb-5">
    <article class="stat-card">
      <div class="stat-icon icon-students"><i class="fas fa-user-graduate"></i></div>
      <div class="stat-content">
        <h3>Total Students</h3>
        <p class="stat-number">{{ $totalStudents }}</p>
      </div>
    </article>

    <article class="stat-card">
      <div class="stat-icon icon-faculty"><i class="fas fa-user-tie"></i></div>
      <div class="stat-content">
        <h3>Total Faculty</h3>
        <p class="stat-number">{{ $totalFaculty }}</p>
      </div>
    </article>

    <article class="stat-card">
      <div class="stat-icon icon-programs"><i class="fas fa-book-open"></i></div>
      <div class="stat-content">
        <h3>Program Masters</h3>
        <p class="stat-number">{{ $activeCourses }}</p>
      </div>
    </article>

    <article class="stat-card">
      <div class="stat-icon icon-combinations"><i class="fas fa-layer-group"></i></div>
      <div class="stat-content">
        <h3>Program Combinations</h3>
        <p class="stat-number">{{ $combinationCount }}</p>
      </div>
    </article>

    <article class="stat-card">
      <div class="stat-icon icon-users"><i class="fas fa-users"></i></div>
      <div class="stat-content">
        <h3>Active Users</h3>
        <p class="stat-number">{{ $totalUsers }}</p>
      </div>
    </article>
  </section>

  <section class="quick-links-card">
    <div class="card-head">
      <h3>Quick Access</h3>
      <p>Aligned with key admin sidebar modules</p>
    </div>
    <div class="quick-links-grid">
      <a class="quick-link" href="{{ url('erp/admin/master/batch') }}">
        <i class="fas fa-swatchbook"></i>
        <span>Batches</span>
      </a>
      <a class="quick-link" href="{{ url('erp/admin/master/programs') }}">
        <i class="fas fa-object-group"></i>
        <span>Campus Stream Combination</span>
      </a>
      <a class="quick-link" href="{{ url('erp/admin/master/subjects') }}">
        <i class="fas fa-building"></i>
        <span>Departments</span>
      </a>
      <a class="quick-link" href="{{ route('itcell.admission.applications') }}">
        <i class="fas fa-check-circle"></i>
        <span>Admission Applications</span>
      </a>
    </div>
  </section>

  <section class="row g-3">
    <div class="col-lg-4">
      <div class="panel-card h-100">
        <h3>Recent Notices</h3>
        <div class="activity-list">
          @forelse($recentNotices ?? [] as $notice)
          <div class="activity-item">
            <div class="activity-icon"><i class="fas fa-bell"></i></div>
            <div class="activity-details">
              <p class="activity-title">{{ $notice->title }}</p>
              <span class="activity-time">{{ $notice->created_at->diffForHumans() }}</span>
            </div>
          </div>
          @empty
          <p class="text-muted mb-0">No recent notices.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="panel-card h-100">
        <h3>Batch Wise Student Count</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Batch</th>
                <th>Students</th>
              </tr>
            </thead>
            <tbody>
              @forelse($batchwiseStudentCounts as $batchCount)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $batchCount->batch_name }}</td>
                <td>{{ $batchCount->students_count }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">No batch-wise data found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="panel-card h-100">
        <h3>Semester Wise Student Count</h3>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Semester</th>
                <th>Students</th>
              </tr>
            </thead>
            <tbody>
              @forelse($semesterwiseStudentCounts as $semesterCount)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>Semester {{ $semesterCount->semester }}</td>
                <td>{{ $semesterCount->students_count }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center">No semester-wise data found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>


@include('includes.footer')
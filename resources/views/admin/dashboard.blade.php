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

<style>
  .dashboard-wrap {
    --dash-bg: #f5f8f7;
    --dash-card: #ffffff;
    --dash-ink: #13332d;
    --dash-muted: #556971;
    --dash-line: #d8e6e2;
    --dash-accent: #0f4476;
    --dash-accent-soft: #d7f2ee;
    /* background: radial-gradient(circle at top right, #e5f4f1 0%, var(--dash-bg) 42%, #eef4f8 100%); */
    padding-bottom: 24px;
  }

  .hero-panel {
    background: linear-gradient(120deg, #3d49a6 0%, #5409b7 100%);
    color: #f5fffc;
    padding: 28px;
    border-radius: 18px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 22px;
    box-shadow: 0 12px 28px rgba(17, 63, 56, 0.18);
  }

  .hero-kicker {
    margin: 0;
    font-size: 12px;
    letter-spacing: 0.12em;
    opacity: 0.85;
  }

  .hero-panel h1 {
    margin: 8px 0 4px;
    font-size: 30px;
    line-height: 1.2;
    font-weight: 700;
  }

  .hero-subtitle {
    margin: 0;
    color: #d2fff6;
    font-size: 14px;
  }

  .hero-quote {
    margin-top: 14px;
    font-size: 14px;
    color: #defaf4;
  }

  .hero-quote span {
    color: #b9efe4;
  }

  .hero-badge {
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 14px;
    padding: 14px 16px;
    min-width: 180px;
  }

  .hero-badge p {
    margin: 0;
    font-size: 12px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }

  .hero-badge h4 {
    margin: 8px 0 0;
    font-size: 20px;
    color: #f3f9ff;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
  }

  .stat-card {
    background: #e8f0ff;
    /* border: 1px solid grey; */
    padding: 20px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 15px 15px 30px #bebebe,
      -15px -15px 30px #ffffff;
  }

  .stat-content h3 {
    font-size: 14px;
    color: var(--dash-muted);
    margin: 0;
    font-weight: 600;
  }

  .stat-number {
    font-size: 30px;
    font-weight: 700;
    color: var(--dash-ink);
    margin: 6px 0 0;
  }

  .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    color: white;
  }

  .icon-students {
    background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
  }

  .icon-faculty {
    background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
  }

  .icon-programs {
    background: linear-gradient(135deg, #ca8a04 0%, #f59e0b 100%);
  }

  .icon-combinations {
    background: linear-gradient(135deg, #4d7c0f 0%, #65a30d 100%);
  }

  .icon-users {
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  }

  .quick-links-card,
  .panel-card {
    background: var(--dash-card);
    border: 1px solid var(--dash-line);
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(15, 45, 40, 0.06);
    padding: 20px;
    margin-bottom: 20px;
  }

  .card-head h3,
  .panel-card h3 {
    margin: 0;
    color: var(--dash-ink);
    font-size: 19px;
  }

  .card-head p {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--dash-muted);
  }

  .quick-links-grid {
    margin-top: 14px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 10px;
  }

  .quick-link {
    border: 1px solid var(--dash-line);
    border-radius: 12px;
    padding: 12px 14px;
    text-decoration: none;
    color: var(--dash-ink);
    background: linear-gradient(180deg, #ffffff 0%, #f3fbf9 100%);
    display: flex;
    align-items: center;
    gap: 9px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
  }

  .quick-link i {
    color: var(--dash-accent);
  }

  .quick-link:hover {
    border-color: #8ba9c4;
    color: #0b413a;
    transform: translateY(-1px);
  }

  .activity-list {
    margin-top: 14px;
    max-height: 365px;
    overflow-y: auto;
  }

  .activity-item {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px dashed var(--dash-line);
  }

  .activity-item:last-child {
    border-bottom: none;
  }

  .activity-icon {
    width: 34px;
    min-width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    background: var(--dash-accent-soft);
    color: var(--dash-accent);
  }

  .activity-title {
    margin: 0;
    color: #173f38;
    font-weight: 600;
    font-size: 14px;
  }

  .activity-time {
    font-size: 12px;
    color: #636b80;
  }

  .data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #f8fcfb;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(15, 45, 40, 0.06);
  }

  .data-table th {
    background: linear-gradient(90deg, #dbe4f2 0%, #f3fbf9 100%);
    padding: 14px 16px;
    text-align: left;
    font-weight: 700;
    color: #0e244f;
    font-size: 13px;
    border-bottom: 2px solid var(--dash-line);
    letter-spacing: 0.01em;
  }

  .data-table td {
    padding: 13px 16px;
    border-bottom: 1px solid #e4efec;
    font-size: 14px;
    color: #1b3f38;
    background: #fff;
    transition: background 0.15s;
  }

  .data-table tr:hover td {
    background: #f3fbf9;
  }

  .table-wrap {
    margin-top: 14px;
    overflow-x: auto;
  }

  @media (max-width: 991px) {
    .hero-panel {
      flex-direction: column;
    }

    .hero-badge {
      width: 100%;
    }

  }
</style>
@include('includes.footer')
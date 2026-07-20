<?php

use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\HourMaster;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\SubjectHasStudentProgam;

$totalStudents = StudentMaster::where('is_left', 0)->where('is_deleted', 0)->count();
$totalFaculty = Faculty::where('IS_LEFT', 0)->count();
$activeCourses = StudentProgram::count(); // Example static data
$labels = HourMaster::pluck('name')->all();
$batchId = BatchMaster::where('admission_active_batch', 1)->value('id');
$batchName = BatchMaster::where('admission_active_batch', 1)->value('batch_name');
$combinationCount = SubjectHasStudentProgam::with('batchmaster')->where('batch_id', $batchId)->count();
$roletype = auth()->user()->userroletype;
?>
@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid">

  <span style="font-weight: 100; font-size: 33px;">Hi, </span>
  <span class="text-capitalize text-primary" style="font-weight: 700; font-size: 33px;">{{ auth()->user()->name }}</span> <span>({{ $roletype->role_name }})</span><br>
  <strong>Stay Motivated</strong>
  <p>{{$quote->body ?? ''}} <span class="text-muted">- {{$quote->author ?? 'Unknown'}}</span></p>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-primary">
        <i class="fas fa-user-graduate"></i>
      </div>
      <div class="stat-content">
        <h3>Total Students</h3>
        <p class="stat-number">{{ $totalStudents ?? 0 }}</p>
        <span class="stat-change positive">Active </span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-success">
        <i class="fas fa-user-tie"></i>
      </div>
      <div class="stat-content">
        <h3>Total Faculty</h3>
        <p class="stat-number">{{ $totalFaculty ?? 0 }}</p>
        <span class="stat-change positive"></span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-warning">
        <i class="fas fa-book-open"></i>
      </div>
      <div class="stat-content">
        <h3>Active Courses</h3>
        <p class="stat-number">{{ $activeCourses ?? 0 }}</p>
        <span class="stat-change positive"></span>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-danger">
        <i class="fas fa-percentage"></i>
      </div>
      <div class="stat-content">
        <h3>Offered Combinations {{$batchName}}</h3>
        <p class="stat-number">{{ $combinationCount}}</p>
        <span class="stat-change positive">Academic Batch - {{$batchName}}</span>
      </div>
    </div>
  </div>
  <div class="charts-section">
    <div class="chart-card">
      <h5> Admission Enrollment Trend - {{$batchName}}</h5>
      <canvas id="enrollmentChart"></canvas>

    </div>

    <div class="chart-card">
      <h5> Dept Wise Enrollment Distribution - {{$batchName}} </h5>

      <canvas id="enrollmentDeptChart"></canvas>

    </div>
    <div class="chart-card">
      <h5> Campus Wise Registration - {{$batchName}} </h5>

      <canvas id="campusChart"></canvas>

    </div>

    <div class="chart-card">
      <h3>Recent Notices</h3>
      <div class="activity-list">
        @forelse($recentNotices ?? [] as $notice)
        <div class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-bell"></i>
          </div>
          <div class="activity-details">
            <p class="activity-title">{{ $notice->title }}</p>
            <span class="activity-time">{{ $notice->created_at->diffForHumans() }}</span>
          </div>
        </div>
        @empty
        <p class="text-muted">No recent notices</p>
        @endforelse
      </div>
    </div>

  </div>
  <!-- Hourwise Class Attendance Section -->
  <!-- <div class="charts-section">
    <div class="chart-card" style="grid-column: span 2;">
      <h3>Hourwise Class Attendance (Today)</h3>


      <table class="data-table" id="exportTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Hour</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Attendance %</th>
          </tr>
        </thead>
        <tbody>
          @forelse($hourwiseAttendance ?? [] as $hour)
          <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{ $hour->title }}</td>
            <td>{{ $hour->present }}</td>
            <td>{{ $hour->absent }}</td>
            <td>
              @php
              $total = $hour->present + $hour->absent;
              $percent = $total > 0 ? round(($hour->present / $total) * 100, 1) : 0;
              @endphp
              {{ round($percent) }}%
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center">No hourwise attendance data available</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div> -->
  <!-- End Hourwise Class Attendance Section -->



  <div class="data-table-section">
    <div class="table-card">
      <h3>Recent Fee Payments</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentPayments ?? [] as $payment)
          <tr>
            <td>#{{ $payment->id }}</td>
            <td>{{ $payment->student_name }}</td>
            <td>₹{{ number_format($payment->amount, 2) }}</td>
            <td><span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
            <td>{{ $payment->created_at->format('M d, Y') }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">No payments found</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
  .dashboard-container {
    padding: 20px;
    background: #f5f6fa;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 28px;
    margin-bottom: 36px;
  }

  .stat-card {
    background: linear-gradient(120deg, #f8fafc 60%, #e0e7ff 100%);
    padding: 28px 24px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.08), 0 1.5px 6px rgba(0, 0, 0, 0.03);
    transition: transform 0.18s cubic-bezier(.4, 2, .6, 1), box-shadow 0.18s;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 90px;
    height: 90px;
    background: rgba(102, 126, 234, 0.09);
    border-radius: 50%;
    z-index: 0;
  }

  .stat-card:hover {
    transform: translateY(-7px) scale(1.025);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.13), 0 2px 8px rgba(0, 0, 0, 0.04);
  }

  .stat-icon {
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.10);
    border: 2px solid #fff;
  }

  .stat-content {
    position: relative;
    z-index: 1;
  }

  .stat-content h3 {
    font-size: 15px;
    color: #5a5a89;
    margin: 0 0 6px 0;
    letter-spacing: 0.02em;
    font-weight: 600;
  }

  .stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #22223b;
    margin: 7px 0 3px 0;
    letter-spacing: 0.01em;
  }

  .stat-change {
    font-size: 13px;
    font-weight: 500;
    opacity: 0.85;
  }

  .stat-change.positive {
    color: #22c55e;
  }

  .stat-change.negative {
    color: #ef4444;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    margin-right: 15px;
  }

  .bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .bg-success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }

  .bg-warning {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .bg-danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  }

  .stat-content h3 {
    font-size: 14px;
    color: #666;
    margin: 0 0 5px 0;
  }

  .stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin: 5px 0;
  }

  .stat-change {
    font-size: 12px;
  }

  .stat-change.positive {
    color: #4caf50;
  }

  .stat-change.negative {
    color: #f44336;
  }

  .charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
  }

  .chart-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .chart-card h3 {
    margin-bottom: 20px;
    color: #333;
  }

  .activity-list {
    max-height: 400px;
    overflow-y: auto;
  }

  .activity-item {
    display: flex;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
  }

  .activity-icon {
    margin-right: 15px;
    color: #667eea;
  }

  .activity-title {
    margin: 0;
    color: #333;
    font-weight: 500;
  }

  .activity-time {
    font-size: 12px;
    color: #999;
  }

  .table-card {
    background: #fff;
    padding: 28px 24px;
    border-radius: 16px;
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.08), 0 1.5px 6px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
    transition: box-shadow 0.18s;
  }

  .table-card:hover {
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.13), 0 2px 8px rgba(0, 0, 0, 0.04);
  }

  .data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(102, 126, 234, 0.06);
  }

  .data-table th {
    background: linear-gradient(90deg, #e0e7ff 0%, #f8fafc 100%);
    padding: 14px 16px;
    text-align: left;
    font-weight: 700;
    color: #4f46e5;
    font-size: 15px;
    border-bottom: 2px solid #e5e7eb;
    letter-spacing: 0.01em;
  }

  .data-table td {
    padding: 13px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
    color: #22223b;
    background: #fff;
    transition: background 0.15s;
  }

  .data-table tr:hover td {
    background: #f1f5fd;
  }

  .badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.01em;
    box-shadow: 0 1px 4px rgba(102, 126, 234, 0.07);
    border: none;
    margin-right: 4px;
    margin-bottom: 2px;
    vertical-align: middle;
  }

  .badge-pending {
    background: linear-gradient(90deg, #fffbe6 0%, #fff3cd 100%);
    color: #b38600;
    border: 1px solid #ffe066;
  }

  .badge-completed {
    background: linear-gradient(90deg, #e6fffa 0%, #d4edda 100%);
    color: #218838;
    border: 1px solid #b7f7d8;
  }

  .badge-cancelled {
    background: linear-gradient(90deg, #ffeaea 0%, #f8d7da 100%);
    color: #c82333;
    border: 1px solid #f5c6cb;
  }
</style>
@include('includes.footer')
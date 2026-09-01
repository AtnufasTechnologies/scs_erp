<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\ProgramMaster;
use App\Models\Semester;
use App\Models\StudentProgram;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasRoutine;
use App\Models\SpecializationMaster;
use App\Models\ShiftMaster;
use App\Models\Campus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


$batches = BatchMaster::latest()->get();
$semesters = Semester::get();
$course_master = SubjectCourseMaster::with('courseMaster')->where('subject_id', $data->id)->get();
$faculties = Faculty::all();
$mainStreams = ProgramMaster::all();
$subjectShiftIds = collect($data->shift_ids ?? [])->map(fn($id) => (int) $id)->filter()->unique()->values();
$subjectHasShiftDelivery = (int) ($data->has_shift_delivery ?? 0) === 1;
$campuses = Campus::all();
$shiftQuery = ShiftMaster::where('is_active', 1)->orderBy('sort_order');
if ($subjectHasShiftDelivery) {
  if ($subjectShiftIds->isNotEmpty()) {
    $shiftQuery->whereIn('id', $subjectShiftIds->all());
  } else {
    $shiftQuery->whereRaw('1 = 0');
  }
} else {
  $commonShiftId = ShiftMaster::where('slug', 'common')->value('id');
  if (!empty($commonShiftId)) {
    $shiftQuery->where('id', (int) $commonShiftId);
  } else {
    $shiftQuery->whereRaw('1 = 0');
  }
}

$shiftMasters = $shiftQuery->get(['id', 'slug', 'title']);

$deptFacultyIds = collect($deptfaculties ?? [])->pluck('faculty_id')->filter()->unique()->values()->all();
$facultyIdsWithTimetable = [];
if (!empty($deptFacultyIds)) {
  $facultyIdsWithTimetable = SubjectHasRoutine::whereIn('faculty_id', $deptFacultyIds)
    ->whereHas('syllabus', function ($query) use ($data) {
      $query->where('subject_id', $data->id);
    })
    ->distinct()
    ->pluck('faculty_id')
    ->map(fn($id) => (int) $id)
    ->all();
}

$integratedProgramIds = collect();
if (Schema::hasTable('integrated_program_sublayer_settings')) {
  $integratedProgramIds = DB::table('integrated_program_sublayer_settings')
    ->where('is_active', 1)
    ->pluck('student_program_id')
    ->map(fn($id) => (int) $id)
    ->filter(fn($id) => $id > 0)
    ->unique()
    ->values();
}
?>
@include('includes.header')
@include('includes.dept-sidebar')

<style>
  /* Custom scrollbar for activities section */
  .activities-scroll::-webkit-scrollbar {
    width: 8px;
  }

  .activities-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .activities-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
  }

  .activities-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  }

  /* For Firefox */
  .activities-scroll {
    scrollbar-width: thin;
    scrollbar-color: #667eea #f1f1f1;
  }

  .quick-scroll-row {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 4px 4px 12px;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .quick-scroll-row .quick-item {
    flex: 0 0 clamp(220px, 24vw, 280px);
    min-width: 220px;
    scroll-snap-align: start;
  }

  .quick-scroll-row .stats-card,
  .quick-scroll-row .action-card {
    height: 100%;
  }

  .quick-scroll-row::-webkit-scrollbar {
    height: 8px;
  }

  .quick-scroll-row::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
  }

  @media (max-width: 768px) {
    .quick-scroll-row .quick-item {
      flex-basis: 78vw;
      min-width: 78vw;
    }
  }

  .attendance-filter-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    padding: 16px;
  }

  .attendance-summary-card {
    border-radius: 14px;
    padding: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
  }

  .attendance-summary-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
  }

  .attendance-table-wrap {
    max-height: 360px;
    overflow: auto;
  }

  .attendance-chart-wrap {
    position: relative;
    width: 100%;
    height: 260px;
    max-height: 260px;
  }

  .attendance-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
  }

  @media (max-width: 768px) {
    .attendance-chart-wrap {
      height: 210px;
      max-height: 210px;
    }
  }
</style>

<!-- Main Content -->
<div class="main-content">
  @php
  $notificationCount = count($upcomingActivities ?? []);
  @endphp

  <nav class="navbar navbar-expand-lg bg-white border rounded-3 shadow-sm px-3 py-2 mb-3">
    <div class="container-fluid px-0">
      <a class="navbar-brand fw-bold" href="#" style="color: #1f2937;">
        {{ $data->title ?? 'Department Dashboard' }}
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#deptNavbar" aria-controls="deptNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="deptNavbar">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-2 mt-lg-0">

          <li class="nav-item dropdown">
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell fs-5 text-dark"></i>
              @if($notificationCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $notificationCount }}
              </span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 300px;">
              <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center">
                <span>Notifications</span>
                @if($notificationCount > 0)
                <span class="badge bg-primary">{{ $notificationCount }}</span>
                @endif
              </li>
              @if($notificationCount > 0)
              <li>
                <a class="dropdown-item d-flex justify-content-between" href="{{ route('department.activities.index', [$data->id]) }}">
                  Upcoming Activities
                  <span class="badge bg-primary">{{ $notificationCount }}</span>
                </a>
              </li>
              @else
              <li><span class="dropdown-item-text text-muted">No new notifications</span></li>
              @endif

            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>



  <div class="row g-4">
    <div class="quick-scroll-row">
      <!-- Quick Stats -->
      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Course Master</div>
              <div style="font-size: 36px; font-weight: 700;">{{ $data->courseMasterPivot->count() ?? 0 }}</div>
              <a href="{{route('department.course.master',[$data->id,$data->slug])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">View Details →</a>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-book" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Syllabus </div>
              <div style="font-size: 36px; font-weight: 700;">{{ $syllabusCount ?? 0 }}</div>
              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{route('department.syllabus.manager',['id'=>$data->id,'slug'=>$data->slug])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manager →</a>
              </div>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-box" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="quick-item">

        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faculty</div>
              <div style="font-size: 36px; font-weight: 700;">{{count($deptfaculties)}} </div>


              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{ route('department.faculty.access', [$data->id,$data->slug]) }}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manage →</a>
              </div>
            </div>

            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chalkboard-teacher" style="font-size: 28px;"></i>
            </div>

          </div>
        </div>

      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Program</div>
              <div style="font-size: 36px; font-weight: 700;">Specialization </div>

              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{route('department.specialization.master',[ $data->id, $data->title])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manage →</a>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Student Group </div>
              <div style="font-size: 36px; font-weight: 700;">Allocation </div>

              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{route('department.student.group.allocation',[ $data->id, $data->title])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manage →</a>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Time Table</div>
              <div style="font-size: 36px; font-weight: 700;">
                <a href="{{ route('department.timetable', [$data->id,$data->title]) }}" style="color: white; text-decoration: none;">Scheduler</a>
              </div>
              <div style="opacity: 0.9; font-size: 13px;">Manager →</div>
              <div style="margin-top: 6px; font-size: 13px;">
                <a href="{{ route('department.timetable.history', [$data->id]) }}" style="color: #fff3a3; text-decoration: none; font-weight: 700;">
                  <i class="fa fa-history me-1"></i>Full History View
                </a>
              </div>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calendar-alt" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="quick-item">

        <div class="action-card gradient-green">
          <div class="action-card-icon">
            <i class="fas fa-exchange-alt"></i>
          </div>
          <div>
            <a href="{{ route('department.substitution', [$data->id]) }}" style="color:yellow;">
              <h6 class="mb-1" style="font-weight: 700;">Manage Substitution</h6>
            </a>
            <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Get a reminder to help with your studying process.</p>
          </div>

          <div class="mt-2">
            <a href="{{ route('department.substitution.history.page') }}" style="color:yellow; font-size: 13px; font-weight:bold">
              View Substitution History →</a>
          </div>
        </div>

      </div>

      <div class="quick-item">
        <a href="{{route('department.admission.list')}}" style="text-decoration: none;">
          <div class="action-card gradient-green">
            <div class="action-card-icon">
              <i class="fas fa-certificate"></i>
            </div>
            <div>
              <h6 class="mb-1" style="font-weight: 700;">Admission Portal</h6>
              <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Stay updated with registrations and applications</p>
            </div>
            <div class="mt-2" style="font-size: 13px; opacity: 0.9;">View Now →</div>
          </div>
        </a>
      </div>

    </div>

    <!-- Left Column: Today's Course -->
    <div class="col-lg-5">
      <!-- Upcoming Activities Section -->
      @if(count($upcomingActivities) > 0)
      <div class="mb-4">
        <div class="p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">
              <i class="fas fa-calendar-star me-2" style="color: #fbbf24;"></i>Upcoming Activities
            </h5>
            <a href="{{ route('department.activities.index', [$data->id]) }}" class="btn btn-modern" style="background: #5b4cdb; color: white;">
              <i class="fas fa-calendar-check me-2"></i>View All ({{ $activityStats['total'] ?? 0 }})
            </a>
          </div>
          <div class="row g-3 activities-scroll" style="max-height: 500px; overflow-y: auto; overflow-x: hidden; padding-right: 10px;">
            @foreach($upcomingActivities as $activity)
            <div class="col-md-12">
              <div class="course-card" style="border-left: 4px solid #667eea;">
                <div class="d-flex align-items-start gap-3">
                  <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-calendar-day" style="color: white; font-size: 20px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600;">{{ $activity->title }}</h6>
                    <div class="mb-2">
                      <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                        {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                      </span>
                    </div>
                    <p class="mb-1" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-calendar me-1" style="color: #667eea;"></i>{{ $activity->formatted_date }}
                    </p>
                    @if($activity->start_time)
                    <p class="mb-1" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-clock me-1" style="color: #667eea;"></i>{{ date('h:i A', strtotime($activity->start_time)) }}
                    </p>
                    @endif
                    @if($activity->venue)
                    <p class="mb-2" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-map-marker-alt me-1" style="color: #667eea;"></i>{{ Str::limit($activity->venue, 30) }}
                    </p>
                    @endif
                    @if($activity->expected_participants)
                    <p class="mb-0" style="font-size: 12px; color: #6b7280;">
                      <i class="fas fa-users me-1 text-warning fa-2x"></i>{{ $activity->expected_participants }} attendees expected |
                      <i class="fas fa-user-check me-1 text-success fa-2x"></i>{{ $activity->participants_count }} confirmed
                    </p>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

    </div>

    <!-- Right Column: Stats and Actions -->
    <div class="col-lg-12">
      <div class="row g-3 mb-4">
        <div class="col-12">
          <div class="attendance-filter-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0" style="color: #1a1a1a; font-weight: 700;">
                <i class="fas fa-chart-line me-2" style="color: #0ea5e9;"></i>Attendance Analytics
              </h5>
              <span class="badge" style="background: #ecfeff; color: #0c4a6e; border: 1px solid #bae6fd;">
                Date Wise + Course Wise
              </span>
            </div>

            <form method="GET" action="{{ route('department.dashboard') }}" class="row g-2 align-items-end mb-3">
              <input type="hidden" name="batch" value="{{ request('batch') }}">

              <div class="col-md-3">
                <label for="attendanceFrom" class="form-label fw-semibold" style="color: #475569;">From Date</label>
                <input type="date" name="attendance_from" id="attendanceFrom" class="form-control" value="{{ $attendanceFrom }}">
              </div>

              <div class="col-md-3">
                <label for="attendanceTo" class="form-label fw-semibold" style="color: #475569;">To Date</label>
                <input type="date" name="attendance_to" id="attendanceTo" class="form-control" value="{{ $attendanceTo }}">
              </div>

              <div class="col-md-2">
                <label for="attendanceBatch" class="form-label fw-semibold" style="color: #475569;">Batch</label>
                <select name="attendance_batch" id="attendanceBatch" class="form-select">
                  <option value="">All Batches</option>
                  @foreach($batches as $batch)
                  <option value="{{ $batch->id }}" {{ (int) ($attendanceBatch ?? 0) === (int) $batch->id ? 'selected' : '' }}>
                    {{ $batch->batch_name }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4">
                <label for="attendanceCourseId" class="form-label fw-semibold" style="color: #475569;">Course (Date Wise Trend)</label>
                <select name="attendance_course_id" id="attendanceCourseId" class="dselect-example">
                  <option value="">All Courses</option>
                  @foreach($attendanceCourses as $course)
                  <option value="{{ $course->id }}" {{ (int) ($attendanceCourseId ?? 0) === (int) $course->id ? 'selected' : '' }}>
                    {{ trim(($course->course_code ?? '') . ' - ' . ($course->course_title ?? ''), ' -') }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 d-flex gap-2 mt-1">
                <button type="submit" class="btn btn-modern" style="background: #0284c7; color: #fff;">
                  <i class="fas fa-filter me-1"></i>Check Percentage
                </button>
                <a href="{{ route('department.dashboard', ['batch' => request('batch')]) }}" class="btn btn-light border">
                  <i class="fas fa-rotate-left me-1"></i>Reset
                </a>
              </div>
            </form>

            <div class="row g-2 mb-3">
              <div class="col-md-4">
                <div class="attendance-summary-card">
                  <div style="font-size: 12px; color: #64748b;">Overall Attendance</div>
                  <div class="attendance-summary-value">{{ number_format((float) $overallAttendancePercentage, 2) }}%</div>
                  <div style="font-size: 12px; color: #64748b;">{{ $overallAttendedRecords }} / {{ $overallTotalRecords }} records present</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="attendance-summary-card">
                  <div style="font-size: 12px; color: #64748b;">Attendance Alerts</div>
                  <div class="attendance-summary-value" style="color: #dc2626;">{{ $attendanceAlertCount }}</div>
                  <div style="font-size: 12px; color: #64748b;">{{ $belowThresholdCount }} students below 75%</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="attendance-summary-card">
                  <div style="font-size: 12px; color: #64748b;">Date Range</div>
                  <div style="font-size: 18px; font-weight: 700; color: #1e293b;">{{ \Carbon\Carbon::parse($attendanceFrom)->format('d M Y') }}</div>
                  <div style="font-size: 13px; color: #64748b;">to {{ \Carbon\Carbon::parse($attendanceTo)->format('d M Y') }}</div>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <div class="course-card">
                  <h6 style="font-weight: 700; color: #0f172a;">Course Wise Attendance Percentage</h6>
                  @if(($courseWiseAttendance ?? collect())->count() > 0)
                  <div class="attendance-chart-wrap">
                    <canvas id="courseWiseAttendanceChart"></canvas>
                  </div>
                  @else
                  <p class="mb-0" style="color: #94a3b8;">No attendance data found for selected filters.</p>
                  @endif
                </div>
              </div>

              <div class="col-12">
                <div class="course-card">
                  <h6 style="font-weight: 700; color: #0f172a;">Date Wise Attendance Percentage</h6>
                  @if(($dateWiseAttendance ?? collect())->count() > 0)
                  <div class="attendance-chart-wrap">
                    <canvas id="dateWiseAttendanceChart"></canvas>
                  </div>
                  @else
                  <p class="mb-0" style="color: #94a3b8;">No date-wise attendance entries in this window.</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="table-modern mt-4">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">Attendance Alert Students</h5>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('department.dashboard.attendance-alerts.export') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-download me-1"></i>Export CSV
          </a>
          <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">{{ $attendanceAlertCount }} Students</span>
        </div>
      </div>

      <div class="attendance-table-wrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
            <tr>
              <th style="font-size: 12px; text-transform: uppercase; color: #64748b;">Roll No</th>
              <th style="font-size: 12px; text-transform: uppercase; color: #64748b;">Student Name</th>
              <th style="font-size: 12px; text-transform: uppercase; color: #64748b;">Program</th>
              <th style="font-size: 12px; text-transform: uppercase; color: #64748b;">Present / Total</th>
              <th style="font-size: 12px; text-transform: uppercase; color: #64748b;">Current %</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attendanceAlertStudents as $student)
            <tr>
              <td>
                <a href="{{ route('department.student.attendance.details', ['id' => $student['student_id'], 'rollno' => $student['roll_no']]) }}" class="fw-semibold" style="color: #0369a1; text-decoration: none;" title="View attendance details">
                  {{ $student['roll_no'] }}
                </a>
              </td>
              <td>{{ $student['student_name'] }}</td>
              <td>{{ $student['program_name'] }}</td>
              <td>{{ $student['attended_records'] }} / {{ $student['total_records'] }}</td>
              <td>
                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                  {{ number_format((float) $student['attendance_percentage'], 2) }}%
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center" style="color: #64748b;">No students below 75% in overall attendance records.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>


  <!-- Program Combinations Section -->
  <div class="table-modern mt-4">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">Program Combinations</h5>
        <button class="btn btn-modern" style="background: #5b4cdb; color: white;" data-bs-toggle="modal" data-bs-target="#programConnect">
          <i class="fas fa-plus-circle me-2"></i>Add Program
        </button>
      </div>

      <div class="mb-3">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
          <input type="hidden" name="attendance_from" value="{{ $attendanceFrom }}">
          <input type="hidden" name="attendance_to" value="{{ $attendanceTo }}">
          <input type="hidden" name="attendance_batch" value="{{ $attendanceBatch }}">
          <input type="hidden" name="attendance_course_id" value="{{ $attendanceCourseId }}">
          <label for="batchFilter" class="fw-semibold" style="color: #6b7280;">Filter by Batch:</label>
          <select name="batch" id="batchFilter" class="form-select" style="width: 200px; border-radius: 10px; border: 1px solid #e5e7eb;" onchange="this.form.submit()">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ request('batch') == $batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
        </form>
      </div>

      @if(count($combinations))
      @php
      $specializations = SpecializationMaster::where('subject_id', $data->id)->where('is_active', 1)->orderBy('name')->get();
      @endphp
      <div class="row g-3">
        @forelse($combinations as $combination)
        @php
        $selectedSpecializationIds = collect($combination->specialization_ids ?? [])->map(fn($id) => (int) $id)->all();
        $connectedSpecializations = $specializations->whereIn('id', $selectedSpecializationIds);
        $isIntegratedCombination = $integratedProgramIds->contains((int) ($combination->student_program_id ?? 0));
        @endphp
        <div class="col-12 col-md-6 col-xl-4">
          <div class="h-100" style="border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; background: #fff; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);">
            <div class="d-flex justify-content-between align-items-start gradient-green p-2">
              <div>
                <div style="font-size: 12px; color: rgba(255, 255, 255, 0.8); font-weight: 600;">Combination #{{ $loop->iteration }}</div>
                <div style="font-size: 14px; color: #fff; font-weight: 700; margin-top: 3px;">{{ $combination->studentprograminfo->code ?? '-' }}</div>
              </div>
              <span class="badge badge-light">ID: {{ $combination->id ?? '-' }}</span>
            </div>

            <div style="padding: 16px;">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                  <a href="{{ $isIntegratedCombination
                    ? route('department.integrated.program.student.mappings', ['combinationId' => $combination->id])
                    : route('department.show.student.list', ['program_id' => $combination->studentprograminfo->id, 'slug' => $combination->studentprograminfo->name, 'batch_id' => $combination->batchmaster->id]) }}" style="font-size: 16px; font-weight: 700; color: #111827; text-decoration: none;">
                    {{ $combination->studentprograminfo->name ?? '-' }}
                  </a>
                  <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">{{ $combination->batchmaster->batch_name ?? '-' }} | {{ $combination->shift ?? '-' }}</div>
                </div>
                <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 10px; border-radius: 8px;">{{ $combination->program_type ?? '-' }}</span>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-4">
                  <div style="background: #f8fafc; border-radius: 10px; padding: 10px;">
                    <div style="font-size: 11px; color: #6b7280;">Seats</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 700;">{{ $combination->total_seats ?? '-' }}</div>
                  </div>
                </div>
                <div class="col-4">
                  <div style="background: #f8fafc; border-radius: 10px; padding: 10px;">
                    <div style="font-size: 11px; color: #6b7280;">Available</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 700;">{{ $combination->total_available_seats ?? '-' }}</div>
                  </div>
                </div>
                <div class="col-4">
                  <div style="background: #f8fafc; border-radius: 10px; padding: 10px;">
                    <div style="font-size: 11px; color: #6b7280;">Enrolled</div>
                    <div style="font-size: 16px; color: #0f172a; font-weight: 700;">{{ $combination->studentmaster_count }}</div>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div style="font-size: 12px; color: #6b7280; font-weight: 600;">Specializations</div>
                  @if(!$isIntegratedCombination)
                  <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSpecialization{{ $combination->id }}" title="Connect Specializations">
                    <i class="fa fa-plus-circle"></i>
                  </button>
                  @endif
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  @if($isIntegratedCombination)
                  <span class="badge badge-info">Managed in sublayer programs</span>
                  @else
                  @forelse($connectedSpecializations as $specialization)
                  <span class="badge badge-warning">{{ $specialization->name }}</span>
                  @empty
                  <span class="badge badge-dark">No specialization</span>
                  @endforelse
                  @endif
                </div>
              </div>

              <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(!$isIntegratedCombination)
                <a href="{{ route('curriculam.builder.engine', [$combination->id, $combination->studentprograminfo->code]) }}" class="btn btn-sm btn-dark">
                  <i class="fas fa-drafting-compass me-1"></i>Curriculam Engine
                </a>
                @endif

                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{ $combination->id }}">
                  <i class="fa fa-edit me-1"></i>Edit
                </button>

                <form action="{{ route('department.combination.delete', $combination->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this combination?');" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm" style="background: #fee; color: #dc2626; border: none; border-radius: 8px; padding: 6px 12px;">
                    <i class="fas fa-trash me-1"></i>Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        @if(!$isIntegratedCombination)
        <div class="modal fade" id="addSpecialization{{ $combination->id }}" tabindex="-1" aria-labelledby="addSpecializationLabel{{ $combination->id }}" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="{{ route('department.combination.specializations.update', $combination->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                  <h5 class="modal-title" id="addSpecializationLabel{{ $combination->id }}">Connect Specializations - {{ $combination->studentprograminfo->code ?? '' }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <label class="form-label">Select Specializations</label>
                  <select name="specialization_ids[]" class="select-multiple" multiple size="8">
                    @foreach($specializations as $specialization)
                    <option value="{{ $specialization->id }}" {{ in_array((int) $specialization->id, $selectedSpecializationIds, true) ? 'selected' : '' }}>
                      {{ $specialization->name }}
                    </option>
                    @endforeach
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Save specializations</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endif

        <div class="modal fade" id="edit{{ $combination->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $combination->id }}" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $combination->id }}">Edit {{ $combination->batchmaster->batch_name ?? '-' }} - {{ $combination->studentprograminfo->name ?? '' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form action="{{ route('department.combination.update', $combination->id) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="mb-3">
                    <label for="totalSeats{{ $combination->id }}" class="form-label">Total Seats</label>
                    <input type="number" class="form-control" id="totalSeats{{ $combination->id }}" name="total_seats" value="{{ $combination->total_seats ?? '' }}">
                  </div>
                  <div class="mb-3">
                    <label for="shiftId{{ $combination->id }}" class="form-label">Shift</label>
                    <select class="form-select" id="shiftId{{ $combination->id }}" name="shift_id">
                      <option value="">-- Select Shift --</option>
                      @foreach($shiftMasters as $shift)
                      <option value="{{ $shift->slug }}" {{ $combination->shift  == $shift->slug ? 'selected' : '' }}>
                        {{ $shift->title }}
                      </option>
                      @endforeach
                    </select>
                  </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
              </div>
              </form>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <div class="text-center" style="padding: 40px; color: #6b7280; border: 1px dashed #e5e7eb; border-radius: 16px;">
            <i class="fas fa-inbox fa-3x mb-3" style="color: #e5e7eb;"></i>
            <p>No combinations found.</p>
          </div>
        </div>
        @endforelse
      </div>
      @else
      <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x mb-3" style="color: #e5e7eb;"></i>
        <p style="color: #6b7280;">No combinations found.</p>
      </div>
      @endif
    </div>
  </div>

  <!-- Modals -->
  <!-- Add Program Modal -->
  <div class="modal fade" id="programConnect" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);">
        <div class="modal-header" style="padding: 18px 20px; border-bottom: 1px solid #eef2f7;">
          <h5 class="modal-title" style="color: #111827; font-weight: 700;" id="exampleModalLabel">Connect Programs</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('add.programs.to.subject')}}" method="post">
          @csrf
          <div class="modal-body" style="padding: 20px;">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="programConnectBatch" class="form-label" style="color: #111827; font-weight: 600;">Batch</label>
                <select name="batch_id" id="programConnectBatch" class="form-select" style="border-radius: 10px; border: 1px solid #dbe3ee;">
                  <option value="">--Select--</option>
                  @foreach ($batches as $batch)
                  <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label for="programConnectCampus" class="form-label" style="color: #111827; font-weight: 600;">Campus</label>
                <select name="campus_id" id="programConnectCampus" class="form-select" style="border-radius: 10px; border: 1px solid #dbe3ee;" required>
                  <option value="">-- Select Campus --</option>
                  @foreach ($campuses as $campus)
                  <option value="{{ $campus->id }}" {{ (int) ($data->campus_id ?? 0) === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label for="program" class="form-label" style="color: #111827; font-weight: 600;">Program Type</label>
                <select name="program_type" id="programConnectProgramType" class="form-select" style="border-radius: 10px; border: 1px solid #dbe3ee;" required>
                  <option value="">-- Select --</option>
                  @foreach ($mainStreams as $ms)
                  <option value="{{ $ms->title }}">{{ $ms->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label for="programConnectTotalSeats" class="form-label" style="color: #111827; font-weight: 600;">Total Seats</label>
                <input type="number" id="programConnectTotalSeats" name="total_seats" class="form-control" style="border-radius: 10px; border: 1px solid #dbe3ee;" min="0" required>
              </div>
            </div>

            <div id="programConnectProgramsBlock" class="mt-3 d-none">
              <label for="programConnectPrograms" class="form-label" style="color: #111827; font-weight: 600;">Programs</label>
              <select
                name="programs[]"
                id="programConnectPrograms"
                class="form-select select-multiple"
                style="border-radius: 10px; border: 1px solid #dbe3ee;"
                data-programs-url="{{ route('department.batch.enrolled-programs') }}"
                data-subject-id="{{ $data->id }}"
                multiple>
              </select>
            </div>

            <div id="programConnectProgramsHint" class="small mt-3" style="color: #64748b;"></div>


            <input type="hidden" name="subject_id" value="{{$data->id}}">
          </div>
          <div class="modal-footer" style="padding: 14px 20px; border-top: 1px solid #eef2f7;">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Connect</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const programModal = document.getElementById('programConnect');
    const batchSelect = document.getElementById('programConnectBatch');
    const campusSelect = document.getElementById('programConnectCampus');
    const programBlock = document.getElementById('programConnectProgramsBlock');
    const programSelect = document.getElementById('programConnectPrograms');
    const hint = document.getElementById('programConnectProgramsHint');

    if (!batchSelect || !campusSelect || !programSelect) {
      return;
    }

    const endpoint = programSelect.getAttribute('data-programs-url');
    const subjectId = programSelect.getAttribute('data-subject-id');

    const setHint = function(text) {
      if (hint) {
        hint.textContent = text;
      }
    };

    const toggleProgramBlock = function(show) {
      if (!programBlock) {
        return;
      }
      programBlock.classList.toggle('d-none', !show);
    };

    const initProgramSelectUi = function() {
      if (!window.jQuery) {
        return;
      }

      const $select = window.jQuery(programSelect);
      if (typeof window.jQuery.fn.bsMultiSelect === 'function') {
        try {
          $select.bsMultiSelect('Dispose');
        } catch (e) {
          // Ignore if not initialized yet.
        }
        $select.bsMultiSelect();
        return;
      }

      // Fallback for pages using dselect on this control.
      if (typeof window.dselect === 'function' && programSelect.classList.contains('dselect-example')) {
        window.dselect(programSelect, {
          search: true,
          clearable: true,
          maxHeight: '300px',
          size: 'sm',
        });
      }
    };

    const rebuildOptions = function(programs) {
      programSelect.innerHTML = '';
      programs.forEach(function(program) {
        const option = document.createElement('option');
        option.value = program.id;
        option.textContent = [program.code, program.name].filter(Boolean).join(' - ');
        programSelect.appendChild(option);
      });

      initProgramSelectUi();
    };

    const clearPrograms = function(message) {
      rebuildOptions([]);
      toggleProgramBlock(false);
      setHint(message || 'Select batch and campus to load enrolled programs.');
    };

    const loadBatchCampusPrograms = function(batchId, campusId) {
      if (!batchId || !campusId || !endpoint || !subjectId) {
        clearPrograms('Select batch and campus to load enrolled programs.');
        return;
      }

      setHint('Loading enrolled programs...');
      fetch(endpoint + '?batch_id=' + encodeURIComponent(batchId) + '&campus_id=' + encodeURIComponent(campusId) + '&subject_id=' + encodeURIComponent(subjectId), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(function(response) {
          if (!response.ok) {
            throw new Error('Failed to fetch programs');
          }
          return response.json();
        })
        .then(function(payload) {
          const programs = Array.isArray(payload.programs) ? payload.programs : [];
          rebuildOptions(programs);
          if (programs.length === 0) {
            clearPrograms('No enrolled programs found for this batch in this subject campus.');
          } else {
            toggleProgramBlock(true);
            setHint(programs.length + ' enrolled program(s) available.');
          }
        })
        .catch(function() {
          rebuildOptions([]);
          toggleProgramBlock(false);
          setHint('Could not load programs. Please refresh and try again.');
        });
    };

    const refreshPrograms = function() {
      loadBatchCampusPrograms(batchSelect.value, campusSelect.value);
    };

    batchSelect.addEventListener('change', function() {
      refreshPrograms();
    });

    campusSelect.addEventListener('change', function() {
      refreshPrograms();
    });

    if (batchSelect.value && campusSelect.value) {
      refreshPrograms();
    } else {
      clearPrograms('Select batch and campus to load enrolled programs.');
    }

    if (programModal) {
      programModal.addEventListener('shown.bs.modal', function() {
        if (!programBlock.classList.contains('d-none')) {
          initProgramSelectUi();
        }
      });
    }
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
      return;
    }

    const courseWise = JSON.parse('{!! addslashes(json_encode($courseWiseAttendance)) !!}');
    const dateWise = JSON.parse('{!! addslashes(json_encode($dateWiseAttendance)) !!}');

    const courseChartCanvas = document.getElementById('courseWiseAttendanceChart');
    if (courseChartCanvas && Array.isArray(courseWise) && courseWise.length > 0) {
      new Chart(courseChartCanvas, {
        type: 'bar',
        data: {
          labels: courseWise.map(function(item) {
            return item.course_label;
          }),
          datasets: [{
            label: 'Attendance %',
            data: courseWise.map(function(item) {
              return item.attendance_percentage;
            }),
            backgroundColor: 'rgba(14, 165, 233, 0.45)',
            borderColor: 'rgba(2, 132, 199, 1)',
            borderWidth: 1.5,
            borderRadius: 8,
            maxBarThickness: 32,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: function(value) {
                  return value + '%';
                },
              },
            },
          },
          plugins: {
            legend: {
              display: true,
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.parsed.y + '%';
                },
              },
            },
          },
        },
      });
    }

    const dateChartCanvas = document.getElementById('dateWiseAttendanceChart');
    if (dateChartCanvas && Array.isArray(dateWise) && dateWise.length > 0) {
      new Chart(dateChartCanvas, {
        type: 'line',
        data: {
          labels: dateWise.map(function(item) {
            return item.date;
          }),
          datasets: [{
            label: 'Attendance %',
            data: dateWise.map(function(item) {
              return item.attendance_percentage;
            }),
            fill: true,
            tension: 0.3,
            pointRadius: 3,
            pointBackgroundColor: '#0369a1',
            backgroundColor: 'rgba(14, 165, 233, 0.18)',
            borderColor: '#0369a1',
            borderWidth: 2,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: function(value) {
                  return value + '%';
                },
              },
            },
          },
          plugins: {
            legend: {
              display: true,
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.parsed.y + '%';
                },
              },
            },
          },
        },
      });
    }
  });
</script>

@include('includes.footer')
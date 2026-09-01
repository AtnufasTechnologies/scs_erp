@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h4 class="mb-1" style="font-weight: 700; color: #1e293b;">Student Attendance Details</h4>
        <div style="color: #64748b;">
          {{ $student->roll_no }} | {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
        </div>
      </div>
      <a href="{{ route('department.dashboard', request()->only(['attendance_from', 'attendance_to', 'attendance_batch', 'attendance_course_id'])) }}" class="btn btn-light border">
        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
      </a>
    </div>

    <div class="card mb-3" style="border-radius: 12px; border: 1px solid #e2e8f0;">
      <div class="card-body">
        <form method="GET" action="{{ route('department.student.attendance.details') }}" class="row g-2 align-items-end">
          <input type="hidden" name="id" value="{{ $student->id }}">
          <input type="hidden" name="rollno" value="{{ $student->roll_no }}">

          <div class="col-md-3">
            <label class="form-label" for="attendanceFrom">From Date</label>
            <input id="attendanceFrom" name="attendance_from" type="date" class="form-control" value="{{ $attendanceFrom ?? '' }}">
          </div>

          <div class="col-md-3">
            <label class="form-label" for="attendanceTo">To Date</label>
            <input id="attendanceTo" name="attendance_to" type="date" class="form-control" value="{{ $attendanceTo ?? '' }}">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="attendanceCourseId">Course</label>
            <select id="attendanceCourseId" name="attendance_course_id" class="form-select">
              <option value="">All Courses</option>
              @foreach($attendanceCourses as $course)
              <option value="{{ $course->id }}" {{ (int) ($attendanceCourseId ?? 0) === (int) $course->id ? 'selected' : '' }}>
                {{ trim(($course->course_code ?? '') . ' - ' . ($course->course_title ?? ''), ' -') }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Filter</button>
          </div>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0;">
          <div class="card-body">
            <div style="font-size: 12px; color: #64748b;">Overall Attendance</div>
            <div style="font-size: 30px; font-weight: 700; color: #0f172a;">{{ number_format((float) $overallPercentage, 2) }}%</div>
            <div style="font-size: 13px; color: #64748b;">{{ $attendedRecords }} / {{ $totalRecords }} attended records</div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0;">
          <div class="card-body">
            <div style="font-size: 12px; color: #64748b;">Range</div>
            <div style="font-size: 18px; font-weight: 700; color: #1e293b;">
              @if($hasDateRange && $attendanceFrom && $attendanceTo)
              {{ \Carbon\Carbon::parse($attendanceFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($attendanceTo)->format('d M Y') }}
              @else
              All Time
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3" style="border-radius: 12px; border: 1px solid #e2e8f0;">
      <div class="card-header bg-white" style="font-weight: 700;">Course-wise Summary</div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th>Course</th>
              <th>Present / Total</th>
              <th>Attendance %</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attendanceSummaryByCourse as $row)
            <tr>
              <td>{{ $row['course_label'] }}</td>
              <td>{{ $row['attended_records'] }} / {{ $row['total_records'] }}</td>
              <td>{{ number_format((float) $row['attendance_percentage'], 2) }}%</td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No attendance records found for selected filters.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0;">
      <div class="card-header bg-white" style="font-weight: 700;">Attendance Timeline</div>
      <div class="table-responsive" style="max-height: 420px; overflow: auto;">
        <table class="table table-hover mb-0">
          <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
            <tr>
              <th>Date</th>
              <th>Course</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attendanceTimeline as $item)
            @php
            $status = strtolower((string) ($item->status ?? ''));
            $isPresentLike = in_array($status, ['present', 'late', 'excused'], true);
            @endphp
            <tr>
              <td>{{ \Carbon\Carbon::parse($item->attendance_date)->format('d M Y') }}</td>
              <td>{{ trim(($item->course_code ?? '') . ' - ' . ($item->course_title ?? ''), ' -') ?: 'Unknown Course' }}</td>
              <td>
                <span class="badge {{ $isPresentLike ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($item->status ?? 'NA') }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No attendance entries found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
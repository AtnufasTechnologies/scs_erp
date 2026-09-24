@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')

  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Exam Sessions</h1>
      <p class="fs-6 mb-0 text-dark">Date and start-time wise list of courses with examinations, based on Exam Calendar entries.</p>
    </div>
  </div>

  <div class="container-fluid">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Session Filters</h5>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('admin.seating-allocation.exam-sessions') }}">
          @if(!empty($examId))
          <input type="hidden" name="exam_id" value="{{ (int) $examId }}">
          @endif
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="session_date" class="form-label">Exam Day</label>
              <input type="date" class="form-control" id="session_date" name="session_date" value="{{ $sessionDate }}">
            </div>
            <div class="col-md-4">
              <label for="session_start_time" class="form-label">Start Time</label>
              <input type="time" class="form-control" id="session_start_time" name="session_start_time" value="{{ $sessionStartTime }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-search"></i> Filter
              </button>
              <a href="{{ route('admin.seating-allocation.exam-sessions', !empty($examId) ? ['exam_id' => (int) $examId] : []) }}" class="btn btn-outline-secondary">
                <i class="fa fa-refresh"></i> Reset
              </a>
            </div>
          </div>
        </form>
        @if(!empty($selectedExam))
        <div class="mt-3 alert alert-info mb-0 py-2">
          <strong>Exam Context:</strong> {{ $selectedExam->name }}
        </div>
        @endif
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fa fa-clock-o"></i> Date-Time and Courses</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Courses</th>
              </tr>
            </thead>
            <tbody>
              @forelse($calendarSessions as $session)
              <tr>
                <td>{{ \Carbon\Carbon::parse($session->exam_date)->format('D, d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($session->start_time)->format('h:i A') }}</td>
                <td>
                  @if(!empty($session->end_time))
                  {{ \Carbon\Carbon::parse($session->end_time)->format('h:i A') }}
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-primary mb-2">{{ (int) ($session->course_count ?? 0) }} courses</span>
                  <div>
                    @forelse(($session->courses ?? collect()) as $course)
                    <div class="small">
                      <strong>{{ $course['course_code'] }}</strong> - {{ $course['course_title'] }}
                      <span class="badge bg-secondary ms-1">{{ (int) ($course['student_count'] ?? 0) }} students</span>
                    </div>
                    @empty
                    <span class="text-muted small">No courses mapped.</span>
                    @endforelse
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center py-4 text-muted">No exam sessions found for the selected filter.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')
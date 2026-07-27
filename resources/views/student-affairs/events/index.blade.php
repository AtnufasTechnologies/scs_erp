@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Event Monitoring (Integrated)</h3>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>EC Events</small>
              <h4>{{ $summary['ec_events'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>EC Programs</small>
              <h4>{{ $summary['ec_programs'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Participants</small>
              <h4>{{ $summary['ec_participants'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Dept Activities</small>
              <h4>{{ $summary['department_activities'] }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Department Activities</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Title</th>
                <th>Department</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Participants</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentActivities as $activity)
              <tr>
                <td>{{ $activity->title }}</td>
                <td>{{ $activity->subject->title ?? '-' }}</td>
                <td>{{ optional($activity->activity_date)->format('d-M-Y') }}</td>
                <td>{{ $activity->venue ?? '-' }}</td>
                <td>{{ (int) ($activity->actual_participants ?? 0) }}</td>
                <td>{{ $activity->status }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No departmental activities found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Programs (from Event Module)</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Program</th>
                <th>Event</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Participants</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($programs as $program)
              <tr>
                <td>{{ $program->name }}</td>
                <td>{{ $program->event->title ?? '-' }}</td>
                <td>{{ optional($program->program_date)->format('d-M-Y') }}</td>
                <td>{{ $program->venue ?? '-' }}</td>
                <td>{{ $program->participants_count }}</td>
                <td>{{ $program->status }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No event programs found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
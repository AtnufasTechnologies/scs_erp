@include('includes.header')

<div class="wrapper">
  @include('dean-office.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <h3 class="fw-bold mb-3">Event Overview</h3>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>EC Events</small>
              <h4>{{ $summary['ec_events'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>EC Programs</small>
              <h4>{{ $summary['ec_programs'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>EC Participants</small>
              <h4>{{ $summary['ec_participants'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Department Activities</small>
              <h4>{{ $summary['department_activities'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">Event Programs</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Event</th>
                <th>Program</th>
                <th>Participants</th>
              </tr>
            </thead>
            <tbody>
              @forelse($programs as $row)
              <tr>
                <td>{{ $row->event->title ?? 'N/A' }}</td>
                <td>{{ $row->title ?? 'N/A' }}</td>
                <td>{{ $row->participants_count ?? 0 }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center text-muted">No event programs found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Department Activities</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Activity Date</th>
                <th>Subject</th>
                <th>Activity</th>
                <th>Participants</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentActivities as $row)
              <tr>
                <td>{{ $row->activity_date }}</td>
                <td>{{ $row->subject->title ?? 'N/A' }}</td>
                <td>{{ $row->activity_title ?? 'N/A' }}</td>
                <td>{{ $row->actual_participants ?? 0 }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No department activities found.</td>
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
@include('includes.header')

<div class="wrapper">
  @include('dean-office.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <h3 class="fw-bold mb-3">Event Features Board</h3>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Event Monitoring</small>
              <h4>{{ $summary['ec_events'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Programs</small>
              <h4>{{ $summary['ec_programs'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Participants</small>
              <h4>{{ $summary['ec_participants'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body"><small>Dept Participants</small>
              <h4>{{ $summary['department_participants'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Event Tab Features</div>
        <div class="card-body">
          <ul class="mb-0">
            <li>Event Monitoring Snapshot (institution-wide and department-wise)</li>
            <li>Calendar View for start/end date tracking</li>
            <li>Participation overview to identify high/low engagement departments</li>
            <li>Quick access to Dean Student Affairs event monitoring module</li>
          </ul>
          <div class="mt-3">
            <a href="{{ route('dean.events.index') }}" class="btn btn-primary btn-sm">Open Event Monitoring</a>
            <a href="{{ route('dean.office.events.calendar') }}" class="btn btn-outline-primary btn-sm">Open Event Calendar</a>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
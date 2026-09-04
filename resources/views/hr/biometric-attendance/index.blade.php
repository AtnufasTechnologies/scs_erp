@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Biometric Attendance Logs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Biometric Logs</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-transparent">
        <h5 class="mb-0">Hikvision Attendance Punches</h5>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('hr.biometric-attendance.index') }}" class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Employee No</label>
            <input type="text" class="form-control" name="employee_no" value="{{ $employeeNo }}" placeholder="EMP001">
          </div>
          <div class="col-md-3">
            <label class="form-label">Event Type</label>
            <select name="event_type" class="form-select">
              <option value="">All</option>
              @foreach($eventTypes as $type)
              <option value="{{ $type }}" {{ $eventType === $type ? 'selected' : '' }}>{{ $type }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Employee No</th>
                <th>Punch Time</th>
                <th>Event</th>
                <th>Device</th>
                <th>IP</th>
                <th>Source IP</th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $log)
              <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->employee_no ?: '-' }}</td>
                <td>{{ optional($log->punch_time)->format('d M Y h:i:s A') ?: '-' }}</td>
                <td>{{ $log->event_type ?: '-' }}</td>
                <td>{{ $log->device_name ?: '-' }}</td>
                <td>{{ $log->device_ip ?: '-' }}</td>
                <td>{{ $log->source_ip ?: '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No biometric logs found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $logs->links() }}
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
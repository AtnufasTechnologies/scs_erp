@include('includes.header')

<div class="wrapper">
  @include('dean-office.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <h3 class="fw-bold mb-3">Event Calendar</h3>

      <div class="card shadow-sm">
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th>Event</th>
                <th>Program</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Participants</th>
              </tr>
            </thead>
            <tbody>
              @forelse($calendarRows as $row)
              <tr>
                <td>{{ $row['event_title'] }}</td>
                <td>{{ $row['program_title'] }}</td>
                <td>{{ $row['start_date'] }}</td>
                <td>{{ $row['end_date'] }}</td>
                <td>{{ $row['participants_count'] }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No calendar records found.</td>
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
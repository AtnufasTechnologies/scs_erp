@include('includes.header')

<div class="wrapper">
  @include('dean-office.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Department Activity Intelligence</h3>
        <a href="{{ route('dean.office.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">Filters</div>
        <div class="card-body">
          <form method="GET" action="{{ route('dean.office.department.activities') }}" class="row g-2">
            <div class="col-md-2">
              <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['planned' => 'Planned', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <select name="subject_id" class="form-select">
                <option value="0">All Subjects</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject['id'] }}" {{ (int) ($filters['subject_id'] ?? 0) === (int) $subject['id'] ? 'selected' : '' }}>{{ $subject['title'] }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2">
              <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach($types as $type)
                <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <input name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Search title, venue, organizer, remarks">
            </div>

            <div class="col-md-2 d-grid">
              <button class="btn btn-primary">Apply Filters</button>
            </div>
          </form>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <small class="text-muted">Total Activities</small>
              <h4 class="mb-0">{{ $summary['total'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <small class="text-muted">Planned / Ongoing / Completed</small>
              <h5 class="mb-0">{{ $summary['planned'] ?? 0 }} / {{ $summary['ongoing'] ?? 0 }} / {{ $summary['completed'] ?? 0 }}</h5>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <small class="text-muted">Cancelled</small>
              <h4 class="mb-0">{{ $summary['cancelled'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <small class="text-muted">Participants</small>
              <h4 class="mb-0">{{ $summary['participants'] ?? 0 }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <small class="text-muted">Budget vs Expense</small>
              <h6 class="mb-0">{{ number_format((float) ($summary['budget'] ?? 0), 2) }} / {{ number_format((float) ($summary['expense'] ?? 0), 2) }}</h6>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header fw-semibold">Department Activity Details</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Activity Date</th>
                <th>Subject</th>
                <th>Title</th>
                <th>Type</th>
                <th>Venue / Time</th>
                <th>Organizer</th>
                <th>Participants</th>
                <th>Budget / Expense</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Attachments</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
              @php
              $attachments = is_array($row->attachments ?? null) ? $row->attachments : [];
              @endphp
              <tr>
                <td>{{ optional($row->activity_date)->format('d M Y') ?? $row->activity_date }}</td>
                <td>{{ $row->subject->title ?? 'N/A' }}</td>
                <td>
                  <div class="fw-semibold">{{ $row->title ?? 'N/A' }}</div>
                  @if(!empty($row->description))
                  <small class="text-muted">{{ $row->description }}</small>
                  @endif
                </td>
                <td>{{ $row->activity_type ?? '-' }}</td>
                <td>
                  <div>{{ $row->venue ?? '-' }}</div>
                  <small class="text-muted">{{ $row->start_time ?? '-' }} to {{ $row->end_time ?? '-' }}</small>
                </td>
                <td>
                  <div>{{ $row->organizer_name ?? '-' }}</div>
                  <small class="text-muted">{{ $row->organizer_email ?? '-' }}</small><br>
                  <small class="text-muted">{{ $row->organizer_phone ?? '-' }}</small>
                </td>
                <td>{{ (int) ($row->actual_participants ?? 0) }} / {{ (int) ($row->expected_participants ?? 0) }}</td>
                <td>{{ number_format((float) ($row->budget ?? 0), 2) }} / {{ number_format((float) ($row->actual_expense ?? 0), 2) }}</td>
                <td><span class="badge bg-{{ $row->status_badge }}">{{ ucfirst((string) ($row->status ?? 'n/a')) }}</span></td>
                <td>{{ $row->remarks ?? '-' }}</td>
                <td>{{ count($attachments) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="11" class="text-center text-muted">No departmental activities found for the selected filters.</td>
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
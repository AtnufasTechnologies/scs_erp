@include('includes.header')

<div class="wrapper">
  @include('iqac.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Event Controller Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('iqac.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Review Queue</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white">
        <h6 class="mb-0">Filter Reports</h6>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('iqac.event-controller-reports.index') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="">All</option>
              @foreach(['pending', 'approved', 'rejected'] as $status)
              <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Submitted On</label>
            <input type="date" name="submitted_on" class="form-control" value="{{ request('submitted_on') }}">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a href="{{ route('iqac.event-controller-reports.index') }}" class="btn btn-light">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white">
        <h6 class="mb-0">Event Snapshot</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Event</th>
                <th>Dates</th>
                <th>Venue</th>
                <th>Status</th>
                <th>Report Count</th>
                <th>Latest Report Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($events as $event)
              @php
              $latest = $event->iqacReports->first();
              $latestStatus = strtolower((string) ($latest->approval_status ?? 'pending'));
              @endphp
              <tr>
                <td>{{ $event->title }}</td>
                <td>{{ optional($event->start_date)->format('d M Y') }} - {{ optional($event->end_date)->format('d M Y') }}</td>
                <td>{{ $event->venue ?? '-' }}</td>
                <td>{{ ucfirst((string) $event->status) }}</td>
                <td>{{ $event->iqacReports->count() }}</td>
                <td>
                  @if($latest)
                  <span class="badge bg-{{ $latestStatus === 'approved' ? 'success' : ($latestStatus === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($latestStatus) }}</span>
                  @else
                  <span class="badge bg-secondary">No Report Yet</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No events found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">{{ $events->links('vendor.pagination.bootstrap-5') }}</div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <h6 class="mb-0">Report Review List</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Submitted</th>
                <th>Event</th>
                <th>Report</th>
                <th>File</th>
                <th>Status</th>
                <th>Review</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reports as $report)
              <tr>
                <td>{{ optional($report->submitted_on)->format('d M Y') }}</td>
                <td>
                  <div class="fw-semibold">{{ optional($report->event)->title ?? '-' }}</div>
                  <div class="small text-muted">{{ optional(optional($report->event)->start_date)->format('d M Y') }} - {{ optional(optional($report->event)->end_date)->format('d M Y') }}</div>
                  <div class="small text-muted">{{ optional($report->event)->venue ?? '-' }}</div>
                </td>
                <td>
                  <div>{{ $report->report_title ?: '-' }}</div>
                  <div class="small text-muted">{{ $report->submission_note ?: '-' }}</div>
                </td>
                <td>
                  @if($report->report_file_path)
                  <a href="{{ asset('storage/' . $report->report_file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Open</a>
                  @else
                  -
                  @endif
                </td>
                <td>
                  @php $status = strtolower((string) ($report->approval_status ?? 'pending')); @endphp
                  <span class="badge bg-{{ $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($status) }}</span>
                </td>
                <td style="min-width: 320px;">
                  <form method="POST" action="{{ route('iqac.event-controller-reports.status', $report->id) }}" class="row g-2">
                    @csrf
                    <div class="col-5">
                      <select name="approval_status" class="form-select form-select-sm" required>
                        <option value="pending" {{ ($report->approval_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ ($report->approval_status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ ($report->approval_status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                      </select>
                    </div>
                    <div class="col-7">
                      <input type="text" name="review_remarks" class="form-control form-control-sm" value="{{ $report->review_remarks }}" placeholder="Review remarks (required for reject)">
                    </div>
                    <div class="col-12">
                      <button class="btn btn-sm btn-primary" type="submit">Update Review</button>
                    </div>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No reports found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">{{ $reports->links('vendor.pagination.bootstrap-5') }}</div>
    </div>
  </main>
</div>

@include('includes.footer')
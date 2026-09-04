@include('includes.header')

<div class="wrapper">
  @include('iqac.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">IQAC Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">International Office Events</div>
            <div class="display-6 fw-bold text-primary">{{ $eventCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Total IQAC Reports</div>
            <div class="display-6 fw-bold text-dark">{{ $reportCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Pending</div>
            <div class="display-6 fw-bold text-warning">{{ $pendingCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Approved</div>
            <div class="display-6 fw-bold text-success">{{ $approvedCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Rejected</div>
            <div class="display-6 fw-bold text-danger">{{ $rejectedCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Department Activities</div>
            <div class="display-6 fw-bold text-info">{{ $departmentActivityCount }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="small text-muted">Dept Activities Pending Review</div>
            <div class="display-6 fw-bold text-warning">{{ $departmentPendingCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Pending Reports from International Office</h6>
        <a href="{{ route('iqac.international-office.reports') }}" class="btn btn-sm btn-outline-primary">Open Review Queue</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Submitted On</th>
                <th>Event Type</th>
                <th>Institution</th>
                <th>Title</th>
                <th>File</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentPendingReports as $report)
              <tr>
                <td>{{ optional($report->submitted_on)->format('d M Y') }}</td>
                <td>{{ optional(optional($report->event)->activityType)->title ?? '-' }}</td>
                <td>{{ optional($report->event)->visiting_institution_name ?? '-' }}</td>
                <td>{{ $report->report_title ?: '-' }}</td>
                <td>
                  @if($report->report_file_path)
                  <a href="{{ asset('storage/' . $report->report_file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                  @else
                  -
                  @endif
                </td>
                <td><span class="badge bg-warning">Pending</span></td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No pending reports at the moment.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Pending Departmental Activities</h6>
        <a href="{{ route('iqac.departmental-activities.index') }}" class="btn btn-sm btn-outline-primary">Open Activities Queue</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Department</th>
                <th>Activity</th>
                <th>Type</th>
                <th>Status</th>
                <th>Report</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentDepartmentPendingActivities as $activity)
              <tr>
                <td>{{ optional($activity->activity_date)->format('d M Y') }}</td>
                <td>{{ optional($activity->subject)->title ?? '-' }}</td>
                <td>{{ $activity->title }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', (string) $activity->activity_type)) }}</td>
                <td><span class="badge bg-warning">Pending</span></td>
                <td>
                  @if(!empty($activity->report_file))
                  <a href="{{ asset('storage/' . $activity->report_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Open</a>
                  @else
                  -
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">No pending departmental activities at the moment.</td>
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
@include('includes.header')

<div class="wrapper">
  @include('iqac.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Departmental Activities</div>
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
        <h6 class="mb-0">Filter Activities</h6>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('iqac.departmental-activities.index') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Department</label>
            <select name="subject_id" class="form-select">
              <option value="">All Departments</option>
              @foreach($subjectOptions as $subject)
              <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->title }}{{ $subject->code ? ' (' . $subject->code . ')' : '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Activity Type</label>
            <input type="text" name="activity_type" class="form-control" value="{{ request('activity_type') }}" placeholder="seminar/workshop">
          </div>
          <div class="col-md-2">
            <label class="form-label">Activity Date</label>
            <input type="date" name="activity_date" class="form-control" value="{{ request('activity_date') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">IQAC Status</label>
            <select name="iqac_status" class="form-select">
              <option value="">All</option>
              @foreach(['pending','approved','rejected'] as $status)
              <option value="{{ $status }}" {{ request('iqac_status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a href="{{ route('iqac.departmental-activities.index') }}" class="btn btn-light">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <h6 class="mb-0">Departmental Activities Review List</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Department</th>
                <th>Activity</th>
                <th>Operational Status</th>
                <th>IQAC Status</th>
                <th>Report</th>
                <th>Review</th>
              </tr>
            </thead>
            <tbody>
              @forelse($activities as $activity)
              @php
              $iqacStatus = strtolower((string) ($activity->iqac_approval_status ?? 'pending'));
              @endphp
              <tr>
                <td>{{ optional($activity->activity_date)->format('d M Y') }}</td>
                <td>{{ optional($activity->subject)->title ?? '-' }}</td>
                <td>
                  <div class="fw-semibold">{{ $activity->title }}</div>
                  <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', (string) $activity->activity_type)) }}</div>
                  <div class="small text-muted">Venue: {{ $activity->venue ?: '-' }}</div>
                </td>
                <td>
                  <span class="badge bg-{{ $activity->status_badge }}">{{ ucfirst((string) $activity->status) }}</span>
                </td>
                <td>
                  <span class="badge bg-{{ $iqacStatus === 'approved' ? 'success' : ($iqacStatus === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($iqacStatus) }}</span>
                </td>
                <td>
                  @if(!empty($activity->report_file))
                  <a href="{{ asset('storage/' . $activity->report_file) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Open</a>
                  @else
                  -
                  @endif
                </td>
                <td style="min-width: 320px;">
                  <form method="POST" action="{{ route('iqac.departmental-activities.status', $activity->id) }}" class="row g-2">
                    @csrf
                    <div class="col-5">
                      <select name="iqac_approval_status" class="form-select form-select-sm" required>
                        <option value="pending" {{ ($activity->iqac_approval_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ ($activity->iqac_approval_status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ ($activity->iqac_approval_status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                      </select>
                    </div>
                    <div class="col-7">
                      <input type="text" name="iqac_review_remarks" class="form-control form-control-sm" value="{{ $activity->iqac_review_remarks }}" placeholder="Review remarks (required for reject)">
                    </div>
                    <div class="col-12">
                      <button class="btn btn-sm btn-primary" type="submit">Update Review</button>
                    </div>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">No departmental activities found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">{{ $activities->links('vendor.pagination.bootstrap-5') }}</div>
    </div>
  </main>
</div>

@include('includes.footer')
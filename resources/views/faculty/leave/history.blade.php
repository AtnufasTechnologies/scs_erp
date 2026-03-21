@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Leave History</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.leave.index') }}">Leave Applications</a></li>
            <li class="breadcrumb-item active" aria-current="page">History</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('faculty.leave.index') }}" class="btn btn-primary">
          <i class="fas fa-arrow-left me-2"></i>Current Session Leaves
        </a>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- Session Filter -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <form method="GET" action="{{ route('faculty.leave.history') }}" class="row align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-bold">Select Academic Session</label>
                <select name="session_id" class="form-select" onchange="this.form.submit()">
                  <option value="">-- Select Session --</option>
                  @foreach($sessions as $session)
                  <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>
                    {{ $session->title }}
                  </option>
                  @endforeach
                </select>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    @if(!empty($stats))
    <!-- Session Statistics -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold">
              <i class="fas fa-chart-bar me-2 text-primary"></i>
              Leave Summary for {{ $stats['session_title'] }}
            </h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="text-center p-3 border-end">
                  <h4 class="fw-bold text-info mb-2">{{ $stats['total'] }}</h4>
                  <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Applications</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center p-3 border-end">
                  <h4 class="fw-bold text-success mb-2">{{ $stats['approved'] }}</h4>
                  <p class="text-muted mb-0" style="font-size: 0.9rem;">Approved</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center p-3 border-end">
                  <h4 class="fw-bold text-danger mb-2">{{ $stats['rejected'] }}</h4>
                  <p class="text-muted mb-0" style="font-size: 0.9rem;">Rejected</p>
                </div>
              </div>
              <div class="col-md-3">
                <div class="text-center p-3">
                  <h4 class="fw-bold text-primary mb-2">{{ $stats['total_days_taken'] }}</h4>
                  <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Days Taken</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leave Breakdown by Type -->
    @if(!empty($stats['leave_breakdown']) && $stats['leave_breakdown']->count() > 0)
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold">
              <i class="fas fa-layer-group me-2 text-primary"></i>
              Leave Breakdown by Type
            </h6>
          </div>
          <div class="card-body">
            <div class="row">
              @foreach($stats['leave_breakdown'] as $breakdown)
              <div class="col-md-4 mb-3">
                <div class="border rounded p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold">{{ $breakdown['name'] }}</span>
                    <span class="badge bg-{{ $breakdown['badge_color'] }}">{{ $breakdown['code'] }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted" style="font-size: 0.9rem;">Days Taken:</span>
                    <span class="fw-bold">{{ $breakdown['days_taken'] }}</span>
                  </div>
                  @if($breakdown['allowed'])
                  <div class="d-flex justify-content-between">
                    <span class="text-muted" style="font-size: 0.9rem;">Allowed:</span>
                    <span>{{ $breakdown['allowed'] }}</span>
                  </div>
                  <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-{{ $breakdown['badge_color'] }}"
                      style="width: {{ min(100, ($breakdown['days_taken'] / $breakdown['allowed']) * 100) }}%">
                    </div>
                  </div>
                  @else
                  <div class="text-muted" style="font-size: 0.85rem;">Unlimited</div>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    @endif

    <!-- Leave Applications Table -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-transparent border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">
            <i class="fas fa-history me-2 text-primary"></i>Leave Application History
          </h6>
        </div>
      </div>
      <div class="card-body">
        @if($leaveApplications->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Session</th>
                <th>Leave Type</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leaveApplications as $application)
              <tr>
                <td>
                  @if($application->annualSession)
                  <span class="badge bg-secondary">{{ $application->annualSession->title }}</span>
                  @else
                  <span class="text-muted">Not Set</span>
                  @endif
                </td>
                <td>
                  <span class="badge bg-{{ $application->leave_type_badge }}">
                    {{ $application->leave_type_name }}
                  </span>
                </td>
                <td>
                  <small>
                    {{ $application->start_date->format('d M Y') }} -
                    {{ $application->end_date->format('d M Y') }}
                  </small>
                </td>
                <td>
                  <span class="fw-bold">{{ $application->total_days }}</span> days
                </td>
                <td>
                  <small>{{ Str::limit($application->reason, 40) }}</small>
                </td>
                <td>
                  <span class="badge bg-{{ $application->status_badge }}">
                    {{ ucfirst($application->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('faculty.leave.show', $application->id) }}"
                    class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $leaveApplications->appends(['session_id' => $selectedSessionId])->links() }}
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">No leave applications found for the selected session.</p>
          @if(!$selectedSessionId)
          <p class="text-muted">Please select a session to view history.</p>
          @endif
        </div>
        @endif
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
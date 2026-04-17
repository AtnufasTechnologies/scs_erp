@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Leave Applications</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">My Leaves</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
      <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 h-100 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase d-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;"><i class="fas fa-clock text-warning"></i> Pending</p>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending'] }}</h3>
                <small class="text-muted mt-1 d-block">Awaiting approval</small>
              </div>
              <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock text-warning"></i>
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="progress" style="height: 4px;">
              @php $pendingPercent = $stats['total'] > 0 ? ($stats['pending'] / $stats['total']) * 100 : 0; @endphp
              <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercent }}%"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 h-100 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase d-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;"><i class="fas fa-check-circle text-success"></i> Approved</p>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['approved'] }}</h3>
                <small class="text-muted mt-1 d-block">This session</small>
              </div>
              <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="progress" style="height: 4px;">
              @php $approvedPercent = $stats['total'] > 0 ? ($stats['approved'] / $stats['total']) * 100 : 0; @endphp
              <div class="progress-bar bg-success" role="progressbar" style="width: {{ $approvedPercent }}%"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 h-100 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase d-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;"><i class="fas fa-times-circle text-danger"></i> Rejected</p>
                <h3 class="mb-0 fw-bold text-danger">{{ $stats['rejected'] }}</h3>
                <small class="text-muted mt-1 d-block">Not approved</small>
              </div>
              <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-times-circle text-danger" style="font-size: 2.5rem;"></i>
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="progress" style="height: 4px;">
              @php $rejectedPercent = $stats['total'] > 0 ? ($stats['rejected'] / $stats['total']) * 100 : 0; @endphp
              <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $rejectedPercent }}%"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 h-100 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase d-flex align-items-center gap-1" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;"><i class="fas fa-file-alt text-primary"></i> Total</p>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total'] }}</h3>
                <small class="text-muted mt-1 d-block">All applications</small>
              </div>
              <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-alt text-primary" style="font-size: 2.5rem;"></i>
              </div>
            </div>
          </div>
          <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="progress" style="height: 4px;">
              <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
      .hover-lift {
        transition: all 0.3s ease;
      }

      .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
      }
    </style>

    <!-- Leave Balance -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 fw-bold">
                <i class="fas fa-calendar-check me-2"></i>Leave Balance (Current Session)
              </h6>
              <span class="badge bg-white text-primary">{{ date('Y') }}</span>
            </div>
          </div>
          <div class="card-body p-4">
            <div class="row g-4">
              <!-- Casual Leave -->
              <div class="col-md-4">
                <div class="leave-card p-4 border-start border-primary border-4 bg-light rounded-3 h-100">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-umbrella-beach text-primary" style="font-size: 1.25rem;"></i>
                        <h6 class="mb-0 fw-bold text-primary">Casual Leave</h6>
                      </div>
                      <small class="text-muted">CL</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                      {{ max(0, 10 - $casualLeaves) }} left
                    </span>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-muted small">Used</span>
                      <span class="fw-bold text-primary">{{ $casualLeaves }} / 10 days</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px;">
                      <div class="progress-bar bg-primary" role="progressbar"
                        style="width: {{ min(100, ($casualLeaves / 10) * 100) }}%; border-radius: 10px;">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      <i class="fas fa-circle" style="font-size: 6px;"></i>
                      {{ number_format((($casualLeaves / 10) * 100), 0) }}% utilized
                    </small>
                    @if($casualLeaves >= 10)
                    <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Limit reached</small>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Sick Leave -->
              <div class="col-md-4">
                <div class="leave-card p-4 border-start border-danger border-4 bg-light rounded-3 h-100">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-notes-medical text-danger" style="font-size: 1.25rem;"></i>
                        <h6 class="mb-0 fw-bold text-danger">Sick Leave</h6>
                      </div>
                      <small class="text-muted">SL</small>
                    </div>
                    <span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                      Unlimited
                    </span>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-muted small">Used</span>
                      <span class="fw-bold text-danger">{{ $sickLeaves }} days</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px;">
                      <div class="progress-bar bg-danger bg-gradient" role="progressbar"
                        style="width: {{ min(100, ($sickLeaves / 15) * 100) }}%; border-radius: 10px;">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      <i class="fas fa-circle" style="font-size: 6px;"></i>
                      No limit applicable
                    </small>
                  </div>
                </div>
              </div>

              <!-- Earned Leave -->
              <div class="col-md-4">
                <div class="leave-card p-4 border-start border-success border-4 bg-light rounded-3 h-100">
                  <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-award text-success" style="font-size: 1.25rem;"></i>
                        <h6 class="mb-0 fw-bold text-success">Earned Leave</h6>
                      </div>
                      <small class="text-muted">EL</small>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-2" style="font-size: 0.85rem;">
                      {{ max(0, 25 - $earnedLeaves) }} left
                    </span>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="text-muted small">Used</span>
                      <span class="fw-bold text-success">{{ $earnedLeaves }} / 25 days</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px;">
                      <div class="progress-bar bg-success" role="progressbar"
                        style="width: {{ min(100, ($earnedLeaves / 25) * 100) }}%; border-radius: 10px;">
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      <i class="fas fa-circle" style="font-size: 6px;"></i>
                      {{ number_format((($earnedLeaves / 25) * 100), 0) }}% utilized
                    </small>
                    @if($earnedLeaves >= 25)
                    <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Limit reached</small>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
      .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }

      .leave-card {
        transition: all 0.3s ease;
        border-radius: 12px !important;
      }

      .leave-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
      }
    </style>

    <!-- Leave Applications List -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>My Leave Applications (Current Session)</h6>
              </div>
              <div class="col-auto">
                <a href="{{ route('faculty.leave.history') }}" class="btn btn-secondary btn-sm me-2">
                  <i class="fas fa-history me-1"></i>View History
                </a>
                <a href="{{ route('faculty.leave.create') }}" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus me-1"></i>Apply for Leave
                </a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-3" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link {{ $filter == 'all' ? 'active' : '' }}" href="{{ route('faculty.leave.index', ['filter' => 'all']) }}">
                  All
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link {{ $filter == 'pending' ? 'active' : '' }}" href="{{ route('faculty.leave.index', ['filter' => 'pending']) }}">
                  Pending
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link {{ $filter == 'approved' ? 'active' : '' }}" href="{{ route('faculty.leave.index', ['filter' => 'approved']) }}">
                  Approved
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link {{ $filter == 'rejected' ? 'active' : '' }}" href="{{ route('faculty.leave.index', ['filter' => 'rejected']) }}">
                  Rejected
                </a>
              </li>
            </ul>

            <!-- Applications Table -->
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Applied On</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($leaveApplications as $leave)
                  <tr>
                    <td>
                      <span class="badge bg-{{ $leave->leave_type_badge }}">
                        {{ ucfirst($leave->leave_type) }}
                      </span>
                    </td>
                    <td>
                      <small class="d-block">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</small>
                      <small class="text-muted">to {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</small>
                    </td>
                    <td><strong>{{ $leave->total_days }}</strong> day(s)</td>
                    <td>
                      <small>{{ \Str::limit($leave->reason, 40) }}</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $leave->status_badge }}">
                        {{ ucfirst($leave->status) }}
                      </span>
                      @if($leave->dept_action === 'forwarded')
                      <br><small class="badge bg-info mt-1">Forwarded to {{ $leave->forwarded_to }}</small>
                      @elseif($leave->dept_action === 'rejected')
                      <br><small class="badge bg-danger mt-1">Dept Rejected</small>
                      @endif
                    </td>
                    <td>
                      <small>{{ $leave->created_at->format('d M Y') }}</small>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <a href="{{ route('faculty.leave.show', $leave->id) }}" class="btn btn-outline-primary btn-sm" title="View">
                          <i class="fas fa-eye"></i>
                        </a>
                        @if($leave->status == 'pending')
                        <a href="{{ route('faculty.leave.edit', $leave->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('faculty.leave.cancel', $leave->id) }}" method="POST" class="d-inline">
                          @csrf
                          <button type="submit" class="btn btn-outline-danger btn-sm" title="Cancel"
                            onclick="return confirm('Are you sure you want to cancel this application?')">
                            <i class="fas fa-times"></i>
                          </button>
                        </form>
                        @endif
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <i class="fas fa-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                      <p class="text-muted mt-3 mb-0">No leave applications found</p>
                      <a href="{{ route('faculty.leave.create') }}" class="btn btn-sm btn-primary mt-2">
                        Apply for Leave
                      </a>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            @if($leaveApplications->hasPages())
            <div class="d-flex justify-content-center mt-3">
              {{ $leaveApplications->links() }}
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </main>
  <!--end page content-->
</div>

@include('includes.footer')
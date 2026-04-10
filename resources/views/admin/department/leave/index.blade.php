@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid">

    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1a1a1a;">
              <i class="fas fa-clipboard-check me-2" style="color: #5b4cdb;"></i>Faculty Leave Sanction
            </h3>
            <p class="text-muted mb-0">Review and take action on faculty leave applications</p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('department.leave.categories') }}" class="btn btn-outline-primary">
              <i class="fas fa-tags me-1"></i>Leave Categories
            </a>
            <a href="{{ route('department.dashboard') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back
            </a>
          </div>
        </div>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
      <div class="col-md col-sm-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
          <div class="card-body text-center py-4">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #5b4cdb22, #7c3aed22); border-radius: 16px;">
              <i class="fas fa-file-alt" style="font-size: 2.5rem; color: #5b4cdb;"></i>
            </div>
            <h3 class="mb-1 fw-bold">{{ $stats['total'] }}</h3>
            <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Total Applications</small>
          </div>
        </div>
      </div>
      <div class="col-md col-sm-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
          <div class="card-body text-center py-4">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f59e0b22, #d9770622); border-radius: 16px;">
              <i class="fas fa-hourglass-half" style="font-size: 2.5rem; color: #f59e0b;"></i>
            </div>
            <h3 class="mb-1 fw-bold text-warning">{{ $stats['pending'] }}</h3>
            <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Pending Review</small>
          </div>
        </div>
      </div>
      <div class="col-md col-sm-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
          <div class="card-body text-center py-4">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #06b6d422, #0891b222); border-radius: 16px;">
              <i class="fas fa-paper-plane" style="font-size: 2.5rem; color: #06b6d4;"></i>
            </div>
            <h3 class="mb-1 fw-bold text-info">{{ $stats['forwarded'] }}</h3>
            <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Forwarded</small>
          </div>
        </div>
      </div>
      <div class="col-md col-sm-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
          <div class="card-body text-center py-4">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ef444422, #dc262622); border-radius: 16px;">
              <i class="fas fa-ban" style="font-size: 2.5rem; color: #ef4444;"></i>
            </div>
            <h3 class="mb-1 fw-bold text-danger">{{ $stats['dept_rejected'] }}</h3>
            <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Rejected</small>
          </div>
        </div>
      </div>
      <div class="col-md col-sm-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
          <div class="card-body text-center py-4">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #10b98122, #05966922); border-radius: 16px;">
              <i class="fas fa-check-double" style="font-size: 2.5rem; color: #10b981;"></i>
            </div>
            <h3 class="mb-1 fw-bold text-success">{{ $stats['approved'] }}</h3>
            <small class="text-muted fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Approved</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
      <div class="card-body py-3">
        <form method="GET" action="{{ route('department.leave.index') }}" class="d-flex align-items-center gap-3 flex-wrap">
          <label class="fw-semibold mb-0" style="color: #6b7280;">
            <i class="fas fa-filter me-1"></i>Filters:
          </label>
          <select name="status" class="form-select" style="width: 180px; border-radius: 10px;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
            <option value="forwarded" {{ request('status') == 'forwarded' ? 'selected' : '' }}>Forwarded</option>
            <option value="dept_rejected" {{ request('status') == 'dept_rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          </select>
          <select name="leave_type" class="form-select" style="width: 180px; border-radius: 10px;" onchange="this.form.submit()">
            <option value="">All Leave Types</option>
            @foreach($leaveTypes as $lt)
            <option value="{{ $lt->id }}" {{ request('leave_type') == $lt->id ? 'selected' : '' }}>{{ $lt->leave_type_name }}</option>
            @endforeach
          </select>
          @if(request('status') || request('leave_type'))
          <a href="{{ route('department.leave.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
            <i class="fas fa-times me-1"></i>Clear
          </a>
          @endif
        </form>
      </div>
    </div>

    <!-- Applications Table -->
    <div class="card shadow-sm" style="border-radius: 16px; border: none;">
      <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
        <h5 class="mb-0" style="font-weight: 700;">
          <i class="fas fa-list me-2" style="color: #5b4cdb;"></i>Leave Applications
          <span class="badge ms-2" style="background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); font-size: 13px; border-radius: 8px;">
            {{ $applications->total() }}
          </span>
        </h5>
      </div>
      <div class="card-body p-0">
        @if($applications->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background: #f9fafb;">
              <tr>
                <th style="padding: 14px 16px; color: #6b7280; font-weight: 600; font-size: 13px;">#</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Faculty</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Leave Type</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Period</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Days</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Applied On</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Status</th>
                <th style="color: #6b7280; font-weight: 600; font-size: 13px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($applications as $index => $app)
              <tr>
                <td style="padding: 14px 16px;">{{ $applications->firstItem() + $index }}</td>
                <td>
                  <div class="fw-semibold">{{ $app->faculty->FIRST_NAME ?? '' }} {{ $app->faculty->LAST_NAME ?? '' }}</div>
                  <small class="text-muted">{{ $app->faculty->USER_CODE ?? '' }}</small>
                </td>
                <td>
                  <span class="badge bg-{{ $app->leaveMaster->badge_color ?? 'secondary' }}">
                    {{ $app->leaveMaster->leave_type_name ?? ucfirst($app->leave_type) }}
                  </span>
                </td>
                <td>
                  <div>{{ $app->start_date->format('d M Y') }}</div>
                  <small class="text-muted">to {{ $app->end_date->format('d M Y') }}</small>
                </td>
                <td><span class="fw-semibold">{{ $app->total_days }}</span></td>
                <td>{{ $app->created_at->format('d M Y') }}</td>
                <td>
                  @if($app->dept_action === 'forwarded')
                  <span class="badge bg-info">Forwarded to {{ $app->forwarded_to }}</span>
                  @elseif($app->dept_action === 'rejected')
                  <span class="badge bg-danger">Rejected by Dept</span>
                  @elseif($app->status === 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($app->status === 'cancelled')
                  <span class="badge bg-secondary">Cancelled</span>
                  @else
                  <span class="badge bg-warning text-dark">Pending Review</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('department.leave.show', $app->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    @if($app->status === 'pending' && !$app->dept_action)
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Reject"
                      data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}">
                      <i class="fas fa-times"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" title="Forward"
                      data-bs-toggle="modal" data-bs-target="#forwardModal{{ $app->id }}">
                      <i class="fas fa-share"></i>
                    </button>
                    @endif
                  </div>
                </td>
              </tr>

              {{-- Reject Modal --}}
              @if($app->status === 'pending' && !$app->dept_action)
              <div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0">
                      <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject Leave Application</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('department.leave.reject', $app->id) }}" method="POST">
                      @csrf
                      <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                          <strong>{{ $app->faculty->FIRST_NAME ?? '' }} {{ $app->faculty->LAST_NAME ?? '' }}</strong><br>
                          {{ $app->leaveMaster->leave_type_name ?? ucfirst($app->leave_type) }} &bull;
                          {{ $app->start_date->format('d M') }} - {{ $app->end_date->format('d M Y') }}
                          ({{ $app->total_days }} days)
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                          <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejecting this leave..."></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Additional Remarks</label>
                          <textarea name="admin_remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                        </div>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              {{-- Forward Modal --}}
              <div class="modal fade" id="forwardModal{{ $app->id }}" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0">
                      <h5 class="modal-title"><i class="fas fa-share text-info me-2"></i>Forward Leave Application</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('department.leave.forward', $app->id) }}" method="POST">
                      @csrf
                      <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                          <strong>{{ $app->faculty->FIRST_NAME ?? '' }} {{ $app->faculty->LAST_NAME ?? '' }}</strong><br>
                          {{ $app->leaveMaster->leave_type_name ?? ucfirst($app->leave_type) }} &bull;
                          {{ $app->start_date->format('d M') }} - {{ $app->end_date->format('d M Y') }}
                          ({{ $app->total_days }} days)
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Forward To <span class="text-danger">*</span></label>
                          <select name="forwarded_to" class="form-select" required>
                            <option value="">Select Authority</option>
                            <option value="DeanOfStudentStudies">Dean of Student Studies</option>
                            <option value="DCOE">DCOE</option>
                            <option value="HR">HR</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label fw-semibold">Remarks</label>
                          <textarea name="forwarded_remarks" class="form-control" rows="3" placeholder="Optional remarks for the authority..."></textarea>
                        </div>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white"><i class="fas fa-share me-1"></i>Forward</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endif
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="p-3">
          {{ $applications->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">No leave applications found</p>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>

@include('includes.footer')
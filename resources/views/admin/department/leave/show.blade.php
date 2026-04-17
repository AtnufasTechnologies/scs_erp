@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid">

    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1a1a1a;">
              <i class="fas fa-file-alt me-2" style="color: #5b4cdb;"></i>Leave Application Detail
            </h3>
          </div>
          <a href="{{ route('department.leave.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
          </a>
        </div>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
      <div class="col-lg-8">
        <!-- Application Details Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
          <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-bold">Application Details</h5>
              @if($application->dept_action === 'forwarded')
              <span class="badge bg-info fs-6">Forwarded to {{ $application->forwarded_to }}</span>
              @elseif($application->dept_action === 'rejected')
              <span class="badge bg-danger fs-6">Rejected by Dept</span>
              @elseif($application->status === 'approved')
              <span class="badge bg-success fs-6">Approved</span>
              @elseif($application->status === 'cancelled')
              <span class="badge bg-secondary fs-6">Cancelled</span>
              @else
              <span class="badge bg-warning text-dark fs-6">Pending Review</span>
              @endif
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">Faculty Name</label>
                <p class="fw-semibold mb-0">{{ $application->faculty->FIRST_NAME ?? '' }} {{ $application->faculty->MIDDLE_NAME ?? '' }} {{ $application->faculty->LAST_NAME ?? '' }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">Faculty Code</label>
                <p class="fw-semibold mb-0">{{ $application->faculty->USER_CODE ?? 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">Leave Type</label>
                <p class="mb-0">
                  <span class="badge bg-{{ $application->leaveMaster->badge_color ?? 'secondary' }}">
                    {{ $application->leaveMaster->leave_type_name ?? ucfirst($application->leave_type) }}
                  </span>
                </p>
              </div>
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">Total Days</label>
                <p class="fw-bold fs-5 mb-0">{{ $application->total_days }} day(s)</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">Start Date</label>
                <p class="mb-0">{{ $application->start_date->format('d M Y (l)') }}</p>
              </div>
              <div class="col-md-6">
                <label class="text-muted small fw-semibold">End Date</label>
                <p class="mb-0">{{ $application->end_date->format('d M Y (l)') }}</p>
              </div>
              <div class="col-12">
                <label class="text-muted small fw-semibold">Reason</label>
                <p class="mb-0" style="white-space: pre-line;">{{ $application->reason }}</p>
              </div>
              @if($application->contact_during_leave)
              <div class="col-12">
                <label class="text-muted small fw-semibold">Contact During Leave</label>
                <p class="mb-0">{{ $application->contact_during_leave }}</p>
              </div>
              @endif
              @if($application->attachment)
              <div class="col-12">
                <label class="text-muted small fw-semibold">Attachment</label>
                <p class="mb-0"><a href="{{ $application->attachment }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-paperclip me-1"></i>View Attachment</a></p>
              </div>
              @endif
              <div class="col-12">
                <label class="text-muted small fw-semibold">Applied On</label>
                <p class="mb-0">{{ $application->created_at->format('d M Y, h:i A') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Dept Action History -->
        @if($application->dept_action)
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
          <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-muted"></i>Department Action</h5>
          </div>
          <div class="card-body">
            @if($application->dept_action === 'forwarded')
            <div class="alert alert-info border-0">
              <i class="fas fa-share me-2"></i>
              <strong>Forwarded to {{ $application->forwarded_to }}</strong>
              @if($application->dept_action_at) on {{ $application->dept_action_at->format('d M Y, h:i A') }} @endif
            </div>
            @if($application->forwarded_remarks)
            <div class="mt-2">
              <label class="text-muted small fw-semibold">Forwarding Remarks</label>
              <p class="mb-0">{{ $application->forwarded_remarks }}</p>
            </div>
            @endif
            @elseif($application->dept_action === 'rejected')
            <div class="alert alert-danger border-0">
              <i class="fas fa-times-circle me-2"></i>
              <strong>Rejected by Department</strong>
              @if($application->dept_action_at) on {{ $application->dept_action_at->format('d M Y, h:i A') }} @endif
            </div>
            @if($application->rejection_reason)
            <div class="mt-2">
              <label class="text-muted small fw-semibold">Rejection Reason</label>
              <p class="mb-0">{{ $application->rejection_reason }}</p>
            </div>
            @endif
            @if($application->admin_remarks)
            <div class="mt-2">
              <label class="text-muted small fw-semibold">Admin Remarks</label>
              <p class="mb-0">{{ $application->admin_remarks }}</p>
            </div>
            @endif
            @endif
          </div>
        </div>
        @endif
      </div>

      <!-- Action Panel -->
      <div class="col-lg-4">
        @if($application->status === 'pending' && !$application->dept_action)
        <!-- Reject Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
          <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
            <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-times-circle me-2"></i>Reject Application</h6>
          </div>
          <div class="card-body">
            <form action="{{ route('department.leave.reject', $application->id) }}" method="POST">
              @csrf
              <div class="mb-3">
                <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Reason for rejection..."></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Remarks</label>
                <textarea name="admin_remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
              </div>
              <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this application?')">
                <i class="fas fa-times me-1"></i>Reject
              </button>
            </form>
          </div>
        </div>

        <!-- Forward Card -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
          <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
            <h6 class="mb-0 fw-bold text-info"><i class="fas fa-share me-2"></i>Forward Application</h6>
          </div>
          <div class="card-body">
            <form action="{{ route('department.leave.forward', $application->id) }}" method="POST">
              @csrf
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
                <textarea name="forwarded_remarks" class="form-control" rows="3" placeholder="Optional remarks..."></textarea>
              </div>
              <button type="submit" class="btn btn-info text-white w-100" onclick="return confirm('Forward this application?')">
                <i class="fas fa-share me-1"></i>Forward
              </button>
            </form>
          </div>
        </div>
        @else
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
          <div class="card-body text-center py-4">
            @if($application->dept_action === 'forwarded')
            <i class="fas fa-share-square text-info" style="font-size: 2.5rem;"></i>
            <p class="mt-3 fw-semibold text-info">Forwarded to {{ $application->forwarded_to }}</p>
            @elseif($application->dept_action === 'rejected')
            <i class="fas fa-times-circle text-danger" style="font-size: 2.5rem;"></i>
            <p class="mt-3 fw-semibold text-danger">Rejected by Department</p>
            @elseif($application->status === 'approved')
            <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
            <p class="mt-3 fw-semibold text-success">Already Approved</p>
            @else
            <i class="fas fa-ban text-secondary" style="font-size: 2.5rem;"></i>
            <p class="mt-3 fw-semibold text-secondary">{{ ucfirst($application->status) }}</p>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>

@include('includes.footer')
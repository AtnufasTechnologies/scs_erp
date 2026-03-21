@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Leave Details</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.leave.index') }}">Leaves</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Leave Application Details</h5>
              <span class="badge bg-{{ $leaveApplication->status_badge }} fs-6">
                {{ ucfirst($leaveApplication->status) }}
              </span>
            </div>
          </div>
          <div class="card-body p-4">
            <div class="row mb-4">
              <div class="col-md-6">
                <h6 class="text-muted mb-2">Leave Type</h6>
                <span class="badge bg-{{ $leaveApplication->leave_type_badge }} fs-6">
                  {{ ucfirst($leaveApplication->leave_type) }} Leave
                </span>
              </div>
              <div class="col-md-6">
                <h6 class="text-muted mb-2">Application Date</h6>
                <p class="mb-0"><strong>{{ $leaveApplication->created_at->format('d M Y, h:i A') }}</strong></p>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-4">
                <h6 class="text-muted mb-2">Start Date</h6>
                <p class="mb-0"><strong>{{ \Carbon\Carbon::parse($leaveApplication->start_date)->format('d M Y') }}</strong></p>
              </div>
              <div class="col-md-4">
                <h6 class="text-muted mb-2">End Date</h6>
                <p class="mb-0"><strong>{{ \Carbon\Carbon::parse($leaveApplication->end_date)->format('d M Y') }}</strong></p>
              </div>
              <div class="col-md-4">
                <h6 class="text-muted mb-2">Total Days</h6>
                <p class="mb-0"><strong class="text-primary">{{ $leaveApplication->total_days }} day(s)</strong></p>
              </div>
            </div>

            @if($leaveApplication->contact_during_leave)
            <div class="mb-4">
              <h6 class="text-muted mb-2">Contact During Leave</h6>
              <p class="mb-0">{{ $leaveApplication->contact_during_leave }}</p>
            </div>
            @endif

            <div class="mb-4">
              <h6 class="text-muted mb-2">Reason for Leave</h6>
              <p class="mb-0">{{ $leaveApplication->reason }}</p>
            </div>

            @if($leaveApplication->attachment)
            <div class="mb-4">
              <h6 class="text-muted mb-2">Attachment</h6>
              <a href="{{ $leaveApplication->attachment }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-1"></i>Download Attachment
              </a>
            </div>
            @endif

            @if($leaveApplication->status == 'approved')
            <div class="alert alert-success">
              <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Approved</h6>
              @if($leaveApplication->approved_at)
              <p class="mb-1">Approved on: <strong>{{ $leaveApplication->approved_at->format('d M Y, h:i A') }}</strong></p>
              @endif
              @if($leaveApplication->approver)
              <p class="mb-1">Approved by: <strong>{{ $leaveApplication->approver->name }}</strong></p>
              @endif
              @if($leaveApplication->admin_remarks)
              <hr>
              <p class="mb-0"><strong>Remarks:</strong> {{ $leaveApplication->admin_remarks }}</p>
              @endif
            </div>
            @elseif($leaveApplication->status == 'rejected')
            <div class="alert alert-danger">
              <h6 class="alert-heading"><i class="fas fa-times-circle me-2"></i>Rejected</h6>
              @if($leaveApplication->approved_at)
              <p class="mb-1">Rejected on: <strong>{{ $leaveApplication->approved_at->format('d M Y, h:i A') }}</strong></p>
              @endif
              @if($leaveApplication->approver)
              <p class="mb-1">Rejected by: <strong>{{ $leaveApplication->approver->name }}</strong></p>
              @endif
              @if($leaveApplication->rejection_reason)
              <hr>
              <p class="mb-0"><strong>Reason:</strong> {{ $leaveApplication->rejection_reason }}</p>
              @endif
            </div>
            @elseif($leaveApplication->status == 'pending')
            <div class="alert alert-warning">
              <i class="fas fa-clock me-2"></i>
              This application is pending approval from the administration.
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-between mt-4">
              <a href="{{ route('faculty.leave.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
              <div class="d-flex gap-2">
                @if($leaveApplication->status == 'pending')
                <a href="{{ route('faculty.leave.edit', $leaveApplication->id) }}" class="btn btn-primary">
                  <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form action="{{ route('faculty.leave.cancel', $leaveApplication->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to cancel this application?')">
                    <i class="fas fa-times me-1"></i>Cancel Application
                  </button>
                </form>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!--end page content-->
</div>

@include('includes.footer')
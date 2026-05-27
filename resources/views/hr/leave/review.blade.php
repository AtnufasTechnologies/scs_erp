@include('includes.header')

@include('hr.sidebar')

<!--start main wrapper-->
<main class="page-content">
  <!--start breadcrumb-->
  <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
    <div class="breadcrumb-title pe-3">Leave Management</div>
    <div class="ps-2">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
          <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Leave Applications</a></li>
          <li class="breadcrumb-item active" aria-current="page">Review Application</li>
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

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <!-- Application Details Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Leave Application Details</h5>
            </div>
            <div class="col-md-6 text-end">
              <a href="{{ route('hr.leave.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th style="width: 40%;">Faculty Name:</th>
                  <td><strong>{{ $application->faculty->FIRST_NAME ?? '' }} {{ $application->faculty->LAST_NAME ?? '' }}</strong></td>
                </tr>
                <tr>
                  <th>Employee Code:</th>
                  <td>{{ $application->faculty->USER_CODE ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Department:</th>
                  <td><span class="badge bg-secondary">{{ $application->faculty->department->name ?? '-' }}</span></td>
                </tr>
                <tr>
                  <th>Leave Type:</th>
                  <td><span class="badge bg-info">{{ $application->leaveMaster->leave_type_name ?? ucfirst($application->leave_type) }}</span></td>
                </tr>
                <tr>
                  <th>Applied Date:</th>
                  <td><strong>{{ $application->created_at ? $application->created_at->format('d M Y, h:i A') : '-' }}</strong></td>
                </tr>
                <tr>
                  <th>From Date:</th>
                  <td>{{ $application->start_date ? $application->start_date->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                  <th>To Date:</th>
                  <td>{{ $application->end_date ? $application->end_date->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                  <th>Number of Days:</th>
                  <td><span class="badge bg-dark">{{ $application->total_days }} day{{ $application->total_days > 1 ? 's' : '' }}</span></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th style="width: 40%;">Status:</th>
                  <td>
                    @if($application->status === 'pending')
                    <span class="badge bg-warning">Pending</span>
                    @elseif($application->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @elseif($application->status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Academic Session:</th>
                  <td>{{ $application->created_at->format('Y') }}</td>
                </tr>
                <tr>
                  <th>Email:</th>
                  <td>{{ $application->faculty->MAIL_ID ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Phone:</th>
                  <td>{{ $application->faculty->MOBILE_NO ?? '-' }}</td>
                </tr>
                @if($application->forwarded_to)
                <tr>
                  <th>Forwarded To:</th>
                  <td><span class="badge bg-primary">{{ $application->forwarded_to }}</span></td>
                </tr>
                @endif
                @if($application->forwarded_at)
                <tr>
                  <th>Forwarded Date:</th>
                  <td>{{ $application->forwarded_at->format('d M Y, h:i A') }}</td>
                </tr>
                @endif
                @if($application->dept_action)
                <tr>
                  <th>Dept Action:</th>
                  <td><span class="badge bg-info">{{ ucfirst($application->dept_action) }}</span></td>
                </tr>
                @endif
              </table>
            </div>
          </div>

          <!-- Principal/VP Response Section -->
          @if($application->approved_by)
          <div class="row mt-3">
            <div class="col-12">
              <div class="alert alert-{{ $application->status === 'approved' ? 'success' : 'danger' }} border-0">
                <h6 class="alert-heading">
                  <i class="fas fa-{{ $application->status === 'approved' ? 'check-circle' : 'times-circle' }} me-2"></i>
                  Authority Response
                </h6>
                <hr>
                <div class="row">
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Action By:</strong> {{ $application->approver->name ?? 'Authority' }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ $application->approved_at ? $application->approved_at->format('d M Y, h:i A') : '-' }}</p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-1"><strong>Decision:</strong>
                      <span class="badge bg-{{ $application->status === 'approved' ? 'success' : 'danger' }}">
                        {{ ucfirst($application->status) }}
                      </span>
                    </p>
                  </div>
                </div>
                @if($application->admin_remarks)
                <hr>
                <p class="mb-0"><strong>Remarks:</strong> {{ $application->admin_remarks }}</p>
                @endif
                @if($application->rejection_reason)
                <hr>
                <p class="mb-0"><strong>Rejection Reason:</strong> {{ $application->rejection_reason }}</p>
                @endif
              </div>
            </div>
          </div>
          @endif

          <div class="row mt-3">
            <div class="col-12">
              <h6 class="mb-2">Reason for Leave:</h6>
              <div class="alert alert-light">
                {{ $application->reason }}
              </div>
            </div>
          </div>

          @if($application->forwarded_remarks)
          <div class="row mt-3">
            <div class="col-12">
              <h6 class="mb-2">Forwarding Remarks:</h6>
              <div class="alert alert-info">
                {{ $application->forwarded_remarks }}
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Change Leave Type Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-warning">
        <div class="card-header bg-warning text-dark">
          <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Change Leave Type</h6>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-3">Update the leave type if the application was submitted under wrong category.</p>
          <form action="{{ route('hr.leave.change-type', $application->id) }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
              <label class="form-label">Select New Leave Type <span class="text-danger">*</span></label>
              <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                <option value="">-- Select Leave Type --</option>
                @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}" {{ $application->leave_type_id == $type->id ? 'selected' : '' }}>
                  {{ $type->leave_type_name }}
                  @if($type->allowed_days_per_year)
                  ({{ $type->allowed_days_per_year }} days/year)
                  @endif
                </option>
                @endforeach
              </select>
              @error('leave_type_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to change the leave type?')">
                <i class="fas fa-sync me-1"></i>Update Leave Type
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Cards -->
  <div class="row">
    <!-- Approve Card -->
    <div class="col-md-4 mb-4">
      <div class="card h-100 border-success">
        <div class="card-header bg-success text-white">
          <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Approve Application</h6>
        </div>
        <div class="card-body">
          <p class="text-muted small">Special Rights to Approve this leave application with optional remarks.</p>
          <form action="{{ route('hr.leave.approve', $application->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Remarks (Optional)</label>
              <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Add any remarks..."></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this leave application?')">
              <i class="fas fa-check me-1"></i>Approve Leave
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Reject Card -->
    <div class="col-md-4 mb-4">
      <div class="card h-100 border-danger">
        <div class="card-header bg-danger text-white">
          <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Reject Application</h6>
        </div>
        <div class="card-body">
          <p class="text-muted small">Reject this leave application with a reason.</p>
          <form action="{{ route('hr.leave.reject', $application->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
              <textarea name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="2" placeholder="Specify reason for rejection..." required></textarea>
              @error('rejection_reason')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this leave application?')">
              <i class="fas fa-times me-1"></i>Reject Leave
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Forward Card -->
    <div class="col-md-4 mb-4">
      <div class="card h-100 border-info">
        <div class="card-header bg-info text-white">
          <h6 class="mb-0"><i class="fas fa-share me-2"></i>Forward Application</h6>
        </div>
        <div class="card-body">
          <p class="text-muted small">Forward this leave application to higher authority.</p>
          <form action="{{ route('hr.leave.forward', $application->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Forward To <span class="text-danger">*</span></label>
              <select name="forwarded_to" class="form-select @error('forwarded_to') is-invalid @enderror" required>
                <option value="">Select Authority</option>
                <option value="Principal">Principal | Vice Principal</option>

              </select>
              @error('forwarded_to')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks (Optional)</label>
              <textarea name="forwarded_remarks" class="form-control" rows="2" placeholder="Add forwarding remarks..."></textarea>
            </div>
            <button type="submit" class="btn btn-info w-100" onclick="return confirm('Are you sure you want to forward this leave application?')">
              <i class="fas fa-share me-1"></i>Forward Application
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</main>
<!--end main wrapper-->

@include('includes.footer')
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
                  <td><strong>{{ $application->faculty->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                  <th>Employee Code:</th>
                  <td>{{ $application->faculty->faculty_code ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Leave Type:</th>
                  <td><span class="badge bg-info">{{ $application->leaveMaster->name ?? '-' }}</span></td>
                </tr>
                <tr>
                  <th>From Date:</th>
                  <td>{{ date('d M Y', strtotime($application->from_date)) }}</td>
                </tr>
                <tr>
                  <th>To Date:</th>
                  <td>{{ date('d M Y', strtotime($application->to_date)) }}</td>
                </tr>
                <tr>
                  <th>Number of Days:</th>
                  <td><span class="badge bg-secondary">{{ $application->days }} days</span></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th style="width: 40%;">Status:</th>
                  <td><span class="badge bg-warning">{{ ucfirst($application->status) }}</span></td>
                </tr>
                <tr>
                  <th>Academic Session:</th>
                  <td>{{ $application->annualSession->session_name ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Applied Date:</th>
                  <td>{{ date('d M Y, h:i A', strtotime($application->created_at)) }}</td>
                </tr>
                <tr>
                  <th>Email:</th>
                  <td>{{ $application->faculty->email ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Phone:</th>
                  <td>{{ $application->faculty->phone ?? '-' }}</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <h6 class="mb-2">Reason for Leave:</h6>
              <div class="alert alert-light">
                {{ $application->reason }}
              </div>
            </div>
          </div>
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
          <p class="text-muted small">Approve this leave application with optional remarks.</p>
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
            <div class="mb-3">
              <label class="form-label">Additional Remarks (Optional)</label>
              <textarea name="admin_remarks" class="form-control" rows="2" placeholder="Add any remarks..."></textarea>
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
                <option value="Principal">Principal</option>
                <option value="DeanOfStudentStudies">Dean of Student Studies</option>
                <option value="DCOE">Deputy COE</option>
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
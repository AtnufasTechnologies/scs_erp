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
          <li class="breadcrumb-item active" aria-current="page">View Details</li>
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

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-transparent">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h5 class="mb-0">Leave Application Details</h5>
            </div>
            <div class="col-md-6 text-end">
              <a href="{{ route('hr.leave.index') }}" class="btn btn-secondary">
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
                  <td>{{ $application->faculty->name ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Employee Code:</th>
                  <td>{{ $application->faculty->faculty_code ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Leave Type:</th>
                  <td>{{ $application->leaveMaster->name ?? '-' }}</td>
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
                  <td><span class="badge bg-info">{{ $application->days }} days</span></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <th style="width: 40%;">Status:</th>
                  <td>
                    @if($application->status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                    @elseif($application->status == 'approved')
                    <span class="badge bg-success">Approved</span>
                    @elseif($application->status == 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                    @elseif($application->status == 'forwarded')
                    <span class="badge bg-info">Forwarded to Principal</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Academic Session:</th>
                  <td>{{ $application->annualSession->session_name ?? '-' }}</td>
                </tr>
                <tr>
                  <th>Applied Date:</th>
                  <td>{{ date('d M Y, h:i A', strtotime($application->created_at)) }}</td>
                </tr>
                @if($application->approved_by)
                <tr>
                  <th>Reviewed By:</th>
                  <td>{{ $application->approver->name ?? '-' }}</td>
                </tr>
                @endif
                @if($application->approved_at)
                <tr>
                  <th>Reviewed Date:</th>
                  <td>{{ date('d M Y, h:i A', strtotime($application->approved_at)) }}</td>
                </tr>
                @endif
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

          @if($application->remarks)
          <div class="row mt-3">
            <div class="col-12">
              <h6 class="mb-2">Remarks:</h6>
              <div class="alert alert-secondary">
                {{ $application->remarks }}
              </div>
            </div>
          </div>
          @endif

          @if($application->status == 'pending')
          <div class="row mt-4">
            <div class="col-12">
              <a href="{{ route('hr.leave.review', $application->id) }}" class="btn btn-primary">
                <i class="fas fa-check-circle me-1"></i>Review Application
              </a>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</main>
<!--end main wrapper-->

@include('includes.footer')
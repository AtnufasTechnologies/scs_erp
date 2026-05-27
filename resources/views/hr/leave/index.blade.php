@include('includes.header')

<div class="wrapper">
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
            <li class="breadcrumb-item active" aria-current="page">Leave Applications</li>
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

    <div class="card">
      <div class="card-header bg-transparent">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0">Faculty Leave Applications</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.leave.statistics') }}" class="btn btn-outline-primary">
              <i class="fas fa-chart-bar me-1"></i>Statistics
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('hr.leave.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-3">
              <input type="text" name="search" class="form-control" placeholder="Search faculty name" value="{{ $search }}">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="leave_type" class="form-select">
                <option value="">All Leave Types</option>
                @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}" {{ $leaveType == $type->id ? 'selected' : '' }}>
                  {{ $type->leave_type_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
          </div>
        </form>

        <!-- Leave Applications Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Faculty</th>
                <th>Leave Type</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Status</th>
                <th>Applied On</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($leaveApplications as $application)
              <tr>
                <td>
                  <strong>{{ $application->faculty->FIRST_NAME ?? 'N/A' }} {{ $application->faculty->LAST_NAME ?? '' }}</strong><br>
                  <small class="text-muted">{{ $application->faculty->USER_CODE ?? '' }}</small>
                </td>
                <td>
                  <span class="badge badge-success">
                    {{ $application->leaveMaster->leave_type_name ?? $application->leave_type }}
                  </span>
                </td>
                <td>
                  <small>{{ $application->start_date->format('d M Y') }}</small><br>
                  <small class="text-muted">to {{ $application->end_date->format('d M Y') }}</small>
                </td>
                <td><strong>{{ $application->total_days }}</strong></td>
                <td>
                  @if($application->status == 'pending')
                  <span class="badge bg-warning">Pending</span>
                  @elseif($application->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($application->status == 'rejected')
                  <span class="badge bg-danger">Rejected</span>
                  @else
                  <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                  @endif
                </td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>
                  <div class="btn-group btn-group-sm">

                    @if($application->status == 'pending')
                    <a href="{{ route('hr.leave.review', $application->id) }}" class="btn btn-outline-success" title="Review">
                      <i class="fas fa-eye"></i>
                    </a>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No leave applications found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $leaveApplications->links() }}
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Leave Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Leave Applications</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filters --}}
    <div class="card mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('principal.leaves.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
          <select name="campus_id" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $selectedStatus === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>
          <span class="badge bg-dark ms-auto">{{ $leaveApplications->count() }} Applications</span>
        </form>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center border-start border-4 border-warning">
          <div class="card-body py-3">
            <h4 class="mb-0 text-warning">{{ $leaveApplications->where('status', 'pending')->count() }}</h4>
            <small class="text-muted">Pending</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center border-start border-4 border-success">
          <div class="card-body py-3">
            <h4 class="mb-0 text-success">{{ $leaveApplications->where('status', 'approved')->count() }}</h4>
            <small class="text-muted">Approved</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center border-start border-4 border-danger">
          <div class="card-body py-3">
            <h4 class="mb-0 text-danger">{{ $leaveApplications->where('status', 'rejected')->count() }}</h4>
            <small class="text-muted">Rejected</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center border-start border-4 border-primary">
          <div class="card-body py-3">
            <h4 class="mb-0 text-primary">{{ $leaveApplications->count() }}</h4>
            <small class="text-muted">Total</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Leave Applications --}}
    <div class="row mt-3">
      @forelse($leaveApplications as $leave)
      <div class="col-lg-6 col-xl-4 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid {{ $leave->status === 'pending' ? '#ffc107' : ($leave->status === 'approved' ? '#198754' : '#dc3545') }} !important;">
          <div class="card-body">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-0 text-capitalize">
                  {{ $leave->faculty ? $leave->faculty->FIRST_NAME . ' ' . $leave->faculty->LAST_NAME : 'Unknown' }}
                </h6>
                <small class="text-muted">
                  {{ $leave->faculty ? $leave->faculty->USER_CODE : '' }}
                  @if($leave->faculty && $leave->faculty->department_info)
                  | {{ $leave->faculty->department_info->name }}
                  @endif
                </small>
              </div>
              <span class="badge bg-{{ $leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : 'danger') }}">
                {{ ucfirst($leave->status) }}
              </span>
            </div>

            {{-- Leave Details --}}
            <div class="mb-3" style="font-size: 0.85rem;">
              <div class="row g-2">
                <div class="col-6">
                  <small class="text-muted d-block">Leave Type</small>
                  <span class="badge bg-secondary">{{ $leave->leaveMaster ? $leave->leaveMaster->leave_type_name : ucfirst($leave->leave_type) }}</span>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">Duration</small>
                  <strong>{{ $leave->total_days }} day{{ $leave->total_days > 1 ? 's' : '' }}</strong>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">From</small>
                  {{ $leave->start_date ? $leave->start_date->format('d M Y') : '-' }}
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">To</small>
                  {{ $leave->end_date ? $leave->end_date->format('d M Y') : '-' }}
                </div>
              </div>
            </div>

            {{-- Reason --}}
            <div class="mb-3">
              <small class="text-muted d-block">Reason</small>
              <p class="mb-0" style="font-size: 0.85rem;">{{ $leave->reason ?? 'No reason provided' }}</p>
            </div>

            {{-- Admin Remarks (if already actioned) --}}
            @if($leave->admin_remarks)
            <div class="mb-3 p-2 bg-light rounded" style="font-size: 0.85rem;">
              <small class="text-muted d-block">Admin Note</small>
              <span>{{ $leave->admin_remarks }}</span>
            </div>
            @endif

            {{-- Action Buttons for Pending --}}
            @if($leave->status === 'pending')
            <hr class="my-2">
            <form action="{{ route('principal.leaves.action', $leave->id) }}" method="POST">
              @csrf
              <div class="mb-2">
                <textarea name="admin_remarks" class="form-control form-control-sm" rows="2" placeholder="Add a note (optional)..."></textarea>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" name="action" value="approved" class="btn btn-sm btn-success flex-fill">
                  <i class="fas fa-check me-1"></i>Grant
                </button>
                <button type="submit" name="action" value="rejected" class="btn btn-sm btn-danger flex-fill">
                  <i class="fas fa-times me-1"></i>Deny
                </button>
              </div>
            </form>
            @endif
          </div>

          {{-- Card Footer --}}
          <div class="card-footer bg-transparent border-0 pt-0">
            <small class="text-muted">Applied: {{ $leave->created_at ? $leave->created_at->format('d M Y, h:i A') : '-' }}</small>
            @if($leave->approved_at)
            <br><small class="text-muted">Actioned: {{ $leave->approved_at->format('d M Y, h:i A') }}</small>
            @endif
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
            <p class="text-muted">No leave applications found.</p>
          </div>
        </div>
      </div>
      @endforelse
    </div>
  </main>
</div>

@include('includes.footer')
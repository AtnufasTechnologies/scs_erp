@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Remuneration</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Faculty Remuneration</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-money-bill-wave me-2"></i>Faculty Remuneration</h3>
                  <p class="text-white-50 mb-0">Manage duty-based remuneration for faculty members</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <form method="POST" action="{{ route('admin.exam-remuneration.auto-calculate') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-lg me-2" onclick="return confirm('Auto-calculate remuneration from all completed duties?')">
                      <i class="fas fa-calculator me-2"></i>Auto Calculate
                    </button>
                  </form>
                  <a href="{{ route('admin.exam-remuneration.create') }}" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-plus-circle me-2"></i>Add Entry
                  </a>
                  <a href="{{ route('admin.exam-remuneration.export') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-file-excel me-2"></i>Export
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-rupee-sign text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Amount</p>
                  <h4 class="mb-0 fw-bold">{{ number_format($totalAmount, 2) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-clock text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending ({{ $pendingCount }})</p>
                  <h4 class="mb-0 fw-bold">{{ number_format($pendingAmount, 2) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Approved ({{ $approvedCount }})</p>
                  <h4 class="mb-0 fw-bold">{{ number_format($approvedAmount, 2) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-money-check-alt text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Paid ({{ $paidCount }})</p>
                  <h4 class="mb-0 fw-bold">{{ number_format($paidAmount, 2) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-remuneration.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Faculty</label>
              <select name="faculty_id" class="form-select">
                <option value="">All Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                  {{ $faculty->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Duty Type</label>
              <select name="duty_type" class="form-select">
                <option value="">All Types</option>
                <option value="invigilation" {{ request('duty_type') === 'invigilation' ? 'selected' : '' }}>Invigilation</option>
                <option value="evaluation" {{ request('duty_type') === 'evaluation' ? 'selected' : '' }}>Evaluation</option>
                <option value="moderation" {{ request('duty_type') === 'moderation' ? 'selected' : '' }}>Moderation</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.exam-remuneration.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Remuneration Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Faculty</th>
                  <th>Duty Type</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                  <th>Generated At</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($remunerations as $rem)
                <tr>
                  <td>{{ $loop->iteration + ($remunerations->currentPage() - 1) * $remunerations->perPage() }}</td>
                  <td>
                    <div class="fw-semibold">{{ $rem->faculty->name ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $rem->faculty->department ?? '' }}</small>
                  </td>
                  <td>
                    @if($rem->duty_type === 'invigilation')
                    <span class="badge bg-primary">Invigilation</span>
                    @elseif($rem->duty_type === 'evaluation')
                    <span class="badge bg-info">Evaluation</span>
                    @else
                    <span class="badge bg-secondary">Moderation</span>
                    @endif
                  </td>
                  <td>{{ $rem->quantity }}</td>
                  <td>{{ number_format($rem->rate, 2) }}</td>
                  <td class="fw-bold">{{ number_format($rem->total_amount, 2) }}</td>
                  <td>
                    @if($rem->status === 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($rem->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @else
                    <span class="badge bg-info">Paid</span>
                    @endif
                  </td>
                  <td>{{ $rem->generated_at ? $rem->generated_at->format('d M Y') : '-' }}</td>
                  <td class="text-center">
                    <a href="{{ route('admin.exam-remuneration.show', $rem->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    @if($rem->status === 'pending')
                    <form method="POST" action="{{ route('admin.exam-remuneration.approve', $rem->id) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Approve this remuneration?')">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    @endif
                    @if($rem->status === 'approved')
                    <form method="POST" action="{{ route('admin.exam-remuneration.mark-paid', $rem->id) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-info" title="Mark Paid" onclick="return confirm('Mark this as paid?')">
                        <i class="fas fa-money-bill-wave"></i>
                      </button>
                    </form>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No remuneration records found</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($remunerations->hasPages())
        <div class="card-footer bg-white">
          {{ $remunerations->appends(request()->query())->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Payment Batches</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Payment Batches</li>
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
                <div class="col-md-7">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-layer-group me-2"></i>Payment Batches</h3>
                  <p class="text-white-50 mb-0">Group approved remunerations into payment batches and process payments</p>
                </div>
                <div class="col-md-5 text-md-end">
                  @if($availableForBatch > 0)
                  <a href="{{ route('admin.payment-batches.create') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Create Batch
                    <span class="badge bg-success ms-1">{{ $availableForBatch }} approved</span>
                  </a>
                  @else
                  <button class="btn btn-light btn-lg" disabled>
                    <i class="fas fa-plus-circle me-2"></i>No Approved Items
                  </button>
                  @endif
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

      <!-- Summary Cards -->
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
                  <i class="fas fa-file-alt text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Draft</p>
                  <h4 class="mb-0 fw-bold">{{ $draftCount }}</h4>
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
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Approved</p>
                  <h4 class="mb-0 fw-bold">{{ $approvedCount }}</h4>
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
          <form method="GET" action="{{ route('admin.payment-batches.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.payment-batches.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Batches Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Batch Name</th>
                  <th>Items</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($batches as $batch)
                <tr>
                  <td>{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                  <td class="fw-semibold">{{ $batch->batch_name }}</td>
                  <td><span class="badge bg-secondary">{{ $batch->items_count }} items</span></td>
                  <td class="fw-bold">{{ number_format($batch->total_amount, 2) }}</td>
                  <td>
                    @if($batch->status === 'draft')
                    <span class="badge bg-warning text-dark">Draft</span>
                    @elseif($batch->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @else
                    <span class="badge bg-info">Paid</span>
                    @endif
                  </td>
                  <td>{{ $batch->created_at->format('d M Y') }}</td>
                  <td class="text-center">
                    <a href="{{ route('admin.payment-batches.show', $batch->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    @if($batch->status === 'draft')
                    <form method="POST" action="{{ route('admin.payment-batches.approve', $batch->id) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Approve" onclick="return confirm('Approve this batch?')">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    @endif
                    @if($batch->status === 'approved')
                    <form method="POST" action="{{ route('admin.payment-batches.mark-paid', $batch->id) }}" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-info" title="Mark Paid" onclick="return confirm('Mark this batch as paid? This will also mark all included remunerations as paid.')">
                        <i class="fas fa-money-bill-wave"></i>
                      </button>
                    </form>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No payment batches found</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($batches->hasPages())
        <div class="card-footer bg-white">
          {{ $batches->appends(request()->query())->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
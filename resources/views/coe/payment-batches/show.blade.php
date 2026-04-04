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
            <li class="breadcrumb-item"><a href="{{ route('admin.payment-batches.index') }}">Payment Batches</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $batch->batch_name }}</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-2"></i>{{ $batch->batch_name }}</h3>
                  <p class="text-white-50 mb-0">
                    @if($batch->status === 'draft')
                    <span class="badge bg-warning text-dark">Draft</span>
                    @elseif($batch->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @else
                    <span class="badge bg-info">Paid</span>
                    @endif
                    &nbsp; Created {{ $batch->created_at->format('d M Y, h:i A') }}
                  </p>
                </div>
                <div class="col-md-5 text-md-end">
                  @if($batch->status === 'draft')
                  <form method="POST" action="{{ route('admin.payment-batches.approve', $batch->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-2" onclick="return confirm('Approve this payment batch?')">
                      <i class="fas fa-check me-1"></i>Approve Batch
                    </button>
                  </form>
                  @elseif($batch->status === 'approved')
                  <form method="POST" action="{{ route('admin.payment-batches.mark-paid', $batch->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info me-2" onclick="return confirm('Mark this batch as paid? All included remunerations will also be marked as paid.')">
                      <i class="fas fa-money-bill-wave me-1"></i>Mark as Paid
                    </button>
                  </form>
                  @endif
                  <a href="{{ route('admin.payment-batches.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
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

      <!-- Batch Summary -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-rupee-sign text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Amount</p>
                  <h4 class="mb-0 fw-bold">{{ number_format($batch->total_amount, 2) }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-list-ol text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Items</p>
                  <h4 class="mb-0 fw-bold">{{ $batch->items->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-users text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Faculty Members</p>
                  <h4 class="mb-0 fw-bold">{{ $byFaculty->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Items by Faculty -->
      @foreach($byFaculty as $facultyId => $group)
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0 fw-bold">
              <i class="fas fa-user-tie text-primary me-2"></i>{{ $group['faculty']->name ?? 'Unknown Faculty' }}
            </h6>
            <small class="text-muted">{{ $group['faculty']->department ?? '' }} | {{ $group['faculty']->designation ?? '' }}</small>
          </div>
          <div>
            <span class="badge bg-primary fs-6">{{ number_format($group['total'], 2) }}</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Duty Type</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($group['items'] as $item)
                @php $rem = $item->facultyRemuneration; @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
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
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                  <td class="fw-bold">{{ number_format($group['total'], 2) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      @endforeach

      @if($byFaculty->isEmpty())
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-4">
          <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
          <p class="text-muted mt-2 mb-0">No items in this batch</p>
        </div>
      </div>
      @endif
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
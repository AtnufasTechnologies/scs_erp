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
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-remuneration.index') }}">Remuneration</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
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
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-file-invoice-dollar me-2"></i>Remuneration Details</h3>
                  <p class="text-white-50 mb-0">View remuneration record and faculty earnings summary</p>
                </div>
                <div class="col-md-4 text-md-end">
                  @if($remuneration->status === 'pending')
                  <form method="POST" action="{{ route('admin.exam-remuneration.approve', $remuneration->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-2" onclick="return confirm('Approve this remuneration?')">
                      <i class="fas fa-check me-1"></i>Approve
                    </button>
                  </form>
                  @elseif($remuneration->status === 'approved')
                  <form method="POST" action="{{ route('admin.exam-remuneration.mark-paid', $remuneration->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info me-2" onclick="return confirm('Mark as paid?')">
                      <i class="fas fa-money-bill-wave me-1"></i>Mark Paid
                    </button>
                  </form>
                  @endif
                  <a href="{{ route('admin.exam-remuneration.index') }}" class="btn btn-outline-light">
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

      <div class="row">
        <!-- Remuneration Details -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Remuneration Info</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="text-muted" style="width: 40%;">Faculty</td>
                  <td class="fw-semibold">{{ $remuneration->faculty->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Department</td>
                  <td>{{ $remuneration->faculty->department ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Designation</td>
                  <td>{{ $remuneration->faculty->designation ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Duty Type</td>
                  <td>
                    @if($remuneration->duty_type === 'invigilation')
                    <span class="badge bg-primary">Invigilation</span>
                    @elseif($remuneration->duty_type === 'evaluation')
                    <span class="badge bg-info">Evaluation</span>
                    @else
                    <span class="badge bg-secondary">Moderation</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Quantity</td>
                  <td>{{ $remuneration->quantity }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Rate</td>
                  <td>{{ number_format($remuneration->rate, 2) }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Total Amount</td>
                  <td class="fw-bold fs-5 text-success">{{ number_format($remuneration->total_amount, 2) }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Status</td>
                  <td>
                    @if($remuneration->status === 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($remuneration->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @else
                    <span class="badge bg-info">Paid</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-muted">Generated At</td>
                  <td>{{ $remuneration->generated_at ? $remuneration->generated_at->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                <tr>
                  <td class="text-muted">Created</td>
                  <td>{{ $remuneration->created_at->format('d M Y, h:i A') }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Faculty Earnings Summary -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-success me-2"></i>Faculty Earnings Summary</h5>
            </div>
            <div class="card-body">
              @php
              $grouped = $facultyEarnings->groupBy('duty_type');
              @endphp

              @foreach(['invigilation', 'evaluation', 'moderation'] as $type)
              @if(isset($grouped[$type]))
              <div class="mb-3">
                <h6 class="fw-bold text-capitalize">{{ $type }}</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($grouped[$type] as $entry)
                      <tr>
                        <td>
                          @if($entry->status === 'pending')
                          <span class="badge bg-warning text-dark">Pending</span>
                          @elseif($entry->status === 'approved')
                          <span class="badge bg-success">Approved</span>
                          @else
                          <span class="badge bg-info">Paid</span>
                          @endif
                        </td>
                        <td>{{ $entry->count }}</td>
                        <td class="fw-bold">{{ number_format($entry->total, 2) }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
              @endif
              @endforeach

              @if($facultyEarnings->isEmpty())
              <div class="text-center py-3">
                <p class="text-muted mb-0">No earnings data available</p>
              </div>
              @else
              <hr>
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Total Faculty Earnings</h6>
                <h5 class="fw-bold text-success mb-0">{{ number_format($facultyEarnings->sum('total'), 2) }}</h5>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
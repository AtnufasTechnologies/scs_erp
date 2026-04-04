@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Faculty Payment Report</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-money-bill-wave me-2"></i>Faculty Payment Report</h3>
                  <p class="text-white-50 mb-0">Remuneration summary by faculty, department, and duty type</p>
                </div>
                <div class="col-md-5 text-md-end">
                  <form method="POST" action="{{ route('admin.exam-reports.export-pdf') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="remuneration">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <button type="submit" class="btn btn-light me-2"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                  </form>
                  <form method="POST" action="{{ route('admin.exam-reports.export-excel') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="remuneration">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <button type="submit" class="btn btn-outline-light"><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Amount</p>
              <h4 class="mb-0 fw-bold">{{ number_format($totalAmount, 2) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending</p>
              <h4 class="mb-0 fw-bold text-warning">{{ number_format($pendingAmount, 2) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Approved</p>
              <h4 class="mb-0 fw-bold text-success">{{ number_format($approvedAmount, 2) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Paid</p>
              <h4 class="mb-0 fw-bold text-info">{{ number_format($paidAmount, 2) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Faculty Members</p>
              <h4 class="mb-0 fw-bold text-primary">{{ $facultyCount }}</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-reports.remuneration') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label fw-semibold">Faculty</label>
              <select name="faculty_id" class="form-select">
                <option value="">All Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Department</label>
              <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->name }}" {{ request('department') == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Duty Type</label>
              <select name="duty_type" class="form-select">
                <option value="">All Types</option>
                <option value="invigilation" {{ request('duty_type') === 'invigilation' ? 'selected' : '' }}>Invigilation</option>
                <option value="evaluation" {{ request('duty_type') === 'evaluation' ? 'selected' : '' }}>Evaluation</option>
                <option value="moderation" {{ request('duty_type') === 'moderation' ? 'selected' : '' }}>Moderation</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
              </select>
            </div>
            <div class="col-md-1">
              <label class="form-label fw-semibold">From</label>
              <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
              <label class="form-label fw-semibold">To</label>
              <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-1"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.exam-reports.remuneration') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Faculty</th>
                  <th>Department</th>
                  <th>Duty Type</th>
                  <th>Quantity</th>
                  <th>Rate</th>
                  <th>Total Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($remunerations as $rem)
                <tr>
                  <td>{{ $loop->iteration + ($remunerations->currentPage() - 1) * $remunerations->perPage() }}</td>
                  <td class="fw-semibold">{{ $rem->faculty->name ?? 'N/A' }}</td>
                  <td>{{ $rem->faculty->department ?? '-' }}</td>
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
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No payment records found</p>
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
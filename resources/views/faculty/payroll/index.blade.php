@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Payroll</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Salary Slips</li>
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

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
      <div class="col-md-4">
        <div class="card shadow-sm border-0 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Total Paid ({{ date('Y') }})</p>
                <h3 class="mb-0 fw-bold text-success">₹{{ number_format($currentYearStats['total_paid'], 2) }}</h3>
                <small class="text-muted mt-1 d-block">{{ $currentYearStats['slips_count'] }} salary slips</small>
              </div>
              <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-rupee-sign text-success" style="font-size: 1.75rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Salary Slips</p>
                <h3 class="mb-0 fw-bold text-primary">{{ $currentYearStats['slips_count'] }}</h3>
                <small class="text-muted mt-1 d-block">This year</small>
              </div>
              <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-invoice-dollar text-primary" style="font-size: 1.75rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card shadow-sm border-0 hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; font-weight: 600;">Pending</p>
                <h3 class="mb-0 fw-bold text-warning">{{ $currentYearStats['pending_count'] }}</h3>
                <small class="text-muted mt-1 d-block">Awaiting processing</small>
              </div>
              <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock text-warning" style="font-size: 1.75rem;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <form method="GET" action="{{ route('faculty.payroll') }}" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label fw-bold small">Year</label>
            <select name="year" class="form-select">
              <option value="">All Years</option>
              @foreach($availableYears as $availableYear)
              <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>
                {{ $availableYear }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold small">Month</label>
            <select name="month" class="form-select">
              <option value="">All Months</option>
              @for($m = 1; $m <= 12; $m++)
                @php
                $monthValue=str_pad($m, 2, '0' , STR_PAD_LEFT);
                $monthName=\Carbon\Carbon::create()->month($m)->format('F');
                @endphp
                <option value="{{ $monthValue }}" {{ $month == $monthValue ? 'selected' : '' }}>
                  {{ $monthName }}
                </option>
                @endfor
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label fw-bold small">Status</label>
            <select name="status" class="form-select">
              <option value="">All Status</option>
              <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
              <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
          </div>

          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-filter me-1"></i>Filter
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Salary Slips List -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-transparent border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">
            <i class="fas fa-file-invoice me-2 text-primary"></i>My Salary Slips
            @if($year)
            - {{ $year }}
            @endif
          </h6>
          @if($salarySlips->count() > 0 && $year)
          <a href="{{ route('faculty.payroll.bulk.download', ['year' => $year]) }}" class="btn btn-success btn-sm">
            <i class="fas fa-download me-1"></i>Download All ({{ $year }})
          </a>
          @endif
        </div>
      </div>
      <div class="card-body p-0">
        @if($salarySlips->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Slip Number</th>
                <th>Month/Year</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($salarySlips as $slip)
              <tr>
                <td>
                  <span class="fw-bold text-primary">{{ $slip->salary_slip_number }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-alt text-muted me-2"></i>
                    <span class="fw-bold">{{ $slip->month_year }}</span>
                  </div>
                </td>
                <td>
                  <span class="text-success fw-bold">₹{{ number_format($slip->gross_salary, 2) }}</span>
                </td>
                <td>
                  <span class="text-danger">₹{{ number_format($slip->total_deductions, 2) }}</span>
                </td>
                <td>
                  <span class="text-primary fw-bold fs-6">₹{{ number_format($slip->net_salary, 2) }}</span>
                </td>
                <td>
                  <span class="badge bg-{{ $slip->status_badge }} rounded-pill">
                    {{ ucfirst($slip->status) }}
                  </span>
                  @if($slip->status == 'paid' && $slip->payment_date)
                  <br><small class="text-muted">{{ $slip->payment_date->format('d M Y') }}</small>
                  @endif
                </td>
                <td>
                  <a href="{{ route('faculty.payroll.show', $slip->id) }}" class="btn btn-sm btn-info me-1" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('faculty.payroll.download', $slip->id) }}" class="btn btn-sm btn-success" title="Download PDF">
                    <i class="fas fa-download"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="p-3">
          {{ $salarySlips->appends(['year' => $year, 'month' => $month, 'status' => $status])->links() }}
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-file-invoice text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
          <p class="text-muted mt-3 mb-0">No salary slips found</p>
          <small class="text-muted">Salary slips will appear here once they are generated by the administration</small>
        </div>
        @endif
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

<style>
  .hover-lift {
    transition: all 0.3s ease;
  }

  .hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
  }
</style>

@include('includes.footer')
@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Payroll Management</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Salary Slips</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Monthly Payroll</a>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($financialYearMissing)
    <div class="alert alert-warning">
      No active financial year is configured. Configure an active financial year to view payroll periods.
    </div>
    @endif

    @if($financialYearSessionMismatch)
    <div class="alert alert-warning">
      Active financial year is {{ $activeFinancialYear->title ?? 'N/A' }}, but no matching Annual Session exists.
      Showing payroll periods using active financial year date range fallback. Create an Annual Session with the same title for strict mapping.
    </div>
    @endif

    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Payroll by Month and Financial Year</h6>
          <small class="text-muted">Click a month to view generated payrolls and update individual slips.</small>
        </div>

        <form method="GET" class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="form-label mb-1">Month</label>
            <select name="month" class="form-select">
              <option value="">All Months</option>
              @for($m = 1; $m <= 12; $m++)
                @php $monthValue=str_pad($m, 2, '0' , STR_PAD_LEFT); @endphp
                <option value="{{ $monthValue }}" {{ (string) $month === (string) $monthValue ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
                @endfor
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Financial Year</label>
            <input type="text" class="form-control" value="{{ $activeFinancialYear->title ?? 'Not Configured' }}" readonly>
            <small class="text-muted">Payroll periods are locked to the active financial year.</small>
          </div>
          <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Search</button>
            <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>

        <div class="row g-2">
          @forelse($payrollPeriods as $period)
          <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('admin.payroll.period-payrolls', ['month' => $period->month, 'year' => $period->year, 'annual_session_id' => $period->annual_session_id]) }}"
              class="text-decoration-none">
              <div class="border rounded p-3 border-light-subtle">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-semibold text-dark">{{ \Carbon\Carbon::create()->month((int) $period->month)->format('F') }} {{ $period->year }}</div>
                    <small class="text-muted">FY: {{ $period->financial_year_title ?? 'N/A' }}</small>
                  </div>
                  <span class="badge bg-primary">{{ $period->slips_count }}</span>
                </div>
                <div class="mt-2 small text-muted">Total Net: ₹{{ number_format((float) $period->total_net_salary, 0) }}</div>
              </div>
            </a>
          </div>
          @empty
          <div class="col-12">
            <div class="text-muted">No payroll periods found.</div>
          </div>
          @endforelse
        </div>
      </div>
    </div>


  </div>
</div>

@include('includes.footer')
@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Payrolls</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active">{{ \Carbon\Carbon::create()->month((int) $month)->format('F') }} {{ $year }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
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

    <div class="card mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-1">{{ \Carbon\Carbon::create()->month((int) $month)->format('F') }} {{ $year }}</h5>
          <p class="mb-0 text-muted">Financial Year: {{ $activeFinancialYear->title ?? ($periodSession->title ?? 'N/A') }}</p>
        </div>
        <div class="text-muted">Generated Slips: <strong>{{ $salarySlips->total() }}</strong></div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Slip Number</th>
                <th>Faculty</th>
                <th>Month/Year</th>
                <th>Financial Year</th>
                <th>Gross</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($salarySlips as $slip)
              <tr>
                <td><small class="text-primary fw-bold">{{ $slip->salary_slip_number }}</small></td>
                <td>{{ $slip->faculty->FIRST_NAME ?? '' }} {{ $slip->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ $slip->month_year }}</td>
                <td>{{ $slip->financialYear->title ?? ($slip->annualSession->title ?? '-') }}</td>
                <td>₹{{ number_format($slip->gross_salary, 0) }}</td>
                <td>₹{{ number_format($slip->total_deductions, 0) }}</td>
                <td><strong>₹{{ number_format($slip->net_salary, 0) }}</strong></td>
                <td><span class="badge bg-{{ $slip->status_badge }}">{{ ucfirst($slip->status) }}</span></td>
                <td>
                  <a href="{{ route('admin.payroll.show', $slip->id) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                  @if($slip->status !== 'paid')
                  <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-warning" title="Update"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('admin.payroll.destroy', $slip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary slip? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No payroll slips found for this period.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $salarySlips->links() }}
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
@include('includes.header')
@include('hr.sidebar')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">HR</div>
  <div class="ps-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Payroll</a></li>
        <li class="breadcrumb-item active" aria-current="page">Statistics</li>
      </ol>
    </nav>
  </div>
  <div class="ms-auto">
    <a href="{{ route('hr.payroll.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Back to Payroll
    </a>
  </div>
</div>

{{-- Year Filter --}}
<div class="card mb-4">
  <div class="card-body py-2">
    <form method="GET" action="{{ route('hr.payroll.statistics') }}" class="d-flex align-items-center gap-3">
      <label class="form-label mb-0 fw-semibold">Year:</label>
      <select name="year" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
        @for($y = date('Y'); $y >= 2020; $y--)
        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </form>
  </div>
</div>

{{-- Annual Summary Cards --}}
@php
$totalSlips = collect($monthlyStats)->sum('total_slips');
$totalPaid = collect($monthlyStats)->sum('paid');
$totalAmount = collect($monthlyStats)->sum('total_amount');
$totalApproved = collect($monthlyStats)->sum('approved');
@endphp
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex align-items-start gap-2">
          <div>
            <p class="mb-0 fs-6">Total Slips ({{ $year }})</p>
          </div>
          <div class="ms-auto widget-icon-small text-white bg-gradient-info">
            <i class="fas fa-file-alt"></i>
          </div>
        </div>
        <h4 class="mb-0 mt-3">{{ $totalSlips }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex align-items-start gap-2">
          <div>
            <p class="mb-0 fs-6">Approved</p>
          </div>
          <div class="ms-auto widget-icon-small text-white bg-gradient-success">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <h4 class="mb-0 mt-3">{{ $totalApproved }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex align-items-start gap-2">
          <div>
            <p class="mb-0 fs-6">Paid</p>
          </div>
          <div class="ms-auto widget-icon-small text-white bg-gradient-purple">
            <i class="fas fa-money-bill-wave"></i>
          </div>
        </div>
        <h4 class="mb-0 mt-3">{{ $totalPaid }}</h4>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card radius-10">
      <div class="card-body">
        <div class="d-flex align-items-start gap-2">
          <div>
            <p class="mb-0 fs-6">Total Amount ({{ $year }})</p>
          </div>
          <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
        <h4 class="mb-0 mt-3">₹{{ number_format($totalAmount, 0) }}</h4>
      </div>
    </div>
  </div>
</div>

{{-- Monthly Breakdown --}}
<div class="card mb-4">
  <div class="card-header bg-transparent">
    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Monthly Breakdown — {{ $year }}</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Month</th>
            <th class="text-center">Total Slips</th>
            <th class="text-center">Approved</th>
            <th class="text-center">Paid</th>
            <th class="text-end">Total Net Amount</th>
            <th class="text-center">Coverage</th>
          </tr>
        </thead>
        <tbody>
          @foreach($monthlyStats as $stat)
          <tr>
            <td><strong>{{ $stat['month_name'] }}</strong></td>
            <td class="text-center">
              @if($stat['total_slips'] > 0)
              <a href="{{ route('hr.payroll.index', ['month' => str_pad($stat['month'], 2, '0', STR_PAD_LEFT), 'year' => $year]) }}"
                class="badge bg-info text-decoration-none">{{ $stat['total_slips'] }}</a>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td class="text-center">
              <span class="badge bg-success">{{ $stat['approved'] }}</span>
            </td>
            <td class="text-center">
              <span class="badge bg-primary">{{ $stat['paid'] }}</span>
            </td>
            <td class="text-end">
              @if($stat['total_amount'] > 0)
              <strong>₹{{ number_format($stat['total_amount'], 2) }}</strong>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td class="text-center">
              @if($stat['total_slips'] > 0)
              <div class="progress" style="height:8px; min-width:80px;">
                @php $pct = $stat['total_slips'] > 0 ? round(($stat['paid'] / $stat['total_slips']) * 100) : 0; @endphp
                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
              </div>
              <small class="text-muted">{{ $pct }}% paid</small>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light">
          <tr>
            <th>Total</th>
            <th class="text-center">{{ $totalSlips }}</th>
            <th class="text-center">{{ $totalApproved }}</th>
            <th class="text-center">{{ $totalPaid }}</th>
            <th class="text-end">₹{{ number_format($totalAmount, 2) }}</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

{{-- Pay Matrix Usage --}}
@if($payMatrixUsage->count() > 0)
<div class="card">
  <div class="card-header bg-transparent">
    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Pay Matrix Usage</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Matrix Code</th>
            <th>Designation</th>
            <th>Grade Level</th>
            <th>Employment Type</th>
            <th class="text-center">Faculty Assigned</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payMatrixUsage as $matrix)
          <tr>
            <td><strong>{{ $matrix->matrix_code }}</strong></td>
            <td>{{ $matrix->designation }}</td>
            <td><span class="badge bg-info">{{ $matrix->grade_level }}</span></td>
            <td><span class="badge bg-secondary">{{ ucfirst($matrix->employment_type) }}</span></td>
            <td class="text-center"><span class="badge bg-primary">{{ $matrix->faculty_salaries_count }}</span></td>
            <td>
              @php $colors = ['active'=>'success','inactive'=>'warning','archived'=>'secondary']; @endphp
              <span class="badge bg-{{ $colors[$matrix->status] ?? 'secondary' }}">{{ ucfirst($matrix->status) }}</span>
            </td>
            <td>
              <a href="{{ route('hr.pay-matrix.show', $matrix->id) }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

@include('includes.footer')
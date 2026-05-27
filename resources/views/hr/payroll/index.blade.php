@extends('layouts.master')

@section('title')
Payroll Management
@endsection

@section('content')
@include('includes.hr-sidebar')

<!--start main wrapper-->
<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">HR</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Payroll</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.payroll.generate') }}" class="btn btn-primary">
          <i class="material-icons-outlined">account_balance_wallet</i> Generate Payroll
        </a>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
      <div class="col">
        <div class="card radius-10">
          <div class="card-body">
            <div class="d-flex align-items-start gap-2">
              <div>
                <p class="mb-0 fs-6">Total Faculty</p>
              </div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-purple">
                <i class="material-icons-outlined">people</i>
              </div>
            </div>
            <div class="d-flex align-items-center mt-3">
              <div>
                <h4 class="mb-0">{{ $stats['total_faculty'] }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10">
          <div class="card-body">
            <div class="d-flex align-items-start gap-2">
              <div>
                <p class="mb-0 fs-6">Slips Generated</p>
              </div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-info">
                <i class="material-icons-outlined">description</i>
              </div>
            </div>
            <div class="d-flex align-items-center mt-3">
              <div>
                <h4 class="mb-0">{{ $stats['slips_generated'] }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10">
          <div class="card-body">
            <div class="d-flex align-items-start gap-2">
              <div>
                <p class="mb-0 fs-6">Approved</p>
              </div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-success">
                <i class="material-icons-outlined">check_circle</i>
              </div>
            </div>
            <div class="d-flex align-items-center mt-3">
              <div>
                <h4 class="mb-0">{{ $stats['approved'] }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10">
          <div class="card-body">
            <div class="d-flex align-items-start gap-2">
              <div>
                <p class="mb-0 fs-6">Total Amount</p>
              </div>
              <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                <i class="material-icons-outlined">payments</i>
              </div>
            </div>
            <div class="d-flex align-items-center mt-3">
              <div>
                <h4 class="mb-0">₹{{ number_format($stats['total_amount'], 2) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <h5 class="mb-0">Salary Slips</h5>
        </div>

        <!-- Month/Year Filter -->
        <form method="GET" action="{{ route('hr.payroll.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-3">
              <select name="month" class="form-select" required>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                  {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                  {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                  </option>
                  @endfor
              </select>
            </div>
            <div class="col-md-3">
              <select name="year" class="form-select" required>
                @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="material-icons-outlined">search</i> Filter
              </button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Slip Number</th>
                <th>Faculty</th>
                <th>Month/Year</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($salarySlips as $slip)
              <tr>
                <td><strong>{{ $slip->salary_slip_number }}</strong></td>
                <td>{{ $slip->faculty->FIRST_NAME }} {{ $slip->faculty->LAST_NAME }}</td>
                <td>{{ DateTime::createFromFormat('!m', $slip->month)->format('M') }} {{ $slip->year }}</td>
                <td>₹{{ number_format($slip->gross_salary, 2) }}</td>
                <td>₹{{ number_format($slip->total_deductions, 2) }}</td>
                <td><strong>₹{{ number_format($slip->net_salary, 2) }}</strong></td>
                <td>
                  @if($slip->status == 'draft')
                  <span class="badge bg-warning">Draft</span>
                  @elseif($slip->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($slip->status == 'paid')
                  <span class="badge bg-primary">Paid</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="{{ route('hr.payroll.show', $slip->id) }}" class="btn btn-sm btn-info" title="View">
                      <i class="material-icons-outlined">visibility</i>
                    </a>
                    @if($slip->status == 'draft')
                    <form action="{{ route('hr.payroll.approve', $slip->id) }}" method="POST"
                      style="display: inline;">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success" title="Approve">
                        <i class="material-icons-outlined">check</i>
                      </button>
                    </form>
                    @endif
                    @if($slip->status != 'paid')
                    <form action="{{ route('hr.payroll.destroy', $slip->id) }}" method="POST"
                      style="display: inline;"
                      onsubmit="return confirm('Are you sure you want to delete this salary slip?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="material-icons-outlined">delete</i>
                      </button>
                    </form>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center">No salary slips found for {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $salarySlips->links() }}
        </div>
      </div>
    </div>
  </div>
</main>
<!--end main wrapper-->

@include('includes.footer')
@endsection
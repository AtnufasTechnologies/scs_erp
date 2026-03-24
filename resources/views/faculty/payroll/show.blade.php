@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 ">
      <div class="breadcrumb-title pe-3">Payroll</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.payroll') }}">Salary Slips</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $salarySlip->salary_slip_number }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('faculty.payroll.download', $salarySlip->id) }}" class="btn btn-success mb-3">
          <i class="fas fa-download me-1"></i>Download PDF
        </a>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- Salary Slip Header -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="mb-2 fw-bold">{{ $salarySlip->salary_slip_number }}</h4>
            <p class="text-muted mb-1">
              <i class="fas fa-calendar-alt me-2"></i>
              <strong>Period:</strong> {{ $salarySlip->month_year }}
            </p>
            <p class="text-muted mb-0">
              <i class="fas fa-user me-2"></i>
              <strong>Faculty:</strong> {{ $salarySlip->faculty->USER_CODE ?? 'N/A' }} - {{ $salarySlip->faculty->FIRST_NAME ?? 'N/A' }} {{ $salarySlip->faculty->LAST_NAME ?? 'N/A' }}
            </p>
          </div>
          <div class="col-md-4 text-md-end">
            <span class="badge bg-{{ $salarySlip->status_badge }} rounded-pill fs-6 px-3 py-2">
              {{ ucfirst($salarySlip->status) }}
            </span>
            @if($salarySlip->status == 'paid' && $salarySlip->payment_date)
            <p class="text-muted mb-0 mt-2">
              <small><strong>Paid on:</strong> {{ $salarySlip->payment_date->format('d F Y') }}</small>
            </p>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Earnings Column -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-success bg-opacity-10 border-bottom">
            <h6 class="mb-0 fw-bold text-light">
              <i class="fas fa-plus-circle me-2"></i>Earnings
            </h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="text-muted">Basic Salary</td>
                  <td class="text-end fw-bold">₹{{ number_format($salarySlip->basic_salary, 2) }}</td>
                </tr>
                @if($salarySlip->da > 0)
                <tr>
                  <td class="text-muted">Dearness Allowance (DA)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->da, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->hra > 0)
                <tr>
                  <td class="text-muted">House Rent Allowance (HRA)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->hra, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->ta > 0)
                <tr>
                  <td class="text-muted">Transport Allowance (TA)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->ta, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->medical_allowance > 0)
                <tr>
                  <td class="text-muted">Medical Allowance</td>
                  <td class="text-end">₹{{ number_format($salarySlip->medical_allowance, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->special_allowance > 0)
                <tr>
                  <td class="text-muted">Special Allowance</td>
                  <td class="text-end">₹{{ number_format($salarySlip->special_allowance, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->other_allowances > 0)
                <tr>
                  <td class="text-muted">Other Allowances</td>
                  <td class="text-end">₹{{ number_format($salarySlip->other_allowances, 2) }}</td>
                </tr>
                @endif
              </tbody>
              <tfoot class="border-top">
                <tr>
                  <td class="fw-bold text-success pt-3">Gross Salary</td>
                  <td class="text-end fw-bold text-success pt-3 fs-5">₹{{ number_format($salarySlip->gross_salary, 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Deductions Column -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-danger bg-opacity-10 border-bottom">
            <h6 class="mb-0 fw-bold text-light">
              <i class="fas fa-minus-circle me-2"></i>Deductions
            </h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless mb-0">
              <tbody>
                @if($salarySlip->pf > 0)
                <tr>
                  <td class="text-muted">Provident Fund (PF)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->pf, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->esi > 0)
                <tr>
                  <td class="text-muted">Employee State Insurance (ESI)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->esi, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->professional_tax > 0)
                <tr>
                  <td class="text-muted">Professional Tax</td>
                  <td class="text-end">₹{{ number_format($salarySlip->professional_tax, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->tds > 0)
                <tr>
                  <td class="text-muted">Tax Deducted at Source (TDS)</td>
                  <td class="text-end">₹{{ number_format($salarySlip->tds, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->loan_deduction > 0)
                <tr>
                  <td class="text-muted">Loan Deduction</td>
                  <td class="text-end">₹{{ number_format($salarySlip->loan_deduction, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->other_deductions > 0)
                <tr>
                  <td class="text-muted">Other Deductions</td>
                  <td class="text-end">₹{{ number_format($salarySlip->other_deductions, 2) }}</td>
                </tr>
                @endif
                @if($salarySlip->total_deductions == 0)
                <tr>
                  <td colspan="2" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-1"></i>No deductions
                  </td>
                </tr>
                @endif
              </tbody>
              <tfoot class="border-top">
                <tr>
                  <td class="fw-bold text-danger pt-3">Total Deductions</td>
                  <td class="text-end fw-bold text-danger pt-3 fs-5">₹{{ number_format($salarySlip->total_deductions, 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Net Salary Card -->
    <div class="card shadow-sm border-0 mt-4  bg-opacity-10">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h4 class="mb-0 fw-bold text-primary">Net Salary</h4>
            <p class="text-muted mb-0 mt-1">Amount payable after all deductions</p>
          </div>
          <div class="col-md-4 text-md-end">
            <h2 class="mb-0 fw-bold text-primary">₹{{ number_format($salarySlip->net_salary, 2) }}</h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Additional Details -->
    <div class="row g-4 mt-1">
      <!-- Attendance Details -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-info bg-opacity-10 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-user-check me-2"></i>Attendance Information
            </h6>
          </div>
          <div class="card-body">
            <div class="row g-3 text-center">
              <div class="col-4">
                <div class="p-3 bg-light rounded">
                  <h4 class="mb-1 fw-bold text-primary">{{ $salarySlip->working_days }}</h4>
                  <p class="text-muted mb-0 small">Working Days</p>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded">
                  <h4 class="mb-1 fw-bold text-success">{{ $salarySlip->present_days }}</h4>
                  <p class="text-muted mb-0 small">Present Days</p>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded">
                  <h4 class="mb-1 fw-bold text-warning">{{ $salarySlip->leave_days }}</h4>
                  <p class="text-muted mb-0 small">Leave Days</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Details -->
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-warning bg-opacity-10 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="fas fa-money-check-alt me-2"></i>Payment Information
            </h6>
          </div>
          <div class="card-body">
            @if($salarySlip->status == 'paid')
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="text-muted"><i class="fas fa-calendar-check me-2"></i>Payment Date</td>
                  <td class="text-end fw-bold">{{ $salarySlip->payment_date->format('d F Y') }}</td>
                </tr>
                @if($salarySlip->payment_mode)
                <tr>
                  <td class="text-muted"><i class="fas fa-credit-card me-2"></i>Payment Mode</td>
                  <td class="text-end">{{ ucfirst($salarySlip->payment_mode) }}</td>
                </tr>
                @endif
                @if($salarySlip->payment_reference)
                <tr>
                  <td class="text-muted"><i class="fas fa-hashtag me-2"></i>Reference Number</td>
                  <td class="text-end"><code>{{ $salarySlip->payment_reference }}</code></td>
                </tr>
                @endif
              </tbody>
            </table>
            @else
            <div class="text-center py-3">
              <i class="fas fa-clock text-muted" style="font-size: 2rem; opacity: 0.3;"></i>
              <p class="text-muted mt-2 mb-0">Payment pending</p>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Remarks -->
    @if($salarySlip->remarks)
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-header bg-secondary bg-opacity-10 border-bottom">
        <h6 class="mb-0 fw-bold">
          <i class="fas fa-comment-dots me-2"></i>Remarks
        </h6>
      </div>
      <div class="card-body">
        <p class="mb-0 text-muted">{{ $salarySlip->remarks }}</p>
      </div>
    </div>
    @endif

    <!-- Approval Information -->
    @if($salarySlip->status != 'draft' && $salarySlip->approved_by)
    <div class="card shadow-sm border-0 mt-4">
      <div class="card-body">
        <p class="text-muted mb-0">
          <i class="fas fa-check-circle text-success me-2"></i>
          <strong>Approved by:</strong> {{ $salarySlip->approver->name ?? 'N/A' }}
          @if($salarySlip->approved_at)
          on {{ $salarySlip->approved_at->format('d F Y, h:i A') }}
          @endif
        </p>
      </div>
    </div>
    @endif

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
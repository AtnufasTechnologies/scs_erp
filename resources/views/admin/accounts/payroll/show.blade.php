@include('includes.header')
@include('admin.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Salary Slip Details</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active">{{ $salarySlip->salary_slip_number }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        @if($salarySlip->status !== 'paid')
        <a href="{{ route('admin.payroll.edit', $salarySlip->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        @endif
        @if($salarySlip->status === 'draft')
        <form action="{{ route('admin.payroll.approve', $salarySlip->id) }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-success" onclick="return confirm('Approve this salary slip?')">
            <i class="fas fa-check"></i> Approve
          </button>
        </form>
        @endif
        @if($salarySlip->status !== 'paid')
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markPaidModal">
          <i class="fas fa-money-check-alt"></i> Mark as Paid
        </button>
        @endif
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <h5>{{ $salarySlip->salary_slip_number }}</h5>
            <p class="mb-1"><strong>Faculty:</strong> {{ $salarySlip->faculty->FIRST_NAME ?? '' }} {{ $salarySlip->faculty->LAST_NAME ?? '' }}</p>
            <p class="mb-1"><strong>Period:</strong> {{ $salarySlip->month_year }}</p>
          </div>
          <div class="col-md-4 text-end">
            <span class="badge bg-{{ $salarySlip->status_badge }} fs-6 px-3 py-2">{{ ucfirst($salarySlip->status) }}</span>
            @if($salarySlip->payment_date)
            <p class="mb-0 mt-2"><small>Paid on: {{ $salarySlip->payment_date->format('d M Y') }}</small></p>
            @endif
          </div>
        </div>
      </div>
    </div>

    @php
    $activeLoan = \App\Models\FacultyLoan::where('faculty_id', $salarySlip->faculty_id)
    ->where('status', 'active')
    ->first();
    @endphp

    @if($activeLoan)
    <!-- Active Loan Info -->
    <div class="alert alert-info mb-3" style="border-left: 4px solid #0dcaf0;">
      <div class="row align-items-center">
        <div class="col-md-1 text-center">
          <i class="fas fa-hand-holding-usd fa-3x"></i>
        </div>
        <div class="col-md-11">
          <h6 class="mb-2"><strong>Active Loan Information</strong></h6>
          <div class="row">
            <div class="col-md-4">
              <small class="text-muted">Loan Type:</small><br>
              <strong>{{ $activeLoan->loan_type }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Loan Number:</small><br>
              <strong>{{ $activeLoan->loan_number }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Total Loan:</small><br>
              <strong>₹{{ number_format($activeLoan->loan_amount, 2) }}</strong>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-md-4">
              <small class="text-muted">Monthly EMI:</small><br>
              <strong class="text-primary">₹{{ number_format($activeLoan->emi_amount, 2) }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Remaining Amount:</small><br>
              <strong class="text-danger">₹{{ number_format($activeLoan->remaining_amount, 2) }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Installments:</small><br>
              <strong>{{ $activeLoan->paid_installments }}/{{ $activeLoan->total_installments }}</strong>
              <span class="badge bg-success">{{ $activeLoan->progress_percentage }}%</span>
            </div>
          </div>
          @if($salarySlip->loan_deduction > 0)
          <div class="mt-2 pt-2 border-top">
            <small><i class="fas fa-check-circle text-success"></i> EMI of ₹{{ number_format($salarySlip->loan_deduction, 2) }} deducted in this salary slip</small>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif

    <div class="row">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-plus-circle"></i> Earnings</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td>Basic Salary</td>
                <td class="text-end">₹{{ number_format($salarySlip->basic_salary, 2) }}</td>
              </tr>
              @if($salarySlip->da > 0)<tr>
                <td>DA</td>
                <td class="text-end">₹{{ number_format($salarySlip->da, 2) }}</td>
              </tr>@endif
              @if($salarySlip->hra > 0)<tr>
                <td>HRA</td>
                <td class="text-end">₹{{ number_format($salarySlip->hra, 2) }}</td>
              </tr>@endif
              @if($salarySlip->ta > 0)<tr>
                <td>TA</td>
                <td class="text-end">₹{{ number_format($salarySlip->ta, 2) }}</td>
              </tr>@endif
              @if($salarySlip->medical_allowance > 0)<tr>
                <td>Medical</td>
                <td class="text-end">₹{{ number_format($salarySlip->medical_allowance, 2) }}</td>
              </tr>@endif
              @if($salarySlip->special_allowance > 0)<tr>
                <td>Special</td>
                <td class="text-end">₹{{ number_format($salarySlip->special_allowance, 2) }}</td>
              </tr>@endif
              @if($salarySlip->other_allowances > 0)<tr>
                <td>Other</td>
                <td class="text-end">₹{{ number_format($salarySlip->other_allowances, 2) }}</td>
              </tr>@endif
              <tr class="table-success">
                <td><strong>Gross Salary</strong></td>
                <td class="text-end"><strong>₹{{ number_format($salarySlip->gross_salary, 2) }}</strong></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header bg-danger text-white">
            <h6 class="mb-0"><i class="fas fa-minus-circle"></i> Deductions</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              @if($salarySlip->pf > 0)<tr>
                <td>PF</td>
                <td class="text-end">₹{{ number_format($salarySlip->pf, 2) }}</td>
              </tr>@endif
              @if($salarySlip->esi > 0)<tr>
                <td>ESI</td>
                <td class="text-end">₹{{ number_format($salarySlip->esi, 2) }}</td>
              </tr>@endif
              @if($salarySlip->professional_tax > 0)<tr>
                <td>Professional Tax</td>
                <td class="text-end">₹{{ number_format($salarySlip->professional_tax, 2) }}</td>
              </tr>@endif
              @if($salarySlip->tds > 0)<tr>
                <td>TDS</td>
                <td class="text-end">₹{{ number_format($salarySlip->tds, 2) }}</td>
              </tr>@endif
              @if($salarySlip->loan_deduction > 0)<tr>
                <td>Loan/EMI</td>
                <td class="text-end">₹{{ number_format($salarySlip->loan_deduction, 2) }}</td>
              </tr>@endif
              @if($salarySlip->other_deductions > 0)<tr>
                <td>Other</td>
                <td class="text-end">₹{{ number_format($salarySlip->other_deductions, 2) }}</td>
              </tr>@endif
              @if($salarySlip->total_deductions == 0)<tr>
                <td colspan="2" class="text-center text-muted">No deductions</td>
              </tr>@endif
              <tr class="table-danger">
                <td><strong>Total Deductions</strong></td>
                <td class="text-end"><strong>₹{{ number_format($salarySlip->total_deductions, 2) }}</strong></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-3  text-light">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h4 class="mb-0">Net Salary</h4>
          </div>
          <div class="col-md-6 text-end">
            <h3 class="mb-0">₹{{ number_format($salarySlip->net_salary, 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Attendance</h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-4">
                <h5>{{ $salarySlip->working_days }}</h5>
                <small>Working Days</small>
              </div>
              <div class="col-4">
                <h5 class="text-success">{{ $salarySlip->present_days }}</h5>
                <small>Present</small>
              </div>
              <div class="col-4">
                <h5 class="text-warning">{{ $salarySlip->leave_days }}</h5>
                <small>Leaves</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($salarySlip->status === 'paid' && $salarySlip->payment_date)
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Payment Info</h6>
          </div>
          <div class="card-body">
            <p class="mb-1"><strong>Date:</strong> {{ $salarySlip->payment_date->format('d F Y') }}</p>
            @if($salarySlip->payment_mode)<p class="mb-1"><strong>Mode:</strong> {{ ucfirst($salarySlip->payment_mode) }}</p>@endif
            @if($salarySlip->payment_reference)<p class="mb-0"><strong>Reference:</strong> {{ $salarySlip->payment_reference }}</p>@endif
          </div>
        </div>
      </div>
      @endif
    </div>

    @if($salarySlip->remarks)
    <div class="card mt-3">
      <div class="card-body">
        <strong>Remarks:</strong> {{ $salarySlip->remarks }}
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.payroll.mark-paid', $salarySlip->id) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Mark as Paid</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Payment Date*</label>
            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Mode*</label>
            <select name="payment_mode" class="form-select" required>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="cash">Cash</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Reference</label>
            <input type="text" name="payment_reference" class="form-control" placeholder="Transaction ID / Cheque No">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Mark as Paid</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
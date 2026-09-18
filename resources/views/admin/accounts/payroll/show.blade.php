@include('includes.header')
@include('admin.accounts.sidebar')

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

    @if(session('payroll_notice') === 'existing_finalized_slip_opened')
    <div class="alert alert-info">
      This slip was already in approved or paid status for the selected month, so a new publish action was not created.
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
        <hr class="my-3">
        <div class="row text-center g-2">
          <div class="col-md-4">
            <div class="border rounded py-2">
              <h6 class="mb-0">{{ $salarySlip->working_days }}</h6>
              <small class="text-muted">Working Days</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded py-2">
              <h6 class="mb-0 text-success">{{ $salarySlip->present_days }}</h6>
              <small class="text-muted">Present</small>
            </div>
          </div>
          <div class="col-md-4">
            <div class="border rounded py-2">
              <h6 class="mb-0 text-warning">{{ $salarySlip->leave_days }}</h6>
              <small class="text-muted">Leaves</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    @php
    $salaryMaster = \App\Models\FacultySalaryMaster::where('faculty_id', $salarySlip->faculty_id)
    ->active()
    ->first();
    $baseOtherDeduction = (float) ($salaryMaster->other_deductions ?? 0);
    $displayLateDeduction = (float) ($salarySlip->late_attendance_deduction ?? 0);
    $displayLeaveDeduction = (float) ($salarySlip->leave_deduction_amount ?? 0);
    $displayManualOtherDeduction = (float) ($salarySlip->manual_other_deduction ?? 0);
    $displayOtherDeduction = (float) ($salarySlip->other_deductions ?? 0);
    $netSalaryAmount = (float) ($salarySlip->net_salary ?? 0);

    $toWords = function ($amount) {
    $amount = round((float) $amount, 2);
    $rupees = (int) floor(abs($amount));
    $paise = (int) round((abs($amount) - $rupees) * 100);

    if (class_exists('NumberFormatter')) {
    $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
    $rupeesWords = ucfirst($formatter->format($rupees));
    $words = $rupeesWords . ' rupees';
    if ($paise > 0) {
    $words .= ' and ' . $formatter->format($paise) . ' paise';
    }
    if ($amount < 0) {
      $words='Minus ' . $words;
      }
      return $words . ' only' ;
      }

      return number_format($amount, 2) . ' only' ;
      };
      $netSalaryInWords=$toWords($netSalaryAmount);

      // Legacy slips stored unsplit variable deductions inside other_deductions only.
      if (
      $displayOtherDeduction> $baseOtherDeduction &&
      $displayLateDeduction == 0.0 &&
      $displayLeaveDeduction == 0.0 &&
      $displayManualOtherDeduction == 0.0
      ) {
      $legacyExtraDeduction = $displayOtherDeduction - $baseOtherDeduction;
      $displayLeaveDeduction = $legacyExtraDeduction;
      $displayOtherDeduction = $baseOtherDeduction;
      }

      $loanRecords = \App\Models\FacultyLoan::where('faculty_id', $salarySlip->faculty_id)
      ->orderByDesc('id')
      ->get();

      $loanSnapshot = $loanRecords->first();
      $loanTotalInstallments = (int) $loanRecords->sum('total_installments');
      $loanPaidInstallments = (int) $loanRecords->sum('paid_installments');
      $loanProgressPercent = $loanTotalInstallments > 0
      ? round(($loanPaidInstallments / $loanTotalInstallments) * 100, 2)
      : 0;
      $loanMonthlyEmi = (float) $loanRecords->where('status', 'active')->sum('emi_amount');
      $loanRemainingAmount = (float) $loanRecords->sum('remaining_amount');
      $loanTotalAmount = (float) $loanRecords->sum('loan_amount');
      $loanTotalPaidAmount = (float) $loanRecords->sum('total_paid');
      @endphp

      @if($loanRecords->isNotEmpty())
      <!-- Loan Info -->
      <div class="alert alert-info mb-3" style="border-left: 4px solid #0dcaf0;">
        <div class="row align-items-center">
          <div class="col-md-1 text-center">
            <i class="fas fa-hand-holding-usd fa-3x"></i>
          </div>
          <div class="col-md-11">
            <h6 class="mb-2"><strong>Loan Information</strong></h6>
            <div class="row">
              <div class="col-md-4">
                <small class="text-muted">Latest Loan Type:</small><br>
                <strong>{{ $loanSnapshot->loan_type ?? 'N/A' }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Latest Loan Number:</small><br>
                <strong>{{ $loanSnapshot->loan_number ?? 'N/A' }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Total Loan Amount:</small><br>
                <strong>₹{{ number_format($loanTotalAmount, 2) }}</strong>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <small class="text-muted">Current Monthly EMI:</small><br>
                <strong class="text-primary">₹{{ number_format($loanMonthlyEmi, 2) }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Remaining Amount:</small><br>
                <strong class="text-danger">₹{{ number_format($loanRemainingAmount, 2) }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Installments:</small><br>
                <strong>{{ $loanPaidInstallments }}/{{ $loanTotalInstallments }}</strong>
                <span class="badge bg-success">{{ $loanProgressPercent }}%</span>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-4">
                <small class="text-muted">Total Paid:</small><br>
                <strong class="text-success">₹{{ number_format($loanTotalPaidAmount, 2) }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Loans Count:</small><br>
                <strong>{{ $loanRecords->count() }}</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted">Active Loans:</small><br>
                <strong>{{ $loanRecords->where('status', 'active')->count() }}</strong>
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
                  <td>EPFO</td>
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
                @if($displayLateDeduction > 0)<tr>
                  <td>Late Attendance</td>
                  <td class="text-end">₹{{ number_format($displayLateDeduction, 2) }}</td>
                </tr>@endif
                @if($displayLeaveDeduction > 0)<tr>
                  <td>Leave Deduction</td>
                  <td class="text-end">₹{{ number_format($displayLeaveDeduction, 2) }}</td>
                </tr>@endif
                @if($displayOtherDeduction > 0)<tr>
                  <td>Other</td>
                  <td class="text-end">₹{{ number_format($displayOtherDeduction, 2) }}</td>
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
          <div class="mt-2">
            <small class="text-dark">In Words: {{ $netSalaryInWords }}</small>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        @if($salarySlip->status === 'paid' && $salarySlip->payment_date)
        <div class="col-md-12">
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
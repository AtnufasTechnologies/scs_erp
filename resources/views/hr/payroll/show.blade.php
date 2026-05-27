@extends('layouts.master')

@section('title')
Salary Slip Details
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
            <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active" aria-current="page">Salary Slip</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.payroll.index') }}" class="btn btn-secondary">
          <i class="material-icons-outlined">arrow_back</i> Back to List
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

    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="mb-0">Salary Slip: {{ $salarySlip->salary_slip_number }}</h4>
          <div>
            @if($salarySlip->status == 'draft')
            <form action="{{ route('hr.payroll.approve', $salarySlip->id) }}" method="POST" style="display: inline;">
              @csrf
              <button type="submit" class="btn btn-success">
                <i class="material-icons-outlined">check</i> Approve
              </button>
            </form>
            @endif
            @if($salarySlip->status == 'approved')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markPaidModal">
              <i class="material-icons-outlined">payments</i> Mark as Paid
            </button>
            @endif
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <h6 class="mb-3">Faculty Information</h6>
            <table class="table table-borderless">
              <tr>
                <td width="40%"><strong>Name:</strong></td>
                <td>{{ $salarySlip->faculty->FIRST_NAME }} {{ $salarySlip->faculty->LAST_NAME }}</td>
              </tr>
              <tr>
                <td><strong>Employee Code:</strong></td>
                <td>{{ $salarySlip->faculty->FACULTY_CODE }}</td>
              </tr>
              <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $salarySlip->faculty->EMAIL }}</td>
              </tr>
              <tr>
                <td><strong>Phone:</strong></td>
                <td>{{ $salarySlip->faculty->MOBILE_NO }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="mb-3">Slip Information</h6>
            <table class="table table-borderless">
              <tr>
                <td width="40%"><strong>Period:</strong></td>
                <td>{{ DateTime::createFromFormat('!m', $salarySlip->month)->format('F') }} {{ $salarySlip->year }}</td>
              </tr>
              <tr>
                <td><strong>Working Days:</strong></td>
                <td>{{ $salarySlip->working_days }}</td>
              </tr>
              <tr>
                <td><strong>Present Days:</strong></td>
                <td>{{ $salarySlip->present_days }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  @if($salarySlip->status == 'draft')
                  <span class="badge bg-warning">Draft</span>
                  @elseif($salarySlip->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($salarySlip->status == 'paid')
                  <span class="badge bg-primary">Paid</span>
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-md-6">
            <h6 class="mb-3 text-success">Earnings</h6>
            <table class="table table-bordered">
              <tr>
                <td>Basic Salary</td>
                <td class="text-end">₹{{ number_format($salarySlip->basic_salary, 2) }}</td>
              </tr>
              <tr>
                <td>Dearness Allowance (DA)</td>
                <td class="text-end">₹{{ number_format($salarySlip->da, 2) }}</td>
              </tr>
              <tr>
                <td>House Rent Allowance (HRA)</td>
                <td class="text-end">₹{{ number_format($salarySlip->hra, 2) }}</td>
              </tr>
              <tr>
                <td>Transport Allowance (TA)</td>
                <td class="text-end">₹{{ number_format($salarySlip->ta, 2) }}</td>
              </tr>
              <tr>
                <td>Medical Allowance</td>
                <td class="text-end">₹{{ number_format($salarySlip->medical_allowance, 2) }}</td>
              </tr>
              <tr>
                <td>Special Allowance</td>
                <td class="text-end">₹{{ number_format($salarySlip->special_allowance, 2) }}</td>
              </tr>
              <tr>
                <td>Other Allowances</td>
                <td class="text-end">₹{{ number_format($salarySlip->other_allowances, 2) }}</td>
              </tr>
              <tr class="table-success">
                <td><strong>Total Earnings</strong></td>
                <td class="text-end"><strong>₹{{ number_format($salarySlip->gross_salary, 2) }}</strong></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="mb-3 text-danger">Deductions</h6>
            <table class="table table-bordered">
              <tr>
                <td>Provident Fund (PF)</td>
                <td class="text-end">₹{{ number_format($salarySlip->pf, 2) }}</td>
              </tr>
              <tr>
                <td>Employee State Insurance (ESI)</td>
                <td class="text-end">₹{{ number_format($salarySlip->esi, 2) }}</td>
              </tr>
              <tr>
                <td>Professional Tax</td>
                <td class="text-end">₹{{ number_format($salarySlip->professional_tax, 2) }}</td>
              </tr>
              <tr>
                <td>Tax Deducted at Source (TDS)</td>
                <td class="text-end">₹{{ number_format($salarySlip->tds, 2) }}</td>
              </tr>
              <tr>
                <td>Loan Deduction</td>
                <td class="text-end">₹{{ number_format($salarySlip->loan_deduction, 2) }}</td>
              </tr>
              <tr>
                <td>Other Deductions</td>
                <td class="text-end">₹{{ number_format($salarySlip->other_deductions, 2) }}</td>
              </tr>
              <tr class="table-danger">
                <td><strong>Total Deductions</strong></td>
                <td class="text-end"><strong>₹{{ number_format($salarySlip->total_deductions, 2) }}</strong></td>
              </tr>
            </table>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-12">
            <div class="alert alert-info">
              <h5 class="mb-0">
                Net Salary: <strong class="float-end">₹{{ number_format($salarySlip->net_salary, 2) }}</strong>
              </h5>
            </div>
          </div>
        </div>

        @if($salarySlip->status == 'paid')
        <hr>
        <h6 class="mb-3">Payment Information</h6>
        <table class="table table-borderless">
          <tr>
            <td width="20%"><strong>Payment Date:</strong></td>
            <td>{{ $salarySlip->payment_date }}</td>
          </tr>
          <tr>
            <td><strong>Payment Mode:</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $salarySlip->payment_mode)) }}</td>
          </tr>
          <tr>
            <td><strong>Payment Reference:</strong></td>
            <td>{{ $salarySlip->payment_reference ?? 'N/A' }}</td>
          </tr>
        </table>
        @endif

        @if($salarySlip->remarks)
        <hr>
        <h6 class="mb-2">Remarks</h6>
        <p class="text-muted">{{ $salarySlip->remarks }}</p>
        @endif
      </div>
    </div>
  </div>
</main>
<!--end main wrapper-->

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('hr.payroll.mark-paid', $salarySlip->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Mark Salary as Paid</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
            <select name="payment_mode" class="form-select" required>
              <option value="">Select Payment Mode</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cash">Cash</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Reference</label>
            <input type="text" name="payment_reference" class="form-control"
              placeholder="Transaction ID / Cheque Number">
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
@endsection
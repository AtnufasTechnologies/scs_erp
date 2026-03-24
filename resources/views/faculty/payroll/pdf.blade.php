<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salary Slip - {{ $salarySlip->salary_slip_number }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      line-height: 1.6;
      color: #333;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #2c3e50;
      padding-bottom: 15px;
    }

    .header h1 {
      font-size: 20px;
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .header h2 {
      font-size: 16px;
      color: #7f8c8d;
      font-weight: normal;
    }

    .header h3 {
      font-size: 14px;
      color: #7f8c8d;
      font-weight: normal;
    }

    .header img {
      margin-bottom: 10px;
      height: 80px;
    }

    .slip-info {
      margin-bottom: 20px;
      background: #ecf0f1;
      padding: 15px;
      border-radius: 5px;
    }

    .slip-info table {
      width: 100%;
    }

    .slip-info td {
      padding: 5px;
    }

    .slip-info td:first-child {
      font-weight: bold;
      width: 40%;
    }

    .section-title {
      background: #34495e;
      color: white;
      padding: 8px 10px;
      margin-top: 20px;
      margin-bottom: 10px;
      font-weight: bold;
      font-size: 14px;
    }

    .salary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .salary-table th,
    .salary-table td {
      border: 1px solid #bdc3c7;
      padding: 8px;
      text-align: left;
    }

    .salary-table th {
      background: #ecf0f1;
      font-weight: bold;
    }

    .salary-table td:last-child {
      text-align: right;
    }

    .total-row {
      background: #ecf0f1;
      font-weight: bold;
    }

    .earnings-section {
      float: left;
      width: 48%;
    }

    .deductions-section {
      float: right;
      width: 48%;
    }

    .clearfix::after {
      content: "";
      display: table;
      clear: both;
    }

    .net-salary {
      clear: both;
      background: #ecf0f1;
      color: white;
      padding: 15px;
      text-align: center;
      margin-top: 20px;
      border-radius: 5px;
    }

    .net-salary h3 {
      font-size: 16px;
      margin-bottom: 5px;
      color: #333;
    }

    .net-salary .amount {
      font-size: 28px;
      font-weight: bold;
      color: #333;
    }

    .attendance-section {
      margin-top: 20px;
      padding: 15px;
      background: #e8f5e9;
      border-left: 4px solid #4e67be;
    }

    .attendance-section table {
      width: 100%;
    }

    .attendance-section td {
      padding: 5px;
      text-align: center;
    }

    .footer {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 2px solid #ecf0f1;
      text-align: center;
      font-size: 10px;
      color: #7f8c8d;
    }

    .signature-section {
      margin-top: 50px;
      display: flex;
      justify-content: space-between;
    }

    .signature-box {
      width: 45%;
      text-align: center;
      border-top: 1px solid #333;
      padding-top: 5px;
    }

    .status-badge {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .status-paid {
      background: #27ae60;
      color: white;
    }

    .status-approved {
      background: #3498db;
      color: white;
    }

    .status-draft {
      background: #95a5a6;
      color: white;
    }

    .payment-info {
      margin-top: 20px;
      padding: 15px;
      background: #fff9e6;
      border-left: 4px solid #f39c12;
    }

    .remarks-section {
      margin-top: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-left: 4px solid #6c757d;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Salesian College (Autonomous)</h1>
    <h3>Sonada and Siliguri</h3>
    <h2>Salary Slip</h2>
  </div>

  <div class="slip-info">
    <table>
      <tr>
        <td>Slip Number:</td>
        <td><strong>{{ $salarySlip->salary_slip_number }}</strong></td>
        <td>Month/Year:</td>
        <td><strong>{{ $salarySlip->month_year }}</strong></td>
      </tr>
      <tr>
        <td>Faculty Name:</td>
        <td>{{ $salarySlip->faculty->USER_CODE ?? 'N/A' }} - {{ $salarySlip->faculty->FIRST_NAME ?? 'N/A' }} {{ $salarySlip->faculty->LAST_NAME ?? 'N/A' }}</td>
        <td>Status:</td>
        <td>
          <span class="status-badge status-{{ $salarySlip->status }}">
            {{ ucfirst($salarySlip->status) }}
          </span>
        </td>
      </tr>
      @if($salarySlip->faculty->department)
      <tr>
        <td>Department:</td>
        <td colspan="3">{{ $salarySlip->faculty->department }}</td>
      </tr>
      @endif
    </table>
  </div>

  <div class="clearfix">
    <!-- Earnings Section -->
    <div class="earnings-section">
      <div class="section-title">EARNINGS</div>
      <table class="salary-table">
        <thead>
          <tr>
            <th>Particulars</th>
            <th style="width: 35%;">Amount (₹)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Basic Salary</td>
            <td>{{ number_format($salarySlip->basic_salary, 2) }}</td>
          </tr>
          @if($salarySlip->da > 0)
          <tr>
            <td>Dearness Allowance (DA)</td>
            <td>{{ number_format($salarySlip->da, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->hra > 0)
          <tr>
            <td>House Rent Allowance (HRA)</td>
            <td>{{ number_format($salarySlip->hra, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->ta > 0)
          <tr>
            <td>Transport Allowance (TA)</td>
            <td>{{ number_format($salarySlip->ta, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->medical_allowance > 0)
          <tr>
            <td>Medical Allowance</td>
            <td>{{ number_format($salarySlip->medical_allowance, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->special_allowance > 0)
          <tr>
            <td>Special Allowance</td>
            <td>{{ number_format($salarySlip->special_allowance, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->other_allowances > 0)
          <tr>
            <td>Other Allowances</td>
            <td>{{ number_format($salarySlip->other_allowances, 2) }}</td>
          </tr>
          @endif
          <tr class="total-row">
            <td><strong>GROSS SALARY</strong></td>
            <td><strong>{{ number_format($salarySlip->gross_salary, 2) }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Deductions Section -->
    <div class="deductions-section">
      <div class="section-title">DEDUCTIONS</div>
      <table class="salary-table">
        <thead>
          <tr>
            <th>Particulars</th>
            <th style="width: 35%;">Amount (₹)</th>
          </tr>
        </thead>
        <tbody>
          @if($salarySlip->pf > 0)
          <tr>
            <td>Provident Fund (PF)</td>
            <td>{{ number_format($salarySlip->pf, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->esi > 0)
          <tr>
            <td>Employee State Insurance (ESI)</td>
            <td>{{ number_format($salarySlip->esi, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->professional_tax > 0)
          <tr>
            <td>Professional Tax</td>
            <td>{{ number_format($salarySlip->professional_tax, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->tds > 0)
          <tr>
            <td>Tax Deducted at Source (TDS)</td>
            <td>{{ number_format($salarySlip->tds, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->loan_deduction > 0)
          <tr>
            <td>Loan Deduction</td>
            <td>{{ number_format($salarySlip->loan_deduction, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->other_deductions > 0)
          <tr>
            <td>Other Deductions</td>
            <td>{{ number_format($salarySlip->other_deductions, 2) }}</td>
          </tr>
          @endif
          @if($salarySlip->total_deductions == 0)
          <tr>
            <td colspan="2" style="text-align: center; padding: 30px;">No deductions</td>
          </tr>
          @endif
          <tr class="total-row">
            <td><strong>TOTAL DEDUCTIONS</strong></td>
            <td><strong>{{ number_format($salarySlip->total_deductions, 2) }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="net-salary">
    <h3>NET SALARY PAYABLE</h3>
    <div class="amount">INR {{ number_format($salarySlip->net_salary, 2) }}</div>
    <p style="margin-top: 10px; font-size: 14px;color: #555;">
      In Words: <strong>{{ ucwords(\App\Helpers\Qs::numberToWords($salarySlip->net_salary)) }} Only</strong>
    </p>
  </div>

  <!-- Attendance Information -->
  <div class="attendance-section">
    <div class="section-title" style="background: #4caf50; margin-top: 0;">ATTENDANCE INFORMATION</div>
    <table>
      <tr>
        <td><strong>Working Days:</strong> {{ $salarySlip->working_days }}</td>
        <td><strong>Present Days:</strong> {{ $salarySlip->present_days }}</td>
        <td><strong>Leave Days:</strong> {{ $salarySlip->leave_days }}</td>
      </tr>
    </table>
  </div>

  <!-- Payment Information -->
  @if($salarySlip->status == 'paid' && $salarySlip->payment_date)
  <div class="payment-info">
    <div class="section-title" style="background: #f39c12; margin-top: 0;">PAYMENT INFORMATION</div>
    <table style="width: 100%;">
      <tr>
        <td><strong>Payment Date:</strong> {{ $salarySlip->payment_date->format('d F Y') }}</td>
        @if($salarySlip->payment_mode)
        <td><strong>Payment Mode:</strong> {{ ucfirst($salarySlip->payment_mode) }}</td>
        @endif
        @if($salarySlip->payment_reference)
        <td><strong>Reference:</strong> {{ $salarySlip->payment_reference }}</td>
        @endif
      </tr>
    </table>
  </div>
  @endif

  <!-- Remarks -->
  @if($salarySlip->remarks)
  <div class="remarks-section">
    <strong>Remarks:</strong> {{ $salarySlip->remarks }}
  </div>
  @endif

  <!-- Signature Section -->
  <!-- <div class="signature-section">
    <div class="signature-box">
      <p>Faculty Signature</p>
    </div>
    <div class="signature-box">
      <p>Authorized Signatory</p>
    </div>
  </div> -->

  <div class="footer">
    <p>This is a computer-generated salary slip and does not require a physical signature.</p>
    <p>Generated on {{ now()->format('d F Y, h:i A') }}</p>
    @if($salarySlip->approved_by && $salarySlip->approved_at)
    <p style="margin-top: 10px;">
      Approved by: {{ $salarySlip->approver->name ?? 'N/A' }} on {{ $salarySlip->approved_at->format('d F Y, h:i A') }}
    </p>
    @endif
  </div>
</body>

</html>
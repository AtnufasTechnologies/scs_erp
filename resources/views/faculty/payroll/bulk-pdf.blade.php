<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salary Slips Summary - {{ $year }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      line-height: 1.5;
      color: #333;
      padding: 15px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 3px solid #2c3e50;
      padding-bottom: 15px;
    }

    .header h1 {
      font-size: 22px;
      color: #2c3e50;
      margin-bottom: 3px;
    }

    .header h2 {
      font-size: 16px;
      color: #7f8c8d;
      font-weight: normal;
    }

    .header p {
      margin-top: 5px;
      font-size: 12px;
    }

    .summary-cards {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      gap: 10px;
    }

    .summary-card {
      flex: 1;
      padding: 12px;
      border-radius: 5px;
      text-align: center;
    }

    .summary-card.earnings {
      background: #d5f4e6;
      border: 2px solid #27ae60;
    }

    .summary-card.deductions {
      background: #fadbd8;
      border: 2px solid #e74c3c;
    }

    .summary-card.net {
      background: #d6eaf8;
      border: 2px solid #3498db;
    }

    .summary-card h3 {
      font-size: 10px;
      color: #666;
      margin-bottom: 5px;
      text-transform: uppercase;
    }

    .summary-card .amount {
      font-size: 18px;
      font-weight: bold;
    }

    .summary-card.earnings .amount {
      color: #27ae60;
    }

    .summary-card.deductions .amount {
      color: #e74c3c;
    }

    .summary-card.net .amount {
      color: #3498db;
    }

    .summary-card p {
      font-size: 9px;
      color: #666;
      margin-top: 3px;
    }

    .slip-section {
      page-break-inside: avoid;
      margin-bottom: 30px;
      border: 1px solid #ddd;
      padding: 15px;
      background: white;
    }

    .slip-header {
      background: #34495e;
      color: white;
      padding: 8px 10px;
      margin: -15px -15px 15px -15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .slip-header h3 {
      font-size: 13px;
      margin: 0;
    }

    .status-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 9px;
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

    .salary-details {
      display: flex;
      gap: 15px;
    }

    .earnings-col,
    .deductions-col {
      flex: 1;
    }

    .detail-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }

    .detail-table th {
      background: #ecf0f1;
      padding: 6px;
      text-align: left;
      font-size: 10px;
      border: 1px solid #bdc3c7;
    }

    .detail-table td {
      padding: 5px 6px;
      border: 1px solid #ddd;
      font-size: 10px;
    }

    .detail-table td:last-child {
      text-align: right;
    }

    .total-row {
      background: #ecf0f1;
      font-weight: bold;
    }

    .net-amount {
      background: #3498db;
      color: white;
      padding: 10px;
      text-align: center;
      margin-top: 10px;
      border-radius: 3px;
    }

    .net-amount .label {
      font-size: 10px;
      margin-bottom: 3px;
    }

    .net-amount .value {
      font-size: 16px;
      font-weight: bold;
    }

    .attendance-bar {
      display: flex;
      gap: 10px;
      margin-top: 10px;
      padding: 8px;
      background: #f8f9fa;
      border-radius: 3px;
    }

    .attendance-item {
      flex: 1;
      text-align: center;
      font-size: 9px;
    }

    .attendance-item strong {
      display: block;
      font-size: 12px;
      color: #2c3e50;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #ecf0f1;
      text-align: center;
      font-size: 9px;
      color: #7f8c8d;
    }

    .page-break {
      page-break-after: always;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>{{ config('app.name', 'Institution Name') }}</h1>
    <h2>Annual Salary Statement - {{ $year }}</h2>
    <p><strong>Faculty:</strong> {{ $faculty->title ?? 'N/A' }}</p>
  </div>

  <!-- Summary Section -->
  <div class="summary-cards">
    <div class="summary-card earnings">
      <h3>Total Earnings {{ $year }}</h3>
      <div class="amount">₹{{ number_format($totalEarnings, 2) }}</div>
      <p>Gross salary for the year</p>
    </div>

    <div class="summary-card deductions">
      <h3>Total Deductions {{ $year }}</h3>
      <div class="amount">₹{{ number_format($totalDeductions, 2) }}</div>
      <p>All deductions combined</p>
    </div>

    <div class="summary-card net">
      <h3>Total Net Pay {{ $year }}</h3>
      <div class="amount">₹{{ number_format($totalNetPay, 2) }}</div>
      <p>Amount received</p>
    </div>
  </div>

  <div style="margin-bottom: 20px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; font-size: 10px;">
    <strong>Note:</strong> This document contains {{ $salarySlips->count() }} salary slip(s) for the year {{ $year }}.
    Generated on {{ now()->format('d F Y, h:i A') }}.
  </div>

  <!-- Individual Salary Slips -->
  @foreach($salarySlips as $index => $slip)
  <div class="slip-section">
    <div class="slip-header">
      <h3>{{ $slip->month_year }} - {{ $slip->salary_slip_number }}</h3>
      <span class="status-badge status-{{ $slip->status }}">{{ ucfirst($slip->status) }}</span>
    </div>

    <div class="salary-details">
      <!-- Earnings Column -->
      <div class="earnings-col">
        <table class="detail-table">
          <thead>
            <tr>
              <th colspan="2" style="background: #27ae60; color: white;">EARNINGS</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Basic Salary</td>
              <td>₹{{ number_format($slip->basic_salary, 2) }}</td>
            </tr>
            @if($slip->da > 0)
            <tr>
              <td>DA</td>
              <td>₹{{ number_format($slip->da, 2) }}</td>
            </tr>
            @endif
            @if($slip->hra > 0)
            <tr>
              <td>HRA</td>
              <td>₹{{ number_format($slip->hra, 2) }}</td>
            </tr>
            @endif
            @if($slip->ta > 0)
            <tr>
              <td>TA</td>
              <td>₹{{ number_format($slip->ta, 2) }}</td>
            </tr>
            @endif
            @if($slip->medical_allowance > 0)
            <tr>
              <td>Medical Allowance</td>
              <td>₹{{ number_format($slip->medical_allowance, 2) }}</td>
            </tr>
            @endif
            @if($slip->special_allowance > 0)
            <tr>
              <td>Special Allowance</td>
              <td>₹{{ number_format($slip->special_allowance, 2) }}</td>
            </tr>
            @endif
            @if($slip->other_allowances > 0)
            <tr>
              <td>Other Allowances</td>
              <td>₹{{ number_format($slip->other_allowances, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
              <td><strong>Gross</strong></td>
              <td><strong>₹{{ number_format($slip->gross_salary, 2) }}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Deductions Column -->
      <div class="deductions-col">
        <table class="detail-table">
          <thead>
            <tr>
              <th colspan="2" style="background: #e74c3c; color: white;">DEDUCTIONS</th>
            </tr>
          </thead>
          <tbody>
            @if($slip->pf > 0)
            <tr>
              <td>PF</td>
              <td>₹{{ number_format($slip->pf, 2) }}</td>
            </tr>
            @endif
            @if($slip->esi > 0)
            <tr>
              <td>ESI</td>
              <td>₹{{ number_format($slip->esi, 2) }}</td>
            </tr>
            @endif
            @if($slip->professional_tax > 0)
            <tr>
              <td>Professional Tax</td>
              <td>₹{{ number_format($slip->professional_tax, 2) }}</td>
            </tr>
            @endif
            @if($slip->tds > 0)
            <tr>
              <td>TDS</td>
              <td>₹{{ number_format($slip->tds, 2) }}</td>
            </tr>
            @endif
            @if($slip->loan_deduction > 0)
            <tr>
              <td>Loan Deduction</td>
              <td>₹{{ number_format($slip->loan_deduction, 2) }}</td>
            </tr>
            @endif
            @if($slip->other_deductions > 0)
            <tr>
              <td>Other Deductions</td>
              <td>₹{{ number_format($slip->other_deductions, 2) }}</td>
            </tr>
            @endif
            @if($slip->total_deductions == 0)
            <tr>
              <td colspan="2" style="text-align: center; padding: 20px; color: #999;">No deductions</td>
            </tr>
            @endif
            <tr class="total-row">
              <td><strong>Total</strong></td>
              <td><strong>₹{{ number_format($slip->total_deductions, 2) }}</strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="net-amount">
      <div class="label">NET SALARY PAYABLE</div>
      <div class="value">₹{{ number_format($slip->net_salary, 2) }}</div>
    </div>

    <div class="attendance-bar">
      <div class="attendance-item">
        <strong>{{ $slip->working_days }}</strong>
        Working Days
      </div>
      <div class="attendance-item">
        <strong>{{ $slip->present_days }}</strong>
        Present
      </div>
      <div class="attendance-item">
        <strong>{{ $slip->leave_days }}</strong>
        Leaves
      </div>
      @if($slip->status == 'paid' && $slip->payment_date)
      <div class="attendance-item">
        <strong>{{ $slip->payment_date->format('d M Y') }}</strong>
        Payment Date
      </div>
      @endif
    </div>
  </div>

  @if(!$loop->last && ($index + 1) % 2 == 0)
  <div class="page-break"></div>
  @endif
  @endforeach

  <div class="footer">
    <p>This is a computer-generated annual salary statement.</p>
    <p>Generated on {{ now()->format('d F Y, h:i A') }}</p>
    <p style="margin-top: 10px;">
      <strong>{{ config('app.name', 'Institution Name') }}</strong> | Confidential Document
    </p>
  </div>
</body>

</html>
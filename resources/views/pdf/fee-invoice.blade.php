<?php

use Carbon\Carbon;

$timestamp = Carbon::now()
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Fee Invoice</title>

  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      margin: 40px;
      color: #000;
    }

    .header {
      text-align: center;
      margin-bottom: 25px;
    }

    .header h1 {
      margin: 0;
      font-size: 28px;
    }

    .sub-text {
      font-size: 13px;
      margin-top: 3px;
    }

    .title {
      text-align: center;
      font-size: 22px;
      margin-top: 15px;
      margin-bottom: 15px;
      font-weight: bold;
      text-transform: uppercase;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      margin-bottom: 20px;
    }

    table th,
    table td {
      border: 1px solid #555;
      padding: 8px;
      font-size: 13px;
    }

    table th {
      background: #eaeaea;
      text-align: left;
    }

    .section-title {
      font-size: 16px;
      font-weight: bold;
      margin-top: 25px;
      margin-bottom: 8px;
    }

    .footer {
      font-size: 13px;
      margin-top: 40px;
    }
  </style>
</head>

<body>

  <div class="header">
    <h1>SALESIAN COLLEGE AUTONOMOUS</h1>
    <div class="sub-text">Sonada • Siliguri Campus</div>
    <div class="sub-text">accounts.office@salesiancollege.net • +91-9000000000</div>
  </div>

  <div class="title">Fee Payment Information</div>


  <p><strong>Generated On:</strong> {{ now()->format('d M Y') }}</p>
  <p><strong>Payment Verified By:</strong> Accounts Department</p>

  {{-- ===================== STUDENT INFORMATION ===================== --}}
  <div class="section-title">Student Information</div>

  <table>
    <tr>
      <th style="width: 25%">Name</th>
      <td>{{ $student->first_name }} {{ $student->last_name }}</td>
    </tr>
    <tr>
      <th>Roll No</th>
      <td>{{ $student->roll_no }}</td>
    </tr>
    <tr>
      <th>Course</th>
      <td> {{ $student->programGroup->programInfo->name }}</td>
    </tr>
    <tr>
      <th>Batch</th>
      <td>{{ $student->batchmaster->batch_name }}</td>
    </tr>
    <tr>
      <th>Campus</th>
      <td>{{ $student->campusmaster->name }}</td>
    </tr>
    <tr>
      <th>Current Year</th>
      <td>{{ $student->current_year }}</td>
    </tr>
  </table>

  {{-- ===================== PAYMENT BREAKDOWN ===================== --}}
  <div class="section-title">Payment Breakdown</div>

  <table>
    <thead>
      <tr>
        <th>Paid On</th>
        <th>Invoice #</th>
        <th>Quarter</th>
        <th>Amount</th>
        <th>Status</th>


      </tr>
    </thead>
    <tbody>
      @foreach($paidInvoices as $row)
      <tr>
        <td>{{ $row['paid_on'] }}</td>
        <td>{{ $row['inv_id'] }}</td>
        <td>{{ $row['quarter'] }}</td>
        <td>₹{{ number_format($row['payable_amount']) }}</td>
        <td>{{ $row['status'] }}</td>


      </tr>
      @endforeach
    </tbody>
  </table>

  <h3><strong>Total Paid: ₹{{ number_format($total_paid) }}</strong></h3>

  {{-- ===================== NOTES ===================== --}}
  <div class="section-title">Notes:</div>

  <p class="footer">
    • This is a computer-generated invoice and does not require a signature.<br>
    • Payments once made are non-refundable.<br>
    • For any discrepancies, please contact the Accounts Department within 7 working days. <br>

  </p>

</body>

</html>
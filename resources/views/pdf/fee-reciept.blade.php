<?php

use Carbon\Carbon;

$timestamp = Carbon::now()
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Payment Receipt</title>

  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      margin: 40px;
    }

    .header {
      text-align: center;
      margin-bottom: 25px;
    }

    .header h1 {
      margin: 0;
      font-size: 28px;
    }

    .title {
      text-align: center;
      font-size: 22px;
      margin: 20px 0;
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    table th,
    table td {
      border: 1px solid #555;
      padding: 8px;
      font-size: 13px;
    }

    table th {
      background: #eaeaea;
    }

    .section-title {
      margin-top: 30px;
      font-weight: bold;
      font-size: 16px;
    }

    .footer {
      margin-top: 40px;
      font-size: 13px;
    }
  </style>
</head>

<body>

  {{-- Header --}}
  <div class="header">
    <h1>SALESIAN COLLEGE AUTONOMOUS</h1>
    <div>Sonada • Siliguri Campus</div>
    <div>account.office@salesiancollege.net | +91-9000000000</div>
  </div>

  <div class="title">Fee Payment Receipt</div>

  <p><strong>Invoice #:</strong> {{ $payment->invoice_id }}</p>
  <p><strong>Payment Date:</strong> {{ $payment->transaction_date ?? 'N/A' }}</p>

  <p>

    <?php
    if ($payment->gateway_id == 1) {
      $gateway = 'Easebuzz';
    } else if ($payment->gateway_id == 3) {
      $gateway = 'Billdesk';
    } else {
      $gateway = 'Cash';
    }
    ?>
    <strong>Payment Mode: </strong> {{$gateway}} {{ $payment->gateway_ref_code != null ?? ''}}

  </p>

  {{-- Student Info --}}
  <div class="section-title">Student Information</div>

  <table>
    <tr>
      <th>Name</th>
      <td>{{ $student->first_name }} {{ $student->last_name }}</td>
    </tr>
    <tr>
      <th>Roll No</th>
      <td>{{$student->roll_no }}</span></td>
    </tr>

    <tr>
      <th>Batch</th>
      <td>{{ $student->batchmaster->batch_name }}</td>
    </tr>

    <tr>
      <th>Course</th>
      <td>{{ $student->programGroup->programInfo->name }}</td>
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

  {{-- Payment Info --}}
  <div class="section-title">Payment Details</div>

  <table>
    <tr>
      <th>Quarter</th>
      <td>{{ $fee->quarter_title }}</td>
    </tr>
    <tr>
      <th>Amount Paid</th>
      <td>₹{{ number_format($amount) }}</td>
    </tr>
    <tr>
      <th>Status</th>
      <td>PAID</td>
    </tr>

  </table>

  <h3>Total Paid: ₹{{ number_format($amount) }}</h3>

  {{-- Footer Notes --}}
  <div class="footer">
    • This is a computer-generated receipt and does not require a signature.<br>
    • Payments once made are non-refundable.<br>
    • For issues, contact the Accounts Department within 7 days. <br>
    • Print Timestamp {{$timestamp}}
  </div>

</body>

</html>
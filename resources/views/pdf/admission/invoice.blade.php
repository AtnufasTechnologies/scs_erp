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
    <img src="{{ asset('admin/images/logo.png') }}" alt="logo" style="height: 90px;">
    <h1>SALESIAN COLLEGE AUTONOMOUS</h1>
    <div>Sonada • Siliguri Campus</div>
    <div>admissionenquiry@salesiancollege.net | +91 99334 02478 / 0353 254 5622</div>
  </div>

  <div class="title">Application Payment Receipt</div>
  <button onclick="window.print()" style="padding: 6px 12px; background: #0d47a1; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 10px; font-weight: 600;">Print</button>
  <p><strong>Form No #:</strong> {{ $data->application_code }}</p>
  <p><strong>Payment Date:</strong> {{ $data->updated_at ?? 'N/A' }}</p>



  {{-- Student Info --}}
  <div class="section-title">Applicant Information</div>

  <table>
    <tr>
      <th>Name</th>
      <td>{{ $data->registrationmaster->first_name }} {{ $data->registrationmaster->last_name }}</td>
    </tr>
    <tr>
      <th>Email</th>
      <td>{{$data->registrationmaster->mail_id }}</span></td>
    </tr>
    <tr>
      <th>Mobile No</th>
      <td>{{$data->registrationmaster->mobile_no }}</td>
    </tr>

    <tr>
      <th>Batch</th>
      <td>{{ $data->registrationmaster->batch }}</td>
    </tr>

    <tr>
      <th>Course</th>
      <td>{{ $data->stdCourseMaster->name }}</td>
    </tr>
    <tr>
      <th>Campus</th>
      <td>{{ $data->registrationmaster->campusmaster->name }}</td>
    </tr>

  </table>

  {{-- Payment Info --}}
  <div class="section-title">Payment Details</div>

  <table>
    <tr>
      <th>Transaction#</th>
      <td>{{ $data->payment_gateway_ref != null ? $data->payment_gateway_ref : 'N/A'}}</td>
    </tr>
    <tr>
      <th>Transaction Detail</th>
      <td>Admission Application Fee - {{ $data->registrationmaster->batch }}</td>
    </tr>

    <tr>
      <th>Transaction Status</th>
      <td>{{$data->payment_gateway_status}}</td>
    </tr>

  </table>

  <h3>Total Paid: ₹{{ number_format($data->captured_amount) }}</h3>

  {{-- Footer Notes --}}
  <div class="footer">
    • This is a computer-generated receipt and does not require a signature.<br>
    • Payments once made are non-refundable.<br>
    • For issues, contact the Accounts Department within 7 days. <br>
    • Print Timestamp {{$timestamp}}
  </div>

</body>

</html>
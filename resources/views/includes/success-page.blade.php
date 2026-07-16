<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$userInfo = null;
if (Auth::check()) {
  $userId = Auth::user()->id;
  $userInfo = User::select('name')->find($userId);
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Invoice {{ $invoiceId ?? 'N/A' }}</title>
  <link rel="stylesheet" href="{{asset('admin/css/inv.css')}}">
  <style>
    .btn-download-pdf {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #1976d2;
      color: #fff;
      font-weight: 700;
      padding: 8px 18px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }

    .btn-download-pdf:hover {
      background: #1565c0;
      color: #fff;
    }

    .btn-print-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #555;
      color: #fff;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-print-link:hover {
      background: #333;
      color: #fff;
    }

    .action-bar {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
  </style>
</head>

<body>
  <div class="container">

    {{-- ACTION BAR - Hide for PDF --}}
    @if(!isset($isPdf) || !$isPdf)
    <div class="action-bar">
      <a onclick="window.print()" style="cursor:pointer" class="fa fa-print btn-print-link">
        &#128438; Print
      </a>
    </div>
    @endif

    <div class="header">
      <div>
        <div class="title">INVOICE</div>
        <p>
          <strong>Invoice No:</strong> {{ $invoiceId }}<br>
          <strong>Date:</strong> {{ date('d-m-Y', strtotime($transactionDate)) }}<br>

          @if(isset($gatewayType) && ($gatewayType == 1 || $gatewayType == 2))
          <strong>Gateway Ref:</strong> {{ $gatewayRef ?? 'N/A' }} <br>
          @endif
          @if(isset($gatewayType))
          <strong>Payment Mode:</strong>
          @if ($gatewayType == 1)
          EaseBuzz
          @elseif ($gatewayType == 2)
          BillDesk
          @elseif ($gatewayType == 3)
          Cash
          @elseif ($gatewayType == 4)
          Offline
          @else
          Not Specified
          @endif
          @endif


        </p>
      </div>

      <div class="company">
        <img src="{{asset('admin/images/logo.png')}}" alt="logo">
        <strong>Salesian College Autonomous</strong><br>
        Sonada & Siliguri<br>

        Phone: 76020 32968(Sonada) <br>0353 254 5622(Siliguri) <br>
        accountsoffice@salesiancollege.net
      </div>
    </div>

    {{-- STUDENT --}}
    <div class="section">
      <strong>Billed To</strong><br>
      {{ ucFirst($student['first_name']) }} {{ ucFirst($student['last_name']) }}<br>
      Roll No: {{ strtoupper($student['roll_no']) }}<br>
      Mobile: {{ $student['mobile_no'] }}<br>
      Email: {{ $student['mail_id'] }}
    </div>
    {{-- FEE STRUCTURES --}}
    @php $grandTotal = 0; @endphp


    @foreach($transactions as $txn)
    @php
    $structure = $txn['feepaymentinfo'];
    $courseFee = 0;
    $otherFees = 0;
    $feeStructureId = $txn['fee_structure_id'] ?? null;
    $fixedLateFeeMap = $fixedLateFeeMap ?? [];
    $hasFixedOverride = !is_null($feeStructureId) && array_key_exists($feeStructureId, $fixedLateFeeMap);
    $txnLateFee = $hasFixedOverride
    ? (float)$fixedLateFeeMap[$feeStructureId]
    : (float)($txn['late_fee_amount'] ?? 0);
    $txnLateDays = (int)($txn['late_days'] ?? 0);
    @endphp

    {{-- CALCULATE --}}
    @foreach($structure['fee_heads'] as $head)
    @if($head['fee_head_id'] == 17)
    @php $courseFee += $head['amount']; @endphp
    @else
    @php $otherFees += $head['amount']; @endphp
    @endif
    @endforeach

    <div class="section">
      <div class="section-title">
        {{ strtoupper($structure['quarter_title']) }}
      </div>

      <table>
        @if($courseFee > 0)
        <tr>
          <td>Course Fee</td>
          <td class="amount">₹{{ number_format($courseFee, 2) }}</td>
        </tr>
        @endif

        @if($otherFees > 0)
        <tr>
          <td>Others</td>
          <td class="amount">₹{{ number_format($otherFees, 2) }}</td>
        </tr>
        @endif

        <tr class="subtotal">
          <td>Subtotal</td>
          <td class="amount">₹{{ number_format($courseFee + $otherFees, 2) }}</td>
        </tr>

        @if($txnLateFee > 0)
        <tr>
          <td>
            <strong>{{ $hasFixedOverride ? 'Fixed Late Fee (Exemption Applied)' : 'Late Fee' }}</strong>
            @if($txnLateDays > 0)
            <span style="font-size:11px; color:#888;"> ({{ $txnLateDays }} day{{ $txnLateDays > 1 ? 's' : '' }} overdue)</span>
            @endif
          </td>
          <td class="amount" style="color:#c0392b; font-weight:700;">₹{{ number_format($txnLateFee, 2) }}</td>
        </tr>
        <tr class="subtotal">
          <td>Quarter Total</td>
          <td class="amount">₹{{ number_format($courseFee + $otherFees + $txnLateFee, 2) }}</td>
        </tr>
        @endif
      </table>
    </div>

    @php $grandTotal += ($courseFee + $otherFees + $txnLateFee); @endphp
    @endforeach

    {{-- GRAND TOTAL --}}
    <table>
      <tr class="grand-total">
        <td style="padding:15px">Grand Total Paid</td>
        <td class="amount" style="padding:15px">
          ₹{{ number_format($grandTotal, 2) }}
        </td>
      </tr>
    </table>

    {{-- STATUS --}}
    @php
    $statusMap = [
    'success' => ['PAID', 'status-success'],
    'pending' => ['PENDING', 'status-pending'],
    'failed' => ['FAILED', 'status-failed'],
    'refunded' => ['REFUNDED', 'status-refunded'],
    ];
    $label = $statusMap[$status][0] ?? strtoupper($status);
    $class = $statusMap[$status][1] ?? '';
    @endphp

    <div class="section">
      <strong>Payment Status:</strong>
      <span class="{{ $class }}">{{ $label }}</span>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
      This is a system-generated invoice. No signature required. <br>
      For any discrepancies, please contact the Accounts Department within 7 working days. <br>
      @if(isset($userInfo))
      Print Date: {{ date('d-m-Y') }} by ({{ $userInfo->name }})<br>
      @else
      Print Date: {{ date('d-m-Y') }}<br>
      @endif
      Generated by SCS ERP
    </div>

  </div>

</body>

</html>
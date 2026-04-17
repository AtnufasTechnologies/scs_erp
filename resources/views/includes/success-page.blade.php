<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$userId = Auth::user()->id;
$userInfo = User::select('name')->find($userId);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Invoice {{ $invoiceId }}</title>
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

    {{-- ACTION BAR --}}
    <div class="action-bar">
      <a onclick="window.print()" style="cursor:pointer" class="btn-print-link">
        &#128438; Print
      </a>
      @if(isset($downloadPdfUrl))
      <a href="{{ $downloadPdfUrl }}" class="btn-download-pdf" id="pdfDownloadBtn">
        &#128196; Download PDF Invoice
      </a>
      @endif
    </div>

    <div class="header">
      <div>
        <div class="title">INVOICE</div>
        <p>
          <strong>Invoice No:</strong> {{ $invoiceId }}<br>
          <strong>Date:</strong> {{ date('d-m-Y', strtotime($transactionDate)) }}<br>

          @if($gatewayType == 1 || $gatewayType == 2 )
          <strong>Gateway Ref:</strong> {{ $gatewayRef }} <br>
          @endif
          <strong>Payment Mode:</strong>
          @if ($gatewayType == 1)
          EaseBuzz
          @endif

          @if ($gatewayType == 2)
          BillDesk
          @endif

          @if ($gatewayType == 3)
          Cash
          @endif

          @if ($gatewayType == 4)
          Offline
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
          <td class="amount">
            ₹{{ number_format($courseFee + $otherFees, 2) }}
          </td>
        </tr>
        @if(isset($fixedLateFee) && $fixedLateFee !== null)
        <tr>
          <td><strong>Fixed Late Fee (Exemption)</strong></td>
          <td class="amount">₹{{ number_format($fixedLateFee, 2) }}</td>
        </tr>
        @endif
      </table>
    </div>

    @php $grandTotal += ($courseFee + $otherFees); @endphp
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
      Print Date: {{ date('d-m-Y') }} by ({{ $userInfo->name }})<br>
      Generated by SCS ERP
    </div>

  </div>

  @if(isset($downloadPdfUrl))
  <script>
    // Auto-trigger PDF download 2 seconds after page loads (only on success status)
    @if(isset($status) && $status === 'success')
    window.addEventListener('load', function() {
      setTimeout(function() {
        var link = document.createElement('a');
        link.href = '{{ $downloadPdfUrl }}';
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }, 2000);
    });
    @endif
  </script>
  @endif

</body>

</html>
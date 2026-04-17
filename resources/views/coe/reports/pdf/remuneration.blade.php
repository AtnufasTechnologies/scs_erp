<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #333;
    }

    h2 {
      text-align: center;
      margin-bottom: 5px;
    }

    .info {
      text-align: center;
      color: #666;
      margin-bottom: 15px;
      font-size: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th {
      background-color: #2c3e50;
      color: #fff;
      padding: 8px 6px;
      text-align: left;
      font-size: 10px;
    }

    td {
      padding: 6px;
      border-bottom: 1px solid #ddd;
      font-size: 10px;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .badge {
      padding: 2px 8px;
      border-radius: 3px;
      color: #fff;
      font-size: 9px;
    }

    .bg-success {
      background-color: #28a745;
    }

    .bg-warning {
      background-color: #ffc107;
      color: #333;
    }

    .bg-info {
      background-color: #17a2b8;
    }

    .text-right {
      text-align: right;
    }

    .footer {
      text-align: center;
      margin-top: 20px;
      font-size: 9px;
      color: #999;
    }

    .summary {
      margin-top: 15px;
      padding: 10px;
      background: #f8f9fa;
      border: 1px solid #ddd;
    }

    .summary td {
      border: none;
      font-size: 11px;
    }
  </style>
</head>

<body>
  <h2>{{ $title }}</h2>
  <div class="info">Generated on {{ now()->format('d M Y, h:i A') }}</div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Faculty</th>
        <th>Department</th>
        <th>Duty Type</th>
        <th>Quantity</th>
        <th>Rate</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      @php $grandTotal = 0; @endphp
      @foreach($records as $i => $record)
      @php $grandTotal += $record->total_amount; @endphp
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $record->faculty->name ?? 'N/A' }}</td>
        <td>{{ $record->faculty->department ?? '-' }}</td>
        <td>{{ ucfirst($record->duty_type) }}</td>
        <td>{{ $record->quantity }}</td>
        <td class="text-right">{{ number_format($record->rate, 2) }}</td>
        <td class="text-right">{{ number_format($record->total_amount, 2) }}</td>
        <td>
          @if($record->status === 'pending')
          <span class="badge bg-warning">Pending</span>
          @elseif($record->status === 'approved')
          <span class="badge bg-success">Approved</span>
          @else
          <span class="badge bg-info">Paid</span>
          @endif
        </td>
        <td>{{ $record->generated_at ? \Carbon\Carbon::parse($record->generated_at)->format('d M Y') : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table class="summary">
    <tr>
      <td><strong>Total Records:</strong> {{ count($records) }}</td>
      <td class="text-right"><strong>Grand Total:</strong> {{ number_format($grandTotal, 2) }}</td>
    </tr>
  </table>

  <div class="footer">SCS ERP - Faculty Payment Report</div>
</body>

</html>
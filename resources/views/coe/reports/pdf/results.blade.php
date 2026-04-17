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

    .bg-danger {
      background-color: #dc3545;
    }

    .bg-secondary {
      background-color: #6c757d;
    }

    .footer {
      text-align: center;
      margin-top: 20px;
      font-size: 9px;
      color: #999;
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
        <th>Student</th>
        <th>Exam</th>
        <th>SGPA</th>
        <th>CGPA</th>
        <th>Percentage</th>
        <th>Credits</th>
        <th>Result</th>
        <th>Published</th>
      </tr>
    </thead>
    <tbody>
      @foreach($records as $i => $record)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $record->student->studentMaster->first_name ?? '' }} {{ $record->student->studentMaster->last_name ?? '' }}</td>
        <td>{{ $record->exam->name ?? '-' }}</td>
        <td>{{ $record->sgpa ?? '-' }}</td>
        <td>{{ $record->cgpa ?? '-' }}</td>
        <td>{{ $record->percentage ? $record->percentage . '%' : '-' }}</td>
        <td>{{ $record->earned_credits ?? '-' }}</td>
        <td>
          @if($record->result_status === 'pass')
          <span class="badge bg-success">Pass</span>
          @elseif($record->result_status === 'fail')
          <span class="badge bg-danger">Fail</span>
          @else
          <span class="badge bg-secondary">{{ ucfirst($record->result_status ?? '-') }}</span>
          @endif
        </td>
        <td>{{ $record->published_at ? \Carbon\Carbon::parse($record->published_at)->format('d M Y') : 'No' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">SCS ERP - Exam Results Report</div>
</body>

</html>
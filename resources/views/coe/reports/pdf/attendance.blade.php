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

    .bg-warning {
      background-color: #ffc107;
      color: #333;
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
        <th>Subject</th>
        <th>Seat No</th>
        <th>Status</th>
        <th>Marked At</th>
      </tr>
    </thead>
    <tbody>
      @foreach($records as $i => $record)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $record->student->first_name ?? '' }} {{ $record->student->last_name ?? '' }}</td>
        <td>{{ $record->exam->name ?? '-' }}</td>
        <td>{{ $record->subject->name ?? '-' }}</td>
        <td>{{ $record->seat_no ?? '-' }}</td>
        <td>
          @if($record->status === 'present')
          <span class="badge bg-success">Present</span>
          @elseif($record->status === 'absent')
          <span class="badge bg-danger">Absent</span>
          @else
          <span class="badge bg-warning">Malpractice</span>
          @endif
        </td>
        <td>{{ $record->marked_at ? \Carbon\Carbon::parse($record->marked_at)->format('d M Y') : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">SCS ERP - Exam Attendance Report</div>
</body>

</html>
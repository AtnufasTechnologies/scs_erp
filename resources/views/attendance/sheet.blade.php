<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Attendance Sheet</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
    }

    th,
    td {
      border: 1px solid #333;
      padding: 4px;
      text-align: left;
    }

    th {
      background: #f0f0f0;
    }

    .signature {
      height: 40px;
    }
  </style>
</head>

<body>
  <h2>Attendance Sheet</h2>
  <p><strong>Room:</strong> {{ $room->name }}<br>
    <strong>Location:</strong> {{ $room->location ?? '-' }}
  </p>
  <table>
    <thead>
      <tr>
        <th>Seat No</th>
        <th>Student Name</th>
        <th>Enrollment No</th>
        <th>Signature</th>
      </tr>
    </thead>
    <tbody>
      @foreach($attendances as $attendance)
      <tr>
        <td>{{ $attendance->seat_no }}</td>
        <td>{{ $attendance->student->name ?? '-' }}</td>
        <td>{{ $attendance->student->enrollment_no ?? '-' }}</td>
        <td class="signature"></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <br><br>
  <table style="width:100%;">
    <tr>
      <td style="width:70%;"></td>
      <td style="width:30%; text-align:center;">
        Invigilator Signature:<br><br><br>
        __________________________
      </td>
    </tr>
  </table>
</body>

</html>
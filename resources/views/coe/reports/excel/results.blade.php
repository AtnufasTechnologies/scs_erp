<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Student Name</th>
      <th>Exam</th>
      <th>Session</th>
      <th>SGPA</th>
      <th>CGPA</th>
      <th>Percentage</th>
      <th>Earned Credits</th>
      <th>Result Status</th>
      <th>Published</th>
      <th>Published At</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data['records'] as $i => $record)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ ($record->student->studentMaster->first_name ?? '') . ' ' . ($record->student->studentMaster->last_name ?? '') }}</td>
      <td>{{ $record->exam->name ?? '-' }}</td>
      <td>{{ $record->examSession->name ?? '-' }}</td>
      <td>{{ $record->sgpa ?? '-' }}</td>
      <td>{{ $record->cgpa ?? '-' }}</td>
      <td>{{ $record->percentage ?? '-' }}</td>
      <td>{{ $record->earned_credits ?? '-' }}</td>
      <td>{{ ucfirst($record->result_status ?? '-') }}</td>
      <td>{{ $record->is_published ? 'Yes' : 'No' }}</td>
      <td>{{ $record->published_at ? \Carbon\Carbon::parse($record->published_at)->format('d-m-Y') : '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
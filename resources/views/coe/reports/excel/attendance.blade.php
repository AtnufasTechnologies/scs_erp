<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Student Name</th>
      <th>Exam</th>
      <th>Subject</th>
      <th>Seat No</th>
      <th>Dummy No</th>
      <th>Status</th>
      <th>Marked At</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data['records'] as $i => $record)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ ($record->student->first_name ?? '') . ' ' . ($record->student->last_name ?? '') }}</td>
      <td>{{ $record->exam->name ?? '-' }}</td>
      <td>{{ $record->subject->name ?? '-' }}</td>
      <td>{{ $record->seat_no ?? '-' }}</td>
      <td>{{ $record->dummy_no ?? '-' }}</td>
      <td>{{ ucfirst($record->status) }}</td>
      <td>{{ $record->marked_at ? \Carbon\Carbon::parse($record->marked_at)->format('d-m-Y H:i') : '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
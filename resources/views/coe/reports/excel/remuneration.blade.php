<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Faculty Name</th>
      <th>Department</th>
      <th>Duty Type</th>
      <th>Quantity</th>
      <th>Rate</th>
      <th>Total Amount</th>
      <th>Status</th>
      <th>Generated At</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data['records'] as $i => $record)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $record->faculty->name ?? 'N/A' }}</td>
      <td>{{ $record->faculty->department ?? '-' }}</td>
      <td>{{ ucfirst($record->duty_type) }}</td>
      <td>{{ $record->quantity }}</td>
      <td>{{ $record->rate }}</td>
      <td>{{ $record->total_amount }}</td>
      <td>{{ ucfirst($record->status) }}</td>
      <td>{{ $record->generated_at ? \Carbon\Carbon::parse($record->generated_at)->format('d-m-Y') : '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
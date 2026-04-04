<table>
  <thead>
    <tr>
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
    @foreach($remunerations as $rem)
    <tr>
      <td>{{ $rem->faculty->name ?? 'N/A' }}</td>
      <td>{{ $rem->faculty->department ?? '' }}</td>
      <td>{{ ucfirst($rem->duty_type) }}</td>
      <td>{{ $rem->quantity }}</td>
      <td>{{ number_format($rem->rate, 2) }}</td>
      <td>{{ number_format($rem->total_amount, 2) }}</td>
      <td>{{ ucfirst($rem->status) }}</td>
      <td>{{ $rem->generated_at ? $rem->generated_at->format('d-m-Y') : '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@extends('layouts.app')

@section('content')
<div class="container">
  <h2>My Remuneration</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Duty</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($remunerations as $rem)
      <tr>
        <td>{{ ucfirst($rem->duty_type) }}</td>
        <td>{{ $rem->generated_at ? $rem->generated_at->format('Y-m-d') : '' }}</td>
        <td>{{ number_format($rem->total_amount, 2) }}</td>
        <td>{{ ucfirst($rem->status) }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="4" class="text-center">No earnings found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="mt-3">
    <strong>Total Earnings:</strong> {{ number_format($total_earnings, 2) }}
  </div>
</div>
@endsection
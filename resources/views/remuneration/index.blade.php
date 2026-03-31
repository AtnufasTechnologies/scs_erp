@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Faculty Remuneration</h2>
  <form method="GET" class="mb-3 row g-2">
    <div class="col-md-3">
      <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="Date">
    </div>
    <div class="col-md-3">
      <select name="faculty_id" class="form-control">
        <option value="">All Faculty</option>
        @foreach($faculties as $faculty)
        <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
          {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
        </option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <select name="duty_type" class="form-control">
        <option value="">All Duty Types</option>
        <option value="invigilation" {{ request('duty_type') == 'invigilation' ? 'selected' : '' }}>Invigilation</option>
        <option value="evaluation" {{ request('duty_type') == 'evaluation' ? 'selected' : '' }}>Evaluation</option>
        <option value="moderation" {{ request('duty_type') == 'moderation' ? 'selected' : '' }}>Moderation</option>
      </select>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary">Filter</button>
    </div>
  </form>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Faculty</th>
        <th>Duty Type</th>
        <th>Quantity</th>
        <th>Amount</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($remunerations as $rem)
      <tr>
        <td>{{ $rem->faculty->FIRST_NAME ?? '' }} {{ $rem->faculty->LAST_NAME ?? '' }}</td>
        <td>{{ ucfirst($rem->duty_type) }}</td>
        <td>{{ $rem->quantity }}</td>
        <td>{{ number_format($rem->total_amount, 2) }}</td>
        <td>{{ ucfirst($rem->status) }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center">No records found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <div class="row mt-3">
    <div class="col-md-6">
      <strong>Total Pending:</strong> {{ number_format($total_pending, 2) }}
    </div>
    <div class="col-md-6">
      <strong>Total Paid:</strong> {{ number_format($total_paid, 2) }}
    </div>
  </div>
</div>
@endsection
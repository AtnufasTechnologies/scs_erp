@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Faculty Remuneration Report @if($month) for {{ $month }} @endif</h2>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Faculty Name</th>
        <th>Total Duties</th>
        <th>Total Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($summary as $row)
      <tr>
        <td>{{ $row->faculty->FIRST_NAME ?? '' }} {{ $row->faculty->LAST_NAME ?? '' }}</td>
        <td>{{ $row->total_duties }}</td>
        <td>{{ number_format($row->total_amount, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
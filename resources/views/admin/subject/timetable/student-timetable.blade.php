@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="fw-bold">Student Timetable</h2>
      <p class="text-muted">Timetable for Student: <span class="fw-semibold">{{ $student->name ?? '-' }}</span></p>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card shadow-lg border-0">
        <div class="card-body">
          @if(count($timetable))
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <tr>
                  <th>Day</th>
                  <th>Hour</th>
                  <th>Subject</th>
                  <th>Faculty</th>
                  <th>Lecture Hall</th>
                </tr>
              </thead>
              <tbody>
                @foreach($timetable as $entry)
                <tr>
                  <td>{{ $entry->weekday ?? '-' }}</td>
                  <td>{{ $entry->hour ?? '-' }}</td>
                  <td>{{ $entry->subject ?? '-' }}</td>
                  <td>{{ $entry->faculty ?? '-' }}</td>
                  <td>{{ $entry->lecture_hall ?? '-' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="alert alert-info">No timetable entries found for this student.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
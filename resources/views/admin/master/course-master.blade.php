<?php

use App\Models\SubjectTypeMaster;

$courseTypes = SubjectTypeMaster::all();

?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Course Master</span></h3>


<div class="container-fluid">
  <div class="row">
    <div class="card">
      <div class="card-header">
        <form method="GET" class="d-flex gap-2">
          <select name="course_type" class="form-select dselect-example" onchange="this.form.submit()">
            <option value="">Filter by Course Type</option>
            @foreach($courseTypes as $type)
            <option value="{{ $type->id }}" {{ request('course_type') == $type->id ? 'selected' : '' }}>
              {{ $type->title }}
            </option>
            @endforeach
          </select>
        </form>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white rounded shadow-sm" id="exportTable">
          <thead class="table-dark">
            <tr>
              <th>#</th>

              <th>Course Type</th>
              <th>Course Code</th>
              <th>Course Title</th>
              <th>Students Enrolled</th>
              <th>Credits</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $course)
            <tr>
              <td>{{ $loop->iteration}}</td>

              <td> {{ $course->coursetypemaster->title ?? '-' }}</td>
              <td>{{ $course->course_code ?? '-' }}</td>
              <td>{{ $course->course_title ?? '-' }}</td>
              <td>{{ $course->stucourseinfo_count ?? 0 }}</td>
              <td>{{ $course->credits ?? '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">No courses found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>


@include('includes.footer')
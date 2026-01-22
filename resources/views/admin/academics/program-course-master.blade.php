<?php

use App\Models\DepartmentMaster;
use App\Models\ProgramCourseMaster;
use App\Models\Semester;
use App\Models\SubjectTypeMaster;

$departments = DepartmentMaster::all();
$courseTypes = SubjectTypeMaster::all();
$semesters = Semester::all();
$academicYears = ProgramCourseMaster::select('ACADEMIC_YEAR')->distinct()->orderBy('ACADEMIC_YEAR', 'DESC')->pluck('ACADEMIC_YEAR');
?>
@include('includes.header')
@include('admin.sidebar')
<h3>Program Course Master</h3>
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h5>Filter Courses</h5>
    </div>
    <div class="card-body">
      <form method="GET" action="{{route('program-course.master')}}">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Department</label>
              <select name="department" class="form-control dselect-example">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                  {{ $dept->department_code }} - {{ $dept->name }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Course Type</label>
              <select name="course_type" class="form-control">
                <option value="">All Course Types</option>
                @foreach($courseTypes ?? [] as $type)
                <option value="{{ $type->id }}" {{ request('course_type') == $type->id ? 'selected' : '' }}>
                  {{ $type->title }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label>Semester</label>
              <select name="semester" class="form-control">
                <option value="">All Semesters</option>
                @foreach($semesters ?? [] as $semester)
                <option value="{{ $semester->id }}" {{ request('semester') == $semester->id ? 'selected' : '' }}>
                  {{ $semester->title }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-2">

            <div class="form-group">
              <label>Academic Year</label>
              <select name="academic_year" class="form-control">
                <option value="">All Years</option>
                @foreach($academicYears ?? [] as $year)
                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                  {{ $year }}
                </option>
                @endforeach
              </select>
            </div>


          </div>
          <div class="col-md-1 mt-4">
            <button type="submit" class="btn btn-main">Filter</button>
          </div>
        </div>

      </form>
      <a href="{{route('program-course.master')}}">Reset All</a>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover" id="exportTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Course Code</th>
              <th>Course Title</th>
              <th>Department</th>
              <th>Campus</th>
              <th>Course Type</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th>HRS/Week</th>
              <th>Total Hours</th>
              <th>Credits</th>
              <th>Internal</th>
              <th>External</th>
              <th>Total</th>
              <th>Part</th>
            </tr>
          </thead>
          <tbody>
            @if (count($data))


            @foreach($data as $course)
            <tr>
              <td>{{$loop->iteration}}</td>
              <td>{{ $course->COURSE_CODE }}</td>
              <td>{{ $course->COURSE_TITLE }}</td>
              <td>{{ $course->departmentmaster->name ?? 'N/A' }}</td>
              <td>{{ $course->departmentmaster->campusmaster->name ?? 'N/A' }}</td>
              <td>{{ $course->coursetypemaster->title ?? 'N/A' }}</td>
              <td>{{ $course->semestermaster->title ?? 'N/A' }}</td>
              <td>{{ $course->ACADEMIC_YEAR }}</td>
              <td>{{ $course->HRS_PER_WEEK ?? 'N/A' }}</td>
              <td>{{ $course->TOTAL_ALLOTED_HOURS ?? 'N/A' }}</td>
              <td>{{ $course->CREDITS }}</td>
              <td>{{ $course->INTERNAL }}</td>
              <td>{{ $course->EXTERNAL }}</td>
              <td>{{ $course->TOTAL }}</td>
              <td>{{ $course->PART }}</td>
            </tr>
            @endforeach

            @endif

          </tbody>
        </table>
      </div>


    </div>
  </div>
</div>



@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Timetable</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.faculty.index') }}">Faculty</a></li>
            <li class="breadcrumb-item active" aria-current="page">Timetable</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Faculty Info Header --}}
    <div class="card mt-3">
      <div class="card-body d-flex align-items-center gap-3">
        @if($faculty->photo)
        <img src="{{ asset('storage/' . $faculty->photo) }}" alt="Photo" class="rounded-circle" width="60" height="60" style="object-fit: cover;">
        @else
        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px; font-size: 24px;">
          {{ strtoupper(substr($faculty->FIRST_NAME, 0, 1)) }}
        </div>
        @endif
        <div>
          <h5 class="mb-0 text-capitalize">{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</h5>
          <small class="text-muted">{{ $faculty->USER_CODE }} | {{ $faculty->department_info ? $faculty->department_info->name : '-' }}</small>
        </div>
        <div class="ms-auto">
          <a href="{{ route('principal.faculty.detail', $faculty->id) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="fas fa-arrow-left"></i> Back to Detail</a>
          <a href="{{ route('principal.faculty.work-diary', $faculty->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-book"></i> Work Diary</a>
        </div>
      </div>
    </div>

    {{-- Assigned Courses --}}
    <div class="card mt-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Assigned Courses</h5>
        <span class="badge bg-dark">{{ $assignedCourses->count() }} Courses</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead class="bg-dark text-light">
              <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Course Type</th>
                <th>Semester</th>
                <th>Batch</th>
                <th>Academic Year</th>
              </tr>
            </thead>
            <tbody>
              @php $csl = 1; @endphp
              @foreach($assignedCourses as $ac)
              <tr>
                <td>{{ $csl++ }}</td>
                <td><span class="badge bg-primary">{{ $ac['course_code'] }}</span></td>
                <td>{{ $ac['course_title'] }}</td>
                <td><span class="badge bg-secondary">{{ $ac['course_type'] }}</span></td>
                <td>{{ $ac['semester'] }}</td>
                <td><span class="badge bg-info">{{ $ac['batch'] }}</span></td>
                <td>{{ $ac['academic_year'] }}</td>
              </tr>
              @endforeach
              @if($assignedCourses->isEmpty())
              <tr>
                <td colspan="7" class="text-center py-3 text-muted">No courses assigned</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Timetable Grid --}}
    <div class="card mt-3">
      <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Weekly Timetable</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered text-center">
            <thead class="bg-dark text-light">
              <tr>
                <th>Day / Hour</th>
                @foreach($hours as $hour)
                <th>{{ $hour->title }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($timetableGrid as $dayId => $dayData)
              <tr>
                <td class="fw-bold bg-light">{{ $dayData['day'] }}</td>
                @foreach($dayData['slots'] as $hourId => $slot)
                <td>
                  @if($slot['routine'])
                  @php
                  $cm = $slot['routine']->subjectCourse && $slot['routine']->subjectCourse->courseMaster ? $slot['routine']->subjectCourse->courseMaster : null;
                  @endphp
                  <div class="p-1" style="font-size: 0.78rem;">
                    <strong class="text-primary">{{ $cm ? $cm->course_code : '-' }}</strong>
                    <br>
                    <small class="text-dark">{{ $cm ? Str::limit($cm->course_title, 20) : '' }}</small>
                    <br>
                    <small class="text-muted">{{ $slot['routine']->lecturehallmaster ? $slot['routine']->lecturehallmaster->title : '' }}</small>
                    <br>
                    <span class="badge bg-info" style="font-size: 0.6rem;">{{ $slot['routine']->batch ? $slot['routine']->batch->batch_name : '' }}</span>
                    @if($cm && $cm->semestermaster)
                    <span class="badge bg-secondary" style="font-size: 0.6rem;">{{ $cm->semestermaster->title }}</span>
                    @endif
                  </div>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                @endforeach
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
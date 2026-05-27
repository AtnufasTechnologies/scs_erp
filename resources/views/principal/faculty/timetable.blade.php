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


    {{-- Timetable Grid --}}
    <div class="card mt-3">
      <div class="card-header">
        <div>
          <p> {{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }} ({{ $faculty->USER_CODE }})</p>

        </div>
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
                  <div class="p-1">
                    <strong class="text-primary">{{ $cm ? $cm->course_code : '-' }}</strong>
                    <br>
                    <small class="text-dark">{{ $cm ? Str::limit($cm->course_title, 20) : '' }}</small>
                    <br>
                    <small class="text-muted">{{ $slot['routine']->lecturehallmaster ? $slot['routine']->lecturehallmaster->title : '' }}</small>
                    <br>
                    <span class="badge bg-info">{{ $slot['routine']->batch ? $slot['routine']->batch->batch_name : '' }}</span>
                    @if($slot['routine']->syllabus && $slot['routine']->syllabus->semestermaster)
                    <span class="badge bg-success">{{ $slot['routine']->syllabus->semestermaster->title }}</span>
                    @elseif($cm && $cm->semestermaster)
                    <span class="badge bg-secondary">{{ $cm->semestermaster->title }}</span>
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
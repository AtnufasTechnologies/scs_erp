@include('includes.header')



<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Classes</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Hour-wise Classes</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <h4 class="mb-1"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Class Schedule</h4>
            <p class="text-muted small mb-0">
              <i class="fas fa-calendar-day me-1"></i>{{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}
              @if($selectedCampus)
              <span class="mx-2">•</span>
              <i class="fas fa-building me-1"></i>{{ $campuses->firstWhere('id', $selectedCampus)->name ?? '' }}
              @else
              <span class="mx-2">•</span>
              <i class="fas fa-globe me-1"></i>All Campuses
              @endif
            </p>
          </div>
          <form method="GET" action="{{ route('principal.classes.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <select name="campus_id" class="form-select" style="width: 200px;">
              <option value="">🏢 All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
            <input type="date" name="date" class="form-control" style="width: 180px;" value="{{ $selectedDate }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
          </form>
        </div>
      </div>
      <div class="card-body p-4">

        @foreach($classesByHour as $hourId => $hourData)
        <div class="mb-4">
          <div class="hour-section d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <i class="fas fa-clock fa-2x"></i>
              <div>
                <h5 class="mb-0">{{ $hourData['hour'] }}</h5>
                <small class="opacity-75">{{ count($hourData['classes']) }} {{ count($hourData['classes']) === 1 ? 'class' : 'classes' }} scheduled</small>
              </div>
            </div>
            <span class="badge bg-white text-dark px-3 py-2" style="font-size: 1rem;">{{ count($hourData['classes']) }}</span>
          </div>

          @if(count($hourData['classes']) > 0)
          <div class="row g-3 mt-2">
            @foreach($hourData['classes'] as $class)
            @php
            $courseMaster = $class->subjectCourse && $class->subjectCourse->courseMaster ? $class->subjectCourse->courseMaster : null;
            $semester = $class->syllabus && $class->syllabus->semestermaster ? $class->syllabus->semestermaster->title : ($courseMaster && $courseMaster->semestermaster ? $courseMaster->semestermaster->title : null);
            @endphp
            <div class="col-lg-6 col-xl-4">
              <div class="card class-card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <div class="d-flex align-items-start gap-2 mb-3">
                    <div class="bg-primary text-white rounded p-2" style="min-width: 40px; text-align: center;">
                      <i class="fas fa-book"></i>
                    </div>
                    <div class="flex-grow-1">
                      <span class="course-code d-block">{{ $courseMaster ? $courseMaster->course_code : 'N/A' }}</span>
                      <span class="course-title d-block" title="{{ $courseMaster ? $courseMaster->course_title : 'No course assigned' }}">
                        {{ $courseMaster ? Str::limit($courseMaster->course_title, 45) : 'No course assigned' }}
                      </span>
                      @if($semester)
                      <span class="badge bg-primary bg-opacity-10 text-light mt-1" style="font-size: 0.75rem;">
                        <i class="fas fa-graduation-cap"></i> {{ $semester }}
                      </span>
                      @endif
                    </div>
                  </div>

                  <div class="mb-2">
                    <i class="fas fa-user-tie text-primary me-2"></i>
                    <span class="text-capitalize">
                      @if($class->faculty)
                      {{ $class->faculty->FIRST_NAME }} {{ $class->faculty->LAST_NAME }}
                      @else
                      <span class="text-muted">No faculty assigned</span>
                      @endif
                    </span>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge badge-success">
                      Batch -
                      {{ $class->batch ? $class->batch->batch_name : 'N/A' }}
                    </span>

                    <span class="badge badge-success  ">
                      <i class="fas fa-door-open"></i>
                      {{ $class->lecturehallmaster ? $class->lecturehallmaster->title : 'N/A' }}
                    </span>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge badge-success">
                      <i class="fas fa-building"></i>
                      {{ $class->lecturehallmaster && $class->lecturehallmaster->acblockmaster ? $class->lecturehallmaster->acblockmaster->title : 'N/A' }}
                    </span>
                    <span class="badge badge-success">
                      <i class="fas fa-map-marker-alt"></i>
                      @if($class->lecturehallmaster && $class->lecturehallmaster->acblockmaster)
                      @php
                      $campusName = \App\Models\Campus::find($class->lecturehallmaster->acblockmaster->campus_id);
                      @endphp
                      {{ $campusName ? $campusName->name : 'N/A' }}
                      @else
                      N/A
                      @endif
                    </span>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @else
          <div class="text-center py-4 mt-2">
            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">No classes scheduled for this hour</p>
          </div>
          @endif
        </div>
        @endforeach

        @if(collect($classesByHour)->sum(function($h) { return count($h['classes']); }) == 0)
        <div class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-calendar-times fa-4x text-muted opacity-50"></i>
          </div>
          <h5 class="text-muted mb-2">No Classes Scheduled</h5>
          <p class="text-muted">There are no classes scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('l, d M Y') }}</p>
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">View Internal Marks</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.internal-marks.index') }}">Internal Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">View</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      {{-- Course Info --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h5 class="fw-bold mb-1">{{ $course->course_title ?? 'N/A' }} {{ $course->course_code ? '('.$course->course_code.')' : '' }}</h5>
          <p class="text-muted mb-0">
            <span class="badge bg-success me-1">Semester: {{ $semester }}</span>
            <span class="badge bg-secondary me-1">Batch: {{ $syllabusAssignment->batchmaster->batch_name ?? 'N/A' }}</span>
            <span class="badge bg-primary me-1">Component: {{ $component->name ?? 'N/A' }}</span>
            @if($course->departmentmaster)
            <span class="badge bg-secondary">{{ $course->departmentmaster->dept_name ?? '' }}</span>
            @endif
          </p>
        </div>
      </div>

      {{-- Marks Table --}}
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-list-ol text-primary me-2"></i>Submitted Marks</h6>
          <a href="{{ route('faculty.internal-marks.enter', ['rec_id' => $routineId, 'syllabus_id' => $syllabusId, 'component_id' => $componentId]) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-edit me-1"></i>Edit Marks
          </a>
        </div>
        <div class="card-body">
          @if($marks->count() > 0)
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Roll No</th>
                  <th>Register No</th>
                  <th>Student Name</th>
                  <th>Internal Mark</th>
                </tr>
              </thead>
              <tbody>
                @foreach($marks as $index => $mark)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $mark->student->roll_no ?? '-' }}</td>
                  <td>{{ $mark->student->register_no ?? '-' }}</td>
                  <td class="text-capitalize">{{ $mark->student->first_name ?? '' }} {{ $mark->student->last_name ?? '' }}</td>
                  <td class="fw-bold">{{ $mark->internal_mark }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="text-center py-5">
            <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
            <p class="text-muted">No marks have been entered for this course yet.</p>
            <a href="{{ route('faculty.internal-marks.enter', ['rec_id' => $routineId, 'syllabus_id' => $syllabusId, 'component_id' => $componentId]) }}" class="btn btn-primary">
              <i class="fas fa-edit me-1"></i>Enter Marks Now
            </a>
          </div>
          @endif
        </div>
      </div>

      <div class="mt-3">
        <a href="{{ route('faculty.internal-marks.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
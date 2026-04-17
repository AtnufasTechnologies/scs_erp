@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Internal Marks (FA)</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Internal Marks</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-pen-alt text-primary me-2"></i>Internal Assessment (FA Marks)</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('faculty.internal-marks.enter') }}" method="GET" id="selectForm">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-bold">Course</label>
                <select name="course_id" class="form-select" required>
                  <option value="">-- Select Course --</option>
                  @foreach($courses as $course)
                  <option value="{{ $course->id }}">
                    {{ $course->course_title ?? '' }} {{ $course->course_code ? '('.$course->course_code.')' : '' }}
                    @if($course->departmentmaster) - {{ $course->departmentmaster->dept_name ?? '' }} @endif
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Semester</label>
                <select name="semester" class="form-select" required>
                  <option value="">-- Select Semester --</option>
                  @foreach($semesters as $sem)
                  <option value="{{ $sem->id }}">{{ $sem->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">Academic Year</label>
                <input type="number" name="academic_year" class="form-control" placeholder="e.g. {{ date('Y') }}" value="{{ date('Y') }}">
              </div>
              <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-check-circle me-1"></i> Enter
                </button>
                <button type="submit" class="btn btn-outline-secondary" formaction="{{ route('faculty.internal-marks.view') }}">
                  <i class="fas fa-eye me-1"></i>View
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Enter Internal Marks</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.internal-marks.index') }}">Internal Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Enter Marks</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      {{-- Course Info Card --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="fw-bold mb-1">{{ $course->course_title ?? 'N/A' }} {{ $course->course_code ? '('.$course->course_code.')' : '' }}</h5>
              <p class="text-muted mb-0">
                <span class="badge bg-success me-1">Semester: {{ $semester }}</span>
                <span class="badge bg-secondary me-1">Batch: {{ $syllabusAssignment->batchmaster->batch_name ?? 'N/A' }}</span>
                <span class="badge bg-primary me-1">Component: {{ $component->name ?? 'N/A' }}</span>
                @if($academicYear)
                <span class="badge bg-info me-1">Year: {{ $academicYear }}</span>
                @endif
                @if($course->departmentmaster)
                <span class="badge bg-secondary">{{ $course->departmentmaster->dept_name ?? '' }}</span>
                @endif
              </p>
            </div>
            <div class="col-md-4 text-end">
              <span class="text-muted">{{ $students->count() }} Students</span>
            </div>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <input type="text" id="studentSearch" class="form-control" placeholder="Search student by name, roll no, or register no...">
      </div>


      {{-- Marks Entry Form --}}
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-pen-alt text-primary me-2"></i>Enter FA Marks</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('faculty.internal-marks.store') }}" method="POST" id="marksForm">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <input type="hidden" name="semester" value="{{ $semester }}">
            <input type="hidden" name="batch_id" value="{{ $batchId ?? '' }}">
            <input type="hidden" name="component_id" value="{{ $componentId }}">
            <input type="hidden" name="academic_year" value="{{ $academicYear }}">
            <input type="hidden" name="rec_id" value="{{ $routineId }}">
            <input type="hidden" name="syllabus_id" value="{{ $syllabusId }}">

            @if($students->count() > 0)
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle" id="marksTable">
                <thead class="table-light">
                  <tr>
                    <th width="50">#</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th width="150">Internal Mark</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $index => $student)
                  @php
                  $existing = $existingMarks->get($student->id);
                  @endphp
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->roll_no ?? '-' }}</td>
                    <td class="text-capitalize">{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>
                      <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}">
                      <input type="text" name="marks[{{ $index }}][internal_mark]"
                        class="form-control form-control-sm"
                        value="{{ $existing ? $existing->internal_mark : '' }}"
                        maxlength="45" placeholder="0">
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
              <a href="{{ route('faculty.internal-marks.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Save Marks
              </button>
            </div>
            @else
            <div class="text-center py-5">
              <i class="fas fa-users fa-3x text-muted mb-3"></i>
              <p class="text-muted">No students found for this course/semester.</p>
            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<script>
  document.getElementById('studentSearch').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#marksTable tbody tr').forEach(function(row) {
      let text = row.textContent.toLowerCase();
      row.style.display = text.includes(value) ? '' : 'none';
    });
  });
</script>
@include('includes.footer')
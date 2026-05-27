@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Courses & CSO</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Courses & CSO Subunits</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="fs-2 fw-bold text-primary">{{ count($syllabi) }}</div>
            <div class="text-muted small">Total Courses</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php $avgCompletion = count($syllabi) > 0 ? round($syllabi->avg('completion_percent'), 1) : 0; @endphp
            <div class="fs-2 fw-bold text-success">{{ $avgCompletion }}%</div>
            <div class="text-muted small">Avg Completion</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php $totalClasses = $syllabi->sum('total_classes'); @endphp
            <div class="fs-2 fw-bold text-info">{{ $totalClasses }}</div>
            <div class="text-muted small">Classes Taken</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php $avgAtt = count($syllabi) > 0 ? round($syllabi->avg('avg_attendance_percent'), 1) : 0; @endphp
            <div class="fs-2 fw-bold text-warning">{{ $avgAtt }}%</div>
            <div class="text-muted small">Avg Attendance</div>
          </div>
        </div>
      </div>
    </div>

    <div class=" mt-3">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0">Course Syllabus - Completion, Attendance & Feedback</h5>
        <form method="GET" action="{{ route('principal.courses.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
          <select name="academic_year" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Years</option>
            @foreach($academicYears as $yr)
            <option value="{{ $yr }}" {{ (string)$selectedAcademicYear === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
          </select>
          <select name="semester_id" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Semesters</option>
            @foreach($semesters as $sem)
            <option value="{{ $sem->id }}" {{ (string)$selectedSemester === (string)$sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
            @endforeach
          </select>
          <select name="campus_id" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          <select name="department_id" class="form-select form-select-sm" style="width: 190px;" onchange="this.form.submit()">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ (string)$selectedDepartment === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
          </select>
          @if($selectedAcademicYear || $selectedSemester || $selectedCampus || $selectedDepartment)
          <a href="{{ route('principal.courses.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
          @endif
        </form>
      </div>
      <div class="card-body">
        <div class="row g-4">
          @if(count($syllabi))
          @foreach($syllabi as $syl)
          <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm modern-course-card">
              <div class="card-body d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-dark">{{ $syl->course_code }}</span>
                    <span class="badge bg-secondary">{{ $syl->course_type_name }}</span>
                  </div>
                  <h5 class="fw-bold mb-1">{{ $syl->course_title_pcm }}</h5>
                  <div class="mb-2 text-muted small">{{ $syl->subject ? $syl->subject->title : '-' }}</div>
                  <div class="mb-2">
                    <span class="badge bg-light text-dark me-1">Batch: {{ $syl->batchmaster ? $syl->batchmaster->batch_name : '-' }}</span>
                    <span class="badge bg-light text-dark me-1">Semester: {{ $syl->semestermaster ? $syl->semestermaster->title : '-' }}</span>
                  </div>
                  <div class="mb-2">
                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    <span class="text-capitalize">{{ $syl->faculty_name }}</span>
                  </div>
                  <div class="mb-2">
                    <span class="badge bg-success">{{ $syl->completed_subunits }}</span>
                    <span class="text-muted">/</span>
                    <span class="badge bg-secondary">{{ $syl->total_subunits }}</span>
                    <span class="ms-2">Subunits</span>
                  </div>
                  <div class="mb-2">
                    <div class="progress" style="height: 16px; min-width: 80px;">
                      <div class="progress-bar {{ $syl->completion_percent >= 75 ? 'bg-success' : ($syl->completion_percent >= 50 ? 'bg-warning' : 'bg-danger') }}"
                        role="progressbar" style="width: {{ $syl->completion_percent }}%">
                        {{ $syl->completion_percent }}%
                      </div>
                    </div>
                    <span class="small text-muted">Completion</span>
                  </div>
                  <div class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge {{ $syl->avg_attendance_percent >= 75 ? 'bg-success' : ($syl->avg_attendance_percent >= 50 ? 'bg-warning' : 'bg-danger') }}">
                      {{ $syl->avg_attendance_percent }}% Attendance
                    </span>
                  </div>
                  <div class="mb-2">
                    @if($syl->avg_rating)
                    <span class="badge bg-primary">{{ number_format($syl->avg_rating, 1) }}</span>
                    <small class="text-muted">({{ $syl->feedback_count }}) Feedback</small>
                    @else
                    <span class="text-muted">No Feedback</span>
                    @endif
                  </div>
                </div>
                <div class="mt-2 d-flex gap-2">
                  <a href="{{ route('principal.courses.detail', $syl->id) }}" class="btn btn-sm btn-outline-primary w-100" title="View Detail">
                    <i class="fas fa-chart-bar"></i> Detail
                  </a>
                  <button class="btn btn-sm btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#subunitModal{{ $syl->id }}" title="Subunits">
                    <i class="fas fa-list"></i> Subunits
                  </button>
                </div>
              </div>
            </div>
          </div>
          @endforeach
          @else
          <div class="col-12">
            <div class="alert alert-info text-center py-4 mb-0">No Course Syllabus Records Found</div>
          </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Subunit Detail Modals --}}
    @foreach($syllabi as $syl)
    <div class="modal fade" id="subunitModal{{ $syl->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">CSO Subunits - {{ $syl->course_code }} {{ $syl->course_title_pcm }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            @if($syl->syllabusunits && $syl->syllabusunits->count())
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>CSO Subunit Title</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @php $subSl = 1; @endphp
                @foreach($syl->syllabusunits as $subunit)
                <tr>
                  <td>{{ $subSl++ }}</td>
                  <td>{{ $subunit->csoSubunit ? $subunit->csoSubunit->title : 'N/A' }}</td>
                  <td>
                    @if($subunit->is_completed)
                    <span class="badge bg-success">Completed</span>
                    @else
                    <span class="badge bg-warning">Pending</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <p class="text-muted text-center">No subunits found for this syllabus.</p>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </main>
</div>

@include('includes.footer')
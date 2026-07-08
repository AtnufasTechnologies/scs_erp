@include('includes.header')
@include('admin.sidebar')

<style>
  .dept-hero {
    background: linear-gradient(120deg, #7149e6 0%, #199fb7 100%);
    border-radius: 18px;
    color: #fff;
    padding: 24px;
    box-shadow: 0 14px 30px rgba(15, 118, 110, 0.2);
  }

  .dept-soft-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  }

  .dept-stat-card {
    border-radius: 14px;
    padding: 14px 16px;
    color: #323232;
    min-height: 108px;
    border: 1px solid #199fb7;
  }

  .dept-stat-title {
    font-size: 12px;
    letter-spacing: 0.02em;
    opacity: 0.9;
    margin-bottom: 8px;

  }

  .dept-stat-value {
    font-size: 28px;
    line-height: 1.1;
    font-weight: 700;
  }

  .dept-table thead th {
    background: #0f172a;
    color: #f8fafc;
    font-weight: 600;
    border: 0;
    white-space: nowrap;
  }

  .dept-table tbody td {
    vertical-align: middle;
  }

  .dept-kv {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 8px 14px;
    font-size: 14px;
  }

  .dept-kv .k {
    color: #475569;
    font-weight: 600;
  }

  .dept-kv .v {
    color: #0f172a;
  }

  .dept-raw pre {
    max-height: 360px;
    overflow: auto;
    background: #0b1020;
    color: #dbeafe;
    border-radius: 12px;
    padding: 14px;
    font-size: 12px;
    margin: 0;
  }

  .flutter-tile-container {
    display: flex;
    flex-direction: column;
    max-width: 500px;
    background-color: #5838b6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 10px;
  }

  .flutter-tile {
    display: flex;
    align-items: center;
    padding: 16px;
    transition: background-color 0.2s ease;
    cursor: pointer;
    border-bottom: 1px solid #e0e0e0;
  }

  .flutter-tile:last-child {
    border-bottom: none;
  }

  .flutter-tile:hover {
    background-color: rgba(0, 0, 0, 0.04);
  }

  /* Dense variation (matches Flutter dense property) */
  .flutter-tile.dense {
    padding: 12px 16px;
  }

  .tile-leading {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    margin-right: 16px;
    color: #666666;
    font-size: 24px;
  }

  .tile-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .tile-title {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.87);
  }

  .tile-subtitle {
    margin: 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
  }

  .tile-trailing {
    display: flex;
    align-items: center;
    margin-left: 16px;
    color: rgba(255, 255, 255, 0.54);
    font-size: 14px;
  }

  .scrollable-div {
    height: 300px;
    /* Forces a boundary */
    overflow-y: auto;
    /* Adds vertical scrollbar ONLY when content overflows */
  }

  @media (max-width: 768px) {
    .dept-kv {
      grid-template-columns: 1fr;
      gap: 4px 0;
    }
  }
</style>

@php
$departmentName = $data->name ?? $data->title ?? $data->subject_name ?? $data->slug ?? ('Subject #' . ($data->id ?? ''));
$departmentCode = $data->code ?? $data->subject_code ?? $data->short_code ?? '-';
@endphp

<div class="container-fluid p-4">
  <div class="dept-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
      <div>
        <div class="small mb-1" style="opacity:.9;">Department Manager</div>
        <h4 class="mb-2 text-uppercase text-light" style="font-weight:700;">{{ $departmentName }}</h4>
        <div class="small" style="opacity:.9;">Code: {{ $departmentCode }} | ID: {{ $data->id ?? '-' }}</div>
      </div>
      <div class="text-end">
        <div class="small" style="opacity:.9;">Campus </div>
        <div style="font-size:22px;font-weight:700;">{{ $data->campus_id == 1 ? 'Sonada' : 'Siliguri' }}</div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Total Students</div>
        <div class="dept-stat-value">{{ $students_count ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Semesters</div>
        <div class="dept-stat-value">{{ $semesters_count ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Course Masters</div>
        <div class="dept-stat-value">{{ count($course_masters) ?? 0 }}</div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Combinations</div>
        <div class="dept-stat-value">{{ count($combinations ?? []) }}</div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Programs</div>
        <div class="dept-stat-value">{{ count($programs ?? []) }}</div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="dept-stat-card" style="background: linear-gradient(120deg, #c8eefe, #c8eefe);">
        <div class="dept-stat-title">Faculties</div>
        <div class="dept-stat-value">{{ count($deptfaculties ?? []) }}</div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-lg-8">
      <div class="dept-soft-card p-4 ">
        <h5 class="mb-3" style="font-weight:700;">Program Combinations</h5>
        @if(count($combinations ?? []))
        <div class="table-responsive">
          <table class="table table-hover dept-table mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Combination ID</th>
                <th>Batch</th>
                <th>Program ID</th>
                <th>Program Name</th>
                <th>Program Type</th>
                <th>Total Seats</th>
                <th>Available Seats</th>

              </tr>
            </thead>
            <tbody>
              @foreach($combinations as $combination)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $combination->id ?? '-' }}</td>
                <td>{{ $combination->batchmaster->batch_name ?? '-' }}</td>
                <td>{{ $combination->studentprograminfo->id ?? $combination->student_program_id ?? '-' }}</td>
                <td>{{ $combination->studentprograminfo->name ?? '-' }}</td>
                <td>{{ $combination->program_type ?? '-' }}</td>
                <td>{{ $combination->total_seats ?? '-' }}</td>
                <td>{{ $combination->total_available_seats ?? '-' }}</td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-muted">No combinations found for this subject and batch selection.</div>
        @endif
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card p-2">
        <div class="card-header">
          <h5>Faculties - {{count($deptfaculties)}}</h5>
        </div>
        @if(count($deptfaculties ?? []))
        <div class="card-body scrollable-div">
          @foreach($deptfaculties as $facultyMap)

          <div class="flutter-tile-container">
            <!-- Standard Tile -->
            <div class="flutter-tile">
              <div class="tile-leading"><i class="fas fa-user-circle text-warning"></i></div>
              <div class="tile-content">
                <h3 class="tile-title">{{ trim(($facultyMap->faculty->FIRST_NAME ?? '') . ' ' . ($facultyMap->faculty->MIDDLE_NAME ?? '') . ' ' . ($facultyMap->faculty->LAST_NAME ?? '')) ?: '-' }}</h3>
                <p class="tile-subtitle">{{ $facultyMap->faculty->USER_CODE ?? '-' }}</p>
              </div>
              <!-- <div class="tile-trailing"><i class="fas fa-chevron-right"></i></div> -->
            </div>

          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card p-2 shadow">

        <h5>Courses - {{count($course_masters)}}</h5>
        <div class="mb-2">
          <input type="text" id="courseSearchInput" class="form-control" placeholder="Search courses by code or title...">
        </div>
        <div class="accordion" id="accordionExample">
          @foreach ($course_masters as $course)
          <div class="accordion-item course-search-item" data-course-search="{{ strtolower(($course->courseMaster->course_code ?? '') . ' ' . ($course->courseMaster->course_title ?? '') . ' ' . ($course->courseMaster->papertypemaster->name ?? '')) }}">
            <h2 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne{{$course->id}}" aria-expanded="true" aria-controls="collapseOne">
                <small>#{{$course->id}}</small>
                <span class="badge badge-warning mx-2">{{$course->courseMaster->papertypemaster->name ?? ''}} - {{$course->courseMaster->course_code ?? ''}} </span>
                <span class="mx-2 "> <small>{{$course->courseMaster->course_title ?? ''}}</small></span>
                <a href="{{ route('subject.course.unlinker', [$course->id]) }}"
                  onclick="return confirm('Are you sure you want to unlink this subject from the course?\n\n✅ This will only remove the association.\n❌ No subject or course records will be deleted.');">
                  <span class="mx-5">
                    <i class="fa fa-unlink text-danger"></i>
                  </span>
                </a>
              </button>
            </h2>
            <div id="collapseOne{{$course->id}}" class="accordion-collapse collapse " data-bs-parent="#accordionExample">
              <!-- Fetch list of csos -->
              <div class="accordion-body">
                @php
                $cohascos = $course->courseMaster->csos ?? collect();
                @endphp

                @if($cohascos->count())
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0">
                    <thead>
                      <tr>
                        <th style="width: 60px;">#</th>
                        <th>CohasCo</th>
                        <th style="width: 130px;">Lectures Needed</th>
                        <th style="width: 110px;">Shift</th>
                        <th>Subunits</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($cohascos as $cohasco)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $cohasco->title ?? '-' }}</td>
                        <td>{{ $cohasco->lectures_needed ?? '-' }}</td>
                        <td>{{ $cohasco->shift ?? '-' }}</td>
                        <td>
                          @if(($cohasco->csosubunits ?? collect())->count())
                          <ul class="mb-0 ps-3">
                            @foreach($cohasco->csosubunits as $subunit)
                            <li>{{ $subunit->title ?? '-' }}</li>
                            @endforeach
                          </ul>
                          @else
                          <span class="text-muted">No subunits mapped</span>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                @else
                <span class="text-muted">No CohasCos mapped for this course.</span>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card p-2 shadow">
        <div class="card-header">
          <h5 class="mb-0">Syllabus Status</h5>
        </div>
        <div class="p-2">
          <input type="text" id="syllabusStatusSearchInput" class="form-control" placeholder="Search syllabus status by course...">
        </div>
        <div class="card-body" style="max-height: 520px; overflow-y: auto;">
          @if(count($course_masters ?? []))
          @foreach ($course_masters as $course)
          @php
          $courseSyllabi = $syllabus_by_course[$course->course_master_id] ?? collect();
          @endphp
          <div class="border rounded p-2 mb-2 syllabus-search-item" data-syllabus-search="{{ strtolower(($course->courseMaster->course_code ?? '') . ' ' . ($course->courseMaster->course_title ?? '')) }}">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <div class="small text-muted">{{ $course->courseMaster->course_code ?? '-' }}</div>
                <div class="fw-semibold">{{ $course->courseMaster->course_title ?? '-' }}</div>
              </div>
              @if($courseSyllabi->count())
              <span class="badge bg-success">Created</span>
              @else
              <span class="badge bg-danger">Not Created</span>
              @endif
            </div>

            @if($courseSyllabi->count())
            <div class="mt-2">
              @foreach($courseSyllabi as $syllabus)
              <div class="small mb-1">
                <span class="badge bg-light text-dark border">Batch: {{ $syllabus->batchmaster->batch_name ?? '-' }}</span>
                <span class="badge bg-light text-dark border">Semester: {{ $syllabus->semestermaster->title ?? '-' }}</span>
              </div>
              @endforeach
            </div>
            @else
            <div class="small text-muted mt-2">No syllabus added yet for any semester or batch.</div>
            @endif
          </div>
          @endforeach
          @else
          <div class="text-muted">No course masters found for this department.</div>
          @endif
        </div>
      </div>
    </div>

  </div>



</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const courseSearchInput = document.getElementById('courseSearchInput');
    const courseItems = document.querySelectorAll('.course-search-item');

    if (courseSearchInput && courseItems.length) {
      courseSearchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        courseItems.forEach(function(item) {
          const haystack = item.getAttribute('data-course-search') || '';
          item.style.display = haystack.includes(query) ? '' : 'none';
        });
      });
    }

    const syllabusSearchInput = document.getElementById('syllabusStatusSearchInput');
    const syllabusItems = document.querySelectorAll('.syllabus-search-item');

    if (syllabusSearchInput && syllabusItems.length) {
      syllabusSearchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();

        syllabusItems.forEach(function(item) {
          const haystack = item.getAttribute('data-syllabus-search') || '';
          item.style.display = haystack.includes(query) ? '' : 'none';
        });
      });
    }
  });
</script>

@include('includes.footer')
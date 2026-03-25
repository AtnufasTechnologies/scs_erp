@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Records</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <h2 class="fw-bold">Attendance Records</h2>
          <p class="text-muted mb-1">
            <strong>Subject:</strong> {{ $syllabusAssignment->syllabus->subject->title ?? 'N/A' }}
          </p>
          <p class="text-muted">
            <strong>Course:</strong> {{ $syllabusAssignment->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
            ({{ $syllabusAssignment->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }}) |
            <strong>Semester:</strong> {{ $syllabusAssignment->syllabus->semestermaster->title ?? 'N/A' }}
          </p>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Statistics Summary -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Attendance Statistics</h5>
        </div>
        <div class="card-body">
          @if(empty($statistics))
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No students enrolled in this subject.
          </div>
          @else
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-light">
                <tr>
                  <th width="5%">#</th>
                  <th width="15%">Reg No</th>
                  <th width="30%">Student Name</th>
                  <th width="12%" class="text-center">Present</th>
                  <th width="12%" class="text-center">Absent</th>
                  <th width="12%" class="text-center">Late</th>
                  <th width="14%" class="text-center">Percentage</th>
                </tr>
              </thead>
              <tbody>
                @foreach($statistics as $index => $stat)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $stat['student']->reg_no ?? 'N/A' }}</td>
                  <td><span class="text-capitalize">{{ $stat['student']->first_name }} {{ $stat['student']->middle_name }} {{ $stat['student']->last_name }}</span></td>
                  <td class="text-center"><span class="badge bg-success">{{ $stat['present'] }}</span></td>
                  <td class="text-center"><span class="badge bg-danger">{{ $stat['absent'] }}</span></td>
                  <td class="text-center"><span class="badge bg-warning">{{ $stat['late'] }}</span></td>
                  <td class="text-center">
                    @php
                    $percentage = $stat['percentage'];
                    $color = $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                    @endphp
                    <span class="badge bg-{{ $color }} px-3">{{ $percentage }}%</span>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>

      <!-- Attendance Records by Date -->
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Records by Date</h5>
        </div>
        <div class="card-body">
          @if($attendanceRecords->isEmpty())
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No attendance records found for this subject.
          </div>
          @else
          <div class="accordion" id="attendanceAccordion">
            @foreach($attendanceRecords as $date => $records)
            <div class="accordion-item">
              <h2 class="accordion-header" id="heading{{ $loop->index }}">
                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}"
                  aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                  aria-controls="collapse{{ $loop->index }}">
                  <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y (l)') }}</strong>
                  <span class="ms-3 badge bg-primary">{{ $records->count() }} Records</span>
                </button>
              </h2>
              <div id="collapse{{ $loop->index }}"
                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                aria-labelledby="heading{{ $loop->index }}"
                data-bs-parent="#attendanceAccordion">
                <div class="accordion-body">
                  @foreach($records->groupBy('lecture_start_time') as $time => $lectureRecords)
                  <div class="mb-4">
                    <h6 class="text-primary">
                      <i class="bi bi-clock me-2"></i>Lecture Time:
                      {{ \Carbon\Carbon::parse($time)->format('h:i A') }}
                      @if($lectureRecords->first()->lecture_end_time)
                      - {{ \Carbon\Carbon::parse($lectureRecords->first()->lecture_end_time)->format('h:i A') }}
                      @endif
                    </h6>
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered">
                        <thead class="table-light">
                          <tr>
                            <th width="5%">#</th>
                            <th width="15%">Reg No</th>
                            <th width="35%">Student Name</th>
                            <th width="15%" class="text-center">Status</th>
                            <th width="30%">Remarks</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($lectureRecords as $record)
                          <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $record->student->reg_no ?? 'N/A' }}</td>
                            <td>{{ $record->student->first_name }} {{ $record->student->middle_name }} {{ $record->student->last_name }}</td>
                            <td class="text-center">
                              @if($record->status === 'present')
                              <span class="badge bg-success"><i class="bi bi-check"></i> Present</span>
                              @elseif($record->status === 'absent')
                              <span class="badge bg-danger"><i class="bi bi-x"></i> Absent</span>
                              @elseif($record->status === 'late')
                              <span class="badge bg-warning"><i class="bi bi-clock"></i> Late</span>
                              @elseif($record->status === 'excused')
                              <span class="badge bg-info"><i class="bi bi-file-text"></i> Excused</span>
                              @endif
                            </td>
                            <td>{{ $record->remarks ?? '-' }}</td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>

      <div class="mt-4">
        <a href="{{ route('faculty.attendance.index') }}" class="btn btn-secondary">
          <i class="bi bi-arrow-left me-1"></i>Back to Subjects
        </a>
        <a href="{{ route('faculty.attendance.take', $syllabusAssignment->id) }}" class="btn btn-primary">
          <i class="bi bi-plus-circle me-1"></i>Take New Attendance
        </a>
      </div>
    </div>
  </main>
</div>

<style>
  .accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #0d6efd;
  }
</style>

@include('includes.footer')
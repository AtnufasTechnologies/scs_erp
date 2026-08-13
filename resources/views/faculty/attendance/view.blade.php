<?php

use Carbon\Carbon;

?>
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


      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Filters, Export, and Print -->

      <form action="" method="get">
        <div class="row">
          <div class="col-lg-3">
            <label for="">Attendance Date</label>
            <input type="date" name="attendance_date" class="form-control" value="{{ request('attendance_date') }}">
          </div>
          <div class="col-lg-6">
            <div class="mb-4">

              <label for="">Select Subject</label>
              <div class="input-group">

                <select class="form-select " name="course_filter">
                  <option value="" selected disabled>My Subjects</option>
                  @foreach($syllabusAssignments as $item)
                  <option value="{{ $item->syllabus->courseLink->courseMaster->id}}" {{ (string) request('course_filter') === (string) ($item->syllabus->courseLink->courseMaster->id ?? '') ? 'selected' : '' }}>
                    @php
                    $deliveryType = trim((string) ($item->teachingAssignment->delivery_type ?? $item->teachingAllocation->delivery_type ?? 'Regular'));
                    @endphp
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                    | Delivery: {{ $deliveryType }}
                  </option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-warning"><i class="fa fa-search"></i></button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>




    <!-- Attendance Records Table (Simple Layout) -->

    <div class="card-header">
      <h5 class="mb-0"><i class="fa fa-calendar-check me-2"></i>Attendance Records </h5>
    </div>
    <div class="card-body">
      @if($attendanceRecords->isEmpty())
      <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>No attendance records found for this subject.
      </div>
      @else
      <div class="">
        <table class="table table-hover table-bordered attendance-table" id="exportTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Semester</th>
              <th>Hour</th>
              <th>Course Type</th>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Roll No</th>
              <th>Student Name</th>
              <th class="text-center">Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

            @foreach($attendanceRecords as $record)
            <tr class="attendance-row"
              data-student="{{ strtolower($record->student->first_name . ' ' . $record->student->last_name . ' ' . $record->student->register_no) }}"
              data-date="{{ $record->attendance_date }}">
              <td>{{$loop->iteration}}</td>
              <td>{{ date('d M Y', strtotime($record->attendance_date)) }}</td>
              <td>{{ $record->semester_id }}</td>
              <td>{{$record->hourmaster->name }} - {{$record->hourmaster->start_time }}</td>
              <td>{{ $record->courseinfo->coursetypemaster->title ?? 'N/A' }}</td>
              <td>{{ $record->courseinfo->course_code ?? 'N/A' }}</td>
              <td>{{ $record->courseinfo->course_title ?? 'N/A' }}</td>
              <td><span class="text-uppercase">{{ $record->student->roll_no ?? 'N/A' }}</span></td>
              <td><span class="text-capitalize">{{ $record->student->first_name }} {{ $record->student->middle_name }} {{ $record->student->last_name }}</span></td>
              <td class="text-center">
                @if($record->status === 'present')
                <span class="badge bg-success"><i class="fa fa-check"></i> Present</span>
                @else($record->status === 'absent')
                <span class="badge bg-danger"><i class="fa fa-times"></i> Absent</span>
                @endif
                @if($record->extra === 'late')
                <span class="badge bg-warning"><i class="fa fa-clock"></i> Late</span>
                @elseif($record->extra === 'excused')
                <span class="badge bg-info"><i class="fa fa-file-text"></i> Excused</span>
                @endif
              </td>

              <td>

                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editAttendanceModal{{ $record->id }}">
                  <i class="fa fa-edit"></i>
                </button>

                <!-- Edit Attendance Modal -->
                <div class="modal fade" id="editAttendanceModal{{ $record->id }}" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content" style="border-radius: 16px;">
                      <div class="modal-header border-0">
                        <h5 class="modal-title"><i class="fa fa-edit text-primary me-2"></i>Edit Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="{{ route('faculty.attendance.update', $record->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Attendance Date</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d', strtotime($record->attendance_date)) }}" name="attendance_date" readonly>
                          </div>

                          <div class="mb-3">
                            <label class="form-label fw-semibold">RollNo - {{ $record->student->roll_no }}</label>
                            <input type="text" class="form-control text-capitalize" value="{{ $record->student->first_name }} {{ $record->student->last_name }}" disabled>
                          </div>
                          <div class="mb-3">
                            <label class="form-label fw-semibold">Attendance Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                              <option value="present" {{ $record->status === 'present' ? 'selected' : '' }}>Present</option>
                              <option value="absent" {{ $record->status === 'absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label fw-semibold">Extra Remark <span class="text-danger">*</span></label>
                            <select name="extra" class="form-select" required>
                              <option value="">--Select--</option>
                              <option value="late" {{ $record->status === 'late' ? 'selected' : '' }}>Late</option>
                              <option value="excused" {{ $record->status === 'excused' ? 'selected' : '' }}>Excused</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer border-0">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              </td>

            </tr>
            @endforeach

          </tbody>
        </table>
      </div>
      @endif
    </div>



</div>
</main>
</div>


@include('includes.footer')
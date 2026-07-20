<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\ProgramMaster;
use App\Models\Semester;
use App\Models\StudentProgram;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasRoutine;
use App\Models\SpecializationMaster;


$batches = BatchMaster::latest()->get();
$semesters = Semester::get();
$course_master = SubjectCourseMaster::with('courseMaster')->where('subject_id', $data->id)->get();
$faculties = Faculty::all();
$mainStreams = ProgramMaster::all();

$deptFacultyIds = collect($deptfaculties ?? [])->pluck('faculty_id')->filter()->unique()->values()->all();
$facultyIdsWithTimetable = [];
if (!empty($deptFacultyIds)) {
  $facultyIdsWithTimetable = SubjectHasRoutine::whereIn('faculty_id', $deptFacultyIds)
    ->whereHas('syllabus', function ($query) use ($data) {
      $query->where('subject_id', $data->id);
    })
    ->distinct()
    ->pluck('faculty_id')
    ->map(fn($id) => (int) $id)
    ->all();
}
?>
@include('includes.header')
@include('includes.dept-sidebar')

<style>
  /* Custom scrollbar for activities section */
  .activities-scroll::-webkit-scrollbar {
    width: 8px;
  }

  .activities-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .activities-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
  }

  .activities-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  }

  /* For Firefox */
  .activities-scroll {
    scrollbar-width: thin;
    scrollbar-color: #667eea #f1f1f1;
  }

  .quick-scroll-row {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 4px 4px 12px;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
  }

  .quick-scroll-row .quick-item {
    flex: 0 0 clamp(220px, 24vw, 280px);
    min-width: 220px;
    scroll-snap-align: start;
  }

  .quick-scroll-row .stats-card,
  .quick-scroll-row .action-card {
    height: 100%;
  }

  .quick-scroll-row::-webkit-scrollbar {
    height: 8px;
  }

  .quick-scroll-row::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
  }

  @media (max-width: 768px) {
    .quick-scroll-row .quick-item {
      flex-basis: 78vw;
      min-width: 78vw;
    }
  }
</style>

<!-- Main Content -->
<div class="main-content">
  @php
  $notificationCount = count($upcomingActivities ?? []);
  @endphp

  <nav class="navbar navbar-expand-lg bg-white border rounded-3 shadow-sm px-3 py-2 mb-3">
    <div class="container-fluid px-0">
      <a class="navbar-brand fw-bold" href="#" style="color: #1f2937;">
        {{ $data->title ?? 'Department Dashboard' }}
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#deptNavbar" aria-controls="deptNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="deptNavbar">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-2 mt-lg-0">

          <li class="nav-item dropdown">
            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-bell fs-5 text-dark"></i>
              @if($notificationCount > 0)
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $notificationCount }}
              </span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 300px;">
              <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center">
                <span>Notifications</span>
                @if($notificationCount > 0)
                <span class="badge bg-primary">{{ $notificationCount }}</span>
                @endif
              </li>
              @if($notificationCount > 0)
              <li>
                <a class="dropdown-item d-flex justify-content-between" href="{{ route('department.activities.index', [$data->id]) }}">
                  Upcoming Activities
                  <span class="badge bg-primary">{{ $notificationCount }}</span>
                </a>
              </li>
              @else
              <li><span class="dropdown-item-text text-muted">No new notifications</span></li>
              @endif
              <li><a class="dropdown-item" href="{{ route('department.substitution.history.page') }}">Substitution History</a></li>
              <li><a class="dropdown-item" href="{{ route('department.admission.list') }}">Admission Applications</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>



  <div class="row g-4">
    <div class="quick-scroll-row">
      <!-- Quick Stats -->
      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Course Master</div>
              <div style="font-size: 36px; font-weight: 700;">{{ $data->courseMasterPivot->count() ?? 0 }}</div>
              <a href="{{route('department.course.master',[$data->id,$data->slug])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">View Details →</a>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-book" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Syllabus </div>
              <div style="font-size: 36px; font-weight: 700;">{{ $syllabusCount ?? 0 }}</div>
              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{route('department.syllabus.manager',['id'=>$data->id,'slug'=>$data->slug])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manager →</a>
              </div>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-box" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="quick-item">

        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faculty</div>
              <div style="font-size: 36px; font-weight: 700;">{{count($deptfaculties)}} </div>


              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{ route('department.faculty.access', [$data->id,$data->slug]) }}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manage →</a>
              </div>
            </div>

            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chalkboard-teacher" style="font-size: 28px;"></i>
            </div>

          </div>
        </div>

      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Program</div>
              <div style="font-size: 36px; font-weight: 700;">Specialization </div>

              <div style="opacity: 0.9; font-size: 13px;">
                <a href="{{route('department.specialization.master',[ $data->id, $data->title])}}" style="color: white; opacity: 0.9; font-size: 13px; text-decoration: none;">Manage →</a>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="quick-item">
        <div class="stats-card gradient-green">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Time Table</div>
              <div style="font-size: 36px; font-weight: 700;">
                <a href="{{ route('department.timetable', [$data->id,$data->title]) }}" style="color: white; text-decoration: none;">Scheduler</a>
              </div>
              <div style="opacity: 0.9; font-size: 13px;">Manager →</div>
            </div>
            <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calendar-alt" style="font-size: 28px;"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="quick-item">

        <div class="action-card gradient-green">
          <div class="action-card-icon">
            <i class="fas fa-exchange-alt"></i>
          </div>
          <div>
            <a href="{{ route('department.substitution', [$data->id]) }}" style="color:yellow;">
              <h6 class="mb-1" style="font-weight: 700;">Manage Substitution</h6>
            </a>
            <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Get a reminder to help with your studying process.</p>
          </div>

          <div class="mt-2">
            <a href="{{ route('department.substitution.history.page') }}" style="color:yellow; font-size: 13px; font-weight:bold">
              View Substitution History →</a>
          </div>
        </div>

      </div>

      <div class="quick-item">
        <a href="{{route('department.admission.list')}}" style="text-decoration: none;">
          <div class="action-card gradient-green">
            <div class="action-card-icon">
              <i class="fas fa-certificate"></i>
            </div>
            <div>
              <h6 class="mb-1" style="font-weight: 700;">Admission Portal</h6>
              <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Stay updated with registrations and applications</p>
            </div>
            <div class="mt-2" style="font-size: 13px; opacity: 0.9;">View Now →</div>
          </div>
        </a>
      </div>

    </div>

    <!-- Left Column: Today's Course -->
    <div class="col-lg-5">
      <!-- Upcoming Activities Section -->
      @if(count($upcomingActivities) > 0)
      <div class="mb-4">
        <div class="p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">
              <i class="fas fa-calendar-star me-2" style="color: #fbbf24;"></i>Upcoming Activities
            </h5>
            <a href="{{ route('department.activities.index', [$data->id]) }}" class="btn btn-modern" style="background: #5b4cdb; color: white;">
              <i class="fas fa-calendar-check me-2"></i>View All ({{ $activityStats['total'] ?? 0 }})
            </a>
          </div>
          <div class="row g-3 activities-scroll" style="max-height: 500px; overflow-y: auto; overflow-x: hidden; padding-right: 10px;">
            @foreach($upcomingActivities as $activity)
            <div class="col-md-12">
              <div class="course-card" style="border-left: 4px solid #667eea;">
                <div class="d-flex align-items-start gap-3">
                  <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-calendar-day" style="color: white; font-size: 20px;"></i>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600;">{{ $activity->title }}</h6>
                    <div class="mb-2">
                      <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                        {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                      </span>
                    </div>
                    <p class="mb-1" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-calendar me-1" style="color: #667eea;"></i>{{ $activity->formatted_date }}
                    </p>
                    @if($activity->start_time)
                    <p class="mb-1" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-clock me-1" style="color: #667eea;"></i>{{ date('h:i A', strtotime($activity->start_time)) }}
                    </p>
                    @endif
                    @if($activity->venue)
                    <p class="mb-2" style="font-size: 13px; color: #6b7280;">
                      <i class="fas fa-map-marker-alt me-1" style="color: #667eea;"></i>{{ Str::limit($activity->venue, 30) }}
                    </p>
                    @endif
                    @if($activity->expected_participants)
                    <p class="mb-0" style="font-size: 12px; color: #6b7280;">
                      <i class="fas fa-users me-1 text-warning fa-2x"></i>{{ $activity->expected_participants }} attendees expected |
                      <i class="fas fa-user-check me-1 text-success fa-2x"></i>{{ $activity->participants_count }} confirmed
                    </p>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

    </div>

    <!-- Right Column: Stats and Actions -->
    <div class="col-lg-7">
      <div class="row g-3 mb-4">

      </div>
    </div>
  </div>


  <!-- Program Combinations Section -->
  <div class="table-modern mt-4">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">Program Combinations</h5>
        <button class="btn btn-modern" style="background: #5b4cdb; color: white;" data-bs-toggle="modal" data-bs-target="#programConnect">
          <i class="fas fa-plus-circle me-2"></i>Add Program
        </button>
      </div>

      <div class="mb-3">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
          <label for="batchFilter" class="fw-semibold" style="color: #6b7280;">Filter by Batch:</label>
          <select name="batch" id="batchFilter" class="form-select" style="width: 200px; border-radius: 10px; border: 1px solid #e5e7eb;" onchange="this.form.submit()">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
        </form>
      </div>

      @if(count($combinations))
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr style="border-bottom: 2px solid #fac01f;">
              <th style="color: #e9ebef; font-weight: 600; padding: 16px;">#</th>
              <th style="color: #e9ebef; font-weight: 600;">Curriculam</th>
              <th style="color: #e9ebef; font-weight: 600;">Tracking ID</th>
              <th style="color: #e9ebef; font-weight: 600;">Batch</th>
              <th style="color: #e9ebef; font-weight: 600;">Code</th>
              <th style="color: #e9ebef; font-weight: 600;">Program</th>
              <th style="color: #e9ebef; font-weight: 600;">Specialization</th>
              <th style="color: #e9ebef; font-weight: 600;">Program Type</th>
              <th style="color: #e9ebef; font-weight: 600;">Total Seats</th>
              <th style="color: #e9ebef; font-weight: 600;">Available Seats</th>
              <th style="color: #e9ebef; font-weight: 600;">Enrolled </th>
              <th style="color: #e9ebef; font-weight: 600;">Edit</th>
              <th style="color: #e9ebef; font-weight: 600;">Action</th>
            </tr>
          </thead>
          <tbody>
            @php
            $specializations = SpecializationMaster::where('subject_id', $data->id)->where('is_active', 1)->orderBy('name')->get();
            @endphp
            @forelse($combinations as $combination)
            <tr style="border-bottom: 1px solid #f5f5f5;">
              <td style="padding: 16px; color: #1a1a1a; font-weight: 500;">{{ $loop->iteration }}</td>
              <td>
                <a href="{{ route('curriculam.builder.engine', [$combination->id, $combination->studentprograminfo->code]) }}">
                  <button class=" btn-sm btn-dark"><i class="fas fa-drafting-compass"></i> Build</button>
                </a>
              </td>
              <td>
                <span class="badge" style="background: #43cea2; padding: 6px 12px; border-radius: 8px;">ID: {{ $combination->id ?? '-' }}</span>
              </td>

              <td style="color: #1a1a1a;">{{$combination->batchmaster->batch_name ?? '-'}}</td>
              <td style="color: #1a1a1a;">
                <a href="{{ route('department.show.student.list', ['program_id' => $combination->studentprograminfo->id,
                 'slug' => $combination->studentprograminfo->name, 'batch_id' => $combination->batchmaster->id]) }}">
                  {{ $combination->studentprograminfo->code ?? '-' }}
                </a>
              </td>
              <td style="color: #1a1a1a;">
                <a href="{{ route('department.show.student.list', ['program_id' => $combination->studentprograminfo->id,
                 'slug' => $combination->studentprograminfo->name, 'batch_id' => $combination->batchmaster->id]) }}">
                  {{ $combination->studentprograminfo->name ?? '-' }}
                </a>
              </td>
              <td>
                @php
                $selectedSpecializationIds = collect($combination->specialization_ids ?? [])->map(fn($id) => (int) $id)->all();
                $connectedSpecializations = $specializations->whereIn('id', $selectedSpecializationIds);
                @endphp

                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                  @forelse($connectedSpecializations as $specialization)
                  <span class="badge badge-warning ">{{ $specialization->name }}</span>
                  @empty
                  <span class="badge badge-dark">No specialization</span>
                  @endforelse

                  <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSpecialization{{ $combination->id }}" title="Connect Specializations">
                    <i class="fa fa-plus-circle"></i>
                  </button>
                </div>

                <div class="modal fade" id="addSpecialization{{ $combination->id }}" tabindex="-1" aria-labelledby="addSpecializationLabel{{ $combination->id }}" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="{{ route('department.combination.specializations.update', $combination->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                          <h5 class="modal-title" id="addSpecializationLabel{{ $combination->id }}">Connect Specializations - {{ $combination->studentprograminfo->code ?? '' }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <label class="form-label">Select Specializations</label>
                          <select name="specialization_ids[]" class="select-multiple" multiple size="8">
                            @foreach($specializations as $specialization)
                            <option value="{{ $specialization->id }}" {{ in_array((int) $specialization->id, $selectedSpecializationIds, true) ? 'selected' : '' }}>
                              {{ $specialization->name }}
                            </option>
                            @endforeach
                          </select>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-primary">Save specializations</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
              <td><span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; border-radius: 8px;">{{$combination->program_type}}</span></td>
              <td>{{ $combination->total_seats ?? '-' }}</td>
              <td>{{ $combination->total_available_seats ?? '-' }}</td>
              <td>{{ $combination->studentmaster_count }}</td>
              <td>
                <!-- Button trigger modal -->
                <button type="button" class="btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{ $combination->id }}">
                  <i class="fa fa-edit"></i>
                </button>

                <!-- Modal -->
                <div class="modal fade" id="edit{{ $combination->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit {{$combination->batchmaster->batch_name ?? '-'}} - {{ $combination->studentprograminfo->name ?? '' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form action="{{ route('department.combination.update', $combination->id) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <div class="mb-3">
                            <label for="totalSeats{{ $combination->id }}" class="form-label">Total Seats</label>
                            <input type="number" class="form-control" id="totalSeats{{ $combination->id }}" name="total_seats" value="{{ $combination->total_seats ?? '' }}" required>
                          </div>

                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>

              </td>

              <td>
                <form action="{{ route('department.combination.delete', $combination->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this combination?');" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm" style="background: #fee; color: #dc2626; border: none; border-radius: 8px; padding: 6px 12px;">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center" style="padding: 40px; color: #6b7280;">
                <i class="fas fa-inbox fa-3x mb-3" style="color: #e5e7eb;"></i>
                <p>No combinations found.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @else
      <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x mb-3" style="color: #e5e7eb;"></i>
        <p style="color: #6b7280;">No combinations found.</p>
      </div>
      @endif
    </div>
  </div>
  <!-- Faculty Section -->
  <div class="table-modern mt-4">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">Departmental Faculties</h5>
        <button class="btn btn-modern" style="background: #5b4cdb; color: white;" data-bs-toggle="modal" data-bs-target="#addFaculty">
          <i class="fas fa-plus-circle me-2"></i>Add Faculty
        </button>
      </div>

      @if(count($deptfaculties))
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr style="border-bottom: 2px solid #fac01f;">
              <th style="color: #fff; font-weight: 600; padding: 16px;">#</th>
              <th style="color: #fff; font-weight: 600;">Faculty Code</th>
              <th style="color: #fff; font-weight: 600;">Faculty</th>
              <th style="color: #fff; font-weight: 600;">Joining Date</th>
              <th style="color: #fff; font-weight: 600;">Mobile</th>
              <th style="color: #fff; font-weight: 600;">Mail</th>
              <th style="color: #fff; font-weight: 600;">Timetable</th>
              <th style="color: #fff; font-weight: 600;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deptfaculties as $faculty)
            @php
            $facultyUserId = (int) ($faculty->faculty->id ?? 0);
            $hasTimetable = in_array($facultyUserId, $facultyIdsWithTimetable, true);
            @endphp
            <tr style="border-bottom: 1px solid #f5f5f5;">
              <td style="padding: 16px; color: #1a1a1a; font-weight: 500;">{{ $loop->iteration }}</td>
              <td style="color: #1a1a1a;">{{ $faculty->faculty->USER_CODE ?? '-' }}</td>
              <td style="color: #1a1a1a;">{{ $faculty->faculty->FIRST_NAME ?? '-' }} {{ $faculty->faculty->LAST_NAME ?? '-' }}</td>
              <td style="color: #6b7280;">{{ $faculty->faculty->DOJ ?? '-' }}</td>
              <td style="color: #6b7280;">{{$faculty->faculty->MOBILE_NO ?? '-'}}</td>
              <td style="color: #6b7280;">{{$faculty->faculty->MAIL_ID ?? '-'}}</td>
              <td>
                <a href="{{ route('department.faculty.timetable', $faculty->faculty->id) }}" class="btn btn-sm btn-modern" style="background: {{ $hasTimetable ? '#16a34a' : '#dc2626' }}; color: white;">
                  <i class="fas {{ $hasTimetable ? 'fa-calendar-check' : 'fa-calendar-times' }} me-1"></i>
                  {{ $hasTimetable ? 'Timetable' : 'No Timetable' }}
                </a>
              </td>
              <td>
                <form action="{{ route('department.faculty.delete', $faculty->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this faculty?');" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm" style="background: #fee; color: #dc2626; border: none; border-radius: 8px; padding: 6px 12px;">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="text-center py-5">
        <i class="fas fa-user-slash fa-3x mb-3" style="color: #e5e7eb;"></i>
        <p style="color: #6b7280;">No faculties assigned yet.</p>
      </div>
      @endif
    </div>
  </div>

  <!-- Subject Combinations Section -->
  <!-- <div class="table-modern mt-4">
    <div class="p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: #1a1a1a; font-weight: 700; margin: 0;">
          <i class="fas fa-layer-group me-2" style="color: #5b4cdb;"></i>Subject Combinations
        </h5>
        <button class="btn btn-modern" style="background: #5b4cdb; color: white;" data-bs-toggle="modal" data-bs-target="#addSubjectCombination">
          <i class="fas fa-plus-circle me-2"></i>Add Combination
        </button>
      </div>

      @if($subjectCombinationsGrouped->count() > 0)
      <div class="row g-3">
        @foreach($subjectCombinationsGrouped as $key => $rows)
        @php $first = $rows->first(); @endphp
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm" style="border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); padding: 16px 20px;">
              <div style="font-size: 12px; color: rgba(255,255,255,0.75); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                {{ $first->batch->batch_name ?? '–' }} &bull; {{ $first->campus->name ?? '–' }}
              </div>
              <div style="font-size: 15px; color: white; font-weight: 700;">
                <i class="fas fa-book me-1"></i>{{ $first->mainSubject->title ?? '–' }}
              </div>
            </div>
            <div class="p-3">
              <p style="font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Combo Subjects</p>
              @foreach($rows as $row)
              <div class="d-flex justify-content-between align-items-center mb-2" style="background: #f9fafb; border-radius: 8px; padding: 8px 12px;">
                <span style="font-size: 13px; color: #374151;">{{ $row->comboSubject->title ?? '–' }}</span>
                <a href="{{ url('erp/admin/master/delete-subject-combination/' . $row->id) }}"
                  onclick="return confirm('Remove this combo subject?')"
                  style="color: #dc2626; font-size: 12px; text-decoration: none; flex-shrink: 0; margin-left: 8px;">
                  <i class="fas fa-times-circle"></i>
                </a>
              </div>
              @endforeach
              <button class="btn btn-sm w-100 mt-2 btn-modern"
                style="background: #ede9fe; color: #5b4cdb; font-size: 12px;"
                data-bs-toggle="modal"
                data-bs-target="#addMoreCombos_{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}">
                <i class="fas fa-plus me-1"></i>Add More Combos
              </button>
            </div>
          </div>
        </div>


        <div class="modal fade" id="addMoreCombos_{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-md">
            <div class="modal-content" style="border-radius: 20px; border: none;">
              <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px; background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); border-radius: 20px 20px 0 0;">
                <h5 class="modal-title" style="color: white; font-weight: 700;">
                  Add More Combos — {{ $first->mainSubject->title ?? '' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{ url('erp/admin/master/subject-combination') }}" method="POST">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $first->batch_id }}">
                <input type="hidden" name="campus_id" value="{{ $first->campus_id }}">
                <input type="hidden" name="main_subject_id" value="{{ $first->main_subject_id }}">
                <div class="modal-body" style="padding: 24px;">
                  <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px; display: block;">Select Combo Subject(s)</label>
                  <select name="combo_subject_ids[]" class="form-select select-multiple" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 10px;" multiple required>
                    @foreach($allSubjects as $subj)
                    <option value="{{ $subj->id }}">{{ $subj->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
                  <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-modern" style="background: #5b4cdb; color: white;">Add</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="text-center py-5">
        <i class="fas fa-layer-group fa-3x mb-3" style="color: #e5e7eb;"></i>
        <p style="color: #6b7280;">No subject combinations defined yet.</p>
      </div>
      @endif
    </div>
  </div> -->

  <!-- Add Subject Combination Modal -->
  <!-- <div class="modal fade" id="addSubjectCombination" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px; background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); border-radius: 20px 20px 0 0;">
          <h5 class="modal-title" style="color: white; font-weight: 700;">
            <i class="fas fa-layer-group me-2"></i>New Subject Combination
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ url('erp/admin/master/subject-combination') }}" method="POST">
          @csrf
          <input type="hidden" name="main_subject_id" value="{{ $data->id }}">
          <div class="modal-body" style="padding: 24px;">
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px; display: block;">Academic Batch</label>
                <select name="batch_id" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">-- Select Batch --</option>
                  @foreach($allBatches as $batch)
                  <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px; display: block;">Campus</label>
                <select name="campus_id" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">-- Select Campus --</option>
                  @foreach($allCampuses as $campus)
                  <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <label style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px; display: block;">Combo Subject(s)</label>
            <select name="combo_subject_ids[]" class="form-select select-multiple" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 10px;" multiple required>
              @foreach($allSubjects as $subj)
              <option value="{{ $subj->id }}">{{ $subj->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #5b4cdb; color: white;">Save Combination</button>
          </div>
        </form>
      </div>
    </div>
  </div> -->

  <!-- Modals -->
  <!-- Add Program Modal -->
  <div class="modal fade" id="programConnect" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="exampleModalLabel">Connect Programs for {{$data->title}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('add.programs.to.subject')}}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body" style="padding: 24px;">
            <div class="row g-3 mb-3">
              <div class="col-5">
                <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Academic Batch</label>
                <select name="batch_id" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
                  @foreach ($batches as $batch)
                  <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-5">
                <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Program Type</label>
                <select name="program_type" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">-- Select Program Type --</option>
                  @foreach ($mainStreams as $ms)
                  <option value="{{ $ms->title }}">{{ $ms->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-2">
                <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Total Seats</label>
                <input type=" number" name="total_seats" class="form-control mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>

              </div>
            </div>

            <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Program</label>
            <select name="programs[]" class="form-select mb-3 select-multiple" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" multiple>
              @foreach ($programs as $prg)
              <option value="{{$prg->id}}">{{$prg->code}} - {{$prg->name}}</option>
              @endforeach
            </select>


            <input type="hidden" name="subject_id" value="{{$data->id}}">
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #43cea2; color: white;">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Faculty Modal -->
  <div class="modal fade" id="addFaculty" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="exampleModalLabel">Add Faculty for {{$data->title}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('dept.add.faculty.master')}}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body" style="padding: 24px;">
            <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Add Faculty</label>
            <select name="faculty[]" class="form-select mb-3 select-multiple" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" multiple>
              @foreach ($faculties as $faculty)

              <option value="{{$faculty->id}}">{{$faculty->USER_CODE}} - {{$faculty->FIRST_NAME}} {{$faculty->LAST_NAME}}</option>
              @endforeach
            </select>
            <input type="hidden" name="subject_id" value="{{$data->id}}">
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #43cea2; color: white;">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

@include('includes.footer')
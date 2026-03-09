<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\ProgramMaster;
use App\Models\Semester;
use App\Models\StudentProgram;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use Illuminate\Support\Facades\Auth;

$batches = BatchMaster::latest()->get();
$semesters = Semester::get();
$course_master = SubjectCourseMaster::with('courseMaster')->where('subject_id', $data->id)->get();
$faculties = Faculty::where('IS_LEFT', 0)->get();
$mainStreams = ProgramMaster::all();
?>
@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <!-- Welcome Header -->
  <div class="welcome-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e9e9e9 40%, #7c3aed 100%)">
    <div>
      <h2 class="mb-1" style="color: #1a1a1a; font-weight: 700;">Hello, {{ Auth::user()->name ?? 'User' }} 👋</h2>
      <p class="mb-0" style="color: #6b7280;">Nice to have you back, what an exciting day!</p>
      <p class="mb-0 mt-2" style="color: #6b7280;">Get ready and continue your work today.</p>
    </div>
    <div class="d-flex align-items-center gap-4">
      <div class="xp-badge">
        <div class="xp-coin">
          <i class="fas fa-user-graduate me-1" style="color: #b8860b; font-size: 18px;"></i>

        </div>
        <div>
          <div style="font-size: 24px; font-weight: 700; color: #1a1a1a;">{{ $data->students_count ?? 0 }} </div>
          <div style="font-size: 12px; color: #6b7280;">Students</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Department Info Bar -->
  <!-- <div class="d-flex align-items-center mb-4" style="background: white; padding: 16px 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
    <div class="d-flex align-items-center flex-grow-1">
      <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 16px;">
        <i class="fas fa-building" style="color: white; font-size: 20px;"></i>
      </div>
      <div>
        <h5 class="mb-0" style="color: #1a1a1a; font-weight: 700;">{{ $data->code ?? '-' }}</h5>
        <p class="mb-0" style="color: #6b7280; font-size: 14px;">{{ $data->title ?? '-' }}</p>
      </div>
    </div>
    @if(Auth::user()->role == 'dept-admin-erp')
    <a href="{{ url('logout') }}" class="btn btn-modern" style="background: #5b4cdb; color: white;">
      <i class="fas fa-external-link-alt me-2"></i>Admin Console
    </a>
    @endif
  </div> -->

  <div class="row g-4">
    <!-- Left Column: Today's Course -->
    <div class="col-lg-5">
      <h5 style="color: #1a1a1a; font-weight: 700; margin-bottom: 24px;">Today's Activities</h5>

      <!-- Course Card 1 -->
      <div class="course-card">
        <div class="d-flex align-items-center">
          <div class="progress-circle" style="background: linear-gradient(135deg, rgba(67, 206, 162, 0.15) 0%, rgba(14, 250, 179, 0.15) 100%);">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: conic-gradient(#43cea2 79%, #f0f0f0 0); display: flex; align-items: center; justify-content: center;">
              <div style="width: 48px; height: 48px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #43cea2;">
                79%
              </div>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600;">{{ $data->title ?? 'Course Management' }}</h6>
            <div class="d-flex align-items-center gap-3 mb-2">
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-book me-1"></i> {{ $data->courseMasterPivot->count() ?? 0 }} lessons</span>
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-clock me-1"></i> 50 min</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-tasks me-1"></i> 5 assignments</span>
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-user-graduate me-1"></i> {{ $data->students_count ?? 0 }} students</span>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <a href="{{route('department.course.master',[$data->id,$data->slug])}}" class="btn btn-sm btn-modern" style="background: #43cea2; color: white; flex: 1;">Continue</a>
          <button class="btn btn-sm btn-modern" style="background: #e6e6e6; color: #6b7280;">Skip</button>
        </div>
      </div>

      <!-- Course Card 2 -->
      <div class="course-card">
        <div class="d-flex align-items-center">
          <div class="progress-circle" style="background: linear-gradient(135deg, rgba(255, 153, 102, 0.15) 0%, rgba(255, 94, 98, 0.15) 100%);">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: conic-gradient(#ff9966 64%, #f0f0f0 0); display: flex; align-items: center; justify-content: center;">
              <div style="width: 48px; height: 48px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ff9966;">
                64%
              </div>
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1" style="color: #1a1a1a; font-weight: 600;">Faculty Management</h6>
            <div class="d-flex align-items-center gap-3 mb-2">
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-chalkboard-teacher me-1"></i> {{ count($deptfaculties) ?? 0 }} faculty</span>
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-clock me-1"></i> 45 min</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span style="font-size: 13px; color: #6b7280;"><i class="fas fa-tasks me-1"></i> 2 assignments</span>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-sm btn-modern" style="background: #ff9966; color: white; flex: 1;">Continue</button>
          <button class="btn btn-sm btn-modern" style="background: #e6e6e6; color: #6b7280;">Skip</button>
        </div>
      </div>
    </div>

    <!-- Right Column: Stats and Actions -->
    <div class="col-lg-7">
      <div class="row g-3 mb-4">
        <!-- Quick Stats -->
        <div class="col-md-6">
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

        <div class="col-md-6">
          <div class="stats-card gradient-red">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Syllabus </div>
                <div style="font-size: 36px; font-weight: 700;">{{ $data->students_count ?? 0 }}</div>
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

        <div class="col-md-6">
          <div class="stats-card gradient-yellow">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faculty Members</div>
                <div style="font-size: 36px; font-weight: 700;">{{ count($deptfaculties) ?? 0 }}</div>
                <div style="opacity: 0.9; font-size: 13px;">Active</div>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chalkboard-teacher" style="font-size: 28px;"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="stats-card gradient-purple">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Time Table</div>
                <div style="font-size: 36px; font-weight: 700;">
                  <a href="{{ route('department.timetable', [$data->id]) }}" style="color: white; text-decoration: none;">View</a>
                </div>
                <div style="opacity: 0.9; font-size: 13px;">Schedule</div>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-calendar-alt" style="font-size: 28px;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Cards -->
      <div class="row g-3">
        <div class="col-md-6">
          <a href="{{ route('department.substitution', [$data->id]) }}" style="text-decoration: none;">
            <div class="action-card gradient-orange">
              <div class="action-card-icon">
                <i class="fas fa-exchange-alt"></i>
              </div>
              <div>
                <h6 class="mb-1" style="font-weight: 700;">Manage Substitution</h6>
                <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Get a reminder to help with your studying process.</p>
              </div>
              <div class="mt-2">
                <a href="{{ route('department.substitution.history.page') }}" style="color: white; font-size: 13px; opacity: 0.9;">View History →</a>
              </div>
            </div>
          </a>
        </div>

        <div class="col-md-6">
          <a href="{{route('department.admission.list')}}" style="text-decoration: none;">
            <div class="action-card gradient-pink">
              <div class="action-card-icon">
                <i class="fas fa-certificate"></i>
              </div>
              <div>
                <h6 class="mb-1" style="font-weight: 700;">Admission Portal</h6>
                <p class="mb-0" style="font-size: 13px; opacity: 0.9;">Set targets, see reminders, analyze your study habits.</p>
              </div>
              <div class="mt-2" style="font-size: 13px; opacity: 0.9;">View Applications →</div>
            </div>
          </a>
        </div>
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

      @if(count($combinations))
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

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr style="border-bottom: 2px solid #f0f0f0;">
              <th style="color: #6b7280; font-weight: 600; padding: 16px;">#</th>
              <th style="color: #6b7280; font-weight: 600;">Batch</th>
              <th style="color: #6b7280; font-weight: 600;">Program</th>
              <th style="color: #6b7280; font-weight: 600;">Program Type</th>
              <th style="color: #6b7280; font-weight: 600;">Details</th>
              <th style="color: #6b7280; font-weight: 600;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($combinations as $combination)
            <tr style="border-bottom: 1px solid #f5f5f5;">
              <td style="padding: 16px; color: #1a1a1a; font-weight: 500;">{{ $loop->iteration }}</td>
              <td style="color: #1a1a1a;">{{$combination->batchmaster->batch_name ?? '-'}}</td>
              <td style="color: #1a1a1a;">{{ $combination->studentprograminfo->name ?? '-' }}</td>
              <td><span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 6px 12px; border-radius: 8px;">{{$combination->program_type}}</span></td>
              <td>
                <span class="badge" style="background: #43cea2; padding: 6px 12px; border-radius: 8px;">ID: {{ $combination->studentprograminfo->id ?? '-' }}</span>
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
            <tr style="border-bottom: 2px solid #f0f0f0;">
              <th style="color: #6b7280; font-weight: 600; padding: 16px;">#</th>
              <th style="color: #6b7280; font-weight: 600;">Faculty Code</th>
              <th style="color: #6b7280; font-weight: 600;">Faculty</th>
              <th style="color: #6b7280; font-weight: 600;">Joining Date</th>
              <th style="color: #6b7280; font-weight: 600;">Mobile</th>
              <th style="color: #6b7280; font-weight: 600;">Mail</th>
              <th style="color: #6b7280; font-weight: 600;">Timetable</th>
              <th style="color: #6b7280; font-weight: 600;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deptfaculties as $faculty)
            <tr style="border-bottom: 1px solid #f5f5f5;">
              <td style="padding: 16px; color: #1a1a1a; font-weight: 500;">{{ $loop->iteration }}</td>
              <td style="color: #1a1a1a;">{{ $faculty->faculty->USER_CODE ?? '-' }}</td>
              <td style="color: #1a1a1a;">{{ $faculty->faculty->FIRST_NAME ?? '-' }} {{ $faculty->faculty->LAST_NAME ?? '-' }}</td>
              <td style="color: #6b7280;">{{ $faculty->faculty->DOJ ?? '-' }}</td>
              <td style="color: #6b7280;">{{$faculty->faculty->MOBILE_NO ?? '-'}}</td>
              <td style="color: #6b7280;">{{$faculty->faculty->MAIL_ID ?? '-'}}</td>
              <td>
                <a href="{{ route('department.faculty.timetable', $faculty->faculty->id) }}" class="btn btn-sm btn-modern" style="background: #5b4cdb; color: white;">
                  <i class="fas fa-calendar me-1"></i> Timetable
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
              <div class="col-6">
                <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Academic Batch</label>
                <select name="batch_id" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;">
                  @foreach ($batches as $batch)
                  <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Program Type</label>
                <select name="program_type" class="form-select" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" required>
                  <option value="">-- Select Program Type --</option>
                  @foreach ($mainStreams as $ms)
                  <option value="{{ $ms->title }}">{{ $ms->title }}</option>
                  @endforeach
                </select>
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
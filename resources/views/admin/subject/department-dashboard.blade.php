<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\StudentProgram;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectHasDeptAdmin;
use Illuminate\Support\Facades\Auth;

$batches = BatchMaster::latest()->get();
$semesters = Semester::get();
$course_master = SubjectCourseMaster::with('courseMaster')->where('subject_id', $data->id)->get();
$faculty = Faculty::where('IS_LEFT', 0)->get();

?>
@include('includes.header')

<!-- <style>
  body {
    background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
  }
</style> -->
<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #17472f 0%, #8931f6 100%); border-radius: 0.75rem;">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }}</span>
      </a>
      <div class="d-flex">
        @if(StaticController::fetchUserRole() == 'dept-admin-erp')
        <a href="{{ url('logout') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
          <i class="fa fa-sign-out-alt me-1"></i> Logout
        </a>
        @else
        <a href="{{route('admin.dashboard')}}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
          <i class="fa fa-sign-out-alt me-1"></i>Admin Console
        </a>
        @endif

      </div>
    </div>
  </nav>
  <div class="alert alert-info shadow-sm rounded-3 mb-4 fw-semibold fs-5" style="background:#5b86e5; color:#fff; border:0;">
    <i class="fa fa-smile-beam me-2"></i>
    Welcome to the Department Dashboard! Here you can view and manage subjects, students, timetable, faculty and semesters with ease.
  </div>

  <div class="row g-4">
    <!-- User Info Card (Single Row) -->
    <div class="col-4">
      <div class="card shadow-lg border-0 mb-4" style="background: linear-gradient(135deg, #cc5be5 0%, #5b86e5 100%); color: #fff;">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
          <div class="d-flex align-items-center">
            <i class="fa fa-user-circle fa-3x me-3"></i>
            <div>
              <p class="display-6 fw-bold"> Welcome</p>
              <span class="fw-bold">{{ Auth::user()->name ?? '-' }}</span>
              <span class="ms-3">{{ Auth::user()->email ?? '-' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-4">
      <div class="card shadow-lg border-0 mb-4" style="background: linear-gradient(135deg, #5b86e5 0%, #cc5be5 100%); color: #fff;">

        <div class="card-body d-flex align-items-center justify-content-left flex-wrap">
          <div class="d-flex align-items-center">
            <i class="fa fa-user-chart fa-3x me-3"></i>
            <div>
              <a href="{{ route('department.substitution', [$data->id]) }}" class="text-light" style="text-decoration: none;">
                <h5 class="card-title mb-1">Manage Substitution | </h5>

              </a>
              <a href="{{ route('department.substitution.history.page') }}" class="text-light" style="text-decoration: none;">
                <p class="display-6 fw-bold">View History</p>
              </a>

            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="col-4">
      <div class="card shadow-lg border-0 mb-4" style="background: linear-gradient(135deg, #cc5be5 0%, #5b86e5 100%); color: #fff;">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
          <div class="d-flex align-items-center">
            <i class="fa fa-certificate fa-3x me-3"></i>
            <div>
              <a href="{{route('department.admission.list')}}" class="text-light" style="text-decoration: none;">
                <h3 class="text-light fw-bold"> Admission - </h3>
              </a>
              <span class="fw-bold">Applications</span>

            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Course Master Card -->
    <div class="col-md-3">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #43cea2 0%, #0efab3 100%); color: #fff;">
        <a href="{{route('department.course.master',[$data->id,$data->slug])}}" class="text-decoration-none text-white">
          <div class="card-body">
            <h5 class="card-title"> Course Master</h5>
            <p class="display-6 fw-bold">{{ $data->courseMasterPivot->count() ?? 0 }}</p>
          </div>

        </a>
      </div>

    </div>
    <!-- Number of Students Card -->
    <div class="col-md-3">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title"> Students</h5>
          <p class="display-6 fw-bold">{{ $data->students_count ?? 0 }}</p>
        </div>
      </div>
    </div>
    <!-- Semesters Card -->
    <div class="col-md-3">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title">Faculty</h5>
          <p class="display-6 fw-bold">{{ count($deptfaculties) ?? 0 }}</p>
        </div>
      </div>
    </div>

    <!-- Semesters Card -->
    <div class="col-md-3">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #f48cce 0%, #6cf5f7 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title">Time Table</h5>
          <p class="display-6 fw-bold"><a href="{{ route('department.timetable', [$data->id]) }}" class="text-white text-decoration-none">View</a></p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-3">
      <button class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#programConnect">
        <i class="fa fa-plus-circle"></i> Combinations
      </button>

      <div class="modal fade" id="programConnect" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title text-dark" id="exampleModalLabel">Connect Programs for {{$data->title}} </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('add.programs.to.subject')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                <div class="row">
                  <div class="col-6">
                    <label for="" class="text-dark">Select Academic Batch</label>
                    <div class="input-group">

                      <select name="batch_id" class="form-select">
                        @foreach ($batches as $batch)
                        <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                        @endforeach
                      </select>

                    </div>
                  </div>
                  <div class="col-6">
                    <label for="" class="text-dark">Select Program Type</label>
                    <div class="input-group">

                      <select name="program_type" class="form-select" required>
                        <option value="">-- Select Program Type --</option>
                        <option value="UG">UG</option>
                        <option value="PG">PG</option>
                      </select>

                    </div>
                  </div>
                </div>

                <label for="" class="text-dark">Select Program</label>

                <select name="programs[]" class="form-select mb-3 select-multiple" multiple>
                  @foreach ($programs as $prg)
                  <option value="{{$prg->id}}">{{$prg->code}} - {{$prg->name}}</option>
                  @endforeach
                </select>


                <input type="hidden" name="subject_id" value="{{$data->id}}">

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>



  <div class="row">
    @if(count($combinations))
    <div class="col-12">
      <div class="card shadow-lg border-0">
        <div class="card-header bg-white border-0 pb-0">
          <form method="GET" action="" class="d-flex align-items-center">
            <label for="batchFilter" class="me-2 fw-semibold">Filter by Batch:</label>
            <select name="batch" id="batchFilter" class="form-select w-auto me-2" onchange="this.form.submit()">
              <option value="">All Batches</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                {{ $batch->batch_name }}
              </option>
              @endforeach
            </select>
            <noscript><button type="submit" class="btn btn-primary btn-sm">Filter</button></noscript>
          </form>
        </div>
        <div class="card-body">
          <h5 class="card-title mb-4">Offered Program Combinations</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <tr>
                  <th>#</th>
                  <th>Batch</th>
                  <th>Program</th>
                  <th>Program Type</th>
                  <th>Details</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($combinations as $combination)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{$combination->batchmaster->batch_name ?? '-'}}</td>
                  <td>{{ $combination->studentprograminfo->name ?? '-' }}</td>
                  <td>{{$combination->program_type}}</td>
                  <td>
                    <!-- Add more details as needed -->
                    <span class="badge bg-success">ID: {{ $combination->studentprograminfo->id ?? '-' }}</span>
                  </td>
                  <td>
                    <form action="{{ route('department.combination.delete', $combination->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this combination?');" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
              </tbody>
              <tr>
                <td colspan="3" class="text-center">No combinations found.</td>
              </tr>
              @endforelse
            </table>
          </div>
        </div>


      </div>
    </div>
    @else
    <p class="text-center text-light">No combinations found.</p>
    @endif
  </div>


  <div class="row">
    <div class="col-lg-2">
      <button class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#addFaculty">
        <i class="fa fa-plus-circle"></i> Faculty
      </button>
      <div class="modal fade" id="addFaculty" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title text-dark" id="exampleModalLabel">Add Faculty for {{$data->title}} </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('dept.add.faculty.master')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">

                <label for="" class="text-dark">Add Faculty</label>

                <select name="faculty[]" class="form-select mb-3 select-multiple" multiple>
                  @foreach ($faculty as $fac)
                  <option value="{{$fac->id}}">{{$fac->USER_CODE}} - {{$fac->FIRST_NAME}} {{$fac->LAST_NAME}}</option>
                  @endforeach
                </select>
                <input type="hidden" name="subject_id" value="{{$data->id}}">

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
    <div class="row">
      @if(count($deptfaculties))
      <div class="col-12">
        <div class="card shadow-lg border-0">
          <div class="card-header bg-white border-0 pb-0">
            <h5 class="card-title">Departmental Faculties</h5>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Faculty Code</th>
                  <th>Faculty</th>
                  <th>Joining Date</th>
                  <th>Mobile</th>
                  <th>Mail</th>
                  <th>Timetable</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($deptfaculties as $faculty)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $faculty->faculty->USER_CODE ?? '-' }}</td>
                  <td>{{ $faculty->faculty->FIRST_NAME ?? '-' }} {{ $faculty->faculty->LAST_NAME ?? '-' }}</td>
                  <td>{{ $faculty->faculty->DOJ ?? '-' }}</td>
                  <td>{{$faculty->faculty->MOBILE_NO ?? '-'}}</td>
                  <td>{{$faculty->faculty->MAIL_ID ?? '-'}}</td>
                  <td>
                    <a href="{{ route('department.faculty.timetable', $faculty->faculty->id) }}" class="btn btn-primary btn-sm">
                      <i class="fa fa-calendar"></i> View Timetable
                    </a>
                  </td>
                  <td>
                    <form action="{{ route('department.faculty.delete', $faculty->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this faculty?');" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

      </div>
      @else
      <p class="text-center text-light">No faculties assigned yet.</p>
      @endif
    </div>
  </div>

  @include('includes.footer')
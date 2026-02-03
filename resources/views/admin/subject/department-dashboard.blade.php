<?php

use App\Models\BatchMaster;
use App\Models\Semester;
use App\Models\StudentProgram;

$batches = BatchMaster::latest()->get();
$semesters = Semester::get();

?>
@include('includes.header')

<style>
  body {
    background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
  }
</style>
<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }}</span>
      </a>
      <div class="d-flex">
        <a href="{{ url('logout') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
          <i class="fa fa-sign-out-alt me-1"></i> Logout
        </a>
      </div>
    </div>
  </nav>
  <div class="alert alert-info shadow-sm rounded-3 mb-4 fw-semibold fs-5" style="background:rgba(255,255,255,0.15); color:#fff; border:0;">
    <i class="fa fa-smile-beam me-2"></i>
    Welcome to the Department Dashboard! Here you can view and manage subjects, students, timetable, faculty and semesters with ease.
  </div>

  <div class="row g-4">
    <!-- User Info Card (Single Row) -->
    <div class="col-12">
      <div class="card shadow-lg border-0 mb-4" style="background: linear-gradient(135deg, #36d1c4 0%, #5b86e5 100%); color: #fff;">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
          <div class="d-flex align-items-center">
            <i class="fa fa-user-circle fa-3x me-3"></i>
            <div>
              <h5 class="card-title mb-1">Welcome</h5>
              <span class="fw-bold">{{ Auth::user()->name ?? '-' }}</span>
              <span class="ms-3">{{ Auth::user()->email ?? '-' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Course Master Card -->
    <div class="col-md-4">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #43cea2 0%, #0efab3 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title"> Subjects</h5>
          <p class="display-6 fw-bold">{{ $data->students_count ?? 0 }}</p>
        </div>
      </div>
    </div>
    <!-- Number of Students Card -->
    <div class="col-md-4">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title"> Students</h5>
          <p class="display-6 fw-bold">{{ $data->students_count ?? 0 }}</p>
        </div>
      </div>
    </div>
    <!-- Semesters Card -->
    <div class="col-md-4">
      <div class="card shadow-lg border-0" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: #fff;">
        <div class="card-body">
          <h5 class="card-title">Faculty</h5>
          <p class="display-6 fw-bold">{{ $data->faculty_count ?? 0 }}</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-2">
      <button class="btn btn-light mb-3" data-bs-toggle="modal" data-bs-target="#programConnect">
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
                <label for="" class="text-dark">Select Academic Batch</label>
                <div class="input-group">

                  <select name="batch_id" class="form-select">
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>

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

  @if(count($combinations))
  <div class="row">
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
  </div>
  @endif

  @include('includes.footer')
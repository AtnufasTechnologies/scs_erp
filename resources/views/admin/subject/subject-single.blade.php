<?php

use App\Models\BatchMaster;
use App\Models\Semester;
use App\Models\StudentProgram;

$batches = BatchMaster::get();
$semesters = Semester::get();
$programs = StudentProgram::latest()->get();
?>
@include('includes.header')
@include('admin.sidebar')

<div class="p-5 mb-4 profile-header-sub text-white rounded-3 shadow">
  <div class="container-fluid py-3">
    <h1 class="display-5 fw-bold text-light text-capitalize"><span class="fw-semibold"> {{ $data->program_master->title }} -</span> {{ $data->title }} ({{ $data->code }}) </h1>
    Academic Batch: <span class="fw-semibold text-warning">{{ $batchmaster->batch_name }}</span>

    <div class="row mb-3">

      <div class="col-lg-2">
        <form action="{{url('erp/admin/master/view-subject')}}" method="get">
          <input type="hidden" name="id" value="{{$data->id}}">
          <input type="hidden" name="slug" value="{{$data->slug}}">
          <div class="input-group">
            <select name="batch" class="form-select">
              @foreach ($batches as $batch)
              <option value="{{$batch->id}}" {{ $batchmaster->id == $batch->id ? 'selected' : ''}}>{{$batch->batch_name}}</option>
              @endforeach
            </select>
            <button type="submit" class="btn btn-outline-light"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>




    </div>

    <div class="row">

      <div class="col-lg-2">
        <!-- Button trigger modal -->
        <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
          <span class="button-decor"></span>
          <div class="button-content">
            <div class="button__icon">
              <i class="fa fa-plus-circle"></i>
            </div>
            <span class="button__text"> New Semester</span>
          </div>
        </button>
        <!-- Modal -->
        <div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title text-dark" id="exampleModalLabel">Add Semester </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{route('add.semester.to.subject')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                  <label for="" class="text-dark">Select Batch</label>
                  <select name="batch" class="form-select mb-3">
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}" {{ $batchmaster->id == $batch->id ? 'selected' : ''}}>{{$batch->batch_name}}</option>
                    @endforeach
                  </select>

                  <label for="" class="text-dark">Semester</label>
                  <select name="semester" class="form-select">
                    <option value="">--Select--</option>
                    @foreach ($semesters as $sem)
                    <option value="{{$sem->id}}">{{$sem->title}}</option>
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


      <div class="col-lg-2">
        <button class="cst-button mb-3" style="--clr: rgb(174, 217, 33);" data-bs-toggle="modal" data-bs-target="#programConnect">
          <span class="button-decor"></span>
          <div class="button-content">
            <div class="button__icon">
              <i class="fa fa-link"></i>
            </div>
            <span class="button__text"> Connect Programs</span>
          </div>
        </button>
        <div class="modal fade" id="programConnect" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title text-dark" id="exampleModalLabel">Connect Programs for {{$data->title}} </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{route('add.programs.to.subject')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                  <input type="hidden" name="batch_id" value=" {{$batchmaster->id}}">
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


  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="container my-5">
      <h2 class="h3 text-dark border-bottom pb-2 mb-4">Course Syllabus</h2>

      <div class="accordion modern-accordion" id="syllabusAccordion">
        @foreach ($data->semesters as $semester)
        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button fw-semibold bg-light text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $semester->id }}" aria-expanded="false" aria-controls="collapse{{ $semester->id }}" style="transition: background 0.2s;">
              <i class="fa fa-book-open me-2 text-primary"></i> {{ $semester->semestermaster->title }}
            </button>
          </h2>
          <div id="collapse{{ $semester->id }}" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#syllabusAccordion">
            <div class="accordion-body bg-white">
              <!-- Syllabus List -->
              @if($semester->syllabus && count($semester->syllabus))
              <ul class="list-group mb-3">
                @foreach($semester->syllabus as $syllabus)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>{{ $syllabus->topic }}</span>
                  <span class="text-muted">{{ $syllabus->description }}</span>
                </li>
                @endforeach
              </ul>
              @else
              <p class="text-muted">No syllabus added for this semester.</p>
              @endif

              <!-- Button trigger syllabus modal -->
              <button type="button" class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#addSyllabusModal{{ $semester->id }}">
                <i class="fa fa-plus"></i> Add Syllabus
              </button>

              <!-- Add Syllabus Modal -->
              <div class="modal fade" id="addSyllabusModal{{ $semester->id }}" tabindex="-1" aria-labelledby="addSyllabusLabel{{ $semester->id }}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form action="{{ route('add.syllabus.to.semester') }}" method="post">
                      @csrf
                      <div class="modal-header">
                        <h5 class="modal-title" id="addSyllabusLabel{{ $semester->id }}">Add Syllabus for {{ $semester->semestermaster->title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                        <input type="hidden" name="subject_id" value="{{ $data->id }}">
                        <div class="mb-3">
                          <label for="topic{{ $semester->id }}" class="form-label">Topic</label>
                          <input type="text" class="form-control" id="topic{{ $semester->id }}" name="topic" required>
                        </div>
                        <div class="mb-3">
                          <label for="description{{ $semester->id }}" class="form-label">Description</label>
                          <textarea class="form-control" id="description{{ $semester->id }}" name="description" rows="3" required></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
        @endforeach


      </div>
      <style>

      </style>

    </div>
  </div>


  <div class="col-lg-4">
    <div class="container my-5">
      <h2 class="h3 text-dark border-bottom pb-2 mb-4">Connected Programs - {{ $data->programs->count() }}</h2>
      <div class="card">
        <div class="card-body  global-scroll-card">
          @foreach ($data->programs as $program)
          <div class="radius-10  alert alert-info d-flex justify-content-between align-items-center shadow" role="alert">
            <div>
              <strong>{{ $program->student_program->code }} - {{ $program->student_program->name }}</strong>
            </div>
            <form action="" method="post" onsubmit="return confirm('Are you sure you want to remove this program from the subject?');">
              @csrf
              <input type="hidden" name="subject_id" value="{{ $data->id }}">
              <input type="hidden" name="program_id" value="{{ $program->student_program->id }}">
              <button type="submit" class="btn btn-sm btn-danger">
                <i class="fa fa-trash"></i>
              </button>
            </form>
          </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
  <div class="col-lg-12">
    <div class="container my-5">
      <h2 class="h3 text-dark border-bottom pb-2 mb-4">Faculty</h2>
    </div>
  </div>
</div>



@include('includes.footer')
<?php

use App\Models\StudentProgram;
use App\Models\StudentProgramTypeMaster;

$programs = StudentProgram::with('campusmaster')->get();
$programTypeMasters = StudentProgramTypeMaster::all();

?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Student Program Type Master</span></h3>
<!-- Button trigger modal -->
<button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <i class="fa fa-plus-circle"></i> Add New
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Multi Add Student Program Types</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('itcell.student-program-type.multi.update')}}" method="post">
        @csrf
        <div class="modal-body">

          <label for="">Select Program Type</label>
          <select name="program_type" class="form-control mb-3">
            <option value="">Select Program Type</option>
            @foreach ($programTypeMasters as $type)
            <option value="{{$type->id}}">{{$type->name}}</option>
            @endforeach
          </select>

          <label for="">Select Programs</label>
          <select name="programs[]" class="form-control select-multiple" multiple>
            @foreach ($programs as $prg)
            <option value="{{$prg->id}}">{{$prg->code}} - {{$prg->name}} | {{$prg->campusmaster->name ?? '-'}}</option>
            @endforeach
          </select>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container-fluid ">
  <div class="row">
    @foreach ($data as $item)
    <div class="col-lg-6">
      <div class="card shadow">
        <div class="card-header">
          <h3>{{$item->name}}</h3>
          <p>{{count($item->stdprograms)}} Programs</p>
        </div>
        <div class="card-body">

          @foreach ($item->stdprograms as $prg )
          <div class="alert alert-success">
            <p><strong>Code - {{$prg->code}} </strong> <br>
              {{$prg->name}}
            </p>
          </div>

          @endforeach
        </div>
      </div>

    </div>
    @endforeach
  </div>

  @include('includes.footer')
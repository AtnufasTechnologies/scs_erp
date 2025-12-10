<?php

use App\Models\StudentProgram;

$allStdPrograms = StudentProgram::get();
?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Program Group</span></h3>

<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Program Group</th>
        <th>Shift</th>
        <th>Program Info</th>
        <th>No.of.Semesters</th>
        <th>Edit</th>

      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->campus->name}}</td>
        <td><span class="text-uppercase"> {{$item->program_code}}</span></td>
        <td><span class="text-uppercase">{{$item->shift}} </span></td>
        <td>{{$item->programInfo->name}} - {{$item->programInfo->description}} </td>
        <td>{{$item->programInfo->semester_count}}</td>
        <td>
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Editing - {{$item->campus->name}} </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/master/update-program-group')}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <div class="row">
                      <input type="hidden" name="id" value="{{$item->id}}">

                      <div class="col-lg-12">
                        <label for="">Change Program Dept</label>
                        <select name="program_code" class="dselect-example mb-3">
                          <option value="">--Select--</option>
                          @foreach ($allStdPrograms as $p)
                          <option value="{{$p->id}}">{{$p->name}}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-lg-4">
                        <label for="">Program Code</label>
                        <input type="text" class="form-control mb-3" value="{{$item->program_code}}" readonly>
                      </div>
                      <div class="col-lg-4">
                        <label for="">No of Semesters</label>
                        <input type="number" name="semester_count" class="form-control mb-3" value="{{$item->programInfo->NO_OF_SEMESTER}}">
                      </div>

                      <div class="col-lg-4">
                        <label for="">Shift</label>
                        <input type="number" name="shift" class="form-control mb-3" value="{{$item->shift}}">
                      </div>

                      <div class="col-lg-12">
                        <label for="">Program Info Name</label>
                        <input type="text" name="pginfo_name" class="form-control mb-3" value="{{$item->programInfo->name}}">
                      </div>

                      <div class="col-lg-12">
                        <label for="">Program Info Description</label>
                        <input type="text" name="pginfo_desc" class="form-control mb-3" value="{{$item->programInfo->description}}">
                      </div>
                    </div>


                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>


      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>

@include('includes.footer')
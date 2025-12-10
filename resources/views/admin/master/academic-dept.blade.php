<?php

use App\Models\BatchMaster;
use App\Models\DepartmentMaster;
use App\Models\MainProgram;

$programs = MainProgram::with('campus')->get();
$batches = BatchMaster::get();
$alldepartments = DepartmentMaster::get();
?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Academic Departments </span></h3>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>



<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/academic-dept')}}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row">

            <div class="col-lg-6">
              <label for="">Select Batch *</label>
              <select name="batch" class="form-control mb-3">
                <option value=""> -- Select --</option>
                @foreach ($batches as $b)
                <option value="{{$b->id}}"> {{$b->batch_name }} </option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label for="">Select Program *</label>
              <select name="program_id" class="form-control mb-3">
                @foreach ($programs as $p)
                <option value="{{$p->id}}"> {{$p->campus->name}} - {{$p->name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6"><label for="">Code *</label>
              <input type="text" name="short_name" class="form-control mb-3">
            </div>
            <div class="col-lg-12">
              <label for="">Dept Name and Timing *</label>
              <input type="text" name="full_name" class="form-control mb-3">
            </div>
          </div>


        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3 table-hover" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Program</th>
        <th>Code</th>
        <th>Name</th>
        <th>Connect Dept</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td> {{$item->campus->name}}</td>
        <td>{{$item->program->name}}</td>
        <td><span class="text-uppercase">{{$item->deptmaster != null ? $item->deptmaster->department_code :''}}</span> </td>
        <td><span class="text-capitalize"> {{$item->name}}</span></td>

        <td>

          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#connect{{$item->id}}">
            <i class="fa fa-link"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="connect{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Connecting Academic Dept</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/master/connect-academic-dept')}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <label for="">Select a Department *</label>
                    <select name="dept" data-dselect-search="true" data-dselect-creatable="true" data-dselect-clearable="true" data-dselect-max-height="300px" data-dselect-size="sm" class="form-select dselect-example">

                      @foreach ($alldepartments as $d)
                      <option value="{{$d->id}}">{{$d->department_code}} - {{$d->name}}</option>
                      @endforeach
                    </select>

                    <input type="hidden" name="id" value="{{$item->id}}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Link Now</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
        <td><a href="{{url('erp/admin/master/del-dept/'.$item->id)}}" id="citadel"><button class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i></button></a></td>
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')
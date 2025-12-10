<?php

use App\Models\ProgramMaster;

$programs = ProgramMaster::latest()->get();

?>
@include('includes.header')
@include('admin.sidebar')
<h3><span class="text-uppercase">Subject Master</span></h3>
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>



<!-- Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Subject </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/subject')}}" method="post">
        @csrf
        <div class="modal-body">

          <select name="program_id" class="form-control">
            @foreach ($programs as $item)
            <option value="{{$item->id}}">{{$item->title}}</option>
            @endforeach
          </select>
          <label for="">Subject Name</label>
          <input type="text" class="form-control mb-3" name="title" placeholder="Type here...">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="container-fluid card shdaow">

  <table class="table mt-3 mb-3 table-hover" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Program </th>
        <th>Subject Name</th>
        <th>View</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->program_master->title}}</td>
        <td><span class="text-capitalize">{{$item->title}}</span></td>
        <td>
          <form action="{{url('erp/admin/master/view-subject')}}" method="get">
            <input type="hidden" name="id" value="{{$item->id}}">
            <input type="hidden" name="slug" value="{{$item->slug}}">

            <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
          </form>

        </td>

        <td>
          <a href="" id="citadel"><button class="btn btn-outline-danger"><i class="fa fa-trash-alt"></i></button></a>
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
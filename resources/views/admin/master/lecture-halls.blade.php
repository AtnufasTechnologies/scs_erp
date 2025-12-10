<?php

use App\Models\AcademicBlock;
use App\Models\RoomMaster;

$acblocks = AcademicBlock::latest()->get();
$roomtypes = RoomMaster::latest()->get();
?>

@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Lecture Halls</span></h3>
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
      <form action="{{url('erp/admin/master/add-lecture-hall')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="">Select Block *</label>
          <select name="acblock_id" class="form-control mb-3">
            @foreach ($acblocks as $acb)
            <option value="{{$acb->id}}">{{$acb->title}}</option>
            @endforeach
          </select>

          <label for="">Select Room Type *</label>
          <select name="roomtype_id" class="form-control mb-3">
            @foreach ($roomtypes as $room)
            <option value="{{$room->id}}">{{$room->title}}</option>
            @endforeach
          </select>

          <label for="">Room Name</label>
          <input type="text" name="title" class="form-control mb-3" placeholder="Ground Floor, Hall 1">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Block</th>
        <th>Room Type</th>
        <th>Room</th>

      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->acblockmaster->title}}</td>
        <td>{{$item->roomtypemaster->title}}</td>
        <td> {{$item->title}}</td>
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>
@include('includes.footer')
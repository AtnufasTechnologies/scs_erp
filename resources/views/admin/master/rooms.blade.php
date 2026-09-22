<?php

use App\Models\AcademicBlock;

$blocks = AcademicBlock::orderBy('title')->get();
?>

@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Room Management</span></h3>

<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addRoomModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New Room</span>
  </div>
</button>

<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addRoomModalLabel">Add Examination Room</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/rooms')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="block_id">Building *</label>
          <select class="form-control mb-3" name="block_id" required>
            <option value="">Select Building</option>
            @foreach ($blocks as $block)
            <option value="{{$block->id}}">{{$block->title}}</option>
            @endforeach
          </select>

          <label for="room_number">Room Number *</label>
          <input type="text" class="form-control mb-3" name="room_number" placeholder="A-101" required>

          <div class="row">
            <div class="col-md-6">
              <label for="rows">Rows *</label>
              <input type="number" class="form-control mb-3" name="rows" min="1" placeholder="10" required>
            </div>
            <div class="col-md-6">
              <label for="columns">Columns *</label>
              <input type="number" class="form-control mb-3" name="columns" min="1" placeholder="8" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label for="capacity">Capacity</label>
              <input type="number" class="form-control mb-3" name="capacity" min="1" placeholder="80">
              <small class="text-muted">Leave empty to auto-calculate from rows x columns.</small>
            </div>
            <div class="col-md-6">
              <label for="priority">Priority *</label>
              <input type="number" class="form-control mb-3" name="priority" min="1" placeholder="1" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label for="room_type">Room Type</label>
              <input type="text" class="form-control mb-3" name="room_type" placeholder="Exam Hall">
            </div>
            <div class="col-md-6">
              <label for="floor">Floor</label>
              <input type="text" class="form-control mb-3" name="floor" placeholder="Ground Floor">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label for="room_code">Room Code</label>
              <input type="text" class="form-control mb-3" name="room_code" placeholder="BLK-A101">
            </div>
            <div class="col-md-6">
              <label for="status">Status</label>
              <select class="form-control mb-3" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
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
  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Building</th>
        <th>Room Number</th>
        <th>Layout (R x C)</th>
        <th>Capacity</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{$item->block->title ?? 'N/A'}}</td>
        <td>{{$item->room_number ?? $item->title}}</td>
        <td>{{(int) ($item->rows ?? 0)}} x {{(int) ($item->columns ?? 0)}}</td>
        <td>{{(int) ($item->capacity ?? 0)}}</td>
        <td>{{(int) ($item->priority ?? 0)}}</td>
        <td>
          @if(($item->status ?? 'active') === 'active')
          <span class="badge bg-success">Active</span>
          @else
          <span class="badge bg-secondary">Inactive</span>
          @endif
        </td>
        <td>
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="editRoom{{$item->id}}Label" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editRoom{{$item->id}}Label">Edit Room</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/master/update-room')}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <input type="hidden" name="id" value="{{$item->id}}">

                    <label for="block_id">Building *</label>
                    <select class="form-control mb-3" name="block_id" required>
                      <option value="">Select Building</option>
                      @foreach ($blocks as $block)
                      <option value="{{$block->id}}" {{$item->block_id == $block->id ? 'selected' : ''}}>{{$block->title}}</option>
                      @endforeach
                    </select>

                    <label for="room_number">Room Number *</label>
                    <input type="text" class="form-control mb-3" name="room_number" value="{{$item->room_number ?? $item->title}}" required>

                    <div class="row">
                      <div class="col-md-6">
                        <label for="rows">Rows *</label>
                        <input type="number" class="form-control mb-3" name="rows" min="1" value="{{$item->rows ?? 1}}" required>
                      </div>
                      <div class="col-md-6">
                        <label for="columns">Columns *</label>
                        <input type="number" class="form-control mb-3" name="columns" min="1" value="{{$item->columns ?? 1}}" required>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control mb-3" name="capacity" min="1" value="{{$item->capacity}}">
                      </div>
                      <div class="col-md-6">
                        <label for="priority">Priority *</label>
                        <input type="number" class="form-control mb-3" name="priority" min="1" value="{{$item->priority ?? 1}}" required>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <label for="room_type">Room Type</label>
                        <input type="text" class="form-control mb-3" name="room_type" value="{{$item->room_type}}" placeholder="Exam Hall">
                      </div>
                      <div class="col-md-6">
                        <label for="floor">Floor</label>
                        <input type="text" class="form-control mb-3" name="floor" value="{{$item->floor}}" placeholder="Ground Floor">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <label for="room_code">Room Code</label>
                        <input type="text" class="form-control mb-3" name="room_code" value="{{$item->room_code}}" placeholder="BLK-A101">
                      </div>
                      <div class="col-md-6">
                        <label for="status">Status</label>
                        <select class="form-control mb-3" name="status">
                          <option value="active" {{($item->status ?? 'active') === 'active' ? 'selected' : ''}}>Active</option>
                          <option value="inactive" {{($item->status ?? 'active') === 'inactive' ? 'selected' : ''}}>Inactive</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan="8" class="text-center">No Records</td>
      </tr>
      @endif
    </tbody>
  </table>
</div>

@include('includes.footer')
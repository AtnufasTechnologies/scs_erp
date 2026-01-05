<?php

use App\Models\Campus;
use App\Models\PermissionMaster;


$permissionMaster = PermissionMaster::all();
$campusMaster = Campus::all()
?>

@include('includes.header')
@include('admin.sidebar')
<h3>User Access Management</h3>


<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New User Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/user-access/newuser')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <label for="">Full Name *</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Login Email *</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="Type Here...">


          <label for="">Login Password * (min 8 characters)</label>
          <input type="password" name="password" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Authorized For Campus </label>
          <select name="campus" class="form-control mb-3">
            <option value="">Select Campus</option>
            @foreach ($campusMaster as $cm)
            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
            @endforeach
          </select>

          <label for="">Roles *</label>
          <select name="roles[]" class="form-control mb-3 select-multiple" multiple>
            @foreach ($permissionMaster as $pm)
            <option value="{{ $pm->permission_name }}">{{ $pm->permission_name }}</option>
            @endforeach
          </select>

        </div>


        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>


@if (count($data))
<div class="row">
  @foreach ($data as $itm)
  <div class="col-lg-3 mb-4">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">{{ $itm->name }}</h5>
        <p class="card-text"><strong>Email:</strong> {{ $itm->email }}</p>
        <p class="card-text"><strong>Role:</strong>
        <ul>

          @foreach ($itm->roles as $role)
          <li>
            <span> <a href="{{url('erp/admin/user-access/remove-user-permission/'.$role->id  )}}" id="citadel">
                <i class="fa fa-trash text-danger"></i></a></span>
            {{ $role->permissionmaster->permission_name  }}
            <span class="mx-1"><i class="fa fa-check-circle text-success"></i></span>
          </li>
          @endforeach
        </ul>

        </p>
      </div>
      <hr>
      <div class="card-body">
        <button class="btn btn-edit mb-3" data-bs-toggle="modal" data-bs-target="#edit{{$itm->id}}">

          Edit Permission
      </div>
      </button>

      <div class="modal fade" id="edit{{$itm->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Edit User Info</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{url('erp/admin/user-access/update-permission')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">


                <label for="">Roles *</label>
                <select name="roles[]" class="form-control mb-3 select-multiple" multiple>
                  @foreach ($permissionMaster as $pm)
                  <option value="{{ $pm->permission_name }}">{{ $pm->permission_name }}</option>
                  @endforeach
                </select>

                <input type="hidden" name="user_id" value="{{$itm->id}}">

              </div>


              <div class="modal-footer">
                <button type="submit" class="btn btn-edit">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  @endforeach
</div>
@else
<p class="display-4 text-center">No users found.</p>
@endif


@include('includes.footer')
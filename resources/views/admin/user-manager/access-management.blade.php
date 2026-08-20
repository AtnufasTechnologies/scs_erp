<?php

use App\Models\Campus;
use App\Models\MenuMaster;
use App\Models\PermissionMaster;
use App\Models\RoleMaster;
use App\Models\UserType;

$permissionMaster = MenuMaster::all();
$userTypes = RoleMaster::all();
$campusMaster = Campus::all()
?>

@include('includes.header')
@include('admin.sidebar')
<h3>User Access Management </h3>
<div class="row">
  <div class="col-lg-4">
    <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#add">
      <span class="button-decor"></span>
      <div class="button-content">
        <div class="button__icon">
          <i class="fa fa-plus-circle"></i>
        </div>
        <span class="button__text">Add New</span>
      </div>
    </button>
  </div>

</div>


<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New User Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('add.newuser')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <label for="">Full Name *</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Login Email *</label>
          <input type="email" name="email" class="form-control mb-3" placeholder="Type Here...">


          <label for="">Login Password * (min 6 characters)</label>
          <input type="text" name="password" class="form-control mb-3" placeholder="Type Here...">

          <label for="">User Type </label>
          <select name="user_type" class=" mb-3 dselect-example">
            <option value="">Select User Type</option>
            @foreach ($userTypes as $ut)
            <option value="{{ $ut->slug }}">{{ $ut->role_name }}</option>
            @endforeach
          </select>

          <label for="">Authorized For Campus </label>
          <select name="campus" class="form-control mb-3">
            <option value="">Select Campus</option>
            @foreach ($campusMaster as $cm)
            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
            @endforeach
          </select>

          <!-- <label for="">Access Permission *</label>
          <select name="roles[]" class="form-control mb-3 select-multiple" multiple>
            @foreach ($permissionMaster as $pm)
            <option value="{{ $pm->id }}">{{ $pm->menu_name }} - {{ $pm->module_type }}</option>
            @endforeach
          </select> -->

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
  <table class="table table-bordered" id="myTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>User Type</th>
        <th>Campus</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data as $itm)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{ $itm->name }}</td>
        <td>{{ $itm->email }}</td>
        <td><span class="badge badge-warning">{{$itm->decrypted_password }}</span></td>
        <td>{{ $itm->userroletype != null ? $itm->userroletype->role_name : 'N/A' }}</td>
        <td>
          @if ($itm->userroletype != null)
          @if($itm->userroletype->role_name == 'super-admin' || $itm->userroletype->role_name == 'principal')
          All Access
          @else
          @if ($itm->campuspermission && $itm->campuspermission->campus)
          {{ $itm->campuspermission->campus->name }}
          @else
          No Campus
          @endif
          @endif
          @endif
        </td>
        <td>
          <button class="btn btn-primary">Edit</button>
          <button class="btn btn-danger">Delete</button>
        </td>
      </tr>

      @endforeach

</div>
@else
<p class="display-4 text-center">No users found.</p>
@endif


@include('includes.footer')
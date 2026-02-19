<?php

use App\Models\Campus;
use App\Models\MenuMaster;
use App\Models\PermissionMaster;
use App\Models\UserType;

$permissionMaster = MenuMaster::all();
$userTypes = UserType::all();
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
  <div class="col-lg-4 offset-lg-4">
    <div class="input-group mb-3">
      <input type="text" id="userSearch" class="form-control" placeholder="Search by name or email...">
    </div>
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

          <label for="">User Type <small>(For SuperAdmin and Principal all permissions are granted)</small></label>
          <select name="user_type" class=" mb-3 dselect-example">
            <option value="">Select User Type</option>
            @foreach ($userTypes as $ut)
            <option value="{{ $ut->slug }}">{{ $ut->name }}</option>
            @endforeach
          </select>

          <label for="">Authorized For Campus </label>
          <select name="campus" class="form-control mb-3">
            <option value="">Select Campus</option>
            @foreach ($campusMaster as $cm)
            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
            @endforeach
          </select>

          <label for="">Access Permission *</label>
          <select name="roles[]" class="form-control mb-3 select-multiple" multiple>
            @foreach ($permissionMaster as $pm)
            <option value="{{ $pm->id }}">{{ $pm->menu_name }} - {{ $pm->module_type }}</option>
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



<script>
  document.getElementById('userSearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const cards = document.querySelectorAll('.fixed-card');

    cards.forEach(card => {
      const name = card.querySelector('.card-title').textContent.toLowerCase();
      const email = card.querySelector('.fa-envelope').parentElement.textContent.toLowerCase();

      if (name.includes(searchTerm) || email.includes(searchTerm)) {
        card.parentElement.style.display = '';
      } else {
        card.parentElement.style.display = 'none';
      }
    });
  });
</script>
@if (count($data))
<div class="row">
  @foreach ($data as $itm)
  <div class="col-lg-4 mb-4">
    <div class="card mb-4 fixed-card" style="background: linear-gradient(135deg, #1c2242e3 0%, #263851 100%); border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
      <div class="card-body scrollable-card" style="color: white;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
          <h5 class="card-title text-capitalize" style="margin: 0; font-weight: 600; font-size: 1.3rem;">{{ $itm->name }}</h5>
          <a href="{{ route('impersonate.user', ['id' => $itm->id]) }}" target="_blank" style="color: #fff; font-size: 1.2rem; transition: 0.2s;">
            <i class="fa fa-lock-open"></i></a>
        </div>

        <p class="card-text" style="margin-bottom: 12px;"><strong><i class="fa fa-envelope text-warning"></i></strong> {{ $itm->email }}</p>

        <p class="card-text" style="margin-bottom: 12px;">
          User Type <span class="badge" style="background: rgba(225, 219, 108, 0.43); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;"><strong>{{$itm->userroletype != null ? $itm->userroletype->role_name : 'N/A'}}</strong></span>
        </p>

        <p class="card-text" style="margin-bottom: 12px;"><strong>Access</strong>
          @if ($itm->userroletype != null)
          @if($itm->userroletype->role_name == 'super-admin' || $itm->userroletype->role_name == 'principal')
          <span class="badge" style="background: rgba(76, 255, 100, 0.3); color: #4cff64; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">All Access <i class="fa fa-check-circle"></i></span>
          @else
          @if ($itm->campuspermission && $itm->campuspermission->campus)
          <span class="badge" style="background: rgba(255,255,255,0.25); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">{{ $itm->campuspermission->campus->name }}</span>
          @else
          <span class="badge" style="background: rgba(255, 76, 76, 0.3); color: #ff4c4c; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">No Campus</span>
          @endif
          @endif
          @endif
        </p>

        <p class="card-text"><strong>Permissions:</strong>
          @if ($itm->menupermission != null)
          @foreach ($itm->menupermission as $role)
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 8px 0; background: rgba(255, 255, 255, 0.14); padding: 8px; border-radius: 6px;">
          <span class="badge" style="background: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">{{ $role->permission_name }}</span>
          <a href="{{route('admin.user-access.delete-permission', $role->id)}}" style="color: #fb4848;"><i class="fa fa-trash"></i></a>
        </div>
        @endforeach
        @endif
        </p>
      </div>

      <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="row g-2">
          <div class="col-lg-6">
            <button class="btn w-100" style="background: linear-gradient(135deg, #b1b5f0 0%, #a5aaf0 100%); border: none; color: white; border-radius: 8px; padding: 10px; font-weight: 500; box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3); transition: 0.3s;" data-bs-toggle="modal" data-bs-target="#edit{{$itm->id}}">
              <i class="fa fa-edit"></i>
            </button>
          </div>
          <div class="col-lg-6">
            <a href="{{route('admin.user-access.delete', $itm->id)}}" class="w-100">
              <button class="btn w-100" style="background: rgba(255,76,76,0.9); border: none; color: white; border-radius: 8px; padding: 10px; font-weight: 500; box-shadow: 0 4px 15px rgba(255, 76, 76, 0.3); transition: 0.3s;">
                <i class="fa fa-trash"></i>
              </button>
            </a>
          </div>
        </div>
      </div>

      <div class="modal fade" id="edit{{$itm->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px 12px 0 0; color: white;">
              <h5 class="modal-title">Edit User Info</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{route('update.user.permission')}}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                <label for="" style="font-weight: 600; margin-bottom: 8px;">Roles *</label>
                <select name="roles[]" class="form-control mb-3 select-multiple" multiple style="border-radius: 8px; border: 2px solid #667eea;">
                  @foreach ($permissionMaster as $pm)
                  <option value="{{ $pm->id }}">{{ $pm->menu_name }} - {{ $pm->module_type }}</option>
                  @endforeach
                </select>
                <input type="hidden" name="user_id" value="{{$itm->id}}">
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 500;">Update</button>
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
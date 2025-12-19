<?php

use App\Models\BatchMaster;
use App\Models\CollegeBankAccount;
use App\Models\MainProgram;

$batches = BatchMaster::all();
$programs = MainProgram::with('campus')->get();
$banks = CollegeBankAccount::get();
?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Fee Heads</span></h3>
<!-- Button trigger modal -->
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
        <h5 class="modal-title" id="exampleModalLabel">Create New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/fee-heads')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <label for="">Head Title</label>
          <input type="text" name="head_name" class="form-control mb-3" placeholder="University Fee">
          <label for="">Required Bank Account for Distribution</label>
          <select name="bank" class="form-control">
            <option value="">--Select--</option>
            @foreach ($banks as $b)
            <option value="{{$b->id}}">{{$b->acc_name}} - {{$b->acc_no}}</option>
            @endforeach
          </select>

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

        <th>Head Name</th>
        <th>Connected Bank Account</th>
        <th>Edit</th>
        <th>Delete</th>

      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$sl++}}</td>
        <td> {{$item->head_name}}</td>
        <td>{{$item->bankmaster ? $item->bankmaster->acc_name   .' - '.  $item->bankmaster->acc_no: ''}}</td>
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
                  <h5 class="modal-title" id="exampleModalLabel">Edit </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/accounts/update-feehead')}}" method="post" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                    <label for="">Head Name *</label>
                    <input type="text" name="head_name" class="form-control mb-3" value="{{$item->head_name}}">
                    <label for="">Update Bank Account for Distribution</label>
                    <select name="bank" class="form-control">
                      <option value="">--Select--</option>
                      @foreach ($banks as $b)
                      <option value="{{$b->id}}">{{$b->acc_name}} - {{$b->acc_no}}</option>
                      @endforeach
                    </select>
                    <input type="hidden" name="id" value="{{$item->id}}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>

        <td><a href="{{url('erp/admin/accounts/del-feehead/'.$item->id)}}" id="citadel"><button class="btn btn-outline-danger"><i class="fa fa-trash-alt"></i></button></a></td>
      </tr>
      @endforeach

      @else
      <p class="display-4 text-center">No Records</p>
      @endif
    </tbody>

  </table>
</div>


@include('includes.footer')
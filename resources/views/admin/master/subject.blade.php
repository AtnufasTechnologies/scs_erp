<?php

use App\Models\Campus;
use App\Models\ProgramMaster;

$programs = ProgramMaster::latest()->get();
$campuses = Campus::latest()->get();
?>
@include('includes.header')
@include('admin.sidebar')
<h3><span class="text-uppercase">Academic Departments Master</span></h3>
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
          <div class="row">
            <div class="col-4">
              <select name="campus" class="form-control">
                <option value="">Select Campus *</option>
                @foreach ($campuses as $campus)
                <option value="{{$campus->id}}">{{$campus->name}}</option>
                @endforeach
                <option value="3">Sonada and Siliguri</option>
              </select>
            </div>
            <div class="col-4">
              <select name="program_id" class="form-control">
                <option value="">Degree Type *</option>
                @foreach ($programs as $item)
                <option value="{{$item->id}}">{{$item->title}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-4">
              <input type="text" class="form-control mb-3" name="code" placeholder="Subject Code *">
            </div>
            <div class="col-12">
              <label for=""> Title</label>
              <input type="text" class="form-control mb-3" name="title" placeholder="Type here...">

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

<div class="container-fluid card shdaow">

  <table class="table mt-3 mb-3 table-hover" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus </th>
        <th>Code</th>
        <th>Academic Department Name</th>
        <th>Main Program Type</th>
        <th>Edit</th>
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
        <td>{{$item->campusmaster->name}}</td>
        <td><span class="text-capitalize">{{$item->code}}</span></td>
        <td><span class="text-capitalize">{{$item->title}}</span></td>
        <td><span class="text-capitalize">{{$item->main_program_type}}</span></td>
        <td>
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="exampleModalLabel">Edit </h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.master.update.academic-dept', $item->id)}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-4">
                        <select name="campus" class="form-control">
                          <option value="">Select Campus *</option>
                          @foreach ($campuses as $campus)
                          <option value="{{$campus->id}}" {{$item->campus_id == $campus->id ? 'selected' : ''}}>{{$campus->name}}</option>
                          @endforeach

                        </select>
                      </div>
                      <div class="col-4">
                        <select name="program_id" class="form-control">
                          <option value="">Degree Type *</option>
                          @foreach ($programs as $prog)
                          <option value="{{$prog->title}}" {{$item->main_program_type == $prog->title ? 'selected' : ''}}>{{$prog->title}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-4">
                        <input type="text" class="form-control mb-3" name="code" placeholder="Subject Code *" value="{{$item->code}}">
                      </div>
                      <div class="col-12">
                        <label for=""> Title</label>
                        <input type="text" class="form-control mb-3" name="title" placeholder="Type here..." value="{{$item->title}}">

                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
        <td>
          <form action="{{url('erp/admin/master/view-subject')}}" method="get">
            <input type="hidden" name="id" value="{{$item->id}}">
            <input type="hidden" name="slug" value="{{$item->slug}}">
            <button class="btn btn-primary"><i class="fa fa-eye"></i></button>
          </form>

        </td>

        <td>
          <a href="{{url('erp/admin/master/delete-subject/'.$item->id) }}" id="citadel">
            <button class="btn btn-outline-danger"><i class="fa fa-trash"></i></button>
          </a>
        </td>

      </tr>
      @endforeach


      @endif
    </tbody>

  </table>
</div>

@include('includes.footer')
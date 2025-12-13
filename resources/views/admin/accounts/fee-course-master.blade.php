<?php

use App\Http\Controllers\StaticController;

$fetchPrograms = StaticController::fetchProgramGroupNew();

?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Fee Course Master</span></h3>

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
        <h5 class="modal-title" id="exampleModalLabel">Create New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/fee-course-master')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="">Course Title</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="Type here">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row mb-3">
  <form action="{{url('erp/admin/accounts/fee-course-master')}}" method="get">
    <div class="col-lg-6 offset-6">
      <div class="input-group">
        <select name="coursemaster" class="form-control dselect-example">
          <option value="">--Select--</option>
          @foreach ($allcourses as $item)
          <option value="{{$item->id}}">{{$item->name}}</option>
          @endforeach
        </select>
        <button type="submit" class="btn-sm btn-info"><i class="fa fa-search"></i></button>
      </div>

    </div>
  </form>
</div>

<div class="container-fluid card shadow">

  <table class="table mt-3 mb-3">
    <thead>
      <tr>
        <th>ID#</th>
        <th>Name</th>
        <th>Program Groups</th>
        <th>Connect Programs</th>
        <th>Edit</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))
      <?php $sl = 1 ?>
      @foreach ($data as $item)
      <tr>
        <td>{{$item->id}}</td>
        <td> {{$item->name}}</td>
        <td>
          <?php $courseGroup = StaticController::fetchCourseMasterGroups($item->id);
          ?>
          @if(count($courseGroup ))
          <a data-bs-toggle="modal" data-bs-target="#viewProgs{{$item->id}}" class="btn-sm btn-danger mx-1">
            {{count($courseGroup)}}
          </a>
          <div class="modal fade " id="viewProgs{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Linked Programs </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body editfeestructure">
                  <div class="row">
                    @if (count($courseGroup))
                    @foreach ($courseGroup as $s)
                    <div class="col-lg-6 mb-3">
                      <button type="button" class="btn-sm btn-outline-primary position-relative">
                        {{$s->programgroupinfo->programInfo->name}} - {{$s->programgroupinfo->campus->name}}
                        <a href="{{url('erp/admin/accounts/unlink/fee-structure-group/'.$s->id)}}" id="citadel">
                          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <i class="fa fa-times"></i>
                          </span>
                        </a>
                      </button>
                    </div>

                    @endforeach
                    @else
                    <p class="display-4 text-center"> No Associations </p>
                    @endif
                  </div>

                </div>


              </div>
            </div>
          </div>

          @endif
        </td>

        <td>
          <button class="btn-sm btn-success" data-bs-target="#linkAddModal{{$item->id}}" data-bs-toggle="modal" data-bs-dismiss="modal"><i class="fa fa-plus-circle"></i> Create Groups</button>

          <div class="modal fade" id="linkAddModal{{$item->id}}" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalToggleLabel2">Make Program Group</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('link.coursemaster.prggroup')}}" method="post">
                  @csrf
                  <div class="modal-body">

                    <label for="">Select Program Groups (atleast 1 required)</label>
                    <select name="progs[]" class="select-multiple" multiple>
                      @foreach ($fetchPrograms as $p)
                      <option value="{{$p->id}}">{{$p->programInfo->code}} - {{$p->programInfo->name}} | {{$p->campus->name}}</option>
                      @endforeach
                    </select>
                    <input type="hidden" name="coursemasterId" value="{{$item->id}}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary">Submit</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>

        <td>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{url('erp/admin/accounts/update-fee-course-master')}}" method="post" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                    <label for="">Course Name *</label>
                    <input type="text" name="name" class="form-control mb-3" value="{{$item->name}}">
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

        <td>
          <a href="{{url('erp/admin/accounts/del-feecourse-master/'.$item->id)}}" id="citadel">
            <button class="btn btn-outline-danger"><i class="fa fa-trash-alt"></i></button>
          </a>
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
<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\FeeCourseMaster;
use App\Models\FeeHead;
use App\Models\MainProgram;
use App\Models\ProgramGroup;
use App\Models\StudentProgram;

$batches = BatchMaster::all();
$programs = MainProgram::with('campus')->get();
$feeheads = FeeHead::latest()->get();
$feecoursemaster = FeeCourseMaster::latest()->get();
$programgroups = ProgramGroup::with(['programInfo'])->get();
?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Fee Structure </span></h3>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/fee-structure')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row">


            <div class="col-lg-4">
              <label for="">Select Program *</label>
              <select name="program" class="form-control mb-3">
                @foreach ($programs as $program)
                <option value="{{$program->id}}">{{$program->name}} - {{$program->campus->name}}</option>
                @endforeach
              </select>
            </div>


            <div class="col-lg-2">

              <label for="">Select Batch *</label>
              <select name="batch" class="form-control mb-3">
                <option value="">--Select--</option>
                @foreach ($batches as $batch)
                <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="student start getting message">Activation Date *</label>
              <input type="date" name="reminder_date" class="form-control mb-3">
            </div>
            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="final msg to student">Due Date *</label>
              <input type="date" name="due_date" class="form-control mb-3">
            </div>


            <div class="col-lg-12">
              <label for="">Course Name *</label>
              <select name="course" class="form-control mb-3">
                <option value="">--Select--</option>
                @foreach ($feecoursemaster as $fcm)
                <option value="{{$fcm->id}}">{{$fcm->name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label for="">Quarter Title *</label>
              <input type="text" name="quarter_title" class="form-control mb-3" placeholder="example : Admisson Time July ">
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="structure applicable to which year students">Studying in Year *</label>
              <select name="applicable_year" class="form-control">
                <option value="">--Select--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="fee display in order for student">Yearly Payment Order *</label>
              <select name="yearly_pay_order" class="form-control">
                <option value="">--Select--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>

            <hr>

            <div class="col-lg-12">
              <label for="">Select Fee Heads</label>
              <div id="morefeehead">
                <div class="row mb-3">
                  <div class="col-lg-7">
                    <select name="heads[]" class="form-select ">
                      @foreach ($feeheads as $feehead)
                      <option value="{{$feehead->id}}">{{$feehead->head_name}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-lg-3">
                    <input type="number" class="form-control" name="amounts[]" placeholder="Amount">
                  </div>

                  <div class="col-lg-1">
                    <button class="btn btn-danger" onClick="$(this).parent().parent().remove();"><i class=" fa fa-trash-alt"></i></button>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="addmorebtn">Add More Head</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>

      </form>
    </div>
  </div>
</div>

@if(count($data))
<div class="row">
  @foreach ($data as $item)
  <div class="col-lg-4 mb-3">
    <div class="card p-3 fee-card">
      @if ($item->is_payable == 1)
      <a href="{{url('erp/admin/accounts/update/feestructure-status/'.$item->id)}}"><i class="fa fa-check-circle fa-2x text-success"></i></a>
      @else
      <a href="{{url('erp/admin/accounts/update/feestructure-status/'.$item->id)}}"><i class="fa fa-ban fa-2x text-warning"></i></a>
      @endif
      <div class="card-body">

        <div class="title">
          <div class="fa">
            {{$item->batch->batch_name}}
          </div>

          <h4>{{$item->program->name}} - {{$item->program->campus->name}}</h2>
            <p>{{$item->feecoursemaster->name}}</p>
            <p> {{$item->std_current_year}} year - {{$item->quarter_title}}</p>

        </div>
        <hr>
        @if (count($item->feepvthead))
        <div class="price">
          <ul>
            @foreach ($item->feepvthead as $f)

            <li>
              <a href="{{url('erp/admin/accounts/del-headpvt/'.$f->id)}}" id="a-custom">
                <i class="fa fa-trash text-warning"></i>
              </a>
              {{$f->head->head_name}} - {{$f->amount}}

              <a data-bs-toggle="modal" data-bs-target="#edit{{$f->id}}">
                <i class="fa fa-edit text-dark"></i>
              </a>

              <div class="modal fade" id="edit{{$f->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title text-dark" id="exampleModalLabel">Edit Head</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{url('erp/admin/accounts/update-head-single')}}" method="post">
                      @csrf
                      <div class="modal-body">
                        <input type="text" value=" {{$f->head->head_name}} " readonly class="form-control mb-2">
                        <input type="text" value=" {{$f->amount}} " name="amount" class="form-control mb-2">
                        <input type="hidden" name="id" value="{{$f->id}}">

                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </li>

            @endforeach
          </ul>
        </div>
        @else
        <p>No Heads Found</p>
        @endif
        <div class="row">
          <button class="btn-total" style="cursor:none">
            <?php $total = StaticController::feeStructureTotal($item->id); ?>
            <i class="fa fa-rupee-sign"></i> {{$total}}/-
          </button>
        </div>



      </div>
      <div class="card-footer" style="cursor:pointer">



        <a data-bs-toggle="modal" data-bs-target="#editCard{{$item->id}}" class="btn-sm btn-dark">
          Edit Fee Structure
        </a>
        <div class="modal fade " id="editCard{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="{{url('erp/admin/accounts/update-fee-structure')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body editfeestructure">
                  <div class="row">
                    <div class="col-lg-6">
                      <label for="">Select Program *</label>
                      <select name="program" class="form-control mb-3">

                        @foreach ($programs as $program)
                        <option value="{{$program->id}}" {{$item->program->id == $program->id ? 'selected' : ''}}>{{$program->name}} - {{$program->campus->name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-lg-6">

                      <label for="">Select Batch *</label>
                      <select name="batch" class="form-control mb-3">
                        <option value="">--Select--</option>
                        @foreach ($batches as $batch)
                        <option value="{{$batch->id}}" {{$item->batch->id == $batch->id ? 'selected' : ''}}>{{$batch->batch_name}}</option>
                        @endforeach
                      </select>


                    </div>

                    <div class="col-lg-12">
                      <label for="">Course Name *</label>
                      <input type="text" class="form-control mb-3" value="{{$item->feecoursemaster->name}}" readonly>

                    </div>
                    <hr>


                    <div class="col-lg-12">
                      <label for="">Select Fee Heads</label>
                      <div id="updatefeehead">
                        <div class="row mb-3">
                          <div class="col-lg-7">
                            <select name="heads[]" class="form-select ">
                              <option value="">--Select--</option>
                              @foreach ($feeheads as $feehead)
                              <option value="{{$feehead->id}}">{{$feehead->head_name}}</option>
                              @endforeach
                            </select>
                          </div>

                          <div class="col-lg-3">
                            <input type="number" class="form-control" name="amounts[]" placeholder="Amount">
                          </div>


                          <div class="col-lg-1">
                            <button class="btn btn-danger" onClick="$(this).parent().parent().remove();"><i class=" fa fa-trash-alt"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="id" value="{{$item->id}}">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="updatemorebtn">Add More Head</button>
                  <button type="submit" class="btn btn-success">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <a data-bs-toggle="modal" data-bs-target="#viewProgs{{$item->id}}" class="btn-sm btn-dark mx-1">
          Link Programs *
        </a>
        <div class="modal fade " id="viewProgs{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Linked Programs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body editfeestructure">
                <div class="row">
                  @if (count($item->programspivot))
                  @foreach ($item->programspivot as $s)
                  <div class="col-lg-12 mb-3">
                    <button type="button" class="btn-sm btn-primary position-relative">
                      {{$s->programgroupinfo->program_code}} - {{$s->programgroupinfo->programInfo->name}}
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

        <a href="{{url('erp/admin/accounts/delete-feestructure/'.$item->id)}}" id="citadel" class="btn-sm btn-danger ">Delete</a>

      </div>
    </div>
  </div>
  @endforeach
</div>
@else
<p class=" text-center display-4">No Records Found</p>
@endif



@include('includes.footer')
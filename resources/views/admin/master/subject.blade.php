<?php

use App\Models\Campus;
use App\Models\ProgramMaster;
use App\Models\ShiftMaster;

$programs = ProgramMaster::latest()->get();
$campuses = Campus::latest()->get();
$shiftMasters = $shiftMasters ?? ShiftMaster::where('is_active', 1)->orderBy('sort_order')->get(['id', 'title', 'slug']);
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


  <div class="row mt-3">
    <div class="col-md-4 col-sm-12">
      <input type="text" id="subjectTableSearch" class="form-control" placeholder="Search academic departments...">
    </div>
  </div>


  <table class="table mt-3 mb-3 table-hover" id="subjectTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus </th>
        <th>Code</th>
        <th>Academic Department Name</th>
        <th>Main Program Type</th>
        <th>Enabled Shifts</th>
        <th>Admission Form Visibility</th>
        <th>Edit</th>
        <th>View</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @if (count($data))

      @foreach ($data as $item)
      <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{$item->campusmaster->name}}</td>
        <td><span>{{$item->code}}</span></td>
        <td><span>{{$item->title}}</span></td>
        <td><span class="text-capitalize">{{$item->main_program_type}}</span></td>
        <td>
          @php
          $selectedShiftIds = $item->shift_ids;
          if (is_string($selectedShiftIds)) {
          $selectedShiftIds = json_decode($selectedShiftIds, true);
          }
          $selectedShiftIds = collect($selectedShiftIds ?: [])->map(fn($id) => (int) $id)->filter()->values();
          $enabledShifts = $shiftMasters->whereIn('id', $selectedShiftIds->all())->values();
          @endphp

          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#shiftMode{{$item->id}}">
            <i class="fa fa-sliders"></i> Configure
          </button>
          <div class="mt-2">
            <span class="badge {{$item->has_shift_delivery == 1 ? 'badge-success' : 'badge-danger'}}">
              {{$item->has_shift_delivery == 1 ? 'Enabled' : 'Disabled'}}
            </span>
            @if($enabledShifts->count())
            @foreach($enabledShifts as $enabledShift)
            <span class="badge badge-info">{{$enabledShift->title}}</span>
            @endforeach
            @endif
          </div>

          <div class="modal fade" id="shiftMode{{$item->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Configure Shift Delivery</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('update.subject.shift.mode',[$item->id])}}" method="post">
                  @csrf
                  <div class="modal-body">
                    <label class="form-label">Select Enabled Shifts (multi-select)</label>
                    <select name="shift_ids[]" class="select-multiple" multiple size="6">
                      @foreach($shiftMasters as $shiftOption)
                      <option value="{{$shiftOption->id}}" {{$selectedShiftIds->contains((int) $shiftOption->id) ? 'selected' : ''}}>
                        {{$shiftOption->title}}
                      </option>
                      @endforeach
                    </select>
                    <small class="text-muted">Select one or more shifts to enable delivery. Leave empty to disable shift mode.</small>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Shifts</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </td>
        <td><a href=" {{route('toggle.subject.visibility.admission',[$item->id])}}">
            <span class="badge {{$item->display_in_admission_form == 1? 'badge-success' : 'badge-danger'}}">
              {{$item->display_in_admission_form == 1? 'Yes' : 'No'}}</span>
          </a>
        </td>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('subjectTableSearch');
    const tableBody = document.querySelector('#subjectTable tbody');

    if (!searchInput || !tableBody) {
      return;
    }

    const rows = Array.from(tableBody.querySelectorAll('tr'));

    searchInput.addEventListener('input', function() {
      const query = this.value.trim().toLowerCase();

      rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
      });
    });
  });
</script>

@include('includes.footer')
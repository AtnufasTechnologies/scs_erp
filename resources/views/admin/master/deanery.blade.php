<?php

use App\Models\MainProgram;

$programs = MainProgram::with('campus')->get();
?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">Deanery</span></h3>

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
        <h5 class="modal-title" id="exampleModalLabel">Add New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/master/deanery')}}" method="post">
        @csrf
        <div class="modal-body">
          <label for="">Select Program *</label>
          <select name="program_id" class="form-control mb-3">
            @foreach ($programs as $p)
            <option value="{{$p->id}}"> {{$p->campus->name}} - {{$p->name}}</option>
            @endforeach
          </select>
          <label for="">Deanery Name</label>
          <input type="text" class="form-control mb-3" name="title" placeholder="Type Here...">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="container-fluid card shdaow">

  <table class="table mt-3 mb-3" id="exportTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Programs</th>
        <th>Campus</th>
        <th>Departments </th>


      </tr>
    </thead>
    <tbody>
      @if (count($deanery))
      <?php $sl = 1 ?>
      @foreach($deanery as $d)
      <tr>
        <td>{{$sl++}}</td>
        <td>{{ $d->title }}</td>
        <td>{{ $d->program->name ?? '' }}</td>
        <td> {{ $d->program->campus->name ?? '' }}</td>
        <td>
          <ul>
            @foreach($d->deanerydeptpivot as $dp)
            <li>
              <button type="button" class="btn btn-primary position-relative">
                {{$dp->department!= null? $dp->department->name : ''}}
              </button>
            </li>
            @endforeach
          </ul>

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
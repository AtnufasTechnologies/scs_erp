<?php

use App\Http\Controllers\StaticController;
use App\Models\StudentProgram;

$allprograms = StudentProgram::with('campusmaster')->orderby('code', 'ASC')->get();
?>
@include('includes.header')
@include('admin.accounts.sidebar')

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

<div class="row mb-4">
  <div class="col-lg-6">
    <div class="input-group">
      <span class="input-group-text bg-primary text-white"><i class="fa fa-search"></i></span>
      <input type="text" id="liveSearchCards" class="form-control" placeholder="Search by ID or Course Name...">
    </div>
  </div>
  <div class="col-lg-6">
    <form action="{{url('erp/admin/accounts/fee-course-master')}}" method="get">
      <div class="input-group">
        <select name="coursemaster" class="form-control dselect-example">
          <option value="">--Filter by Course--</option>
          @foreach ($allcourses as $item)
          <option value="{{$item->id}}">{{$item->name}}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Filter</button>
      </div>
    </form>
  </div>
</div>

<div id="coursesContainer">
  @if (count($data))
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle small" id="courseTable">
      <thead class="table-dark">
        <tr>
          <th style="width:60px">#</th>
          <th>Course Name</th>
          <th class="text-center" style="width:160px">Linked Programs</th>
          <th class="text-center" style="width:280px">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data as $item)
        <?php
        $connectedPrograms = StaticController::fetchFeeStructurePrograms($item->id);
        ?>
        <tr data-course-id="{{$item->id}}" data-course-name="{{$item->name}}">
          <td class="text-center fw-bold text-muted">{{ $item->id }}</td>
          <td class="fw-semibold">{{ $item->name }}</td>
          <td class="text-center">
            @if(count($connectedPrograms))
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewProgs{{$item->id}}">
              {{ count($connectedPrograms) }} Program(s)
            </button>
            @else
            <span class="text-muted fst-italic small">None</span>
            @endif
          </td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-1">
              <button class="btn btn-success btn-sm" data-bs-target="#linkAddModal{{$item->id}}" data-bs-toggle="modal" title="Connect Programs">
                <i class="fa fa-plus-circle"></i> Connect
              </button>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}" title="Edit Course Name">
                <i class="fa fa-edit"></i> Edit
              </button>
              <a href="{{url('erp/admin/accounts/del-feecourse-master/'.$item->id)}}" id="citadel"
                onclick="return confirm('Delete this course?')">
                <button class="btn btn-sm btn-danger" title="Delete Course">
                  <i class="fa fa-trash-alt"></i>
                </button>
              </a>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- MODALS (outside table) --}}
  @foreach ($data as $item)
  <?php
  $connectedPrograms = StaticController::fetchFeeStructurePrograms($item->id);
  ?>

  <!-- View Programs Modal -->
  <div class="modal fade" id="viewProgs{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <div>
            <h5 class="modal-title mb-0"><i class="fa fa-link me-2"></i>Linked Programs</h5>
            <small class="opacity-75">{{ $item->name }}</small>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="px-3 pt-3 pb-2 border-bottom bg-light">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
              <input type="text" class="form-control border-start-0" placeholder="Search by code, name or campus…"
                onkeyup="filterPrograms(this, 'programList{{$item->id}}')">
            </div>
          </div>
          <div class="px-3 py-2">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold small">
              {{ count($connectedPrograms) }} program(s) linked
            </span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0" id="programList{{$item->id}}">
              <thead class="table-light">
                <tr>
                  <th style="width:90px">Code</th>
                  <th>Program Name</th>
                  <th style="width:140px">Campus</th>
                  <th class="text-center" style="width:80px">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($connectedPrograms as $s)
                <tr class="program-item" data-program-text="{{$s->studentprogram->code ?? ''}} {{$s->studentprogram->name ?? ''}} {{$s->studentprogram->campusmaster->name ?? ''}}">
                  <td><span class="badge bg-secondary fw-normal">{{ $s->studentprogram->code ?? '-' }}</span></td>
                  <td class="fw-semibold">{{ $s->studentprogram->name ?? '-' }}</td>
                  <td class="text-muted">{{ $s->studentprogram->campusmaster->name ?? '-' }}</td>
                  <td class="text-center">
                    <a href="{{ url('erp/admin/accounts/unlink/fee-structure-group/'.$s->id) }}"
                      onclick="return confirm('Remove this program from the course?')"
                      class="btn btn-outline-danger btn-sm" title="Unlink">
                      <i class="fa fa-unlink"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer bg-light border-top">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success btn-sm" data-bs-dismiss="modal"
            data-bs-toggle="modal" data-bs-target="#linkAddModal{{$item->id}}">
            <i class="fa fa-plus-circle me-1"></i> Add More
          </button>
        </div>
      </div>
    </div>
  </div>


  <!-- Link Add Modal -->
  <div class="modal fade" id="linkAddModal{{$item->id}}" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-plus-circle me-2"></i>Connect Programs — {{ $item->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{route('link.coursemaster.prggroup')}}" method="post">
          @csrf
          <div class="modal-body">
            <label for="">Select Programs (at least 1 required)</label>
            <select name="progs[]" class="select-multiple" multiple>
              @foreach ($allprograms as $p)
              <option value="{{$p->id}}">{{$p->code ?? '-'}} - {{$p->name ?? '-'}} | {{$p->campusmaster->name ?? ''}}</option>
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

  <!-- Edit Modal -->
  <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Edit Course Name</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

  @endforeach

  @else
  <div class="alert alert-info text-center py-5">
    <i class="fa fa-info-circle fa-3x mb-3"></i>
    <h4>No Course Records Found</h4>
    <p class="mb-0">Click "Add New" to create your first fee course master.</p>
  </div>
  @endif
</div>

<style>
  @media (max-width: 768px) {
    .table-responsive {
      font-size: 0.8rem;
    }
  }
</style>

@include('includes.footer')

<script>
  // Live search — filter table rows
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchCards');
    if (searchInput) {
      searchInput.addEventListener('keyup', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#courseTable tbody tr').forEach(row => {
          const id = (row.getAttribute('data-course-id') || '').toLowerCase();
          const name = (row.getAttribute('data-course-name') || '').toLowerCase();
          row.style.display = (id.includes(term) || name.includes(term)) ? '' : 'none';
        });
      });
    }
  });

  // Live search filter function for program lists in modals
  function filterPrograms(input, tableId) {
    const term = input.value.toLowerCase().trim();
    document.querySelectorAll('#' + tableId + ' tbody tr.program-item').forEach(row => {
      const text = (row.getAttribute('data-program-text') || '').toLowerCase();
      row.style.display = text.includes(term) ? '' : 'none';
    });
  }
</script>
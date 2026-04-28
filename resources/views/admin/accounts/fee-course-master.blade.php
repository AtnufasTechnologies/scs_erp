<?php

use App\Http\Controllers\StaticController;

$fetchPrograms = StaticController::fetchProgramGroupNew();

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

<div id="coursesContainer" class="row g-4">
  @if (count($data))
  @foreach ($data as $item)
  <?php $courseGroup = StaticController::fetchCourseMasterGroups($item->id); ?>

  <div class="col-lg-4 col-md-6 " data-course-id="{{$item->id}}" data-course-name="{{$item->name}}">
    <div class="card shadow h-100 hover-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-graduation-cap me-2"></i>Course #{{$item->id}}</h5>
        <a href="{{url('erp/admin/accounts/del-feecourse-master/'.$item->id)}}" id="citadel">
          <button class="btn btn-sm btn-danger" title="Delete Course">
            <i class="fa fa-trash-alt"></i>
          </button>
        </a>
      </div>
      <div class="card-body">
        <h6 class="card-title text-dark fw-bold mb-3">{{$item->name}}</h6>

        <div class="d-flex align-items-center mb-3">

          @if(count($courseGroup))
          <a data-bs-toggle="modal" data-bs-target="#viewProgs{{$item->id}}" class="btn btn-sm btn-outline-danger">
            <i class="fa fa-eye"></i> View {{count($courseGroup)}}
          </a>
          @else
          <span class="text-muted small">No associations</span>
          @endif
        </div>

        <div class="d-grid gap-2">
          <button class="btn btn-success btn-sm" data-bs-target="#linkAddModal{{$item->id}}" data-bs-toggle="modal">
            <i class="fa fa-plus-circle"></i> Connect Programs
          </button>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}">
            <i class="fa fa-edit"></i> Edit Course Name
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Programs Modal -->
  @if(count($courseGroup))
  <div class="modal fade" id="viewProgs{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Linked Programs</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body editfeestructure">
          <div class="mb-3">
            <input type="text" class="form-control" placeholder="Search programs..." onkeyup="filterPrograms(this, 'programList{{$item->id}}')">
          </div>
          <div class="row" id="programList{{$item->id}}">
            @foreach ($courseGroup as $s)
            <div class="col-lg-6 mb-3 program-item" data-program-text="{{$s->programgroupinfo->programInfo->code}} {{$s->programgroupinfo->programInfo->name}} {{$s->programgroupinfo->campus->name}}">
              <button type="button" class="btn-sm btn-outline-primary position-relative">
                ({{$s->programgroupinfo->programInfo->code}}) {{$s->programgroupinfo->programInfo->name}} - {{$s->programgroupinfo->campus->name}}
                <a href="{{url('erp/admin/accounts/unlink/fee-structure-group/'.$s->id)}}" id="citadel">
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <i class="fa fa-times"></i>
                  </span>
                </a>
              </button>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  <!-- Link Add Modal -->
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

  <!-- Edit Modal -->
  <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
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

  @endforeach
  @else
  <div class="col-12">
    <div class="alert alert-info text-center py-5">
      <i class="fa fa-info-circle fa-3x mb-3"></i>
      <h4>No Course Records Found</h4>
      <p class="mb-0">Click "Add New" to create your first fee course master.</p>
    </div>
  </div>
  @endif
</div>

<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .hover-card {
    transition: all 0.3s ease;
  }

  .hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
  }

  .card-header h5 {
    font-size: 1rem;
  }

  .card-title {
    font-size: 1.1rem;
    min-height: 50px;
    display: flex;
    align-items: center;
  }

  @media (max-width: 768px) {
    .col-lg-4 {
      margin-bottom: 1rem;
    }
  }
</style>

@include('includes.footer')

<script>
  // Live search filter function for cards
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchCards');

    if (searchInput) {
      searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const courseCards = document.querySelectorAll('.course-card');

        courseCards.forEach(card => {
          const courseId = card.getAttribute('data-course-id').toLowerCase();
          const courseName = card.getAttribute('data-course-name').toLowerCase();

          if (courseId.includes(searchTerm) || courseName.includes(searchTerm)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    }
  });

  // Live search filter function for program lists in modals
  function filterPrograms(input, listId) {
    const searchTerm = input.value.toLowerCase().trim();
    const programList = document.getElementById(listId);
    const programItems = programList.querySelectorAll('.program-item');

    programItems.forEach(item => {
      const programText = item.getAttribute('data-program-text').toLowerCase();

      if (programText.includes(searchTerm)) {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }
</script>
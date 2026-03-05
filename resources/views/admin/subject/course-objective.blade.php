<?php

use App\Models\CognitiveLevelMaster;

$taxonomylevels = CognitiveLevelMaster::all();

?>
@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">

  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $course->courseMaster->course_code ?? '-' }} - {{ $course->courseMaster->course_title ?? '-' }} / Course Specific Objectives</span>
        </a>

      </div>
    </nav>

    <!-- Button to trigger modal for new objective -->
    <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addObjectiveModal">
      <i class="fa fa-plus-circle"></i> New CSO
    </button>

    <!-- Modal for adding new objective -->
    <div class="modal fade" id="addObjectiveModal" tabindex="-1" aria-labelledby="addObjectiveModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addObjectiveModalLabel">Add New CSO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('department.create.course.specific.objective') ?? '#' }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="row">

                <div class="col-lg-3">
                  <div class="mb-3">
                    <label for="lecturesNeeded" class="form-label">Lectures Needed*</label>
                    <input type="number" class="form-control" id="lecturesNeeded" name="lectures_needed" required min="1">

                  </div>

                </div>
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="objectiveTitle" class="form-label">CSO Title *</label>
                    <textarea name="title" class="editor2 form-control"></textarea>

                  </div>
                </div>


                <input type="hidden" name="course_id" value="{{ $course->courseMaster->id }}">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Create CSO</button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>

    <!-- Course Details Card -->
    <div class="container-fluid">
      <div class="row mb-4">
        <div class="col-lg-12">
          <div class="card shadow-sm">
            <div class="card-header bg-light">
              <h5 class="mb-0">Course Information</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Course Code:</strong> {{ $course->courseMaster->course_code ?? '-' }}</p>
                  <p><strong>Course Title:</strong> {{ $course->courseMaster->course_title ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                  <p><strong>Course Type:</strong> {{ $course->courseMaster->coursetypemaster->title ?? '-' }}</p>
                  <p><strong>Credits:</strong> {{ $course->courseMaster->credits ?? '-' }} |
                    <strong>Paper Type:</strong> {{ $course->courseMaster->papertypemaster->name ?? '-' }} |
                    <strong>Total Hrs:</strong> {{ $course->courseMaster->total_alloted_hours ?? '-' }}
                  </p>

                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Objectives Table -->
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header bg-light">
              <h5 class="mb-0">Course Specific Objectives</h5>
            </div>
            <div class="card-body">
              @forelse ($course->courseMaster->csos as $cso)
              <div class="card mb-3 border">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-9">
                      <h6 class="card-title mb-2">{{ $loop->iteration }}. {!! $cso->title !!}</h6>
                      <p class="card-text text-muted mb-0">
                        <strong>Lectures Needed:</strong> {{ $cso->lectures_needed }}
                      </p>
                    </div>
                    <div class="col-md-3 text-end">

                      <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editObjectiveModal{{ $cso->id }}">Edit</button>

                      <!-- Modal for editing objective -->
                      <div class="modal fade" id="editObjectiveModal{{ $cso->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit CSO</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('department.update.cso', $cso->id) ?? '#' }}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="modal-body">
                                <div class="row">
                                  <div class="col-lg-3">

                                    <label for="lecturesNeeded" class="form-label">Lectures Needed*</label>
                                    <input type="number" class="form-control" id="lecturesNeeded{{ $cso->id }}" name="lectures_needed" value="{{ $cso->lectures_needed }}" required min="1">

                                  </div>
                                  <div class="col-lg-12">

                                    <label for="">CSO Title*</label>
                                    <textarea name="title" class="form-control" required>{!! $cso->title !!}</textarea>

                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Update </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                      <button class="btn btn-sm btn-danger">Delete</button>


                    </div>
                  </div>

                  <!-- CSO Subunits Section -->
                  <hr class="my-3">
                  <h6 class="mb-2">Subunits:</h6>
                  @if($cso->subunits && count($cso->subunits) > 0)
                  <ul class="list-group list-group-sm mb-2">
                    @foreach($cso->subunits as $subunit)
                    <li class="list-group-item">{{ $subunit->title }}</li>
                    @endforeach
                  </ul>
                  @else
                  <p class="text-muted small">No subunits added yet.</p>
                  @endif
                  <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addSubunitModal{{ $cso->id }}">
                    <i class="fa fa-plus"></i> Add Subunit
                  </button>

                  <!-- Modal for adding subunit -->
                  <div class="modal fade" id="addSubunitModal{{ $cso->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Add Subunit to CSO</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="" method="post">
                          @csrf
                          <div class="modal-body">
                            <div class="mb-3">
                              <label for="subunitTitle" class="form-label">Subunit Title *</label>
                              <textarea name="title" class="form-control" required></textarea>
                            </div>
                            <input type="hidden" name="cso_id" value="{{ $cso->id }}">
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Add Subunit</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @empty
              <p class="text-muted">No objectives added yet.</p>
              @endforelse
            </div>
            <div class="card-footer bg-light">
              <p class="mb-0 text-muted small">Total CSOs: {{ count($course->courseMaster->csos) }}</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  @include('includes.footer')
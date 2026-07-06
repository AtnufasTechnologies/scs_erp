<?php

use App\Models\CognitiveLevelMaster;

$taxonomylevels = CognitiveLevelMaster::all();
$selectedShift = request('shift', 'all');
$subjectUsesShifts = $subjectUsesShifts ?? false;
$shiftOptions = $shiftOptions ?? collect();
$shiftSlugs = $shiftOptions->pluck('slug')->toArray();
$defaultShift = in_array('common', $shiftSlugs, true) ? 'common' : ($shiftSlugs[0] ?? 'common');
$newCsoShift = in_array($selectedShift, $shiftSlugs, true) ? $selectedShift : $defaultShift;
$allCsos = $course->courseMaster->csos ?? collect();
$filteredCsos = $allCsos;
if (in_array($selectedShift, $shiftSlugs, true)) {
  $filteredCsos = $allCsos->where('shift', $selectedShift)->values();
}

?>
@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  @if ($errors->any())

  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  @endif
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

    @if($subjectUsesShifts)
    <form action="{{ route('department.view.cso', $course->courseMaster->id) }}" method="get" class="row g-2 mb-3">
      <div class="col-md-3">
        <select name="shift" class="form-select" onchange="this.form.submit()">
          <option value="all" {{ $selectedShift === 'all' ? 'selected' : '' }}>All Shifts</option>
          @foreach ($shiftOptions as $shiftOption)
          <option value="{{ $shiftOption->slug }}" {{ $selectedShift === $shiftOption->slug ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
          @endforeach
        </select>
      </div>
    </form>
    @endif

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

                @if($subjectUsesShifts)
                <div class="col-lg-3">
                  <div class="mb-3">
                    <label for="csoShift" class="form-label">Shift*</label>
                    <select class="form-select" id="csoShift" name="shift" required>
                      @foreach ($shiftOptions as $shiftOption)
                      <option value="{{ $shiftOption->slug }}" {{ $newCsoShift === $shiftOption->slug ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                @endif

                <div class="col-lg-3">
                  <div class="mb-3">
                    <label for="lecturesNeeded" class="form-label">Lectures Needed*</label>
                    <input type="number" class="form-control" id="lecturesNeeded" name="lectures_needed" required min="1">

                  </div>

                </div>
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="objectiveTitle" class="form-label">CSO Title *</label>
                    <textarea name="title" class="form-control" placeholder="Ex: Unit 1"></textarea>

                  </div>
                </div>


                <input type="hidden" name="course_id" value="{{ $course->courseMaster->id ?? '-' }}">
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
                  <p><strong>Course Type:</strong> {{ $course->courseMaster->coursetypemaster->title ?? '-' }} - {{ $course->courseMaster->coursetypemaster->description ?? '-' }}</p>
                  <p><strong>Credits:</strong> {{ $course->courseMaster->credits ?? '-' }} |
                    <strong>Paper Type:</strong> {{ $course->courseMaster->papertypemaster->name ?? '-' }} |
                    <strong>Teaching Hrs:</strong> {{ $course->courseMaster->total_alloted_hours ?? '-' }}
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


          <h5 class="mb-0">Course Specific Objectives</h5>

          <div class="card-body">
            @forelse ($filteredCsos as $cso)
            <div class="card mb-3 border">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-9">
                    <h6 class="card-title mb-2">{{ $loop->iteration }}. {!! $cso->title !!}</h6>
                    <p class="card-text text-muted mb-0">
                      <strong>Lectures Needed:</strong> {{ $cso->lectures_needed }}
                      @if($subjectUsesShifts)
                      <span class="ms-2 badge bg-info text-dark">{{ ucfirst($cso->shift ?? 'common') }}</span>
                      @endif
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
                                @if($subjectUsesShifts)
                                <div class="col-lg-3">

                                  <label for="csoShift{{ $cso->id }}" class="form-label">Shift*</label>
                                  <select class="form-select" id="csoShift{{ $cso->id }}" name="shift" required>
                                    @foreach ($shiftOptions as $shiftOption)
                                    <option value="{{ $shiftOption->slug }}" {{ ($cso->shift ?? $defaultShift) === $shiftOption->slug ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                                    @endforeach
                                  </select>

                                </div>
                                @endif
                                <div class="col-lg-3">

                                  <label for="lecturesNeeded" class="form-label">Lectures Needed*</label>
                                  <input type="number" class="form-control" id="lecturesNeeded{{ $cso->id }}" name="lectures_needed" value="{{ $cso->lectures_needed }}" required min="1">

                                </div>
                                <div class="col-lg-12">

                                  <label for="">CSO Title*</label>
                                  <textarea name="title" class="form-control" required placeholder="Ex: Unit 1">{!! $cso->title !!}</textarea>

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
                    <a href="{{ route('department.delete.cso', $cso->id) }}" class="btn btn-sm btn-danger" id="citadel">Delete</a>


                  </div>
                </div>

                <!-- CSO Subunits Section -->
                <hr class="my-3">
                <h6 class="mb-2">Sub Units:</h6>
                @if($cso->csosubunits && count($cso->csosubunits) > 0)
                <ul class="list-group list-group-sm mb-2">
                  @foreach($cso->csosubunits as $subunit)
                  <li class="list-group-item shadow d-flex justify-content-between align-items-start">
                    <div>
                      @foreach ($subunit->taxonomies as $taxonomy)
                      <button type="button" class="badge badge-success position-relative">
                        {{ $taxonomy->rbtmaster->shortname ?? 'N/A' }}
                        <a href="{{route('department.delete.cso.subunit.taxonomy', $taxonomy->id)}}" id="citadel">
                          <i class="fa fa-times"></i>
                        </a>
                      </button>
                      @endforeach
                      <br>

                      {{$loop->iteration}}. {{ $subunit->title }}
                      @if($subunit->image_path != null)
                      <br>
                      <img src="{{Storage::disk('s3')->url($subunit->image_path)}}" alt="Subunit Image" class="img-fluid mt-2">
                      @endif
                    </div>
                    <!-- bootstrap modal for editing subunit -->
                    <div>
                      <button class="btn btn-xs btn-warning" data-bs-toggle="modal" data-bs-target="#editSubunitModal{{ $subunit->id }}"> <i class="fa fa-edit"></i> </button>

                      <!-- Modal for editing subunit -->
                      <div class="modal fade" id="editSubunitModal{{ $subunit->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit Sub Unit</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('department.update.cso.subunit', $subunit->id) ?? '#' }}" method="post" enctype="multipart/form-data">
                              @csrf
                              @method('PUT')
                              <div class="modal-body">

                                <div class="mb-3">
                                  <label for="subunitTitle" class="form-label">Sub Unit Title *</label>
                                  <textarea name="title" class="form-control">{{ $subunit->title }}</textarea>
                                  @error('title')
                                  <span class="text-danger">{{$message}}</span>
                                  @enderror
                                </div>
                                <div class="row">
                                  <div class="col-lg-12">
                                    <div class="mb-3">
                                      <label for="" class="form-label">Bloom's Taxonomy *</label>
                                      <select name="taxonomy[]" class="form-select select-multiple" multiple>
                                        @foreach ($taxonomylevels as $level)
                                        <option value="{{$level->id}}" {{ $subunit->taxonomies->contains('rbt_id', $level->id) ? 'selected' : '' }}>{{$level->shortname}} - {{$level->fullname}}</option>
                                        @endforeach
                                      </select>
                                      @error('taxonomy')
                                      <span class="text-danger">{{$message}}</span>
                                      @enderror
                                    </div>
                                  </div>
                                  <div class="col-lg-12">
                                    <div class="mb-3">
                                      <label for="subunitPhoto" class="form-label">Upload Photo (allowed: jpg,pn max: 5MB)</label>
                                      <input type="file" class="form-control" name="photo">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Update Sub Unit</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>

                      <a href="{{ route('department.delete.cso.subunit', $subunit->id) }}"
                        class="btn btn-xs btn-danger ms-2"
                        onclick="return confirm('Delete this subunit?')">
                        <i class="fa fa-trash"></i>
                      </a>
                  </li>
                  @endforeach
                </ul>
                @else
                <p class="text-muted small">No subunits added yet.</p>
                @endif
                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#addSubunitModal{{ $cso->id }}">
                  <i class="fa fa-plus"></i> Add Sub Unit
                </button>

                <!-- Modal for adding subunit -->
                <div class="modal fade" id="addSubunitModal{{ $cso->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Add Sub Unit to CSO</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="{{route('department.add.cso.subunit')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">

                          <div class="mb-3">
                            <label for="subunitTitle" class="form-label">Sub Unit Title *</label>
                            <textarea name="title" class="form-control"></textarea>
                            @error('title')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                          </div>

                          <div class="row">
                            <div class="col-lg-12">
                              <div class="mb-3">
                                <label for="" class="form-label">Bloom's Taxonomy *</label>
                                <select name="taxonomy[]" class="form-select select-multiple" multiple>
                                  @foreach ($taxonomylevels as $level)
                                  <option value="{{$level->id}}">{{$level->shortname}} - {{$level->fullname}}</option>
                                  @endforeach
                                </select>
                                @error('taxonomy')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                              </div>
                            </div>
                            <div class="col-lg-12">
                              <div class="mb-3">
                                <label for="subunitPhoto" class="form-label">Upload Photo (allowed: jpg,pn max: 5MB)</label>
                                <input type="file" class="form-control" name="photo">
                              </div>
                            </div>
                          </div>



                          <input type="hidden" name="cso_id" value="{{ $cso->id }}">
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-success" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none me-2" id="loader" role="status" aria-hidden="true"></span>
                            <span id="btnText">Add Sub Unit</span>
                          </button>

                          <script>
                            document.querySelector('form').addEventListener('submit', function() {
                              document.getElementById('submitBtn').disabled = true;
                              document.getElementById('loader').classList.remove('d-none');
                            });
                          </script>
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
            <p class="mb-0 text-muted small">Total CSOs: {{ count($filteredCsos) }}</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@include('includes.footer')
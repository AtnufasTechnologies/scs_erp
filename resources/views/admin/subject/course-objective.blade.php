@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">

  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $course->courseMaster->course_code ?? '-' }} - {{ $course->courseMaster->course_title ?? '-' }} / Objectives</span>
        </a>
        <div class="d-flex">
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
        </div>
      </div>
    </nav>

    <!-- Button to trigger modal for new objective -->
    <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addObjectiveModal">
      <i class="fa fa-plus-circle"></i> Add New Objective
    </button>

    <!-- Modal for adding new objective -->
    <div class="modal fade" id="addObjectiveModal" tabindex="-1" aria-labelledby="addObjectiveModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addObjectiveModalLabel">Add New Objective</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('department.create.course.objective') ?? '#' }}" method="post">
            @csrf
            <div class="modal-body">
              <div class="row">
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="objectiveTitle" class="form-label">Objective Title *</label>
                    <input type="text" class="form-control" id="objectiveTitle" name="objective_title" required>
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="objectiveDescription" class="form-label">Objective Description *</label>
                    <textarea class="form-control" id="objectiveDescription" name="objective_description" rows="4" required></textarea>
                  </div>
                </div>
              </div>

              <input type="hidden" name="course_id" value="{{ $course->id ?? '' }}">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Create Objective</button>
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
                  <p><strong>Credits:</strong> {{ $course->courseMaster->credits ?? '-' }}</p>
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
        <div class="card">
          <div class="card-header bg-light">
            <h5 class="mb-0">Course Objectives</h5>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white rounded shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Objective Title</th>
                  <th>Description</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($objectives ?? [] as $objective)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $objective->objective_title ?? '-' }}</td>
                  <td>{{ Str::limit($objective->objective_description ?? '-', 100) }}</td>
                  <td>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editObjectiveModal{{ $objective->id ?? '' }}">
                      <i class="fa fa-edit"></i> Edit
                    </button>
                    <form action="" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this objective?')">Delete</button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center">No objectives found. Add your first objective using the button above.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

@include('includes.footer')
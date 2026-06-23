<?php

use App\Models\BatchMaster;
use App\Models\PaperTypeMaster;
use App\Models\SubjectTypeMaster;

$coursetypes = SubjectTypeMaster::all();
$batches = BatchMaster::all();
$papertypes = PaperTypeMaster::all();
?>

@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">

  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / Course Objective Master</span>
        </a>
        <div class="d-flex">
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
        </div>
      </div>
    </nav>

    <!-- Button to trigger modal for new course -->
    <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addCourseModal">
      <i class="fa fa-plus-circle"></i> Add New Course
    </button>

    <!-- Modal for adding new course -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('department.create.course.master') }}" method="post">
            @csrf
            <div class="modal-body">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="batch" class="form-label">Batch *</label>
                    <select class="form-select dselect-example" id="" name="batch" required>
                      <option value="">Select Batch</option>
                      @foreach($batches as $batch)
                      <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="courseType" class="form-label">Course Type *</label>
                    <select class="form-select dselect-example" id="" name="course_type" required>
                      <option value="">Select Course Type</option>
                      @foreach($coursetypes as $type)
                      <option value="{{ $type->id }}">{{ $type->title }} - {{$type->description}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="courseCode" class="form-label">Course Code *</label>
                    <input type="text" class="form-control" id="courseCode" name="course_code" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="paperType" class="form-label">Paper Type *</label>
                    <select class="form-select" id="paperType" name="paper_type" required>
                      <option value="">Select Paper Type</option>
                      @foreach($papertypes as $papertype)
                      <option value="{{ $papertype->id }}">{{ $papertype->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="credits" class="form-label">Credits *</label>
                    <input type="number" class="form-control" id="credits" name="credits" step="0.5" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="teachingHours" class="form-label">Teaching Hours *</label>
                    <input type="number" class="form-control" id="teachingHours" name="total_alloted_hours" min="0">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="internalMarks" class="form-label">Internal Marks *</label>
                    <input type="number" class="form-control" id="internalMarks" name="internal" min="0">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="externalMarks" class="form-label">External Marks *</label>
                    <input type="number" class="form-control" id="externalMarks" name="external" min="0">
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="courseTitle" class="form-label">Course Title *</label>
                    <textarea name="course_title" id="courseTitle" class="form-control" required></textarea>
                  </div>
                </div>
              </div>

              <input type="hidden" name="subject_id" value="{{ $data->id }}">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success submit-btn">
                <span class="submit-text">Create Course</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <span class="loading-text d-none">Processing...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Add Courses</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('department.add.course.master') }}" method="post">
            @csrf
            <div class="modal-body">
              <label for="">Select Master Course *</label>
              <select name="courses[]" class="select-multiple" multiple>
                @foreach ($course_master as $course)
                <option value="{{ $course->id }}">{{ $course->id }} - {{ $course->course_code }} - {{ $course->course_title }} ({{$course->coursetypemaster->title ?? '-'   }})</option>
                @endforeach
              </select>


              <input type="hidden" name="subject_id" value="{{ $data->id }}">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary submit-btn">
                <span class="submit-text">Save changes</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <span class="loading-text d-none">Processing...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
      <i class="fa fa-plus-circle"></i> From Existing Course Master
    </button>

    <!-- Search Bar -->
    <div class="mb-3">
      <input type="text" class="form-control" id="courseSearch" placeholder="Search courses by code or title...">
    </div>

    <script>
      document.getElementById('courseSearch').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.col-md-6.col-lg-4');

        cards.forEach(card => {
          const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
          const text = card.querySelector('.card-text')?.textContent.toLowerCase() || '';

          if (title.includes(searchTerm) || text.includes(searchTerm)) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    </script>


    <div class="container-fluid">
      <h3>MY CO List</h3>

      <!-- Card Layout for Courses -->
      <div class="row mt-4">
        @forelse($mycourses as $course)
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card shadow-sm h-100">
            <div class="card-header">
              <a href="{{ route('department.view.cso', $course->course_master_id  ) }}"> <button class="btn btn-outline-success">Design Course</button></a>
            </div>
            <div class="card-body">
              <p class="card-text "><strong>Code# </strong>{{ $course->courseMaster->course_code ?? '' }}</p>
              <p class="card-text text-muted"><strong>Name:</strong> {{ $course->courseMaster->course_title ?? '' }}</p>
              <ul class="list-unstyled small">
                <li><i class="far fa-quote-left text-success"></i><strong> Type:</strong> {{ $course->courseMaster->coursetypemaster->title ?? '-' }} - {{ $course->courseMaster->coursetypemaster->description ?? '-' }}
                </li>
                <li><i class="fa fa-bookmark text-success"></i> <strong>Credits:</strong> {{ $course->courseMaster->credits ?? '-' }}</li>
                <li><i class="fa fa-badge text-success"></i> <strong>Paper Type:</strong> {{ $course->courseMaster->papertypemaster->name ?? '-' }}</li>
                <li><i class="fa fa-clock text-success"></i> <strong>Teaching Hours:</strong> {{ $course->courseMaster->total_alloted_hours ?? '-' }}</li>
                <li><strong>Internal:</strong> {{ $course->courseMaster->internal ?? '-' }} | <strong>External:</strong> {{ $course->courseMaster->external ?? '-' }}</li>
              </ul>
            </div>
            <div class="card-footer bg-white">
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editCourseModal{{ $course->id }}"><i class="fa fa-edit"></i></button>

              <!-- Edit Course Modal -->
              <div class="modal fade" id="editCourseModal{{ $course->id }}" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('department.update.course.master',$course->courseMaster->id ?? '') }}" method="post">
                      @csrf
                      @method('PUT')
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">Course Code *</label>
                              <input type="text" class="form-control" name="course_code" value="{{ $course->courseMaster->course_code ?? '' }}" required>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">Credits *</label>
                              <input type="number" class="form-control" name="credits" value="{{ $course->courseMaster->credits ?? '' }}" step="0.5" required>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">Total Teaching Hours</label>
                              <input type="number" class="form-control" name="total_alloted_hours" value="{{ $course->courseMaster->total_alloted_hours ?? '' }}">
                            </div>
                          </div>

                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">Internal</label>
                              <input type="number" class="form-control internal-marks" name="internal" value="{{ $course->courseMaster->internal ?? '' }}" required>
                            </div>
                          </div>

                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">External</label>
                              <input type="number" class="form-control external-marks" name="external" value="{{ $course->courseMaster->external ?? '' }}" required>
                            </div>
                          </div>

                          <div class="col-lg-4">
                            <div class="mb-3">
                              <label class="form-label">Full</label>
                              <input type="number" class="form-control full-marks" value="{{ ($course->courseMaster->internal ?? 0) + ($course->courseMaster->external ?? 0) }}" name="full" readonly>
                            </div>
                          </div>

                          <script>
                            document.querySelectorAll('.internal-marks, .external-marks').forEach(input => {
                              input.addEventListener('input', function() {
                                const parent = this.closest('.modal-content');
                                const internal = parseFloat(parent.querySelector('.internal-marks').value) || 0;
                                const external = parseFloat(parent.querySelector('.external-marks').value) || 0;
                                parent.querySelector('.full-marks').value = (internal + external);
                              });
                            });
                          </script>
                          <div class="col-lg-3">
                            <div class="mb-3">
                              <label class="form-label">Paper Type *</label>
                              <select name="paper_type" id="paper_type" class="form-select" required>
                                <option value="">Select Paper Type</option>
                                @foreach($papertypes as $papertype)
                                <option value="{{ $papertype->id }}" {{ ($course->courseMaster->papertypemaster->id ?? '') == $papertype->id ? 'selected' : '' }}>{{ $papertype->name }}</option>
                                @endforeach

                              </select>
                            </div>
                          </div>
                          <div class="col-lg-9">
                            <div class="mb-3">
                              <label class="form-label">Course Type *</label>
                              <select class="form-select dselect-example" name="course_type" required>
                                <option value="">Select Course Type</option>
                                @foreach($coursetypes as $type)
                                <option value="{{ $type->id }}" {{ ($course->courseMaster->coursetypemaster->id ?? '') == $type->id ? 'selected' : '' }}>{{ $type->title }} - {{$type->description}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-lg-12">
                            <div class="mb-3">
                              <label class="form-label">Course Title *</label>
                              <input type="text" class="form-control" name="course_title" value="{{ $course->courseMaster->course_title ?? '' }}" required>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success submit-btn">
                          <span class="submit-text">Update Course</span>
                          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                          <span class="loading-text d-none">Processing...</span>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <form action="" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?')"><i class="fa fa-trash"></i> Delete</button>
              </form>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12">
          <p class="text-center text-muted">No courses found.</p>
        </div>
        @endforelse
      </div>

    </div>
  </div>
</div>

</div>
</div>

<script>
  // Prevent multiple form submissions
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('.submit-btn');

      if (submitBtn && !submitBtn.disabled) {
        // Disable the button
        submitBtn.disabled = true;

        // Toggle visibility of text and spinner
        const submitText = submitBtn.querySelector('.submit-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        const loadingText = submitBtn.querySelector('.loading-text');

        if (submitText) submitText.classList.add('d-none');
        if (spinner) spinner.classList.remove('d-none');
        if (loadingText) loadingText.classList.remove('d-none');
      }
    });
  });
</script>

@include('includes.footer')
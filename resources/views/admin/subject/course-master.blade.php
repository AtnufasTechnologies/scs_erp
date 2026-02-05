@include('includes.header')

<style>
  body {
    background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
  }
</style>
<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / Course Master</span>
      </a>
      <div class="d-flex">
        <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
          <i class="fa fa-step-backward me-1"></i> back
        </a>
      </div>
    </div>
  </nav>


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
            <label for="">Select Master Course</label>
            <select name="courses[]" class="select-multiple" multiple>
              @foreach ($course_master as $course)
              <option value="{{ $course->id }}">{{ $course->course_code }} - {{ $course->course_title }}</option>
              @endforeach
            </select>

            <input type="hidden" name="subject_id" value="{{ $data->id }}">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Button to trigger modal -->
  <button type="button" class="btn btn-light mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <i class="fa fa-plus-circle"></i> From Existing Course Master
  </button>


  <div class="container-fluid">
    <div class="row">
      <div class="card">


        <div class="table-responsive">
          <table class="table table-bordered table-striped bg-white rounded shadow-sm">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Credits</th>
                <th>Semester</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($mycourses as $course)
              <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $course->courseMaster->course_code ?? '-' }}</td>
                <td>{{ $course->courseMaster->course_title ?? '-' }}</td>
                <td>{{ $course->courseMaster->credits ?? '-' }}</td>
                <td>{{ $course->courseMaster->semester ?? '-' }}</td>
                <td>
                  <!-- Example action buttons -->

                  <form action="" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?')">Delete</button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">No courses found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>


  @include('includes.footer')
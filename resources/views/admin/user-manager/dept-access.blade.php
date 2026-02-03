<?php

$campusMaster = App\Models\Campus::all();
?>
@include('includes.header')
@include('admin.sidebar')
<h3>Admission | Department Access Control </h3>
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#deptAccessModal">
  Create Departmental Access
</button>

<!-- Department Access Modal -->
<div class="modal fade" id="deptAccessModal" tabindex="-1" aria-labelledby="deptAccessModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('dept.erp.grant-access') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deptAccessModalLabel">Grant Departmental Access</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">

            <label for="program_group" class="form-label">Select Academic Department </label>
            <select class="form-select dselect-example   mb-3" name="department">
              <option value="">Choose...</option>
              @foreach($departments as $dept)
              <option value="{{ $dept->id }}">{{ $dept->code }} ({{ $dept->campusmaster->name ?? '' }}) - <?php echo ucfirst($dept->title); ?></option>
              @endforeach
            </select>

            <label for="name" class="form-label">Full Name </label>
            <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>

            <label for="email" class="form-label">Login Email address </label>
            <input type="email" name="email" class="form-control mb-3" placeholder="  Email" required>

            <label for="password" class="form-label">Your Login Password </label>
            <input type="password" name="password" class="form-control mb-3" placeholder="Login Password" required>






          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Allow Access</button>
          </div>
        </div>

      </div>

    </form>
  </div>
</div>


@if (count($data))


<div class="row mt-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Departmental Access List</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle" id="exportTable">
            <thead class="table-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th>Status</th>
                <th>Action</th>
                <th scope="col">Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data as $index => $item)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->status }}</td>
                <td>

                  <a href="{{ route('dept.erp.revoke-access', $item->id) }}">
                    <button type="submit" class="btn {{ $item->status == 'ACTIVE' ? 'btn-success' : 'btn-danger' }} btn-sm">{{ $item->status == 'ACTIVE' ? 'Block Access' : 'Allow Access' }}</button></a>

                </td>
                <td>{{ $item->created_at->format('Y-m-d') }}</td>
                <td>{{ $item->updated_at->format('Y-m-d') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@else
<p class="display-4 text-center">No departmental access records found.</p>
@endif

<!-- Make sure jQuery is loaded before this script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    $(".departmentId").change(function() {
      var deptId = $(this).val();
      var campusId = $("#campus").val();
      $("#listedPrograms ul").empty();
      if (deptId == "") {
        $("#listedPrograms").hide();
      } else {
        $("#listedPrograms").show();

        $.ajax({
          type: "get",
          url: "getprogramsbydepartment",
          data: {
            deptId: deptId,
            campusId: campusId,
          },
          success: function(response) {
            // Assuming response is an array of programs
            if (Array.isArray(response) && response.length > 0) {
              response.forEach(function(program) {
                $("#listedPrograms ul").append(
                  "<li>" +
                  program.student_program.code +
                  " - " +
                  program.student_program.name +
                  "</li>"
                );
              });
            } else {
              $("#listedPrograms ul").append("<li>No programs found.</li>");
            }
          },
          error: function() {
            $("#listedPrograms ul").append("<li>Error loading programs.</li>");
          },
        });
      }
    });

    $("#campus").change(function() {

      $("#listedPrograms ul").empty();
    });
  });
</script>
@include('includes.footer')
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
    <form method="POST" action="{{ route('admin.admission.grant-access') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deptAccessModalLabel">Grant Departmental Access</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="program_group" class="form-label">Select Department </label>
            <select class="form-select dselect-example departmentId mb-3" name="department">
              <option value="">Choose...</option>
              @foreach($departments as $dept)
              <option value="{{ $dept->id }}">{{ $dept->department_code }} - {{ $dept->name }}</option>
              @endforeach
            </select>
            <div id="listedPrograms" style="margin-top:15px; display:none;">
              <strong>Programs in this Department:</strong>
              <ul>
              </ul>
            </div>

            <input type="email" name="email" class="form-control mb-3" placeholder="Login Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Login Password" required>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Allow Access</button>
          </div>
        </div>
    </form>
  </div>
</div>
<!-- Make sure jQuery is loaded before this script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {
    $(".departmentId").change(function() {
      var deptId = $(this).val();
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
          },
          success: function(response) {
            // Assuming response is an array of programs
            if (Array.isArray(response) && response.length > 0) {
              response.forEach(function(program) {
                $("#listedPrograms ul").append(
                  "<li>" +
                  program.code +
                  " - " +
                  program.name +
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
  });
</script>
@include('includes.footer')
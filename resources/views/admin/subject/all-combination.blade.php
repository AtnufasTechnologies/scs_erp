@include('includes.header')
@include('admin.sidebar')
<h3>All Combinations</h3>
<div class="row">
  <div class="col-lg-4">
    <div class="mb-3">
      <label for="batchFilter" class="form-label">Filter by Batch:</label>
      <select id="batchFilter" class="form-control dselect-example">
        <option value="">All Batches</option>
        @foreach ($combinations->groupBy('batchmaster.batch_name') as $batch => $items)
        <option value="{{ $batch }}">{{ $batch }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <!-- <div class="col-lg-4">
    <div class="mb-3">
      <label for="departmentFilter" class="form-label">Filter by Department:</label>
      <select id="departmentFilter" class="form-control dselect-example">
        <option value="">All Departments</option>
        @foreach ($combinations->groupBy('studentprograminfo.departmentmaster.name') as $department => $items)
        @if ($department)
        <option value="{{ $department }}">{{ $department }}</option>
        @endif
        @endforeach
      </select>
    </div>
  </div> -->
</div>


<script>
  document.getElementById('batchFilter').addEventListener('change', function() {
    const batch = this.value;
    document.querySelectorAll('#exportTable tbody tr').forEach(row => {
      row.style.display = (!batch || row.cells[1].textContent.trim() === batch) ? '' : 'none';
    });
  });
</script>



<script>
  document.getElementById('batchFilter').addEventListener('change', function() {
    filterTable();
  });

  document.getElementById('departmentFilter').addEventListener('change', function() {
    filterTable();
  });

  function filterTable() {
    const batch = document.getElementById('batchFilter').value;
    const department = document.getElementById('departmentFilter').value;
    document.querySelectorAll('#exportTable tbody tr').forEach(row => {
      const rowBatch = row.cells[1].textContent.trim();
      const rowDepartment = row.cells[5].textContent.trim();
      const batchMatch = !batch || rowBatch === batch;
      const departmentMatch = !department || rowDepartment === department;
      row.style.display = (batchMatch && departmentMatch) ? '' : 'none';
    });
  }
</script>
<table class="table table-bordered" id="exportTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Batch</th>
      <th>Campus</th>
      <th> Code</th>
      <th> Name</th>
      <th>Program Type</th>
      <th>Degree</th>
      <th>Semester Count</th>
      <th>Created </th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($combinations as $combination)
    <tr>
      <td>{{ $combination->id }}</td>
      <td>{{ $combination->batchmaster->batch_name ?? '' }}</td>
      <td>{{ $combination->campusmaster->name ?? '' }}</td>
      <td>{{ $combination->studentprograminfo->code ?? '' }}</td>
      <td>{{ $combination->studentprograminfo->name ?? '' }}</td>
      <td>{{$combination->program_type}}</td>
      <td>{{ $combination->studentprograminfo->degree ?? '' }}</td>
      <td>{{ $combination->studentprograminfo->semester_count ?? '' }}</td>
      <td>{{ $combination->created_at }}</td>
      <td>
        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $combination->id }}" title="Edit">
          <i class="fa fa-edit"></i>
        </button>
      </td>
      <td>
        <a href="{{route('admin.delete.combination',$combination->id)}}" id="citadel">
          <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
        </a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<!-- Edit Modals -->
@foreach ($combinations as $combination)
<div class="modal fade" id="editModal{{ $combination->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Edit Combination - {{ $combination->subjectmaster->title ?? 'N/A' }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.update.combination', $combination->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Subject/Department</label>
                <input type="text" class="form-control" value="{{ $combination->subjectmaster->title ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Program</label>
                <input type="text" class="form-control" value="{{ $combination->studentprograminfo->name ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Campus</label>
                <input type="text" class="form-control" value="{{ $combination->campusmaster->name ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Batch</label>
                <input type="text" class="form-control" value="{{ $combination->batchmaster->batch_name ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Program Type</label>
                <select name="program_type" class="form-control">
                  <option value="">--Select--</option>
                  <option value="UG" {{ $combination->program_type == 'UG' ? 'selected' : '' }}>UG</option>
                  <option value="PG" {{ $combination->program_type == 'PG' ? 'selected' : '' }}>PG</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Total Seats *</label>
                <input type="number" name="total_seats" class="form-control" value="{{ $combination->total_seats }}" min="0" required>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group mb-3">
                <label>Available Seats *</label>
                <input type="number" name="total_available_seats" class="form-control" value="{{ $combination->total_available_seats }}" min="0" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">Update Combination</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@include('includes.footer')
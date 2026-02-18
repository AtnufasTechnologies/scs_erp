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
      <th>Department</th>
      <th>Program Type</th>
      <th>Degree</th>
      <th>Semester Count</th>
      <th>Created </th>
      <th>Action</th>
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
      <td>{{ $combination->subjectmaster->title ?? '' }}</td>
      <td>{{$combination->program_type}}</td>
      <td>{{ $combination->studentprograminfo->degree ?? '' }}</td>
      <td>{{ $combination->studentprograminfo->semester_count ?? '' }}</td>
      <td>{{ $combination->created_at }}</td>
      <td><a href="{{route('admin.delete.combination',$combination->id)}}" id="citadel"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a></td>
    </tr>
    @endforeach
  </tbody>
</table>

@include('includes.footer')
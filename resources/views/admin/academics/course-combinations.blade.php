@include('includes.header')
@include('admin.sidebar')
<h3>Course Combinations</h3>
<table class="table table-bordered" id="exportTable">
  <thead>
    <tr>
      <th>#</th>
      <th>Code</th>
      <th>Name</th>
      <th>Description</th>
      <th>Department Code</th>
      <th>Department</th>
      <th>Campus</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($data as $dt)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $dt->code ?? 'N/A' }}</td>
      <td>{{ $dt->name ?? 'N/A' }}</td>
      <td>{{ $dt->description ?? 'N/A' }}</td>
      <td>{{ $dt->departmentmaster->department_code ?? 'N/A' }}</td>
      <td>{{ $dt->departmentmaster->name ?? 'N/A' }}</td>
      <td>{{ $dt->departmentmaster?->campusmaster?->name ?? 'N/A' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@include('includes.footer')
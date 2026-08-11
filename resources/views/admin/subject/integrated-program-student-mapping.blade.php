@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">Integrated Program Student Mapping</h4>
      <p class="text-muted mb-0">
        {{ optional($combination->studentprograminfo)->code }} - {{ optional($combination->studentprograminfo)->name }}
        | Batch: {{ optional($combination->batchmaster)->batch_name }}
      </p>
    </div>
    <a href="{{ route('department.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
  </div>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Students and mapped sublayer programs</span>
      <span class="badge bg-primary">Total: {{ collect($rows)->count() }}</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Roll No</th>
              <th>Register No</th>
              <th>Student</th>
              <th>Status</th>
              <th>Mapped Sublayer Program</th>
              <th>Mapped Combination</th>
              <th>Mapped On</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $row)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $row['roll_no'] }}</td>
              <td>{{ $row['register_no'] }}</td>
              <td>{{ $row['student_name'] }}</td>
              <td>
                @if($row['status'] === 'Mapped')
                <span class="badge bg-success">Mapped</span>
                @else
                <span class="badge bg-secondary">Not Mapped</span>
                @endif
              </td>
              <td>{{ $row['mapped_program'] ?? '-' }}</td>
              <td>{{ $row['mapped_combination'] ?? '-' }}</td>
              <td>{{ !empty($row['mapped_on']) ? \Carbon\Carbon::parse($row['mapped_on'])->format('d M Y h:i A') : '-' }}</td>
              <td>{{ !empty($row['remarks']) ? $row['remarks'] : '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">No students found for this integrated program and batch.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
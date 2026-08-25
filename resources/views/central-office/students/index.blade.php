@include('includes.header')
@include('central-office.sidebar')

<div class="container-fluid">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Central Office</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('central-office.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Student List</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('central-office.students.index') }}" class="row g-2">
          <div class="col-md-3">
            <label class="form-label">Batch</label>
            <select class="form-select" name="batch_id">
              <option value="0">All Batches</option>
              @foreach($batches as $batchItem)
              <option value="{{ $batchItem->id }}" {{ (int) $batchId === (int) $batchItem->id ? 'selected' : '' }}>{{ $batchItem->batch_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
              <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
              <option value="left" {{ $status === 'left' ? 'selected' : '' }}>Left</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Name / Roll / Register / Mobile">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Apply</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Students</h6>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('central-office.students.export', ['batch_id' => $batchId, 'status' => $status, 'search' => $search]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-excel me-1"></i>Export Excel
          </a>
          @if((int) $batchId <= 0)
            <span class="text-muted small">Select a batch to export.</span>
            @endif
            <span class="badge bg-info">{{ $students->total() }} records</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Roll No</th>
                <th>Name</th>
                <th>Register No</th>
                <th>Batch</th>
                <th>Program</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($students as $index => $student)
              <tr>
                <td>{{ $students->firstItem() + $index }}</td>
                <td>{{ $student->roll_no ?: 'N/A' }}</td>
                <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}</td>
                <td>{{ $student->register_no ?: 'N/A' }}</td>
                <td>{{ $student->batchmaster?->batch_name ?? 'N/A' }}</td>
                <td>{{ $student->stdprogramenrolled?->code ? ($student->stdprogramenrolled->code . ' - ' . $student->stdprogramenrolled->name) : 'N/A' }}</td>
                <td>
                  @if((int) ($student->is_left ?? 0) === 1)
                  <span class="badge bg-danger">Left</span>
                  @else
                  <span class="badge bg-success">Active</span>
                  @endif
                </td>
                <td>
                  @if((int) ($student->is_left ?? 0) === 1)
                  <form action="{{ route('central-office.students.reactivate', $student->id) }}" method="POST" onsubmit="return confirm('Reactivate this student?');">
                    @csrf
                    <button class="btn btn-sm btn-success" type="submit">Reactivate</button>
                  </form>
                  @else
                  <form action="{{ route('central-office.students.mark-left', $student->id) }}" method="POST" onsubmit="return confirm('Mark this student as left?');">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit">Set Left</button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center py-4">No students found for the selected filters.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white">
        {{ $students->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
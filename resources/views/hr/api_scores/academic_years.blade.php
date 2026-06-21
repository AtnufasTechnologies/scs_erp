@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Academic Years Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-scores.index') }}">API Scores</a></li>
            <li class="breadcrumb-item active">Academic Years</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add Academic Year</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('hr.api-scores.academic-years.store') }}" method="POST">
              @csrf
              <div class="row">
                <div class="col-md-3 mb-3">
                  <label class="form-label">Year Name <span class="text-danger">*</span></label>
                  <input type="text" name="year_name" class="form-control @error('year_name') is-invalid @enderror"
                    placeholder="e.g., 2026-2027" value="{{ old('year_name') }}" required>
                  @error('year_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Start Date <span class="text-danger">*</span></label>
                  <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                    value="{{ old('start_date') }}" required>
                  @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">End Date <span class="text-danger">*</span></label>
                  <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                    value="{{ old('end_date') }}" required>
                  @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">Status <span class="text-danger">*</span></label>
                  <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                  </select>
                  @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-save"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Academic Years List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Year Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Faculty Scores</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($years as $year)
                  <tr>
                    <td><strong>{{ $year->year_name }}</strong></td>
                    <td>{{ $year->start_date->format('d M Y') }}</td>
                    <td>{{ $year->end_date->format('d M Y') }}</td>
                    <td>
                      @if($year->status == 'active')
                      <span class="badge bg-success">Active</span>
                      @else
                      <span class="badge bg-secondary">Closed</span>
                      @endif
                    </td>
                    <td>{{ $year->facultyScores->count() }}</td>
                    <td>{{ $year->created_at->format('d M Y') }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="text-center">No academic years found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            {{ $years->links() }}
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
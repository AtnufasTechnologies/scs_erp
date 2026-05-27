@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FDP Programs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.index') }}">FDP List</a></li>
            <li class="breadcrumb-item active">Faculty Tracker</li>
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

    <div class="card">
      <div class="card-header bg-transparent">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Faculty FDP Tracker</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.fdp.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-arrow-left me-1"></i>Back to FDP List
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        {{-- Search --}}
        <form method="GET" action="{{ route('hr.fdp.faculty-tracker') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-6">
              <input type="text" name="search" class="form-control"
                placeholder="Search by faculty name or code..."
                value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
            @if($search)
            <div class="col-md-2">
              <a href="{{ route('hr.fdp.faculty-tracker') }}" class="btn btn-outline-secondary w-100">
                <i class="fas fa-times me-1"></i>Clear
              </a>
            </div>
            @endif
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Faculty Name</th>
                <th>Employee Code</th>
                <th>Total Programs</th>
                <th>Completed</th>
                <th>Certificates</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($faculties as $i => $item)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                  <strong>{{ $item['faculty']->FIRST_NAME }} {{ $item['faculty']->LAST_NAME }}</strong>
                </td>
                <td><span class="badge bg-light text-dark">{{ $item['faculty']->USER_CODE ?? $item['faculty']->FACULTY_CODE ?? 'N/A' }}</span></td>
                <td>
                  <span class="badge bg-info">{{ $item['total_programs'] }}</span>
                </td>
                <td>
                  <span class="badge bg-success">{{ $item['completed'] }}</span>
                </td>
                <td>
                  @if($item['certificates'] > 0)
                  <span class="badge bg-warning text-dark">
                    <i class="fas fa-certificate me-1"></i>{{ $item['certificates'] }}
                  </span>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('hr.fdp.faculty-tracker', ['faculty_id' => $item['faculty']->id]) }}"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>View Details
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="fas fa-users fa-2x mb-2 d-block"></i>No faculty members found.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
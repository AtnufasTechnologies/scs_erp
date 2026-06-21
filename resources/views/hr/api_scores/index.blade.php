@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Score Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">API Scores</li>
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
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Faculty API Scores</h5>
              <div>
                <a href="{{ route('hr.api-scores.reports') }}" class="btn btn-info btn-sm me-2">
                  <i class="fas fa-chart-bar me-1"></i>Reports
                </a>
                <a href="{{ route('hr.api-scores.academic-years') }}" class="btn btn-secondary btn-sm me-2">
                  <i class="fas fa-calendar me-1"></i>Academic Years
                </a>
                <a href="{{ route('hr.api-scores.create') }}" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus me-1"></i>Add API Score
                </a>
              </div>
            </div>

            <form method="GET" class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label">Academic Year</label>
                <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                  <option value="">All Years</option>
                  @foreach($academicYears as $year)
                  <option value="{{ $year->id }}" {{ $selectedYear == $year->id ? 'selected' : '' }}>
                    {{ $year->year_name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                  <option value="">All Status</option>
                  <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                  <option value="verified" {{ $status == 'verified' ? 'selected' : '' }}>Verified</option>
                  <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Faculty name or code" value="{{ $search }}">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-search me-1"></i>Filter
                </button>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Faculty Code</th>
                    <th>Faculty Name</th>
                    <th>Academic Year</th>
                    <th>Total Score</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($scores as $score)
                  <tr>
                    <td>{{ $score->faculty->USER_CODE }}</td>
                    <td>{{ $score->faculty->FIRST_NAME }} {{ $score->faculty->LAST_NAME }}</td>
                    <td>{{ $score->academicYear->year_name }}</td>
                    <td>
                      <strong class="text-primary">{{ number_format($score->total_score, 2) }}</strong> / 100
                    </td>
                    <td>
                      @if($score->status == 'draft')
                      <span class="badge bg-secondary">Draft</span>
                      @elseif($score->status == 'submitted')
                      <span class="badge bg-warning">Submitted</span>
                      @elseif($score->status == 'verified')
                      <span class="badge bg-info">Verified</span>
                      @elseif($score->status == 'approved')
                      <span class="badge bg-success">Approved</span>
                      @endif
                    </td>
                    <td>{{ $score->submitted_at ? $score->submitted_at->format('d M Y') : '-' }}</td>
                    <td>
                      <a href="{{ route('hr.api-scores.show', $score->id) }}" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('hr.api-scores.edit', $score->id) }}" class="btn btn-sm btn-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($score->status == 'draft')
                      <form method="POST" action="{{ route('hr.api-scores.submit', $score->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" title="Submit">
                          <i class="fas fa-paper-plane"></i>
                        </button>
                      </form>
                      @endif
                      @if($score->status == 'submitted')
                      <form method="POST" action="{{ route('hr.api-scores.verify', $score->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning" title="Verify">
                          <i class="fas fa-check-circle"></i>
                        </button>
                      </form>
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="text-center">No API scores found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            {{ $scores->links() }}
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
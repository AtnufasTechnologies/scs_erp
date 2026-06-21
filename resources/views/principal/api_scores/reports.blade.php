@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Score Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">API Score Reports</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="GET" class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Select Academic Year</label>
                <select name="academic_year_id" class="form-select" onchange="this.form.submit()">
                  <option value="">Select Year</option>
                  @foreach($academicYears as $year)
                  <option value="{{ $year->id }}" {{ $selectedYear == $year->id ? 'selected' : '' }}>
                    {{ $year->year_name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    @if($selectedYear && !empty($stats))
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-0">Total Faculties</h6>
            <h3 class="mb-0 mt-2">{{ $stats['total_faculties'] }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-0">Average Score</h6>
            <h3 class="mb-0 mt-2">{{ number_format($stats['average_score'], 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-0">Highest Score</h6>
            <h3 class="mb-0 mt-2 text-success">{{ number_format($stats['highest_score'], 2) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-0">Lowest Score</h6>
            <h3 class="mb-0 mt-2 text-danger">{{ number_format($stats['lowest_score'], 2) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Top 10 Performers</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Rank</th>
                    <th>Faculty Code</th>
                    <th>Faculty Name</th>
                    <th>Total Score</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($topScorers as $index => $scorer)
                  <tr>
                    <td><strong>{{ $index + 1 }}</strong></td>
                    <td>{{ $scorer->faculty->USER_CODE }}</td>
                    <td>{{ $scorer->faculty->FIRST_NAME }} {{ $scorer->faculty->LAST_NAME }}</td>
                    <td><strong class="text-primary">{{ number_format($scorer->total_score, 2) }}</strong></td>
                    <td>
                      @if($scorer->status == 'final')
                      <span class="badge bg-success">Final</span>
                      @else
                      <span class="badge bg-warning">Draft</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('principal.api-scores.faculty-report', $scorer->faculty_id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-chart-line"></i> View Details
                      </a>
                    </td>
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
    <div class="alert alert-info">
      <i class="fas fa-info-circle me-2"></i>Please select an academic year to view reports
    </div>
    @endif
  </main>
</div>

@include('includes.footer')
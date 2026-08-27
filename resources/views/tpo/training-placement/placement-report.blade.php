@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Placement Report</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Selected Students</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i>Placement Selection Report</h4>
              <p class="text-muted mb-0">Insights and full selected-students dataset for job placements.</p>
            </div>
          </div>

          <form method="GET" action="{{ route('tpo.training-placement.placement-report.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label fw-semibold mb-1">Search</label>
              <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Student, roll, register, job title or company">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold mb-1">Job Description</label>
              <select name="placement_id" class="form-select">
                <option value="">All Jobs</option>
                @foreach(($placementsForFilter ?? collect()) as $placementFilter)
                <option value="{{ $placementFilter->id }}" {{ (int) ($placementId ?? 0) === (int) $placementFilter->id ? 'selected' : '' }}>
                  {{ $placementFilter->title }} @if(!empty($placementFilter->company_name)) ({{ $placementFilter->company_name }}) @endif
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Filter</button>
              @if(!empty($search) || !empty($placementId))
              <a href="{{ route('tpo.training-placement.placement-report.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Total Selected Records</div>
              <h4 class="mb-0 fw-bold">{{ (int) ($insights['total_selected_records'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Unique Selected Students</div>
              <h4 class="mb-0 fw-bold text-success">{{ (int) ($insights['unique_selected_students'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Unique Selected Jobs</div>
              <h4 class="mb-0 fw-bold text-primary">{{ (int) ($insights['unique_selected_jobs'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Companies Hiring</div>
              <h4 class="mb-0 fw-bold text-info">{{ (int) ($insights['unique_companies'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">Selections By Job</div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                  <thead>
                    <tr>
                      <th class="px-3">Job</th>
                      <th class="text-end pe-3">Selected</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse(($insights['selection_by_job'] ?? collect()) as $row)
                    <tr>
                      <td class="px-3">{{ $row['title'] }}</td>
                      <td class="text-end pe-3">{{ (int) $row['count'] }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="2" class="text-center text-muted py-3">No selected records.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">Selections By Company</div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                  <thead>
                    <tr>
                      <th class="px-3">Company</th>
                      <th class="text-end pe-3">Selected</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse(($insights['selection_by_company'] ?? collect()) as $row)
                    <tr>
                      <td class="px-3">{{ $row['company'] }}</td>
                      <td class="text-end pe-3">{{ (int) $row['count'] }}</td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="2" class="text-center text-muted py-3">No selected records.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Selected Students Data</div>
        <div class="card-body p-0">
          @if(($selectedApplications ?? collect())->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Selected On</th>
                  <th>Student</th>
                  <th>Roll / Register</th>
                  <th>Job / Company</th>
                  <th>Campus / Year</th>
                  <th>Status</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                @foreach($selectedApplications as $application)
                <tr>
                  <td>{{ optional($application->updated_at)->format('d M Y h:i A') ?: optional($application->applied_at)->format('d M Y h:i A') }}</td>
                  <td>
                    <div class="fw-semibold">{{ trim((string) (($application->student->first_name ?? '') . ' ' . ($application->student->last_name ?? ''))) ?: 'N/A' }}</div>
                    <div class="small text-muted">{{ $application->student->mail_id ?? 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="small">Roll: {{ $application->student->roll_no ?? 'N/A' }}</div>
                    <div class="small text-muted">Reg: {{ $application->student->register_no ?? 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $application->placement->title ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $application->placement->company_name ?? 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="small">{{ $application->student->campusmaster->name ?? 'N/A' }}</div>
                    <div class="small text-muted">Year {{ $application->student->current_year ?? 'N/A' }}</div>
                  </td>
                  <td>
                    <span class="badge bg-success text-uppercase">{{ str_replace('_', ' ', (string) ($application->status ?? 'selected')) }}</span>
                  </td>
                  <td>
                    <div class="small">{{ $application->remarks ?: 'N/A' }}</div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="p-3">
            {{ $selectedApplications->links() }}
          </div>
          @else
          <div class="p-4 text-muted">No selected student records found for the current filters.</div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
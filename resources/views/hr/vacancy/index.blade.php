@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Recruitment</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Vacancies</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

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
            <h5 class="mb-0">Job Vacancies</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.vacancy.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Post Vacancy
            </a>
            <a href="{{ route('vacancies.public.index') }}" target="_blank" class="btn btn-outline-info">
              <i class="fas fa-external-link-alt me-1"></i>View Public Page
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('hr.vacancy.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-4">
              <input type="text" name="search" class="form-control" placeholder="Search vacancy code or position" value="{{ $search }}">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ $status == 'published' ? 'selected' : '' }}>Published</option>
                <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                <option value="filled" {{ $status == 'filled' ? 'selected' : '' }}>Filled</option>
                <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="recruitment_type" class="form-select">
                <option value="">All Recruitment Types</option>
                <option value="regular" {{ $recruitmentType == 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="adhoc" {{ $recruitmentType == 'adhoc' ? 'selected' : '' }}>Ad-hoc</option>
                <option value="contractual" {{ $recruitmentType == 'contractual' ? 'selected' : '' }}>Contractual</option>
                <option value="guest" {{ $recruitmentType == 'guest' ? 'selected' : '' }}>Guest</option>
                <option value="visiting" {{ $recruitmentType == 'visiting' ? 'selected' : '' }}>Visiting</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
          </div>
        </form>

        <!-- Vacancies Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Code</th>
                <th>Position</th>
                <th>Type</th>
                <th>Applications</th>
                <th>Closing Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($vacancies as $vacancy)
              <tr>
                <td><strong>{{ $vacancy->vacancy_code }}</strong></td>
                <td>
                  {{ $vacancy->position_title }}<br>
                  <small class="text-muted">{{ $vacancy->department->subject_name ?? 'General' }}</small>
                </td>
                <td>
                  <span class="badge bg-{{ $vacancy->recruitment_type_badge }}">
                    {{ ucfirst($vacancy->recruitment_type) }}
                  </span>
                </td>
                <td>
                  <strong>{{ $vacancy->applications()->count() }}</strong> applications<br>
                  <small class="text-muted">{{ $vacancy->number_of_positions }} position(s)</small>
                </td>
                <td>{{ $vacancy->application_end_date->format('d M Y') }}</td>
                <td>
                  <span class="badge bg-{{ $vacancy->status_badge }}">
                    {{ ucfirst($vacancy->status) }}
                  </span>
                  @if($vacancy->publish_to_website)
                  <br><small class="badge bg-info mt-1">Public</small>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('hr.vacancy.show', $vacancy->id) }}" class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('hr.vacancy.edit', $vacancy->id) }}" class="btn btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    @if($vacancy->status == 'published')
                    <a href="{{ route('hr.vacancy.applications', $vacancy->id) }}" class="btn btn-outline-success" title="Applications">
                      <i class="fas fa-users"></i>
                    </a>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No vacancies found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $vacancies->links() }}
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
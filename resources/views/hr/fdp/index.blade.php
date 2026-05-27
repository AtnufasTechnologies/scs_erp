@include('includes.header')

<div class="wrapper">
  @include('includes.hr-sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FDP Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">FDP Programs</li>
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
            <h5 class="mb-0">Faculty Development Programs</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.fdp.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Create FDP
            </a>
            <a href="{{ route('hr.fdp.faculty-tracker') }}" class="btn btn-outline-info">
              <i class="fas fa-chart-line me-1"></i>Faculty Tracker
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('hr.fdp.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-4">
              <input type="text" name="search" class="form-control" placeholder="Search program title or code" value="{{ $search }}">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="open" {{ $status == 'open' ? 'selected' : '' }}>Open</option>
                <option value="ongoing" {{ $status == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="workshop" {{ $type == 'workshop' ? 'selected' : '' }}>Workshop</option>
                <option value="seminar" {{ $type == 'seminar' ? 'selected' : '' }}>Seminar</option>
                <option value="conference" {{ $type == 'conference' ? 'selected' : '' }}>Conference</option>
                <option value="training" {{ $type == 'training' ? 'selected' : '' }}>Training</option>
                <option value="certification" {{ $type == 'certification' ? 'selected' : '' }}>Certification</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
          </div>
        </form>

        <!-- FDP Programs Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Code</th>
                <th>Program Title</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Participants</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($fdpPrograms as $fdp)
              <tr>
                <td><strong>{{ $fdp->program_code }}</strong></td>
                <td>
                  {{ $fdp->program_title }}<br>
                  <small class="text-muted">{{ $fdp->start_date->format('d M Y') }} - {{ $fdp->end_date->format('d M Y') }}</small>
                </td>
                <td>
                  <span class="badge bg-info">{{ ucfirst($fdp->program_type) }}</span>
                </td>
                <td>{{ $fdp->duration_days }} days</td>
                <td>
                  <strong>{{ $fdp->approvedParticipants()->count() }}</strong>
                  @if($fdp->max_participants)
                  / {{ $fdp->max_participants }}
                  @endif
                </td>
                <td>
                  <span class="badge bg-{{ $fdp->status_badge }}">
                    {{ ucfirst($fdp->status) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('hr.fdp.show', $fdp->id) }}" class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('hr.fdp.edit', $fdp->id) }}" class="btn btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No FDP programs found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $fdpPrograms->links() }}
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
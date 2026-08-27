@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Training & Placement Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">T&P Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="fw-bold mb-1"><i class="fas fa-briefcase me-2 text-primary"></i>Training & Placement Office</h4>
              <p class="text-muted mb-0">Track module health, completion trends, and upcoming placement activities.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.index') }}" class="btn btn-primary">
                <i class="fas fa-chalkboard-teacher me-1"></i>Manage Training
              </a>
              <a href="{{ route('tpo.training-placement.job-description.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-briefcase me-1"></i>Manage Job Description
              </a>
              <a href="{{ route('tpo.training-placement.companies.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-building me-1"></i>Companies
              </a>
              <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-inbox me-1"></i>Inbox
              </a>
              <a href="{{ route('tpo.training-placement.analytics') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-line me-1"></i>Analytics
              </a>
              <a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-user-check me-1"></i>Student Opt-Ins
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted mb-1">Total Trainings</p>
              <h3 class="fw-bold mb-1">{{ $totalTrainings }}</h3>
              <small class="text-success">Active: {{ $activeTrainings }}</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted mb-1">Placement Opportunities</p>
              <h3 class="fw-bold mb-1">{{ $totalPlacements }}</h3>
              <small class="text-success">Active: {{ $activePlacements }}</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted mb-1">Training Resources</p>
              <h3 class="fw-bold mb-1">{{ $totalResources }}</h3>
              <small class="text-muted">Survey Questions: {{ $totalSurveyQuestions }}</small>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted mb-1">Overall Completion</p>
              <h3 class="fw-bold mb-1">{{ $overallCompletionRate }}%</h3>
              <small class="text-muted">Across all role-assigned trainings</small>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">My Training Snapshot</h6>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-2">
                <span>Assigned</span>
                <strong>{{ $myAssignedCount }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Completed</span>
                <strong class="text-success">{{ $myCompletedCount }}</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Pending</span>
                <strong class="text-warning">{{ $myPendingCount }}</strong>
              </div>
              <div class="mt-3">
                <a href="{{ route('tpo.training-placement.index') }}#my-training-pane" class="btn btn-sm btn-outline-primary">Go To My Trainings</a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">Recent Trainings</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table mb-0">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th>Applicable Roles</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($recentTrainings as $training)
                    <tr>
                      <td>{{ $training->title }}</td>
                      <td>{{ $training->targetRoles->pluck('role_name')->map(fn($role) => ucfirst(str_replace('-', ' ', $role)))->implode(', ') }}</td>
                      <td>
                        <span class="badge {{ $training->is_active ? 'bg-success' : 'bg-secondary' }}">
                          {{ $training->is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted py-3">No trainings found.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
          <h6 class="mb-0 fw-bold">Upcoming Placement Drives</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Opportunity</th>
                  <th>Company</th>
                  <th>Drive Date</th>
                  <th>Apply Deadline</th>
                </tr>
              </thead>
              <tbody>
                @forelse($upcomingPlacements as $placement)
                <tr>
                  <td>{{ $placement->title }}</td>
                  <td>{{ $placement->company_name ?: 'N/A' }}</td>
                  <td>{{ $placement->drive_date ? $placement->drive_date->format('d M Y') : 'N/A' }}</td>
                  <td>{{ $placement->apply_deadline ? $placement->apply_deadline->format('d M Y') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">No upcoming placement drives.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
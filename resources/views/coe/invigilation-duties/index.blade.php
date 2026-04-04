@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Invigilation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Invigilation Duties</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-chalkboard-teacher me-2"></i>Invigilation Duties</h3>
                  <p class="text-muted mb-0">Manage and track faculty invigilation duty assignments</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.invigilation-duties.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>Assign Duty
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-clipboard-list text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Duties</p>
                  <h4 class="mb-0 fw-bold">{{ $totalDuties }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-user-clock text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Assigned</p>
                  <h4 class="mb-0 fw-bold">{{ $assignedCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Completed</p>
                  <h4 class="mb-0 fw-bold">{{ $completedCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-calendar-day text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Today's Duties</p>
                  <h4 class="mb-0 fw-bold">{{ $todayCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.invigilation-duties.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label fw-semibold">Date</label>
              <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Faculty</label>
              <select name="faculty_id" class="form-select">
                <option value="">All Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                  {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Exam</label>
              <select name="exam_id" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                  {{ $exam->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Session</label>
              <select name="session" class="form-select">
                <option value="">All</option>
                <option value="morning" {{ request('session') === 'morning' ? 'selected' : '' }}>Morning</option>
                <option value="afternoon" {{ request('session') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                <option value="evening" {{ request('session') === 'evening' ? 'selected' : '' }}>Evening</option>
              </select>
            </div>
            <div class="col-md-1">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.invigilation-duties.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Duties Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Faculty</th>
                  <th>Exam</th>
                  <th>Room</th>
                  <th>Date</th>
                  <th>Session</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($duties as $duty)
                <tr>
                  <td>{{ $loop->iteration + ($duties->currentPage() - 1) * $duties->perPage() }}</td>
                  <td>
                    <div class="fw-semibold">{{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}</div>
                    <small class="text-muted">{{ $duty->faculty->DEPARTMENT ?? '' }}</small>
                  </td>
                  <td>{{ $duty->exam->name ?? 'N/A' }}</td>
                  <td>{{ $duty->room->name ?? 'N/A' }}</td>
                  <td>{{ \Carbon\Carbon::parse($duty->date)->format('d M Y') }}</td>
                  <td><span class="badge bg-info text-capitalize">{{ $duty->session }}</span></td>
                  <td><span class="text-capitalize">{{ str_replace('_', ' ', $duty->role) }}</span></td>
                  <td>
                    @if($duty->status === 'assigned')
                    <span class="badge bg-warning">Assigned</span>
                    @elseif($duty->status === 'completed')
                    <span class="badge bg-success">Completed</span>
                    @else
                    <span class="badge bg-secondary text-capitalize">{{ $duty->status }}</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <a href="{{ route('admin.invigilation-duties.show', $duty->id) }}" class="btn btn-sm btn-outline-info" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('admin.invigilation-duties.edit', $duty->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($duty->status === 'assigned')
                      <form action="{{ route('admin.invigilation-duties.mark-completed', $duty->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Completed" onclick="return confirm('Mark this duty as completed?')">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      @endif
                      <form action="{{ route('admin.invigilation-duties.destroy', $duty->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this duty?')">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No invigilation duties found.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($duties->hasPages())
        <div class="card-footer bg-white">
          <div class="d-flex justify-content-center">
            {{ $duties->appends(request()->query())->links() }}
          </div>
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
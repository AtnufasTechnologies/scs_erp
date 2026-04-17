@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Moderation</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Moderation Duties</li>
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
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-check-double me-2"></i>Moderation Duties</h3>
                  <p class="text-muted mb-0">Manage marks moderation assignments</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.moderation-duties.compare') }}" class="btn btn-warning me-2">
                    <i class="fas fa-balance-scale me-2"></i>Compare Marks
                  </a>
                  <a href="{{ route('admin.moderation-duties.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Assign Duty
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-tasks text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total Duties</div>
                  <div class="fs-4 fw-bold">{{ $totalDuties }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-clock text-warning fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Pending</div>
                  <div class="fs-4 fw-bold">{{ $pendingCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Completed</div>
                  <div class="fs-4 fw-bold">{{ $completedCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.moderation-duties.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('admin.moderation-duties.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Duties Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Faculty</th>
                  <th>Subject</th>
                  <th>Exam</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Assigned</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($duties as $duty)
                <tr>
                  <td class="ps-4">{{ $duty->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}</div>
                    <small class="text-muted">{{ $duty->faculty->DEPARTMENT ?? '' }}</small>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $duty->subject->subject_code ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $duty->subject->name ?? '' }}</small>
                  </td>
                  <td>{{ $duty->exam->name ?? 'N/A' }}</td>
                  <td>
                    <span class="badge {{ $duty->moderation_type == 'internal' ? 'bg-info' : 'bg-secondary' }}">
                      {{ ucfirst($duty->moderation_type) }}
                    </span>
                  </td>
                  <td>
                    @if($duty->status == 'completed')
                    <span class="badge bg-success">Completed</span>
                    @elseif($duty->status == 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($duty->status) }}</span>
                    @endif
                  </td>
                  <td>{{ $duty->created_at->format('d M Y') }}</td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('admin.moderation-duties.show', $duty->id) }}" class="btn btn-outline-primary" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('admin.moderation-duties.edit', $duty->id) }}" class="btn btn-outline-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($duty->status != 'completed')
                      <form action="{{ route('admin.moderation-duties.mark-completed', $duty->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" title="Mark Complete">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      @endif
                      <form action="{{ route('admin.moderation-duties.destroy', $duty->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this duty?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No moderation duties found
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($duties->hasPages())
        <div class="card-footer">
          {{ $duties->withQueryString()->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
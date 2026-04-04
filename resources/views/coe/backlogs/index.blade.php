@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Backlogs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">All Backlogs</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-redo me-2"></i>Backlog Management</h3>
                  <p class="text-muted mb-0">Track and manage student backlog registrations with attempt history</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('coe.backlogs.failed-subjects') }}" class="btn btn-warning me-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>Failed Subjects
                  </a>
                  <a href="{{ route('coe.backlogs.report') }}" class="btn btn-info">
                    <i class="fas fa-chart-bar me-2"></i>Report
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

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-list-ol text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total Backlogs</div>
                  <div class="fs-4 fw-bold">{{ $totalBacklogs }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
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
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-pen-square text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Registered</div>
                  <div class="fs-4 fw-bold">{{ $registeredCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Cleared</div>
                  <div class="fs-4 fw-bold">{{ $clearedCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.backlogs.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Session</label>
              <select name="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} (Sem {{ $session->semester }})
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
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                <option value="cleared" {{ request('status') == 'cleared' ? 'selected' : '' }}>Cleared</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Enrollment No</label>
              <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('coe.backlogs.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Backlogs Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student</th>
                  <th>Subject</th>
                  <th>Exam</th>
                  <th>Session</th>
                  <th class="text-center">Attempt</th>
                  <th class="text-center">Prev Marks</th>
                  <th class="text-center">Prev Grade</th>
                  <th>Status</th>
                  <th>Registered</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($backlogs as $backlog)
                <tr>
                  <td class="ps-4">{{ $backlog->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $backlog->student->enrollment_no ?? 'N/A' }}</div>
                    @if($backlog->student)
                    <small class="text-muted">ID: {{ $backlog->student->erp_student_id }}</small>
                    @endif
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $backlog->subject->subject_code ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $backlog->subject->name ?? '' }}</small>
                  </td>
                  <td>{{ $backlog->exam->name ?? 'N/A' }}</td>
                  <td>
                    @if($backlog->examSession)
                    <div>{{ $backlog->examSession->name }}</div>
                    <small class="text-muted">Sem {{ $backlog->examSession->semester }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="badge bg-{{ $backlog->attempt_number > 2 ? 'danger' : ($backlog->attempt_number > 1 ? 'warning text-dark' : 'secondary') }} rounded-pill">
                      {{ $backlog->attempt_number ?? 1 }}
                    </span>
                  </td>
                  <td class="text-center">{{ $backlog->previous_marks !== null ? number_format($backlog->previous_marks, 2) : '—' }}</td>
                  <td class="text-center">
                    @if($backlog->previous_grade)
                    <span class="badge {{ $backlog->previous_grade == 'F' ? 'bg-danger' : ($backlog->previous_grade == 'Ab' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                      {{ $backlog->previous_grade }}
                    </span>
                    @else
                    —
                    @endif
                  </td>
                  <td>
                    @if($backlog->status == 'cleared')
                    <span class="badge bg-success">Cleared</span>
                    @elseif($backlog->status == 'registered')
                    <span class="badge bg-info">Registered</span>
                    @else
                    <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                  </td>
                  <td>{{ $backlog->registered_at ? $backlog->registered_at->format('d M Y') : '—' }}</td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('coe.backlogs.show', $backlog->id) }}" class="btn btn-outline-primary" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      @if($backlog->status !== 'cleared')
                      <form method="POST" action="{{ route('coe.backlogs.destroy', $backlog->id) }}" class="d-inline"
                        onsubmit="return confirm('Delete this backlog?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="11" class="text-center py-5">
                    <div class="text-muted">
                      <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                      <p>No backlogs found. Check <a href="{{ route('coe.backlogs.failed-subjects') }}">failed subjects</a> to register backlogs.</p>
                    </div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($backlogs->hasPages())
          <div class="card-footer bg-white">
            {{ $backlogs->withQueryString()->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
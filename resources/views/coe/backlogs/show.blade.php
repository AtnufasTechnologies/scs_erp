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
            <li class="breadcrumb-item"><a href="{{ route('coe.backlogs.index') }}">Backlogs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Backlog #{{ $backlog->id }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
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

      <div class="row">
        <!-- Backlog Details -->
        <div class="col-lg-8">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header gradient-coe border-0">
              <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-redo me-2"></i>Backlog Details</h5>
            </div>
            <div class="card-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <h6 class="text-muted text-uppercase small mb-2">Student Information</h6>
                  <table class="table table-borderless table-sm mb-0">
                    <tr>
                      <td class="fw-semibold text-muted" style="width:40%">Enrollment No</td>
                      <td>{{ $backlog->student->enrollment_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">ERP Student ID</td>
                      <td>{{ $backlog->student->erp_student_id ?? 'N/A' }}</td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted text-uppercase small mb-2">Subject Information</h6>
                  <table class="table table-borderless table-sm mb-0">
                    <tr>
                      <td class="fw-semibold text-muted" style="width:40%">Subject Code</td>
                      <td>{{ $backlog->subject->subject_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Subject Name</td>
                      <td>{{ $backlog->subject->name ?? 'N/A' }}</td>
                    </tr>
                  </table>
                </div>
                <div class="col-12">
                  <hr class="my-0">
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted text-uppercase small mb-2">Exam Details</h6>
                  <table class="table table-borderless table-sm mb-0">
                    <tr>
                      <td class="fw-semibold text-muted" style="width:40%">Exam</td>
                      <td>{{ $backlog->exam->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Session</td>
                      <td>
                        @if($backlog->examSession)
                        {{ $backlog->examSession->name }} (Sem {{ $backlog->examSession->semester }})
                        @else
                        N/A
                        @endif
                      </td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted text-uppercase small mb-2">Backlog Status</h6>
                  <table class="table table-borderless table-sm mb-0">
                    <tr>
                      <td class="fw-semibold text-muted" style="width:40%">Status</td>
                      <td>
                        @if($backlog->status == 'cleared')
                        <span class="badge bg-success fs-6">Cleared</span>
                        @elseif($backlog->status == 'registered')
                        <span class="badge bg-info fs-6">Registered</span>
                        @else
                        <span class="badge bg-warning text-dark fs-6">Pending</span>
                        @endif
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Attempt #</td>
                      <td>
                        <span class="badge bg-{{ ($backlog->attempt_number ?? 1) > 2 ? 'danger' : (($backlog->attempt_number ?? 1) > 1 ? 'warning text-dark' : 'secondary') }} rounded-pill fs-6">
                          {{ $backlog->attempt_number ?? 1 }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Previous Marks</td>
                      <td>{{ $backlog->previous_marks !== null ? number_format($backlog->previous_marks, 2) : '—' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Previous Grade</td>
                      <td>
                        @if($backlog->previous_grade)
                        <span class="badge {{ $backlog->previous_grade == 'F' ? 'bg-danger' : ($backlog->previous_grade == 'Ab' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                          {{ $backlog->previous_grade }}
                        </span>
                        @else
                        —
                        @endif
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Registered</td>
                      <td>{{ $backlog->registered_at ? $backlog->registered_at->format('d M Y, h:i A') : '—' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold text-muted">Cleared</td>
                      <td>{{ $backlog->cleared_at ? $backlog->cleared_at->format('d M Y, h:i A') : '—' }}</td>
                    </tr>
                    @if($backlog->remarks)
                    <tr>
                      <td class="fw-semibold text-muted">Remarks</td>
                      <td>{{ $backlog->remarks }}</td>
                    </tr>
                    @endif
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Attempt History -->
          <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-0">
              <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Attempt History</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-4">Attempt</th>
                      <th>Exam</th>
                      <th>Session</th>
                      <th class="text-center">Prev Marks</th>
                      <th class="text-center">Prev Grade</th>
                      <th>Status</th>
                      <th>Registered</th>
                      <th>Cleared</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($attemptHistory as $attempt)
                    <tr class="{{ $attempt->id == $backlog->id ? 'table-active' : '' }}">
                      <td class="ps-4">
                        <span class="badge bg-{{ $attempt->attempt_number > 2 ? 'danger' : ($attempt->attempt_number > 1 ? 'warning text-dark' : 'secondary') }} rounded-pill">
                          {{ $attempt->attempt_number ?? 1 }}
                        </span>
                        @if($attempt->id == $backlog->id)
                        <small class="text-primary ms-1">(current)</small>
                        @endif
                      </td>
                      <td>{{ $attempt->exam->name ?? 'N/A' }}</td>
                      <td>
                        @if($attempt->examSession)
                        {{ $attempt->examSession->name }}
                        @else
                        N/A
                        @endif
                      </td>
                      <td class="text-center">{{ $attempt->previous_marks !== null ? number_format($attempt->previous_marks, 2) : '—' }}</td>
                      <td class="text-center">
                        @if($attempt->previous_grade)
                        <span class="badge {{ $attempt->previous_grade == 'F' ? 'bg-danger' : ($attempt->previous_grade == 'Ab' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                          {{ $attempt->previous_grade }}
                        </span>
                        @else
                        —
                        @endif
                      </td>
                      <td>
                        @if($attempt->status == 'cleared')
                        <span class="badge bg-success">Cleared</span>
                        @elseif($attempt->status == 'registered')
                        <span class="badge bg-info">Registered</span>
                        @else
                        <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                      </td>
                      <td>{{ $attempt->registered_at ? $attempt->registered_at->format('d M Y') : '—' }}</td>
                      <td>{{ $attempt->cleared_at ? $attempt->cleared_at->format('d M Y') : '—' }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
          <!-- Quick Actions -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light border-0">
              <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Actions</h6>
            </div>
            <div class="card-body">
              @if($backlog->status !== 'cleared')
              <form method="POST" action="{{ route('coe.backlogs.mark-cleared', $backlog->id) }}" class="mb-3">
                @csrf
                <div class="mb-3">
                  <label class="form-label fw-semibold">Remarks (optional)</label>
                  <textarea name="remarks" class="form-control" rows="3" placeholder="Add remarks..."></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this backlog as cleared?')">
                  <i class="fas fa-check-circle me-2"></i>Mark as Cleared
                </button>
              </form>
              <hr>
              <form method="POST" action="{{ route('coe.backlogs.destroy', $backlog->id) }}" onsubmit="return confirm('Delete this backlog?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100">
                  <i class="fas fa-trash me-2"></i>Delete Backlog
                </button>
              </form>
              @else
              <div class="text-center py-3">
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <p class="text-muted mb-0">This backlog has been cleared.</p>
                @if($backlog->cleared_at)
                <small class="text-muted">Cleared on {{ $backlog->cleared_at->format('d M Y, h:i A') }}</small>
                @endif
              </div>
              @endif
            </div>
          </div>

          <!-- Navigation -->
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <a href="{{ route('coe.backlogs.index') }}" class="btn btn-outline-primary w-100 mb-2">
                <i class="fas fa-list me-2"></i>All Backlogs
              </a>
              <a href="{{ route('coe.backlogs.failed-subjects') }}" class="btn btn-outline-warning w-100 mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>Failed Subjects
              </a>
              <a href="{{ route('coe.backlogs.report') }}" class="btn btn-outline-info w-100">
                <i class="fas fa-chart-bar me-2"></i>Report
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
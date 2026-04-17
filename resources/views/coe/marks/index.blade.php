@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Marks Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Marks</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-edit me-2"></i>Marks Management</h3>
                  <p class="text-white-50 mb-0">View and manage marks entries for all exam sessions</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.marks.entry') }}" class="btn btn-light btn-lg me-2">
                    <i class="fas fa-plus-circle me-2"></i>Enter Marks
                  </a>
                  <a href="{{ route('coe.marks.audit-log') }}" class="btn btn-outline-light">
                    <i class="fas fa-history me-2"></i>Audit Log
                  </a>
                  <a href="{{ route('coe.marks.locks') }}" class="btn btn-outline-light ms-1">
                    <i class="fas fa-lock me-2"></i>Locks
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
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-file-alt text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Entries</p>
                  <h4 class="mb-0 fw-bold">{{ $marks->total() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-calendar-alt text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Exam Sessions</p>
                  <h4 class="mb-0 fw-bold">{{ $examSessions->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-book text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Subjects</p>
                  <h4 class="mb-0 fw-bold">{{ $subjects->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.marks.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam Session</label>
              <select name="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($examSessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name ?? 'Session #'.$session->id }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Subject</label>
              <select name="erp_subject_id" class="form-select">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->erp_subject_id }}" {{ request('erp_subject_id') == $subject->erp_subject_id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Search Student</label>
              <input type="text" name="search" class="form-control" placeholder="Name or Roll No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('coe.marks.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Marks Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  <th>Roll No</th>
                  <th>Subject</th>
                  <th>Session</th>
                  <th>Marks</th>
                  <th>Entered By</th>
                  <th>Entered At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($marks as $index => $mark)
                <tr>
                  <td>{{ $marks->firstItem() + $index }}</td>
                  <td>{{ $mark->student->first_name ?? '' }} {{ $mark->student->last_name ?? '' }}</td>
                  <td><span class="badge bg-light text-dark">{{ $mark->student->roll_no ?? 'N/A' }}</span></td>
                  <td>
                    @if($mark->subjectMaster)
                    {{ $mark->subjectMaster->subject_code }} - {{ $mark->subjectMaster->name }}
                    @else
                    Subject #{{ $mark->erp_subject_id }}
                    @endif
                  </td>
                  <td>{{ $mark->examSession->name ?? 'Session #'.$mark->exam_session_id }}</td>
                  <td><span class="badge bg-primary fs-6">{{ $mark->marks }}</span></td>
                  <td>{{ $mark->enteredByUser->name ?? 'N/A' }}</td>
                  <td>{{ $mark->entered_at ? \Carbon\Carbon::parse($mark->entered_at)->format('d M Y, h:i A') : '-' }}</td>
                  <td>
                    <a href="{{ route('coe.marks.show', $mark->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No marks entries found.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($marks->hasPages())
        <div class="card-footer bg-white">
          {{ $marks->withQueryString()->links() }}
        </div>
        @endif
      </div>

    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')
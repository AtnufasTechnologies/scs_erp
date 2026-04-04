@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Marks Locks</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.marks.index') }}">Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Locks</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-lock me-2"></i>Marks Locks Management</h3>
                  <p class="text-white-50 mb-0">View and manage marks lock status for all session/subject combinations</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.marks.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Marks
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

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.marks.locks') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
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
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Locked</option>
                <option value="unlocked" {{ request('status') === 'unlocked' ? 'selected' : '' }}>Unlocked</option>
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('coe.marks.locks') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Locks Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Session</th>
                  <th>Subject</th>
                  <th>Status</th>
                  <th>Locked By</th>
                  <th>Locked At</th>
                  <th>Unlocked By</th>
                  <th>Unlocked At</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($locks as $index => $lock)
                <tr class="{{ $lock->is_locked ? 'table-danger' : '' }}">
                  <td>{{ $locks->firstItem() + $index }}</td>
                  <td>{{ $lock->examSession->name ?? 'Session #'.$lock->exam_session_id }}</td>
                  <td>
                    @if($lock->subjectMaster)
                    {{ $lock->subjectMaster->subject_code }} - {{ $lock->subjectMaster->name }}
                    @else
                    Subject #{{ $lock->erp_subject_id }}
                    @endif
                  </td>
                  <td>
                    @if($lock->is_locked)
                    <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Locked</span>
                    @else
                    <span class="badge bg-success"><i class="fas fa-lock-open me-1"></i>Unlocked</span>
                    @endif
                  </td>
                  <td>{{ $lock->lockedByUser->name ?? 'N/A' }}</td>
                  <td>{{ $lock->locked_at ? $lock->locked_at->format('d M Y, h:i A') : '-' }}</td>
                  <td>{{ $lock->unlockedByUser->name ?? '-' }}</td>
                  <td>{{ $lock->unlocked_at ? $lock->unlocked_at->format('d M Y, h:i A') : '-' }}</td>
                  <td>
                    @if($lock->remarks)
                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $lock->remarks }}">{{ $lock->remarks }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($lock->is_locked)
                    <form method="POST" action="{{ route('coe.marks.unlock') }}" class="d-inline">
                      @csrf
                      <input type="hidden" name="exam_session_id" value="{{ $lock->exam_session_id }}">
                      <input type="hidden" name="erp_subject_id" value="{{ $lock->erp_subject_id }}">
                      <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to unlock marks?')">
                        <i class="fas fa-lock-open me-1"></i>Unlock
                      </button>
                    </form>
                    @else
                    <a href="{{ route('coe.marks.entry', ['exam_session_id' => $lock->exam_session_id, 'erp_subject_id' => $lock->erp_subject_id]) }}" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-pen me-1"></i>Entry
                    </a>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No lock records found.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($locks->hasPages())
        <div class="card-footer bg-white">
          {{ $locks->withQueryString()->links() }}
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
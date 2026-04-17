@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Marks Audit Log</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.marks.index') }}">Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-history me-2"></i>Marks Audit Log</h3>
                  <p class="text-white-50 mb-0">Complete audit trail of all marks entries, updates, and COE overrides</p>
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

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.marks.audit-log') }}" class="row g-3 align-items-end">
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
            <div class="col-md-2">
              <label class="form-label fw-semibold">Action</label>
              <select name="action" class="form-select">
                <option value="">All Actions</option>
                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="coe_override" {{ request('action') === 'coe_override' ? 'selected' : '' }}>COE Override</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Search Student</label>
              <input type="text" name="search" class="form-control" placeholder="Name or Roll..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('coe.marks.audit-log') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Audit Log Table -->
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
                  <th>Old Marks</th>
                  <th>New Marks</th>
                  <th>Action</th>
                  <th>Changed By</th>
                  <th>Remarks</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $index => $log)
                <tr class="{{ $log->action === 'coe_override' ? 'table-warning' : '' }}">
                  <td>{{ $logs->firstItem() + $index }}</td>
                  <td>{{ $log->student->first_name ?? '' }} {{ $log->student->last_name ?? '' }}</td>
                  <td><span class="badge bg-light text-dark">{{ $log->student->roll_no ?? 'N/A' }}</span></td>
                  <td>
                    @if($log->subjectMaster)
                    {{ $log->subjectMaster->subject_code }} - {{ $log->subjectMaster->name }}
                    @else
                    Subject #{{ $log->erp_subject_id }}
                    @endif
                  </td>
                  <td>{{ $log->examSession->name ?? 'Session #'.$log->exam_session_id }}</td>
                  <td>
                    @if($log->old_marks !== null)
                    <span class="badge bg-secondary">{{ $log->old_marks }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td><span class="badge bg-primary">{{ $log->new_marks }}</span></td>
                  <td>
                    @if($log->action === 'created')
                    <span class="badge bg-success">Created</span>
                    @elseif($log->action === 'updated')
                    <span class="badge bg-info">Updated</span>
                    @elseif($log->action === 'coe_override')
                    <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>COE Override</span>
                    @else
                    <span class="badge bg-secondary">{{ $log->action }}</span>
                    @endif
                  </td>
                  <td>{{ $log->changedByUser->name ?? 'N/A' }}</td>
                  <td>
                    @if($log->remarks)
                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $log->remarks }}">{{ $log->remarks }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="11" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No audit log entries found.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white">
          {{ $logs->withQueryString()->links() }}
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
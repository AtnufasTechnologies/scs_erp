@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Evaluation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Evaluation Duties</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-file-alt me-2"></i>Evaluation Duties</h3>
                  <p class="text-dark-50 mb-0">Track answer script evaluation assignments and progress</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.evaluation-duties.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>Assign Copies
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
                  <i class="fas fa-copy text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Copies Assigned</p>
                  <h4 class="mb-0 fw-bold">{{ $totalAssigned }}</h4>
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
                  <i class="fas fa-check-double text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Copies Evaluated</p>
                  <h4 class="mb-0 fw-bold">{{ $totalEvaluated }}</h4>
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
                  <i class="fas fa-tasks text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Overall Progress</p>
                  <h4 class="mb-0 fw-bold">{{ $overallProgress }}%</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overall Progress Bar -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold"><i class="fas fa-chart-bar me-2 text-primary"></i>Overall Evaluation Progress</span>
            <span class="badge bg-primary fs-6">{{ $totalEvaluated }} / {{ $totalAssigned }} copies</span>
          </div>
          <div class="progress" style="height: 24px;">
            <input type="hidden" id="jsOverallProgress" value="{{ $overallProgress }}">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
              style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
              0%
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.evaluation-duties.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
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
              <label class="form-label fw-semibold">Subject</label>
              <select name="subject_id" class="form-select">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
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
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.evaluation-duties.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
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
                  <th>Subject</th>
                  <th>Exam</th>
                  <th>Copies Assigned</th>
                  <th>Copies Evaluated</th>
                  <th style="min-width: 180px;">Progress</th>
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
                  <td>
                    <div class="fw-semibold">{{ $duty->subject->subject_code ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $duty->subject->name ?? '' }}</small>
                  </td>
                  <td>{{ $duty->exam->name ?? 'N/A' }}</td>
                  <td><span class="badge bg-secondary">{{ $duty->copies_assigned }}</span></td>
                  <td><span class="badge bg-primary">{{ $duty->copies_evaluated }}</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="progress flex-grow-1" style="height: 20px;">
                        <input type="hidden" class="jsRowProgress" value="{{ $duty->progress }}">
                        <div class="progress-bar {{ $duty->progress >= 100 ? 'bg-success' : ($duty->progress >= 50 ? 'bg-info' : 'bg-warning') }}"
                          role="progressbar" style="width: 0%"
                          aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                          0%
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($duty->status === 'pending')
                    <span class="badge bg-secondary">Pending</span>
                    @elseif($duty->status === 'in_progress')
                    <span class="badge bg-warning">In Progress</span>
                    @elseif($duty->status === 'completed')
                    <span class="badge bg-success">Completed</span>
                    @else
                    <span class="badge bg-light text-dark text-capitalize">{{ $duty->status }}</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <a href="{{ route('admin.evaluation-duties.show', $duty->id) }}" class="btn btn-sm btn-outline-info" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('admin.evaluation-duties.edit', $duty->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($duty->status !== 'completed')
                      <button type="button" class="btn btn-sm btn-outline-success btnUpdateProgress" title="Update Progress"
                        data-id="{{ $duty->id }}" data-assigned="{{ $duty->copies_assigned }}" data-evaluated="{{ $duty->copies_evaluated }}"
                        data-bs-toggle="modal" data-bs-target="#progressModal">
                        <i class="fas fa-chart-line"></i>
                      </button>
                      <form action="{{ route('admin.evaluation-duties.mark-completed', $duty->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Completed" onclick="return confirm('Mark as completed?')">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      @endif
                      <form action="{{ route('admin.evaluation-duties.destroy', $duty->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this evaluation duty?')">
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
                    No evaluation duties found.
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

<!-- Update Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="progressForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="progressModalLabel"><i class="fas fa-chart-line me-2"></i>Update Evaluation Progress</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Copies Assigned</label>
            <input type="text" class="form-control" id="modalAssigned" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Copies Evaluated</label>
            <input type="number" name="copies_evaluated" class="form-control" id="modalEvaluated" min="0" required>
          </div>
          <div class="progress mt-3" style="height: 24px;">
            <div class="progress-bar bg-success progress-bar-striped" id="modalProgressBar" role="progressbar" style="width: 0%">0%</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Progress</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Animate overall progress bar
    var overallEl = document.getElementById('jsOverallProgress');
    if (overallEl) {
      var overallVal = parseInt(overallEl.value) || 0;
      var overallBar = overallEl.parentElement.querySelector('.progress-bar');
      if (overallBar) {
        setTimeout(function() {
          overallBar.style.width = overallVal + '%';
          overallBar.setAttribute('aria-valuenow', overallVal);
          overallBar.textContent = overallVal + '%';
        }, 300);
      }
    }

    // Animate row progress bars
    var rows = document.querySelectorAll('.jsRowProgress');
    rows.forEach(function(input) {
      var val = parseInt(input.value) || 0;
      var bar = input.parentElement.querySelector('.progress-bar');
      if (bar) {
        setTimeout(function() {
          bar.style.width = val + '%';
          bar.setAttribute('aria-valuenow', val);
          bar.textContent = val + '%';
        }, 300);
      }
    });

    // Update Progress modal
    var buttons = document.querySelectorAll('.btnUpdateProgress');
    buttons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var dutyId = this.getAttribute('data-id');
        var assigned = this.getAttribute('data-assigned');
        var evaluated = this.getAttribute('data-evaluated');
        document.getElementById('progressForm').action = '/erp/admin/evaluation-duties/' + dutyId + '/update-progress';
        document.getElementById('modalAssigned').value = assigned;
        document.getElementById('modalEvaluated').value = evaluated;
        document.getElementById('modalEvaluated').max = assigned;
        updateModalBar(evaluated, assigned);
      });
    });

    var evalInput = document.getElementById('modalEvaluated');
    if (evalInput) {
      evalInput.addEventListener('input', function() {
        var assigned = parseInt(document.getElementById('modalAssigned').value) || 1;
        var evaluated = parseInt(this.value) || 0;
        if (evaluated > assigned) {
          this.value = assigned;
          evaluated = assigned;
        }
        updateModalBar(evaluated, assigned);
      });
    }

    function updateModalBar(evaluated, assigned) {
      var pct = assigned > 0 ? Math.round((evaluated / assigned) * 100) : 0;
      var bar = document.getElementById('modalProgressBar');
      bar.style.width = pct + '%';
      bar.textContent = pct + '%';
    }
  });
</script>
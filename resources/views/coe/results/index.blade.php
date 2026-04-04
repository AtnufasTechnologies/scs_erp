@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Results</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Semester Results</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-trophy me-2"></i>Semester Results</h3>
                  <p class="text-muted mb-0">Manage semester-wise student results with subject breakdown</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exam-results.semester-wise') }}" class="btn btn-info me-2">
                    <i class="fas fa-layer-group me-2"></i>Semester-wise
                  </a>
                  <a href="{{ route('admin.exam-results.generate') }}" class="btn btn-success">
                    <i class="fas fa-cogs me-2"></i>Generate Results
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
                  <div class="text-muted small">Total Results</div>
                  <div class="fs-4 fw-bold">{{ $totalResults }}</div>
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
                  <div class="text-muted small">Published</div>
                  <div class="fs-4 fw-bold">{{ $publishedCount }}</div>
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
                  <i class="fas fa-graduation-cap text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Passed</div>
                  <div class="fs-4 fw-bold">{{ $passCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Promotion Stats -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-arrow-up text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Promoted (Clean)</div>
                  <div class="fs-4 fw-bold">{{ $promotedCount }}</div>
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
                  <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Promoted with Backlogs</div>
                  <div class="fs-4 fw-bold">{{ $promotedWithBacklogsCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-dark bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-pause-circle text-dark fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Withheld</div>
                  <div class="fs-4 fw-bold">{{ $withheldCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-results.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam Session</label>
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
              <select name="result_status" class="form-select">
                <option value="">All</option>
                <option value="pass" {{ request('result_status') == 'pass' ? 'selected' : '' }}>Pass</option>
                <option value="fail" {{ request('result_status') == 'fail' ? 'selected' : '' }}>Fail</option>
                <option value="withheld" {{ request('result_status') == 'withheld' ? 'selected' : '' }}>Withheld</option>
                <option value="pending" {{ request('result_status') == 'pending' ? 'selected' : '' }}>Pending</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Published</label>
              <select name="published" class="form-select">
                <option value="">All</option>
                <option value="yes" {{ request('published') == 'yes' ? 'selected' : '' }}>Published</option>
                <option value="no" {{ request('published') == 'no' ? 'selected' : '' }}>Unpublished</option>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('admin.exam-results.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Publish / Unpublish / Lock Actions -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Session Actions</h5>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Select Session</label>
              <select id="jsActionSessionId" class="form-select">
                <option value="">Select Session</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}"
                  data-locked="{{ in_array($session->id, $resultLocks) ? '1' : '0' }}">
                  {{ $session->name }} (Sem {{ $session->semester }})
                  @if(in_array($session->id, $resultLocks)) 🔒 @endif
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-8">
              <div class="d-flex flex-wrap gap-2">
                {{-- Publish --}}
                <form id="publishForm" method="POST" action="{{ route('admin.exam-results.publish') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" id="jsPublishSessionInput">
                  <button type="submit" class="btn btn-success" id="jsPublishBtn" disabled>
                    <i class="fas fa-globe me-1"></i>Publish All
                  </button>
                </form>

                {{-- Unpublish --}}
                <form id="unpublishForm" method="POST" action="{{ route('admin.exam-results.unpublish') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" id="jsUnpublishSessionInput">
                  <button type="submit" class="btn btn-outline-danger" id="jsUnpublishBtn" disabled>
                    <i class="fas fa-eye-slash me-1"></i>Unpublish All
                  </button>
                </form>

                <div class="vr mx-1"></div>

                {{-- Lock --}}
                <form id="lockForm" method="POST" action="{{ route('admin.exam-results.lock') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" id="jsLockSessionInput">
                  <input type="hidden" name="remarks" id="jsLockRemarks">
                  <button type="button" class="btn btn-dark" id="jsLockBtn" disabled onclick="confirmLock()">
                    <i class="fas fa-lock me-1"></i>Lock Results
                  </button>
                </form>

                {{-- Unlock --}}
                <form id="unlockForm" method="POST" action="{{ route('admin.exam-results.unlock') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" id="jsUnlockSessionInput">
                  <button type="submit" class="btn btn-outline-warning" id="jsUnlockBtn" disabled
                    onclick="return confirm('Are you sure you want to unlock results for this session?')">
                    <i class="fas fa-unlock me-1"></i>Unlock Results
                  </button>
                </form>
              </div>
              <div id="jsLockStatus" class="mt-2" style="display:none;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Results Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student</th>
                  <th>Session / Semester</th>
                  <th>Exam</th>
                  <th>Subjects</th>
                  <th>SGPA</th>
                  <th>CGPA</th>
                  <th>Credits</th>
                  <th>Status</th>
                  <th>Published</th>
                  <th>Promotion</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($results as $result)
                <tr>
                  <td class="ps-4">{{ $result->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $result->student->enrollment_no ?? 'N/A' }}</div>
                    @if($result->student && $result->student->erp_student_id)
                    <small class="text-muted">ID: {{ $result->student->erp_student_id }}</small>
                    @endif
                  </td>
                  <td>
                    @if($result->examSession)
                    <div class="fw-semibold">{{ $result->examSession->name }}</div>
                    <small class="text-muted">Semester {{ $result->examSession->semester }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>{{ $result->exam->name ?? 'N/A' }}</td>
                  <td>
                    <span class="badge bg-secondary">{{ $result->resultSubjects->count() }} subjects</span>
                  </td>
                  <td>
                    <span class="fw-bold {{ $result->sgpa >= 7 ? 'text-success' : ($result->sgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                      {{ number_format($result->sgpa, 2) }}
                    </span>
                  </td>
                  <td>
                    @if($result->cgpa)
                    <span class="fw-bold {{ $result->cgpa >= 7 ? 'text-success' : ($result->cgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                      {{ number_format($result->cgpa, 2) }}
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($result->earned_credits)
                    <span class="badge bg-info">{{ $result->earned_credits }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($result->result_status == 'pass')
                    <span class="badge bg-success">Pass</span>
                    @elseif($result->result_status == 'fail')
                    <span class="badge bg-danger">Fail</span>
                    @elseif($result->result_status == 'withheld')
                    <span class="badge bg-dark">Withheld</span>
                    @else
                    <span class="badge bg-warning text-dark">{{ ucfirst($result->result_status) }}</span>
                    @endif
                  </td>
                  <td>
                    @if($result->is_published)
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Yes</span>
                    @else
                    <span class="badge bg-secondary">No</span>
                    @endif
                    @if(in_array($result->exam_session_id, $resultLocks))
                    <span class="badge bg-dark ms-1" title="Session Locked"><i class="fas fa-lock"></i></span>
                    @endif
                  </td>
                  <td>
                    @if($result->student && $result->student->promotion_status === 'promoted')
                    <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Promoted</span>
                    <small class="d-block text-muted">Sem {{ $result->student->current_semester }}</small>
                    @elseif($result->student && $result->student->promotion_status === 'promoted_with_backlogs')
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Promoted with Backlogs</span>
                    <small class="d-block text-muted">Sem {{ $result->student->current_semester }}</small>
                    @elseif($result->student && $result->student->promotion_status === 'withheld')
                    <span class="badge bg-dark"><i class="fas fa-pause me-1"></i>Withheld</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('admin.exam-results.show', $result->id) }}" class="btn btn-outline-primary" title="View Details">
                        <i class="fas fa-eye"></i>
                      </a>
                      @if(!$result->is_published && !in_array($result->exam_session_id, $resultLocks))
                      <form method="POST" action="{{ route('admin.exam-results.destroy', $result->id) }}" class="d-inline"
                        onsubmit="return confirm('Delete this result?')">
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
                  <td colspan="13" class="text-center py-5">
                    <div class="text-muted">
                      <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                      <p>No results found. <a href="{{ route('admin.exam-results.generate') }}">Generate results</a> for an exam session.</p>
                    </div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($results->hasPages())
          <div class="card-footer bg-white">
            {{ $results->withQueryString()->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var sessionSelect = document.getElementById('jsActionSessionId');
    var publishInput = document.getElementById('jsPublishSessionInput');
    var unpublishInput = document.getElementById('jsUnpublishSessionInput');
    var lockInput = document.getElementById('jsLockSessionInput');
    var unlockInput = document.getElementById('jsUnlockSessionInput');
    var publishBtn = document.getElementById('jsPublishBtn');
    var unpublishBtn = document.getElementById('jsUnpublishBtn');
    var lockBtn = document.getElementById('jsLockBtn');
    var unlockBtn = document.getElementById('jsUnlockBtn');
    var lockStatus = document.getElementById('jsLockStatus');

    sessionSelect.addEventListener('change', function() {
      var val = this.value;
      var opt = this.options[this.selectedIndex];
      var isLocked = opt.getAttribute('data-locked') === '1';

      publishInput.value = val;
      unpublishInput.value = val;
      lockInput.value = val;
      unlockInput.value = val;

      publishBtn.disabled = !val;
      unpublishBtn.disabled = !val || isLocked;
      lockBtn.disabled = !val || isLocked;
      unlockBtn.disabled = !val || !isLocked;

      if (val && isLocked) {
        lockStatus.innerHTML = '<span class="badge bg-dark fs-6"><i class="fas fa-lock me-1"></i>This session is LOCKED — results cannot be modified or unpublished</span>';
        lockStatus.style.display = 'block';
      } else if (val) {
        lockStatus.innerHTML = '<span class="badge bg-light text-dark fs-6"><i class="fas fa-unlock me-1"></i>This session is unlocked</span>';
        lockStatus.style.display = 'block';
      } else {
        lockStatus.style.display = 'none';
      }
    });
  });

  function confirmLock() {
    var remarks = prompt('Enter optional remarks for locking:');
    if (remarks !== null) {
      document.getElementById('jsLockRemarks').value = remarks;
      document.getElementById('lockForm').submit();
    }
  }
</script>
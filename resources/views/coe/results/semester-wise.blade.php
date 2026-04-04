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
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index') }}">Results</a></li>
            <li class="breadcrumb-item active" aria-current="page">Semester-wise Results</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-layer-group me-2"></i>Semester-wise Results</h3>
                  <p class="text-muted mb-0">View results grouped by semester with subject-wise analysis</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exam-results.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Results
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

      <!-- Session Selector -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-results.semester-wise') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Exam Session / Semester</label>
              <select name="exam_session_id" class="form-select form-select-lg" onchange="this.form.submit()">
                <option value="">Select Exam Session</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} — Semester {{ $session->semester }} ({{ $session->academic_year }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              @if($selectedSession)
              <div class="d-flex gap-2 align-items-center">
                @if($lock && $lock->is_locked)
                <span class="badge bg-dark fs-6 px-3 py-2"><i class="fas fa-lock me-1"></i>Locked</span>
                @else
                <span class="badge bg-light text-dark fs-6 px-3 py-2"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                @endif

                {{-- Lock / Unlock --}}
                @if(!$lock || !$lock->is_locked)
                <form method="POST" action="{{ route('admin.exam-results.lock') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
                  <button type="submit" class="btn btn-dark btn-sm" onclick="this.form.remarks.value = prompt('Remarks for locking:') || ''; return true;">
                    <input type="hidden" name="remarks" value="">
                    <i class="fas fa-lock me-1"></i>Lock
                  </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.exam-results.unlock') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
                  <button type="submit" class="btn btn-outline-warning btn-sm"
                    onclick="return confirm('Unlock results for this session?')">
                    <i class="fas fa-unlock me-1"></i>Unlock
                  </button>
                </form>
                @endif

                {{-- Publish / Unpublish --}}
                <form method="POST" action="{{ route('admin.exam-results.publish') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
                  <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-globe me-1"></i>Publish All
                  </button>
                </form>

                @if(!$lock || !$lock->is_locked)
                <form method="POST" action="{{ route('admin.exam-results.unpublish') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
                  <button type="submit" class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Unpublish all results for this session?')">
                    <i class="fas fa-eye-slash me-1"></i>Unpublish
                  </button>
                </form>
                @endif
              </div>
              @endif
            </div>
          </form>
        </div>
      </div>

      @if($selectedSession)
      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="fs-2 fw-bold text-primary">{{ $totalStudents }}</div>
              <div class="text-muted small">Total Students</div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="fs-2 fw-bold text-success">{{ $passedStudents }}</div>
              <div class="text-muted small">Passed</div>
              @if($totalStudents > 0)
              <div class="text-muted small">({{ round(($passedStudents / $totalStudents) * 100, 1) }}%)</div>
              @endif
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="fs-2 fw-bold text-danger">{{ $failedStudents }}</div>
              <div class="text-muted small">Failed</div>
              @if($totalStudents > 0)
              <div class="text-muted small">({{ round(($failedStudents / $totalStudents) * 100, 1) }}%)</div>
              @endif
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="fs-2 fw-bold text-dark">{{ $withheldStudents }}</div>
              <div class="text-muted small">Withheld</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subject-wise Summary -->
      @if($subjectSummary->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-info"></i>Subject-wise Summary</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th class="text-center">Total</th>
                  <th class="text-center">Pass</th>
                  <th class="text-center">Fail</th>
                  <th class="text-center">Absent</th>
                  <th class="text-center">Withheld</th>
                  <th class="text-center">Avg Marks</th>
                  <th class="text-center">Pass %</th>
                </tr>
              </thead>
              <tbody>
                @foreach($subjectSummary as $index => $sub)
                <tr>
                  <td class="ps-4">{{ $index + 1 }}</td>
                  <td><span class="fw-semibold">{{ $sub['subject_code'] }}</span></td>
                  <td>{{ $sub['subject_name'] }}</td>
                  <td class="text-center">{{ $sub['total_students'] }}</td>
                  <td class="text-center text-success fw-bold">{{ $sub['pass_count'] }}</td>
                  <td class="text-center text-danger fw-bold">{{ $sub['fail_count'] }}</td>
                  <td class="text-center">
                    @if($sub['absent_count'] > 0)
                    <span class="badge bg-warning text-dark">{{ $sub['absent_count'] }}</span>
                    @else
                    0
                    @endif
                  </td>
                  <td class="text-center">
                    @if($sub['withheld_count'] > 0)
                    <span class="badge bg-dark">{{ $sub['withheld_count'] }}</span>
                    @else
                    0
                    @endif
                  </td>
                  <td class="text-center">{{ number_format($sub['avg_marks'], 2) }}</td>
                  <td class="text-center">
                    <span class="badge {{ $sub['pass_percentage'] >= 70 ? 'bg-success' : ($sub['pass_percentage'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                      {{ $sub['pass_percentage'] }}%
                    </span>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      <!-- Student Results Table -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Student Results</h5>
          <div>
            <input type="text" id="jsStudentSearch" class="form-control form-control-sm" placeholder="Search students..." style="width: 250px;">
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="jsResultsTable">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Enrollment No</th>
                  <th>Subjects</th>
                  <th class="text-center">SGPA</th>
                  <th class="text-center">Percentage</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Published</th>
                  <th class="text-center">Promotion</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($results as $index => $result)
                <tr class="{{ $result->result_status == 'withheld' ? 'table-dark' : ($result->result_status == 'fail' ? 'table-danger bg-opacity-10' : '') }}">
                  <td class="ps-4">{{ $index + 1 }}</td>
                  <td>
                    <div class="fw-semibold">{{ $result->student->enrollment_no ?? 'N/A' }}</div>
                    <small class="text-muted">ID: {{ $result->student->erp_student_id ?? '' }}</small>
                  </td>
                  <td><span class="badge bg-secondary">{{ $result->resultSubjects->count() }}</span></td>
                  <td class="text-center">
                    <span class="fw-bold {{ $result->sgpa >= 7 ? 'text-success' : ($result->sgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                      {{ number_format($result->sgpa, 2) }}
                    </span>
                  </td>
                  <td class="text-center">{{ number_format($result->percentage, 2) }}%</td>
                  <td class="text-center">
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
                  <td class="text-center">
                    @if($result->is_published)
                    <span class="badge bg-success"><i class="fas fa-check"></i></span>
                    @else
                    <span class="badge bg-secondary">No</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($result->student && $result->student->promotion_status === 'promoted')
                    <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Promoted</span>
                    @elseif($result->student && $result->student->promotion_status === 'detained')
                    <span class="badge bg-danger"><i class="fas fa-hand-paper me-1"></i>Detained</span>
                    @elseif($result->student && $result->student->promotion_status === 'withheld')
                    <span class="badge bg-dark">Withheld</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    <a href="{{ route('admin.exam-results.show', $result->id) }}" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-eye me-1"></i>View
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    No results found for this session.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @else
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5 text-muted">
          <i class="fas fa-hand-pointer fa-3x mb-3 d-block"></i>
          <p class="mb-0">Select an exam session above to view semester-wise results.</p>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('jsStudentSearch');
    if (searchInput) {
      searchInput.addEventListener('keyup', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#jsResultsTable tbody tr');
        rows.forEach(function(row) {
          var text = row.textContent.toLowerCase();
          row.style.display = text.includes(filter) ? '' : 'none';
        });
      });
    }
  });
</script>
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
            <li class="breadcrumb-item active" aria-current="page">Failed Subjects</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Failed Subjects</h3>
                  <p class="text-muted mb-0">Select failed subjects to register as backlogs for re-examination</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('coe.backlogs.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Backlogs
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

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.backlogs.failed-subjects') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
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
            <div class="col-md-4">
              <label class="form-label fw-semibold">Enrollment No</label>
              <input type="text" name="search" class="form-control" placeholder="Search enrollment no..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('coe.backlogs.failed-subjects') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Registration Form wraps the table -->
      <form method="POST" action="{{ route('coe.backlogs.register') }}" id="registerForm">
        @csrf

        <!-- Registration Options -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Register for Exam <span class="text-danger">*</span></label>
                <select name="exam_id" class="form-select" required>
                  <option value="">Select Exam</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Session (optional)</label>
                <select name="exam_session_id" class="form-select">
                  <option value="">Select Session</option>
                  @foreach($sessions as $session)
                  <option value="{{ $session->id }}">{{ $session->name }} (Sem {{ $session->semester }})</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-success" id="btnRegister" disabled>
                  <i class="fas fa-plus-circle me-1"></i>Register Selected (<span id="selectedCount">0</span>)
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Failed Subjects Table -->
        <div class="card shadow-sm border-0">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="ps-4">
                      <input type="checkbox" class="form-check-input" id="selectAll">
                    </th>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Session</th>
                    <th class="text-center">Total Marks</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($failedSubjects as $rs)
                  <tr>
                    <td class="ps-4">
                      <input type="checkbox" class="form-check-input subject-checkbox" name="result_subject_ids[]" value="{{ $rs->id }}">
                    </td>
                    <td>
                      @if($rs->result && $rs->result->student)
                      <div class="fw-semibold">{{ $rs->result->student->enrollment_no }}</div>
                      <small class="text-muted">ID: {{ $rs->result->student->erp_student_id }}</small>
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td>
                      @if($rs->subjectMaster)
                      <div class="fw-semibold">{{ $rs->subjectMaster->subject_code }}</div>
                      <small class="text-muted">{{ $rs->subjectMaster->name }}</small>
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td>
                      @if($rs->result && $rs->result->examSession)
                      <div>{{ $rs->result->examSession->name }}</div>
                      <small class="text-muted">Sem {{ $rs->result->examSession->semester }}</small>
                      @else
                      <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td class="text-center">{{ $rs->total_marks !== null ? number_format($rs->total_marks, 2) : '—' }}</td>
                    <td class="text-center">
                      <span class="badge {{ $rs->grade == 'F' ? 'bg-danger' : ($rs->grade == 'Ab' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                        {{ $rs->grade ?? '—' }}
                      </span>
                    </td>
                    <td class="text-center">
                      @if($rs->result_status == 'Absent')
                      <span class="badge bg-warning text-dark">Absent</span>
                      @else
                      <span class="badge bg-danger">Failed</span>
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <div class="text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                        <p>No failed subjects found for the selected filters.</p>
                      </div>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($failedSubjects->hasPages())
            <div class="card-footer bg-white">
              {{ $failedSubjects->withQueryString()->links() }}
            </div>
            @endif
          </div>
        </div>
      </form>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAll');
    var btnRegister = document.getElementById('btnRegister');
    var selectedCountEl = document.getElementById('selectedCount');

    function updateCount() {
      var checked = document.querySelectorAll('.subject-checkbox:checked').length;
      selectedCountEl.textContent = checked;
      btnRegister.disabled = checked === 0;
    }

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.subject-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
          checkboxes[i].checked = selectAll.checked;
        }
        updateCount();
      });
    }

    var checkboxes = document.querySelectorAll('.subject-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].addEventListener('change', updateCount);
    }

    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
      registerForm.addEventListener('submit', function(e) {
        var checked = document.querySelectorAll('.subject-checkbox:checked').length;
        if (checked === 0) {
          e.preventDefault();
          alert('Please select at least one subject.');
          return;
        }
        if (!confirm('Register ' + checked + ' subject(s) as backlog?')) {
          e.preventDefault();
        }
      });
    }
  });
</script>
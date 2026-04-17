@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Marks Entry</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.marks.index') }}">Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Entry</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-pen-fancy me-2"></i>Marks Entry</h3>
                  <p class="text-white-50 mb-0">Enter marks subject-wise for registered students</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.marks.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-list me-2"></i>View All Marks
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

      <!-- Session & Subject Selection -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-filter me-2 text-primary"></i>Select Session & Subject</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('coe.marks.entry') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Exam Session <span class="text-danger">*</span></label>
              <select name="exam_session_id" class="form-select" required>
                <option value="">-- Select Session --</option>
                @foreach($examSessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name ?? 'Session #'.$session->id }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
              <select name="erp_subject_id" class="form-select" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->erp_subject_id }}" {{ request('erp_subject_id') == $subject->erp_subject_id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Load Students</button>
            </div>
          </form>
        </div>
      </div>

      @if($selectedSession && $selectedSubject)
      <!-- Info Banner -->
      <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="row">
          <div class="col-md-3">
            <strong><i class="fas fa-calendar me-1"></i>Session:</strong> {{ $selectedSession->name ?? 'Session #'.$selectedSession->id }}
          </div>
          <div class="col-md-3">
            <strong><i class="fas fa-book me-1"></i>Subject:</strong> {{ $selectedSubject->subject_code }} - {{ $selectedSubject->name }}
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-star me-1"></i>Max Marks:</strong> <span class="badge bg-danger fs-6">{{ $maxMarks }}</span>
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-users me-1"></i>Students:</strong> <span class="badge bg-success fs-6">{{ $students->count() }}</span>
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-lock me-1"></i>Status:</strong>
            @if($isLocked)
            <span class="badge bg-danger fs-6"><i class="fas fa-lock me-1"></i>Locked</span>
            @else
            <span class="badge bg-success fs-6"><i class="fas fa-lock-open me-1"></i>Unlocked</span>
            @endif
          </div>
        </div>
      </div>

      <!-- Lock/Unlock Controls -->
      @if($students->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            @if($isLocked)
            <span class="text-danger fw-semibold"><i class="fas fa-lock me-2"></i>Marks are LOCKED — editing is disabled. Only COE can modify via override.</span>
            @if($marksLock)
            <br><small class="text-muted">Locked by: {{ $marksLock->lockedByUser->name ?? 'N/A' }} on {{ $marksLock->locked_at ? $marksLock->locked_at->format('d M Y, h:i A') : '-' }}</small>
            @if($marksLock->remarks)<br><small class="text-muted">Remarks: {{ $marksLock->remarks }}</small>@endif
            @endif
            @else
            <span class="text-success fw-semibold"><i class="fas fa-lock-open me-2"></i>Marks are UNLOCKED — editing is allowed.</span>
            @endif
          </div>
          <div class="d-flex gap-2">
            @if(!$isLocked)
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#lockModal">
              <i class="fas fa-lock me-2"></i>Lock Marks
            </button>
            @else
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#unlockModal">
              <i class="fas fa-lock-open me-2"></i>Unlock Marks (COE)
            </button>
            @endif
            <a href="{{ route('coe.marks.audit-log', ['exam_session_id' => $selectedSession->id, 'erp_subject_id' => $selectedSubject->erp_subject_id]) }}" class="btn btn-outline-info">
              <i class="fas fa-history me-2"></i>Audit Log
            </a>
          </div>
        </div>
      </div>
      @endif

      <!-- Hidden inputs for JS references -->
      <input type="hidden" id="jsExamSessionId" value="{{ $selectedSession->id }}">
      <input type="hidden" id="jsErpSubjectId" value="{{ $selectedSubject->erp_subject_id }}">
      <input type="hidden" id="jsMaxMarks" value="{{ $maxMarks ?? 100 }}">

      @if($students->count() > 0)
      <!-- Bulk Entry Form -->
      <form method="POST" action="{{ route('coe.marks.bulk-entry') }}" id="bulkEntryForm">
        @csrf
        <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
        <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject->erp_subject_id }}">

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-table me-2 text-primary"></i>Student Marks List</h5>
            @if(!$isLocked)
            <button type="submit" class="btn btn-success" id="bulkSaveBtn">
              <i class="fas fa-save me-2"></i>Save All Marks
            </button>
            @else
            <span class="badge bg-danger fs-6"><i class="fas fa-lock me-1"></i>Locked — Use COE Override to edit</span>
            @endif
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="marksTable">
                <thead class="table-light">
                  <tr>
                    <th width="5%">#</th>
                    <th width="15%">Roll No</th>
                    <th width="25%">Student Name</th>
                    <th width="15%">Register No</th>
                    <th width="15%">Marks (Max: {{ $maxMarks }})</th>
                    <th width="10%">Status</th>
                    <th width="15%">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($students as $index => $student)
                  @php
                  $existing = $existingMarks->get($student->id);
                  @endphp
                  <tr id="row-{{ $student->id }}" class="{{ $existing ? 'table-success' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td><span class="badge bg-light text-dark">{{ $student->roll_no ?? 'N/A' }}</span></td>
                    <td class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->register_no ?? 'N/A' }}</td>
                    <td>
                      <input type="hidden" name="marks_data[{{ $index }}][erp_student_id]" value="{{ $student->id }}">
                      <input
                        type="number"
                        name="marks_data[{{ $index }}][marks]"
                        class="form-control marks-input"
                        id="marks-{{ $student->id }}"
                        min="0"
                        max="{{ $maxMarks }}"
                        step="0.01"
                        placeholder="0 - {{ $maxMarks }}"
                        value="{{ $existing ? $existing->marks : '' }}"
                        data-student-id="{{ $student->id }}"
                        data-max="{{ $maxMarks }}"
                        {{ $isLocked ? 'disabled' : '' }}>
                      <div class="invalid-feedback" id="error-{{ $student->id }}"></div>
                    </td>
                    <td>
                      <span class="badge {{ $existing ? 'bg-success' : 'bg-secondary' }}" id="status-{{ $student->id }}">
                        {{ $existing ? 'Saved' : 'Pending' }}
                      </span>
                    </td>
                    <td>
                      @if($isLocked)
                      <button type="button" class="btn btn-sm btn-warning coe-override-btn"
                        data-student-id="{{ $student->id }}"
                        data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                        data-current-marks="{{ $existing ? $existing->marks : '' }}"
                        title="COE Override Edit">
                        <i class="fas fa-user-shield me-1"></i>Override
                      </button>
                      @else
                      <button type="button" class="btn btn-sm btn-primary save-single-btn" data-student-id="{{ $student->id }}" title="Save this row">
                        <i class="fas fa-check me-1"></i>Save
                      </button>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <span class="text-muted">
              <i class="fas fa-info-circle me-1"></i>
              @if($isLocked)
              Marks are locked. Use <strong>COE Override</strong> button to edit individual marks.
              @else
              Click <strong>Save</strong> per row for individual save, or <strong>Save All Marks</strong> for bulk save.
              @endif
            </span>
            @if(!$isLocked)
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-2"></i>Save All Marks
            </button>
            @endif
          </div>
        </div>
      </form>
      @else
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5 text-muted">
          <i class="fas fa-users-slash fa-3x mb-3"></i>
          <h5>No Registered Students</h5>
          <p>No approved registrations found for this session. Please check the exam registration status.</p>
        </div>
      </div>
      @endif

      @endif
    </div>
  </main>
</div>

<!-- Lock Modal -->
@if(isset($selectedSession) && isset($selectedSubject) && !$isLocked)
<div class="modal fade" id="lockModal" tabindex="-1" aria-labelledby="lockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('coe.marks.lock') }}">
        @csrf
        <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
        <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject->erp_subject_id }}">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="lockModalLabel"><i class="fas fa-lock me-2"></i>Lock Marks</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> Locking marks will prevent any further editing. Only COE can unlock or override edit locked marks.
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for locking marks..." maxlength="500"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-lock me-2"></i>Confirm Lock</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- Unlock Modal -->
@if(isset($selectedSession) && isset($selectedSubject) && $isLocked)
<div class="modal fade" id="unlockModal" tabindex="-1" aria-labelledby="unlockModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('coe.marks.unlock') }}">
        @csrf
        <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
        <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject->erp_subject_id }}">
        <div class="modal-header bg-warning">
          <h5 class="modal-title" id="unlockModalLabel"><i class="fas fa-lock-open me-2"></i>Unlock Marks (COE)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Unlocking will allow marks to be edited again by anyone with entry access.
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for unlocking marks..." maxlength="500"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fas fa-lock-open me-2"></i>Confirm Unlock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- COE Override Modal -->
<div class="modal fade" id="coeOverrideModal" tabindex="-1" aria-labelledby="coeOverrideModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="coeOverrideModalLabel"><i class="fas fa-user-shield me-2"></i>COE Override Edit</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <strong>COE Override:</strong> This change will be logged in the audit trail with your remarks.
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Student</label>
          <input type="text" class="form-control" id="coeOverrideStudentName" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Current Marks</label>
          <input type="text" class="form-control" id="coeOverrideOldMarks" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">New Marks <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="coeOverrideNewMarks" min="0" max="{{ $maxMarks }}" step="0.01" required>
          <div class="invalid-feedback" id="coeOverrideError"></div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Reason for Override <span class="text-danger">*</span></label>
          <textarea class="form-control" id="coeOverrideRemarks" rows="3" placeholder="Provide a reason for this override..." required maxlength="500"></textarea>
        </div>
        <input type="hidden" id="coeOverrideStudentId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="coeOverrideSubmitBtn">
          <i class="fas fa-save me-2"></i>Save Override
        </button>
      </div>
    </div>
  </div>
</div>
@endif

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .marks-input {
    width: 120px;
  }

  .marks-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  .marks-input.is-invalid {
    border-color: #dc3545;
  }

  .save-single-btn[disabled] {
    opacity: 0.6;
  }

  tr.save-success {
    animation: flashGreen 0.5s ease;
  }

  @keyframes flashGreen {
    0% {
      background-color: #d4edda;
    }

    100% {
      background-color: transparent;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
      '{{ csrf_token() }}';

    // Client-side max marks validation on input
    document.querySelectorAll('.marks-input').forEach(function(input) {
      input.addEventListener('input', function() {
        const max = parseFloat(this.dataset.max);
        const val = parseFloat(this.value);
        const errorEl = document.getElementById('error-' + this.dataset.studentId);

        if (this.value !== '' && (isNaN(val) || val < 0 || val > max)) {
          this.classList.add('is-invalid');
          errorEl.textContent = 'Marks must be between 0 and ' + max;
        } else {
          this.classList.remove('is-invalid');
          errorEl.textContent = '';
        }
      });
    });

    // Per-row AJAX save
    document.querySelectorAll('.save-single-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const studentId = this.dataset.studentId;
        const input = document.getElementById('marks-' + studentId);
        const max = parseFloat(input.dataset.max);
        const val = parseFloat(input.value);

        if (input.value === '' || isNaN(val)) {
          input.classList.add('is-invalid');
          document.getElementById('error-' + studentId).textContent = 'Please enter marks.';
          return;
        }

        if (val < 0 || val > max) {
          input.classList.add('is-invalid');
          document.getElementById('error-' + studentId).textContent = 'Marks must be between 0 and ' + max;
          return;
        }

        // Disable button during save
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

        fetch('{{ route("coe.marks.store-single") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              exam_session_id: document.getElementById('jsExamSessionId').value,
              erp_student_id: studentId,
              erp_subject_id: document.getElementById('jsErpSubjectId').value,
              marks: val
            })
          })
          .then(function(response) {
            return response.json().then(function(data) {
              return {
                ok: response.ok,
                data: data
              };
            });
          })
          .then(function(result) {
            btn.disabled = false;
            if (result.ok && result.data.success) {
              btn.innerHTML = '<i class="fas fa-check me-1"></i>Save';
              // Update row status
              const statusBadge = document.getElementById('status-' + studentId);
              statusBadge.className = 'badge bg-success';
              statusBadge.textContent = 'Saved';
              // Flash row green
              const row = document.getElementById('row-' + studentId);
              row.classList.add('table-success', 'save-success');
              setTimeout(function() {
                row.classList.remove('save-success');
              }, 600);
              // Clear validation
              input.classList.remove('is-invalid');
              document.getElementById('error-' + studentId).textContent = '';
            } else {
              btn.innerHTML = '<i class="fas fa-check me-1"></i>Save';
              input.classList.add('is-invalid');
              document.getElementById('error-' + studentId).textContent = result.data.message || 'Save failed.';
            }
          })
          .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Save';
            input.classList.add('is-invalid');
            document.getElementById('error-' + studentId).textContent = 'Network error. Please try again.';
          });
      });
    });

    // Bulk form validation
    document.getElementById('bulkEntryForm')?.addEventListener('submit', function(e) {
      let hasError = false;
      document.querySelectorAll('.marks-input').forEach(function(input) {
        const max = parseFloat(input.dataset.max);
        const val = parseFloat(input.value);
        if (input.value !== '' && (isNaN(val) || val < 0 || val > max)) {
          input.classList.add('is-invalid');
          document.getElementById('error-' + input.dataset.studentId).textContent = 'Marks must be between 0 and ' + max;
          hasError = true;
        }
      });
      if (hasError) {
        e.preventDefault();
      }
    });

    // COE Override Modal handler
    document.querySelectorAll('.coe-override-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.getElementById('coeOverrideStudentId').value = this.dataset.studentId;
        document.getElementById('coeOverrideStudentName').value = this.dataset.studentName;
        document.getElementById('coeOverrideOldMarks').value = this.dataset.currentMarks || 'N/A';
        document.getElementById('coeOverrideNewMarks').value = '';
        document.getElementById('coeOverrideRemarks').value = '';
        document.getElementById('coeOverrideError').textContent = '';
        document.getElementById('coeOverrideNewMarks').classList.remove('is-invalid');
        var modal = new bootstrap.Modal(document.getElementById('coeOverrideModal'));
        modal.show();
      });
    });

    // COE Override Submit
    document.getElementById('coeOverrideSubmitBtn')?.addEventListener('click', function() {
      var studentId = document.getElementById('coeOverrideStudentId').value;
      var newMarks = parseFloat(document.getElementById('coeOverrideNewMarks').value);
      var remarks = document.getElementById('coeOverrideRemarks').value.trim();
      var maxMarks = parseFloat(document.getElementById('jsMaxMarks').value);
      var errorEl = document.getElementById('coeOverrideError');
      var marksInput = document.getElementById('coeOverrideNewMarks');

      if (isNaN(newMarks) || newMarks < 0 || newMarks > maxMarks) {
        marksInput.classList.add('is-invalid');
        errorEl.textContent = 'Marks must be between 0 and ' + maxMarks;
        return;
      }

      if (!remarks) {
        alert('Please provide a reason for the override.');
        return;
      }

      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

      fetch('{{ route("coe.marks.coe-override") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            exam_session_id: document.getElementById('jsExamSessionId').value,
            erp_student_id: studentId,
            erp_subject_id: document.getElementById('jsErpSubjectId').value,
            marks: newMarks,
            remarks: remarks
          })
        })
        .then(function(response) {
          return response.json().then(function(data) {
            return {
              ok: response.ok,
              data: data
            };
          });
        })
        .then(function(result) {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Override';
          if (result.ok && result.data.success) {
            // Update row display
            var marksEl = document.getElementById('marks-' + studentId);
            if (marksEl) marksEl.value = newMarks;
            var statusBadge = document.getElementById('status-' + studentId);
            if (statusBadge) {
              statusBadge.className = 'badge bg-info';
              statusBadge.textContent = 'Override';
            }
            var row = document.getElementById('row-' + studentId);
            if (row) {
              row.classList.add('table-warning', 'save-success');
              setTimeout(function() {
                row.classList.remove('save-success');
              }, 600);
            }
            // Update the override button's current-marks data
            var overrideBtn = row?.querySelector('.coe-override-btn');
            if (overrideBtn) overrideBtn.dataset.currentMarks = newMarks;
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('coeOverrideModal'))?.hide();
          } else {
            marksInput.classList.add('is-invalid');
            errorEl.textContent = result.data.message || 'Override failed.';
          }
        })
        .catch(function(err) {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-save me-2"></i>Save Override';
          marksInput.classList.add('is-invalid');
          errorEl.textContent = 'Network error. Please try again.';
        });
    });
  });
</script>

@include('includes.footer')
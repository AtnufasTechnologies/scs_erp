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
          <div class="col-md-4">
            <strong><i class="fas fa-calendar me-1"></i>Session:</strong> {{ $selectedSession->name ?? 'Session #'.$selectedSession->id }}
          </div>
          <div class="col-md-4">
            <strong><i class="fas fa-book me-1"></i>Subject:</strong> {{ $selectedSubject->subject_code }} - {{ $selectedSubject->name }}
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-star me-1"></i>Max Marks:</strong> <span class="badge bg-danger fs-6">{{ $maxMarks }}</span>
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-users me-1"></i>Students:</strong> <span class="badge bg-success fs-6">{{ $students->count() }}</span>
          </div>
        </div>
      </div>

      @if($students->count() > 0)
      <!-- Bulk Entry Form -->
      <form method="POST" action="{{ route('coe.marks.bulk-entry') }}" id="bulkEntryForm">
        @csrf
        <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
        <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject->erp_subject_id }}">

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-table me-2 text-primary"></i>Student Marks List</h5>
            <button type="submit" class="btn btn-success" id="bulkSaveBtn">
              <i class="fas fa-save me-2"></i>Save All Marks
            </button>
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
                        data-max="{{ $maxMarks }}">
                      <div class="invalid-feedback" id="error-{{ $student->id }}"></div>
                    </td>
                    <td>
                      <span class="badge {{ $existing ? 'bg-success' : 'bg-secondary' }}" id="status-{{ $student->id }}">
                        {{ $existing ? 'Saved' : 'Pending' }}
                      </span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-primary save-single-btn" data-student-id="{{ $student->id }}" title="Save this row">
                        <i class="fas fa-check me-1"></i>Save
                      </button>
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
              Click <strong>Save</strong> per row for individual save, or <strong>Save All Marks</strong> for bulk save.
            </span>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-2"></i>Save All Marks
            </button>
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
              exam_session_id: {
                {
                  $selectedSession - > id ?? 'null'
                }
              },
              erp_student_id: studentId,
              erp_subject_id: {
                {
                  $selectedSubject - > erp_subject_id ?? 'null'
                }
              },
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
  });
</script>

@include('includes.footer')
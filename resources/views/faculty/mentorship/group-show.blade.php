@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Mentorship</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.index') }}">My Groups</a></li>
            <li class="breadcrumb-item active">{{ $group->name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif

      <!-- Header -->
      <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
          <h4 class="fw-bold mb-1">{{ $group->name }}
            <span class="badge bg-{{ $group->status === 'active' ? 'success' : 'secondary' }} ms-2 fs-6">{{ ucfirst($group->status) }}</span>
          </h4>
          <p class="text-muted mb-0">{{ $group->description }}</p>
          <small class="text-muted">
            @if($group->academic_year) <i class="bx bx-calendar"></i> {{ $group->academic_year }} &nbsp; @endif
            @if($group->semester) <i class="fa fa-book"></i> Semester {{ $group->semester }} @endif
          </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('faculty.mentorship.group.edit', $group->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-edit"></i> Edit Group
          </a>
          <form id="deleteGroupFormBtn" method="POST" action="{{ route('faculty.mentorship.group.destroy', $group->id) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>
          </form>
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-primary mb-0">{{ $totalStudents }}</div>
            <div class="text-muted small">Students</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-success mb-0">{{ $completedSessions }}/{{ $totalSessions }}</div>
            <div class="text-muted small">Sessions Done</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-warning mb-0">{{ $totalAssignments }}</div>
            <div class="text-muted small">Assignments</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-info mb-0">{{ $group->created_at->format('M Y') }}</div>
            <div class="text-muted small">Created</div>
          </div>
        </div>
      </div>

      <div class="row g-4">

        <!-- ── Students Panel ── -->
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-user-plus text-primary me-2"></i>Students ({{ $totalStudents }})</h6>
              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentsModal">
                <i class="fa fa-plus"></i> Add Students
              </button>
            </div>
            <div class="card-body p-0">
              @if($group->students->isEmpty())
              <p class="text-muted text-center py-4">No students yet. Add students to this group.</p>
              @else
              <div class="list-group list-group-flush">
                @foreach($group->students as $student)
                <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                  <div>
                    <a href="{{ route('faculty.mentorship.student.profile', [$group->id, $student->id]) }}" class="fw-semibold text-decoration-none">
                      {{ $student->first_name }} {{ $student->last_name }}
                    </a>
                    <div class="text-muted small text-uppercase"> Roll: {{ $student->roll_no }}</div>
                  </div>
                  <form method="POST" action="{{ route('faculty.mentorship.students.remove', [$group->id, $student->id]) }}" style="display:inline;" class="js-remove-student-form">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i></button>
                  </form>
                </div>
                @endforeach
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- ── Sessions & Assignments Panel ── -->
        <div class="col-lg-7">

          <!-- Sessions -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-calendar-check text-success me-2"></i>Sessions ({{ $totalSessions }})</h6>
              <a href="{{ route('faculty.mentorship.session.create', $group->id) }}" class="btn btn-sm btn-success">
                <i class="bx bx-plus"></i> New Session
              </a>
            </div>
            <div class="card-body p-0">
              @if($sessions->isEmpty())
              <p class="text-muted text-center py-3">No sessions scheduled yet.</p>
              @else
              <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Title</th>
                      <th>Date</th>
                      <th>Mode</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($sessions as $session)
                    <tr>
                      <td><a href="{{ route('faculty.mentorship.session.show', $session->id) }}" class="fw-semibold text-decoration-none">{{ $session->title }}</a></td>
                      <td>{{ $session->session_date->format('d M Y') }}</td>
                      <td><span class="badge bg-info text-dark">{{ $session->mode }}</span></td>
                      <td>
                        <span class="badge bg-{{ $session->status === 'completed' ? 'success' : ($session->status === 'cancelled' ? 'danger' : 'warning') }}">
                          {{ ucfirst($session->status) }}
                        </span>
                      </td>
                      <td>
                        <a href="{{ route('faculty.mentorship.session.show', $session->id) }}" class="btn btn-xs btn-outline-primary btn-sm">View</a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endif
            </div>
          </div>

          <!-- Assignments -->
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-task text-warning me-2"></i>Assignments ({{ $totalAssignments }})</h6>
              <a href="{{ route('faculty.mentorship.assignment.create', $group->id) }}" class="btn btn-sm btn-warning">
                <i class="fa fa-plus"></i> New Assignment
              </a>
            </div>
            <div class="card-body p-0">
              @if($assignments->isEmpty())
              <p class="text-muted text-center py-3">No assignments yet.</p>
              @else
              <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Title</th>
                      <th>Due</th>
                      <th>Marks</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($assignments as $assignment)
                    <tr>
                      <td><a href="{{ route('faculty.mentorship.assignment.show', $assignment->id) }}" class="fw-semibold text-decoration-none">{{ $assignment->title }}</a></td>
                      <td>{{ $assignment->due_date ? $assignment->due_date->format('d M Y') : '—' }}</td>
                      <td>{{ $assignment->max_marks }}</td>
                      <td><span class="badge bg-{{ $assignment->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span></td>
                      <td>
                        <a href="{{ route('faculty.mentorship.assignment.show', $assignment->id) }}" class="btn btn-xs btn-outline-primary btn-sm">View</a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endif
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>
</div>


<!-- Add Students Modal -->
<div class="modal fade" id="addStudentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Add Students to {{ $group->name }}</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
          <h6 class="mb-0">Available Students</h6>
          <small class="text-muted">Faculty campus students</small>
        </div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-12">
            <input type="text" id="availableStudentSearch" class="form-control" placeholder="Search by name, roll no, register no">
          </div>
          <div class="col-6">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="selectAllAvailableStudents">
              <label class="form-check-label" for="selectAllAvailableStudents">Select All</label>
            </div>
          </div>
          <div class="col-6 text-end">
            <small class="text-muted"><span id="selectedAvailableCount">0</span> selected</small>
          </div>
        </div>

        <div class="border rounded" style="max-height: 380px; overflow-y: auto;">
          @if(($availableStudents ?? collect())->isEmpty())
          <div class="p-3 text-center text-muted small">No available students to add.</div>
          @else
          @foreach($availableStudents as $availableStudent)
          @php
          $searchBlob = strtolower(trim((string) (($availableStudent->first_name ?? '') . ' ' . ($availableStudent->last_name ?? '') . ' ' . ($availableStudent->roll_no ?? '') . ' ' . ($availableStudent->register_no ?? ''))));
          $studentName = trim((string) (($availableStudent->first_name ?? '') . ' ' . ($availableStudent->last_name ?? '')));
          $studentRoll = (string) ($availableStudent->roll_no ?? '');
          $studentReg = (string) ($availableStudent->register_no ?? '');
          @endphp
          <label
            class="d-flex align-items-start gap-2 p-2 border-bottom mb-0 available-student-row"
            data-search="{{ $searchBlob }}"
            data-name="{{ strtolower($studentName) }}"
            data-roll="{{ strtolower($studentRoll) }}"
            data-reg="{{ strtolower($studentReg) }}">
            <input type="checkbox" class="form-check-input mt-1 available-student-checkbox" value="{{ (int) $availableStudent->id }}">
            <span class="small">
              <span class="fw-semibold d-block">{{ $studentName !== '' ? $studentName : 'N/A' }}</span>
              <span class="text-muted text-uppercase">Roll: {{ $availableStudent->roll_no ?? 'N/A' }}</span>
              <span class="text-muted d-block">Reg: {{ $availableStudent->register_no ?? 'N/A' }}</span>
            </span>
          </label>
          @endforeach
          @endif
        </div>

        <div id="bulkAddFeedback" class="small mt-2"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="confirmAddStudents" disabled>Add Selected Students</button>
      </div>
    </div>
  </div>
</div>



<!-- Delete Group Confirmation Modal -->
<div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Delete Group?</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">This will delete all sessions, assignments and notes in this group. This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteGroupForm" method="POST" style="display:inline;">@csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const studentSearch = document.getElementById('availableStudentSearch');
    const selectAll = document.getElementById('selectAllAvailableStudents');
    const checkboxes = Array.from(document.querySelectorAll('.available-student-checkbox'));
    const rows = Array.from(document.querySelectorAll('.available-student-row'));
    const addSelectedBtn = document.getElementById('confirmAddStudents');
    const selectedCountEl = document.getElementById('selectedAvailableCount');
    const bulkAddFeedback = document.getElementById('bulkAddFeedback');
    const addStudentsModalEl = document.getElementById('addStudentsModal');

    const selectedStudentIds = new Set();

    const syncSelectionUi = function() {
      const selectedCount = selectedStudentIds.size;

      if (selectedCountEl) {
        selectedCountEl.textContent = String(selectedCount);
      }

      if (addSelectedBtn) {
        addSelectedBtn.disabled = selectedCount < 1;
      }

      if (selectAll && checkboxes.length > 0) {
        const checkedCount = checkboxes.filter(function(checkbox) {
          return checkbox.checked;
        }).length;
        selectAll.checked = checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
      }
    };

    if (studentSearch) {
      const normalize = function(value) {
        return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
      };

      studentSearch.addEventListener('input', function() {
        const needle = normalize(this.value);
        const compactNeedle = needle.replace(/\s+/g, '');

        rows.forEach(function(row) {
          const searchable = normalize(row.dataset.search || '');
          const name = normalize(row.dataset.name || '');
          const roll = normalize(row.dataset.roll || '');
          const reg = normalize(row.dataset.reg || '');

          const haystacks = [searchable, name, roll, reg];
          const compactHaystacks = haystacks.map(function(item) {
            return item.replace(/\s+/g, '');
          });

          const matched = needle === '' || haystacks.some(function(item) {
            return item.includes(needle);
          }) || compactNeedle !== '' && compactHaystacks.some(function(item) {
            return item.includes(compactNeedle);
          });

          row.classList.toggle('d-none', !matched);
        });
      });
    }

    checkboxes.forEach(function(checkbox) {
      checkbox.addEventListener('change', function() {
        const studentId = Number(checkbox.value);
        if (!studentId) {
          return;
        }

        if (checkbox.checked) {
          selectedStudentIds.add(studentId);
        } else {
          selectedStudentIds.delete(studentId);
        }

        syncSelectionUi();
      });
    });

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        const shouldCheck = !!selectAll.checked;

        checkboxes.forEach(function(checkbox) {
          checkbox.checked = shouldCheck;
          const studentId = Number(checkbox.value);
          if (!studentId) {
            return;
          }

          if (shouldCheck) {
            selectedStudentIds.add(studentId);
          } else {
            selectedStudentIds.delete(studentId);
          }
        });

        syncSelectionUi();
      });
    }

    if (addSelectedBtn) {
      addSelectedBtn.addEventListener('click', function() {
        if (selectedStudentIds.size < 1) {
          return;
        }

        if (bulkAddFeedback) {
          bulkAddFeedback.textContent = '';
          bulkAddFeedback.className = 'small mt-2';
        }

        fetch(`{{ route('faculty.mentorship.students.add', $group->id) }}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              student_ids: Array.from(selectedStudentIds)
            })
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              if (bulkAddFeedback) {
                bulkAddFeedback.textContent = data.message || 'Students added successfully.';
                bulkAddFeedback.className = 'text-success small mt-2';
              }
              setTimeout(() => window.location.reload(), 700);
            } else {
              if (bulkAddFeedback) {
                bulkAddFeedback.textContent = data.message || 'Could not add selected students.';
                bulkAddFeedback.className = 'text-danger small mt-2';
              }
            }
          })
          .catch(() => {
            if (bulkAddFeedback) {
              bulkAddFeedback.textContent = 'Error adding selected students.';
              bulkAddFeedback.className = 'text-danger small mt-2';
            }
          });
      });
    }

    if (addStudentsModalEl) {
      addStudentsModalEl.addEventListener('hidden.bs.modal', function() {
        if (studentSearch) {
          studentSearch.value = '';
        }

        rows.forEach(function(row) {
          row.classList.remove('d-none');
        });

        checkboxes.forEach(function(checkbox) {
          checkbox.checked = false;
        });

        selectedStudentIds.clear();

        if (bulkAddFeedback) {
          bulkAddFeedback.textContent = '';
          bulkAddFeedback.className = 'small mt-2';
        }

        syncSelectionUi();
      });
    }

    const removeStudentForms = Array.from(document.querySelectorAll('.js-remove-student-form'));
    removeStudentForms.forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
          Swal.fire({
            title: 'Remove student?',
            text: 'This student will be removed from this mentorship group.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
          }).then(function(result) {
            if (result.isConfirmed) {
              HTMLFormElement.prototype.submit.call(form);
            }
          });
          return;
        }

        if (confirm('Remove this student from the group?')) {
          HTMLFormElement.prototype.submit.call(form);
        }
      });
    });

    syncSelectionUi();
  });

  function deleteGroup(id) {
    document.getElementById('deleteGroupForm').action = `/faculty/mentorship/groups/${id}`;
    new bootstrap.Modal(document.getElementById('deleteGroupModal')).show();
  }
</script>

@include('includes.footer')
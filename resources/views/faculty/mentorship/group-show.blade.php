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
            @if($group->semester) <i class="bx bx-book"></i> Semester {{ $group->semester }} @endif
          </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="{{ route('faculty.mentorship.group.edit', $group->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-edit"></i> Edit Group
          </a>
          <form id="deleteGroupFormBtn" method="POST" action="{{ route('faculty.mentorship.group.destroy', $group->id) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> Delete</button>
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

            </div>
            <div class="p-3 border-bottom">
              <form id="addByRollForm" class="row g-2 align-items-center">
                <div class="col-8">
                  <input type="text" class="form-control" id="rollNoInput" placeholder="Enter Student Roll No">
                </div>
                <div class="col-4">
                  <button type="submit" class="btn btn-outline-success w-100">Add</button>
                </div>
                <div class="col-12 mt-2">
                  <div id="addByRollFeedback" class="small"></div>
                </div>
              </form>
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
                  <form method="POST" action="{{ route('faculty.mentorship.students.remove', [$group->id, $student->id]) }}" style="display:inline;">
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
                <i class="bx bx-plus"></i> New Assignment
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
    const addByRollForm = document.getElementById('addByRollForm');
    if (addByRollForm) {
      addByRollForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const rollNo = document.getElementById('rollNoInput').value.trim();
        const feedback = document.getElementById('addByRollFeedback');
        feedback.textContent = '';
        if (!rollNo) {
          feedback.textContent = 'Please enter a roll number.';
          feedback.className = 'text-danger small';
          return;
        }
        fetch("{{ route('faculty.mentorship.add-by-roll', $group->id) }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              roll_no: rollNo
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              feedback.textContent = data.message;
              feedback.className = 'text-success small';
              setTimeout(() => window.location.reload(), 1000);
            } else {
              feedback.textContent = data.message || 'Could not add student.';
              feedback.className = 'text-danger small';
            }
          })
          .catch(() => {
            feedback.textContent = 'Error adding student.';
            feedback.className = 'text-danger small';
          });
      });
    }
  });

  let selectedStudentIds = [];

  // Student search
  let searchTimer;
  const studentSearchEl = document.getElementById('studentSearch');
  if (studentSearchEl) studentSearchEl.addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) {
      document.getElementById('studentSearchResults').innerHTML = '';
      return;
    }
    searchTimer = setTimeout(() => {
      fetch(`{{ route('faculty.mentorship.students.search') }}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          const container = document.getElementById('studentSearchResults');
          if (data.length === 0) {
            container.innerHTML = '<div class="list-group-item text-muted">No students found</div>';
            return;
          }
          container.innerHTML = data.map(s => `
          <button type="button" class="list-group-item list-group-item-action"
            onclick="selectStudent(${s.id}, '${s.name}', '${s.register_no}')">
            <strong>${s.name}</strong> <span class="text-muted ms-2">${s.register_no}</span>
            ${selectedStudentIds.includes(s.id) ? '<span class="badge bg-success float-end">Selected</span>' : ''}
          </button>
        `).join('');
        });
    }, 300);
  });

  function selectStudent(id, name, regNo) {
    if (selectedStudentIds.includes(id)) return;
    selectedStudentIds.push(id);
    const pill = document.createElement('span');
    pill.className = 'badge bg-primary d-flex align-items-center gap-1';
    pill.innerHTML = `${name} <button type="button" class="btn-close btn-close-white btn-sm" style="font-size:10px;" onclick="deselectStudent(${id}, this)"></button>`;
    pill.dataset.id = id;
    document.getElementById('selectedStudentList').appendChild(pill);
  }

  function deselectStudent(id, btn) {
    selectedStudentIds = selectedStudentIds.filter(i => i !== id);
    btn.closest('.badge').remove();
  }

  document.getElementById('confirmAddStudents').addEventListener('click', function() {
    if (selectedStudentIds.length === 0) {
      alert('Select at least one student.');
      return;
    }
    fetch(`{{ route('faculty.mentorship.students.add', $group->id) }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          student_ids: selectedStudentIds
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message);
        }
      });
  });

  function removeStudent(gId, sId) {
    if (!confirm('Remove this student from the group?')) return;
    fetch(`/faculty/mentorship/groups/${gId}/students/${sId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) location.reload();
      });
  }

  function deleteGroup(id) {
    document.getElementById('deleteGroupForm').action = `/faculty/mentorship/groups/${id}`;
    new bootstrap.Modal(document.getElementById('deleteGroupModal')).show();
  }
</script>

@include('includes.footer')
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
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.group.show', $group->id) }}">{{ $group->name }}</a></li>
            <li class="breadcrumb-item active">{{ $student->first_name }} {{ $student->last_name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div id="alertBox"></div>

      <!-- Student Header -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex gap-3 align-items-center flex-wrap">
          <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:28px;">
            <i class="bx bx-user text-primary"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0">{{ $student->first_name }} {{ $student->last_name }}</h4>
            <div class="text-muted">Register: {{ $student->register_no }} &nbsp;|&nbsp; Roll: {{ $student->roll_no }}</div>
            <div class="text-muted small">Group: {{ $group->name }}</div>
          </div>
        </div>
      </div>

      <div class="row g-4">

        <!-- Attendance -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-calendar-check text-success me-2"></i>Attendance</h6>
            </div>
            <div class="card-body text-center">
              @php $total = $presentCount + $absentCount; @endphp
              <div class="display-4 fw-bold text-success mb-0">
                {{ $total > 0 ? round(($presentCount / $total) * 100) : 0 }}%
              </div>
              <p class="text-muted">Attendance Rate</p>
              <div class="row text-center mt-3">
                <div class="col-6">
                  <div class="h4 fw-bold text-success">{{ $presentCount }}</div>
                  <div class="text-muted small">Present</div>
                </div>
                <div class="col-6">
                  <div class="h4 fw-bold text-danger">{{ $absentCount }}</div>
                  <div class="text-muted small">Absent</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assignments -->
        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-task text-warning me-2"></i>Assignments</h6>
            </div>
            <div class="card-body p-0">
              @if($submissions->isEmpty())
              <p class="text-muted text-center py-3">No assignments assigned.</p>
              @else
              <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Title</th>
                      <th>Due</th>
                      <th>Status</th>
                      <th>Marks</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($submissions as $sub)
                    <tr>
                      <td><a href="{{ route('faculty.mentorship.assignment.show', $sub->assignment->id) }}" class="text-decoration-none fw-semibold">{{ $sub->assignment->title }}</a></td>
                      <td class="text-muted">{{ $sub->assignment->due_date ? $sub->assignment->due_date->format('d M Y') : '—' }}</td>
                      <td><span class="badge bg-{{ $sub->status === 'graded' ? 'success' : ($sub->status === 'submitted' ? 'info' : 'secondary') }}">{{ ucfirst($sub->status) }}</span></td>
                      <td>{{ $sub->marks_obtained !== null ? $sub->marks_obtained . '/' . $sub->assignment->max_marks : '—' }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
              <h6 class="fw-bold mb-0"><i class="bx bx-note text-info me-2"></i>Mentor Notes</h6>
              <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="bx bx-plus"></i> Add Note
              </button>
            </div>
            <div class="card-body">
              @if($notes->isEmpty())
              <p class="text-muted text-center py-3">No notes recorded yet.</p>
              @else
              <div class="row g-3" id="notesList">
                @foreach($notes as $note)
                <div class="col-md-6" id="note-{{ $note->id }}">
                  <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between mb-2">
                      <span class="badge bg-{{ match($note->category) {
                          'academic' => 'primary',
                          'achievement' => 'success',
                          'concern' => 'danger',
                          'behavioral' => 'warning',
                          'personal' => 'info',
                          default => 'secondary'
                        } }}">{{ ucfirst($note->category) }}</span>
                      <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">{{ $note->noted_on->format('d M Y') }}</small>
                        <button class="btn btn-xs btn-outline-danger btn-sm" onclick="deleteNote({{ $note->id }})">
                          <i class="bx bx-trash"></i>
                        </button>
                      </div>
                    </div>
                    <p class="mb-0">{{ $note->note }}</p>
                  </div>
                </div>
                @endforeach
              </div>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Mentor Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Category</label>
          <select id="noteCategory" class="form-select">
            <option value="general">General</option>
            <option value="academic">Academic</option>
            <option value="behavioral">Behavioral</option>
            <option value="personal">Personal</option>
            <option value="achievement">Achievement</option>
            <option value="concern">Concern</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" id="noteDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Note <span class="text-danger">*</span></label>
          <textarea id="noteText" class="form-control" rows="4" placeholder="Enter your observation..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-info text-white" id="saveNoteBtn">Save Note</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('saveNoteBtn').addEventListener('click', function() {
    const note = document.getElementById('noteText').value.trim();
    if (!note) {
      alert('Please enter a note.');
      return;
    }

    fetch('{{ route("faculty.mentorship.note.store", $group->id) }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          student_id: {
            {
              $student - > id
            }
          },
          note: note,
          category: document.getElementById('noteCategory').value,
          noted_on: document.getElementById('noteDate').value
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
          location.reload();
        } else {
          alert(data.message);
        }
      });
  });

  function deleteNote(id) {
    if (!confirm('Delete this note?')) return;
    fetch(`/faculty/mentorship/notes/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) document.getElementById(`note-${id}`).remove();
      });
  }
</script>

@include('includes.footer')
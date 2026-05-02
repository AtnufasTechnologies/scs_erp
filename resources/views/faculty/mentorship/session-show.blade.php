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
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.group.show', $session->group->id) }}">{{ $session->group->name }}</a></li>
            <li class="breadcrumb-item active">{{ $session->title }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div id="alertBox"></div>

      <!-- Session Details -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="row align-items-start">
            <div class="col-md-8">
              <h4 class="fw-bold mb-1">{{ $session->title }}</h4>
              <p class="text-muted mb-2">{{ $session->group->name }}</p>
              <div class="d-flex gap-3 flex-wrap">
                <span><i class="bx bx-calendar text-primary"></i> {{ $session->session_date->format('d M Y') }}</span>
                @if($session->start_time)<span><i class="bx bx-time text-info"></i> {{ $session->start_time }} – {{ $session->end_time ?? '?' }}</span>@endif
                <span class="badge bg-info text-dark">{{ ucfirst($session->mode) }}</span>
                <span class="badge bg-{{ $session->status === 'completed' ? 'success' : ($session->status === 'cancelled' ? 'danger' : 'warning') }}">
                  {{ ucfirst($session->status) }}
                </span>
              </div>
              @if($session->agenda)
              <div class="mt-3">
                <strong>Agenda:</strong>
                <p class="text-muted mb-0">{{ $session->agenda }}</p>
              </div>
              @endif
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              <button class="btn btn-sm btn-outline-danger" onclick="deleteSession({{ $session->id }})">
                <i class="bx bx-trash"></i> Delete Session
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Summary -->
      <div class="row g-3 mb-4">
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-success mb-0" id="presentCount">{{ $present }}</div>
            <div class="text-muted small">Present</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-danger mb-0" id="absentCount">{{ $absent }}</div>
            <div class="text-muted small">Absent</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-warning mb-0" id="excusedCount">{{ $excused }}</div>
            <div class="text-muted small">Excused</div>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
          <h6 class="fw-bold mb-0"><i class="bx bx-check-circle text-success me-2"></i>Attendance</h6>
        </div>
        <div class="card-body">
          @if($attendances->isEmpty())
          <p class="text-muted text-center py-3">No students in this group.</p>
          @else
          <div class="mb-3">
            <label class="form-label fw-semibold">Session Minutes / Notes</label>
            <textarea id="minutesInput" class="form-control" rows="2"
              placeholder="Notes or minutes from this session...">{{ $session->minutes }}</textarea>
          </div>
          <form method="POST" action="{{ route('faculty.mentorship.session.attendance', $session->id) }}" id="attendanceForm">
            @csrf
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Attendance</th>
                    <th>Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($attendances as $att)
                  <tr id="row-{{ $att->student_id }}">
                    <td class="fw-semibold">{{ $att->student->first_name }} {{ $att->student->last_name }}</td>
                    <td class="text-muted">{{ $att->student->roll_no }}</td>
                    <td>
                      <div class="d-flex gap-3">
                        <div class="form-check">
                          <input class="form-check-input att-radio" type="radio"
                            name="attendance[{{ $att->student_id }}][status]" id="p_{{ $att->student_id }}"
                            value="present" @checked($att->status === 'present')
                          data-student="{{ $att->student_id }}" onchange="updateCount()">
                          <label class="form-check-label text-success fw-semibold" for="p_{{ $att->student_id }}">Present</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input att-radio" type="radio"
                            name="attendance[{{ $att->student_id }}][status]" id="a_{{ $att->student_id }}"
                            value="absent" @checked($att->status === 'absent')
                          data-student="{{ $att->student_id }}" onchange="updateCount()">
                          <label class="form-check-label text-danger fw-semibold" for="a_{{ $att->student_id }}">Absent</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input att-radio" type="radio"
                            name="attendance[{{ $att->student_id }}][status]" id="e_{{ $att->student_id }}"
                            value="excused" @checked($att->status === 'excused')
                          data-student="{{ $att->student_id }}" onchange="updateCount()">
                          <label class="form-check-label text-warning fw-semibold" for="e_{{ $att->student_id }}">Excused</label>
                        </div>
                      </div>
                      <input type="hidden" name="attendance[{{ $att->student_id }}][student_id]" value="{{ $att->student_id }}">
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm remark-input" style="width:160px;"
                        name="attendance[{{ $att->student_id }}][remarks]" id="remark_{{ $att->student_id }}" value="{{ $att->remarks }}" placeholder="Remarks...">
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="mt-3 d-flex gap-2">
              <input type="hidden" name="minutes" id="minutesInputHidden">
              <button type="submit" class="btn btn-success">
                <i class="bx bx-save me-1"></i> Save Attendance & Complete Session
              </button>
              <button type="button" class="btn btn-outline-primary" onclick="markAll('present')">Mark All Present</button>
            </div>
          </form>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  function updateCount() {
    let p = 0,
      a = 0,
      e = 0;
    document.querySelectorAll('.att-radio:checked').forEach(r => {
      if (r.value === 'present') p++;
      else if (r.value === 'absent') a++;
      else e++;
    });
    document.getElementById('presentCount').textContent = p;
    document.getElementById('absentCount').textContent = a;
    document.getElementById('excusedCount').textContent = e;
  }

  function markAll(status) {
    document.querySelectorAll('.att-radio').forEach(r => {
      if (r.value === status) r.checked = true;
    });
    updateCount();
  }

  // Remove saveAttendance JS. Use form submit instead.

  // Use a hidden form for deleteSession
  function deleteSession(id) {
    if (!confirm('Delete this session and all its attendance records?')) return;
    document.getElementById('deleteSessionForm').submit();
  }

  // Sync textarea to hidden input on submit
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('attendanceForm');
    if (form) {
      form.addEventListener('submit', function() {
        document.getElementById('minutesInputHidden').value = document.getElementById('minutesInput').value;
      });
    }
  });
</script>

<form id="deleteSessionForm" method="POST" action="{{ route('faculty.mentorship.session.destroy', $session->id) }}" style="display:none;">
  @csrf
  @method('DELETE')
</form>

@include('includes.footer')
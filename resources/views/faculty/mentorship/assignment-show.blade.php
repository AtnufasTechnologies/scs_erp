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
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.group.show', $assignment->group->id) }}">{{ $assignment->group->name }}</a></li>
            <li class="breadcrumb-item active">{{ $assignment->title }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div id="alertBox"></div>

      <!-- Assignment Details -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h4 class="fw-bold mb-1">{{ $assignment->title }}</h4>
              <p class="text-muted">{{ $assignment->group->name }}</p>
              <div class="d-flex gap-3 flex-wrap mb-3">
                <span><i class="bx bx-trophy text-warning"></i> Max Marks: <strong>{{ $assignment->max_marks }}</strong></span>
                @if($assignment->due_date)
                <span class="{{ $assignment->due_date->isPast() ? 'text-danger' : 'text-muted' }}">
                  <i class="bx bx-calendar"></i> Due: {{ $assignment->due_date->format('d M Y') }}
                  {{ $assignment->due_date->isPast() ? '(Overdue)' : '' }}
                </span>
                @endif
                <span class="badge bg-{{ $assignment->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span>
              </div>
              <div class="bg-light rounded p-3">{{ $assignment->description }}</div>
              @if($assignment->attachment_path)
              <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                <i class="bx bx-download"></i> Download Attachment
              </a>
              @endif
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment({{ $assignment->id }})">
              <i class="bx bx-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-warning mb-0">{{ $pending }}</div>
            <div class="text-muted small">Pending</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-info mb-0">{{ $submitted }}</div>
            <div class="text-muted small">Submitted</div>
          </div>
        </div>
        <div class="col-4">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="h3 fw-bold text-success mb-0">{{ $graded }}</div>
            <div class="text-muted small">Graded</div>
          </div>
        </div>
      </div>

      <!-- Submissions Table -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
          <h6 class="fw-bold mb-0"><i class="bx bx-list-ul text-primary me-2"></i>Student Submissions</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Student</th>
                  <th>Status</th>
                  <th>Submitted At</th>
                  <th>Marks</th>
                  <th>Feedback</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($assignment->submissions as $sub)
                <tr id="row-{{ $sub->id }}">
                  <td>
                    <div class="fw-semibold">{{ $sub->student->first_name }} {{ $sub->student->last_name }}</div>
                    <div class="text-muted small">{{ $sub->student->register_no }}</div>
                  </td>
                  <td>
                    <span class="badge bg-{{ $sub->status === 'graded' ? 'success' : ($sub->status === 'submitted' ? 'info' : 'secondary') }}">
                      {{ ucfirst($sub->status) }}
                    </span>
                  </td>
                  <td class="text-muted">{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y H:i') : '—' }}</td>
                  <td>
                    <input type="number" class="form-control form-control-sm marks-input" id="marks_{{ $sub->id }}"
                      style="width:80px;" value="{{ $sub->marks_obtained }}"
                      min="0" max="{{ $assignment->max_marks }}" placeholder="—">
                  </td>
                  <td>
                    <input type="text" class="form-control form-control-sm feedback-input" id="feedback_{{ $sub->id }}"
                      style="width:180px;" value="{{ $sub->feedback }}" placeholder="Feedback...">
                  </td>
                  <td>
                    <button class="btn btn-sm btn-success" onclick="gradeSubmission({{ $sub->id }})">
                      <i class="bx bx-check"></i> Grade
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  function gradeSubmission(subId) {
    const marks = document.getElementById(`marks_${subId}`).value;
    const feedback = document.getElementById(`feedback_${subId}`).value;

    if (marks === '') {
      alert('Enter marks to grade.');
      return;
    }

    fetch(`/faculty/mentorship/submissions/${subId}/grade`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          marks_obtained: marks,
          feedback: feedback
        })
      })
      .then(r => r.json())
      .then(data => {
        const box = document.getElementById('alertBox');
        box.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show">
      ${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        if (data.success) setTimeout(() => location.reload(), 1200);
      });
  }

  function deleteAssignment(id) {
    if (!confirm('Delete this assignment and all submissions?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/faculty/mentorship/assignments/${id}`;
    form.innerHTML = '<input name="_token" value="{{ csrf_token() }}"><input name="_method" value="DELETE">';
    document.body.appendChild(form);
    form.submit();
  }
</script>

@include('includes.footer')
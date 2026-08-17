@include('includes.header')

@php
$courseMaster = $routine->syllabus->courseLink->courseMaster ?? null;
$courseCode = trim((string) ($courseMaster->course_code ?? 'N/A'));
$courseTitle = trim((string) ($courseMaster->course_title ?? 'N/A'));
$safeRosterRows = collect($rosterRows ?? []);
@endphp

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.student.course.roster') }}">Student Course Roster</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Roster</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">

      <div id="ajaxMessage"></div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h5 class="mb-2 fw-bold">{{ $courseTitle }} ({{ $courseCode }})</h5>
          <p class="mb-0 text-muted">
            Subject: {{ $routine->syllabus->subject->title ?? 'N/A' }} |
            Semester: {{ $routine->syllabus->semestermaster->title ?? 'N/A' }} |
            Batch: {{ $routine->syllabus->batchmaster->batch_name ?? 'N/A' }} |
            Shift: {{ ucfirst(strtolower((string) ($routine->shift ?? 'common'))) }}
          </p>
          <div class="mt-3 d-flex gap-2">
            <a href="{{ route('faculty.student.course.roster') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back To Course List
            </a>
            <a href="{{ route('faculty.course.roster.export', ['id' => $routine->id, 'code' => $courseCode]) }}" class="btn btn-success">
              <i class="fas fa-file-excel me-1"></i>Export To Excel
            </a>
            <a href="{{ route('faculty.course.roster.create', ['id' => $routine->id, 'code' => $courseCode]) }}" class="btn btn-outline-primary">
              <i class="fas fa-plus-circle me-1"></i>Add More Students
            </a>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-info"></i>Roster Students</h6>
          <div class="d-flex align-items-center gap-2">
            <input type="text" id="studentSearch" class="form-control form-control-sm" placeholder="Search by roll/register/name" style="max-width: 260px;">
            <span class="badge bg-primary" id="rosterCountBadge">{{ $safeRosterRows->count() }} Students</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rosterTable">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Roll No</th>
                  <th>Register No</th>
                  <th>Student Name</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="rosterTableBody">
                @forelse($safeRosterRows as $index => $row)
                @php
                $student = $row->studentmaster;
                @endphp
                <tr data-student-id="{{ (int) ($row->student_id ?? 0) }}">
                  <td class="ps-4 fw-semibold">{{ $index + 1 }}</td>
                  <td>{{ $student->roll_no ?? '-' }}</td>
                  <td>{{ $student->register_no ?? '-' }}</td>
                  <td>{{ trim((string) ($student->first_name ?? '') . ' ' . (string) ($student->last_name ?? '')) }}</td>
                  <td class="text-center">
                    <form method="POST" action="{{ route('faculty.course.roster.student.remove', ['id' => $routine->id, 'code' => $courseCode, 'studentId' => (int) ($row->student_id ?? 0)]) }}" class="d-inline remove-roster-form">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm remove-btn">
                        <i class="fa fa-trash"></i> Remove
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr id="emptyRosterRow">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <i class="fas fa-info-circle me-2"></i>No students found in this roster.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>


    </div>
  </main>
</div>

<script>
  (function() {
    const ajaxMessage = document.getElementById('ajaxMessage');
    const rosterTableBody = document.getElementById('rosterTableBody');
    const rosterCountBadge = document.getElementById('rosterCountBadge');
    const searchInput = document.getElementById('studentSearch');

    function showMessage(type, message) {
      if (!ajaxMessage) return;
      const safeType = type === 'success' ? 'success' : 'danger';
      ajaxMessage.innerHTML = `
        <div class="alert alert-${safeType} alert-dismissible fade show" role="alert">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
    }

    function refreshSerialNumbers() {
      const visibleRows = Array.from(document.querySelectorAll('#rosterTableBody tr[data-student-id]'));
      visibleRows.forEach((row, index) => {
        const serialCell = row.querySelector('td');
        if (serialCell) {
          serialCell.textContent = String(index + 1);
        }
      });
    }

    function refreshCountBadge() {
      if (!rosterCountBadge) return;
      const totalRows = document.querySelectorAll('#rosterTableBody tr[data-student-id]').length;
      rosterCountBadge.textContent = `${totalRows} Students`;
    }

    function ensureEmptyState() {
      if (!rosterTableBody) return;
      const totalRows = document.querySelectorAll('#rosterTableBody tr[data-student-id]').length;
      let emptyRow = document.getElementById('emptyRosterRow');

      if (totalRows === 0 && !emptyRow) {
        emptyRow = document.createElement('tr');
        emptyRow.id = 'emptyRosterRow';
        emptyRow.innerHTML = `
          <td colspan="5" class="text-center py-5 text-muted">
            <i class="fas fa-info-circle me-2"></i>No students found in this roster.
          </td>
        `;
        rosterTableBody.appendChild(emptyRow);
      }

      if (totalRows > 0 && emptyRow) {
        emptyRow.remove();
      }
    }

    document.querySelectorAll('.remove-roster-form').forEach((form) => {
      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (!confirm('Remove this student from roster?')) {
          return;
        }

        const button = form.querySelector('.remove-btn');
        const row = form.closest('tr');
        const formData = new FormData(form);

        if (button) {
          button.disabled = true;
          button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Removing';
        }

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
          });

          const payload = await response.json();

          if (!response.ok || !payload.success) {
            showMessage('error', payload.message || 'Failed to remove student.');
            return;
          }

          if (row) {
            row.remove();
          }

          refreshSerialNumbers();
          refreshCountBadge();
          ensureEmptyState();
          showMessage('success', payload.message || 'Student removed from roster.');
        } catch (error) {
          showMessage('error', 'Request failed. Please try again.');
        } finally {
          if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-trash"></i> Remove';
          }
        }
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const query = (this.value || '').toLowerCase().trim();
        const rows = Array.from(document.querySelectorAll('#rosterTableBody tr[data-student-id]'));

        rows.forEach((row) => {
          const text = (row.textContent || '').toLowerCase();
          row.style.display = text.includes(query) ? '' : 'none';
        });
      });
    }
  })();
</script>

@include('includes.footer')
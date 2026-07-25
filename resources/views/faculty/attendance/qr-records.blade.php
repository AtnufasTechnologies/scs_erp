@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">QR Records</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <h4 class="mb-1">Generated Attendance QR Records</h4>
          <p class="text-muted mb-0">History of QR links generated for student mobile attendance.</p>
        </div>
        <a href="{{ route('faculty.attendance.index') }}" class="btn btn-outline-primary">
          <i class="fa fa-plus me-1"></i>Generate New QR
        </a>
      </div>

      <form method="get" class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Attendance Date</label>
              <input type="date" class="form-control" name="attendance_date" value="{{ request('attendance_date') }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Course</label>
              <select name="course_id" class="form-select">
                <option value="">All Courses</option>
                @foreach($courseFilters as $course)
                <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                  {{ $course->course_code ?? 'N/A' }} - {{ $course->course_title ?? 'N/A' }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-warning"><i class="fa fa-filter me-1"></i>Filter</button>
            </div>
          </div>
        </div>
      </form>

      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Generated At</th>
                  <th>Date</th>
                  <th>Course</th>
                  <th>Semester</th>
                  <th>Batch</th>
                  <th>Shift</th>
                  <th>Hour ID</th>
                  <th>Type</th>
                  <th>Expires</th>
                  <th>Status</th>
                  <th>Scans</th>
                  <th>Students</th>
                  <th>Scan URL</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($records as $record)
                @php
                $course = optional(optional(optional($record->routine)->syllabus)->courseLink)->courseMaster;
                $semester = optional(optional($record->routine)->syllabus)->semestermaster->title ?? $record->semester_id;
                $batch = optional(optional($record->routine)->syllabus)->batchmaster->batch_name ?? $record->batch_id;
                $isExpired = !empty($record->expires_at) ? now()->greaterThan($record->expires_at) : false;
                $isFinalized = (int) ($record->status ?? 0) === 2;
                @endphp
                <tr>
                  <td>{{ $loop->iteration + (($records->currentPage() - 1) * $records->perPage()) }}</td>
                  <td>{{ optional($record->created_at)->format('d M Y h:i A') }}</td>
                  <td>{{ optional($record->attendance_date)->format('d M Y') }}</td>
                  <td>{{ $course->course_code ?? 'N/A' }} - {{ $course->course_title ?? 'N/A' }}</td>
                  <td>{{ $semester }}</td>
                  <td>{{ $batch }}</td>
                  <td>{{ ucfirst(optional($record->routine)->shift ?? 'common') }}</td>
                  <td>{{ $record->hourmaster->name ?? '' }}</td>
                  <td>{{ ucfirst($record->attendance_type ?? 'regular') }}</td>
                  <td>{{ optional($record->expires_at)->format('d M Y h:i A') }}</td>
                  <td>
                    @if($isFinalized)
                    <span class="badge bg-primary">Finalized</span>
                    @elseif((int) ($record->status ?? 0) === 3)
                    <span class="badge bg-warning text-dark">Fake Test</span>
                    @elseif($isExpired)
                    <span class="badge bg-secondary">Expired</span>
                    @else
                    <span class="badge bg-success">Active</span>
                    @endif
                  </td>
                  <td><span class="badge bg-info text-dark">{{ (int) ($record->scan_count ?? 0) }}</span></td>
                  <td>
                    @if((int) ($record->scan_count ?? 0) > 0)
                    <button class="btn btn-sm btn-outline-primary js-view-students" data-students='@json($record->scanned_students ?? [])'>
                      <i class="fa fa-users me-1"></i>View
                    </button>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>
                    @if(!empty($record->scan_url))
                    <button class="btn btn-sm btn-outline-dark js-copy-url" data-url="{{ $record->scan_url }}">
                      <i class="fa fa-copy me-1"></i>Copy
                    </button>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger js-delete-qr" data-record-id="{{ $record->id }}">
                      <i class="fa fa-trash me-1"></i>Delete
                    </button>
                  </td>
                </tr>

                @empty
                <tr>
                  <td colspan="15" class="text-center py-4 text-muted">No QR records found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="mt-3">
        {{ $records->links() }}
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteQrEndpoint = `{{ route('faculty.attendance.qr.delete') }}`;
    const csrfToken = `{{ csrf_token() }}`;

    function escapeHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    document.querySelectorAll('.js-view-students').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const students = JSON.parse(this.getAttribute('data-students') || '[]');
        const rows = students.map((student, index) => {
          return `<tr><td>${index + 1}</td><td>${escapeHtml(student.roll_no || 'N/A')}</td><td>${escapeHtml(student.name || 'N/A')}</td></tr>`;
        }).join('');

        const html = students.length > 0 ?
          `<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>#</th><th>Roll No</th><th>Name</th></tr></thead><tbody>${rows}</tbody></table></div>` :
          '<p class="text-muted mb-0">No scanned students found.</p>';

        Swal.fire({
          title: 'Students Who Scanned',
          html,
          width: 700,
          confirmButtonText: 'Close'
        });
      });
    });

    document.querySelectorAll('.js-copy-url').forEach(function(btn) {
      btn.addEventListener('click', async function() {
        const url = this.getAttribute('data-url') || '';
        if (!url) return;

        try {
          await navigator.clipboard.writeText(url);
          this.innerHTML = '<i class="fa fa-check me-1"></i>Copied';
          setTimeout(() => {
            this.innerHTML = '<i class="fa fa-copy me-1"></i>Copy';
          }, 1200);
        } catch (e) {
          alert('Unable to copy URL.');
        }
      });
    });

    document.querySelectorAll('.js-delete-qr').forEach(function(btn) {
      btn.addEventListener('click', async function() {
        const recordId = Number(this.getAttribute('data-record-id') || 0);
        if (!recordId) {
          return;
        }

        const ok = confirm('Delete this QR record? This will allow regeneration for the same slot.');
        if (!ok) {
          return;
        }

        try {
          const response = await fetch(deleteQrEndpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              record_id: recordId
            })
          });

          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to delete QR record.');
          }

          window.location.reload();
        } catch (error) {
          alert(error.message || 'Unable to delete QR record.');
        }
      });
    });
  });
</script>

@include('includes.footer')
@include('includes.header')

@php
$safeStudents = collect($students ?? []);
$safeExistingStudentIds = collect($existingStudentIds ?? [])->map(fn($id) => (int) $id)->all();
$existingMap = array_fill_keys($safeExistingStudentIds, true);

$courseMaster = $routine->syllabus->courseLink->courseMaster ?? null;
$courseCode = trim((string) ($courseMaster->course_code ?? ($record->course->course_code ?? 'N/A')));
$courseTitle = trim((string) ($courseMaster->course_title ?? ($record->course->course_title ?? 'N/A')));
@endphp

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.student.course.roster') }}">Student Course Roster</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Students</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid mt-4">
      <div class="mb-3">
        <a href="{{ route('faculty.student.course.roster') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>

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

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="row align-items-center g-3">
            <div class="col-md-9">
              <h5 class="mb-1 fw-bold">{{ $courseTitle }} ({{ $courseCode }})</h5>
              <p class="mb-0 text-muted">
                Subject: {{ $routine->syllabus->subject->title ?? 'N/A' }} |
                Semester: {{ $routine->syllabus->semestermaster->title ?? $semesterId }} |
                Batch: {{ $routine->syllabus->batchmaster->batch_name ?? $batchId }} |
                Shift: {{ ucfirst(strtolower((string) ($routine->shift ?? 'common'))) }}
              </p>
            </div>
            <div class="col-md-3 text-end">
              <span class="badge bg-primary">{{ $safeStudents->count() }} Students Found in Batch {{ $routine->syllabus->batchmaster->batch_name ?? $batchId }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center gap-2">
          <h6 class="mb-0 fw-bold"><i class="fas fa-user-plus text-success me-2"></i>Add Students To Course Roster</h6>
          <input type="text" id="studentSearch" class="form-control form-control-sm" placeholder="Search by roll no, register no, name" style="max-width: 280px;">
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('faculty.course.roster.store', ['id' => $routine->id, 'code' => $courseCode]) }}" id="rosterForm">
            @csrf

            @if($safeStudents->isEmpty())
            <div class="text-center py-5 text-muted">
              No eligible students found for this batch/shift context.
            </div>
            @else
            <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
              <button type="submit" class="btn btn-success btn-sm" id="submitAddBtn">
                <i class="fas fa-save me-1"></i>Add Selected Students
              </button>
              <button type="button" id="selectAllBtn" class="btn btn-outline-secondary btn-sm">Select All Visible</button>
              <button type="button" id="clearAllBtn" class="btn btn-outline-secondary btn-sm">Clear Selection</button>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle" id="studentsTable">
                <thead class="table-light">
                  <tr>
                    <th width="50">#</th>
                    <th width="70">Pick</th>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th width="150">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($safeStudents as $index => $student)
                  @php
                  $studentId = (int) ($student->id ?? 0);
                  $isAlreadyEnrolled = isset($existingMap[$studentId]);
                  @endphp
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                      <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="{{ $studentId }}" {{ $isAlreadyEnrolled ? 'checked disabled' : '' }}>
                    </td>
                    <td>{{ $student->roll_no ?? '-' }}</td>
                    <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}</td>
                    <td>
                      @if($isAlreadyEnrolled)
                      <span class="badge badge-success"> Enrolled</span>
                      @else
                      <span class="badge badge-warning text-dark">Not Enrolled</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </main>

  <script>
    (function() {
      const searchInput = document.getElementById('studentSearch');
      const rows = Array.from(document.querySelectorAll('#studentsTable tbody tr'));
      const selectAllBtn = document.getElementById('selectAllBtn');
      const clearAllBtn = document.getElementById('clearAllBtn');
      const rosterForm = document.getElementById('rosterForm');
      const submitAddBtn = document.getElementById('submitAddBtn');
      const ajaxMessage = document.getElementById('ajaxMessage');

      function showMessage(type, message) {
        if (!ajaxMessage) return;
        const safeType = type === 'success' ? 'success' : 'danger';
        ajaxMessage.innerHTML = `\n          <div class="alert alert-${safeType} alert-dismissible fade show" role="alert">\n            ${message}\n            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>\n          </div>\n        `;
      }

      if (rosterForm) {
        rosterForm.addEventListener('submit', async function(event) {
          event.preventDefault();

          const selected = Array.from(document.querySelectorAll('.student-checkbox:checked')).filter((item) => !item.disabled);
          if (selected.length === 0) {
            showMessage('error', 'Please select at least one student to add.');
            return;
          }

          if (submitAddBtn) {
            submitAddBtn.disabled = true;
            submitAddBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
          }

          try {
            const formData = new FormData(rosterForm);
            const response = await fetch(rosterForm.action, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: formData,
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
              let errorMessage = payload.message || 'Failed to update course roster.';
              if (payload.errors) {
                errorMessage = Object.values(payload.errors).flat().join(' ');
              }
              showMessage('error', errorMessage);
              return;
            }

            showMessage('success', payload.message || 'Students added successfully.');
            window.setTimeout(function() {
              window.location.reload();
            }, 900);
          } catch (error) {
            showMessage('error', 'Request failed. Please try again.');
          } finally {
            if (submitAddBtn) {
              submitAddBtn.disabled = false;
              submitAddBtn.innerHTML = '<i class="fas fa-save me-1"></i>Add Selected Students';
            }
          }
        });
      }

      if (searchInput) {
        searchInput.addEventListener('keyup', function() {
          const query = (this.value || '').toLowerCase().trim();
          rows.forEach(function(row) {
            const text = (row.textContent || '').toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
          });
        });
      }

      if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
          rows.forEach(function(row) {
            if (row.style.display === 'none') {
              return;
            }
            const checkbox = row.querySelector('.student-checkbox');
            if (checkbox) {
              checkbox.checked = true;
            }
          });
        });
      }

      if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
          document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
          });
        });
      }
    })();
  </script>
  <!--end page main-->
  @include('includes.footer')
</div>
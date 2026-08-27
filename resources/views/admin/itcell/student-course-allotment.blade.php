@include('includes.header')

<div class="wrapper">
  @include('admin.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">ITCELL</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('itcell.student-login-access.index') }}">Student Login Access</a></li>
            <li class="breadcrumb-item active" aria-current="page">Student Course Allotment</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div id="ajaxSyncAlert" class="mt-3" style="display:none;"></div>

    <div class="card shadow-sm border-0 mt-3 mb-3">
      <div class="card-body">
        <h5 class="fw-bold mb-1">Student Course Allotment</h5>
        <div class="text-muted small mb-2">Add course to one student and sync into both StudentCourseRoster and StudentCourseInfo.</div>
        <div><strong>Student:</strong> {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: 'N/A' }}</div>
        <div><strong>Roll No:</strong> {{ $student->roll_no ?? 'N/A' }} | <strong>Register No:</strong> {{ $student->register_no ?? 'N/A' }}</div>
        <div><strong>Program:</strong> {{ $student->stdprogramenrolled->code ?? '' }} {{ !empty($student->stdprogramenrolled->name) ? ('- ' . $student->stdprogramenrolled->name) : '' }}</div>
        <div><strong>Batch:</strong> {{ $student->batchmaster->batch_name ?? 'N/A' }}</div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('itcell.student-login-access.course-allotment.index', $student->id) }}" class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Semester</label>
            <select name="semester_id" class="form-select">
              <option value="0">All Semesters</option>
              @foreach($semesters as $semester)
              <option value="{{ $semester->id }}" {{ (int) $semesterFilter === (int) $semester->id ? 'selected' : '' }}>
                {{ $semester->title }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Search Course</label>
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by course code/title">
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-header bg-transparent fw-bold">Already Allotted (Source of Truth: StudentCourseRoster)</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Course</th>
                <th>Semester</th>
                <th>Type</th>
                <th>Credits</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allottedCourses as $course)
              @php
              $inRoster = $rosterCourseIds->contains((int) $course->id);
              $inInfo = $courseInfoCourseIds->contains((int) $course->id);
              @endphp
              <tr data-allotted-row-course-id="{{ $course->id }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $course->course_code ?? 'N/A' }}</td>
                <td>{{ $course->course_title ?? 'N/A' }}</td>
                <td>{{ $course->semestermaster->title ?? ('Semester ' . ((int) ($course->semester_id ?? 0))) }}</td>
                <td>{{ $course->coursetypemaster->title ?? 'N/A' }}</td>
                <td>{{ $course->credits ?? 'N/A' }}</td>
                <td>
                  @if($inRoster && $inInfo)
                  <span class="badge bg-success js-sync-status" data-course-id="{{ $course->id }}">Synced (Roster + CourseInfo)</span>
                  @elseif($inRoster)
                  <span class="badge bg-warning text-dark js-sync-status" data-course-id="{{ $course->id }}">Roster only</span>
                  @else
                  <span class="badge bg-secondary js-sync-status" data-course-id="{{ $course->id }}">Unknown</span>
                  @endif
                </td>
                <td>
                  @if($inRoster && !$inInfo)
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-warning js-sync-course"
                    data-course-id="{{ $course->id }}"
                    data-target="allotted">
                    <i class="fas fa-sync-alt me-1"></i>Sync Now
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger js-remove-course ms-1"
                    data-course-id="{{ $course->id }}">
                    <i class="fas fa-trash-alt me-1"></i>
                  </button>
                  @elseif($inRoster)
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger js-remove-course"
                    data-course-id="{{ $course->id }}">
                    <i class="fas fa-trash-alt me-1"></i>
                  </button>
                  @else
                  <span class="text-muted small">-</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-3">No allotted courses yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-transparent fw-bold">Available Courses</div>
      <div class="card-body p-0">
        <div class="p-3 border-bottom">
          <input
            type="text"
            id="availableCoursesSearch"
            class="form-control"
            placeholder="Search within Available Courses table (code/title/type/semester)">
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle mb-0" id="availableCoursesTable">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Course</th>
                <th>Semester</th>
                <th>Type</th>
                <th>Credits</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($courses as $course)
              @php
              $inRoster = $rosterCourseIds->contains((int) $course->id);
              $inInfo = $courseInfoCourseIds->contains((int) $course->id);
              @endphp
              <tr>
                <td>{{ $loop->iteration + (($courses->currentPage() - 1) * $courses->perPage()) }}</td>
                <td>{{ $course->course_code ?? 'N/A' }}</td>
                <td>{{ $course->course_title ?? 'N/A' }}</td>
                <td>{{ $course->semestermaster->title ?? ('Semester ' . ((int) ($course->semester_id ?? 0))) }}</td>
                <td>{{ $course->coursetypemaster->title ?? 'N/A' }}</td>
                <td>{{ $course->credits ?? 'N/A' }}</td>
                <td>
                  @if($inRoster && $inInfo)
                  <span class="badge bg-success js-sync-status" data-course-id="{{ $course->id }}">Synced</span>
                  @elseif($inRoster)
                  <span class="badge bg-warning text-dark js-sync-status" data-course-id="{{ $course->id }}">Roster only</span>
                  @else
                  <span class="badge bg-secondary js-sync-status" data-course-id="{{ $course->id }}">Not allotted</span>
                  @endif
                </td>
                <td>
                  <form method="POST" action="{{ route('itcell.student-login-access.course-allotment.store', $student->id) }}" onsubmit="return confirm('Allot this course to the selected student and sync both tables?');" class="d-inline">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-plus me-1"></i>Allot + Sync
                    </button>
                  </form>
                  @if($inRoster && !$inInfo)
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-warning js-sync-course ms-1"
                    data-course-id="{{ $course->id }}"
                    data-target="available">
                    <i class="fas fa-sync-alt me-1"></i>Sync
                  </button>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No courses found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-transparent">
        {{ $courses->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  </main>
</div>

<script>
  (function() {
    const syncButtons = Array.from(document.querySelectorAll('.js-sync-course'));
    const removeButtons = Array.from(document.querySelectorAll('.js-remove-course'));
    const alertContainer = document.getElementById('ajaxSyncAlert');
    const availableSearchInput = document.getElementById('availableCoursesSearch');
    const availableTable = document.getElementById('availableCoursesTable');
    const syncUrl = "{{ route('itcell.student-login-access.course-allotment.store', $student->id) }}";
    const removeUrl = "{{ route('itcell.student-login-access.course-allotment.destroy', $student->id) }}";
    const csrfToken = "{{ csrf_token() }}";

    function showAlert(type, message) {
      if (!alertContainer) {
        return;
      }

      const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
      const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
      alertContainer.style.display = 'block';
      alertContainer.innerHTML =
        '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<i class="fas ' + iconClass + ' me-2"></i>' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';
    }

    function setSyncedState(courseId) {
      const statuses = Array.from(document.querySelectorAll('.js-sync-status[data-course-id="' + courseId + '"]'));
      statuses.forEach((statusEl) => {
        statusEl.classList.remove('bg-warning', 'text-dark', 'bg-secondary');
        statusEl.classList.add('bg-success');
        statusEl.textContent = 'Synced';
      });

      const syncForCourse = Array.from(document.querySelectorAll('.js-sync-course[data-course-id="' + courseId + '"]'));
      syncForCourse.forEach((btn) => {
        btn.disabled = true;
        btn.classList.remove('btn-outline-warning');
        btn.classList.add('btn-success');
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Synced';
      });
    }

    function setRemovedState(courseId) {
      const allottedRows = Array.from(document.querySelectorAll('tr[data-allotted-row-course-id="' + courseId + '"]'));
      allottedRows.forEach((row) => row.remove());

      const statuses = Array.from(document.querySelectorAll('.js-sync-status[data-course-id="' + courseId + '"]'));
      statuses.forEach((statusEl) => {
        statusEl.classList.remove('bg-success', 'bg-warning', 'text-dark');
        statusEl.classList.add('bg-secondary');
        statusEl.textContent = 'Not allotted';
      });

      const syncForCourse = Array.from(document.querySelectorAll('.js-sync-course[data-course-id="' + courseId + '"]'));
      syncForCourse.forEach((btn) => btn.remove());

      const removeForCourse = Array.from(document.querySelectorAll('.js-remove-course[data-course-id="' + courseId + '"]'));
      removeForCourse.forEach((btn) => btn.remove());
    }

    function syncCourse(button) {
      const courseId = (button.getAttribute('data-course-id') || '').trim();
      if (!courseId) {
        showAlert('error', 'Course id missing for sync.');
        return;
      }

      button.disabled = true;
      const originalHtml = button.innerHTML;
      button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Syncing...';

      fetch(syncUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            course_id: parseInt(courseId, 10),
            ajax: true
          })
        })
        .then(async (response) => {
          const payload = await response.json().catch(() => ({}));
          if (!response.ok || !payload.ok) {
            const msg = payload.message || 'Sync failed for selected course.';
            throw new Error(msg);
          }
          setSyncedState(courseId);
          showAlert('success', payload.message || 'Course synced successfully.');
        })
        .catch((error) => {
          button.disabled = false;
          button.innerHTML = originalHtml;
          showAlert('error', error.message || 'Sync failed.');
        });
    }

    function removeCourse(button) {
      const courseId = (button.getAttribute('data-course-id') || '').trim();
      if (!courseId) {
        showAlert('error', 'Course id missing for remove.');
        return;
      }

      if (!confirm('Delete this roster allotment and sync removal to StudentCourseInfo as well?')) {
        return;
      }

      button.disabled = true;
      const originalHtml = button.innerHTML;
      button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Removing...';

      fetch(removeUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            course_id: parseInt(courseId, 10),
            ajax: true
          })
        })
        .then(async (response) => {
          const payload = await response.json().catch(() => ({}));
          if (!response.ok || !payload.ok) {
            const msg = payload.message || 'Remove failed for selected course.';
            throw new Error(msg);
          }
          setRemovedState(courseId);
          showAlert('success', payload.message || 'Course removed successfully.');
        })
        .catch((error) => {
          button.disabled = false;
          button.innerHTML = originalHtml;
          showAlert('error', error.message || 'Remove failed.');
        });
    }

    syncButtons.forEach((button) => {
      button.addEventListener('click', function() {
        syncCourse(button);
      });
    });

    removeButtons.forEach((button) => {
      button.addEventListener('click', function() {
        removeCourse(button);
      });
    });

    if (availableSearchInput && availableTable) {
      availableSearchInput.addEventListener('input', function() {
        const term = (availableSearchInput.value || '').toLowerCase().trim();
        const rows = Array.from(availableTable.querySelectorAll('tbody tr'));

        rows.forEach((row) => {
          const text = (row.textContent || '').toLowerCase();
          row.style.display = term === '' || text.includes(term) ? '' : 'none';
        });
      });
    }
  })();
</script>

@include('includes.footer')
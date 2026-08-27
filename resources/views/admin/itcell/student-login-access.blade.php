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
            <li class="breadcrumb-item active" aria-current="page">Student Login Access</li>
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

    <div class="card shadow-sm border-0 mt-3">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <h5 class="mb-0 fw-bold">Student Login Access Checker</h5>
          <small class="text-muted">Default password policy: student roll number</small>
        </div>

        <form method="GET" action="{{ route('itcell.student-login-access.index') }}" class="row g-2 mb-3">
          <div class="col-md-8">
            <input
              type="text"
              name="search"
              value="{{ $search ?? '' }}"
              class="form-control"
              placeholder="Search by roll no, register no, app code, name, email">
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search me-1"></i>Search
            </button>
            <a href="{{ route('itcell.student-login-access.index') }}" class="btn btn-outline-secondary">
              Reset
            </a>
          </div>
        </form>

        <form method="POST" action="{{ route('itcell.student-login-access.bulk-reset-default-password') }}" id="bulkResetForm" class="mb-3">
          @csrf
          <div id="bulkStudentIds"></div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="submit" id="bulkResetBtn" class="btn btn-outline-primary" disabled>
              <i class="fas fa-key me-1"></i>Bulk Reset Default Password
            </button>
            <span id="selectedCountText" class="text-muted small">0 selected</span>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 42px;">
                  <input type="checkbox" id="selectAllStudents" class="form-check-input" title="Select all on current page">
                </th>
                <th>#</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Register No</th>
                <th>Program</th>
                <th>Batch</th>
                <th>Email</th>
                <th>Access Status</th>
                <th>Login Roll</th>
                <th>Decrypted Password</th>
                <th>Reset</th>
                <th>Courses</th>
              </tr>
            </thead>
            <tbody>
              @forelse($students as $student)
              @php
              $user = $student->access_user;
              $status = strtoupper((string) ($user->status ?? ''));
              $active = $status === 'ACTIVE';
              @endphp
              <tr>
                <td>
                  <input
                    type="checkbox"
                    class="form-check-input js-student-checkbox"
                    value="{{ $student->id }}"
                    title="Select student">
                </td>
                <td>{{ $loop->iteration + (($students->currentPage() - 1) * $students->perPage()) }}</td>
                <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: 'N/A' }}</td>
                <td>{{ $student->roll_no ?? 'N/A' }}</td>
                <td>{{ $student->register_no ?? 'N/A' }}</td>
                <td>
                  {{ $student->stdprogramenrolled->code ?? '' }}
                  @if(!empty($student->stdprogramenrolled->name))
                  - {{ $student->stdprogramenrolled->name }}
                  @endif
                </td>
                <td>{{ $student->batchmaster->batch_name ?? 'N/A' }}</td>
                <td>{{ $student->mail_id ?? 'N/A' }}</td>
                <td>
                  @if($student->has_login_access)
                  <span class="badge {{ $active ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $active ? 'ACTIVE' : ($status !== '' ? $status : 'INACTIVE') }}
                  </span>
                  @else
                  <span class="badge bg-secondary">NO ACCESS</span>
                  @endif
                </td>
                <td>{{ $user->roll_no ?? 'N/A' }}</td>
                <td>
                  @if($student->has_login_access)
                  <span
                    class="badge bg-light text-dark border js-password-text"
                    data-password="{{ (string) ($user->decrypted_password ?? '') }}"
                    data-visible="0">********</span>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-dark ms-1 js-toggle-password"
                    title="Show password">
                    <i class="fas fa-eye me-1"></i>
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary ms-1 js-copy-password"
                    data-password="{{ (string) ($user->decrypted_password ?? '') }}"
                    title="Copy password">
                    <i class="fas fa-copy me-1"></i>
                  </button>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  <form
                    method="POST"
                    action="{{ route('itcell.student-login-access.reset-default-password', $student->id) }}"
                    onsubmit="return confirm('Reset default password to roll number for this student?');"
                    class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-key me-1"></i>{{ $student->has_login_access ? ' Reset ' : 'Create Access' }}
                    </button>
                  </form>
                </td>
                <td>
                  <a href="{{ route('itcell.student-login-access.course-allotment.index', $student->id) }}" class="btn btn-sm btn-outline-success mt-1">
                    <i class="fas fa-book-medical me-1"></i> Courses
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="12" class="text-center text-muted py-4">No students found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-3">
          {{ $students->links() }}
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  (function() {
    const selectAll = document.getElementById('selectAllStudents');
    const checkboxes = Array.from(document.querySelectorAll('.js-student-checkbox'));
    const selectedCountText = document.getElementById('selectedCountText');
    const bulkResetBtn = document.getElementById('bulkResetBtn');
    const bulkStudentIds = document.getElementById('bulkStudentIds');
    const bulkResetForm = document.getElementById('bulkResetForm');
    const copyButtons = Array.from(document.querySelectorAll('.js-copy-password'));
    const toggleButtons = Array.from(document.querySelectorAll('.js-toggle-password'));

    function maskPassword(password) {
      if (!password) {
        return '********';
      }

      return '*'.repeat(Math.max(password.length, 8));
    }

    function copyToClipboard(text) {
      if (!text) {
        return Promise.reject(new Error('No text to copy'));
      }

      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      }

      return new Promise((resolve, reject) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();

        try {
          const ok = document.execCommand('copy');
          document.body.removeChild(textarea);
          if (!ok) {
            reject(new Error('Copy command failed'));
            return;
          }
          resolve();
        } catch (error) {
          document.body.removeChild(textarea);
          reject(error);
        }
      });
    }

    function selectedValues() {
      return checkboxes.filter((item) => item.checked).map((item) => item.value);
    }

    function syncUi() {
      const selected = selectedValues();
      const selectedCount = selected.length;

      if (selectedCountText) {
        selectedCountText.textContent = selectedCount + ' selected';
      }

      if (bulkResetBtn) {
        bulkResetBtn.disabled = selectedCount === 0;
      }

      if (selectAll) {
        const total = checkboxes.length;
        const allChecked = total > 0 && selectedCount === total;
        selectAll.checked = allChecked;
        selectAll.indeterminate = selectedCount > 0 && selectedCount < total;
      }
    }

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        checkboxes.forEach((item) => {
          item.checked = !!selectAll.checked;
        });
        syncUi();
      });
    }

    checkboxes.forEach((item) => {
      item.addEventListener('change', syncUi);
    });

    copyButtons.forEach((button) => {
      button.addEventListener('click', function() {
        const password = (button.getAttribute('data-password') || '').trim();
        if (!password) {
          alert('Password not available to copy.');
          return;
        }

        const originalHtml = button.innerHTML;
        copyToClipboard(password)
          .then(() => {
            button.innerHTML = '<i class="fas fa-check me-1"></i>Copied';
            setTimeout(() => {
              button.innerHTML = originalHtml;
            }, 1200);
          })
          .catch(() => {
            alert('Unable to copy password. Please copy manually.');
          });
      });
    });

    toggleButtons.forEach((button) => {
      button.addEventListener('click', function() {
        const container = button.closest('td');
        if (!container) {
          return;
        }

        const passwordBadge = container.querySelector('.js-password-text');
        if (!passwordBadge) {
          return;
        }

        const password = (passwordBadge.getAttribute('data-password') || '').trim();
        if (!password) {
          alert('Password not available.');
          return;
        }

        const currentlyVisible = passwordBadge.getAttribute('data-visible') === '1';
        if (currentlyVisible) {
          passwordBadge.textContent = maskPassword(password);
          passwordBadge.setAttribute('data-visible', '0');
          button.innerHTML = '<i class="fas fa-eye me-1"></i>';
          button.setAttribute('title', 'Show password');
        } else {
          passwordBadge.textContent = password;
          passwordBadge.setAttribute('data-visible', '1');
          button.innerHTML = '<i class="fas fa-eye-slash me-1"></i>';
          button.setAttribute('title', 'Hide password');
        }
      });
    });

    if (bulkResetForm) {
      bulkResetForm.addEventListener('submit', function(event) {
        const selected = selectedValues();
        if (selected.length === 0) {
          event.preventDefault();
          return;
        }

        if (!confirm('Reset default password for selected students?')) {
          event.preventDefault();
          return;
        }

        if (bulkStudentIds) {
          bulkStudentIds.innerHTML = '';
          selected.forEach((studentId) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = studentId;
            bulkStudentIds.appendChild(input);
          });
        }
      });
    }

    syncUi();
  })();
</script>

@include('includes.footer')
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
                <th>Actions</th>
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
                  <span class="badge bg-light text-dark border">{{ $user->decrypted_password ?? 'N/A' }}</span>
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
                      <i class="fas fa-key me-1"></i>{{ $student->has_login_access ? 'Reset Default Password' : 'Create Login Access' }}
                    </button>
                  </form>
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
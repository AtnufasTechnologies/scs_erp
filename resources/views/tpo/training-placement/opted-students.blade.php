@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Placement Program</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Opted Students</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-user-graduate me-2 text-primary"></i>Students Opted For Job Placement Program</h4>
            <p class="mb-0 text-muted">Students listed here have at least one training attempt in the placement preparation module.</p>
          </div>
          <a href="{{ route('tpo.training-placement.analytics') }}" class="btn btn-outline-primary">
            <i class="fas fa-chart-line me-1"></i>Training Analytics
          </a>
          <a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="btn btn-primary">
            <i class="fas fa-file-upload me-1"></i>Upload Student Opt-In Forms
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <form method="GET" action="{{ route('tpo.training-placement.opted-students.index') }}" class="row g-2 align-items-end">
            <div class="col-md-10">
              <label class="form-label fw-semibold mb-1">Search Student</label>
              <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, email, roll no or register no">
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Search</button>
              @if($search !== '')
              <a href="{{ route('tpo.training-placement.opted-students.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Opted Students</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0 align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  <th>Roll / Register</th>
                  <th>Campus</th>
                  <th>Department</th>
                  <th>Attempts</th>
                  <th>Completed</th>
                  <th>Last Completed</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($optedStudents as $index => $student)
                @php
                $displayName = trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
                if ($displayName === '') {
                $displayName = (string) ($student->user_name ?? 'N/A');
                }
                $displayRoll = $student->student_roll_no ?: $student->user_roll_no;
                @endphp
                <tr>
                  <td>{{ $optedStudents->firstItem() + $index }}</td>
                  <td>
                    <div class="fw-semibold">{{ $displayName }}</div>
                    <div class="text-muted small">{{ $student->user_email ?: 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="small">Roll: {{ $displayRoll ?: 'N/A' }}</div>
                    <div class="small text-muted">Reg: {{ $student->register_no ?: 'N/A' }}</div>
                  </td>
                  <td>{{ $student->campus_name ?: 'N/A' }}</td>
                  <td>{{ $student->department_name ?: 'N/A' }}</td>
                  <td><span class="badge bg-primary">{{ (int) $student->attempts_count }}</span></td>
                  <td><span class="badge bg-success">{{ (int) $student->completed_count }}</span></td>
                  <td>{{ $student->last_completed_at ? \Carbon\Carbon::parse($student->last_completed_at)->format('d M Y h:i A') : 'N/A' }}</td>
                  <td class="text-end">
                    <a href="{{ route('tpo.training-placement.opted-students.analysis', $student->user_id) }}" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-file-alt me-1"></i>View Training Analysis
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">No opted students found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @if($optedStudents->hasPages())
        <div class="card-footer bg-transparent">
          {{ $optedStudents->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
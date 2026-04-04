@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attendance Report</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-7">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-user-check me-2"></i>Attendance Report</h3>
                  <p class="text-white-50 mb-0">Student attendance records across exams</p>
                </div>
                <div class="col-md-5 text-md-end">
                  <form method="POST" action="{{ route('admin.exam-reports.export-pdf') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="attendance">
                    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <button type="submit" class="btn btn-light me-2"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                  </form>
                  <form method="POST" action="{{ route('admin.exam-reports.export-excel') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="attendance">
                    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <button type="submit" class="btn btn-outline-light"><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-clipboard-list text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Records</p>
                  <h4 class="mb-0 fw-bold">{{ $totalCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Present</p>
                  <h4 class="mb-0 fw-bold">{{ $presentCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-times-circle text-danger" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Absent</p>
                  <h4 class="mb-0 fw-bold">{{ $absentCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-exclamation-triangle text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Malpractice</p>
                  <h4 class="mb-0 fw-bold">{{ $malpracticeCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-reports.attendance') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam</label>
              <select name="exam_id" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Department</label>
              <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->name }}" {{ request('department') == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Date</label>
              <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="malpractice" {{ request('status') === 'malpractice' ? 'selected' : '' }}>Malpractice</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.exam-reports.attendance') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  <th>Exam</th>
                  <th>Subject</th>
                  <th>Seat No</th>
                  <th>Status</th>
                  <th>Marked At</th>
                </tr>
              </thead>
              <tbody>
                @forelse($attendances as $att)
                <tr>
                  <td>{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                  <td>
                    <div class="fw-semibold">{{ $att->student->first_name ?? '' }} {{ $att->student->last_name ?? '' }}</div>
                    <small class="text-muted">{{ $att->student->department ?? '' }}</small>
                  </td>
                  <td>{{ $att->exam->name ?? 'N/A' }}</td>
                  <td>{{ $att->subject->name ?? 'N/A' }}</td>
                  <td>{{ $att->seat_no ?? '-' }}</td>
                  <td>
                    @if($att->status === 'present')
                    <span class="badge bg-success">Present</span>
                    @elseif($att->status === 'absent')
                    <span class="badge bg-danger">Absent</span>
                    @else
                    <span class="badge bg-warning text-dark">Malpractice</span>
                    @endif
                  </td>
                  <td>{{ $att->marked_at ? \Carbon\Carbon::parse($att->marked_at)->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No attendance records found</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($attendances->hasPages())
        <div class="card-footer bg-white">
          {{ $attendances->appends(request()->query())->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
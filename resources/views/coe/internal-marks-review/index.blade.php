@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Internal Marks - Change Log</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">FA Marks Review</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      {{-- Filters --}}
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-filter text-primary me-2"></i>Filter Changes</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('coe.internal-marks-review.index') }}" method="GET">
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <label class="form-label">Course</label>
                <select name="course_id" class="form-select">
                  <option value="">All Courses</option>
                  @foreach($courses as $course)
                  <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                    {{ $course->course_title ?? '' }} {{ $course->course_code ? '('.$course->course_code.')' : '' }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select">
                  <option value="">All</option>
                  @foreach($semesters as $sem)
                  <option value="{{ $sem->id }}" {{ request('semester') == $sem->id ? 'selected' : '' }}>
                    {{ $sem->title }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
              </div>
              <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-search me-1"></i>Filter
                </button>
              </div>
              <div class="col-md-1">
                <a href="{{ route('coe.internal-marks-review.index') }}" class="btn btn-outline-secondary w-100">
                  <i class="fas fa-redo"></i>
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Summary --}}
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <div class="display-6 fw-bold text-warning">{{ $logs->total() }}</div>
              <small class="text-muted">Total Mark Changes</small>
            </div>
          </div>
        </div>
      </div>

      {{-- Log Table --}}
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-history text-warning me-2"></i>Mark Change History</h6>
        </div>
        <div class="card-body">
          @if($logs->count() > 0)
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Date/Time</th>
                  <th>Student</th>
                  <th>Roll No</th>
                  <th>Course</th>
                  <th>Semester</th>
                  <th>Old Mark</th>
                  <th>New Mark</th>
                  <th>Changed By</th>
                </tr>
              </thead>
              <tbody>
                @foreach($logs as $index => $log)
                <tr>
                  <td>{{ $logs->firstItem() + $index }}</td>
                  <td>
                    <small>{{ $log->created_at->format('d M Y') }}</small><br>
                    <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                  </td>
                  <td class="text-capitalize">
                    {{ $log->student->first_name ?? '' }} {{ $log->student->last_name ?? '' }}
                  </td>
                  <td>{{ $log->student->roll_no ?? '-' }}</td>
                  <td>
                    {{ $log->course->course_title ?? '' }}
                    @if($log->course && $log->course->course_code)
                    <br><small class="text-muted">{{ $log->course->course_code }}</small>
                    @endif
                  </td>
                  <td>{{ $log->semester }}</td>
                  <td>
                    <span class="badge bg-danger">{{ $log->old_mark ?? 'N/A' }}</span>
                  </td>
                  <td>
                    <span class="badge bg-success">{{ $log->new_mark }}</span>
                  </td>
                  <td>
                    <span class="fw-bold">{{ $log->changed_by_name ?? '' }}</span>
                    @if($log->changedByUser)
                    <br><small class="text-muted">{{ $log->changedByUser->email ?? '' }}</small>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $logs->appends(request()->query())->links() }}
          </div>
          @else
          <div class="text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p class="text-muted">No mark changes found.</p>
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
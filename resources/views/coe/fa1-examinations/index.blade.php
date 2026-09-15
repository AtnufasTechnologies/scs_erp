@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA1 Examinations</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">FA1 Quiz Monitor</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-question-circle me-2"></i>FA1 Examinations Monitor</h3>
                  <p class="text-white-50 mb-0">Dedicated CoE page to monitor FA1 quiz activity and open detailed reports.</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <span class="badge bg-light text-dark fs-6">Detailed Report Ready</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stats-card">
            <div class="card-body">
              <p class="text-muted mb-1">Total Quizzes</p>
              <h4 class="mb-0 fw-bold">{{ $totalQuizzes }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stats-card">
            <div class="card-body">
              <p class="text-muted mb-1">Upcoming</p>
              <h4 class="mb-0 fw-bold text-info">{{ $upcomingCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stats-card">
            <div class="card-body">
              <p class="text-muted mb-1">Live</p>
              <h4 class="mb-0 fw-bold text-success">{{ $liveCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stats-card">
            <div class="card-body">
              <p class="text-muted mb-1">Submitted Attempts</p>
              <h4 class="mb-0 fw-bold text-primary">{{ $totalSubmittedAttempts }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.fa1-examinations.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Search</label>
              <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Quiz title, course code, faculty name">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="live" {{ $status === 'live' ? 'selected' : '' }}>Live</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">From Date</label>
              <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">To Date</label>
              <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
            </div>
            <div class="col-md-2 d-grid">
              <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-2"></i>Apply</button>
            </div>
            <div class="col-md-2 d-grid">
              <a href="{{ route('coe.fa1-examinations.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold">FA1 Quiz List</h6>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Quiz</th>
                <th>Course</th>
                <th>Faculty</th>
                <th>Open / Close</th>
                <th>Questions</th>
                <th>Attempts</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($quizzes as $quiz)
              @php
              $facultyName = trim((string) ((optional($quiz->faculty)->FIRST_NAME ?? '') . ' ' . (optional($quiz->faculty)->MIDDLE_NAME ?? '') . ' ' . (optional($quiz->faculty)->LAST_NAME ?? '')));
              if ($facultyName === '') {
              $facultyName = optional($quiz->creator)->name ?? 'N/A';
              }
              $now = now();
              $isUpcoming = $quiz->open_at && $quiz->open_at->gt($now);
              $isCompleted = $quiz->close_at && $quiz->close_at->lte($now);
              $statusLabel = $isCompleted ? 'Completed' : ($isUpcoming ? 'Upcoming' : 'Live');
              $statusClass = $isCompleted ? 'bg-secondary' : ($isUpcoming ? 'bg-info text-dark' : 'bg-success');
              @endphp
              <tr>
                <td>{{ $quizzes->firstItem() + $loop->index }}</td>
                <td>
                  <div class="fw-semibold">{{ $quiz->title }}</div>
                  <small class="text-muted">{{ $quiz->subject->title ?? 'N/A' }}</small>
                </td>
                <td>{{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}</td>
                <td>{{ $facultyName }}</td>
                <td>
                  <div><small>Open: {{ optional($quiz->open_at)->format('d M Y h:i A') ?? 'N/A' }}</small></div>
                  <div><small>Close: {{ optional($quiz->close_at)->format('d M Y h:i A') ?? 'N/A' }}</small></div>
                </td>
                <td>{{ (int) ($quiz->questions_count ?? 0) }}</td>
                <td>{{ (int) ($quiz->submitted_attempts_count ?? 0) }}</td>
                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                <td>
                  <a href="{{ route('coe.fa1-examinations.show', $quiz->id) }}" class="btn btn-sm btn-outline-primary">Detailed Report</a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">No FA1 quizzes found for selected filters.</td>
              </tr>
              @endforelse
            </tbody>
          </table>

          @if($quizzes->hasPages())
          <div class="mt-2">
            {{ $quizzes->links('vendor.pagination.bootstrap-5') }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stats-card {
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-4px);
  }
</style>

@include('includes.footer')
@include('includes.header')

@if($role === 'principal')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA1 Quiz Results Monitor</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route($monitorIndexRoute) }}">Quiz Monitor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Results</li>
          </ol>
        </nav>
      </div>
    </div>
    @elseif($role === 'itcell')
    @include('admin.sidebar')

    <div class="container-fluid p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="mb-0">ITCell FA1 Quiz Results Monitor</h4>
          <small class="text-muted">Attempt insights for selected quiz</small>
        </div>
        <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Monitor</a>
      </div>
      @else
      @include('includes.dept-sidebar')
      <div class="main-content">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">FA1 Quiz Results Monitor</h4>
              <small class="text-muted">Attempt insights for selected quiz</small>
            </div>
            <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Monitor</a>
          </div>
          @endif

          <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                  <h5 class="mb-1 fw-bold">{{ $quiz->title }}</h5>
                  <div class="text-muted">
                    {{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}
                    | {{ $quiz->subject->title ?? 'N/A' }}
                  </div>
                </div>
                <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Quiz Monitor</a>
              </div>

              @php
              $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
              if ($facultyName === '') {
              $facultyName = optional($quiz->creator)->name ?? 'N/A';
              }
              $isCompleted = $quiz->close_at && $quiz->close_at->lt(now());
              @endphp

              <div class="row g-3 mt-2">
                <div class="col-md-3">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Faculty</small>
                    <strong>{{ $facultyName }}</strong>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Questions</small>
                    <strong>{{ (int) ($quiz->questions_count ?? 0) }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Attempted Students</small>
                    <strong>{{ (int) $attemptedStudentCount }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Avg Score</small>
                    <strong>{{ is_null($averageScore) ? 'N/A' : (int) round((float) $averageScore) }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ $isCompleted ? 'bg-secondary' : 'bg-success' }}">{{ $isCompleted ? 'Completed' : 'Live/Upcoming' }}</span>
                  </div>
                </div>
              </div>

              <div class="mt-3 text-muted small">
                Latest Submission: {{ $latestSubmissionAt ? \Carbon\Carbon::parse($latestSubmissionAt)->format('d M Y h:i A') : 'N/A' }}
              </div>
            </div>
          </div>

          <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white py-3">
              <h6 class="mb-0 fw-bold">Submitted Attempts</h6>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Register No</th>
                    <th>Attempt No</th>
                    <th>Score</th>
                    <th>Submitted At</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($attempts as $attempt)
                  <tr>
                    <td>{{ $attempts->firstItem() + $loop->index }}</td>
                    <td>{{ trim((string) (($attempt->student->first_name ?? '') . ' ' . ($attempt->student->last_name ?? ''))) ?: 'N/A' }}</td>
                    <td>{{ $attempt->student->roll_no ?? 'N/A' }}</td>
                    <td>{{ $attempt->student->register_no ?? 'N/A' }}</td>
                    <td>{{ (int) ($attempt->attempt_no ?? 0) }}</td>
                    <td>{{ is_null($attempt->score) ? 'N/A' : (int) round((float) $attempt->score) }}</td>
                    <td>{{ optional($attempt->submitted_at)->format('d M Y h:i A') }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted">No submitted attempts yet.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              @if($attempts->hasPages())
              <div class="mt-2">
                {{ $attempts->links() }}
              </div>
              @endif
            </div>
          </div>

          @if($role === 'principal')
  </main>
</div>
@elseif($role === 'itcell')
</div>
@else
</div>
</div>
@endif

@include('includes.footer')
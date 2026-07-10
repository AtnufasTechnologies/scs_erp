@include('includes.header')
@include('student.sidebar')

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Available Quizzes</h4>
      </div>

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

      @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Course</th>
                <th>Total Marks</th>
                <th>Open Time</th>
                <th>Attempts</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($quizzes as $quiz)
              @php
              $summary = $attemptSummary[$quiz->id] ?? null;
              $submittedCount = $summary['submitted_count'] ?? 0;
              $maxAttempts = $summary['max_attempts'] ?? 1;
              $hasInProgress = $summary['has_in_progress'] ?? false;
              $latestAttempt = $summary['latest_attempt'] ?? null;
              $canAttempt = $submittedCount < $maxAttempts || $hasInProgress;
                @endphp
                <tr>
                <td>{{ $quiz->title }}</td>
                <td>{{ $quiz->subject->title ?? 'N/A' }}</td>
                <td>{{ $quiz->course->course_title ?? 'N/A' }} ({{ $quiz->course->course_code ?? 'NA' }})</td>
                <td>{{ $quiz->total_marks }}</td>
                <td>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</td>
                <td>{{ $submittedCount }} / {{ $maxAttempts }}</td>
                <td>
                  @if($hasInProgress)
                  <span class="badge bg-warning text-dark">In Progress</span>
                  @elseif($latestAttempt && $latestAttempt->status === 'submitted')
                  <span class="badge bg-success">Last Score: {{ $latestAttempt->score }}</span>
                  @else
                  <span class="badge bg-primary">Not Started</span>
                  @endif
                </td>
                <td>
                  @if($canAttempt)
                  <a href="{{ route('student.quiz.show', $quiz->id) }}" class="btn btn-sm btn-primary">Attend Quiz</a>
                  @else
                  <button class="btn btn-sm btn-outline-secondary" disabled>No Attempts Left</button>
                  @endif
                </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No active quizzes available for your enrolled subjects.</td>
                </tr>
                @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('student.footer')
@include('includes.footer')
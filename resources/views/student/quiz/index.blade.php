@include('includes.header')

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid mt-4">
      @include('student.quiz.partials.brand_header')
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">FA1 Examination Portal</h5>
        <a href="{{ route('student.fa1.logout') }}" class="btn btn-outline-danger btn-sm">
          <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
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

          <div class="alert alert-warning py-2">
            <strong>Strict Rule : </strong> Navigating to any Tab, Browser, Notification, Keyboard Access, Split Screen on Mobile, Tablet or Laptop will result in <strong>Auto Submit</strong>.
          </div>

          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>FA1 Exam</th>
                <th>Subject</th>
                <th>Course</th>
                <th>Time Slot</th>
                <th>Total Marks</th>
                <th>Attempts</th>
                <th>Status</th>
                <!-- <th>Action</th> -->
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
                $notYetOpen=now()->lt($quiz->open_at);
                @endphp
                <tr>
                  <td>{{ $quiz->title }}</td>
                  <td>{{ $quiz->subject->title ?? 'N/A' }}</td>
                  <td>{{ $quiz->course->course_title ?? 'N/A' }} ({{ $quiz->course->course_code ?? 'NA' }})</td>
                  <td>
                    {{ optional($quiz->open_at)->format('d M Y h:i A') }}
                    -
                    {{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}
                  </td>
                  <td>{{ $quiz->total_marks }}</td>
                  <td>{{ $submittedCount }} / {{ $maxAttempts }}</td>
                  <td>
                    @if($hasInProgress)
                    <span class="badge bg-warning text-dark">In Progress</span>
                    @elseif($notYetOpen)
                    <span class="badge bg-info text-dark">Upcoming</span>
                    @elseif($latestAttempt && $latestAttempt->status === 'submitted')
                    <span class="badge bg-success">Last Score: {{ (int) round((float) $latestAttempt->score) }}</span>
                    @else
                    <span class="badge bg-primary">Not Started</span>
                    @endif
                  </td>
                  <td>
                    @if($canAttempt)
                    <a href="{{ route('student.fa1.lobby', $quiz->id) }}" class="btn btn-sm btn-primary">Select</a>
                    @else
                    <button class="btn btn-sm btn-outline-secondary" disabled>No Attempts Left</button>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No FA1 exams available in your Portal.</td>
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
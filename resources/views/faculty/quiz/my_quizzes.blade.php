@include('includes.header')

<style>
  :root {
    --quiz-accent: #0f4c81;
    --quiz-accent-soft: #e8f0f8;
    --quiz-border: #d9e1ea;
    --quiz-text-muted: #5e6b78;
  }

  .quiz-page .card {
    border: 1px solid var(--quiz-border);
    border-radius: 10px;
  }

  .quiz-page .card-header {
    background: linear-gradient(90deg, #f7f9fc 0%, #eef3f8 100%);
    border-bottom: 1px solid var(--quiz-border);
    padding: 0.75rem 1rem;
  }

  .quiz-page .table thead th {
    white-space: nowrap;
    font-size: 0.78rem;
    text-transform: uppercase;
    color: var(--quiz-text-muted);
  }

  .quiz-page .summary-strip {
    border: 1px solid var(--quiz-border);
    background: var(--quiz-accent-soft);
    border-radius: 8px;
    padding: 0.7rem 0.9rem;
  }
</style>

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content quiz-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Quiz</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.fa1.index') }}">Create FA1 Quiz</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Quizzes</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
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

      <div class="summary-strip d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h5 class="mb-0 fw-bold" style="color: var(--quiz-accent);">My FA1 Quizzes</h5>
          <small class="text-muted">Track created quizzes, attempts, and results.</small>
        </div>
        <a href="{{ route('faculty.fa1.index') }}" class="btn btn-primary btn-sm">Create New FA1 Quiz</a>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th class="text-light">Title</th>
                <th class="text-light">Subject</th>
                <th class="text-light">Course</th>
                <th class="text-light">Batch</th>
                <th class="text-light">Semester</th>
                <th class="text-light">Component</th>
                <th class="text-light">Total Marks</th>
                <th class="text-light">Open At</th>
                <th class="text-light">Close At</th>
                <th class="text-light">Questions</th>
                <th class="text-light">Attempts</th>
                <th class="text-light">Time</th>
                <th class="text-light">Start Delay</th>
                <th class="text-light">Shuffle</th>
                <th class="text-light">Reset Time</th>
                <th class="text-light">Results</th>

              </tr>
            </thead>
            <tbody>
              @forelse($quizzes as $quiz)
              <tr>
                <td>{{ $quiz->title }}</td>
                <td>{{ $quiz->subject->title ?? 'N/A' }}</td>
                <td>{{ $quiz->course->course_title ?? 'N/A' }}</td>
                <td>{{ $quiz->batchmaster->batch_name ?? 'N/A' }}</td>
                <td>{{ $quiz->semestermaster->title ?? 'N/A' }}</td>
                <td>{{ $quiz->ciaComponent->name ?? 'N/A' }}</td>
                <td>{{ $quiz->total_marks }}</td>
                <td>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</td>
                <td>{{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}</td>
                <td>{{ $quiz->questions_count }}</td>
                <td>{{ $quiz->submitted_attempts_count }}</td>
                <td>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</td>
                <td>{{ (int) ($quiz->pre_start_countdown_seconds ?? 10) }} sec</td>
                <td>
                  Q: {{ $quiz->shuffle_questions ? 'Yes' : 'No' }}<br>
                  O: {{ $quiz->shuffle_options ? 'Yes' : 'No' }}
                </td>
                <td>
                  <form method="POST" action="{{ route('faculty.fa1.timing.update', $quiz->id) }}" class="d-grid gap-1" style="min-width: 220px;">
                    @csrf
                    @method('PUT')
                    <input type="datetime-local" name="open_at" class="form-control form-control-sm" value="{{ optional($quiz->open_at)->format('Y-m-d\\TH:i') }}" required>
                    <input type="datetime-local" name="close_at" class="form-control form-control-sm" value="{{ $quiz->close_at ? optional($quiz->close_at)->format('Y-m-d\\TH:i') : '' }}">
                    <input type="number" name="time_limit_minutes" min="1" max="300" class="form-control form-control-sm" value="{{ $quiz->time_limit_minutes ?? '' }}" placeholder="Time limit (mins)">
                    <input type="number" name="pre_start_countdown_seconds" min="0" max="300" class="form-control form-control-sm" value="{{ (int) ($quiz->pre_start_countdown_seconds ?? 10) }}" placeholder="Start delay (sec)">
                    <button type="submit" class="btn btn-sm btn-warning">Reset Time</button>
                  </form>
                </td>
                <td>
                  <a href="{{ route('faculty.fa1.results', $quiz->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>

              </tr>
              @empty
              <tr>
                <td colspan="16" class="text-center text-muted">No quizzes created yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
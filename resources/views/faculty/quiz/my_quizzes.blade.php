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

  .quiz-page .quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1rem;
  }

  .quiz-page .quiz-item-card {
    border: 1px solid var(--quiz-border);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(15, 76, 129, 0.08);
    overflow: hidden;
    background: #fff;
  }

  .quiz-page .quiz-item-head {
    background: linear-gradient(90deg, #f7f9fc 0%, #eef3f8 100%);
    border-bottom: 1px solid var(--quiz-border);
    padding: 0.75rem 0.9rem;
  }

  .quiz-page .quiz-item-title {
    font-weight: 700;
    color: var(--quiz-accent);
    margin-bottom: 0.1rem;
  }

  .quiz-page .quiz-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem 0.75rem;
    font-size: 0.86rem;
    margin-bottom: 0.8rem;
  }

  .quiz-page .quiz-meta .k {
    color: var(--quiz-text-muted);
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.1rem;
  }

  .quiz-page .quiz-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.35rem;
  }

  .quiz-page .timing-form {
    border: 1px dashed var(--quiz-border);
    border-radius: 8px;
    padding: 0.6rem;
    background: #fcfdff;
  }

  @media (max-width: 575.98px) {
    .quiz-page .quiz-meta {
      grid-template-columns: 1fr;
    }
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

      @if($quizzes->isEmpty())
      <div class="card shadow-sm border-0">
        <div class="card-body text-center text-muted">No quizzes created yet.</div>
      </div>
      @else
      <div class="quiz-grid row">
        @foreach($quizzes as $quiz)
        <div class="col-lg-4">
          <div class="quiz-item-card">
            <div class="quiz-item-head">
              <div class="quiz-item-title">{{ $quiz->title }}</div>
              <small class="text-muted">{{ $quiz->ciaComponent->name ?? 'N/A' }}</small>
            </div>
            <div class="p-3">
              <div class="quiz-meta">
                <div>
                  <div class="k">Subject</div>
                  <div>{{ $quiz->subject->title ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="k">Course</div>
                  <div>{{ $quiz->course->course_title ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="k">Batch</div>
                  <div>{{ $quiz->batchmaster->batch_name ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="k">Semester</div>
                  <div>{{ $quiz->semestermaster->title ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="k">Total Marks</div>
                  <div>{{ $quiz->total_marks }}</div>
                </div>
                <div>
                  <div class="k">Questions / Attempts</div>
                  <div>{{ $quiz->questions_count }} / {{ $quiz->submitted_attempts_count }}</div>
                </div>
                <div>
                  <div class="k">Open At</div>
                  <div>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</div>
                </div>
                <div>
                  <div class="k">Close At</div>
                  <div>{{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}</div>
                </div>
                <div>
                  <div class="k">Time Limit</div>
                  <div>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</div>
                </div>
                <div>
                  <div class="k">Start Delay</div>
                  <div>{{ (int) ($quiz->pre_start_countdown_seconds ?? 10) }} sec</div>
                </div>
                <div>
                  <div class="k">Shuffle Questions</div>
                  <div>{{ $quiz->shuffle_questions ? 'Yes' : 'No' }}</div>
                </div>
                <div>
                  <div class="k">Shuffle Options</div>
                  <div>{{ $quiz->shuffle_options ? 'Yes' : 'No' }}</div>
                </div>
              </div>

              <div class="timing-form mb-2">
                <form method="POST" action="{{ route('faculty.fa1.timing.update', $quiz->id) }}" class="d-grid gap-1">
                  @csrf
                  @method('PUT')
                  <input type="datetime-local" name="open_at" class="form-control form-control-sm" value="{{ optional($quiz->open_at)->format('Y-m-d\\TH:i') }}" required>
                  <input type="datetime-local" name="close_at" class="form-control form-control-sm" value="{{ $quiz->close_at ? optional($quiz->close_at)->format('Y-m-d\\TH:i') : '' }}">
                  <input type="number" name="time_limit_minutes" min="1" max="300" class="form-control form-control-sm" value="{{ $quiz->time_limit_minutes ?? '' }}" placeholder="Time limit (mins)">
                  <input type="number" name="pre_start_countdown_seconds" min="0" max="300" class="form-control form-control-sm" value="{{ (int) ($quiz->pre_start_countdown_seconds ?? 10) }}" placeholder="Start delay (sec)">
                  <button type="submit" class="btn btn-sm btn-warning">Reset Time</button>
                </form>
              </div>

              <div class="quiz-actions">
                <a href="{{ route('faculty.fa1.results', $quiz->id) }}" class="btn btn-sm btn-success">View Results</a>
                <a href="{{ route('faculty.fa1.review', $quiz->id) }}" class="btn btn-sm btn-primary">Review Full Quiz</a>
                <a href="{{ route('faculty.fa1.questions.edit', $quiz->id) }}" class="btn btn-sm btn-dark">Edit Questions</a>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')
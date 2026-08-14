<style>
  .quiz-monitor {
    --qm-bg: #f5f7fb;
    --qm-card: #ffffff;
    --qm-border: #e4e9f2;
    --qm-title: #1d2736;
    --qm-muted: #637188;
    --qm-accent: #0f4c81;
    --qm-accent-soft: #e8f1fb;
    --qm-success-soft: #e7f7ef;
    --qm-success-text: #1f7a4f;
    --qm-shadow: 0 10px 30px rgba(16, 38, 74, 0.08);
  }

  .quiz-monitor .panel {
    background: linear-gradient(130deg, #fbfcff 0%, var(--qm-bg) 100%);
    border: 1px solid var(--qm-border);
    border-radius: 16px;
    box-shadow: var(--qm-shadow);
    overflow: hidden;
  }

  .quiz-monitor .panel-head {
    background: var(--qm-card);
    border-bottom: 1px solid var(--qm-border);
    padding: 1rem 1.1rem;
  }

  .quiz-monitor .panel-title {
    margin: 0;
    color: var(--qm-title);
    font-weight: 700;
    letter-spacing: 0.2px;
  }

  .quiz-monitor .metric-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--qm-accent-soft);
    color: var(--qm-accent);
  }

  .quiz-monitor .quiz-card {
    background: var(--qm-card);
    border: 1px solid var(--qm-border);
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(15, 39, 74, 0.06);
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .quiz-monitor .quiz-card-header {
    padding: 1rem 1rem 0.7rem;
    border-bottom: 1px dashed var(--qm-border);
  }

  .quiz-monitor .quiz-title {
    margin: 0;
    color: var(--qm-title);
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.35;
  }

  .quiz-monitor .quiz-meta {
    margin-top: 0.45rem;
    color: var(--qm-muted);
    font-size: 0.82rem;
  }

  .quiz-monitor .close-chip {
    display: inline-block;
    margin-top: 0.45rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    background: #fff3f0;
    color: #b54734;
    font-size: 0.76rem;
    font-weight: 700;
  }

  .quiz-monitor .quiz-body {
    padding: 0.95rem 1rem;
    flex: 1;
  }

  .quiz-monitor .info-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 0.5rem;
    font-size: 0.86rem;
    margin-bottom: 0.5rem;
  }

  .quiz-monitor .info-key {
    color: var(--qm-muted);
    font-weight: 600;
  }

  .quiz-monitor .info-val {
    color: var(--qm-title);
  }

  .quiz-monitor .schedule-box {
    border: 1px solid var(--qm-border);
    border-radius: 10px;
    background: #fcfdff;
    padding: 0.55rem 0.7rem;
    font-size: 0.82rem;
    margin-top: 0.6rem;
  }

  .quiz-monitor .question-toggle {
    width: 100%;
    border: 0;
    border-radius: 10px;
    background: var(--qm-success-soft);
    color: var(--qm-success-text);
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.45rem 0.6rem;
    text-align: left;
  }

  .quiz-monitor .question-list {
    margin: 0.6rem 0 0;
    padding-left: 1rem;
    font-size: 0.84rem;
    color: #2f3a4d;
  }

  .quiz-monitor .question-list li {
    margin-bottom: 0.35rem;
  }

  .quiz-monitor .empty-state {
    border: 1px dashed var(--qm-border);
    background: #ffffff;
    border-radius: 14px;
    padding: 2rem 1rem;
    text-align: center;
    color: var(--qm-muted);
  }

  @media (max-width: 576px) {
    .quiz-monitor .info-row {
      grid-template-columns: 1fr;
      gap: 0.2rem;
    }
  }
</style>

<section class="quiz-monitor mt-3">
  <div class="panel">
    <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="panel-title">Faculty Quiz Repository</h5>
        <small class="text-muted">Centralized faculty quiz insights</small>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="metric-chip">Total Quizzes: {{ $quizzes->total() }}</span>

        @if($canFilterDepartments ?? false)
        <form method="GET" action="{{ route('principal.quizzes.index') }}" class="d-flex gap-2">
          <select name="department" class="form-select form-select-sm" style="min-width: 240px;">
            <option value="">All Departments</option>
            @foreach($departmentOptions as $department)
            <option value="{{ $department }}" {{ $selectedDepartment === $department ? 'selected' : '' }}>{{ $department }}</option>
            @endforeach
          </select>
          <button class="btn btn-sm btn-primary" type="submit">Filter</button>
          @if($selectedDepartment !== '')
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('principal.quizzes.index') }}">Clear</a>
          @endif
        </form>
        @endif
      </div>
    </div>

    <div class="p-3">
      @if($quizzes->count())
      <div class="row g-3">
        @foreach($quizzes as $quiz)
        @php
        $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
        if ($facultyName === '') {
        $facultyName = optional($quiz->creator)->name ?? 'N/A';
        }
        $collapseId = 'quiz-questions-' . $quiz->id;
        @endphp

        <div class="col-12 col-lg-6">
          <article class="quiz-card">
            <div class="quiz-card-header">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <h6 class="quiz-title">{{ $quiz->title }}</h6>
                <span class="badge bg-light text-dark border">#{{ $quizzes->firstItem() + $loop->index }}</span>
              </div>
              <div class="quiz-meta">
                Total Marks: {{ $quiz->total_marks }}
                | Time Limit: {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}
              </div>
              <span class="close-chip">Closes: {{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}</span>
            </div>

            <div class="quiz-body">
              <div class="info-row">
                <div class="info-key">Faculty</div>
                <div class="info-val">{{ $facultyName }}</div>
              </div>
              <div class="info-row">
                <div class="info-key">Department</div>
                <div class="info-val">{{ $quiz->subject->title ?? 'N/A' }}</div>
              </div>
              <div class="info-row">
                <div class="info-key">Total Marks</div>
                <div class="info-val">{{ $quiz->total_marks }}</div>
              </div>
              <div class="info-row">
                <div class="info-key">Time Limit</div>
                <div class="info-val">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</div>
              </div>
              <div class="info-row">
                <div class="info-key">Course</div>
                <div class="info-val">{{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}</div>
              </div>

              <div class="schedule-box">
                <div><strong>Open:</strong> {{ optional($quiz->open_at)->format('d M Y h:i A') ?? 'N/A' }}</div>
                <div><strong>Close:</strong> {{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}</div>
              </div>

              <div class="mt-2">
                <button class="question-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                  Questions ({{ $quiz->questions_count }})
                </button>
                <div class="collapse" id="{{ $collapseId }}">
                  @if($quiz->questions->count())
                  <ol class="question-list">
                    @foreach($quiz->questions as $question)
                    <li>{{ $question->question_text }}</li>
                    @endforeach
                  </ol>
                  @else
                  <div class="text-muted mt-2" style="font-size: 0.84rem;">No questions found.</div>
                  @endif
                </div>
              </div>
            </div>
          </article>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <h6 class="mb-1">No quizzes found</h6>
        <small>Quizzes will appear here once faculty create them for the selected scope.</small>
      </div>
      @endif

      @if($quizzes->hasPages())
      <div class="mt-3">
        {{ $quizzes->links() }}
      </div>
      @endif
    </div>
  </div>
</section>
@include('includes.header')

<style>
  :root {
    --quiz-accent: #0f4c81;
    --quiz-border: #d9e1ea;
    --quiz-soft: #f5f8fc;
    --quiz-correct: #1f8b4c;
  }

  .quiz-review-page .card {
    border: 1px solid var(--quiz-border);
    border-radius: 10px;
  }

  .quiz-review-page .quiz-question-card {
    border-left: 4px solid var(--quiz-accent);
    background: #fff;
  }

  .quiz-review-page .quiz-option {
    border: 1px solid #e7edf5;
    border-radius: 8px;
    padding: 0.55rem 0.7rem;
    margin-bottom: 0.45rem;
    background: #fbfdff;
  }

  .quiz-review-page .quiz-option.correct {
    border-color: #b9e5c9;
    background: #f2fbf5;
  }

  .quiz-review-page .quiz-badge {
    background: var(--quiz-soft);
    border: 1px solid var(--quiz-border);
    color: #34495e;
    font-weight: 600;
  }
</style>

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content quiz-review-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Quiz</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.fa1.my-quizzes') }}">My Quizzes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Review Full Quiz</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h5 class="fw-bold mb-1">{{ $quiz->title }}</h5>
              <div class="text-muted small">
                {{ $quiz->subject->title ?? 'N/A' }} |
                {{ $quiz->course->course_code ?? 'N/A' }} - {{ $quiz->course->course_title ?? 'N/A' }} |
                {{ $quiz->batchmaster->batch_name ?? 'N/A' }} |
                {{ $quiz->semestermaster->title ?? 'N/A' }}
              </div>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('faculty.fa1.my-quizzes') }}" class="btn btn-outline-secondary btn-sm">Back</a>
              <a href="{{ route('faculty.fa1.questions.edit', $quiz->id) }}" class="btn btn-outline-warning btn-sm">Edit Questions</a>
            </div>
          </div>

          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge quiz-badge">Component: {{ $quiz->ciaComponent->name ?? 'N/A' }}</span>
            <span class="badge quiz-badge">Total Marks: {{ $quiz->total_marks }}</span>
            <span class="badge quiz-badge">Questions: {{ $quiz->questions->count() }}</span>
            <span class="badge quiz-badge">Time Limit: {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</span>
          </div>
        </div>
      </div>

      @forelse($quiz->questions as $question)
      <div class="card quiz-question-card shadow-sm border-0 mb-2">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-bold">Q{{ $loop->iteration }}. {{ $question->question_text }}</h6>
            <span class="badge bg-light text-dark border">Position: {{ $question->position ?? $loop->iteration }}</span>
          </div>

          @if(!empty($question->question_image))
          <div class="mb-2">
            <img src="{{ $question->question_image }}" alt="Question image" style="max-height:180px; max-width:100%; border-radius:6px; border:1px solid #dde6f1;">
          </div>
          @endif

          <div>
            @forelse($question->options as $option)
            <div class="quiz-option {{ $option->is_correct ? 'correct' : '' }}">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <strong>Option {{ $loop->iteration }}:</strong>
                  {{ $option->option_text }}
                </div>
                @if($option->is_correct)
                <span class="badge" style="background:#e9f8ef;color:var(--quiz-correct);border:1px solid #b9e5c9;">Correct</span>
                @endif
              </div>

              @if(!empty($option->option_image))
              <div class="mt-2">
                <img src="{{ $option->option_image }}" alt="Option image" style="max-height:140px; max-width:100%; border-radius:6px; border:1px solid #dde6f1;">
              </div>
              @endif
            </div>
            @empty
            <div class="text-muted">No options found for this question.</div>
            @endforelse
          </div>
        </div>
      </div>
      @empty
      <div class="alert alert-info">No questions added in this quiz yet.</div>
      @endforelse
    </div>
  </main>
</div>

@include('includes.footer')
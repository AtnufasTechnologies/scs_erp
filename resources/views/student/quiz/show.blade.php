@include('includes.header')
@include('student.sidebar')

@php
use Illuminate\Support\Facades\Storage;

$cloudDisk = config('filesystems.cloud', 's3');
$resolveImageUrl = function ($path) use ($cloudDisk) {
if (!$path) {
return null;
}

if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
return $path;
}

try {
return Storage::disk($cloudDisk)->url($path);
} catch (\Throwable $e) {
return asset('storage/' . $path);
}
};
@endphp

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid mt-4">
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <h4 class="fw-bold mb-1">{{ $quiz->title }}</h4>
          <p class="text-muted mb-2">Answer questions and submit before time expires.</p>
          <div class="d-flex gap-3 flex-wrap">
            <span class="badge bg-info text-dark">Total Marks: {{ $quiz->total_marks }}</span>
            <span class="badge bg-secondary">Questions: {{ count($questionItems) }}</span>
            <span class="badge bg-light text-dark">Open: {{ optional($quiz->open_at)->format('d M Y h:i A') }}</span>
            <span class="badge bg-dark">Attempt: {{ $attempt->attempt_no }} / {{ $maxAttempts }}</span>
            @if(!is_null($remainingSeconds))
            <span class="badge bg-danger" id="quizTimer" data-seconds="{{ $remainingSeconds }}">Time Left: --:--</span>
            @endif
          </div>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <form method="POST" action="{{ route('student.quiz.submit', $quiz->id) }}">
        @csrf
        <div class="card shadow-sm border-0">
          <div class="card-body">
            @foreach($questionItems as $index => $item)
            @php
            $question = $item['question'];
            $options = $item['options'];
            @endphp
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="fw-bold">Q{{ $index + 1 }}. {{ $question->question_text }}</h6>
              @if($question->question_image)
              <div class="mb-2">
                <img src="{{ $resolveImageUrl($question->question_image) }}" alt="Question image" class="img-fluid rounded" style="max-height:220px;">
              </div>
              @endif

              @foreach($options as $option)
              <div class="form-check mb-2">
                <input
                  class="form-check-input"
                  type="radio"
                  name="answers[{{ $question->id }}]"
                  id="q{{ $question->id }}_o{{ $option->id }}"
                  value="{{ $option->id }}"
                  data-question-id="{{ $question->id }}"
                  data-option-id="{{ $option->id }}"
                  @checked(($savedAnswers[$question->id] ?? null) == $option->id)
                required>
                <label class="form-check-label" for="q{{ $question->id }}_o{{ $option->id }}">
                  {{ $option->option_text }}
                </label>
                @if($option->option_image)
                <div class="mt-2 ms-4">
                  <img src="{{ $resolveImageUrl($option->option_image) }}" alt="Option image" class="img-fluid rounded" style="max-height:160px;">
                </div>
                @endif
              </div>
              @endforeach
            </div>
            @endforeach

            <button type="submit" class="btn btn-success">Submit Quiz</button>
            <a href="{{ route('student.quiz.index') }}" class="btn btn-outline-secondary">Back</a>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
  (function() {
    const timerEl = document.getElementById('quizTimer');
    const form = document.querySelector('form[action*="/submit"]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function formatTime(sec) {
      const mm = Math.floor(sec / 60).toString().padStart(2, '0');
      const ss = (sec % 60).toString().padStart(2, '0');
      return `${mm}:${ss}`;
    }

    if (timerEl) {
      let seconds = parseInt(timerEl.dataset.seconds || '0', 10);
      timerEl.textContent = `Time Left: ${formatTime(Math.max(0, seconds))}`;

      const tick = setInterval(() => {
        seconds -= 1;
        timerEl.textContent = `Time Left: ${formatTime(Math.max(0, seconds))}`;
        if (seconds <= 0) {
          clearInterval(tick);
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = 'auto_timeout';
          hidden.value = '1';
          form.appendChild(hidden);
          form.submit();
        }
      }, 1000);
    }

    document.querySelectorAll('input[type="radio"][data-question-id][data-option-id]').forEach((radio) => {
      radio.addEventListener('change', async function() {
        const questionId = this.dataset.questionId;
        const optionId = this.dataset.optionId;

        try {
          await fetch("{{ route('student.quiz.save-answer', $quiz->id) }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              question_id: parseInt(questionId, 10),
              option_id: parseInt(optionId, 10)
            })
          });
        } catch (e) {
          // Silent fail; final submit still records answers.
        }
      });
    });
  })();
</script>

@include('student.footer')
@include('includes.footer')
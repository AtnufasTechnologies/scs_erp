@include('includes.header')

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
      @include('student.quiz.partials.brand_header')
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

      <div class="alert alert-warning py-2">
        Exam mode is active: tab switching will auto-submit. On older iPhone Safari versions, compatibility mode is enabled to prevent browser crashes.
      </div>

      <form method="POST" action="{{ route('student.fa1.submit', $quiz->id) }}" id="quizExamForm">
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
                  @checked(($savedAnswers[$question->id] ?? null) == $option->id)>
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

            <button type="submit" class="btn btn-success" id="submitQuizBtn">Submit Quiz</button>
          </div>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
  (function() {
    const timerEl = document.getElementById('quizTimer');
    const form = document.getElementById('quizExamForm') || document.querySelector('form[action*="/submit"]');
    const submitBtn = document.getElementById('submitQuizBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const lockKey = 'fa1_exam_lock_{{ $quiz->id }}_{{ $attempt->id }}';
    const tabId = Math.random().toString(36).slice(2);
    const ua = navigator.userAgent || '';
    const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isSafariEngine = /Safari/i.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS|Chrome|Android/i.test(ua);
    const iosVersionMatch = ua.match(/OS\s(\d+)_/i);
    const iosMajorVersion = iosVersionMatch ? parseInt(iosVersionMatch[1], 10) : null;
    const useSafariCompatibilityMode = isIOS && isSafariEngine && iosMajorVersion !== null && iosMajorVersion <= 16;
    let hasSubmittedByRestriction = false;
    let hasCleanedUp = false;

    function formatTime(sec) {
      const mm = Math.floor(sec / 60).toString().padStart(2, '0');
      const ss = (sec % 60).toString().padStart(2, '0');
      return `${mm}:${ss}`;
    }

    function submitByRestriction(reason) {
      if (!form || hasSubmittedByRestriction) {
        return;
      }

      hasSubmittedByRestriction = true;
      cleanupExamMode();

      const hiddenTimeout = document.createElement('input');
      hiddenTimeout.type = 'hidden';
      hiddenTimeout.name = 'auto_timeout';
      hiddenTimeout.value = '1';
      form.appendChild(hiddenTimeout);

      const hiddenReason = document.createElement('input');
      hiddenReason.type = 'hidden';
      hiddenReason.name = 'violation_reason';
      hiddenReason.value = reason;
      form.appendChild(hiddenReason);

      form.submit();
    }

    async function requestExamFullscreen() {
      if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
        try {
          await document.documentElement.requestFullscreen();
        } catch (e) {
          // Browser may block fullscreen on some flows.
        }
      }
    }

    function cleanupExamMode() {
      if (hasCleanedUp) {
        return;
      }

      hasCleanedUp = true;
      sessionStorage.removeItem('fa1_force_fullscreen');

      try {
        const raw = localStorage.getItem(lockKey);
        if (raw) {
          const payload = JSON.parse(raw);
          if (payload?.tabId === tabId) {
            localStorage.removeItem(lockKey);
          }
        }
      } catch (e) {
        // Ignore cleanup failures.
      }

      if (document.fullscreenElement && document.exitFullscreen) {
        document.exitFullscreen().catch(() => {
          // Ignore fullscreen exit failure.
        });
      }
    }

    function writeLock() {
      localStorage.setItem(lockKey, JSON.stringify({
        tabId,
        ts: Date.now()
      }));
    }

    function hasActiveForeignTab() {
      const raw = localStorage.getItem(lockKey);
      if (!raw) {
        return false;
      }

      try {
        const parsed = JSON.parse(raw);
        if (!parsed || !parsed.tabId || !parsed.ts) {
          return false;
        }

        if (parsed.tabId === tabId) {
          return false;
        }

        return (Date.now() - Number(parsed.ts)) < 12000;
      } catch (e) {
        return false;
      }
    }

    if (hasActiveForeignTab()) {
      submitByRestriction('multiple_tab_open');
      return;
    }

    if (!useSafariCompatibilityMode) {
      requestExamFullscreen();
      if (sessionStorage.getItem('fa1_force_fullscreen') === '1') {
        requestExamFullscreen();
      }
    }

    let lockHeartbeat = null;
    let foregroundGuard = null;
    writeLock();

    lockHeartbeat = setInterval(() => {
      if (hasActiveForeignTab()) {
        submitByRestriction('multiple_tab_open');
        return;
      }

      writeLock();
    }, 2000);

    window.addEventListener('storage', function(event) {
      if (event.key !== lockKey || !event.newValue) {
        return;
      }

      try {
        const payload = JSON.parse(event.newValue);
        if (payload?.tabId && payload.tabId !== tabId) {
          submitByRestriction('multiple_tab_open');
        }
      } catch (e) {
        // Ignore malformed storage payload.
      }
    });

    document.addEventListener('visibilitychange', function() {
      if (document.hidden) {
        submitByRestriction('tab_switch_detected');
        return;
      }

      if (hasActiveForeignTab()) {
        submitByRestriction('multiple_tab_open');
      }
    });

    // iOS Safari can skip some focus/visibility events in specific app-switch flows.
    document.addEventListener('webkitvisibilitychange', function() {
      if (document.webkitHidden) {
        submitByRestriction('tab_switch_detected');
      }
    });

    window.addEventListener('pagehide', function() {
      submitByRestriction('tab_switch_detected');
    });

    window.addEventListener('freeze', function() {
      submitByRestriction('tab_switch_detected');
    });

    if (!useSafariCompatibilityMode) {
      window.addEventListener('blur', function() {
        submitByRestriction('window_focus_lost');
      });
    }

    foregroundGuard = setInterval(() => {
      if (document.hidden || document.webkitHidden || (typeof document.hasFocus === 'function' && !document.hasFocus())) {
        submitByRestriction('tab_switch_detected');
        return;
      }

      if (hasActiveForeignTab()) {
        submitByRestriction('multiple_tab_open');
      }
    }, 1000);

    window.addEventListener('beforeunload', function() {
      clearInterval(lockHeartbeat);
      clearInterval(foregroundGuard);
      cleanupExamMode();
    });

    if (form) {
      form.addEventListener('submit', function() {
        hasSubmittedByRestriction = true;
        cleanupExamMode();
      });
    }

    if (submitBtn && form) {
      submitBtn.addEventListener('click', function(event) {
        if (hasSubmittedByRestriction) {
          event.preventDefault();
          return;
        }

        event.preventDefault();
        const questionIds = new Set();
        const answeredIds = new Set();

        form.querySelectorAll('input[type="radio"][data-question-id]').forEach((radio) => {
          const qid = radio.dataset.questionId;
          if (!qid) {
            return;
          }

          questionIds.add(qid);
          if (radio.checked) {
            answeredIds.add(qid);
          }
        });

        const unansweredCount = Math.max(0, questionIds.size - answeredIds.size);
        if (unansweredCount > 0) {
          const shouldSubmit = confirm(`You still have ${unansweredCount} unanswered question(s). Submit anyway?`);
          if (!shouldSubmit) {
            return;
          }
        }

        hasSubmittedByRestriction = true;
        cleanupExamMode();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        form.submit();
      });
    }

    // Older iPhone Safari compatibility: avoid global keyboard lock to prevent crashes.
    if (!useSafariCompatibilityMode) {
      ['keydown', 'keypress', 'keyup'].forEach((eventName) => {
        document.addEventListener(eventName, function(event) {
          const target = event.target;
          const isInputLike = target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable);

          if (isInputLike || event.key === 'Tab') {
            return;
          }

          event.preventDefault();
          event.stopPropagation();
          event.stopImmediatePropagation();
          return false;
        }, true);
      });
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
          cleanupExamMode();
          form.submit();
        }
      }, 1000);
    }

    document.querySelectorAll('input[type="radio"][data-question-id][data-option-id]').forEach((radio) => {
      radio.addEventListener('change', async function() {
        const questionId = this.dataset.questionId;
        const optionId = this.dataset.optionId;

        try {
          await fetch("{{ route('student.fa1.save-answer', $quiz->id) }}", {
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
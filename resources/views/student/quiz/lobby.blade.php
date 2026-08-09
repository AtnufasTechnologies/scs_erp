@include('includes.header')

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid mt-4">
      @include('student.quiz.partials.brand_header')
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">FA1 Exam Waiting Room</h4>
        <a href="{{ route('student.fa1.index') }}" class="btn btn-outline-secondary btn-sm">Back to Portal</a>
      </div>

      @if(session('info'))
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="fw-bold mb-1">{{ $quiz->title }}</h5>
          <p class="text-muted mb-3">Questions will appear after you click Start Exam.</p>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <div class="border rounded p-2">
                <small class="text-muted d-block">Subject</small>
                <strong>{{ $quiz->subject->title ?? 'N/A' }}</strong>
              </div>
            </div>
            <div class="col-md-4">
              <div class="border rounded p-2">
                <small class="text-muted d-block">Course</small>
                <strong>{{ $quiz->course->course_title ?? 'N/A' }}</strong>
              </div>
            </div>
            <div class="col-md-4">
              <div class="border rounded p-2">
                <small class="text-muted d-block">Attempts</small>
                <strong>{{ $inProgressAttempt ? $inProgressAttempt->attempt_no : ($submittedCount + 1) }} / {{ $maxAttempts }}</strong>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-2">
                <small class="text-muted d-block">Time Slot</small>
                <strong>{{ optional($quiz->open_at)->format('d M Y h:i A') }} - {{ $quiz->close_at ? optional($quiz->close_at)->format('d M Y h:i A') : 'Until submitted' }}</strong>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-2">
                <small class="text-muted d-block">Exam Start</small>
                <strong>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</strong>
              </div>
            </div>
          </div>

          @if(!$hasRemainingAttempts)
          <div class="alert alert-warning mb-0">No remaining attempts for this FA1 exam.</div>
          @else
          <div class="alert alert-primary d-flex justify-content-between align-items-center flex-wrap gap-2" id="statusPanel">
            <span id="statusText">Checking exam slot...</span>
            <span class="badge bg-dark" id="slotTimer">--:--</span>
          </div>

          <form method="POST" action="{{ route('student.fa1.start', $quiz->id) }}" id="startExamForm">
            @csrf
            <button type="submit" class="btn btn-success" id="startExamBtn" disabled>Start Exam</button>
          </form>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  (function() {
    const ua = navigator.userAgent || '';
    const isSafari = /^((?!chrome|android).)*safari/i.test(ua) || /iPad|iPhone|iPod/.test(ua);

    const statusText = document.getElementById('statusText');
    const slotTimer = document.getElementById('slotTimer');
    const startBtn = document.getElementById('startExamBtn');
    const startForm = document.getElementById('startExamForm');

    if (!statusText || !slotTimer || !startBtn) {
      return;
    }

    let slotSeconds = parseInt('{{ max(0, (int) $secondsUntilOpen) }}', 10);

    function formatTime(totalSeconds) {
      const mm = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
      const ss = (totalSeconds % 60).toString().padStart(2, '0');
      return `${mm}:${ss}`;
    }

    function enableStart() {
      startBtn.disabled = false;
      statusText.textContent = 'You can start now. Click Start Exam.';
      slotTimer.textContent = '00:00';
    }

    async function enterFullscreenAndSubmit(event) {
      if (!startForm || !startBtn || startBtn.disabled) {
        return;
      }

      event.preventDefault();

      if (!isSafari) {
        try {
          if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
          }
        } catch (e) {
          // Ignore if browser blocks fullscreen; exam page will retry.
        }

        sessionStorage.setItem('fa1_force_fullscreen', '1');
      }

      startForm.submit();
    }

    if (slotSeconds <= 0) {
      enableStart();
    } else {
      statusText.textContent = 'Please wait. Exam slot not opened yet.';
      slotTimer.textContent = formatTime(slotSeconds);

      const slotTick = setInterval(() => {
        slotSeconds -= 1;
        slotTimer.textContent = formatTime(Math.max(0, slotSeconds));

        if (slotSeconds <= 0) {
          clearInterval(slotTick);
          enableStart();
        }
      }, 1000);
    }

    if (startForm) {
      startForm.addEventListener('submit', enterFullscreenAndSubmit);
    }
  })();
</script>

@include('student.footer')
@include('includes.footer')
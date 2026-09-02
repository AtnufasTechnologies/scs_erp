@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">ITCell FA1 Quiz Purge Utility</h4>
      <small class="text-muted">Select a quiz date, choose quizzes, and purge all related attempt and marks data.</small>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('itcell.quizzes.index') }}" class="btn btn-outline-secondary btn-sm">Back to Quiz Monitor</a>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>
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

  @if($errors->any())
  <div class="alert alert-danger" role="alert">
    <strong>Please fix the following:</strong>
    <ul class="mb-0 mt-2">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('itcell.quizzes.purge') }}" class="row g-2 align-items-end">
        <div class="col-lg-3 col-md-4">
          <label class="form-label mb-1">Quiz Start Date (open_at)</label>
          <input type="date" name="quiz_date" value="{{ $quizDateInput ?? '' }}" class="form-control" required>
        </div>
        <div class="col-lg-3 col-md-4">
          <button class="btn btn-primary" type="submit">Load Quizzes</button>
        </div>
      </form>
    </div>
  </div>

  @if(($quizDate ?? null) !== null)
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h6 class="mb-0 fw-bold">Quizzes on {{ \Carbon\Carbon::parse($quizDate)->format('d M Y') }}</h6>
        <small class="text-muted">Select one or more quizzes and purge all linked records.</small>
      </div>
      <span class="badge bg-dark">Found: {{ (int) $quizzes->count() }}</span>
    </div>

    <div class="card-body">
      @if($quizzes->isEmpty())
      <div class="text-center text-muted py-3">No quizzes found for selected date.</div>
      @else
      <form method="POST" action="{{ route('itcell.quizzes.purge-selected') }}" id="purgeForm" onsubmit="return confirm('This will permanently delete selected quizzes and all related data. Continue?');">
        @csrf
        <input type="hidden" name="quiz_date" value="{{ $quizDate }}">

        <div class="alert alert-warning border-0 mb-3" role="alert">
          <strong>Warning:</strong> This operation is irreversible and removes quiz attempts, answers, permissions, question records, CIA marks, FA marks, and quiz rows for selected quizzes.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 56px;">
                  <input type="checkbox" id="selectAllQuizzes" class="form-check-input">
                </th>
                <th>#</th>
                <th>Quiz</th>
                <th>Course</th>
                <th>Subject</th>
                <th>Faculty</th>
                <th>Open At</th>
                <th>Submitted Attempts</th>
                <th>Questions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($quizzes as $quiz)
              @php
              $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
              if ($facultyName === '') {
              $facultyName = optional($quiz->creator)->name ?? 'N/A';
              }
              @endphp
              <tr>
                <td>
                  <input
                    type="checkbox"
                    name="quiz_ids[]"
                    value="{{ (int) $quiz->id }}"
                    class="form-check-input quiz-selector">
                </td>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="fw-semibold">{{ $quiz->title }}</div>
                  <small class="text-muted">ID: {{ (int) $quiz->id }}</small>
                </td>
                <td>{{ $quiz->course->course_code ?? 'N/A' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}</td>
                <td>{{ $quiz->subject->title ?? 'N/A' }}</td>
                <td>{{ $facultyName }}</td>
                <td>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</td>
                <td>{{ (int) ($quiz->submitted_attempts_count ?? 0) }}</td>
                <td>{{ (int) ($quiz->questions_count ?? 0) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="row g-2 mt-2 align-items-end">
          <div class="col-lg-3 col-md-5">
            <label class="form-label mb-1">Type DELETE to confirm</label>
            <input type="text" name="confirm_text" class="form-control" placeholder="DELETE" required>
          </div>
          <div class="col-lg-3 col-md-4">
            <button type="submit" id="purgeButton" class="btn btn-danger" disabled>Purge Selected Quizzes</button>
          </div>
          <div class="col-lg-6 col-md-12">
            <small class="text-muted" id="selectedCounter">Selected: 0</small>
          </div>
        </div>
      </form>
      @endif
    </div>
  </div>
  @endif
</div>

<script>
  (function() {
    const selectAll = document.getElementById('selectAllQuizzes');
    const selectors = Array.from(document.querySelectorAll('.quiz-selector'));
    const purgeButton = document.getElementById('purgeButton');
    const selectedCounter = document.getElementById('selectedCounter');

    if (!selectors.length || !purgeButton || !selectedCounter) {
      return;
    }

    const refreshState = function() {
      const selectedCount = selectors.filter(function(el) {
        return el.checked;
      }).length;

      selectedCounter.textContent = 'Selected: ' + selectedCount;
      purgeButton.disabled = selectedCount < 1;

      if (selectAll) {
        selectAll.checked = selectedCount > 0 && selectedCount === selectors.length;
        selectAll.indeterminate = selectedCount > 0 && selectedCount < selectors.length;
      }
    };

    selectors.forEach(function(el) {
      el.addEventListener('change', refreshState);
    });

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        const shouldSelect = !!selectAll.checked;
        selectors.forEach(function(el) {
          el.checked = shouldSelect;
        });
        refreshState();
      });
    }

    refreshState();
  })();
</script>

@include('includes.footer')
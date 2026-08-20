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

  .quiz-monitor .analytics-strip {
    border: 1px solid var(--qm-border);
    border-radius: 12px;
    background: #ffffff;
    padding: 0.7rem 0.8rem;
    margin-bottom: 0.9rem;
  }

  .quiz-monitor .analytics-title {
    color: var(--qm-title);
    font-size: 0.84rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
  }

  .quiz-monitor .analytics-sub {
    color: var(--qm-muted);
    font-size: 0.76rem;
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
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="panel">
    <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="panel-title">Faculty Quiz Repository Centralized</h5>
        <small class="text-muted">Grouped faculty quiz analytics and monitoring controls</small>
      </div>
    </div>

    <div class="p-3 border-bottom bg-white">
      <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <span class="metric-chip">Total Quizzes: {{ $quizzes->total() }}</span>
        <span class="metric-chip">Completed: {{ (int) ($statusCounts['completed'] ?? 0) }}</span>
        <span class="metric-chip">Unique Students (By Start Time): {{ (int) ($totalUniqueStudentsByStartTime ?? 0) }}</span>
      </div>

      @php
      $indexRouteName = $monitorIndexRoute
      ?? (($role ?? '') === 'principal'
      ? 'principal.quizzes.index'
      : (($role ?? '') === 'itcell' ? 'itcell.quizzes.index' : 'department.quizzes.index'));
      @endphp

      <form method="GET" action="{{ route($indexRouteName) }}" class="d-flex gap-2 align-items-center flex-wrap mt-2">

        <div class="row">
          <div class="col-lg-3">
            <input
              type="text"
              name="course_code"
              value="{{ $selectedCourseCode ?? '' }}"
              class="form-control form-control-sm"
              style="min-width: 180px;"
              placeholder="Course Code">

          </div>
          <div class="col-lg-3"> @if($canFilterDepartments ?? false)
            <select name="department" class="form-select form-select-sm" style="min-width: 220px;">
              <option value="">All Departments</option>
              @foreach($departmentOptions as $department)
              <option value="{{ $department }}" {{ $selectedDepartment === $department ? 'selected' : '' }}>{{ $department }}</option>
              @endforeach
            </select>
            @endif
          </div>

          <div class="col-lg-3">
            <select name="status" class="form-select form-select-sm" style="min-width: 160px;">
              <option value="all" {{ ($selectedStatus ?? 'all') === 'all' ? 'selected' : '' }}>All Quizzes</option>
              <option value="upcoming" {{ ($selectedStatus ?? 'all') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
              <option value="live" {{ ($selectedStatus ?? 'all') === 'live' ? 'selected' : '' }}>Ongoing</option>
              <option value="completed" {{ ($selectedStatus ?? 'all') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
          </div>

          <div class="col-lg-3 mb-3">
            <select name="group_by" class="form-select form-select-sm" style="min-width: 220px;">
              <option value="none" {{ ($groupBy ?? 'none') === 'none' ? 'selected' : '' }}>Normal View</option>
              <option value="start_time" {{ ($groupBy ?? 'none') === 'start_time' ? 'selected' : '' }}>Group By Start Time (All Quizzes)</option>
            </select>

          </div>

          <div class="col-lg-4">
            <input
              type="date"
              name="start_date"
              value="{{ $startDate ?? '' }}"
              class="form-control form-control-sm"
              style="min-width: 150px;"
              title="Quiz Start Date">
          </div>
          <div class="col-lg-4">
            <button class="btn btn-sm btn-primary" type="submit">Apply</button>
            @if(($selectedDepartment ?? '') !== '' || ($selectedStatus ?? 'all') !== 'all' || ($selectedCourseCode ?? '') !== '' || ($startDate ?? '') !== '' || ($groupBy ?? 'none') !== 'none')
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($indexRouteName) }}">Clear</a>
            @endif
          </div>
        </div>
      </form>
    </div>

    <div class="p-3">
      @if(($startTimeAnalytics ?? collect())->isNotEmpty())
      <div class="analytics-strip">
        <div class="analytics-title">Start Time Analytics</div>
        <div class="analytics-sub">Unique students grouped by quiz start date and time.</div>
        <div class="d-flex gap-2 flex-wrap mt-2">
          @foreach($startTimeAnalytics as $metric)
          <span class="metric-chip">{{ $metric['start_at_label'] }}: {{ (int) ($metric['unique_students'] ?? 0) }}</span>
          @endforeach
        </div>
      </div>
      @endif

      @if(($groupBy ?? 'none') === 'start_time')
      @if(($groupedQuizzesByStartTime ?? collect())->isNotEmpty())
      <div class="d-flex flex-column gap-3">
        @foreach($groupedQuizzesByStartTime as $group)
        <div class="analytics-strip mb-0">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="analytics-title mb-0">Start Time: {{ $group['start_at_label'] }}</div>
            <span class="metric-chip">Quizzes: {{ (int) ($group['quiz_count'] ?? 0) }}</span>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Quiz</th>
                  <th>Faculty</th>
                  <th>Course</th>
                  <th>Status</th>
                  <th>Expected Students</th>
                  <th>Attempts</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach(($group['quizzes'] ?? collect()) as $quiz)
                @php
                $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
                if ($facultyName === '') {
                $facultyName = optional($quiz->creator)->name ?? 'N/A';
                }
                $now = now();
                $isUpcoming = $quiz->open_at && $quiz->open_at->gt($now);
                $isCompleted = $quiz->close_at && $quiz->close_at->lte($now);
                $statusLabel = $isCompleted ? 'Completed' : ($isUpcoming ? 'Upcoming' : 'Ongoing');
                $statusBadge = $isCompleted ? 'bg-secondary' : ($isUpcoming ? 'bg-info text-dark' : 'bg-success');
                @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <div class="fw-bold">{{ $quiz->title }}</div>
                    <small class="text-muted">{{ optional($quiz->open_at)->format('d M Y h:i A') ?? 'N/A' }}</small>
                  </td>
                  <td>{{ $facultyName }}</td>
                  <td>{{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}</td>
                  <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                  <td>{{ (int) ($quiz->expected_students_count ?? 0) }}</td>
                  <td>{{ (int) ($quiz->submitted_attempts_count ?? 0) }}</td>
                  <td>
                    @php
                    $resultsRouteName = $monitorResultsRoute
                    ?? (($role ?? '') === 'principal'
                    ? 'principal.quizzes.results'
                    : (($role ?? '') === 'itcell' ? 'itcell.quizzes.results' : 'department.quizzes.results'));
                    @endphp
                    <a href="{{ route($resultsRouteName, $quiz->id) }}" class="btn btn-sm btn-outline-primary">Results</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="empty-state">
        <h6 class="mb-1">No grouped quizzes found</h6>
        <small>Try changing filters to see grouped quizzes by start time.</small>
      </div>
      @endif
      @elseif($quizzes->count())
      <div class="row g-3">
        @foreach($quizzes as $quiz)
        @php
        $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
        if ($facultyName === '') {
        $facultyName = optional($quiz->creator)->name ?? 'N/A';
        }
        $now = now();
        $isUpcoming = $quiz->open_at && $quiz->open_at->gt($now);
        $isCompleted = $quiz->close_at && $quiz->close_at->lte($now);
        $statusLabel = $isCompleted ? 'Completed' : ($isUpcoming ? 'Upcoming' : 'Ongoing');
        $statusBadge = $isCompleted ? 'bg-secondary' : ($isUpcoming ? 'bg-info text-dark' : 'bg-success');
        $collapseId = 'quiz-questions-' . $quiz->id;
        @endphp

        <div class="col-12 col-lg-6">
          <article class="quiz-card">
            <div class="quiz-card-header">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <h6 class="quiz-title">{{ $quiz->title }}</h6>
                <div class="d-flex align-items-center gap-1">
                  <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                  <span class="badge bg-light text-dark border">#{{ $quizzes->firstItem() + $loop->index }}</span>
                </div>
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
                <div class="info-key">Expected Students</div>
                <div class="info-val">{{ (int) ($quiz->expected_students_count ?? 0) }}</div>
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
                @php
                $resultsRouteName = $monitorResultsRoute
                ?? (($role ?? '') === 'principal'
                ? 'principal.quizzes.results'
                : (($role ?? '') === 'itcell' ? 'itcell.quizzes.results' : 'department.quizzes.results'));
                @endphp
                <a href="{{ route($resultsRouteName, $quiz->id) }}" class="btn btn-sm btn-outline-primary mb-2 w-100">
                  View Results ({{ (int) ($quiz->submitted_attempts_count ?? 0) }} Attempts)
                </a>
                @if($showQuestionsInMonitor ?? true)
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
                @endif
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

      @if(($groupBy ?? 'none') !== 'start_time' && $quizzes->hasPages())
      <div class="mt-3">
        {{ $quizzes->links('vendor.pagination.bootstrap-5') }}
      </div>
      @endif
    </div>
  </div>
</section>
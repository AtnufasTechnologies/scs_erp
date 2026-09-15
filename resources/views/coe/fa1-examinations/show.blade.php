@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA1 Examinations</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.fa1-examinations.index') }}">FA1 Quiz Monitor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detailed Report</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card gradient-coe shadow-lg border-0 mb-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h4 class="text-white fw-bold mb-1">{{ $quiz->title }}</h4>
              <p class="text-white-50 mb-0">
                {{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}
                | {{ $quiz->subject->title ?? 'N/A' }}
              </p>
            </div>
            <a href="{{ route('coe.fa1-examinations.index') }}" class="btn btn-light">Back to Monitor</a>
          </div>
        </div>
      </div>

      @php
      $facultyName = trim((string) ((optional($quiz->faculty)->FIRST_NAME ?? '') . ' ' . (optional($quiz->faculty)->MIDDLE_NAME ?? '') . ' ' . (optional($quiz->faculty)->LAST_NAME ?? '')));
      if ($facultyName === '') {
      $facultyName = optional($quiz->creator)->name ?? 'N/A';
      }
      @endphp

      <div class="row mb-4 g-3">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <small class="text-muted">Faculty</small>
              <h6 class="mb-0 mt-1">{{ $facultyName }}</h6>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <small class="text-muted">Questions</small>
              <h4 class="mb-0 mt-1">{{ (int) ($quiz->questions_count ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <small class="text-muted">Attempted Students</small>
              <h4 class="mb-0 mt-1">{{ (int) $attemptedStudentCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <small class="text-muted">Average Score</small>
              <h4 class="mb-0 mt-1">{{ is_null($averageScore) ? 'N/A' : number_format((float) $averageScore, 1) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <small class="text-muted">Score Range</small>
              <h6 class="mb-0 mt-1">{{ is_null($lowestScore) ? 'N/A' : number_format((float) $lowestScore, 1) }} - {{ is_null($highestScore) ? 'N/A' : number_format((float) $highestScore, 1) }}</h6>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
          <div class="row g-2 small">
            <div class="col-md-4"><strong>Open:</strong> {{ optional($quiz->open_at)->format('d M Y h:i A') ?? 'N/A' }}</div>
            <div class="col-md-4"><strong>Close:</strong> {{ optional($quiz->close_at)->format('d M Y h:i A') ?? 'N/A' }}</div>
            <div class="col-md-4"><strong>Latest Submission:</strong> {{ $latestSubmissionAt ? \Carbon\Carbon::parse($latestSubmissionAt)->format('d M Y h:i A') : 'N/A' }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold">Question-wise Accuracy Report</h6>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th>Q. No.</th>
                <th>Question</th>
                <th>Total Answers</th>
                <th>Correct Answers</th>
                <th>Accuracy %</th>
              </tr>
            </thead>
            <tbody>
              @forelse($questionAccuracy as $item)
              <tr>
                <td>{{ $item['position'] }}</td>
                <td>{{ $item['question_text'] }}</td>
                <td>{{ $item['total_answers'] }}</td>
                <td>{{ $item['correct_answers'] }}</td>
                <td>
                  <span class="badge {{ $item['accuracy'] >= 70 ? 'bg-success' : ($item['accuracy'] >= 40 ? 'bg-warning text-dark' : 'bg-danger') }}">
                    {{ number_format((float) $item['accuracy'], 1) }}%
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">No question-level analytics available yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold">Submitted Attempts</h6>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Register No</th>
                <th>Attempt No</th>
                <th>Score</th>
                <th>Submitted At</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attempts as $attempt)
              <tr>
                <td>{{ $attempts->firstItem() + $loop->index }}</td>
                <td>{{ trim((string) (($attempt->student->first_name ?? '') . ' ' . ($attempt->student->last_name ?? ''))) ?: 'N/A' }}</td>
                <td>{{ $attempt->student->roll_no ?? 'N/A' }}</td>
                <td>{{ $attempt->student->register_no ?? 'N/A' }}</td>
                <td>{{ (int) ($attempt->attempt_no ?? 0) }}</td>
                <td>{{ is_null($attempt->score) ? 'N/A' : number_format((float) $attempt->score, 1) }}</td>
                <td>{{ optional($attempt->submitted_at)->format('d M Y h:i A') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No submitted attempts found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>

          @if($attempts->hasPages())
          <div class="mt-2">
            {{ $attempts->links('vendor.pagination.bootstrap-5') }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')
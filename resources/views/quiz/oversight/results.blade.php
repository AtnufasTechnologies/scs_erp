@include('includes.header')

@if($role === 'principal')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA1 Quiz Results Monitor</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route($monitorIndexRoute) }}">Quiz Monitor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Results</li>
          </ol>
        </nav>
      </div>
    </div>
    @elseif($role === 'itcell')
    @include('admin.sidebar')

    <div class="container-fluid p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="mb-0">ITCell FA1 Quiz Results Monitor</h4>
          <small class="text-muted">Attempt insights for selected quiz</small>
        </div>
        <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Monitor</a>
      </div>
      @else
      @include('includes.dept-sidebar')
      <div class="main-content">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">FA1 Quiz Results Monitor</h4>
              <small class="text-muted">Attempt insights for selected quiz</small>
            </div>
            <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Monitor</a>
          </div>
          @endif

          @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif

          @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          @endif

          <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                  <h5 class="mb-1 fw-bold">{{ $quiz->title }}</h5>
                  <div class="text-muted">
                    {{ $quiz->course->course_code ?? '' }}{{ $quiz->course ? ' - ' : '' }}{{ $quiz->course->course_title ?? 'N/A' }}
                    | {{ $quiz->subject->title ?? 'N/A' }}
                  </div>
                </div>
                <a href="{{ route($monitorIndexRoute) }}" class="btn btn-outline-secondary btn-sm">Back to Quiz Monitor</a>
              </div>

              @php
              $facultyName = trim((string) (optional($quiz->faculty)->full_name ?? ''));
              if ($facultyName === '') {
              $facultyName = optional($quiz->creator)->name ?? 'N/A';
              }
              $isCompleted = $quiz->close_at && $quiz->close_at->lt(now());
              @endphp

              <div class="row g-3 mt-2">
                <div class="col-md-3">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Faculty</small>
                    <strong>{{ $facultyName }}</strong>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Questions</small>
                    <strong>{{ (int) ($quiz->questions_count ?? 0) }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Attempted Students</small>
                    <strong>{{ (int) $attemptedStudentCount }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Avg Score</small>
                    <strong>{{ is_null($averageScore) ? 'N/A' : (int) round((float) $averageScore) }}</strong>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="border rounded p-2 bg-light h-100">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge {{ $isCompleted ? 'bg-secondary' : 'bg-success' }}">{{ $isCompleted ? 'Completed' : 'Live/Upcoming' }}</span>
                  </div>
                </div>
              </div>

              <div class="mt-3 text-muted small">
                Latest Submission: {{ $latestSubmissionAt ? \Carbon\Carbon::parse($latestSubmissionAt)->format('d M Y h:i A') : 'N/A' }}
              </div>
            </div>
          </div>

          @if($role === 'itcell')
          <div id="emergency-access" class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white py-3">
              <h6 class="mb-0 fw-bold">Emergency Student Access Override</h6>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('itcell.quizzes.allow-attempts', $quiz->id) }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                  <label class="form-label fw-bold">Set Max Attempts</label>
                  <input type="number" min="1" max="10" name="max_attempts" class="form-control" value="1" required>
                </div>
                <div class="col-md-9">
                  <label class="form-label fw-bold">Roll Numbers</label>
                  <textarea name="roll_numbers" class="form-control" rows="2" placeholder="Example: USL2025EDMC001, USL2025ENMC026" required></textarea>
                  <small class="text-muted">Separate values with comma, space, or new line. This bypasses roster visibility for emergencies.</small>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary">Grant Emergency Access</button>
                </div>
              </form>

              <hr class="my-4">

              <form method="POST" action="{{ route('itcell.quizzes.revoke-attempts', $quiz->id) }}" class="row g-3" onsubmit="return confirm('Reset emergency access for the provided roll numbers on this quiz?');">
                @csrf
                <div class="col-md-9">
                  <label class="form-label fw-bold">Reset by Roll Numbers</label>
                  <textarea name="roll_numbers" class="form-control" rows="2" placeholder="Example: USL2025EDMC001, USL2025ENMC026" required></textarea>
                  <small class="text-muted">Only this quiz will be affected.</small>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-outline-danger w-100">Reset Selected Access</button>
                </div>
              </form>

              <form method="POST" action="{{ route('itcell.quizzes.revoke-attempts', $quiz->id) }}" class="mt-3" onsubmit="return confirm('This will remove ALL emergency access rows for this quiz. Continue?');">
                @csrf
                <input type="hidden" name="reset_all" value="1">
                <button type="submit" class="btn btn-danger">Reset All Emergency Access (This Quiz)</button>
              </form>
            </div>
          </div>
          @endif

          <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white py-3">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">Quiz Question Sheet</h6>
                @if($role === 'itcell')
                <a href="{{ route('itcell.quizzes.question-sheet.export', $quiz->id) }}" class="btn btn-sm btn-success">
                  <i class="fas fa-file-excel me-1"></i>Download Full Quiz Sheet
                </a>
                @endif
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <span class="badge bg-primary">Questions: {{ (int) ($quiz->questions_count ?? 0) }}</span>
                <span class="badge bg-success">Attempted Students: {{ (int) $attemptedStudentCount }}</span>
              </div>

              @if($role === 'itcell')
              <form method="POST" action="{{ route('itcell.quizzes.question-sheet.import', $quiz->id) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
                @csrf
                <div class="col-md-8">
                  <label class="form-label fw-bold mb-1">Upload Corrected Full Quiz Sheet</label>
                  <input type="file" name="question_sheet" class="form-control" accept=".xlsx,.xls,.csv" required>
                  <small class="text-muted">Download the sheet, update question text/options/correct answer, and re-upload to apply and recalculate submitted scores.</small>
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-primary">Upload and Recalculate</button>
                </div>
              </form>
              @endif
            </div>
          </div>

          <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white py-3">
              <h6 class="mb-0 fw-bold">Submitted Attempts</h6>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-bordered align-middle">
                <thead>
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
                    <td>{{ is_null($attempt->score) ? 'N/A' : (int) round((float) $attempt->score) }}</td>
                    <td>{{ optional($attempt->submitted_at)->format('d M Y h:i A') }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted">No submitted attempts yet.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>

              @if($attempts->hasPages())
              <div class="mt-2">
                {{ $attempts->links() }}
              </div>
              @endif
            </div>
          </div>

          @if($role === 'principal')
  </main>
</div>
@elseif($role === 'itcell')
</div>
@else
</div>
</div>
@endif

@include('includes.footer')
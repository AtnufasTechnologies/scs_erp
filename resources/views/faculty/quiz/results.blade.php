@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Quiz Results</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.fa1.index') }}">FA1</a></li>
            <li class="breadcrumb-item active" aria-current="page">Results</li>
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

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h5 class="fw-bold mb-1">{{ $quiz->title }}</h5>
          <div class="text-muted mb-2">
            {{ $quiz->subject->title ?? 'N/A' }}
            | {{ $quiz->course->course_title ?? 'N/A' }}
            | Max Marks: {{ $quiz->total_marks }}
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-info text-dark">Component Group: {{ $quiz->cia_group_id }}</span>
            <span class="badge bg-secondary">Time Limit: {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</span>
            <span class="badge bg-primary">Start Delay: {{ (int) ($quiz->pre_start_countdown_seconds ?? 10) }} sec</span>
            <span class="badge bg-dark">Shuffle Q: {{ $quiz->shuffle_questions ? 'Yes' : 'No' }}</span>
            <span class="badge bg-dark">Shuffle O: {{ $quiz->shuffle_options ? 'Yes' : 'No' }}</span>
            <span class="badge bg-warning text-dark">Expected Students (Course + Batch + Semester): {{ $expectedStudentCount ?? 0 }}</span>
            <span class="badge bg-success">Attempted Students: {{ $attemptedStudentCount ?? 0 }}</span>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">Allow Additional Attempts (Selected Students)</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('faculty.fa1.allow-attempts', $quiz->id) }}" method="POST">
            @csrf
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">Set Max Attempts</label>
                <input type="number" min="2" max="10" name="max_attempts" class="form-control" value="2" required>
              </div>
              <div class="col-md-8">
                <label class="form-label fw-bold">Select Students (Mulit Select)</label>
                <select class="select-multiple" name="student_ids[]" multiple size="8" required>
                  @foreach($enrolledStudents as $student)
                  @php
                  $used = (int) ($attemptCounts[$student->id] ?? 0);
                  $allowed = (int) ($permissions[$student->id] ?? 1);
                  @endphp
                  <option value="{{ $student->id }}">
                    {{ $student->roll_no }} - {{ $student->first_name }} {{ $student->last_name }}
                    (Used: {{ $used }}, Allowed: {{ $allowed }})
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary">Update Attempt Permission</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold">Eligible Students and Attempt Status</h5>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <a href="{{ route('faculty.fa1.results.export', $quiz->id) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Export to Excel
              </a>
              @if(($studentRosterSource ?? 'primary') === 'fallback')
              <span class="badge bg-warning text-dark">Roster Source: Fallback (Course/Semester)</span>
              @else
              <span class="badge bg-success">Roster Source: Primary (Subject + Program Mapping)</span>
              @endif
            </div>
          </div>
        </div>
        <div class="card-body table-responsive">
          @if(($studentRosterSource ?? 'primary') === 'fallback')
          <div class="alert alert-warning py-2">
            Subject-program mapping returned no records for this quiz, so student list is populated using course/semester mapping fallback.
          </div>
          @endif
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Register No</th>
                <th>Attempts Used</th>
                <th>Allowed Attempts</th>
                <th>Attempt Status</th>
                <th>Latest Result</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enrolledStudents as $student)
              @php
              $used = (int) ($attemptCounts[$student->id] ?? 0);
              $allowed = (int) ($permissions[$student->id] ?? 1);
              $latest = $latestSubmittedByStudent[$student->id] ?? null;
              @endphp
              <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>{{ $student->roll_no ?? 'N/A' }}</td>
                <td>{{ $student->register_no ?? 'N/A' }}</td>
                <td>{{ $used }}</td>
                <td>{{ $allowed }}</td>
                <td>
                  @if($used > 0)
                  <span class="badge bg-success">Attempted</span>
                  @else
                  <span class="badge bg-secondary">Not Attempted</span>
                  @endif
                </td>
                <td>
                  @if($latest)
                  Score: {{ (int) round((float) $latest->score) }}
                  <br>
                  <small class="text-muted">Submitted: {{ optional($latest->submitted_at)->format('d M Y h:i A') }}</small>
                  @else
                  <span class="text-muted">No submission yet</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No enrolled students found for this quiz mapping.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">Submitted Attempts</h5>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>Student</th>
                <th>Roll No</th>
                <th>Register No</th>
                <th>Attempt No</th>
                <th>Raw Score</th>
                <th>Total Questions</th>
                <th>Score</th>
                <th>Submitted At</th>
                <th>Mode</th>
              </tr>
            </thead>
            <tbody>
              @forelse($attempts as $attempt)
              <tr>
                <td>{{ $attempt->student->first_name ?? '' }} {{ $attempt->student->last_name ?? '' }}</td>
                <td>{{ $attempt->student->roll_no ?? 'N/A' }}</td>
                <td>{{ $attempt->student->register_no ?? 'N/A' }}</td>
                <td>{{ $attempt->attempt_no }}</td>
                <td>{{ $attempt->raw_score }}</td>
                <td>{{ $attempt->total_questions }}</td>
                <td>{{ (int) round((float) $attempt->score) }}</td>
                <td>{{ optional($attempt->submitted_at)->format('d M Y h:i A') }}</td>
                <td>
                  @if($attempt->submitted_by_timeout)
                  <span class="badge bg-warning text-dark">Auto (timeout)</span>
                  @else
                  <span class="badge bg-success">Manual</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted">No quiz submissions yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
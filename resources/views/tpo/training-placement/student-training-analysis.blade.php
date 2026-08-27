@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Training Analysis</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}">Student Opt-Ins</a></li>
            <li class="breadcrumb-item active" aria-current="page">Report</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h5 class="mb-1 fw-bold">
              {{ $studentMeta ? trim((string) (($studentMeta->first_name ?? '') . ' ' . ($studentMeta->last_name ?? ''))) : ($user->name ?? 'Student') }}
            </h5>
            <div class="small text-muted">
              Roll: {{ ($studentMeta->roll_no ?? $user->roll_no) ?: 'N/A' }} |
              Register: {{ $studentMeta->register_no ?? 'N/A' }} |
              Department: {{ $studentMeta->department_name ?? 'N/A' }} |
              Campus: {{ $studentMeta->campus_name ?? 'N/A' }}
            </div>
            <div class="small text-muted">Email: {{ $user->email ?: 'N/A' }}</div>
          </div>
          <a href="{{ route('tpo.training-placement.student-opt-in-forms.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Student Opt-Ins
          </a>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Total Attempts</div>
              <h4 class="mb-0 fw-bold">{{ $summary['total_attempts'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Completed Attempts</div>
              <h4 class="mb-0 fw-bold text-success">{{ $summary['completed_attempts'] }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Average Score</div>
              <h4 class="mb-0 fw-bold text-primary">{{ $summary['avg_score_pct'] }}%</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="text-muted small">Highest Score</div>
              <h4 class="mb-0 fw-bold text-info">{{ $summary['highest_score_pct'] }}%</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Attempt-wise Training Analysis</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0 align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Training Program</th>
                  <th>Total Score</th>
                  <th>Max Score</th>
                  <th>Score %</th>
                  <th>Status</th>
                  <th>Completed At</th>
                </tr>
              </thead>
              <tbody>
                @foreach($attempts as $index => $attempt)
                @php
                $maxScore = (int) $attempt->max_score;
                $scorePercent = $maxScore > 0 ? round((((int) $attempt->total_score) / $maxScore) * 100, 2) : 0;
                @endphp
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ optional($attempt->trainingProgram)->title ?: 'N/A' }}</td>
                  <td>{{ (int) $attempt->total_score }}</td>
                  <td>{{ $maxScore }}</td>
                  <td>
                    <span class="badge {{ $scorePercent >= 75 ? 'bg-success' : ($scorePercent >= 40 ? 'bg-warning text-dark' : 'bg-danger') }}">
                      {{ $scorePercent }}%
                    </span>
                  </td>
                  <td>
                    @if($attempt->completed_at)
                    <span class="badge bg-success">Completed</span>
                    @else
                    <span class="badge bg-secondary">In Progress</span>
                    @endif
                  </td>
                  <td>{{ $attempt->completed_at ? $attempt->completed_at->format('d M Y h:i A') : 'N/A' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
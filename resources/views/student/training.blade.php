@include('includes.header')

@include('student.sidebar')

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid py-4">
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

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Training Portal</h4>
            <p class="mb-0 text-muted">View training availability and status.</p>
          </div>
          <a href="{{ route('student.console.placement') }}" class="btn btn-outline-secondary">
            <i class="fas fa-briefcase me-1"></i>Go to Placement Status
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-semibold">Status</div>
              <div class="small text-muted">Training access depends on your placement approval and training content availability.</div>
            </div>
            @if($tpStatus === 'approved')
            <span class="badge bg-success">Active</span>
            @elseif($tpStatus === 'rejected')
            <span class="badge bg-danger">Blocked (Rejected)</span>
            @elseif($tpStatus === 'in_review')
            <span class="badge bg-warning text-dark">Waiting Approval</span>
            @else
            <span class="badge bg-secondary">Not Active</span>
            @endif
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mt-3">
        <div class="card-header bg-transparent fw-bold">Applicable Trainings</div>
        <div class="card-body p-0">
          @if(($applicableTrainings ?? collect())->isEmpty())
          <div class="p-3 text-muted small">No active trainings are assigned to your role at the moment.</div>
          @else
          <div class="accordion" id="applicableTrainingAccordion">
            @foreach($applicableTrainings as $index => $training)
            @php
            $latestAttempt = $training->attempts->first();
            $isCompleted = !empty($latestAttempt?->completed_at);
            $isInProgress = $latestAttempt && !$isCompleted;
            $resourceCount = (int) ($training->resources_count ?? 0);
            $questionCount = (int) ($training->survey_questions_count ?? 0);
            $hasTrainingContent = $resourceCount > 0 && $questionCount > 0;
            @endphp
            <div class="accordion-item border-0 border-bottom">
              <h2 class="accordion-header" id="headingTraining{{ $training->id }}">
                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTraining{{ $training->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapseTraining{{ $training->id }}">
                  <div class="d-flex w-100 justify-content-between align-items-center gap-2 pe-2">
                    <span class="fw-semibold">{{ $training->title }}</span>
                    @if($isCompleted)
                    <span class="badge bg-success">Completed</span>
                    @elseif($isInProgress)
                    <span class="badge bg-warning text-dark">In Progress</span>
                    @else
                    <span class="badge bg-secondary">Not Started</span>
                    @endif
                  </div>
                </button>
              </h2>
              <div id="collapseTraining{{ $training->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="headingTraining{{ $training->id }}" data-bs-parent="#applicableTrainingAccordion">
                <div class="accordion-body">
                  <div class="small text-muted mb-2">{{ $training->description ?: 'No description provided.' }}</div>
                  <div class="small mb-2"><strong>Applicable For:</strong> {{ $training->targetRoles->pluck('role_name')->filter()->map(fn($r) => ucfirst(str_replace('-', ' ', $r)))->implode(', ') ?: 'N/A' }}</div>
                  <div class="small mb-3">
                    <span class="me-3"><strong>Resources:</strong> {{ $resourceCount }}</span>
                    <span><strong>Questions:</strong> {{ $questionCount }}</span>
                  </div>
                  <div>
                    @if($tpStatus === 'approved' && $hasTrainingContent)
                    <a href="{{ route('student.fa1.index') }}" class="btn btn-sm btn-outline-success">
                      <i class="fas fa-play me-1"></i>Available
                    </a>
                    @elseif($tpStatus === 'approved')
                    <span class="badge bg-light text-dark border">Unavailable: Missing content</span>
                    @else
                    <span class="badge bg-light text-dark border">Locked until approval</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('student.footer')
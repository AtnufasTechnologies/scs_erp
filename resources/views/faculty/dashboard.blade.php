@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Faculty Home</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!--start welcome section-->
    <div class="row">
      <div class="col-12">
        <div class="card gradient-purple shadow-lg border-0 mb-4">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h4 class="text-white fw-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h4>
                <p class="text-white-50 mb-0">You have a productive day ahead. Check your subjects, student feedback, and pending leaves.</p>
              </div>
              <div class="col-md-4 text-md-end">
                <div class="display-5 text-white fw-bold">{{ date('d M Y') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end welcome section-->

    <!--start stats cards-->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-book text-primary" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Assigned Subjects</p>
                <h4 class="mb-0 fw-bold">{{ $totalSubjectsCount ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-star text-warning" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Avg Feedback Score</p>
                <h4 class="mb-0 fw-bold">4.5/5.0</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-calendar-check text-success" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Leave Days Taken</p>
                <h4 class="mb-0 fw-bold">{{ $leaveStats['days_taken'] ?? 0 }} days</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-tasks text-info" style="font-size: 1.8rem;"></i>
              </div>
              <div>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Tasks Completed</p>
                <h4 class="mb-0 fw-bold">42%</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end stats cards-->

    <div class="row">
      <!-- Assigned Subjects with Completion -->
      <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-primary"></i>Assigned Subjects</h6>
              </div>
              <div class="col-auto">
                <a href="{{ route('faculty.subjects') }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View All</a>
              </div>
            </div>
          </div>
          <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            @forelse($assignedSubjects as $index => $subject)
            @php
            $progressColors = ['primary', 'info', 'warning', 'success', 'danger'];
            $progressClass = 'bg-success';

            // Determine status based on completion
            if ($subject['completion_percentage'] >= 80) {
            $statusBadge = '<span class="badge bg-success">Active</span>';
            } elseif ($subject['completion_percentage'] >= 50) {
            $statusBadge = '<span class="badge bg-warning">In Progress</span>';
            } else {
            $statusBadge = '<span class="badge bg-danger">Pending</span>';
            }
            @endphp

            <div class="{{ $loop->last ? '' : 'mb-4' }}">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">{{ $subject['semester'] }} | {{ $subject['batch'] }}</h6>
                  <small class="text-muted"> {{ $subject['course_code'] }} - {{ $subject['course_title'] }}</small>
                </div>
                {!! $statusBadge !!}
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $progressClass }}" role="progressbar"
                  style="width: {{ $subject['completion_percentage'] }}%;"
                  aria-valuenow="{{ $subject['completion_percentage'] }}"
                  aria-valuemin="0"
                  aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">
                Completion: {{ $subject['completion_percentage'] }}% |
                Units: {{ $subject['completed_units'] }}/{{ $subject['total_units'] }} completed
              </small>
            </div>
            @empty
            <div class="text-center py-5">
              <i class="fas fa-book-open text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
              <p class="text-muted mt-3 mb-0">No subjects assigned yet</p>
              <small class="text-muted">Please contact your department for subject assignments</small>
            </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Student Feedback -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-comments me-2 text-warning"></i>Student Feedback</h6>
          </div>
          <div class="card-body">
            <div class="text-center mb-4">
              <div class="display-6 text-warning fw-bold">4.5</div>
              <div class="star-rating mb-2">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star-half-alt text-warning"></i>
              </div>
              <small class="text-muted">Based on 215 responses</small>
            </div>

            <!-- Feedback Categories -->
            <div class="feedback-category mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold" style="font-size: 0.9rem;">Teaching Quality</span>
                <span class="text-warning fw-bold">4.7/5</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-warning" style="width: 94%;"></div>
              </div>
            </div>

            <div class="feedback-category mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold" style="font-size: 0.9rem;">Course Content</span>
                <span class="text-info fw-bold">4.4/5</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-info" style="width: 88%;"></div>
              </div>
            </div>

            <div class="feedback-category mb-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold" style="font-size: 0.9rem;">Accessibility</span>
                <span class="text-success fw-bold">4.6/5</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-success" style="width: 92%;"></div>
              </div>
            </div>

            <div class="feedback-category">
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold" style="font-size: 0.9rem;">Communication</span>
                <span class="text-primary fw-bold">4.3/5</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" style="width: 86%;"></div>
              </div>
            </div>

            <hr>

            <div class="mt-3">
              <a href="javascript:;" class="btn btn-sm btn-outline-primary w-100">
                <i class="fas fa-chart-bar me-2"></i>View Detailed Report
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Leaves Applied -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-danger"></i>Leaves Applied & Sanctioned</h6>
              </div>
              <div class="col-auto">
                <a href="{{ route('faculty.leave.create') }}" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Apply Leave</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- Casual Leave -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold text-primary">
                    <i class="fas fa-umbrella-beach me-1"></i>Casual Leave
                  </h6>
                  <small class="text-muted">You have {{ max(0, 10 - $casualLeaves) }} days remaining</small>
                </div>
                <span class="badge bg-primary rounded-pill">CL</span>
              </div>
              <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-primary" style="width: {{ min(100, ($casualLeaves / 10) * 100) }}%; border-radius: 10px;"></div>
              </div>
              <small class="text-muted d-block mt-2">
                Used: {{ $casualLeaves }}/10 days
                @if($casualLeaves > 0)
                | {{ number_format(($casualLeaves / 10) * 100, 0) }}% utilized
                @endif
              </small>
            </div>

            <!-- Sick Leave -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold text-danger">
                    <i class="fas fa-notes-medical me-1"></i>Sick Leave
                  </h6>
                  <small class="text-muted">Unlimited</small>
                </div>
                <span class="badge bg-danger rounded-pill">SL</span>
              </div>
              <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-danger bg-gradient" style="width: {{ min(100, ($sickLeaves / 15) * 100) }}%; border-radius: 10px;"></div>
              </div>
              <small class="text-muted d-block mt-2">
                Used: {{ $sickLeaves }} days | No limit applicable
              </small>
            </div>

            <!-- Earned Leave -->
            <div class="{{ $recentLeaves->count() > 0 ? 'mb-4' : '' }}">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold text-success">
                    <i class="fas fa-award me-1"></i>Earned Leave
                  </h6>
                  <small class="text-muted">You have {{ max(0, 25 - $earnedLeaves) }} days remaining</small>
                </div>
                <span class="badge bg-success rounded-pill">EL</span>
              </div>
              <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-success" style="width: {{ min(100, ($earnedLeaves / 25) * 100) }}%; border-radius: 10px;"></div>
              </div>
              <small class="text-muted d-block mt-2">
                Used: {{ $earnedLeaves }}/25 days
                @if($earnedLeaves > 0)
                | {{ number_format(($earnedLeaves / 25) * 100, 0) }}% utilized
                @endif
              </small>
            </div>

            <!-- Recent Leave Applications -->
            @if($recentLeaves->count() > 0)
            <div class="mt-4 pt-3 border-top">
              <h6 class="mb-3 fw-bold text-muted" style="font-size: 0.85rem;">
                <i class="fas fa-clock me-1"></i>RECENT APPLICATIONS
              </h6>
              @foreach($recentLeaves as $leave)
              <div class="leave-item p-2 border rounded mb-2 {{ $leave->status == 'pending' ? 'bg-warning bg-opacity-10' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="badge bg-{{ $leave->leave_type_badge }} badge-sm">
                        {{ $leave->leave_type_name }}
                      </span>
                      <span class="badge bg-{{ $leave->status_badge }} badge-sm">
                        {{ ucfirst($leave->status) }}
                      </span>
                    </div>
                    <small class="fw-bold d-block">
                      {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}
                      <span class="text-muted">({{ $leave->total_days }} {{ $leave->total_days > 1 ? 'days' : 'day' }})</span>
                    </small>
                    <small class="text-muted">{{ Str::limit($leave->reason, 50) }}</small>
                  </div>
                </div>
              </div>
              @endforeach
              <a href="{{ route('faculty.leave.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                <i class="fas fa-list me-1"></i>View All Applications
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Work Diary / Activity Log -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-transparent border-bottom py-3">
            <div class="row align-items-center">
              <div class="col">
                <h6 class="mb-0 fw-bold"><i class="fas fa-pen-square me-2 text-info"></i>Work Diary</h6>
              </div>
              <div class="col-auto">
                <a href="{{ route('faculty.workdiary') }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View All</a>
              </div>
            </div>
          </div>
          <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            @forelse($workDiaryEntries as $index => $entry)
            @php
            $colors = ['primary', 'success', 'warning', 'info', 'danger'];
            $colorClass = $colors[$index % count($colors)];
            $daysDiff = \Carbon\Carbon::parse($entry->date)->diffInDays(now());
            if ($daysDiff == 0) {
            $timeAgo = 'Today';
            } elseif ($daysDiff == 1) {
            $timeAgo = 'Yesterday';
            } else {
            $timeAgo = $daysDiff . ' days ago';
            }
            @endphp
            <div class="activity-item mb-4 pb-3 {{ $loop->last ? '' : 'border-bottom' }}">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-{{ $colorClass }}"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">{{ $entry->description }}</h6>
                    <small class="text-muted">{{ $timeAgo }}</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">
                    @if($entry->subject_name)
                    <strong>{{ $entry->subject_name }}</strong> |
                    @endif
                    Hour {{ $entry->hour }}
                    @if($entry->methodology)
                    | {{ $entry->methodology }}
                    @endif
                  </p>
                  <div class="d-flex gap-2">
                    @if($entry->subject_name)
                    <span class="badge bg-primary">{{ $entry->subject_name }}</span>
                    @endif
                    @if($entry->class_type)
                    <span class="badge bg-light text-dark">{{ ucfirst($entry->class_type) }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            @empty
            <div class="text-center py-5">
              <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
              <p class="text-muted mt-3 mb-0">No work diary entries found</p>
              <a href="{{ url('faculty/work-diary') }}" class="btn btn-sm btn-primary mt-3">Create Your First Entry</a>
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

  </main>
  <!--end page content-->
</div>

<style>
  .gradient-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stats-card {
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
  }

  .bg-gradient-primary {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  }

  .bg-gradient-info {
    background: linear-gradient(90deg, #00d4ff 0%, #0099ff 100%);
  }

  .bg-gradient-warning {
    background: linear-gradient(90deg, #ffa94d 0%, #ff7a45 100%);
  }

  .bg-gradient-success {
    background: linear-gradient(90deg, #48c774 0%, #00a86b 100%);
  }

  .bg-gradient-danger {
    background: linear-gradient(90deg, #ff6b6b 0%, #ee5a6f 100%);
  }

  .timeline-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 3px;
  }

  .activity-timeline {
    position: relative;
    min-width: 30px;
  }

  .activity-item {
    transition: all 0.3s ease;
  }

  .activity-item:hover {
    padding-left: 10px;
    background-color: rgba(0, 0, 0, 0.02);
  }

  .card {
    transition: all 0.3s ease;
  }

  .card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  }

  .leave-item {
    transition: all 0.3s ease;
  }

  .leave-item:hover {
    background-color: rgba(255, 255, 255, 1) !important;
    border-color: #e0e0e0 !important;
  }

  .star-rating {
    letter-spacing: 3px;
  }

  @media (max-width: 768px) {
    .card-body {
      padding: 1.25rem 0.75rem;
    }
  }
</style>

@include('includes.footer')
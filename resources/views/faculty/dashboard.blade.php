@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content faculty-dashboard">

    <!--start welcome banner-->
    <div class="welcome-banner mb-4">
      <div class="row align-items-center">
        <div class="col-lg-8 col-md-7">
          <div class="welcome-content">
            <h2 class="welcome-title">Welcome back, {{ Auth::user()->name }}</h2>
            <p class="welcome-subtitle">Here's what's happening with your work today</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
          <div class="date-display">
            <div class="date-label">Today</div>
            <div class="date-value">{{ date('d M Y') }}</div>
            <div class="date-day">{{ date('l') }}</div>
          </div>
        </div>
      </div>
    </div>
    <!--end welcome banner-->

    <!--start key metrics-->
    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="metric-card">
          <div class="metric-icon subjects">
            <i class="fas fa-book"></i>
          </div>
          <div class="metric-content">
            <div class="metric-label">Assigned Subjects</div>
            <div class="metric-value">{{ $totalSubjectsCount ?? 0 }}</div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="metric-card">
          <div class="metric-icon feedback">
            <i class="fas fa-star"></i>
          </div>
          <div class="metric-content">
            <div class="metric-label">Feedback Score</div>
            <div class="metric-value">4.5 <span class="metric-unit">/5.0</span></div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="metric-card">
          <div class="metric-icon leave">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="metric-content">
            <div class="metric-label">Leave Days Used</div>
            <div class="metric-value">{{ $leaveStats['days_taken'] ?? 0 }} <span class="metric-unit">days</span></div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="metric-card">
          <div class="metric-icon activities">
            <i class="fas fa-clipboard-list"></i>
          </div>
          <div class="metric-content">
            <div class="metric-label">Active Duties</div>
            <div class="metric-value">{{ ($eventControllerActivities->count() + $invigilationDuties->count()) }}</div>
          </div>
        </div>
      </div>
    </div>
    <!--end key metrics-->

    <!--start main content area-->
    <div class="row g-4">

      <!-- LEFT COLUMN: Academic Content -->
      <div class="col-lg-8">

        <!-- Assigned Subjects Section -->
        <div class="content-card mb-4">
          <div class="card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title-custom"><i class="fas fa-graduation-cap me-2"></i>Assigned Subjects</h5>
                <p class="card-subtitle-custom">Track your course completion progress</p>
              </div>
              <a href="{{ route('faculty.subjects') }}" class="btn-custom-outline">
                <i class="fas fa-external-link-alt me-1"></i>View All
              </a>
            </div>
          </div>
          <div class="card-body-custom subjects-list">
            @forelse($assignedSubjects as $subject)
            <div class="subject-item">
              <div class="subject-header">
                <div class="subject-info">
                  <h6 class="subject-name">{{ $subject['course_code'] }} - {{ $subject['course_title'] }}</h6>
                  <div class="subject-meta">
                    <span class="meta-badge"><i class="fas fa-layer-group me-1"></i>{{ $subject['semester'] }}</span>
                    <span class="meta-badge"><i class="fas fa-users me-1"></i>{{ $subject['batch'] }}</span>
                  </div>
                </div>
                @php
                if ($subject['completion_percentage'] >= 80) {
                $statusClass = 'status-success';
                $statusText = 'On Track';
                } elseif ($subject['completion_percentage'] >= 50) {
                $statusClass = 'status-warning';
                $statusText = 'In Progress';
                } else {
                $statusClass = 'status-danger';
                $statusText = 'Needs Attention';
                }
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
              </div>
              <div class="progress-container">
                <div class="progress-bar-custom">
                  <div class="progress-fill" style="width: {{ $subject['completion_percentage'] }}%"></div>
                </div>
                <div class="progress-info">
                  <span class="progress-text">{{ $subject['completion_percentage'] }}% Complete</span>
                  <span class="progress-units">{{ $subject['completed_units'] }}/{{ $subject['total_units'] }} units</span>
                </div>
              </div>
            </div>
            @empty
            <div class="empty-state">
              <i class="fas fa-book-open"></i>
              <p>No subjects assigned yet</p>
            </div>
            @endforelse
          </div>
        </div>

        <!-- Work Diary Section -->
        <div class="content-card">
          <div class="card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title-custom"><i class="fas fa-journal-whills me-2"></i>Recent Work Diary</h5>
                <p class="card-subtitle-custom">Your latest teaching activities</p>
              </div>
              <a href="{{ route('faculty.workdiary') }}" class="btn-custom-outline">
                <i class="fas fa-plus me-1"></i>Add Entry
              </a>
            </div>
          </div>
          <div class="card-body-custom timeline-container">
            @forelse($workDiaryEntries->take(5) as $index => $entry)
            @php
            $daysDiff = \Carbon\Carbon::parse($entry->date)->diffInDays(now());
            if ($daysDiff == 0) {
            $timeAgo = 'Today';
            } elseif ($daysDiff == 1) {
            $timeAgo = 'Yesterday';
            } else {
            $timeAgo = $daysDiff . ' days ago';
            }
            @endphp
            <div class="timeline-item">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <div class="timeline-header">
                  <h6 class="timeline-title">{{ $entry->description }}</h6>
                  <span class="timeline-time">{{ $timeAgo }}</span>
                </div>
                <div class="timeline-details">
                  @if($entry->subject_name)
                  <span class="detail-tag">{{ $entry->subject_name }}</span>
                  @endif
                  <span class="detail-text">Hour {{ $entry->hour }}</span>
                  @if($entry->methodology)
                  <span class="detail-text">{{ $entry->methodology }}</span>
                  @endif
                </div>
              </div>
            </div>
            @empty
            <div class="empty-state">
              <i class="fas fa-clipboard-list"></i>
              <p>No work diary entries yet</p>
              <a href="{{ url('faculty/work-diary') }}" class="btn-custom-primary mt-2">Create Entry</a>
            </div>
            @endforelse
          </div>
        </div>

        <!-- Detailed Activities Section -->
        <div class="row g-4 mt-2">
          <div class="col-12">
            <div class="content-card">
              <div class="card-header-custom">
                <h5 class="card-title-custom"><i class="fas fa-tasks me-2"></i>Detailed Activities Overview</h5>
                <p class="card-subtitle-custom">All your assigned duties and departmental activities</p>
              </div>
              <div class="card-body-custom p-0">

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs custom-tabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#eventDuties">
                      <i class="fas fa-calendar-check me-2"></i>Event Controller
                      <span class="tab-badge">{{ $eventControllerActivities->count() }}</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#invigilationDuties">
                      <i class="fas fa-user-shield me-2"></i>Invigilation
                      <span class="tab-badge">{{ $invigilationDuties->count() }}</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#deptActivities">
                      <i class="fas fa-university me-2"></i>Departmental
                      <span class="tab-badge">{{ $departmentalActivities->count() }}</span>
                    </a>
                  </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content custom-tab-content">

                  <!-- Event Controller Tab -->
                  <div class="tab-pane fade show active" id="eventDuties">
                    <div class="activities-list">
                      @forelse($eventControllerActivities as $duty)
                      <div class="activity-card event-card">
                        <div class="activity-header">
                          <div class="activity-title-section">
                            <h6 class="activity-title">{{ $duty->duty_title }}</h6>
                            @if($duty->event)
                            <p class="activity-subtitle">{{ $duty->event->title }}</p>
                            @endif
                          </div>
                          @php
                          $statusColors = ['assigned' => 'warning', 'acknowledged' => 'info', 'completed' => 'success'];
                          $statusColor = $statusColors[$duty->status] ?? 'secondary';
                          @endphp
                          <span class="activity-status status-{{ $statusColor }}">{{ ucfirst($duty->status) }}</span>
                        </div>
                        <div class="activity-body">
                          @if($duty->event)
                          <div class="activity-meta">
                            <span class="meta-item">
                              <i class="fas fa-calendar me-1"></i>
                              {{ $duty->event->start_date ? \Carbon\Carbon::parse($duty->event->start_date)->format('d M') : '' }} -
                              {{ $duty->event->end_date ? \Carbon\Carbon::parse($duty->event->end_date)->format('d M Y') : '' }}
                            </span>
                            @if($duty->event->venue)
                            <span class="meta-item">
                              <i class="fas fa-map-marker-alt me-1"></i>{{ $duty->event->venue }}
                            </span>
                            @endif
                          </div>
                          @endif
                          @if($duty->program)
                          <div class="activity-info-box">
                            <strong>Program:</strong> {{ $duty->program->name }}
                          </div>
                          @endif
                          @if($duty->responsibility)
                          <div class="activity-description">
                            <strong>Responsibility:</strong> {{ $duty->responsibility }}
                          </div>
                          @endif
                          @if($duty->remarks)
                          <div class="activity-remarks">
                            <i class="fas fa-comment-alt me-2"></i>{{ $duty->remarks }}
                          </div>
                          @endif
                        </div>
                      </div>
                      @empty
                      <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No event controller activities assigned</p>
                      </div>
                      @endforelse
                    </div>
                  </div>

                  <!-- Invigilation Tab -->
                  <div class="tab-pane fade" id="invigilationDuties">
                    <div class="activities-list">
                      @forelse($invigilationDuties as $invigilation)
                      <div class="activity-card invigilation-card">
                        <div class="activity-header">
                          <div class="activity-title-section">
                            <h6 class="activity-title">{{ ucfirst($invigilation->role) }}</h6>
                            <p class="activity-subtitle">{{ \Carbon\Carbon::parse($invigilation->date)->format('l, d M Y') }}</p>
                          </div>
                          @php
                          $statusColor = $invigilation->status === 'completed' ? 'success' : 'warning';
                          @endphp
                          <span class="activity-status status-{{ $statusColor }}">
                            {{ $invigilation->status === 'completed' ? 'Completed' : 'Assigned' }}
                          </span>
                        </div>
                        <div class="activity-body">
                          <div class="activity-meta">
                            <span class="meta-item session-badge session-{{ strtolower($invigilation->session) }}">
                              <i class="fas fa-clock me-1"></i>{{ ucfirst($invigilation->session) }} Session
                            </span>
                            @if($invigilation->room)
                            <span class="meta-item">
                              <i class="fas fa-door-open me-1"></i>{{ $invigilation->room->name ?? 'Room ' . $invigilation->room_id }}
                            </span>
                            @endif
                          </div>
                        </div>
                      </div>
                      @empty
                      <div class="empty-state">
                        <i class="fas fa-user-shield"></i>
                        <p>No upcoming invigilation duties</p>
                      </div>
                      @endforelse
                    </div>
                  </div>

                  <!-- Departmental Activities Tab -->
                  <div class="tab-pane fade" id="deptActivities">
                    <div class="activities-list">
                      @forelse($departmentalActivities as $activity)
                      <div class="activity-card dept-card">
                        <div class="activity-header">
                          <div class="activity-title-section">
                            <h6 class="activity-title">{{ $activity->title }}</h6>
                            @if($activity->subject)
                            <p class="activity-subtitle">{{ $activity->subject->title }}</p>
                            @endif
                          </div>
                          @php
                          $typeColors = [
                          'seminar' => 'primary', 'workshop' => 'info', 'conference' => 'success',
                          'fete' => 'warning', 'cultural' => 'danger', 'sports' => 'dark',
                          'guest_lecture' => 'secondary', 'competition' => 'purple', 'exhibition' => 'pink'
                          ];
                          $typeColor = $typeColors[$activity->activity_type] ?? 'secondary';
                          @endphp
                          <span class="activity-type type-{{ $typeColor }}">{{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}</span>
                        </div>
                        <div class="activity-body">
                          <div class="activity-meta">
                            <span class="meta-item">
                              <i class="fas fa-calendar me-1"></i>{{ $activity->activity_date->format('d M Y') }}
                              @if($activity->start_time)
                              at {{ \Carbon\Carbon::parse($activity->start_time)->format('g:i A') }}
                              @endif
                            </span>
                            @if($activity->venue)
                            <span class="meta-item">
                              <i class="fas fa-map-marker-alt me-1"></i>{{ $activity->venue }}
                            </span>
                            @endif
                            <span class="activity-status status-{{ $activity->status_badge }}">{{ ucfirst($activity->status) }}</span>
                          </div>
                        </div>
                      </div>
                      @empty
                      <div class="empty-state">
                        <i class="fas fa-university"></i>
                        <p>No departmental activities found</p>
                      </div>
                      @endforelse
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END LEFT COLUMN -->

      <!-- RIGHT COLUMN: Activities & Leave -->
      <div class="col-lg-4">

        <!-- Student Feedback Card -->
        <div class="content-card mb-4">
          <div class="card-header-custom">
            <h5 class="card-title-custom"><i class="fas fa-chart-line me-2"></i>Student Feedback</h5>
            <p class="card-subtitle-custom">Your performance ratings</p>
          </div>
          <div class="card-body-custom">
            <div class="feedback-score-display">
              <div class="score-circle">
                <div class="score-number">4.5</div>
                <div class="score-label">out of 5.0</div>
              </div>
              <div class="score-stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
              <div class="score-responses">Based on 215 responses</div>
            </div>

            <div class="feedback-categories">
              <div class="feedback-item">
                <div class="feedback-label">
                  <span>Teaching Quality</span>
                  <strong>4.7</strong>
                </div>
                <div class="feedback-bar">
                  <div class="feedback-fill" style="width: 94%"></div>
                </div>
              </div>
              <div class="feedback-item">
                <div class="feedback-label">
                  <span>Course Content</span>
                  <strong>4.4</strong>
                </div>
                <div class="feedback-bar">
                  <div class="feedback-fill" style="width: 88%"></div>
                </div>
              </div>
              <div class="feedback-item">
                <div class="feedback-label">
                  <span>Accessibility</span>
                  <strong>4.6</strong>
                </div>
                <div class="feedback-bar">
                  <div class="feedback-fill" style="width: 92%"></div>
                </div>
              </div>
              <div class="feedback-item">
                <div class="feedback-label">
                  <span>Communication</span>
                  <strong>4.3</strong>
                </div>
                <div class="feedback-bar">
                  <div class="feedback-fill" style="width: 86%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Leave Summary Card -->
        <div class="content-card mb-4">
          <div class="card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title-custom"><i class="fas fa-umbrella-beach me-2"></i>Recent Applications</h5>
              </div>
              <a href="{{ route('faculty.leave.create') }}" class="btn-custom-outline btn-sm">
                <i class="fas fa-plus me-1"></i>Apply
              </a>
            </div>
          </div>
          <div class="card-body-custom">
            <!-- <div class="leave-type-item">
              <div class="leave-type-header">
                <div class="leave-type-info">
                  <div class="leave-type-icon casual">CL</div>
                  <div>
                    <div class="leave-type-name">Casual Leave</div>
                    <div class="leave-type-remaining">{{ max(0, 10 - $casualLeaves) }} days remaining</div>
                  </div>
                </div>
              </div>
              <div class="leave-progress">
                <div class="leave-progress-bar">
                  <div class="leave-progress-fill casual" style="width: {{ min(100, ($casualLeaves / 10) * 100) }}%"></div>
                </div>
                <div class="leave-usage">{{ $casualLeaves }}/10 days used</div>
              </div>
            </div>

            <div class="leave-type-item">
              <div class="leave-type-header">
                <div class="leave-type-info">
                  <div class="leave-type-icon sick">SL</div>
                  <div>
                    <div class="leave-type-name">Sick Leave</div>
                    <div class="leave-type-remaining">Unlimited</div>
                  </div>
                </div>
              </div>
              <div class="leave-progress">
                <div class="leave-progress-bar">
                  <div class="leave-progress-fill sick" style="width: {{ min(100, ($sickLeaves / 15) * 100) }}%"></div>
                </div>
                <div class="leave-usage">{{ $sickLeaves }} days used</div>
              </div>
            </div>

            <div class="leave-type-item">
              <div class="leave-type-header">
                <div class="leave-type-info">
                  <div class="leave-type-icon earned">EL</div>
                  <div>
                    <div class="leave-type-name">Earned Leave</div>
                    <div class="leave-type-remaining">{{ max(0, 25 - $earnedLeaves) }} days remaining</div>
                  </div>
                </div>
              </div>
              <div class="leave-progress">
                <div class="leave-progress-bar">
                  <div class="leave-progress-fill earned" style="width: {{ min(100, ($earnedLeaves / 25) * 100) }}%"></div>
                </div>
                <div class="leave-usage">{{ $earnedLeaves }}/25 days used</div>
              </div>
            </div> -->

            @if($recentLeaves->count() > 0)
            <div class="recent-leaves-section">


              @foreach($recentLeaves as $leave)
              <div class="leave-application-item">
                <div class="leave-app-header">
                  <span class="leave-app-type">{{ $leave->leaveMaster->leave_type_name ?? ucfirst($leave->leave_type) }}</span>
                  @php
                  $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                  $statusColor = $statusColors[$leave->status] ?? 'secondary';
                  @endphp
                  <span class="leave-app-status status-{{ $statusColor }}">{{ ucfirst($leave->status) }}</span>
                </div>
                <div class="leave-app-dates">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }} ({{ $leave->total_days }} days)</div>
              </div>
              @endforeach
              <a href="{{ route('faculty.leave.index') }}" class="btn-custom-link w-100 text-center mt-2">View All Applications →</a>
            </div>
            @endif
          </div>
        </div>

        <!-- Quick Stats Card -->
        <div class="content-card">
          <div class="card-header-custom">
            <h5 class="card-title-custom"><i class="fas fa-chart-bar me-2"></i>Activity Summary</h5>
          </div>
          <div class="card-body-custom">
            <div class="quick-stat-item">
              <div class="quick-stat-icon events">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="quick-stat-info">
                <div class="quick-stat-value">{{ $eventControllerActivities->count() }}</div>
                <div class="quick-stat-label">Event Duties</div>
              </div>
            </div>
            <div class="quick-stat-item">
              <div class="quick-stat-icon invigilation">
                <i class="fas fa-user-shield"></i>
              </div>
              <div class="quick-stat-info">
                <div class="quick-stat-value">{{ $invigilationDuties->count() }}</div>
                <div class="quick-stat-label">Invigilation Duties</div>
              </div>
            </div>
            <div class="quick-stat-item">
              <div class="quick-stat-icon department">
                <i class="fas fa-university"></i>
              </div>
              <div class="quick-stat-info">
                <div class="quick-stat-value">{{ $departmentalActivities->count() }}</div>
                <div class="quick-stat-label">Dept. Activities</div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END RIGHT COLUMN -->

    </div>
    <!--end main content area-->

    <!-- Detailed Activities Section -->
</div>
</div>
</div>
</div>
<!--end stats cards-->

</main>
<!--end page content-->
</div>

<style>
  /* ============================================
   FACULTY DASHBOARD - MODERN CORPORATE DESIGN
   ============================================ */

  /* Base Variables */
  :root {
    --primary-blue: #2563eb;
    --primary-dark: #1e40af;
    --success-green: #10b981;
    --warning-orange: #f59e0b;
    --danger-red: #ef4444;
    --info-cyan: #06b6d4;
    --neutral-50: #f9fafb;
    --neutral-100: #f3f4f6;
    --neutral-200: #e5e7eb;
    --neutral-300: #d1d5db;
    --neutral-500: #6b7280;
    --neutral-700: #374151;
    --neutral-900: #111827;
    --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
    --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
  }

  /* Welcome Banner */
  .welcome-banner {
    background: linear-gradient(135deg, #621ad7 0%, #21bde0 100%);
    border-radius: 16px;
    padding: 2.5rem;
    color: white;
    box-shadow: var(--card-shadow-hover);
    position: relative;
    overflow: hidden;
  }

  .welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
  }

  .welcome-content {
    position: relative;
    z-index: 1;
  }

  .welcome-title {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: white;
  }

  .welcome-subtitle {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
  }

  .date-display {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .date-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.25rem;
  }

  .date-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.25rem;
  }

  .date-day {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.75);
  }

  /* Metric Cards */
  .metric-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid var(--neutral-200);
    height: 100%;
  }

  .metric-card:hover {
    box-shadow: var(--card-shadow-hover);
    transform: translateY(-2px);
  }

  .metric-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
  }

  .metric-icon.subjects {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
  }

  .metric-icon.feedback {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
  }

  .metric-icon.leave {
    background: linear-gradient(135deg, #10b981, #34d399);
  }

  .metric-icon.activities {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
  }

  .metric-content {
    flex: 1;
  }

  .metric-label {
    font-size: 0.875rem;
    color: var(--neutral-500);
    font-weight: 500;
    margin-bottom: 0.25rem;
  }

  .metric-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--neutral-900);
    line-height: 1;
  }

  .metric-unit {
    font-size: 1rem;
    font-weight: 500;
    color: var(--neutral-500);
  }

  /* Content Cards */
  .content-card {
    background: white;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    border: 1px solid var(--neutral-200);
    overflow: hidden;
    transition: box-shadow 0.3s ease;
  }

  .content-card:hover {
    box-shadow: var(--card-shadow-hover);
  }

  .card-header-custom {
    padding: 1.5rem;
    background: var(--neutral-50);
    border-bottom: 1px solid var(--neutral-200);
  }

  .card-title-custom {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--neutral-900);
    margin: 0;
    display: flex;
    align-items: center;
  }

  .card-subtitle-custom {
    font-size: 0.875rem;
    color: var(--neutral-500);
    margin: 0.25rem 0 0 0;
  }

  .card-body-custom {
    padding: 1.5rem;
  }

  .btn-custom-outline {
    padding: 0.5rem 1rem;
    border: 1px solid var(--neutral-300);
    background: white;
    color: var(--neutral-700);
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
  }

  .btn-custom-outline:hover {
    background: var(--neutral-50);
    border-color: var(--neutral-400);
    color: var(--neutral-900);
  }

  .btn-custom-primary {
    padding: 0.5rem 1rem;
    background: var(--primary-blue);
    color: white;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    border: none;
  }

  .btn-custom-primary:hover {
    background: var(--primary-dark);
    color: white;
  }

  .btn-custom-link {
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    transition: color 0.2s ease;
    display: block;
  }

  .btn-custom-link:hover {
    color: var(--primary-dark);
  }

  /* Subjects List */
  .subjects-list {
    max-height: 500px;
    overflow-y: auto;
  }

  .subject-item {
    padding: 1.25rem;
    border-bottom: 1px solid var(--neutral-200);
    transition: background 0.2s ease;
  }

  .subject-item:last-child {
    border-bottom: none;
  }

  .subject-item:hover {
    background: var(--neutral-50);
  }

  .subject-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
  }

  .subject-info {
    flex: 1;
  }

  .subject-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--neutral-900);
    margin: 0 0 0.5rem 0;
  }

  .subject-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .meta-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    background: var(--neutral-100);
    color: var(--neutral-700);
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 500;
  }

  .status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .status-badge.status-success {
    background: #d1fae5;
    color: #065f46;
  }

  .status-badge.status-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .status-badge.status-danger {
    background: #fee2e2;
    color: #991b1b;
  }

  .progress-container {
    margin-top: 0.75rem;
  }

  .progress-bar-custom {
    height: 8px;
    background: var(--neutral-200);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
  }

  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), var(--info-cyan));
    border-radius: 4px;
    transition: width 0.3s ease;
  }

  .progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.8125rem;
  }

  .progress-text {
    color: var(--neutral-700);
    font-weight: 500;
  }

  .progress-units {
    color: var(--neutral-500);
  }

  /* Timeline */
  .timeline-container {
    max-height: 450px;
    overflow-y: auto;
  }

  .timeline-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--neutral-200);
  }

  .timeline-item:last-child {
    border-bottom: none;
  }

  .timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary-blue);
    margin-top: 4px;
    flex-shrink: 0;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
  }

  .timeline-content {
    flex: 1;
  }

  .timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
  }

  .timeline-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--neutral-900);
    margin: 0;
  }

  .timeline-time {
    font-size: 0.8125rem;
    color: var(--neutral-500);
    white-space: nowrap;
  }

  .timeline-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
  }

  .detail-tag {
    padding: 0.25rem 0.625rem;
    background: var(--primary-blue);
    color: white;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .detail-text {
    font-size: 0.8125rem;
    color: var(--neutral-600);
  }

  /* Feedback Section */
  .feedback-score-display {
    text-align: center;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--neutral-200);
    margin-bottom: 1.5rem;
  }

  .score-circle {
    margin-bottom: 1rem;
  }

  .score-number {
    font-size: 3rem;
    font-weight: 700;
    color: var(--warning-orange);
    line-height: 1;
  }

  .score-label {
    font-size: 0.875rem;
    color: var(--neutral-500);
    margin-top: 0.25rem;
  }

  .score-stars {
    font-size: 1.5rem;
    color: var(--warning-orange);
    margin-bottom: 0.5rem;
    letter-spacing: 0.25rem;
  }

  .score-responses {
    font-size: 0.8125rem;
    color: var(--neutral-500);
  }

  .feedback-categories {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .feedback-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .feedback-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
  }

  .feedback-label span {
    color: var(--neutral-700);
    font-weight: 500;
  }

  .feedback-label strong {
    color: var(--neutral-900);
    font-weight: 600;
  }

  .feedback-bar {
    height: 6px;
    background: var(--neutral-200);
    border-radius: 3px;
    overflow: hidden;
  }

  .feedback-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--warning-orange), #fbbf24);
    border-radius: 3px;
    transition: width 0.3s ease;
  }

  /* Leave Balance */
  .leave-type-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--neutral-200);
  }

  .leave-type-item:last-child {
    border-bottom: none;
  }

  .leave-type-header {
    margin-bottom: 0.75rem;
  }

  .leave-type-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .leave-type-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    color: white;
    flex-shrink: 0;
  }

  .leave-type-icon.casual {
    background: var(--primary-blue);
  }

  .leave-type-icon.sick {
    background: var(--danger-red);
  }

  .leave-type-icon.earned {
    background: var(--success-green);
  }

  .leave-type-name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--neutral-900);
  }

  .leave-type-remaining {
    font-size: 0.8125rem;
    color: var(--neutral-500);
  }

  .leave-progress {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .leave-progress-bar {
    height: 8px;
    background: var(--neutral-200);
    border-radius: 4px;
    overflow: hidden;
  }

  .leave-progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
  }

  .leave-progress-fill.casual {
    background: linear-gradient(90deg, var(--primary-blue), #3b82f6);
  }

  .leave-progress-fill.sick {
    background: linear-gradient(90deg, var(--danger-red), #f87171);
  }

  .leave-progress-fill.earned {
    background: linear-gradient(90deg, var(--success-green), #34d399);
  }

  .leave-usage {
    font-size: 0.8125rem;
    color: var(--neutral-600);
    font-weight: 500;
  }

  .recent-leaves-section {
    margin-top: 1.5rem;
  }

  .section-divider {
    height: 1px;
    background: var(--neutral-200);
    margin-bottom: 1rem;
  }

  .section-heading {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--neutral-700);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
  }

  .leave-application-item {
    padding: 0.75rem;
    background: var(--neutral-50);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    border: 1px solid var(--neutral-200);
  }

  .leave-app-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
  }

  .leave-app-type {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--neutral-900);
  }

  .leave-app-status {
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .leave-app-status.status-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .leave-app-status.status-success {
    background: #d1fae5;
    color: #065f46;
  }

  .leave-app-status.status-danger {
    background: #fee2e2;
    color: #991b1b;
  }

  .leave-app-dates {
    font-size: 0.8125rem;
    color: var(--neutral-600);
  }

  /* Quick Stats */
  .quick-stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--neutral-200);
  }

  .quick-stat-item:last-child {
    border-bottom: none;
  }

  .quick-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
  }

  .quick-stat-icon.events {
    background: linear-gradient(135deg, #10b981, #34d399);
  }

  .quick-stat-icon.invigilation {
    background: linear-gradient(135deg, #ef4444, #f87171);
  }

  .quick-stat-icon.department {
    background: linear-gradient(135deg, #06b6d4, #22d3ee);
  }

  .quick-stat-info {
    flex: 1;
  }

  .quick-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--neutral-900);
    line-height: 1;
  }

  .quick-stat-label {
    font-size: 0.875rem;
    color: var(--neutral-500);
    margin-top: 0.25rem;
  }

  /* Custom Tabs */
  .custom-tabs {
    border-bottom: 2px solid var(--neutral-200);
    padding: 0 1.5rem;
    background: var(--neutral-50);
    margin: 0;
  }

  .custom-tabs .nav-item {
    margin-bottom: -2px;
  }

  .custom-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--neutral-600);
    font-weight: 500;
    font-size: 0.9375rem;
    padding: 1rem 1.5rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
  }

  .custom-tabs .nav-link:hover {
    color: var(--neutral-900);
    background: rgba(0, 0, 0, 0.02);
  }

  .custom-tabs .nav-link.active {
    color: var(--primary-blue);
    border-bottom-color: var(--primary-blue);
    background: white;
  }

  .tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 0.5rem;
    background: var(--neutral-200);
    color: var(--neutral-700);
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .custom-tabs .nav-link.active .tab-badge {
    background: var(--primary-blue);
    color: white;
  }

  .custom-tab-content {
    padding: 0;
  }

  /* Activities List */
  .activities-list {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-height: 600px;
    overflow-y: auto;
  }

  .activity-card {
    background: white;
    border: 1px solid var(--neutral-200);
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.2s ease;
  }

  .activity-card:hover {
    box-shadow: var(--card-shadow);
    border-color: var(--neutral-300);
  }

  .activity-card.event-card {
    border-left: 4px solid var(--success-green);
  }

  .activity-card.invigilation-card {
    border-left: 4px solid var(--danger-red);
  }

  .activity-card.dept-card {
    border-left: 4px solid var(--info-cyan);
  }

  .activity-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
  }

  .activity-title-section {
    flex: 1;
  }

  .activity-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--neutral-900);
    margin: 0 0 0.25rem 0;
  }

  .activity-subtitle {
    font-size: 0.875rem;
    color: var(--neutral-500);
    margin: 0;
  }

  .activity-status,
  .activity-type {
    padding: 0.375rem 0.875rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .activity-status.status-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .activity-status.status-info {
    background: #dbeafe;
    color: #1e40af;
  }

  .activity-status.status-success {
    background: #d1fae5;
    color: #065f46;
  }

  .activity-type.type-primary {
    background: #dbeafe;
    color: #1e40af;
  }

  .activity-type.type-info {
    background: #cffafe;
    color: #155e75;
  }

  .activity-type.type-success {
    background: #d1fae5;
    color: #065f46;
  }

  .activity-type.type-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .activity-type.type-danger {
    background: #fee2e2;
    color: #991b1b;
  }

  .activity-type.type-purple {
    background: #f3e8ff;
    color: #6b21a8;
  }

  .activity-type.type-pink {
    background: #fce7f3;
    color: #9f1239;
  }

  .activity-body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .activity-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .meta-item {
    display: inline-flex;
    align-items: center;
    font-size: 0.8125rem;
    color: var(--neutral-600);
    font-weight: 500;
  }

  .session-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
  }

  .session-badge.session-morning {
    background: #dbeafe;
    color: #1e40af;
  }

  .session-badge.session-afternoon {
    background: #fed7aa;
    color: #9a3412;
  }

  .session-badge.session-evening {
    background: #e0e7ff;
    color: #3730a3;
  }

  .activity-info-box {
    padding: 0.75rem;
    background: var(--neutral-50);
    border-radius: 8px;
    font-size: 0.875rem;
    color: var(--neutral-700);
  }

  .activity-description {
    font-size: 0.875rem;
    color: var(--neutral-700);
    line-height: 1.5;
  }

  .activity-remarks {
    padding: 0.75rem;
    background: #fffbeb;
    border-left: 3px solid var(--warning-orange);
    border-radius: 4px;
    font-size: 0.8125rem;
    color: var(--neutral-700);
  }

  /* Empty States */
  .empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--neutral-400);
  }

  .empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
  }

  .empty-state p {
    font-size: 0.9375rem;
    color: var(--neutral-500);
    margin: 0;
  }

  /* Scrollbar Styling */
  .subjects-list::-webkit-scrollbar,
  .timeline-container::-webkit-scrollbar,
  .activities-list::-webkit-scrollbar {
    width: 8px;
  }

  .subjects-list::-webkit-scrollbar-track,
  .timeline-container::-webkit-scrollbar-track,
  .activities-list::-webkit-scrollbar-track {
    background: var(--neutral-100);
    border-radius: 4px;
  }

  .subjects-list::-webkit-scrollbar-thumb,
  .timeline-container::-webkit-scrollbar-thumb,
  .activities-list::-webkit-scrollbar-thumb {
    background: var(--neutral-300);
    border-radius: 4px;
  }

  .subjects-list::-webkit-scrollbar-thumb:hover,
  .timeline-container::-webkit-scrollbar-thumb:hover,
  .activities-list::-webkit-scrollbar-thumb:hover {
    background: var(--neutral-400);
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .welcome-banner {
      padding: 2rem;
    }

    .welcome-title {
      font-size: 1.5rem;
    }

    .date-display {
      margin-top: 1rem;
    }

    .metric-card {
      padding: 1.25rem;
    }

    .metric-icon {
      width: 48px;
      height: 48px;
      font-size: 1.25rem;
    }

    .metric-value {
      font-size: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    .welcome-banner {
      padding: 1.5rem;
    }

    .welcome-title {
      font-size: 1.25rem;
    }

    .card-header-custom {
      padding: 1rem;
    }

    .card-body-custom {
      padding: 1rem;
    }

    .custom-tabs {
      padding: 0 1rem;
      overflow-x: auto;
      white-space: nowrap;
    }

    .custom-tabs .nav-link {
      padding: 0.875rem 1rem;
      font-size: 0.875rem;
    }

    .activities-list {
      padding: 1rem;
    }
  }

  /* Print Styles */
  @media print {

    .welcome-banner,
    .btn-custom-outline,
    .btn-custom-primary {
      display: none;
    }

    .content-card {
      page-break-inside: avoid;
    }
  }
</style>

@include('includes.footer')
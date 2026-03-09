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
                <h4 class="mb-0 fw-bold">5</h4>
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
                <p class="text-muted mb-1" style="font-size: 0.85rem;">Leaves Approved</p>
                <h4 class="mb-0 fw-bold">8/12</h4>
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
                <a href="javascript:;" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View All</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- Subject 1 -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">Data Structures & Algorithms</h6>
                  <small class="text-muted">Semester 3 | 45 Students</small>
                </div>
                <span class="badge bg-success">Active</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">Completion: 78% | Resources: 12/15 uploaded</small>
            </div>

            <!-- Subject 2 -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">Web Development</h6>
                  <small class="text-muted">Semester 4 | 38 Students</small>
                </div>
                <span class="badge bg-success">Active</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-gradient-info" role="progressbar" style="width: 92%;" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">Completion: 92% | Resources: 15/15 uploaded</small>
            </div>

            <!-- Subject 3 -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">Database Management Systems</h6>
                  <small class="text-muted">Semester 4 | 42 Students</small>
                </div>
                <span class="badge bg-warning">In Progress</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">Completion: 65% | Resources: 10/15 uploaded</small>
            </div>

            <!-- Subject 4 -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">Software Engineering</h6>
                  <small class="text-muted">Semester 5 | 35 Students</small>
                </div>
                <span class="badge bg-success">Active</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">Completion: 85% | Resources: 13/15 uploaded</small>
            </div>

            <!-- Subject 5 -->
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">Cloud Computing</h6>
                  <small class="text-muted">Semester 6 | 28 Students</small>
                </div>
                <span class="badge bg-danger">Pending</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 35%;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small class="text-muted d-block mt-1">Completion: 35% | Resources: 5/15 uploaded</small>
            </div>
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
                <a href="javascript:;" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> Apply Leave</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- Sanctioned Leave -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold">Casual Leave</h6>
                  <small class="text-muted">You have 3 days remaining</small>
                </div>
                <span class="badge bg-success">Sanctioned</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" style="width: 70%;"></div>
              </div>
              <small class="text-muted d-block mt-2">Used: 7/10 days | Last used: 2 days ago</small>
            </div>

            <!-- Applied Leave -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold">Sick Leave</h6>
                  <small class="text-muted">Unlimited</small>
                </div>
                <span class="badge bg-warning">Pending Approval</span>
              </div>
              <div class="leave-item p-2 border rounded bg-light mb-2">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="fw-bold d-block">Applied: 15 Mar 2026 to 16 Mar 2026</small>
                    <small class="text-muted">Reason: Medical Appointment</small>
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-danger me-1"><i class="fas fa-times"></i></button>
                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-check"></i></button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Earned Leave -->
            <div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold">Earned Leave</h6>
                  <small class="text-muted">You have 8 days remaining</small>
                </div>
                <span class="badge bg-success">Sanctioned</span>
              </div>
              <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-info" style="width: 40%;"></div>
              </div>
              <small class="text-muted d-block mt-2">Used: 15/25 days | Year ending: 31 Mar 2027</small>
            </div>
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
                <a href="{{ url('faculty/workdiary') }}" class="btn btn-sm btn-light"><i class="fas fa-plus"></i> New Entry</a>
              </div>
            </div>
          </div>
          <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            <!-- Activity 1 -->
            <div class="activity-item mb-4 pb-3 border-bottom">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-primary"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">Conducted Lecture - DSA Module 5</h6>
                    <small class="text-muted">Today</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Completed chapter on Tree Traversal Techniques with practical examples</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">DSA</span>
                    <span class="badge bg-light text-dark">Classroom</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Activity 2 -->
            <div class="activity-item mb-4 pb-3 border-bottom">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-success"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">Evaluated Assignment - Web Dev</h6>
                    <small class="text-muted">Yesterday</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Reviewed and graded 32 assignments on responsive design</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">Web Development</span>
                    <span class="badge bg-light text-dark">Evaluation</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Activity 3 -->
            <div class="activity-item mb-4 pb-3 border-bottom">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-warning"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">Scheduled Office Hours</h6>
                    <small class="text-muted">2 days ago</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Held office hours for doubt clearing session - 15 students attended</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">Office Hours</span>
                    <span class="badge bg-light text-dark">Student Support</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Activity 4 -->
            <div class="activity-item mb-4 pb-3 border-bottom">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-info"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">Updated Course Material</h6>
                    <small class="text-muted">3 days ago</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Uploaded new lecture notes and reference materials for Database Module</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">DBMS</span>
                    <span class="badge bg-light text-dark">Resources</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Activity 5 -->
            <div class="activity-item">
              <div class="d-flex">
                <div class="activity-timeline me-3">
                  <div class="timeline-dot bg-danger"></div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="mb-0 fw-bold">Exam Paper Preparation</h6>
                    <small class="text-muted">5 days ago</small>
                  </div>
                  <p class="text-muted mb-2" style="font-size: 0.9rem;">Started preparing final examination paper for semester 5 students</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">Assessment</span>
                  </div>
                </div>
              </div>
            </div>
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
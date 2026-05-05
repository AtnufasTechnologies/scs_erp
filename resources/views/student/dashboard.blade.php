@include('includes.header')

@include('student.sidebar')

<div class="wrapper">
  <!--start main wrapper-->
  <main class="page-content">
    <style>
      /* Custom Dashboard Styles */
      .std-dashboard-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        color: #fff;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
      }

      .std-dashboard-hero h1 {
        font-size: 1.9rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
      }

      .std-dashboard-hero p {
        font-size: 1rem;
        opacity: 0.95;
      }

      /* Overview Stats Cards */
      .std-stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
        border-left: 5px solid;
        height: 100%;
      }

      .std-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
      }

      .std-stat-card.attendance {
        border-color: #10b981;
      }

      .std-stat-card.courses {
        border-color: #3b82f6;
      }

      .std-stat-card.marks {
        border-color: #f59e0b;
      }

      .std-stat-card.exams {
        border-color: #8b5cf6;
      }

      .std-stat-card.feedback {
        border-color: #ef4444;
      }

      .std-stat-icon {
        width: 65px;
        height: 65px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.9rem;
        margin-bottom: 1rem;
      }

      .std-stat-card.attendance .std-stat-icon {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
      }

      .std-stat-card.courses .std-stat-icon {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
      }

      .std-stat-card.marks .std-stat-icon {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
      }

      .std-stat-card.exams .std-stat-icon {
        background: rgba(139, 92, 246, 0.12);
        color: #8b5cf6;
      }

      .std-stat-card.feedback .std-stat-icon {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
      }

      .std-stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
        color: #1f2937;
      }

      .std-stat-label {
        font-size: 0.95rem;
        color: #6b7280;
        font-weight: 600;
      }

      .std-stat-detail {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: 0.5rem;
      }

      /* Tab Navigation */
      .std-tabs-nav {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        background: #fff;
        padding: 1rem 1.5rem;
        border-radius: 14px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        overflow-x: auto;
      }

      .std-tab-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid #e5e7eb;
        background: #f9fafb;
        color: #6b7280;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .std-tab-btn:hover {
        border-color: #667eea;
        background: #f0f3ff;
        color: #667eea;
      }

      .std-tab-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }

      .std-tab-btn i {
        font-size: 1.1rem;
      }

      /* Tab Content */
      .std-tab-content {
        display: none;
      }

      .std-tab-content.active {
        display: block;
        animation: fadeIn 0.4s ease;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(10px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Section Card */
      .std-section-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.8rem;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
      }

      .std-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f3f4f6;
      }

      .std-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
      }

      .std-section-title i {
        color: #667eea;
        font-size: 1.4rem;
      }

      /* Table Styles */
      .std-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
      }

      .std-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      }

      .std-table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: #495057;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .std-table thead th:first-child {
        border-top-left-radius: 10px;
      }

      .std-table thead th:last-child {
        border-top-right-radius: 10px;
      }

      .std-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s;
      }

      .std-table tbody tr:hover {
        background: #f8fafe;
      }

      .std-table tbody td {
        padding: 1rem;
        color: #4b5563;
        font-size: 0.9rem;
      }

      /* Badge Styles */
      .std-badge {
        display: inline-block;
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
      }

      .std-badge-success {
        background: #d1fae5;
        color: #065f46;
      }

      .std-badge-warning {
        background: #fef3c7;
        color: #92400e;
      }

      .std-badge-danger {
        background: #fee2e2;
        color: #991b1b;
      }

      .std-badge-info {
        background: #dbeafe;
        color: #1e40af;
      }

      .std-badge-purple {
        background: #ede9fe;
        color: #5b21b6;
      }

      /* Progress Bar */
      .std-progress-container {
        background: #e5e7eb;
        border-radius: 10px;
        height: 12px;
        overflow: hidden;
        margin-top: 0.5rem;
      }

      .std-progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease;
      }

      .std-progress-bar.success {
        background: linear-gradient(90deg, #10b981, #059669);
      }

      .std-progress-bar.warning {
        background: linear-gradient(90deg, #f59e0b, #d97706);
      }

      .std-progress-bar.danger {
        background: linear-gradient(90deg, #ef4444, #dc2626);
      }

      /* Course Card */
      .std-course-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.3rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
      }

      .std-course-card:hover {
        border-color: #667eea;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.15);
      }

      .std-course-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
      }

      .std-course-code {
        font-size: 0.85rem;
        color: #6b7280;
        font-weight: 600;
      }

      /* Empty State */
      .std-empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #9ca3af;
      }

      .std-empty-state i {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
      }

      .std-empty-state p {
        font-size: 1.05rem;
        margin: 0;
      }

      /* Accordion */
      .std-accordion-item {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        margin-bottom: 0.8rem;
        overflow: hidden;
      }

      .std-accordion-header {
        background: #f9fafb;
        padding: 1rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
      }

      .std-accordion-header:hover {
        background: #f3f4f6;
      }

      .std-accordion-header.active {
        background: #ede9fe;
        color: #5b21b6;
      }

      .std-accordion-body {
        padding: 1.5rem;
        display: none;
      }

      .std-accordion-body.active {
        display: block;
      }

      /* Button Styles */
      .std-btn {
        padding: 0.6rem 1.3rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
      }

      .std-btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
      }

      .std-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
      }

      .std-btn-secondary {
        background: #e5e7eb;
        color: #4b5563;
      }

      .std-btn-secondary:hover {
        background: #d1d5db;
      }

      /* Info Box */
      .std-info-box {
        display: flex;
        align-items: start;
        gap: 1rem;
        padding: 1rem;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        margin-bottom: 1rem;
      }

      .std-info-box i {
        font-size: 1.5rem;
        color: #3b82f6;
        margin-top: 0.2rem;
      }

      .std-info-box-content p {
        margin: 0;
        color: #1e40af;
        font-size: 0.9rem;
      }
    </style>

    <div class="container-fluid">
      <!-- Hero Section -->
      <div class="std-dashboard-hero">
        <h1>Welcome, {{ $student->firstname ?? 'Student' }} {{ $student->lastname ?? 'Name' }}!</h1>
        <p>
          <i class="fas fa-id-card"></i> Roll No: {{ strtoupper($student->roll_no ?? 'ROLL123') }} |
          <i class="fas fa-calendar-alt"></i> Batch: {{ $student->batchmaster->batch_name ?? '2023-2027' }} |
          <i class="fas fa-building"></i> {{ $student->deptmaster->name ?? 'Computer Science' }}
        </p>
      </div>

      <!-- Quick Stats -->
      <div class="row mb-3">
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card attendance">
            <div class="std-stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="std-stat-value">85.5%</div>
            <div class="std-stat-label">Attendance</div>
            <div class="std-stat-detail">120/140</div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card courses">
            <div class="std-stat-icon"><i class="fas fa-book-open"></i></div>
            <div class="std-stat-value">8</div>
            <div class="std-stat-label">Courses</div>
            <div class="std-stat-detail">Enrolled</div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card marks">
            <div class="std-stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="std-stat-value">24</div>
            <div class="std-stat-label">Assessments</div>
            <div class="std-stat-detail">Recorded</div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card exams">
            <div class="std-stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="std-stat-value">3</div>
            <div class="std-stat-label">Results</div>
            <div class="std-stat-detail">Published</div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card feedback">
            <div class="std-stat-icon"><i class="fas fa-star"></i></div>
            <div class="std-stat-value">5</div>
            <div class="std-stat-label">Pending</div>
            <div class="std-stat-detail">Feedback</div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="std-stat-card exams">
            <div class="std-stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="std-stat-value">2</div>
            <div class="std-stat-label">Exams</div>
            <div class="std-stat-detail">Registered</div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="std-tabs-nav">
        <button class="std-tab-btn active" onclick="switchTab('overview')">
          <i class="fas fa-home"></i> Overview
        </button>
        <button class="std-tab-btn" onclick="switchTab('timetable')">
          <i class="fas fa-calendar-week"></i> Timetable
        </button>
        <button class="std-tab-btn" onclick="switchTab('courses')">
          <i class="fas fa-book-open"></i> My Courses
        </button>
        <button class="std-tab-btn" onclick="switchTab('attendance')">
          <i class="fas fa-user-check"></i> Attendance
        </button>
        <button class="std-tab-btn" onclick="switchTab('mentorship')">
          <i class="fas fa-hands-helping"></i> Mentorship
        </button>
        <button class="std-tab-btn" onclick="switchTab('activities')">
          <i class="fas fa-calendar-star"></i> Activities
        </button>
        <button class="std-tab-btn" onclick="switchTab('fees')">
          <i class="fas fa-wallet"></i> Fee Payments
        </button>
        <button class="std-tab-btn" onclick="switchTab('exams')">
          <i class="fas fa-file-signature"></i> Examinations
        </button>
      </div>

      <!-- Tab Contents -->
      <div id="tab-overview" class="std-tab-content active">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-home"></i> Dashboard Overview</h3>
          </div>
          <div class="std-info-box">
            <i class="fas fa-info-circle"></i>
            <div class="std-info-box-content">
              <p><strong>Dashboard Design Preview</strong></p>
              <p>This is a design preview. Individual tab functionalities will be implemented one by one.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="std-course-card">
                <div class="std-course-title">Recent Activity</div>
                <p class="std-course-code">Your latest updates will appear here</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="std-course-card">
                <div class="std-course-title">Quick Actions</div>
                <p class="std-course-code">Access frequently used features</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="tab-timetable" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-calendar-week"></i> Weekly Timetable</h3>
          </div>
          <div class="table-responsive">
            <table class="std-table">
              <thead>
                <tr>
                  <th style="width: 100px;">Day</th>
                  <th>Period 1<br><small class="text-muted">9:00-10:00</small></th>
                  <th>Period 2<br><small class="text-muted">10:00-11:00</small></th>
                  <th>Period 3<br><small class="text-muted">11:00-12:00</small></th>
                  <th style="background: #f8f9fa;">Break<br><small class="text-muted">12:00-1:00</small></th>
                  <th>Period 4<br><small class="text-muted">1:00-2:00</small></th>
                  <th>Period 5<br><small class="text-muted">2:00-3:00</small></th>
                  <th>Period 6<br><small class="text-muted">3:00-4:00</small></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Monday</strong></td>
                  <td><span class="std-badge std-badge-info">Mathematics</span><br><small>Room 101 - Dr. Smith</small></td>
                  <td><span class="std-badge std-badge-success">Physics</span><br><small>Lab A - Dr. Johnson</small></td>
                  <td><span class="std-badge std-badge-warning">Chemistry</span><br><small>Room 203 - Dr. Brown</small></td>
                  <td style="background: #f8f9fa; text-align: center;"><i class="fas fa-coffee"></i></td>
                  <td><span class="std-badge std-badge-purple">English</span><br><small>Room 105 - Ms. Davis</small></td>
                  <td><span class="std-badge std-badge-info">Computer Science</span><br><small>Lab B - Dr. Wilson</small></td>
                  <td><span class="std-badge std-badge-success">Biology</span><br><small>Room 201 - Dr. Taylor</small></td>
                </tr>
                <tr>
                  <td><strong>Tuesday</strong></td>
                  <td colspan="7" class="text-center" style="padding: 2rem; color: #9ca3af;"><i class="fas fa-calendar-week" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>Timetable data will be loaded from database</td>
                </tr>
                <tr>
                  <td><strong>Wednesday</strong></td>
                  <td colspan="7" class="text-center" style="padding: 2rem; color: #9ca3af;"></td>
                </tr>
                <tr>
                  <td><strong>Thursday</strong></td>
                  <td colspan="7" class="text-center" style="padding: 2rem; color: #9ca3af;"></td>
                </tr>
                <tr>
                  <td><strong>Friday</strong></td>
                  <td colspan="7" class="text-center" style="padding: 2rem; color: #9ca3af;"></td>
                </tr>
                <tr>
                  <td><strong>Saturday</strong></td>
                  <td colspan="7" class="text-center" style="padding: 2rem; color: #9ca3af;"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="tab-courses" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-book-open"></i> My Courses</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-book-open"></i>
            <p>Course details will be loaded here</p>
          </div>
        </div>
      </div>

      <div id="tab-attendance" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-user-check"></i> Attendance Records</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-user-check"></i>
            <p>Attendance data will be displayed here</p>
          </div>
        </div>
      </div>

      <div id="tab-mentorship" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-hands-helping"></i> Mentorship Program</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-hands-helping"></i>
            <p>Mentorship information will be shown here</p>
          </div>
        </div>
      </div>

      <div id="tab-activities" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-calendar-star"></i> Activities & Events</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-calendar-star"></i>
            <p>Upcoming events and activities will be listed here</p>
          </div>
        </div>
      </div>

      <div id="tab-fees" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-wallet"></i> Fee Payment History</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-wallet"></i>
            <p>Payment records will be displayed here</p>
          </div>
        </div>
      </div>

      <div id="tab-exams" class="std-tab-content">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-file-signature"></i> Examination Details</h3>
          </div>
          <div class="std-empty-state">
            <i class="fas fa-file-signature"></i>
            <p>Exam registrations and results will be shown here</p>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('student.footer')

<script>
  function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.std-tab-content').forEach(tab => {
      tab.classList.remove('active');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.std-tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');

    // Add active class to clicked button
    event.target.closest('.std-tab-btn').classList.add('active');
  }

  function toggleAccordion(element) {
    const header = element;
    const body = header.nextElementSibling;

    // Toggle active classes
    header.classList.toggle('active');
    body.classList.toggle('active');
  }
</script>
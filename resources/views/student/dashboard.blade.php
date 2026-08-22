@include('includes.header')

@include('student.sidebar')

<div class="wrapper">
  <!--start main wrapper-->
  <main class="page-content">
    <style>
      .sp-hero {
        background: linear-gradient(135deg, #7411dd 30%, #0e4ab1 100%);
        padding: 2.5rem 2rem 4rem;
        color: #fff;
        position: relative
      }

      .sp-hero .sp-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, .5);
        object-fit: cover;
        background: rgba(255, 255, 255, .15)
      }

      .sp-hero h1 {
        font-size: 1.7rem;
        font-weight: 700;
        margin-bottom: .15rem
      }

      .sp-hero .sp-sub {
        font-size: .85rem;
        opacity: .8
      }

      .sp-hero .sp-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: rgba(255, 255, 255, .15);
        border-radius: 50px;
        padding: .25rem .75rem;
        font-size: .8rem;
        margin: .2rem .15rem 0
      }

      .sp-stat-strip {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .09);
        margin: -2rem 1.5rem 0;
        position: relative;
        z-index: 10;
        padding: 1.2rem 1rem
      }

      .sp-stat {
        text-align: center
      }

      .sp-stat .sp-stat-val {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1;
        color: #1a237e
      }

      .sp-stat .sp-stat-lbl {
        font-size: .72rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-top: .2rem
      }

      .sp-stat-divider {
        border-left: 1px solid #e9ecef
      }

      .sp-tabs-wrap {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 2px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06)
      }

      .sp-tabs {
        display: flex;
        overflow-x: auto;
        padding: 0 1.5rem;
        gap: .25rem;
        scrollbar-width: none
      }

      .sp-tabs::-webkit-scrollbar {
        display: none
      }

      .sp-tab {
        white-space: nowrap;
        cursor: pointer;
        padding: .75rem 1.1rem;
        font-size: .82rem;
        font-weight: 600;
        color: #495057;
        border: none;
        background: none;
        border-bottom: 2.5px solid transparent;
        transition: all .2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem
      }

      .sp-tab:hover {
        color: #1a237e
      }

      .sp-tab.active {
        color: #1a237e;
        border-bottom-color: #1a237e
      }

      .sp-content {
        padding: 1.5rem;
        max-width: 1200px;
        margin: 0 auto
      }

      .sp-panel {
        display: none
      }

      .sp-panel.active {
        display: block
      }

      .sp-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        padding: 1.5rem;
        margin-bottom: 1.25rem
      }

      .sp-card-header {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: 1.2rem;
        padding-bottom: .8rem;
        border-bottom: 1px solid #f0f0f0
      }

      .sp-card-header .sp-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0
      }

      .sp-card-header h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e
      }

      .sp-card-header .sp-count {
        margin-left: auto;
        background: #e8eaf6;
        color: #1a237e;
        border-radius: 50px;
        padding: .15rem .65rem;
        font-size: .75rem;
        font-weight: 700
      }

      .sp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .84rem
      }

      .sp-table thead th {
        background: #f8f9ff;
        color: #3949ab;
        font-weight: 700;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .65rem .85rem;
        border-bottom: 2px solid #e8eaf6;
        white-space: nowrap
      }

      .sp-table tbody td {
        padding: .6rem .85rem;
        border-bottom: 1px solid #f5f5f5;
        color: #343a40
      }

      .sp-table tbody tr:last-child td {
        border-bottom: none
      }

      .sp-table tbody tr:hover {
        background: #fafbff
      }

      .sp-day-label {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: #e8eaf6;
        color: #1a237e;
        border-radius: 6px;
        padding: .3rem .9rem;
        font-weight: 700;
        font-size: .82rem;
        margin: 1rem 0 .5rem
      }

      .sp-slot-badge {
        display: inline-flex;
        align-items: center;
        background: #f8f9ff;
        border: 1px solid #e8eaf6;
        border-radius: 8px;
        padding: .5rem .9rem;
        margin: .3rem;
        font-size: .8rem;
        gap: .5rem
      }

      .sp-slot-badge .hour-num {
        background: #1a237e;
        color: #fff;
        border-radius: 4px;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0
      }

      .sp-grid-wrap {
        overflow-x: auto;
        border: 1px solid #e8eaf6;
        border-radius: 10px;
        background: #fff
      }

      .sp-grid-table {
        width: 100%;
        min-width: 840px;
        border-collapse: collapse
      }

      .sp-grid-table th,
      .sp-grid-table td {
        border: 1px solid #eef1ff;
        vertical-align: top;
        padding: .5rem
      }

      .sp-grid-table thead th {
        background: #f6f8ff;
        color: #27317a;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap
      }

      .sp-grid-table .day-col {
        min-width: 105px;
        font-weight: 700;
        color: #27317a;
        background: #f9faff
      }

      .sp-cell-empty {
        color: #b0b7c3;
        font-size: .78rem;
        text-align: center;
        padding: .6rem 0
      }

      .sp-slot-card {
        border: 1px solid #dfe6ff;
        border-radius: 8px;
        padding: .45rem .5rem;
        background: #fafbff;
        margin-bottom: .4rem
      }

      .sp-slot-card:last-child {
        margin-bottom: 0
      }

      .sp-slot-code {
        font-size: .75rem;
        font-weight: 700;
        color: #1a237e;
        line-height: 1.2
      }

      .sp-slot-title {
        font-size: .72rem;
        color: #4b5563;
        line-height: 1.25;
        margin-top: .15rem
      }

      .sp-slot-meta {
        font-size: .69rem;
        color: #5f6b86;
        margin-top: .25rem
      }

      .sp-att-bar {
        height: 7px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
        margin-top: .35rem
      }

      .sp-att-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width .4s
      }

      .att-high {
        background: #2e7d32
      }

      .att-mid {
        background: #f9a825
      }

      .att-low {
        background: #c62828
      }

      .sp-result-card {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e8eaf6;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04)
      }

      .sp-result-header {
        background: linear-gradient(90deg, #1a237e, #3949ab);
        color: #fff;
        padding: .9rem 1.2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .5rem
      }

      .sp-result-header .rh-name {
        font-weight: 700;
        font-size: .95rem
      }

      .sp-result-header .rh-meta {
        font-size: .78rem;
        opacity: .8
      }

      .sp-stat-pill {
        text-align: center;
        padding: .75rem 1rem;
        border-right: 1px solid #f0f0f0
      }

      .sp-stat-pill:last-child {
        border-right: none
      }

      .sp-stat-pill .val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a237e
      }

      .sp-stat-pill .lbl {
        font-size: .67rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .05em
      }

      .gr-badge {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 6px;
        text-align: center;
        font-weight: 800;
        font-size: .85rem;
        background: #e8eaf6;
        color: #1a237e
      }

      .sp-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: .75rem
      }

      .sp-info-item {
        display: flex;
        gap: .6rem;
        align-items: flex-start
      }

      .sp-info-item .sp-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        flex-shrink: 0;
        background: #e8eaf6;
        color: #3949ab;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem
      }

      .sp-info-item .lbl {
        font-size: .71rem;
        color: #6c757d
      }

      .sp-info-item .val {
        font-size: .88rem;
        font-weight: 600;
        color: #1a1a2e
      }

      .sp-fee-card {
        border-radius: 10px;
        border: 1px solid #e8eaf6;
        padding: 1rem 1.1rem;
        background: #fafbff;
        transition: box-shadow .2s
      }

      .sp-fee-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .09)
      }

      .sp-fee-amount {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1a237e
      }

      .pill-active {
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 50px;
        padding: .15rem .65rem;
        font-size: .75rem;
        font-weight: 600
      }

      .pill-inactive {
        background: #fce4ec;
        color: #b71c1c;
        border-radius: 50px;
        padding: .15rem .65rem;
        font-size: .75rem;
        font-weight: 600
      }

      .sp-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: #adb5bd
      }

      .sp-empty i {
        font-size: 2.2rem;
        margin-bottom: .7rem;
        display: block
      }

      @media(max-width:640px) {
        .sp-hero {
          padding: 1.5rem 1rem 3.5rem
        }

        .sp-stat-strip {
          margin: -1.5rem .75rem 0
        }

        .sp-content {
          padding: 1rem .75rem
        }

        .sp-stat-divider {
          display: none
        }
      }

      /* Custom Dashboard Styles */
      .std-dashboard-hero {
        background: linear-gradient(135deg, #6119d5 0%, #26bace 100%);
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
    </style>

    <div class="container-fluid">
      <!-- Hero Section -->
      <div class="std-dashboard-hero">
        <h1 class="text-light text-capitalize">Welcome, {{ $data->first_name ?? 'Student' }} {{ $data->last_name ?? 'Name' }}!</h1>
        <p>
          <i class="fas fa-id-card"></i> Roll No: {{ strtoupper($data->roll_no ?? 'ROLLNO-NULL') }} |
          <i class="fas fa-calendar-alt"></i> Batch: {{ $data->batchmaster->batch_name ?? '-' }} |
          <i class="fas fa-building "></i> <span class="text-capitalize"> {{ $data->deptmaster->name ?? '-' }}</span>
        </p>
      </div>

      <!-- Flash Messages -->
      @if(session('success'))
      <div style="max-width: 1200px; margin: 1rem auto; padding: .85rem 1.25rem; background: #e8f5e9; border-left: 4px solid #2e7d32; border-radius: 6px; display: flex; align-items: center; gap: .75rem;">
        <i class="fas fa-check-circle" style="color: #2e7d32; font-size: 1.2rem;"></i>
        <span style="color: #1b5e20; font-size: .9rem; font-weight: 500;">{{ session('success') }}</span>
        <button onclick="this.parentElement.style.display='none'" style="margin-left: auto; background: none; border: none; color: #2e7d32; cursor: pointer; font-size: 1.2rem;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      @endif

      @if(session('error'))
      <div style="max-width: 1200px; margin: 1rem auto; padding: .85rem 1.25rem; background: #ffebee; border-left: 4px solid #c62828; border-radius: 6px; display: flex; align-items: center; gap: .75rem;">
        <i class="fas fa-exclamation-circle" style="color: #c62828; font-size: 1.2rem;"></i>
        <span style="color: #b71c1c; font-size: .9rem; font-weight: 500;">{{ session('error') }}</span>
        <button onclick="this.parentElement.style.display='none'" style="margin-left: auto; background: none; border: none; color: #c62828; cursor: pointer; font-size: 1.2rem;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      @endif

      @if($errors->any())
      <div style="max-width: 1200px; margin: 1rem auto; padding: .85rem 1.25rem; background: #fff3e0; border-left: 4px solid #f57f17; border-radius: 6px;">
        <div style="display: flex; align-items: center; gap: .75rem; margin-bottom: .5rem;">
          <i class="fas fa-exclamation-triangle" style="color: #f57f17; font-size: 1.2rem;"></i>
          <span style="color: #e65100; font-size: .9rem; font-weight: 600;">Please correct the following errors:</span>
          <button onclick="this.parentElement.parentElement.style.display='none'" style="margin-left: auto; background: none; border: none; color: #f57f17; cursor: pointer; font-size: 1.2rem;">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <ul style="margin: 0; padding-left: 2rem; color: #e65100; font-size: .85rem;">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif


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
        <!-- <button class="std-tab-btn" onclick="switchTab('activities')">
          <i class="fas fa-calendar-star"></i> Activities
        </button> -->
        <button class="std-tab-btn" onclick="switchTab('fees')">
          <i class="fas fa-wallet"></i> Fee Payments
        </button>
        <!-- <button class="std-tab-btn" onclick="switchTab('exams')">
          <i class="fas fa-file-signature"></i> Examinations
        </button> -->
      </div>

      <!-- Tab Contents -->
      {{-- ── ABOUT ── --}}
      <div id="tab-overview" class="std-tab-content active">
        <div class="std-section-card">
          <div class="std-section-header">
            <h3 class="std-section-title"><i class="fas fa-home"></i> Dashboard Overview</h3>
          </div>
          <div class="sp-card">
            <div class="sp-card-header">
              <div class="sp-icon" style="background:#e8eaf6;color:#1a237e;"><i class="fas fa-id-card"></i></div>
              <h4>Personal Information</h4>
            </div>
            <div class="sp-info-grid">
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-birthday-cake"></i></div>
                <div>
                  <div class="lbl">Date of Birth</div>
                  <div class="val">{{ $data->dob ? date('d M Y',strtotime($data->dob)) : '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-venus-mars"></i></div>
                <div>
                  <div class="lbl">Gender</div>
                  <div class="val">{{ $data->gender==1?'Male':'Female' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-pray"></i></div>
                <div>
                  <div class="lbl">Religion</div>
                  <div class="val text-capitalize">{{ $data->religionmaster->name ?? '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-tint"></i></div>
                <div>
                  <div class="lbl">Blood Group</div>
                  <div class="val">{{ $data->bloodgroup->title ?? '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-globe"></i></div>
                <div>
                  <div class="lbl">Nationality</div>
                  <div class="val text-capitalize">{{ $data->nationalitymaster->name ?? '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-phone"></i></div>
                <div>
                  <div class="lbl">Mobile</div>
                  <div class="val">{{ $data->mobile_no ?? '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item">
                <div class="sp-info-icon"><i class="fas fa-envelope"></i></div>
                <div>
                  <div class="lbl">Email</div>
                  <div class="val">{{ $data->mail_id ?? '—' }}</div>
                </div>
              </div>
              <div class="sp-info-item" style="grid-column:span 2;">
                <div class="sp-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                  <div class="lbl">Address</div>
                  <div class="val text-capitalize">{{ $data->address ?? '—' }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      {{-- ── TIMETABLE ── --}}
      <div id="tab-timetable" class="std-tab-content">
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background:#fce4ec;color:#c62828;"><i class="fas fa-calendar-alt"></i></div>
            <h4>Weekly Timetable</h4>
            @php
            $dayOrder = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
            $allSlots = collect($timetableByDay ?? collect())->flatten(1)->values();
            $orderedDays = collect($timetableByDay ?? collect())->keys()->sortBy(fn($d) => $dayOrder[$d] ?? 99)->values();
            $hours = $allSlots
            ->filter(fn($slot) => !empty($slot['hour']))
            ->groupBy(fn($slot) => (string) ($slot['hour'] ?? ''))
            ->map(function($slots, $label) {
            $first = $slots->first();
            $sort = (int) ($first['hour_sort'] ?? 0);
            if ($sort <= 0 && preg_match('/\d+/', (string) $label, $m)) {
              $sort=(int) $m[0];
              }
              if ($sort <=0) {
              $sort=999;
              }
              return ['label'=> (string) $label, 'sort' => $sort];
              })
              ->sortBy('sort')
              ->pluck('label')
              ->values();

              $gridMap = [];
              foreach ($allSlots as $slot) {
              $day = (string) ($slot['weekday'] ?? 'Unknown');
              $hour = (string) ($slot['hour'] ?? '');
              if ($hour === '') {
              continue;
              }
              if (!isset($gridMap[$day])) {
              $gridMap[$day] = [];
              }
              if (!isset($gridMap[$day][$hour])) {
              $gridMap[$day][$hour] = [];
              }
              $gridMap[$day][$hour][] = $slot;
              }
              @endphp
              <span class="sp-count">{{ $allSlots->count() }} slots</span>
          </div>

          @if($allSlots->isEmpty())
          <div class="sp-empty"><i class="fas fa-calendar-times"></i>No timetable found for this student.</div>
          @else
          <div class="sp-grid-wrap">
            <table class="sp-grid-table">
              <thead>
                <tr>
                  <th class="day-col">Day / Hour</th>
                  @foreach($hours as $hour)
                  <th>{{ $hour }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($orderedDays as $day)
                <tr>
                  <td class="day-col">{{ $day }}</td>
                  @foreach($hours as $hour)
                  @php $cellSlots = $gridMap[$day][$hour] ?? []; @endphp
                  <td>
                    @if(empty($cellSlots))
                    <div class="sp-cell-empty">-</div>
                    @else
                    @foreach($cellSlots as $slot)
                    <div class="sp-slot-card">
                      <div class="sp-slot-code">{{ $slot['course_code'] ?? '—' }}</div>
                      <div class="sp-slot-title">{{ $slot['course_title'] ?? 'Untitled Course' }}</div>
                      <div class="sp-slot-meta">
                        <i class="fas fa-user-tie"></i>
                        {{ $slot['faculty'] ?? 'TBA' }}
                      </div>
                      <div class="sp-slot-meta">
                        <i class="fas fa-sitemap"></i>
                        {{ $slot['delivery_type'] ?? 'COMMON' }}
                        &nbsp;|&nbsp;
                        {{ $slot['group_label'] ?? (!is_null($slot['group'] ?? null) ? 'Group '.$slot['group'] : 'Group —') }}
                      </div>
                      <div class="sp-slot-meta">
                        <i class="fas fa-door-open"></i>
                        {{ $slot['room'] ?? 'TBA' }}
                        &nbsp;|&nbsp;
                        Shift {{ $slot['shift'] ?? '—' }}
                      </div>
                    </div>
                    @endforeach
                    @endif
                  </td>
                  @endforeach
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
      {{-- ── COURSES ── --}}
      <div id="tab-courses" class="std-tab-content">
        @php
        $courseDeliveryMap = $courseDeliveryMap ?? [];
        $courseOfferingSubjectMap = $courseOfferingSubjectMap ?? [];
        $studentMajorDeliveryType = $studentMajorDeliveryType ?? 'COMMON';
        $programOfferingSubjectTitle = $programOfferingSubjectTitle ?? '—';
        $faComponentNames = $faComponentNames ?? collect();
        $faComponentNames = collect($faComponentNames)->values();
        $faMarksBySemesterCourse = $faMarksBySemesterCourse ?? collect();
        $ctColors = [
        'CC' => ['bg'=>'#e8eaf6','color'=>'#1a237e'],
        'GE' => ['bg'=>'#e8f5e9','color'=>'#1b5e20'],
        'SEC' => ['bg'=>'#fff3e0','color'=>'#e65100'],
        'DSE' => ['bg'=>'#fce4ec','color'=>'#880e4f'],
        'AECC' => ['bg'=>'#e3f2fd','color'=>'#0d47a1'],
        'MDC' => ['bg'=>'#f3e5f5','color'=>'#4a148c'],
        'MAJ' => ['bg'=>'#e0f7fa','color'=>'#006064'],
        'MIN' => ['bg'=>'#fff8e1','color'=>'#f57f17'],
        ];
        $defaultCt = ['bg'=>'#f5f5f5','color'=>'#555'];
        @endphp
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background:#e8eaf6;color:#1a237e;"><i class="fas fa-book-open"></i></div>
            <h4>Enrolled Courses</h4>
            <span class="sp-count">{{ $studentCourses->count() }}</span>
          </div>

          @php
          $electiveCoursesBySemester = $electiveCoursesBySemester ?? collect();
          $electiveSemesterMeta = $electiveCoursesBySemester->mapWithKeys(function ($courses, $semId) {
          return [(int) $semId => ($courses->first()?->semestermaster?->title ?? ('Semester ' . $semId))];
          });
          @endphp

          @if($electiveCoursesBySemester->isNotEmpty())
          <div style="border:1px solid #dbe7f5;background:#f8fbff;border-radius:10px;padding:1rem;margin-bottom:1rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.75rem;">
              <div style="font-size:.92rem;font-weight:700;color:#1a237e;">
                <i class="fas fa-list-check me-1"></i> Elective Course Selection
              </div>
              <span style="font-size:.74rem;color:#5f6b86;">Choose your electives semester-wise and confirm once.</span>
            </div>

            <form action="{{ route('student.console.electives.confirm') }}" method="POST" id="studentElectiveForm">
              @csrf
              <div class="row g-2 align-items-end">
                <div class="col-md-4">
                  <label for="electiveSemesterFilter" style="font-size:.75rem;font-weight:600;color:#5f6b86;">Select Semester</label>
                  <select class="form-select form-select-sm" name="semester_id" id="electiveSemesterFilter" required>
                    <option value="">Choose semester</option>
                    @foreach($electiveSemesterMeta as $semId => $semTitle)
                    <option value="{{ $semId }}" {{ (string) old('semester_id') === (string) $semId ? 'selected' : '' }}>{{ $semTitle }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-8">
                  <label style="font-size:.75rem;font-weight:600;color:#5f6b86;">Available Elective Courses</label>
                  <input type="hidden" name="course_id" id="electiveCourseId" value="{{ old('course_id') }}">
                  <div id="electiveCourseChecklist" style="border:1px solid #d7deec;border-radius:8px;background:#fff;padding:.55rem;max-height:220px;overflow:auto;">
                    @foreach($electiveCoursesBySemester as $semId => $semCourses)
                    <div class="elective-semester-group" data-sem="{{ $semId }}" style="margin-bottom:.35rem;">
                      @foreach($semCourses as $optCourse)
                      <label style="display:flex;align-items:flex-start;gap:.55rem;padding:.45rem .55rem;border:1px solid #edf1f7;border-radius:7px;margin-bottom:.35rem;cursor:pointer;background:#fbfcff;">
                        <input
                          type="checkbox"
                          class="elective-course-check"
                          value="{{ $optCourse->id }}"
                          data-sem="{{ $semId }}"
                          {{ (string) old('course_id') === (string) $optCourse->id ? 'checked' : '' }}
                          style="margin-top:.2rem;">
                        <span style="line-height:1.25;">
                          <span style="display:block;font-size:.78rem;font-weight:700;color:#1f2a44;">{{ $optCourse->course_code }} - {{ $optCourse->course_title }}</span>
                          @if($optCourse->coursetypemaster)
                          <span style="display:inline-block;margin-top:.18rem;font-size:.7rem;color:#475569;background:#eef2ff;padding:.1rem .4rem;border-radius:999px;">{{ $optCourse->coursetypemaster->title }}</span>
                          @endif
                        </span>
                      </label>
                      @endforeach
                    </div>
                    @endforeach
                  </div>
                </div>

                <div class="col-12 d-flex justify-content-end mt-2">
                  <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-check-circle me-1"></i> Confirm Electives
                  </button>
                </div>
              </div>
            </form>
          </div>
          @endif

          @if($studentCourses->isEmpty())
          <div class="sp-empty"><i class="fas fa-book"></i>No courses found.</div>
          @else
          @foreach($coursesBySemester as $semTitle => $courses)
          <div style="display:flex;align-items:center;gap:.5rem;margin:.9rem 0 .5rem;padding-bottom:.6rem;border-bottom:1px solid #f0f0f0;">
            <span style="background:#e8eaf6;color:#1a237e;border-radius:6px;padding:.25rem .8rem;font-weight:700;font-size:.82rem;">
              <i class="fas fa-layer-group me-1"></i>{{ $semTitle }}
            </span>
            <span class="sp-count">{{ $courses->count() }} courses</span>
          </div>
          <table class="sp-table mb-3" style="min-width:600px;">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Course Title</th>
                <th>Course Type</th>
                <th>Delivery</th>
                <th>Cr.</th>
                @foreach($faComponentNames as $componentName)
                <th>{{ $componentName }}</th>
                @endforeach
                <th>FA Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($courses as $i => $course)
              @php
              $typeTitle = $course->coursemaster?->coursetypemaster?->title ?? '';
              $ctKey = preg_replace('/\s.*/', '', $typeTitle);
              $ct = $ctColors[$ctKey] ?? $defaultCt;
              $ctBg = $ct['bg'];
              $ctFg = $ct['color'];
              $deliveryKey = (string) ($course->semester ?? $course->coursemaster?->semester_id ?? '') . '_' . (string) ($course->course_id ?? '');
              $deliveryType = $courseDeliveryMap[$deliveryKey] ?? $studentMajorDeliveryType;
              $offeredBySubject = $courseOfferingSubjectMap[$deliveryKey] ?? $programOfferingSubjectTitle;
              $faKey = (string) ((int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0)) . '_' . (string) ((int) ($course->course_id ?? 0));
              $faData = $faMarksBySemesterCourse->get($faKey);
              @endphp
              <tr>
                <td style="color:#adb5bd;">{{ $i+1 }}</td>
                <td><span style="background:#e8eaf6;color:#3949ab;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:600;">{{ $course->coursemaster?->course_code ?? '—' }}</span></td>
                <td style="font-weight:500;">{{ $course->coursemaster?->course_title ?? '—' }}</td>
                <td>
                  @if($typeTitle)
                  <span style="background:{{ $ctBg }};color:{{ $ctFg }};border-radius:4px;padding:.1rem .5rem;font-size:.76rem;font-weight:700;white-space:nowrap;">
                    {{ $typeTitle }}
                  </span>
                  @else
                  —
                  @endif
                </td>
                <td>
                  <span style="background:#e3f2fd;color:#1565c0;border-radius:4px;padding:.1rem .5rem;font-size:.74rem;font-weight:700;white-space:nowrap;">
                    {{ $deliveryType }}
                  </span>
                </td>

                <td>{{ $course->coursemaster?->credits ?? '—' }}</td>
                @foreach($faComponentNames as $componentName)
                @php
                $componentMark = $faData['components'][$componentName] ?? null;
                @endphp
                <td>
                  @if($componentMark !== null)
                  <span style="background:#fff3e0;color:#e65100;border-radius:4px;padding:.1rem .45rem;font-size:.76rem;font-weight:700;white-space:nowrap;">
                    {{ rtrim(rtrim(number_format((float) $componentMark, 2, '.', ''), '0'), '.') }}
                  </span>
                  @else
                  —
                  @endif
                </td>
                @endforeach
                <td>
                  @if($faData)
                  <span style="background:#e8f5e9;color:#1b5e20;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:700;white-space:nowrap;">
                    {{ (int) ($faData['total'] ?? 0) }}
                  </span>
                  @else
                  —
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endforeach
          @endif
        </div>
      </div>
      {{-- ── ATTENDANCE ── --}}
      <div id="tab-attendance" class="std-tab-content">
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background:#e0f7fa;color:#00695c;"><i class="fas fa-check-circle"></i></div>
            <h4>Attendance Summary</h4>
            <span class="sp-count">{{ $attendanceSummary->count() }} courses</span>
          </div>
          @if($attendanceSummary->isEmpty())
          <div class="sp-empty"><i class="fas fa-clipboard"></i>No attendance records found.</div>
          @else
          @php
          $attTotal=$attendanceSummary->sum('total');
          $attPresent=$attendanceSummary->sum('present');
          $attAbsent=$attendanceSummary->sum('absent');
          $attOverall=$attTotal>0?round(($attPresent/$attTotal)*100,1):0;
          @endphp
          <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
              <div style="border-radius:8px;padding:.9rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
                <div style="font-size:1.5rem;font-weight:800;color:#1a237e;">{{ $attTotal }}</div>
                <div style="font-size:.73rem;color:#6c757d;text-transform:uppercase;">Total Classes</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="border-radius:8px;padding:.9rem;text-align:center;background:#e8f5e9;border:1px solid #c8e6c9;">
                <div style="font-size:1.5rem;font-weight:800;color:#2e7d32;">{{ $attPresent }}</div>
                <div style="font-size:.73rem;color:#388e3c;text-transform:uppercase;">Present</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="border-radius:8px;padding:.9rem;text-align:center;background:#fce4ec;border:1px solid #f8bbd9;">
                <div style="font-size:1.5rem;font-weight:800;color:#c62828;">{{ $attAbsent }}</div>
                <div style="font-size:.73rem;color:#c62828;text-transform:uppercase;">Absent</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div style="border-radius:8px;padding:.9rem;text-align:center;background:{{ $attOverall>=75?'#e8f5e9':($attOverall>=60?'#fffde7':'#fce4ec') }};border:1px solid {{ $attOverall>=75?'#c8e6c9':($attOverall>=60?'#fff176':'#f8bbd9') }};">
                <div style="font-size:1.5rem;font-weight:800;color:{{ $attOverall>=75?'#2e7d32':($attOverall>=60?'#f57f17':'#c62828') }};">{{ $attOverall }}%</div>
                <div style="font-size:.73rem;color:#6c757d;text-transform:uppercase;">Overall</div>
              </div>
            </div>
          </div>
          @if($attOverall < 75)
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:8px;">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Your overall attendance is below 75%. Please attend more classes to avoid debarment.</span>
        </div>
        @endif
        <table class="sp-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>Total</th>
              <th>Present</th>
              <th>Absent</th>
              <th style="min-width:160px;">Attendance</th>
            </tr>
          </thead>
          <tbody>
            @foreach($attendanceSummary as $i => $atten)
            @php $pct=$atten['percentage'];$barClass=$pct>=75?'att-high':($pct>=60?'att-mid':'att-low');$pctColor=$pct>=75?'#2e7d32':($pct>=60?'#f57f17':'#c62828'); @endphp
            <tr>
              <td style="color:#adb5bd;">{{ $i+1 }}</td>
              <td>
                <div style="font-weight:600;font-size:.85rem;">{{ $atten['course']->course_title ?? '—' }}</div>
                <div style="font-size:.75rem;color:#3949ab;">{{ $atten['course']->course_code ?? '' }}</div>
              </td>
              <td>{{ $atten['total'] }}</td>
              <td style="color:#2e7d32;font-weight:600;">{{ $atten['present'] }}</td>
              <td style="color:#c62828;font-weight:600;">{{ $atten['absent'] }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem;">
                  <div class="sp-att-bar" style="flex:1;">
                    <div class="sp-att-bar-fill {{ $barClass }}" style="width:{{ $pct }}%;"></div>
                  </div>
                  <span style="font-weight:700;font-size:.82rem;min-width:38px;text-align:right;color:{{ $pctColor }};">{{ $pct }}%</span>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>

    {{-- ── MENTORSHIP ── --}}
    <div id="tab-mentorship" class="std-tab-content">
      <style>
        .mentorship-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
          gap: 1.25rem;
          margin-bottom: 1.5rem;
        }

        .mentor-stat-card {
          background: #fff;
          border: 1px solid #e9ecef;
          border-radius: 8px;
          padding: 1.25rem;
          text-align: center;
          box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        }

        .mentor-stat-value {
          font-size: 2rem;
          font-weight: 700;
          color: #2575fc;
          margin: .5rem 0;
        }

        .mentor-stat-label {
          font-size: .85rem;
          color: #6c757d;
        }

        .mentorship-section {
          margin-bottom: 2rem;
        }

        .session-item {
          background: #f8f9fa;
          border-left: 4px solid #2575fc;
          padding: 1rem;
          border-radius: 4px;
          margin-bottom: .75rem;
        }

        .session-date {
          font-size: .75rem;
          color: #6c757d;
          font-weight: 600;
        }

        .session-title {
          font-weight: 600;
          color: #212529;
          margin: .5rem 0;
        }

        .session-feedback {
          font-size: .85rem;
          color: #495057;
          margin-top: .5rem;
          font-style: italic;
        }

        .assignment-card {
          background: #fff;
          border: 1px solid #e9ecef;
          border-radius: 6px;
          padding: 1.25rem;
          margin-bottom: 1rem;
        }

        .assignment-title {
          font-weight: 600;
          color: #212529;
          margin-bottom: .5rem;
        }

        .assignment-due {
          font-size: .75rem;
          color: #6c757d;
          margin-bottom: .75rem;
        }

        .upload-area {
          border: 2px dashed #2575fc;
          border-radius: 8px;
          padding: 2rem;
          text-align: center;
          cursor: pointer;
          transition: all .3s;
          background: #f0f7ff;
        }

        .upload-area:hover {
          background: #e3f2fd;
          border-color: #1565c0;
        }

        .upload-icon {
          font-size: 2.5rem;
          color: #2575fc;
          margin-bottom: .5rem;
        }

        .feedback-badge {
          display: inline-block;
          background: #e8f5e9;
          color: #2e7d32;
          padding: .35rem .75rem;
          border-radius: 50px;
          font-size: .75rem;
          font-weight: 600;
          margin-right: .5rem;
          margin-bottom: .5rem;
        }
      </style>

      <!-- Mentorship Stats -->
      @if($mentorName)
      <div class="mentorship-section">
        <div class="sp-card" style="background: linear-gradient(135deg, #6a11cb 30%, #2575fc 100%); color: #fff; margin-bottom: 1.5rem;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chalkboard-teacher" style="font-size: 1.8rem;"></i>
            </div>
            <div>
              <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem;">Your Mentor</div>
              <div style="font-size: 1.5rem; font-weight: 700;">{{ $mentorName }}</div>
            </div>
          </div>
        </div>
      </div>
      @endif

      <div class="mentorship-grid">
        <div class="mentor-stat-card">
          <i class="fas fa-users" style="font-size: 2rem; color: #2575fc;"></i>
          <div class="mentor-stat-value">{{ $mentorName ? 1 : 0 }}</div>
          <div class="mentor-stat-label">Assigned Mentor</div>
        </div>
        <div class="mentor-stat-card">
          <i class="fas fa-calendar-check" style="font-size: 2rem; color: #2e7d32;"></i>
          <div class="mentor-stat-value" style="color: #2e7d32;">{{ $mentorshipStats['attended_sessions'] }}</div>
          <div class="mentor-stat-label">Sessions Attended</div>
        </div>
        <div class="mentor-stat-card">
          <i class="fas fa-tasks" style="font-size: 2rem; color: #f57f17;"></i>
          <div class="mentor-stat-value" style="color: #f57f17;">{{ $mentorshipStats['completed_assignments'] }}</div>
          <div class="mentor-stat-label">Assignments Completed</div>
        </div>
      </div>

      <!-- Mentorship Attendance -->
      <div class="mentorship-section">
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background: #e3f2fd; color: #2575fc;"><i class="fas fa-calendar-alt"></i></div>
            <h4> Session Attendance</h4>
            <span class="sp-count">{{ $mentorshipAttendances->count() }} records</span>
          </div>
          <div style="overflow-x: auto;">
            @if($mentorshipAttendances->count() > 0)
            <table style="width: 100%; font-size: .85rem;">
              <thead style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <tr>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">#</th>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">Date</th>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">Session Duration</th>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">Topic</th>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">Agenda</th>
                  <th style="padding: .75rem; text-align: left; color: #fff; font-weight: 600;">Mode</th>
                  <th style="padding: .75rem; text-align: center; color: #fff; font-weight: 600;">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($mentorshipAttendances as $attendance)
                <tr style="border-bottom: 1px solid #dee2e6;">
                  <td style="padding: .75rem;">{{ $loop->iteration }}</td>
                  <td style="padding: .75rem;">{{ $attendance->session->session_date->format('M d, Y') }}</td>
                  <td style="padding: .75rem;">{{ $attendance->session->start_time }} - {{ $attendance->session->end_time }}</td>
                  <td style="padding: .75rem;">{{ $attendance->session->title }}</td>
                  <td style="padding: .75rem;">{{ $attendance->session->agenda }}</td>
                  <td style="padding: .75rem;">{{ $attendance->session->mode }}</td>
                  <td style="padding: .75rem; text-align: center;">
                    @if($attendance->status === 'present')
                    <span style="background: #e8f5e9; color: #2e7d32; padding: .25rem .5rem; border-radius: 4px; font-size: .75rem; font-weight: 600;">Present</span>
                    @elseif($attendance->status === 'absent')
                    <span style="background: #ffebee; color: #c62828; padding: .25rem .5rem; border-radius: 4px; font-size: .75rem; font-weight: 600;">Absent</span>
                    @else
                    <span style="background: #fff3e0; color: #e65100; padding: .25rem .5rem; border-radius: 4px; font-size: .75rem; font-weight: 600;">Excused</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <div class="sp-empty"><i class="fas fa-calendar-alt"></i>No attendance records found.</div>
            @endif
          </div>
        </div>
      </div>


      <!-- Assignments -->
      <div class="mentorship-section">
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background: #fce4ec; color: #c2185b;"><i class="fas fa-file-upload"></i></div>
            <h4>Assignments</h4>
            <span class="sp-count">{{ $mentorshipAssignments->count() }} assignments</span>
          </div>
          <div>
            @if($mentorshipAssignments->count() > 0)

            @foreach($mentorshipAssignments as $assignment)
            @php
            $submission = $assignment->submissions->first();
            @endphp
            <div class="assignment-card">
              <div class="assignment-title">{{ $assignment->title }}</div>
              @if($assignment->due_date)
              <div class="assignment-due">Due: {{ $assignment->due_date->format('F d, Y') }}</div>
              @endif
              <div style="font-size: .85rem; color: #495057; margin-bottom: 1rem;">{{ $assignment->description }}</div>

              @if($submission)
              @if($submission->status === 'graded')
              <div style="background: #e8f5e9; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; font-size: .85rem; color: #1b5e20;">
                <i class="fas fa-check-circle"></i> Submitted on {{ $submission->submitted_at->format('M d, Y') }}
                @if($submission->marks_obtained !== null)
                - <strong>Score: {{ $submission->marks_obtained }}/{{ $assignment->max_marks }}</strong>
                @endif
              </div>

              @if($submission->feedback)
              <div style="font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .5rem;">Mentor Feedback:</div>
              <div style="background: #f8f9fa; padding: .75rem; border-radius: 4px; font-size: .85rem; color: #495057;">
                {{ $submission->feedback }}
              </div>
              @endif
              @elseif($submission->status === 'submitted')
              <div style="background: #e3f2fd; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; font-size: .85rem; color: #1565c0;">
                <i class="fas fa-check-circle"></i> Submitted on {{ $submission->submitted_at->format('M d, Y') }} - Awaiting grading
              </div>
              @elseif($submission->status === 'pending')
              <div style="background: #fff3e0; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; font-size: .85rem; color: #e65100;">
                <i class="fas fa-clock"></i> Pending Submission
              </div>

              <form action="{{ route('student.mentorship.assignment.upload', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm{{ $assignment->id }}">
                @csrf
                <div style="font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .75rem;">Upload Assignment:</div>

                <!-- File input (hidden) -->
                <input type="file" id="fileInput{{ $assignment->id }}" name="file" style="display: none;" accept=".pdf,.doc,.docx,.sql,.zip,.txt" required onchange="handleFileSelect({{ $assignment->id }})">

                <!-- Upload area -->
                <div class="upload-area" onclick="document.getElementById('fileInput{{ $assignment->id }}').click()" id="uploadArea{{ $assignment->id }}">
                  <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                  <div style="font-weight: 600; color: #2575fc; margin-bottom: .25rem;">Click to upload or drag and drop</div>
                  <div style="font-size: .75rem; color: #6c757d;">PDF, DOC, PPT, files up to 10MB</div>
                </div>

                <!-- File preview -->
                <div id="filePreview{{ $assignment->id }}" style="display: none; margin-top: .75rem; padding: .75rem; background: #f8f9fa; border-radius: 6px; font-size: .85rem;">
                  <i class="fas fa-file" style="color: #2575fc;"></i>
                  <span id="fileName{{ $assignment->id }}" style="color: #495057; margin-left: .5rem;"></span>
                  <button type="button" onclick="clearFile({{ $assignment->id }})" style="float: right; background: none; border: none; color: #c62828; cursor: pointer; font-size: .85rem;">
                    <i class="fas fa-times"></i> Remove
                  </button>
                </div>

                <!-- Optional response/notes -->
                <div style="margin-top: 1rem;">
                  <label style="font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .5rem; display: block;">Additional Notes (Optional):</label>
                  <textarea name="response" rows="3" style="width: 100%; padding: .5rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: .85rem; font-family: inherit;" placeholder="Add any notes or comments about your submission..."></textarea>
                </div>

                <!-- Submit button -->
                <button type="submit" style="margin-top: 1rem; width: 100%; background: #2575fc; color: #fff; border: none; padding: .75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: .85rem;" onmouseover="this.style.background='#1565c0'" onmouseout="this.style.background='#2575fc'">
                  <i class="fas fa-upload"></i> Submit Assignment
                </button>
              </form>
              @else
              <div style="background: #fff3e0; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; font-size: .85rem; color: #e65100;">
                <i class="fas fa-clock"></i> Pending Submission
              </div>
              @endif

              @if($submission->submission_path)
              <div style="margin-top: 1rem; font-size: .85rem;">
                <a href="{{ Storage::disk('s3')->url($submission->submission_path) }}" target="_blank" style="color: #2575fc; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem;">
                  <i class="fas fa-file-download"></i> View Submission
                </a>
              </div>
              @endif
              @else
              <div style="background: #e3f2fd; border-radius: 6px; padding: .75rem; margin-bottom: 1rem; font-size: .85rem; color: #1565c0;">
                <i class="fas fa-hourglass-start"></i> Not Started
              </div>

              <form action="{{ route('student.mentorship.assignment.upload', $assignment->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm{{ $assignment->id }}">
                @csrf
                <div style="font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .75rem;">Upload Assignment:</div>

                <!-- File input (hidden) -->
                <input type="file" id="fileInput{{ $assignment->id }}" name="file" style="display: none;" accept=".pdf,.doc,.docx,.sql,.zip,.txt" required onchange="handleFileSelect({{ $assignment->id }})">

                <!-- Upload area -->
                <div class="upload-area" onclick="document.getElementById('fileInput{{ $assignment->id }}').click()" id="uploadArea{{ $assignment->id }}">
                  <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                  <div style="font-weight: 600; color: #2575fc; margin-bottom: .25rem;">Click to upload or drag and drop</div>
                  <div style="font-size: .75rem; color: #6c757d;">PDF, DOC, PPT files up to 10MB</div>
                </div>

                <!-- File preview -->
                <div id="filePreview{{ $assignment->id }}" style="display: none; margin-top: .75rem; padding: .75rem; background: #f8f9fa; border-radius: 6px; font-size: .85rem;">
                  <i class="fas fa-file" style="color: #2575fc;"></i>
                  <span id="fileName{{ $assignment->id }}" style="color: #495057; margin-left: .5rem;"></span>
                  <button type="button" onclick="clearFile({{ $assignment->id }})" style="float: right; background: none; border: none; color: #c62828; cursor: pointer; font-size: .85rem;">
                    <i class="fas fa-times"></i> Remove
                  </button>
                </div>

                <!-- Optional response/notes -->
                <div style="margin-top: 1rem;">
                  <label style="font-size: .75rem; font-weight: 600; color: #6c757d; margin-bottom: .5rem; display: block;">Additional Notes (Optional):</label>
                  <textarea name="response" rows="3" style="width: 100%; padding: .5rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: .85rem; font-family: inherit;" placeholder="Add any notes or comments about your submission..."></textarea>
                </div>

                <!-- Submit button -->
                <button type="submit" style="margin-top: 1rem; width: 100%; background: #2575fc; color: #fff; border: none; padding: .75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: .85rem;" onmouseover="this.style.background='#1565c0'" onmouseout="this.style.background='#2575fc'">
                  <i class="fas fa-upload"></i> Submit Assignment
                </button>
              </form>
              @endif

              @if($assignment->attachment_path)
              <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef; font-size: .85rem;">
                <a href="{{ Storage::disk('s3')->url($assignment->attachment_path) }}" target="_blank" style="color: #2575fc; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem;">
                  <i class="fas fa-paperclip"></i> Download Assignment Instructions
                </a>
              </div>
              @endif
            </div>
            @endforeach
            @else
            <div class="sp-empty"><i class="fas fa-file-upload"></i>No assignments found.</div>
            @endif
          </div>
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
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-rupee-sign"></i></div>
          <h4>Fee Payments</h4>
          <span class="sp-count">{{ count($data->feepayment) }} records</span>
        </div>
        @if(!count($data->feepayment))
        <div class="sp-empty"><i class="fas fa-money-bill"></i>No fee payment records found.</div>
        @else
        @php $totalPaid=$data->feepayment->sum('amount'); @endphp
        <div style="background:#e8f5e9;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
          <i class="fas fa-check-circle" style="color:#2e7d32;"></i>
          <span style="font-size:.85rem;color:#1b5e20;">Total Paid: <strong>₹ {{ number_format($totalPaid,2) }}</strong></span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.85rem;">
          @foreach($data->feepayment as $pay)
          <div class="sp-fee-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem;">
              <span style="font-size:.72rem;color:#6c757d;">{{ date('d M Y',strtotime($pay->transaction_date)) }}</span>
              @if($pay->gatewaytype)
              <span style="background:#e3f2fd;color:#1565c0;border-radius:50px;padding:.1rem .55rem;font-size:.72rem;font-weight:600;">{{ $pay->gateway_type_id!=3?$pay->gatewaytype->title:'Cash' }}</span>
              @endif
            </div>
            <div style="font-size:.8rem;color:#495057;font-weight:600;margin-bottom:.2rem;">#{{ $pay->invoice_id }}</div>
            <div style="font-size:.78rem;color:#6c757d;margin-bottom:.5rem;">{{ $pay->feepaymentinfo->quarter_title ?? '' }}</div>
            <div class="sp-fee-amount"><i class="fas fa-rupee-sign" style="font-size:1rem;"></i> {{ number_format($pay->amount,2) }}</div>
          </div>
          @endforeach
        </div>
        @endif
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

  // Handle file selection for assignment upload
  function handleFileSelect(assignmentId) {
    const fileInput = document.getElementById('fileInput' + assignmentId);
    const filePreview = document.getElementById('filePreview' + assignmentId);
    const fileName = document.getElementById('fileName' + assignmentId);
    const uploadArea = document.getElementById('uploadArea' + assignmentId);

    if (fileInput.files.length > 0) {
      const file = fileInput.files[0];

      // Check file size (10MB = 10485760 bytes)
      if (file.size > 10485760) {
        alert('File size exceeds 10MB limit. Please choose a smaller file.');
        fileInput.value = '';
        return;
      }

      // Show file name
      fileName.textContent = file.name;
      filePreview.style.display = 'block';
      uploadArea.style.opacity = '0.5';
    }
  }

  // Clear selected file
  function clearFile(assignmentId) {
    const fileInput = document.getElementById('fileInput' + assignmentId);
    const filePreview = document.getElementById('filePreview' + assignmentId);
    const uploadArea = document.getElementById('uploadArea' + assignmentId);

    fileInput.value = '';
    filePreview.style.display = 'none';
    uploadArea.style.opacity = '1';
  }

  function initElectiveSelector() {
    const semesterSelect = document.getElementById('electiveSemesterFilter');
    const courseIdInput = document.getElementById('electiveCourseId');
    const electiveForm = document.getElementById('studentElectiveForm');
    const groups = Array.from(document.querySelectorAll('.elective-semester-group'));
    const checks = Array.from(document.querySelectorAll('.elective-course-check'));

    if (!semesterSelect || !courseIdInput || !electiveForm || !groups.length || !checks.length) {
      return;
    }

    const toggleOptions = () => {
      const selectedSem = semesterSelect.value;

      groups.forEach((group) => {
        const sem = group.getAttribute('data-sem') || '';
        const shouldShow = selectedSem !== '' && sem === selectedSem;
        group.style.display = shouldShow ? '' : 'none';

        if (!shouldShow) {
          group.querySelectorAll('.elective-course-check').forEach((check) => {
            check.checked = false;
          });
        }
      });

      const checkedVisible = checks.filter((check) => {
        const group = check.closest('.elective-semester-group');
        return group && group.style.display !== 'none' && check.checked;
      });

      if (checkedVisible.length > 1) {
        checkedVisible.slice(1).forEach((check) => {
          check.checked = false;
        });
      }

      courseIdInput.value = checkedVisible[0] ? checkedVisible[0].value : '';
    };

    checks.forEach((check) => {
      check.addEventListener('change', function() {
        if (this.checked) {
          checks.forEach((other) => {
            if (other !== this) {
              other.checked = false;
            }
          });
          courseIdInput.value = this.value;
        } else if (courseIdInput.value === this.value) {
          courseIdInput.value = '';
        }
      });
    });

    electiveForm.addEventListener('submit', function(e) {
      if (!courseIdInput.value) {
        e.preventDefault();
        alert('Please select one elective course.');
      }
    });

    semesterSelect.addEventListener('change', toggleOptions);
    toggleOptions();
  }

  document.addEventListener('DOMContentLoaded', initElectiveSelector);
</script>
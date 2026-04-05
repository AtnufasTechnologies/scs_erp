@include('includes.header')

<div class="wrapper">


  <!--start main wrapper-->
  <main class="page-content" style="overflow-x:hidden;">

    <style>
      .sp-hero {
        background: linear-gradient(135deg, #653dca 0%, #28937d 40%, #0dc0c9 100%);
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
    </style>

    {{-- HERO --}}
    <div class="sp-hero">
      <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
        <div>
          @if($data->gender == 1)
          <img src="{{ asset('admin/images/male.png') }}" class="sp-avatar" alt="Avatar">
          @else
          <img src="{{ asset('admin/images/female.png') }}" class="sp-avatar" alt="Avatar">
          @endif
        </div>
        <div style="flex:1;min-width:200px;">
          <p class="sp-sub mb-1 text-uppercase">
            <i class="fas fa-id-badge me-1"></i> {{ $data->roll_no }}
            &nbsp;·&nbsp;
            <i class="fas fa-university me-1"></i> {{ $data->campusmaster->name ?? '' }}
          </p>
          <h1 class="text-capitalize mb-0 text-light">{{ $data->first_name }} {{ $data->last_name }}</h1>
          <p class="sp-sub mt-1 mb-2 text-capitalize">
            {{ $data->deptmaster->name ?? '' }}
            @if($data->programgroup && $data->programgroup->programInfo)
            &nbsp;·&nbsp; {{ $data->programgroup->programInfo->name }}
            @endif
            @if($data->batchmaster)
            &nbsp;·&nbsp; Batch {{ $data->batchmaster->batch_name }}
            @endif
          </p>
          <div>
            @if($data->mail_id)<span class="sp-badge"><i class="fas fa-envelope"></i> {{ $data->mail_id }}</span>@endif
            @if($data->mobile_no)<span class="sp-badge"><i class="fas fa-phone"></i> {{ $data->mobile_no }}</span>@endif
            @if($data->bloodgroup)<span class="sp-badge"><i class="fas fa-tint"></i> {{ $data->bloodgroup->title ?? '' }}</span>@endif
            <span class="sp-badge"><i class="fas fa-layer-group"></i> Year {{ $data->current_year ?? '—' }}</span>
          </div>
        </div>
        <div>
          <a href="{{ route('student.feedback.list') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);">
            <i class="fas fa-star me-1"></i> Feedback
          </a>
          <a href="{{ url('logout')  }}" class="btn btn-sm btn-danger">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
          </a>
        </div>
      </div>
    </div>

    {{-- STAT STRIP --}}
    <div style="max-width:1200px;margin:0 auto;padding:0 1.5rem;">
      <div class="sp-stat-strip">
        <div class="row g-0">
          <div class="col sp-stat">
            <div class="sp-stat-val">{{ $studentCourses->count() }}</div>
            <div class="sp-stat-lbl">Courses</div>
          </div>
          <div class="col sp-stat sp-stat-divider">
            @php $overallAtt = $attendanceSummary->isNotEmpty() ? round($attendanceSummary->avg('percentage'),1) : 0; @endphp
            <div class="sp-stat-val" style="color:{{ $overallAtt>=75?'#2e7d32':($overallAtt>=60?'#f57f17':'#c62828') }}">{{ $overallAtt }}%</div>
            <div class="sp-stat-lbl">Avg Attendance</div>
          </div>
          <div class="col sp-stat sp-stat-divider">
            <div class="sp-stat-val">{{ $internalMarks->count() }}</div>
            <div class="sp-stat-lbl">FA Entries</div>
          </div>
          <div class="col sp-stat sp-stat-divider">
            <div class="sp-stat-val">{{ $examResults->count() }}</div>
            <div class="sp-stat-lbl">Results</div>
          </div>
          <div class="col sp-stat sp-stat-divider">
            @php $latestResult = $examResults->first(); @endphp
            <div class="sp-stat-val">{{ $latestResult ? $latestResult->sgpa : '—' }}</div>
            <div class="sp-stat-lbl">Latest SGPA</div>
          </div>
          <div class="col sp-stat sp-stat-divider">
            <div class="sp-stat-val">{{ count($data->feepayment) }}</div>
            <div class="sp-stat-lbl">Fee Records</div>
          </div>
        </div>
      </div>
    </div>

    {{-- TABS --}}
    <div class="container">

      <div class="sp-tabs-wrap mt-3">
        <div class="sp-tabs">
          <button class="sp-tab active" onclick="spTab(event,'tab-about')"><i class="fas fa-user"></i> About</button>
          <button class="sp-tab" onclick="spTab(event,'tab-timetable')"><i class="fas fa-calendar-alt"></i> Timetable</button>
          <button class="sp-tab" onclick="spTab(event,'tab-attendance')"><i class="fas fa-check-circle"></i> Attendance</button>
          <button class="sp-tab" onclick="spTab(event,'tab-fa')"><i class="fas fa-pen"></i> Internal Marks</button>
          <button class="sp-tab" onclick="spTab(event,'tab-courses')"><i class="fas fa-book"></i> Courses</button>
          <button class="sp-tab" onclick="spTab(event,'tab-results')"><i class="fas fa-trophy"></i> Exam Results</button>
          <button class="sp-tab" onclick="spTab(event,'tab-exam-reg')"><i class="fas fa-clipboard-check"></i> Exam Registration</button>
          <button class="sp-tab" onclick="spTab(event,'tab-fee')"><i class="fas fa-rupee-sign"></i> Fee</button>
        </div>
      </div>
    </div>
    <div class="sp-content">

      {{-- ── ABOUT ── --}}
      <div class="sp-panel active" id="tab-about">
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
        @if($examStudent)
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-graduation-cap"></i></div>
            <h4>Academic Profile</h4>
          </div>
          <div class="sp-info-grid">
            <div class="sp-info-item">
              <div class="sp-info-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-id-badge"></i></div>
              <div>
                <div class="lbl">Enrollment No</div>
                <div class="val">{{ $examStudent->enrollment_no }}</div>
              </div>
            </div>
            <div class="sp-info-item">
              <div class="sp-info-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-layer-group"></i></div>
              <div>
                <div class="lbl">Current Semester</div>
                <div class="val">Semester {{ $examStudent->current_semester }}</div>
              </div>
            </div>
            <div class="sp-info-item">
              <div class="sp-info-icon" style="background:#e3f2fd;color:#1565c0;"><i class="fas fa-circle"></i></div>
              <div>
                <div class="lbl">Status</div>
                <div class="val text-capitalize">{{ $examStudent->status }}</div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>

      {{-- ── TIMETABLE ── --}}
      <div class="sp-panel" id="tab-timetable">
        <div class="sp-card">
          <div class="sp-card-header">
            <div class="sp-icon" style="background:#fce4ec;color:#c62828;"><i class="fas fa-calendar-alt"></i></div>
            <h4>Weekly Timetable</h4>
            <span class="sp-count">{{ $timetableByDay->sum(fn($s) => $s->count()) }} slots</span>
          </div>
          @if($timetableByDay->isEmpty())
          <div class="sp-empty"><i class="fas fa-calendar-times"></i>No timetable found for this batch.</div>
          @else
          @php $dayOrder=['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7]; $sorted=$timetableByDay->sortBy(fn($s,$d)=>$dayOrder[$d]??99); @endphp
          @foreach($sorted as $day => $slots)
          <div class="sp-day-label"><i class="fas fa-sun" style="font-size:.75rem;"></i> {{ $day }}</div>
          <div style="display:flex;flex-wrap:wrap;margin-bottom:.5rem;">
            @foreach($slots->sortBy('hour_id') as $slot)
            <div class="sp-slot-badge">
              <span class="hour-num">{{ $slot->hourmaster->title ?? '?' }}</span>
              <div>
                <div style="font-weight:600;line-height:1.2;">{{ $slot->coursemaster->course_code ?? '—' }}</div>
                <div style="font-size:.72rem;color:#6c757d;line-height:1.2;">{{ Str::limit($slot->coursemaster->course_title ?? '—', 28) }}</div>
                <div style="font-size:.7rem;color:#3949ab;">
                  <i class="fas fa-user-tie" style="font-size:.65rem;"></i>
                  {{ $slot->faculty ? $slot->faculty->FIRST_NAME.' '.$slot->faculty->LAST_NAME : '—' }}
                  @if($slot->lecturehallmaster) &nbsp;·&nbsp;<i class="fas fa-door-open" style="font-size:.65rem;"></i> {{ $slot->lecturehallmaster->title }} @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @endforeach
          @endif
        </div>
      </div>

      {{-- ── ATTENDANCE ── --}}
      <div class="sp-panel" id="tab-attendance">
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

    {{-- ── INTERNAL MARKS (FA) ── --}}
    <div class="sp-panel" id="tab-fa">
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-pen-nib"></i></div>
          <h4>Internal Marks (FA)</h4>
          <span class="sp-count">{{ $internalMarks->count() }} records</span>
        </div>
        @if($internalMarks->isEmpty())
        <div class="sp-empty"><i class="fas fa-file-alt"></i>No internal marks recorded.</div>
        @else
        @php $faGrouped=$internalMarks->groupBy(fn($m)=>$m->academic_year??'Unknown'); @endphp
        @foreach($faGrouped as $year => $marks)
        <div style="display:flex;align-items:center;gap:.5rem;margin:.75rem 0 .5rem;">
          <i class="fas fa-calendar" style="color:#e65100;font-size:.8rem;"></i>
          <span style="font-weight:700;font-size:.82rem;color:#e65100;">Academic Year {{ $year }}</span>
          <span class="sp-count">{{ $marks->count() }}</span>
        </div>
        <table class="sp-table mb-3">
          <thead>
            <tr>
              <th>#</th>
              <th>Semester</th>
              <th>Course Code</th>
              <th>Course Title</th>
              <th>FA Marks</th>
            </tr>
          </thead>
          <tbody>
            @foreach($marks as $i => $mark)
            <tr>
              <td style="color:#adb5bd;">{{ $i+1 }}</td>
              <td>{{ $mark->getRelation('semester') ? $mark->getRelation('semester')->title : 'Sem '.$mark->semester }}</td>
              <td><span style="background:#e8eaf6;color:#3949ab;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:600;">{{ $mark->course->course_code ?? '—' }}</span></td>
              <td>{{ $mark->course->course_title ?? '—' }}</td>
              <td><span style="font-size:1.1rem;font-weight:800;color:#e65100;">{{ $mark->internal_mark }}</span>@if($mark->course?->internal) <span style="font-size:.72rem;color:#adb5bd;">/ {{ $mark->course->internal }}</span>@endif</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endforeach
        @endif
      </div>
    </div>

    {{-- ── EXAM RESULTS ── --}}
    <div class="sp-panel" id="tab-results">
      @if($examResults->isEmpty())
      <div class="sp-card">
        <div class="sp-empty"><i class="fas fa-trophy"></i>No published results found.</div>
      </div>
      @else
      {{-- Semester-wise Summary Strip --}}
      <div class="sp-card" style="margin-bottom:1rem;">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-chart-bar"></i></div>
          <h4>Semester-wise Summary</h4>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem;">
          @foreach($resultsBySemester as $semKey => $semData)
          @php
          $r = $semData['result'];
          $qCount = $semData['qualified']->count();
          $bCount = $semData['backlog']->count();
          $isPass = $r->result_status === 'pass';
          @endphp
          <div style="border:1px solid {{ $isPass?'#c8e6c9':'#ffcdd2' }};border-radius:10px;overflow:hidden;">
            <div style="background:{{ $isPass?'#e8f5e9':'#fce4ec' }};padding:.55rem .85rem;display:flex;justify-content:space-between;align-items:center;">
              <span style="font-weight:700;font-size:.82rem;color:{{ $isPass?'#1b5e20':'#b71c1c' }};">{{ $semKey }}</span>
              <span style="background:{{ $isPass?'#2e7d32':'#c62828' }};color:#fff;border-radius:50px;padding:.1rem .6rem;font-size:.72rem;font-weight:700;">{{ $isPass?'PASS':'FAIL' }}</span>
            </div>
            <div style="padding:.6rem .85rem;display:flex;gap:1rem;font-size:.82rem;">
              <div>SGPA <strong style="color:#1a237e;">{{ $r->sgpa }}</strong></div>
              <div>CGPA <strong style="color:#1a237e;">{{ $r->cgpa }}</strong></div>
              <div>{{ $r->percentage }}%</div>
            </div>
            <div style="padding:.4rem .85rem .7rem;display:flex;gap:.6rem;">
              <span style="background:#e8f5e9;color:#2e7d32;border-radius:50px;padding:.15rem .65rem;font-size:.75rem;font-weight:600;"><i class="fas fa-check" style="font-size:.65rem;"></i> {{ $qCount }} Qualified</span>
              @if($bCount > 0)
              <span style="background:#fce4ec;color:#c62828;border-radius:50px;padding:.15rem .65rem;font-size:.75rem;font-weight:600;"><i class="fas fa-times" style="font-size:.65rem;"></i> {{ $bCount }} Backlog</span>
              @endif
            </div>
            @if($bCount > 0)
            <div style="background:#fff8f8;border-top:1px solid #ffcdd2;padding:.5rem .85rem;">
              <div style="font-size:.72rem;color:#b71c1c;font-weight:600;margin-bottom:.3rem;">Backlog Subjects:</div>
              @foreach($semData['backlog'] as $bs)
              <div style="font-size:.74rem;color:#555;padding:.1rem 0;">
                <span style="background:#e8eaf6;color:#3949ab;border-radius:3px;padding:.05rem .35rem;font-size:.72rem;margin-right:.3rem;">{{ $bs->subject_code }}</span>{{ $bs->subject_name }}
                <span style="color:#c62828;font-weight:600;margin-left:.3rem;">{{ $bs->grade }}</span>
              </div>
              @endforeach
            </div>
            @endif
          </div>
          @endforeach
        </div>
      </div>

      {{-- Detailed Results per Semester --}}
      @foreach($resultsBySemester as $semKey => $semData)
      @php $result = $semData['result']; @endphp
      <div class="sp-result-card">
        <div class="sp-result-header">
          <div>
            <div class="rh-name">{{ $result->examSession->name ?? 'Examination' }}</div>
            <div class="rh-meta">{{ $result->examSession->academic_year ?? '' }}@if($result->examSession?->semester) &nbsp;·&nbsp; Semester {{ $result->examSession->semester }} @endif</div>
          </div>
          <span style="background:{{ $result->result_status==='pass'?'rgba(76,175,80,.25)':'rgba(244,67,54,.25)' }};color:#fff;border-radius:50px;padding:.2rem .9rem;font-size:.8rem;font-weight:700;border:1px solid rgba(255,255,255,.3);">
            {{ strtoupper($result->result_status) }}
          </span>
        </div>
        <div style="display:flex;flex-wrap:wrap;background:#fafbff;border-bottom:1px solid #e8eaf6;">
          <div class="sp-stat-pill col">
            <div class="val">{{ $result->sgpa }}</div>
            <div class="lbl">SGPA</div>
          </div>
          <div class="sp-stat-pill col">
            <div class="val">{{ $result->cgpa }}</div>
            <div class="lbl">CGPA</div>
          </div>
          <div class="sp-stat-pill col">
            <div class="val">{{ $result->percentage }}%</div>
            <div class="lbl">Percentage</div>
          </div>
          <div class="sp-stat-pill col">
            <div class="val" style="color:#2e7d32;">{{ $result->earned_credits }}</div>
            <div class="lbl">Credits Earned</div>
          </div>
        </div>
        <div style="overflow-x:auto;padding:.5rem 0;">
          <table class="sp-table" style="min-width:640px;">
            <thead>
              <tr>
                <th>Code</th>
                <th>Subject</th>
                <th>FA</th>
                <th>SA</th>
                <th>Total</th>
                <th>Max</th>
                <th>Cr.</th>
                <th>Gr. Pt.</th>
                <th>Grade</th>
                <th>Result</th>
              </tr>
            </thead>
            <tbody>
              @foreach($result->resultSubjects as $subj)
              @php $sPass=$subj->result_status==='pass'; @endphp
              <tr>
                <td style="font-size:.78rem;color:#3949ab;font-weight:600;">{{ $subj->subject_code }}</td>
                <td>{{ $subj->subject_name }}</td>
                <td>{{ $subj->fa_marks }}</td>
                <td>{{ $subj->sa_marks }}</td>
                <td><strong>{{ $subj->total_marks }}</strong></td>
                <td style="color:#adb5bd;">{{ $subj->max_marks }}</td>
                <td style="font-weight:600;">{{ $subj->credits }}</td>
                <td>{{ $subj->grade_point }}</td>
                <td><span class="gr-badge">{{ $subj->grade }}</span></td>
                <td><span style="background:{{ $sPass?'#e8f5e9':'#fce4ec' }};color:{{ $sPass?'#2e7d32':'#c62828' }};border-radius:50px;padding:.15rem .65rem;font-size:.74rem;font-weight:600;">{{ ucfirst($subj->result_status) }}</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endforeach
      @endif
    </div>

    {{-- ── COURSES ── --}}
    <div class="sp-panel" id="tab-courses">
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#e8eaf6;color:#1a237e;"><i class="fas fa-book-open"></i></div>
          <h4>Enrolled Courses</h4>
          <span class="sp-count">{{ $studentCourses->count() }}</span>
        </div>
        @if($studentCourses->isEmpty())
        <div class="sp-empty"><i class="fas fa-book"></i>No courses found.</div>
        @else
        @foreach($coursesBySemester as $semTitle => $courses)
        @php
        $activeCount = $courses->where('is_active', 1)->count();
        @endphp
        <div style="display:flex;align-items:center;gap:.5rem;margin:.9rem 0 .5rem;">
          <div style="background:#e8eaf6;color:#1a237e;border-radius:6px;padding:.2rem .65rem;font-size:.78rem;font-weight:700;">
            <i class="fas fa-layer-group" style="font-size:.72rem;"></i> {{ $semTitle }}
          </div>
          <span class="sp-count">{{ $courses->count() }} course{{ $courses->count()!=1?'s':'' }}</span>
          @if($activeCount > 0)
          <span style="background:#e8f5e9;color:#2e7d32;border-radius:50px;padding:.1rem .5rem;font-size:.72rem;font-weight:600;">{{ $activeCount }} Active</span>
          @endif
        </div>
        <table class="sp-table mb-3">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Course Title</th>
              <th>Type</th>
              <th>Credits</th>
              <th>Int / Ext / Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $i => $course)
            @php
            $ct = $course->coursemaster?->coursetypemaster;
            $ctColors = ['CC'=>['#e3f2fd','#1565c0'],'GE'=>['#e8f5e9','#2e7d32'],'SEC'=>['#fff3e0','#e65100'],'DSE'=>['#f3e5f5','#6a1b9a']];
            $ctKey = $ct?->title ?? '';
            $ctBg = $ctColors[$ctKey][0] ?? '#f0f0f0';
            $ctFg = $ctColors[$ctKey][1] ?? '#555';
            @endphp
            <tr>
              <td style="color:#adb5bd;">{{ $i+1 }}</td>
              <td><span style="background:#e8eaf6;color:#3949ab;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:600;">{{ $course->coursemaster?->course_code ?? '—' }}</span></td>
              <td>{{ $course->coursemaster?->course_title ?? '—' }}</td>
              <td>
                @if($ct)
                <span style="background:{{ $ctBg }};color:{{ $ctFg }};border-radius:4px;padding:.1rem .45rem;font-size:.75rem;font-weight:700;" title="{{ $ct->description }}">{{ $ct->title }}</span>
                @else
                <span style="color:#adb5bd;">—</span>
                @endif
              </td>
              <td style="font-weight:600;">{{ $course->coursemaster?->credits ?? '—' }}</td>
              <td style="font-size:.8rem;color:#555;">
                {{ $course->coursemaster?->internal ?? '—' }} /
                {{ $course->coursemaster?->external ?? '—' }} /
                {{ $course->coursemaster?->total ?? '—' }}
              </td>
              <td>@if($course->is_active==1)<span class="pill-active">Active</span>@else<span class="pill-inactive">Inactive</span>@endif</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endforeach
        @endif
      </div>
    </div>

    {{-- ── EXAM REGISTRATION ── --}}
    <div class="sp-panel" id="tab-exam-reg">
      @if($examRegistrations->isEmpty())
      {{-- No registration record: show clearance requirements --}}
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-clipboard-check"></i></div>
          <h4>Exam Registration</h4>
        </div>
        <div class="sp-empty"><i class="fas fa-clock"></i>No exam registration record found. Registration opens when clearance is granted.</div>
        {{-- Clearance checklist (pre-registration state) --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;margin-top:1.2rem;">
          @php
          $attPct = $attendanceSummary->avg('percentage') ?? 0;
          $attOk = $attPct >= 75;
          $feesOk = $data->feepayment->isNotEmpty();
          @endphp
          <div style="border:1px solid {{ $attOk?'#c8e6c9':'#ffcdd2' }};border-radius:10px;padding:.85rem 1rem;background:{{ $attOk?'#f1f8e9':'#fce4ec' }};">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
              <i class="fas fa-{{ $attOk?'check-circle':'times-circle' }}" style="color:{{ $attOk?'#2e7d32':'#c62828' }};font-size:1.2rem;"></i>
              <span style="font-weight:700;font-size:.85rem;">Attendance Clearance</span>
            </div>
            <div style="font-size:.8rem;color:#555;">Overall: <strong style="color:{{ $attOk?'#2e7d32':'#c62828' }};">{{ round($attPct,1) }}%</strong> (min 75% required)</div>
            @if(!$attOk)<div style="font-size:.75rem;color:#c62828;margin-top:.3rem;"><i class="fas fa-exclamation-triangle"></i> Attendance below threshold — registration on hold.</div>@endif
          </div>
          <div style="border:1px solid #ffe0b2;border-radius:10px;padding:.85rem 1rem;background:#fff8e1;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
              <i class="fas fa-info-circle" style="color:#f57f17;font-size:1.2rem;"></i>
              <span style="font-weight:700;font-size:.85rem;">Library Clearance</span>
            </div>
            <div style="font-size:.8rem;color:#555;">Pending verification from library.</div>
          </div>
          <div style="border:1px solid {{ $feesOk?'#c8e6c9':'#ffcdd2' }};border-radius:10px;padding:.85rem 1rem;background:{{ $feesOk?'#f1f8e9':'#fce4ec' }};">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
              <i class="fas fa-{{ $feesOk?'check-circle':'times-circle' }}" style="color:{{ $feesOk?'#2e7d32':'#c62828' }};font-size:1.2rem;"></i>
              <span style="font-weight:700;font-size:.85rem;">Fees Clearance</span>
            </div>
            <div style="font-size:.8rem;color:#555;">
              @if($feesOk) Fee payment records found. @else No fee payment found — clearance pending. @endif
            </div>
          </div>
        </div>
      </div>
      @else
      @foreach($examRegistrations as $reg)
      @php
      $attClear = $reg->attendance_clearance === 'granted';
      $libClear = $reg->library_clearance === 'granted';
      $feeClear = $reg->fees_clearance === 'granted';
      $allClear = $attClear && $libClear && $feeClear;
      $statusColors = ['approved'=>['#e8f5e9','#2e7d32'],'pending'=>['#fff8e1','#f57f17'],'rejected'=>['#fce4ec','#c62828']];
      $sc = $statusColors[$reg->status] ?? ['#f5f5f5','#555'];
      $regSubjectCount = $reg->registrationSubjects->count();
      @endphp
      <div class="sp-card" style="margin-bottom:1.2rem;">
        {{-- Header --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;padding-bottom:.85rem;border-bottom:1px solid #f0f0f0;">
          <div>
            <div style="font-weight:700;font-size:1rem;color:#1a237e;">{{ $reg->examSession?->name ?? 'Exam Session' }}</div>
            <div style="font-size:.8rem;color:#6c757d;margin-top:.15rem;">
              A.Y. {{ $reg->examSession?->academic_year ?? '—' }}
              @if($reg->examSession?->semester) &nbsp;·&nbsp; Semester {{ $reg->examSession->semester }} @endif
              @if($reg->registered_at) &nbsp;·&nbsp; Registered: {{ $reg->registered_at->format('d M Y') }} @endif
            </div>
          </div>
          <div style="display:flex;gap:.5rem;align-items:center;margin-top:.3rem;">
            @if($reg->is_backlog)
            <span style="background:#fff3e0;color:#e65100;border-radius:50px;padding:.2rem .8rem;font-size:.75rem;font-weight:700;"><i class="fas fa-redo" style="font-size:.65rem;"></i> Backlog</span>
            @endif
            <span style="background:{{ $sc[0] }};color:{{ $sc[1] }};border-radius:50px;padding:.2rem .9rem;font-size:.78rem;font-weight:700;">{{ ucfirst($reg->status) }}</span>
          </div>
        </div>

        {{-- Clearance Status --}}
        <div style="margin-bottom:1rem;">
          <div style="font-weight:700;font-size:.82rem;color:#1a1a2e;margin-bottom:.6rem;"><i class="fas fa-shield-alt" style="color:#3949ab;font-size:.78rem;margin-right:.35rem;"></i>Clearance Status</div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;">
            {{-- Attendance --}}
            <div style="border:1.5px solid {{ $attClear?'#a5d6a7':'#ef9a9a' }};border-radius:8px;padding:.6rem .75rem;background:{{ $attClear?'#f1f8e9':'#fce4ec' }};">
              <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.25rem;">
                <i class="fas fa-{{ $attClear?'check-circle':'times-circle' }}" style="color:{{ $attClear?'#2e7d32':'#c62828' }};"></i>
                <span style="font-size:.78rem;font-weight:700;">Attendance</span>
              </div>
              <div style="font-size:.75rem;color:#555;">
                @if($reg->attendance_percentage) {{ $reg->attendance_percentage }}% @endif
                <span style="font-weight:600;color:{{ $attClear?'#2e7d32':'#c62828' }};">{{ $attClear?'Granted':'Not Granted' }}</span>
              </div>
              @if(!$attClear)<div style="font-size:.7rem;color:#b71c1c;margin-top:.2rem;"><i class="fas fa-exclamation-triangle"></i> Registration on hold — attend more classes.</div>@endif
            </div>
            {{-- Library --}}
            <div style="border:1.5px solid {{ $libClear?'#a5d6a7':'#ef9a9a' }};border-radius:8px;padding:.6rem .75rem;background:{{ $libClear?'#f1f8e9':'#fce4ec' }};">
              <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.25rem;">
                <i class="fas fa-{{ $libClear?'check-circle':'times-circle' }}" style="color:{{ $libClear?'#2e7d32':'#c62828' }};"></i>
                <span style="font-size:.78rem;font-weight:700;">Library</span>
              </div>
              <div style="font-size:.75rem;font-weight:600;color:{{ $libClear?'#2e7d32':'#c62828' }};">{{ $libClear?'Granted':'Not Granted' }}</div>
              @if(!$libClear)<div style="font-size:.7rem;color:#b71c1c;margin-top:.2rem;"><i class="fas fa-exclamation-triangle"></i> Clear library dues to proceed.</div>@endif
            </div>
            {{-- Fees --}}
            <div style="border:1.5px solid {{ $feeClear?'#a5d6a7':'#ef9a9a' }};border-radius:8px;padding:.6rem .75rem;background:{{ $feeClear?'#f1f8e9':'#fce4ec' }};">
              <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.25rem;">
                <i class="fas fa-{{ $feeClear?'check-circle':'times-circle' }}" style="color:{{ $feeClear?'#2e7d32':'#c62828' }};"></i>
                <span style="font-size:.78rem;font-weight:700;">Fees</span>
              </div>
              <div style="font-size:.75rem;font-weight:600;color:{{ $feeClear?'#2e7d32':'#c62828' }};">{{ $feeClear?'Granted':'Not Granted' }}</div>
              @if(!$feeClear)<div style="font-size:.7rem;color:#b71c1c;margin-top:.2rem;"><i class="fas fa-exclamation-triangle"></i> Clear outstanding fees to proceed.</div>@endif
            </div>
          </div>
          @if($reg->clearance_remarks)
          <div style="margin-top:.6rem;background:#fff8e1;border-left:3px solid #f57f17;padding:.5rem .85rem;border-radius:0 6px 6px 0;font-size:.8rem;color:#5d4037;">
            <i class="fas fa-comment-alt" style="color:#f57f17;margin-right:.35rem;"></i><strong>Remarks:</strong> {{ $reg->clearance_remarks }}
          </div>
          @endif
          @if(!$allClear)
          <div class="alert alert-warning d-flex align-items-center gap-2 mt-2 mb-0" style="border-radius:8px;font-size:.82rem;">
            <i class="fas fa-lock"></i>
            <span>Exam registration is <strong>on hold</strong> until all clearances are granted:
              @if(!$attClear) <span class="badge bg-danger">Attendance</span> @endif
              @if(!$libClear) <span class="badge bg-danger">Library</span> @endif
              @if(!$feeClear) <span class="badge bg-danger">Fees</span> @endif
            </span>
          </div>
          @else
          <div class="alert alert-success d-flex align-items-center gap-2 mt-2 mb-0" style="border-radius:8px;font-size:.82rem;">
            <i class="fas fa-check-circle"></i>
            <span>All clearances granted. Registration is <strong>active</strong>.</span>
          </div>
          @endif
        </div>

        {{-- Registered Subjects --}}
        @if($regSubjectCount > 0)
        <div style="font-weight:700;font-size:.82rem;color:#1a1a2e;margin-bottom:.5rem;"><i class="fas fa-list" style="color:#3949ab;font-size:.78rem;margin-right:.35rem;"></i>Registered Subjects ({{ $regSubjectCount }})</div>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
          @foreach($reg->registrationSubjects as $rs)
          <span style="background:{{ $rs->is_backlog?'#fce4ec':'#e8eaf6' }};color:{{ $rs->is_backlog?'#c62828':'#3949ab' }};border-radius:4px;padding:.2rem .55rem;font-size:.76rem;font-weight:600;">
            {{ $rs->examSubject?->master?->subject_code ?? ('Sub #'.$rs->exam_subject_id) }}
            @if($rs->is_backlog) <span style="font-size:.65rem;">(Backlog)</span> @endif
          </span>
          @endforeach
        </div>
        @endif
      </div>
      @endforeach
      @endif
    </div>

    {{-- ── FEE ── --}}
    <div class="sp-panel" id="tab-fee">
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

</div>{{-- /.sp-content --}}

<script>
  function spTab(e, panelId) {
    document.querySelectorAll('.sp-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sp-panel').forEach(p => p.classList.remove('active'));
    e.currentTarget.classList.add('active');
    document.getElementById(panelId).classList.add('active');
    window.scrollTo({
      top: document.querySelector('.sp-tabs-wrap').offsetTop - 10,
      behavior: 'smooth'
    });
  }

  // Support anchor-based tab switching from dashboard quick links
  window.addEventListener('load', function() {
    const hash = window.location.hash;
    const map = {
      '#timetable': 'tab-timetable',
      '#attendance': 'tab-attendance',
      '#marks': 'tab-fa',
      '#courses': 'tab-courses',
      '#results': 'tab-results',
      '#exam-reg': 'tab-exam-reg',
      '#fee': 'tab-fee'
    };
    if (map[hash]) {
      const btn = document.querySelector(`[onclick*="${map[hash]}"]`);
      if (btn) btn.click();
    }
  });
</script>

</main>
</div>

@include('student.footer')
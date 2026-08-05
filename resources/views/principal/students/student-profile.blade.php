<?php

use App\Http\Controllers\StaticController;
use App\Models\StudentSpecialization;
use Illuminate\Support\Facades\Auth;

$userId = Auth::user()->id;
$userRole = StaticController::fetchUserRole($userId);
?>
@include('includes.header')
@if(in_array($userRole, ['super-admin', 'admin', 'central-office','itcell']))
@include('admin.sidebar')
@elseif($userRole == 'coe')
@include('coe.sidebar')
@elseif($userRole == 'faculty')
@include('faculty.sidebar')
@elseif($userRole == 'principal')
@include('principal.sidebar')
@else
<!-- // No sidebar for other roles -->
@endif

<style>
  .sp-hero {
    background: linear-gradient(135deg, #653dca 0%, #aee8f7 100%);
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

  /* TAB WRAPPER */
  .sp-tabs-wrap {
    background: #ffffff;
    padding: 12px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  }

  /* TAB CONTAINER */
  .sp-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  /* TAB BUTTON */
  .sp-tab {
    border: none;
    background: #f3f4f6;
    color: #4b5563;
    padding: 10px 18px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
  }

  /* ICON */
  .sp-tab i {
    font-size: 15px;
  }

  /* HOVER */
  .sp-tab:hover {
    background: #e0e7ff;
    color: #3730a3;
    transform: translateY(-2px);
  }

  /* ACTIVE TAB */
  .sp-tab.active {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    box-shadow: 0 5px 12px rgba(79, 70, 229, 0.35);
  }

  /* ACTIVE HOVER */
  .sp-tab.active:hover {
    color: white;
    background: linear-gradient(135deg, #4338ca, #6d28d9);
  }

  /* MOBILE */
  @media(max-width:768px) {

    .sp-tabs {
      gap: 8px;
    }

    .sp-tab {
      padding: 9px 14px;
      font-size: 13px;
    }

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

  .sp-faculty-block {
    border: 1px solid #eceff9;
    border-radius: 10px;
    padding: .75rem;
    margin-top: 1rem;
    background: #fcfcff
  }

  .sp-faculty-title {
    font-size: .86rem;
    font-weight: 700;
    color: #1a237e;
    margin-bottom: .6rem
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
        @if(!empty($programOfferingSubjectTitle))
        Offered By: {{ $programOfferingSubjectTitle }}
        @endif

        @if($data->stdprogramenrolled != null)
        &nbsp;·&nbsp;
        {{$data->stdprogramenrolled->code }} - {{ $data->stdprogramenrolled->name }}
        @endif
        @if($data->batchmaster)
        &nbsp;·&nbsp; Batch {{ $data->batchmaster->batch_name }}
        @endif

        @php
        $specializationInfo = StudentSpecialization::with('studentspecialization')->where('student_id', $data->id)->first();
        @endphp
        @if ($specializationInfo != null)
        &nbsp;·&nbsp; Specialization - {{ $specializationInfo->studentspecialization != null ? $specializationInfo->studentspecialization->name: ''}}
        @endif
      </p>
      <div>
        @if($data->mail_id)
        <span class="sp-badge"><i class="fas fa-envelope"></i> {{ $data->mail_id }}</span>
        @endif
        @if($data->mobile_no)
        <span class="sp-badge"><i class="fas fa-phone"></i> {{ $data->mobile_no }}</span>
        @endif
        @if($data->bloodgroup)
        <span class="sp-badge"><i class="fas fa-tint"></i> {{ $data->bloodgroup->title ?? '' }}</span>
        @endif
        @if($data->stdprogramenrolled->program_type == 2)
        @if(!empty($studentMajorDeliveryType))
        <span class="sp-badge"><i class="fas fa-route"></i> Delivery {{ $studentMajorDeliveryType }}</span>

        @endif
        @if(!empty($combo1Title) || !empty($combo2Title))
        <span class="sp-badge"><i class="fas fa-code-branch"></i> Combo A: {{ $combo1Title ?: '—' }} | Combo B: {{ $combo2Title ?: '—' }}</span>
        @endif
        @endif
        <span class="sp-badge"><i class="fas fa-layer-group"></i> Year {{ $data->current_year ?? '—' }}</span>
      </div>
    </div>
    <div>
      @if(in_array($userRole, ['super-admin','itcell']))
      <form method="POST" action="{{ route('admin.student.create-access', $data->id) }}" class="d-inline ms-1"
        onsubmit="return confirm('Create/reset student login? Default password will be their roll number.')">
        @csrf
        <button type="submit" class="btn-sm btn-success">
          <i class="fas fa-key me-1"></i> Create Login
        </button>
      </form>
      @endif
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

{{-- flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
  <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
  <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger mx-3 mt-3">
  <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- TABS --}}
<div class="sp-tabs-wrap mt-3 container">
  <div class="sp-tabs">
    <button class="sp-tab active" onclick="spTab(event,'tab-about')"><i class="fas fa-user"></i> About</button>
    <button class="sp-tab" onclick="spTab(event,'tab-timetable')"><i class="fas fa-calendar-alt"></i> Timetable</button>
    <button class="sp-tab" onclick="spTab(event,'tab-attendance')"><i class="fas fa-check-circle"></i> Attendance</button>
    <button class="sp-tab" onclick="spTab(event,'tab-fa')"><i class="fas fa-pen"></i> FA Marks</button>
    <!-- <button class="sp-tab" onclick="spTab(event,'tab-results')"><i class="fas fa-trophy"></i> Exam Results</button> -->
    <button class="sp-tab" id="btn-tab-courses" onclick="spTab(event,'tab-courses')"><i class="fas fa-book"></i> Courses</button>
    <button class="sp-tab" onclick="spTab(event,'tab-fee')"><i class="fas fa-rupee-sign"></i> Fee</button>
    @if(!empty($student360Profile))
    <button class="sp-tab" id="btn-tab-360" onclick="spTab(event,'tab-360')"><i class="fas fa-chart-line"></i> Student 360</button>
    @endif
    @if(in_array($userRole, ['super-admin','itcell']))
    <button class="sp-tab" id="btn-tab-edit" onclick="spTab(event,'tab-edit')"><i class="fas fa-edit"></i> Edit Details</button>
    @endif
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
        @php
        $dayOrder = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7];
        $allSlots = collect($timetableByDay ?? collect())->flatten(1)->values();
        $orderedDays = collect($timetableByDay ?? collect())->keys()->sortBy(fn($d) => $dayOrder[$d] ?? 99)->values();
        $hours = $allSlots->pluck('hour')->filter()->unique()->sortBy(function($hour) {
        $hourText = (string) $hour;
        if (preg_match('/\d+/', $hourText, $m)) {
        return (int) $m[0];
        }
        return 999;
        })->values();

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
                <div class="sp-cell-empty">—</div>
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
                    Group {{ !is_null($slot['group'] ?? null) ? $slot['group'] : '—' }}
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

  {{-- ── FA Marks ── --}}
  <div class="sp-panel" id="tab-fa">

    {{-- Course type badge colours --}}
    @php
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

    {{-- header row: title + Enroll button --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
      <div style="font-size:1rem;font-weight:700;color:#1a1a2e;">
        <i class="fas fa-book-open me-1" style="color:#1a237e;"></i>
        FA Marks

      </div>

    </div>

    @if($coursesBySemester->isEmpty())
    <div class="sp-card">
      <div class="sp-empty"><i class="fas fa-book"></i>No courses enrolled yet.</div>
    </div>
    @else

    @foreach($coursesBySemester as $semLabel => $courses)
    <div class="sp-card" style="margin-bottom:1rem;">
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;padding-bottom:.6rem;border-bottom:1px solid #f0f0f0;">
        <span style="background:#e8eaf6;color:#1a237e;border-radius:6px;padding:.25rem .8rem;font-weight:700;font-size:.82rem;">
          <i class="fas fa-layer-group me-1"></i>{{ $semLabel }}
        </span>
        <span class="sp-count">{{ $courses->count() }} courses</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="sp-table" style="min-width:600px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Course Title</th>
              <th>Type</th>
              <th>Delivery</th>
              <th>Offered By</th>
              <th>Cr.</th>
              <th>Cia Marks </th>
              <th style="text-align:center;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $i => $course)
            @php
            $locked = in_array($course->course_id, $lockedCourseIds);
            $typeTitle = $course->coursemaster?->coursetypemaster?->title ?? '';
            $ctKey = preg_replace('/\s.*/', '', $typeTitle);
            $ct = $ctColors[$ctKey] ?? $defaultCt;
            $deliveryKey = (string) ($course->semester ?? $course->coursemaster?->semester_id ?? '') . '_' . (string) ($course->course_id ?? '');
            $deliveryType = $courseDeliveryMap[$deliveryKey] ?? ($studentMajorDeliveryType ?? 'COMMON');
            $offeredBySubject = $courseOfferingSubjectMap[$deliveryKey] ?? ($programOfferingSubjectTitle ?? '—');
            @endphp
            <?php
            $courseMarks = StaticController::getStudentCourseMarks($data->id, $course->course_id);
            // $totalFaMark = StaticController::getStudentCourseMarkTotal($data->id, $course->course_id);

            ?>
            <tr>
              <td style="color:#adb5bd;">{{ $i+1 }}</td>
              <td>
                <span style="background:#e8eaf6;color:#3949ab;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:600;">
                  {{ $course->coursemaster?->course_code ?? '—' }}
                </span>
              </td>
              <td style="font-weight:500;">{{ $course->coursemaster?->course_title ?? '—' }}</td>
              <td>
                @if($typeTitle)
                <span style="background:{{ $ct['bg'] }};color:{{ $ct['color'] }};border-radius:4px;padding:.1rem .5rem;font-size:.76rem;font-weight:700;white-space:nowrap;">
                  {{ $typeTitle }}
                </span>
                @else —
                @endif
              </td>
              <td>
                <span style="background:#e3f2fd;color:#1565c0;border-radius:4px;padding:.1rem .5rem;font-size:.74rem;font-weight:700;white-space:nowrap;">
                  {{ $deliveryType }}
                </span>
              </td>
              <td style="font-size:.78rem;color:#374151;font-weight:600;white-space:nowrap;">{{ $offeredBySubject }}</td>
              <td>{{ $course->coursemaster?->credits ?? '—' }}</td>
              <td style="font-size:.8rem;color:#6c757d;">
                @php
                $marksarray = [];
                $totalFaMark = 0;
                @endphp
                @foreach ($courseMarks as $cm)
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.2rem;">
                  <span style="font-weight:600;">{{ $cm->groupinfo?->grouptype?->name ?? '—' }}</span>
                  {{ $cm->COURSE_GROUP_MARK }} / {{ $cm->groupinfo?->MAX_MARK ?? '—' }}
                </div>
                @php
                $groupTypeId = $cm->groupinfo?->grouptype?->id;
                $rawCourseMark = trim((string) ($cm->COURSE_GROUP_MARK ?? ''));
                $courseMark = is_numeric($rawCourseMark) ? (float) $rawCourseMark : 0;
                if ((int) $groupTypeId === 5) {
                $marksarray[] = $courseMark;
                } else {
                $marksarray[] = $courseMark / 2;
                }
                $totalFaMark = array_sum($marksarray);
                @endphp

                @endforeach

              </td>

              <td style="text-align:center;white-space:nowrap;">

                <span style="color:black;font-size:1rem;" class="badge badge-secondary">

                  {{ round( $totalFaMark) }}
                </span>


              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endforeach

    <p style="font-size:.75rem;color:#adb5bd;margin-top:.5rem;">
      <i class="fas fa-lock me-1"></i> Locked = FA or SA marks recorded. Edit/delete not allowed.
    </p>
    @endif
  </div>

  {{-- ── EXAM RESULTS ── --}}
  <div class="sp-panel" id="tab-results">
    @if($examResults->isEmpty())
    <div class="sp-card">
      <div class="sp-empty"><i class="fas fa-trophy"></i>No published results found.</div>
    </div>
    @else
    @foreach($examResults as $result)
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
    <div id="courseAjaxAlert"></div>
    <div id="courseListContainer">
      @include('admin.master.partials.student-courses-list', [
      'data' => $data,
      'studentCourses' => $studentCourses,
      'coursesBySemester' => $coursesBySemester,
      'lockedCourseIds' => $lockedCourseIds,
      'courseDeliveryMap' => $courseDeliveryMap ?? [],
      'courseOfferingSubjectMap' => $courseOfferingSubjectMap ?? [],
      'studentMajorDeliveryType' => $studentMajorDeliveryType ?? null,
      'programOfferingSubjectTitle' => $programOfferingSubjectTitle ?? '',
      ])
    </div>
  </div>

  {{-- Enroll Course Modal --}}
  <div class="modal fade" id="enrollCourseModal" tabindex="-1" aria-labelledby="enrollCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background:#1a237e;">
          <h5 class="modal-title text-white" id="enrollCourseModalLabel">
            <i class="fas fa-plus-circle me-2"></i>New Course Enrollment - {{ $data->first_name }} {{ $data->last_name }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="{{ route('admin.student.courses.store', $data->id) }}" id="enrollForm">
          @csrf
          <div class="modal-body" style="padding:1.25rem;">
            <div class="alert alert-info">
              <p>Selection of Semester connects the student to that particular semesters. This can also be considered as Semester Changing</p>
            </div>
            <div class="row g-3 mb-3 align-items-end">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Roll No</label>
                <input type="text" class="form-control text-uppercase" value=" {{ $data->roll_no }} " readonly>
              </div>
              <div class=" col-md-6">
                <label class="form-label fw-semibold">Select Semester</label>
                <select class="form-select" name="semester_id" id="enrollSemFilter">
                  <option value="">All Semesters</option>
                  @foreach($availableSemesters as $sem)
                  <option value="{{ $sem->id }}">{{ $sem->title }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-12">
                <select name="course_ids[]" class="form-select select-multiple" id="enrollCourseSelect" size="12" multiple>
                  @include('admin.master.partials.student-course-options', ['availableCourses' => $availableCourses])
                </select>
              </div>
              <input type="hidden" name="batch" value="{{$data->batch}}">

            </div>



          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm" id="enrollSubmitBtn">
              <i class="fas fa-save me-1"></i> Enroll
            </button>
          </div>
        </form>
      </div>
    </div>
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

  @if(!empty($student360Profile))
  <div class="sp-panel" id="tab-360">
    <div class="sp-card">
      <div class="sp-card-header">
        <div class="sp-icon" style="background:#e8eaf6;color:#1a237e;"><i class="fas fa-chart-line"></i></div>
        <h4>Student 360 Snapshot</h4>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ $student360Profile['attendance_pct'] ?? 0 }}%</div>
            <div style="font-size:.72rem;color:#6c757d;">Attendance</div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ $student360Profile['mentorship_attendance_pct'] ?? 0 }}%</div>
            <div style="font-size:.72rem;color:#6c757d;">Mentorship Att.</div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ ($student360Profile['mentoring_notes'] ?? collect())->count() }}</div>
            <div style="font-size:.72rem;color:#6c757d;">Mentoring Notes</div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ ($student360Profile['discipline_cases'] ?? collect())->count() }}</div>
            <div style="font-size:.72rem;color:#6c757d;">Discipline Cases</div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ ($student360Profile['counselling_cases'] ?? collect())->count() }}</div>
            <div style="font-size:.72rem;color:#6c757d;">Counselling Cases</div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div style="border-radius:8px;padding:.85rem;text-align:center;background:#f8f9ff;border:1px solid #e8eaf6;">
            <div style="font-size:1.2rem;font-weight:800;color:#1a237e;">{{ $student360Profile['assignment_submission_count'] ?? 0 }}</div>
            <div style="font-size:.72rem;color:#6c757d;">Assignments</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="sp-grid-wrap">
            <table class="sp-table">
              <thead>
                <tr>
                  <th colspan="4">Club Memberships</th>
                </tr>
                <tr>
                  <th>Club</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($student360Profile['club_memberships'] ?? collect()) as $clubMember)
                <tr>
                  <td>{{ $clubMember->club->name ?? '-' }}</td>
                  <td>{{ $clubMember->role_title ?? '-' }}</td>
                  <td>{{ $clubMember->status ?? '-' }}</td>
                  <td>{{ optional($clubMember->joined_on)->format('d-M-Y') ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">No club memberships</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="sp-grid-wrap">
            <table class="sp-table">
              <thead>
                <tr>
                  <th colspan="4">Council Roles</th>
                </tr>
                <tr>
                  <th>Council</th>
                  <th>Role</th>
                  <th>Executive</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($student360Profile['council_roles'] ?? collect()) as $role)
                <tr>
                  <td>{{ $role->council->title ?? '-' }}</td>
                  <td>{{ $role->role_title ?? '-' }}</td>
                  <td>{{ $role->is_executive ? 'Yes' : 'No' }}</td>
                  <td>{{ $role->status ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">No council roles</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="sp-grid-wrap mt-3">
        <table class="sp-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Category</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($student360Profile['mentoring_notes'] ?? collect()) as $note)
            <tr>
              <td>{{ optional($note->created_at)->format('d-M-Y') }}</td>
              <td>{{ $note->category ?? '-' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($note->note, 120) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No mentoring notes</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif

  {{-- ── EDIT DETAILS ── --}}
  <div class="sp-panel" id="tab-edit">
    <form method="POST" action="{{ route('admin.student.update', $data->id) }}">
      @csrf
      @method('PUT')

      {{-- Personal Information --}}
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#e8eaf6;color:#1a237e;"><i class="fas fa-id-card"></i></div>
          <h4>Personal Information</h4>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control form-control-sm @error('first_name') is-invalid @enderror"
              value="{{ old('first_name', $data->first_name) }}" required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Last Name</label>
            <input type="text" name="last_name" class="form-control form-control-sm @error('last_name') is-invalid @enderror"
              value="{{ old('last_name', $data->last_name) }}">
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Gender <span class="text-danger">*</span></label>
            <select name="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror" required>
              <option value="1" {{ old('gender', $data->gender)==1?'selected':'' }}>Male</option>
              <option value="2" {{ old('gender', $data->gender)==2?'selected':'' }}>Female</option>
            </select>
            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Date of Birth</label>
            <input type="date" name="dob" class="form-control form-control-sm @error('dob') is-invalid @enderror"
              value="{{ old('dob', $data->dob ? date('Y-m-d', strtotime($data->dob)) : '') }}">
            @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Mobile No.</label>
            <input type="text" name="mobile_no" class="form-control form-control-sm @error('mobile_no') is-invalid @enderror"
              value="{{ old('mobile_no', $data->mobile_no) }}" maxlength="15">
            @error('mobile_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Email</label>
            <input type="email" name="mail_id" class="form-control form-control-sm @error('mail_id') is-invalid @enderror"
              value="{{ old('mail_id', $data->mail_id) }}">
            @error('mail_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Address</label>
            <textarea name="address" class="form-control form-control-sm @error('address') is-invalid @enderror" rows="2" maxlength="500">{{ old('address', $data->address) }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      {{-- Family Information --}}
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#fce4ec;color:#c62828;"><i class="fas fa-users"></i></div>
          <h4>Family Information</h4>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Father's Name</label>
            <input type="text" name="father_name" class="form-control form-control-sm" value="{{ old('father_name', $data->father_name) }}" maxlength="150">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Father's Occupation</label>
            <input type="text" name="fr_occupation" class="form-control form-control-sm" value="{{ old('fr_occupation', $data->fr_occupation) }}" maxlength="150">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Father's Mobile</label>
            <input type="text" name="fr_mobile_no" class="form-control form-control-sm" value="{{ old('fr_mobile_no', $data->fr_mobile_no) }}" maxlength="15">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Mother's Name</label>
            <input type="text" name="mother_name" class="form-control form-control-sm" value="{{ old('mother_name', $data->mother_name) }}" maxlength="150">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Mother's Occupation</label>
            <input type="text" name="mr_occupation" class="form-control form-control-sm" value="{{ old('mr_occupation', $data->mr_occupation) }}" maxlength="150">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Mother's Mobile</label>
            <input type="text" name="mr_mobile_no" class="form-control form-control-sm" value="{{ old('mr_mobile_no', $data->mr_mobile_no) }}" maxlength="15">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Guardian's Name</label>
            <input type="text" name="guardian_name" class="form-control form-control-sm" value="{{ old('guardian_name', $data->guardian_name) }}" maxlength="150">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Guardian's Mobile</label>
            <input type="text" name="guardian_mobile_no" class="form-control form-control-sm" value="{{ old('guardian_mobile_no', $data->guardian_mobile_no) }}" maxlength="15">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Annual Income (₹)</label>
            <input type="number" name="annual_income" class="form-control form-control-sm" value="{{ old('annual_income', $data->annual_income) }}" min="0">
          </div>
        </div>
      </div>

      {{-- Academic Information --}}
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-graduation-cap"></i></div>
          <h4>Academic Information</h4>
        </div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Roll No.</label>
            <input type="text" name="roll_no" class="form-control form-control-sm @error('roll_no') is-invalid @enderror"
              value="{{ old('roll_no', $data->roll_no) }}" maxlength="50">
            @error('roll_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Register No.</label>
            <input type="text" name="register_no" class="form-control form-control-sm" value="{{ old('register_no', $data->register_no) }}" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">University Reg. No.</label>
            <input type="text" name="university_register_no" class="form-control form-control-sm" value="{{ old('university_register_no', $data->university_register_no) }}" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Current Year</label>
            <select name="current_year" class="form-select form-select-sm">
              <option value="">— Select —</option>
              @for($y=1;$y<=6;$y++)
                <option value="{{ $y }}" {{ old('current_year', $data->current_year)==$y?'selected':'' }}>Year {{ $y }}</option>
                @endfor
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Batch</label>
            <select name="batch" class="form-select form-select-sm @error('batch') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($batches as $b)
              <option value="{{ $b->id }}" {{ old('batch', $data->batch)==$b->id?'selected':'' }}>{{ $b->batch_name }}</option>
              @endforeach
            </select>
            @error('batch')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Department</label>
            <select name="department" class="form-select form-select-sm @error('department') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($departments as $d)
              <option value="{{ $d->id }}" {{ old('department', $data->department)==$d->id?'selected':'' }}>{{ $d->name }}</option>
              @endforeach
            </select>
            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Campus</label>
            <select name="campus_id" class="form-select form-select-sm @error('campus_id') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($campuses as $c)
              <option value="{{ $c->id }}" {{ old('campus_id', $data->campus_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>
              @endforeach
            </select>
            @error('campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Admission Date</label>
            <input type="date" name="admission_date" class="form-control form-control-sm"
              value="{{ old('admission_date', $data->admission_date ? date('Y-m-d', strtotime($data->admission_date)) : '') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Graduation Year</label>
            <input type="number" name="graduation_year" class="form-control form-control-sm" value="{{ old('graduation_year', $data->graduation_year) }}" min="2000" max="2100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Status</label>
            <select name="status" class="form-select form-select-sm">
              <option value="">— Select —</option>
              @foreach(['active','inactive','alumni','dropout','on leave'] as $st)
              <option value="{{ $st }}" {{ old('status', $data->status)==$st?'selected':'' }}>{{ ucfirst($st) }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Identity & Other --}}
      <div class="sp-card">
        <div class="sp-card-header">
          <div class="sp-icon" style="background:#fff3e0;color:#e65100;"><i class="fas fa-fingerprint"></i></div>
          <h4>Identity & Other Details</h4>
        </div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Nationality</label>
            <select name="nationality" class="form-select form-select-sm @error('nationality') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($nationalities as $n)
              <option value="{{ $n->id }}" {{ old('nationality', $data->nationality)==$n->id?'selected':'' }}>{{ ucfirst($n->name) }}</option>
              @endforeach
            </select>
            @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Religion</label>
            <select name="religion" class="form-select form-select-sm @error('religion') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($religions as $r)
              <option value="{{ $r->id }}" {{ old('religion', $data->religion)==$r->id?'selected':'' }}>{{ ucfirst($r->name) }}</option>
              @endforeach
            </select>
            @error('religion')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Blood Group</label>
            <select name="blood_group_id" class="form-select form-select-sm @error('blood_group_id') is-invalid @enderror">
              <option value="">— Select —</option>
              @foreach($bloodGroups as $bg)
              <option value="{{ $bg->id }}" {{ old('blood_group_id', $data->blood_group_id)==$bg->id?'selected':'' }}>{{ strtoupper($bg->name) }}</option>
              @endforeach
            </select>
            @error('blood_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Mother Tongue</label>
            <input type="text" name="mother_tongue" class="form-control form-control-sm" value="{{ old('mother_tongue', $data->mother_tongue) }}" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Community</label>
            <input type="text" name="community" class="form-control form-control-sm" value="{{ old('community', $data->community) }}" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Caste</label>
            <input type="text" name="caste" class="form-control form-control-sm" value="{{ old('caste', $data->caste) }}" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Aadhar No.</label>
            <input type="text" name="aadhar_no" class="form-control form-control-sm" value="{{ old('aadhar_no', $data->aadhar_no) }}" maxlength="20">
          </div>
          <div class="col-md-3 d-flex align-items-end pb-1">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_roman_catholic" id="isRomanCatholic" value="1"
                {{ old('is_roman_catholic', $data->is_roman_catholic) ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="isRomanCatholic" style="font-size:.82rem;">Roman Catholic</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold" style="font-size:.82rem;">Remarks</label>
            <textarea name="remarks" class="form-control form-control-sm" rows="2" maxlength="500">{{ old('remarks', $data->remarks) }}</textarea>
          </div>
        </div>
      </div>

      <div class="text-end mb-4">
        <button type="reset" class="btn btn-sm btn-outline-secondary me-2">
          <i class="fas fa-undo me-1"></i> Reset
        </button>
        <button type="submit" class="btn btn-sm btn-primary">
          <i class="fas fa-save me-1"></i> Save Changes
        </button>
      </div>

    </form>
  </div>{{-- /#tab-edit --}}

</div>{{-- /.sp-content --}}

<script>
  function spTab(e, panelId) {
    var tabs = document.querySelectorAll('.sp-tab');
    var panels = document.querySelectorAll('.sp-panel');
    for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
    for (var i = 0; i < panels.length; i++) panels[i].classList.remove('active');
    if (e && e.currentTarget) e.currentTarget.classList.add('active');
    var panel = document.getElementById(panelId);
    if (panel) panel.classList.add('active');
    window.scrollTo({
      top: document.querySelector('.sp-tabs-wrap').offsetTop - 10,
      behavior: 'smooth'
    });
  }

  function openTab(panelId) {
    var btn = document.getElementById('btn-tab-' + panelId.replace('tab-', ''));
    var tabs = document.querySelectorAll('.sp-tab');
    var panels = document.querySelectorAll('.sp-panel');
    for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
    for (var i = 0; i < panels.length; i++) panels[i].classList.remove('active');
    if (btn) btn.classList.add('active');
    var panel = document.getElementById(panelId);
    if (panel) panel.classList.add('active');
    window.scrollTo({
      top: document.querySelector('.sp-tabs-wrap').offsetTop - 10,
      behavior: 'smooth'
    });
  }

  function filterEnrollOptions() {
    var semFilter = document.getElementById('enrollSemFilter');
    var select = document.getElementById('enrollCourseSelect');
    if (!semFilter || !select) return;

    var semVal = semFilter.value;
    var groups = select.querySelectorAll('optgroup');
    for (var i = 0; i < groups.length; i++) {
      var group = groups[i];
      var match = !semVal || group.getAttribute('data-sem') === semVal;
      var opts = group.querySelectorAll('option');
      var hasVisible = false;

      for (var j = 0; j < opts.length; j++) {
        opts[j].hidden = !match;
        if (!match) opts[j].selected = false;
        if (match) hasVisible = true;
      }

      group.hidden = !hasVisible;
    }
  }

  function showCourseAjaxAlert(type, message) {
    var alertBox = document.getElementById('courseAjaxAlert');
    if (!alertBox) return;

    var alertClass = type === 'error' ? 'danger' : 'success';
    var iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
    alertBox.innerHTML = '<div class="alert alert-' + alertClass + ' alert-dismissible fade show" role="alert">' +
      '<i class="fas ' + iconClass + ' me-1"></i> ' + message +
      '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
      '</div>';
  }

  async function submitCourseAjaxForm(form, confirmMessage) {
    if (!form) return;
    if (confirmMessage && !window.confirm(confirmMessage)) return;

    var listContainer = document.getElementById('courseListContainer');
    var enrollBtn = document.getElementById('enrollSubmitBtn');
    var isEnroll = form.id === 'enrollForm';
    if (isEnroll && enrollBtn) {
      enrollBtn.disabled = true;
      enrollBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    }

    try {
      var response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new FormData(form)
      });

      var payload = await response.json();

      if (!response.ok || !payload.success) {
        var errorMessage = payload.message || 'Unable to complete course action.';
        if (payload.errors) {
          var firstErrorKey = Object.keys(payload.errors)[0];
          if (firstErrorKey && payload.errors[firstErrorKey] && payload.errors[firstErrorKey][0]) {
            errorMessage = payload.errors[firstErrorKey][0];
          }
        }
        showCourseAjaxAlert('error', errorMessage);
        return;
      }

      if (listContainer && payload.course_list_html) {
        listContainer.innerHTML = payload.course_list_html;
      }

      var courseSelect = document.getElementById('enrollCourseSelect');
      if (courseSelect && payload.available_courses_html) {
        courseSelect.innerHTML = payload.available_courses_html;
      }

      if (isEnroll) {
        form.reset();
        filterEnrollOptions();
        var modalEl = document.getElementById('enrollCourseModal');
        var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modal) modal.hide();
      }

      showCourseAjaxAlert('success', payload.message || 'Course action completed successfully.');
      openTab('tab-courses');
    } catch (error) {
      showCourseAjaxAlert('error', 'Network error. Please try again.');
    } finally {
      if (isEnroll && enrollBtn) {
        enrollBtn.disabled = false;
        enrollBtn.innerHTML = '<i class="fas fa-save me-1"></i> Enroll';
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    var semFilter = document.getElementById('enrollSemFilter');
    var enrollForm = document.getElementById('enrollForm');
    var listContainer = document.getElementById('courseListContainer');

    if (semFilter) {
      semFilter.addEventListener('change', filterEnrollOptions);
      filterEnrollOptions();
    }

    if (enrollForm) {
      enrollForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitCourseAjaxForm(enrollForm);
      });
    }

    if (listContainer) {
      listContainer.addEventListener('submit', function(e) {
        var form = e.target.closest('form[data-ajax-course-action]');
        if (!form) return;

        e.preventDefault();
        var confirmMessage = form.getAttribute('data-confirm') || '';
        submitCourseAjaxForm(form, confirmMessage);
      });
    }
  });
</script>
@if($errors-> any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    openTab('tab-edit');
  });
</script>
@endif


@include('includes.footer')
@include('includes.header')

<style>
  :root {
    --corp-ink: #1f2a44;
    --corp-muted: #5f6b80;
    --corp-border: #d9dfeb;
    --corp-surface: #f6f8fc;
    --corp-primary: #1f4e8c;
    --corp-primary-soft: #e8f0fb;
  }

  .corp-page {
    background: linear-gradient(180deg, #f4f7fc 0%, #f8fafd 100%);
    padding-bottom: 24px;
    min-height: calc(100vh - 120px);
  }

  .corp-banner {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    background: linear-gradient(130deg, #ffffff 0%, #f4f8ff 100%);
    box-shadow: 0 8px 24px rgba(28, 46, 87, 0.08);
  }

  .corp-chip {
    background: var(--corp-primary-soft);
    color: var(--corp-primary);
    border: 1px solid #c8d9f2;
    border-radius: 999px;
    font-weight: 600;
    padding: 0.38rem 0.66rem;
  }

  .corp-card {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(23, 36, 66, 0.06);
  }

  .corp-table {
    margin-bottom: 0;
  }

  .corp-table thead th {
    vertical-align: middle;
    border-color: #2d446f;
    background: linear-gradient(145deg, #243f72 0%, #315da1 100%);
    color: #fff;
    font-size: 0.78rem;
    letter-spacing: 0.35px;
    text-transform: uppercase;
  }

  .corp-day-cell {
    background: #f2f6fd;
    color: var(--corp-ink);
    min-width: 120px;
    font-weight: 700;
  }

  .slot-card {
    border: 1px solid var(--corp-border);
    border-radius: 10px;
    background: #fff;
    padding: 8px;
    min-height: 106px;
    text-align: left;
  }

  .slot-empty {
    border: 1px dashed #c6d2e7;
    border-radius: 10px;
    background: #fbfdff;
    color: #8a97af;
    min-height: 106px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
  }

  .slot-course-code {
    color: #1f4e8c;
    font-weight: 700;
  }

  .slot-title {
    color: #22314f;
    font-size: 0.84rem;
    line-height: 1.3;
  }

  .slot-room {
    color: #68758d;
    font-size: 0.78rem;
  }

  .slot-badges .badge {
    font-size: 0.7rem;
    font-weight: 600;
  }

  .hour-range {
    font-size: 0.72rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.8);
  }
</style>

@php
$totalSlots = 0;
$filledSlots = 0;
foreach ($timetableGrid as $gridDay) {
foreach ($gridDay['slots'] as $gridSlot) {
$totalSlots++;
if (!empty($gridSlot['routine'])) {
$filledSlots++;
}
}
}
@endphp

<div class="wrapper">
  @include('receptionist.sidebar')

  <main class="page-content corp-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Timetable</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('receptionist.faculty.index') }}">Faculty</a></li>
            <li class="breadcrumb-item active" aria-current="page">Timetable</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card corp-banner mt-3 mb-3">
      <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <h5 class="mb-1" style="color: var(--corp-ink); font-weight: 700;">Weekly Faculty Schedule</h5>
          <div style="color: var(--corp-muted); font-size: 0.9rem;">{{ trim($faculty->FIRST_NAME . ' ' . $faculty->MIDDLE_NAME . ' ' . $faculty->LAST_NAME) }} ({{ $faculty->USER_CODE }})</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <span class="corp-chip">Total Slots: {{ $totalSlots }}</span>
          <span class="corp-chip">Assigned Slots: {{ $filledSlots }}</span>
          <span class="corp-chip">Free Slots: {{ max($totalSlots - $filledSlots, 0) }}</span>
        </div>
      </div>
    </div>

    <div class="card corp-card mt-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0" style="color: var(--corp-ink); font-weight: 700;">Timetable Matrix</h6>
        <small style="color: var(--corp-muted);">Hourly schedule across weekdays</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered text-center corp-table">
            <thead>
              <tr>
                <th>Day / Hour</th>
                @foreach($hours as $hour)
                <th>
                  <div>{{ $hour->title }}</div>
                  @if(!empty($hour->start_time) && !empty($hour->end_time))
                  <div class="hour-range">
                    {{ \Carbon\Carbon::parse($hour->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($hour->end_time)->format('h:i A') }}
                  </div>
                  @endif
                </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($timetableGrid as $day)
              <tr>
                <td class="corp-day-cell">{{ $day['day'] }}</td>
                @foreach($day['slots'] as $slot)
                <td>
                  @if($slot['routine'])
                  @php
                  $course = optional(optional($slot['routine']->subjectCourse)->courseMaster);
                  $semesterTitle = optional(optional($slot['routine']->syllabus)->semestermaster)->title ?? optional($course->semestermaster)->title;
                  @endphp
                  <div class="slot-card">
                    <div class="slot-course-code">{{ $course->course_code ?? '-' }}</div>
                    <div class="slot-title">{{ $course->course_title ?? '-' }}</div>
                    <div class="slot-room mt-1">Room: {{ optional($slot['routine']->lecturehallmaster)->title ?? '-' }}</div>
                    <div class="slot-badges mt-2 d-flex gap-1 flex-wrap">
                      <span class="badge bg-info">{{ optional($slot['routine']->batch)->batch_name ?? '-' }}</span>
                      <span class="badge bg-secondary">{{ $semesterTitle ?? '-' }}</span>
                    </div>
                  </div>
                  @else
                  <div class="slot-empty">No Class</div>
                  @endif
                </td>
                @endforeach
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
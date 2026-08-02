<?php

use App\Models\BatchMaster;

$batches = BatchMaster::latest()->get();
?>
@include('includes.header')
<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="fw-bold">Faculty Timetable</h2>
      <p class="text-muted">Timetable for Faculty: <span class="fw-bold">{{ $faculty->FIRST_NAME ?? '-' }} {{ $faculty->LAST_NAME ?? '' }}</span></p>
    </div>
  </div>
  <style>
    .page-shell {
      background: linear-gradient(180deg, #f7f8fd 0%, #eef2ff 100%);
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 6px 20px rgba(16, 24, 40, 0.05);
    }

    .filters-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 14px;
    }

    .grid-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    }

    .calendar-table {
      border-collapse: collapse;
      width: 100%;
      min-width: 980px;
      background: #ffffff;
    }

    .calendar-table th,
    .calendar-table td {
      vertical-align: top;
      border: 1px solid #e5e7eb;
      padding: 10px 8px;
    }

    .calendar-table th {
      background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%);
      color: #ffffff;
      font-weight: 600;
      text-align: center;
      font-size: 13px;
      letter-spacing: 0.02em;
    }

    .calendar-table .hour-col {
      background: #eef2ff;
      font-weight: bold;
      color: #312e81;
      width: 190px;
      position: sticky;
      left: 0;
      z-index: 1;
      text-align: center;
      font-size: 12px;
      line-height: 1.35;
    }

    .calendar-table td {
      min-height: 108px;
      background: #fbfcff;
    }

    .calendar-table tbody tr:nth-child(even) td {
      background: #f8faff;
    }

    .calendar-block {
      background: #ffffff;
      border: 1px solid #dbeafe;
      border-left: 4px solid #4f46e5;
      color: #0f172a;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 500;
      box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
      padding: 8px;
      margin: 4px 0;
      text-align: left;
    }

    .calendar-block .course {
      font-size: 12px;
      font-weight: 700;
      color: #1d4ed8;
      line-height: 1.35;
    }

    .calendar-block .semester {
      font-size: 11px;
      color: #0f766e;
    }

    .calendar-block .shift {
      font-size: 11px;
      color: #6b21a8;
    }

    .empty-slot {
      color: #9ca3af;
      font-size: 12px;
      display: inline-block;
      margin-top: 12px;
    }

    @media (max-width: 991.98px) {
      .page-shell {
        padding: 12px;
      }

      .calendar-table {
        min-width: 860px;
      }

      .calendar-table .hour-col {
        width: 150px;
      }
    }
  </style>

  <div class="page-shell">
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="filters-card">
          <label for="batchFilter" class="form-label fw-semibold mb-2">Filters</label>
          <form action="" method="GET" class="row g-2 align-items-end">
            @if(!empty($subjectId))
            <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            @endif
            <div class="col-md-3">
              <label for="batchFilter" class="form-label small text-muted mb-1">Batch</label>
              <select id="batchFilter" class="form-select" name="batch">
                <option value="">All Batches</option>
                @foreach($batches as $batch)
                <option value="{{ $batch->id }}" {{ (int) request('batch', $selectedBatchId ?? 0) === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="semesterFilter" class="form-label small text-muted mb-1">Semester</label>
              <select id="semesterFilter" class="form-select" name="semester_id">
                <option value="">All Semesters</option>
                @foreach($semesterOptions as $sem)
                <option value="{{ $sem->id }}" {{ (int) request('semester_id', $selectedSemesterId ?? 0) === (int) $sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label for="shiftFilter" class="form-label small text-muted mb-1">Shift</label>
              <select id="shiftFilter" class="form-select" name="shift">
                @foreach($shiftOptions as $shift)
                <option value="{{ $shift->slug }}" {{ request('shift', $selectedShift ?? 'common') === $shift->slug ? 'selected' : '' }}>{{ $shift->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label for="programTypeFilter" class="form-label small text-muted mb-1">Program Type</label>
              <select id="programTypeFilter" class="form-select" name="program_type">
                <option value="UG" {{ strtoupper((string) request('program_type', $selectedProgramType ?? 'UG')) === 'UG' ? 'selected' : '' }}>UG</option>
                <option value="PG" {{ strtoupper((string) request('program_type', $selectedProgramType ?? 'UG')) === 'PG' ? 'selected' : '' }}>PG</option>
              </select>
            </div>
            <div class="col-md-2">
              <button class="btn btn-success w-100"><i class="fa fa-search"></i> Apply</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="grid-card">
      <div class="table-responsive">
        @php
        $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $hours = collect($timetable)
        ->groupBy('hour')
        ->map(function ($entries, $hourLabel) {
        return [
        'hour' => $hourLabel,
        'hour_sort' => (int) ($entries->first()['hour_sort'] ?? 0),
        ];
        })
        ->sortBy('hour_sort')
        ->values()
        ->all();
        $calendar = [];
        foreach($weekdays as $day) {
        $calendar[$day] = collect($timetable)->where('weekday', $day)->groupBy('hour');
        }
        @endphp
        <table class="calendar-table">
          <thead>
            <tr>
              <th class="hour-col">Hour</th>
              @foreach($weekdays as $day)
              <th>{{ $day }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($hours as $hourMeta)
            @php $hour = $hourMeta['hour']; @endphp
            <tr>
              <td class="hour-col">{{ $hour }}</td>
              @foreach($weekdays as $day)
              <td>
                @if(isset($calendar[$day][$hour]))
                @foreach($calendar[$day][$hour] as $entry)
                <div class="calendar-block">
                  <div>{{ $entry['course_type'] ?? '-' }}</div>
                  <div class="course">{{ $entry['course'] ?? '-' }}</div>
                  <div class="shift">Faculty: {{ $entry['faculty'] ?? '-' }}</div>
                  @if(!empty($entry['co_faculty']) && count($entry['co_faculty']) > 0)
                  <div class="shift">Co-Faculty: {{ implode(', ', $entry['co_faculty']) }}</div>
                  @endif
                  <div class="semester">Batch: {{ $entry['batch'] ?? '-' }} -{{ $entry['semester'] ?? '-' }}</div>
                  <div class="shift">Shift: {{ $entry['shift'] ?? 'Common' }}</div>
                  <div class="shift">Program: {{ $entry['program_type'] ?? 'UG' }}</div>
                  <div class="shift">Room: {{ $entry['room'] ?? '-' }}</div>
                </div>
                @endforeach
                @else
                <span class="empty-slot">No class</span>
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

</div>
</div>
</div>
</div>
</div>
@include('includes.footer')
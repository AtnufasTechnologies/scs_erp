<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;

$batches = BatchMaster::latest()->get();
$userroletype = StaticController::fetchUserRole();
?>
@include('includes.header')
@if($userroletype == 'itcell')
@include('admin.sidebar')
@else
@include('includes.dept-sidebar')
@endif

<div class="main-content">
  <div class="container-fluid py-4">
    <div class="row mb-4">
      <div class="col-12">
        <h2 class="fw-bold mb-1">Faculty Timetable</h2>
        <p class="text-muted mb-0">Timetable for Faculty: <span class="fw-semibold">{{ $faculty->FIRST_NAME ?? '-' }} {{ $faculty->LAST_NAME ?? '' }}</span></p>
      </div>
    </div>

    @php
    $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

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
    foreach ($weekdays as $day) {
    $calendar[$day] = collect($timetable)->where('weekday', $day)->groupBy('hour');
    }

    $totalClasses = collect($timetable)->count();
    $activeDays = collect($timetable)->pluck('weekday')->filter()->unique()->count();
    $coFacultySlots = collect($timetable)->filter(function ($entry) {
    return !empty($entry['co_faculty']) && count((array) $entry['co_faculty']) > 0;
    })->count();
    @endphp

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body py-3">
            <div class="text-muted small">Total Classes</div>
            <div class="h4 mb-0">{{ $totalClasses }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body py-3">
            <div class="text-muted small">Active Teaching Days</div>
            <div class="h4 mb-0">{{ $activeDays }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm h-100 border-0">
          <div class="card-body py-3">
            <div class="text-muted small">Co-Faculty Slots</div>
            <div class="h4 mb-0">{{ $coFacultySlots }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-header bg-white">
        <strong>Filters</strong>
      </div>
      <div class="card-body">
        <form action="" method="GET" class="row g-2 align-items-end">
          @if(!empty($subjectId))
          <input type="hidden" name="subject_id" value="{{ $subjectId }}">
          @endif

          <div class="col-md-3">
            <label for="batchFilter" class="form-label small text-muted">Batch</label>
            <select id="batchFilter" class="form-select" name="batch">
              <option value="">All Batches</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ (int) request('batch', $selectedBatchId ?? 0) === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label for="semesterFilter" class="form-label small text-muted">Semester</label>
            <select id="semesterFilter" class="form-select" name="semester_id">
              <option value="">All Semesters</option>
              @foreach($semesterOptions as $sem)
              <option value="{{ $sem->id }}" {{ (int) request('semester_id', $selectedSemesterId ?? 0) === (int) $sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label for="shiftFilter" class="form-label small text-muted">Shift</label>
            <select id="shiftFilter" class="form-select" name="shift">
              @foreach($shiftOptions as $shift)
              <option value="{{ $shift->slug }}" {{ request('shift', $selectedShift ?? 'common') === $shift->slug ? 'selected' : '' }}>{{ $shift->title }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label for="programTypeFilter" class="form-label small text-muted">Program Type</label>
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

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          @if(empty($hours))
          <div class="p-4 text-center text-muted">No timetable entries found for selected filters.</div>
          @else
          <table class="table table-bordered table-striped align-middle mb-0" style="min-width: 980px;">
            <thead class="table-light">
              <tr>
                <th style="width: 190px;">Hour</th>
                @foreach($weekdays as $day)
                <th>{{ $day }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($hours as $hourMeta)
              @php $hour = $hourMeta['hour']; @endphp
              <tr>
                <td class="fw-semibold text-primary">{{ $hour }}</td>
                @foreach($weekdays as $day)
                <td>
                  @if(isset($calendar[$day][$hour]))
                  @foreach($calendar[$day][$hour] as $entry)
                  <div class="border rounded p-2 mb-2 bg-white">
                    <div class="small text-muted">{{ $entry['course_type'] ?? '-' }}</div>
                    <div class="fw-semibold text-primary">{{ $entry['course'] ?? '-' }}</div>
                    <div class="small">Faculty: {{ $entry['faculty'] ?? '-' }}</div>
                    @if(!empty($entry['co_faculty']) && count($entry['co_faculty']) > 0)
                    <div class="small">Co-Faculty: {{ implode(', ', $entry['co_faculty']) }}</div>
                    @endif
                    <div class="small text-success">Batch: {{ $entry['batch'] ?? '-' }} - {{ $entry['semester'] ?? '-' }}</div>
                    <div class="small">Shift: {{ $entry['shift'] ?? 'Common' }}</div>
                    <div class="small">Program: {{ $entry['program_type'] ?? 'UG' }}</div>
                    <div class="small">Room: {{ $entry['room'] ?? '-' }}</div>
                  </div>
                  @endforeach
                  @else
                  <span class="text-muted small">No class</span>
                  @endif
                </td>
                @endforeach
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
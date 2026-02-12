@include('includes.header')
<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="fw-bold">Faculty Timetable</h2>
      <p class="text-muted">Timetable for Faculty: <span class="fw-bold">{{ $faculty->FIRST_NAME ?? '-' }} {{ $faculty->LAST_NAME ?? '' }}</span></p>
    </div>
  </div>
  <style>
    .calendar-table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
      background: #535353;
    }

    .calendar-table th,
    .calendar-table td {
      text-align: center;
      vertical-align: middle;
      border: 1px solid #e0e0e0;
      padding: 8px;
    }

    .calendar-table th {
      background: #5e6daa;
      font-weight: 600;
    }

    .calendar-table .hour-col {
      background: #f0f4ff;
      font-weight: bold;
      color: #17472f;
      width: 70px;
    }

    .calendar-block {
      background: linear-gradient(135deg, #327175 0%, #4ba288 100%);
      color: #fff;
      border-radius: 8px;
      font-size: 0.95em;
      font-weight: 500;
      box-shadow: 0 2px 8px #0001;
      padding: 6px 4px;
      margin: 2px 0;
    }

    .calendar-block .course {
      font-size: 0.9em;
      font-weight: 400;
      color: #ffe082;
    }

    .calendar-block .semester {
      font-size: 0.85em;
      color: #b2ffef;
    }

    .calendar-block .batch {
      font-size: 0.85em;
      color: #fffde7;
    }

    .calendar-block .lecture {
      font-size: 0.85em;
      color: #fff;
    }
  </style>
  <div class="table-responsive">
    @php
    $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    $hours = collect($timetable)->pluck('hour')->unique()->sort()->values()->all();
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
        @foreach($hours as $hour)
        <tr>
          <td class="hour-col">{{ $hour }}</td>
          @foreach($weekdays as $day)
          <td>
            @if(isset($calendar[$day][$hour]))
            @foreach($calendar[$day][$hour] as $entry)
            <div class="calendar-block">
              <div>{{ $entry['course_type'] ?? '-' }}</div>
              <div class="course">{{ $entry['course'] ?? '-' }}</div>
              <div class="semester">{{ $entry['semester'] ?? '-' }}</div>
              <div class="batch">Batch: {{ $entry['batch'] ?? '-' }}</div>
              <div class="lecture">Hall: {{ $entry['lecture_hall'] ?? '-' }}</div>
            </div>
            @endforeach
            @else
            <span style="color:#bbb;">—</span>
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
@include('includes.footer')
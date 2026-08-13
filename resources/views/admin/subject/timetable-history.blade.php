@include('includes.header')
@include('includes.dept-sidebar')

<style>
  .tt-history-shell {
    background: linear-gradient(180deg, #f4f7fb 0%, #ffffff 55%);
    min-height: 100vh;
    padding-bottom: 24px;
  }

  .tt-history-head {
    background: #fff;
    border: 1px solid #dde6f1;
    border-left: 5px solid #1f4e79;
    border-radius: 12px;
    padding: 14px 16px;
    box-shadow: 0 8px 20px rgba(15, 39, 65, 0.07);
    margin-bottom: 14px;
  }

  .tt-history-card {
    border: 1px solid #dbe5f1;
    border-radius: 12px;
    box-shadow: 0 8px 18px rgba(15, 39, 65, 0.08);
    overflow: hidden;
    margin-bottom: 16px;
  }

  .tt-history-card .card-header {
    background: linear-gradient(135deg, #1f4e79 0%, #2f6da4 100%);
    color: #fff;
  }

  .tt-history-table th,
  .tt-history-table td {
    vertical-align: top;
    min-width: 160px;
  }

  .tt-slot {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fbff;
    padding: 8px;
    margin-bottom: 8px;
    font-size: 12px;
  }

  .tt-slot:last-child {
    margin-bottom: 0;
  }

  .tt-meta {
    color: #5b6f87;
    font-size: 11px;
    margin-top: 4px;
  }
</style>

<div class="main-content tt-history-shell">
  <div class="container-fluid">
    <div class="tt-history-head d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h4 class="mb-1">Full Timetable History</h4>
        <div class="text-muted">{{ $data->title }} ({{ $data->code ?? '-' }})</div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-primary">Views: {{ $totalGroups }}</span>
        <a href="{{ route('department.timetable', [$data->id, $data->title]) }}" class="btn btn-outline-primary btn-sm">Back To Scheduler</a>
      </div>
    </div>

    @if(($groups ?? collect())->isEmpty())
    <div class="alert alert-info">No timetable records found for any batch/semester.</div>
    @else
    @foreach($groups as $group)
    <div class="card tt-history-card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="fw-semibold">
          {{ $group['batch_name'] ?? 'Batch' }} | {{ $group['semester_title'] ?? 'Semester' }}
        </div>
        <div class="d-flex gap-2">
          <span class="badge bg-light text-dark">{{ $group['program_type'] ?? 'UG' }}</span>
          <span class="badge bg-warning text-dark">{{ $group['shift_title'] ?? ucfirst($group['shift'] ?? 'common') }}</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered mb-0 tt-history-table">
            <thead class="table-light">
              <tr>
                <th style="width: 220px;">Hour</th>
                @foreach($group['days'] as $day)
                <th>{{ $day }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($group['hours'] as $hour)
              <tr>
                <th class="bg-light">{{ $hour['label'] }}</th>
                @foreach($group['days'] as $day)
                @php
                $slots = $group['entries'][$hour['hour_no']][$day] ?? [];
                @endphp
                <td>
                  @if(empty($slots))
                  <span class="text-muted small">-</span>
                  @else
                  @foreach($slots as $slot)
                  <div class="tt-slot">
                    <div class="fw-semibold">{{ $slot['course'] ?? '-' }}</div>
                    <div>{{ $slot['faculty'] ?? '-' }}</div>
                    <div class="tt-meta">
                      <span>Delivery: {{ $slot['delivery'] ?: '-' }}</span>
                      <span class="ms-2">Group: {{ $slot['allocation'] ?: '-' }}</span>
                      <span class="ms-2">Room: {{ $slot['room'] ?: '-' }}</span>
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
      </div>
    </div>
    @endforeach
    @endif
  </div>
</div>

@include('includes.footer')
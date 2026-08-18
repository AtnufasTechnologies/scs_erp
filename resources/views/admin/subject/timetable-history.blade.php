@include('includes.header')
@include('includes.dept-sidebar')

<style>
  .tt-history-shell {
    background: linear-gradient(180deg, #f3f0ff 0%, #ffffff 55%);
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
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px #5740b433;
    overflow: hidden;
    margin-bottom: 16px;
  }

  .tt-history-card .card-header {
    background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
    color: #fff;
  }

  .tt-history-table {
    margin-bottom: 0;
  }

  .tt-history-table th,
  .tt-history-table td {
    border: 1px solid #eee;
    padding: 0.75rem;
    text-align: center;
    vertical-align: top;
    min-width: 160px;
    transition: background 0.2s;
  }

  .tt-history-table th {
    background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
    color: #5740b4;
    font-weight: 600;
    letter-spacing: 0.04em;
  }

  .tt-history-table tr:hover td {
    background: #f7f7fa;
  }

  .tt-period-cell {
    vertical-align: top;
  }

  .tt-slot {
    text-align: left;
    border: 1px solid #d9d2ff;
    border-radius: 8px;
    background: #faf8ff;
    padding: 8px;
    margin-bottom: 8px;
    font-size: 12px;
  }

  .tt-slot:last-child {
    margin-bottom: 0;
  }

  .tt-meta {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 0.25rem;
    margin-top: 0.2rem;
  }

  .tt-chip-delivery {
    background: #efe7ff;
    color: #4c2f9d;
  }

  .tt-chip-group {
    background: #e7fff4;
    color: #1d7f52;
  }

  .tt-cell-count {
    font-weight: 700;
    color: #6c757d;
    margin-bottom: 8px;
    text-align: left;
    font-size: 12px;
  }

  .tt-hidden {
    display: none !important;
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

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-8 col-lg-6">
            <label for="historyCourseSearch" class="form-label mb-1 fw-semibold">Search Course In History</label>
            <input type="text" id="historyCourseSearch" class="form-control" placeholder="Type course code or title...">
          </div>
          <div class="col-md-4 col-lg-6 d-flex align-items-end">
            <small class="text-muted" id="historySearchMeta">Showing all timetable slots</small>
          </div>
        </div>
      </div>
    </div>

    @if(($groups ?? collect())->isEmpty())
    <div class="alert alert-info">No timetable records found for any batch/semester.</div>
    @else
    @foreach($groups as $group)
    <div class="card tt-history-card" data-history-group>
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
          <table class="table tt-history-table align-middle">
            <thead>
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
                <th>{{ $hour['label'] }}</th>
                @foreach($group['days'] as $day)
                @php
                $slots = $group['entries'][$hour['hour_no']][$day] ?? [];
                @endphp
                <td class="tt-period-cell">
                  @if(empty($slots))
                  <span class="text-muted small">-</span>
                  @else
                  <div class="tt-cell-count">{{ count($slots) }} item(s)</div>
                  @foreach($slots as $slot)
                  @php
                  $slotSearchText = strtolower(trim(implode(' ', array_filter([
                  (string) ($slot['course'] ?? ''),
                  (string) ($slot['faculty'] ?? ''),
                  (string) ($slot['room'] ?? ''),
                  (string) ($slot['delivery'] ?? ''),
                  (string) ($slot['allocation'] ?? ''),
                  ], function ($part) {
                  return trim((string) $part) !== '';
                  }))));
                  @endphp
                  <div class="tt-slot" data-search-text="{{ $slotSearchText }}">
                    <div class="fw-semibold">
                      {{ $slot['course'] ?? '-' }}
                      @if(!empty($slot['is_group_teaching']))
                      <span class="badge bg-warning text-dark ms-1">Group Teaching</span>
                      @endif
                    </div>
                    <div>
                      <i class="fas fa-user-tie"></i>
                      <span class="fw-semibold">{{ !empty($slot['is_group_teaching']) ? 'Group Faculty' : 'Primary' }}:</span>
                      {{ $slot['faculty'] ?? '-' }}
                    </div>
                    <div><i class="fas fa-door-open"></i> Room: {{ $slot['room'] ?: '-' }}</div>
                    <div class="mt-1">
                      @if(!empty($slot['delivery']))
                      <span class="tt-meta tt-chip-delivery">{{ $slot['delivery'] }}</span>
                      @endif
                      @if(!empty($slot['allocation']))
                      <span class="tt-meta tt-chip-group">{{ $slot['allocation'] }}</span>
                      @endif
                    </div>
                    <div class="mt-1">
                      <span class="badge {{ (int) ($slot['slot_active'] ?? 1) === 1 ? 'bg-success' : 'bg-secondary' }}">
                        {{ (int) ($slot['slot_active'] ?? 1) === 1 ? 'Active' : 'Inactive' }}
                      </span>
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

<script>
  (function() {
    const searchInput = document.getElementById('historyCourseSearch');
    const searchMeta = document.getElementById('historySearchMeta');
    const groups = Array.from(document.querySelectorAll('[data-history-group]'));

    if (!searchInput || groups.length === 0) {
      return;
    }

    function updateCellCounter(cell) {
      const countLabel = cell.querySelector('.tt-cell-count');
      if (!countLabel) {
        return;
      }

      const visibleSlots = cell.querySelectorAll('.tt-slot:not(.tt-hidden)').length;
      if (visibleSlots > 0) {
        countLabel.classList.remove('tt-hidden');
        countLabel.textContent = visibleSlots + ' item(s)';
      } else {
        countLabel.classList.add('tt-hidden');
      }
    }

    function applyFilter() {
      const term = (searchInput.value || '').trim().toLowerCase();
      let visibleSlotCount = 0;
      let visibleGroupCount = 0;

      groups.forEach((group) => {
        const slots = Array.from(group.querySelectorAll('.tt-slot'));
        let groupHasVisibleSlots = false;

        slots.forEach((slot) => {
          const haystack = (slot.getAttribute('data-search-text') || '').toLowerCase();
          const matched = term === '' || haystack.includes(term);
          slot.classList.toggle('tt-hidden', !matched);

          if (matched) {
            groupHasVisibleSlots = true;
            visibleSlotCount += 1;
          }
        });

        const cells = Array.from(group.querySelectorAll('.tt-period-cell'));
        cells.forEach((cell) => {
          updateCellCounter(cell);
        });

        group.classList.toggle('tt-hidden', term !== '' && !groupHasVisibleSlots);
        if (!group.classList.contains('tt-hidden')) {
          visibleGroupCount += 1;
        }
      });

      if (term === '') {
        searchMeta.textContent = 'Showing all timetable slots';
      } else {
        searchMeta.textContent = 'Matched ' + visibleSlotCount + ' slot(s) in ' + visibleGroupCount + ' batch/semester group(s)';
      }
    }

    searchInput.addEventListener('input', applyFilter);
    applyFilter();
  })();
</script>

@include('includes.footer')
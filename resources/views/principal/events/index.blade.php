@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Event Controller</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Work Overview</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mt-3 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-primary mb-1">{{ (int) ($summary['total_events'] ?? 0) }}</h3>
            <div class="text-muted small">Total Events</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-success mb-1">{{ (int) ($summary['active_events'] ?? 0) }}</h3>
            <div class="text-muted small">Active Events</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-info mb-1">{{ (int) ($summary['total_programs'] ?? 0) }}</h3>
            <div class="text-muted small">Programs</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-warning mb-1">{{ (int) ($summary['upcoming_programs'] ?? 0) }}</h3>
            <div class="text-muted small">Upcoming Programs</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-secondary mb-1">{{ (int) ($summary['total_faculty_duties'] ?? 0) }}</h3>
            <div class="text-muted small">Faculty Duties</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-primary mb-1">Rs {{ number_format((float) ($summary['total_budget'] ?? 0), 0) }}</h3>
            <div class="text-muted small">Total Budget</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-success mb-1">Rs {{ number_format((float) ($summary['total_income'] ?? 0), 0) }}</h3>
            <div class="text-muted small">Total Income</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h3 class="fw-bold text-danger mb-1">Rs {{ number_format((float) ($summary['total_expense'] ?? 0), 0) }}</h3>
            <div class="text-muted small">Total Expense</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3 g-3">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Event Controller Work Log</h5>
            <form method="GET" action="{{ route('principal.events.work') }}" class="d-flex align-items-center gap-2">
              <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($validStatuses as $status)
                <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
              </select>
              @if($selectedStatus !== '')
              <a href="{{ route('principal.events.work') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
              @endif
            </form>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Event</th>
                    <th>Duration</th>
                    <th class="text-center">Programs</th>
                    <th class="text-center">Duties</th>
                    <th class="text-center">Sponsors</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($events as $event)
                  <tr>
                    <td class="fw-semibold">{{ $event->title }}</td>
                    <td>
                      <small>
                        {{ optional($event->start_date)->format('d M Y') }}<br>
                        to {{ optional($event->end_date)->format('d M Y') }}
                      </small>
                    </td>
                    <td class="text-center"><span class="badge bg-info text-dark">{{ (int) $event->programs_count }}</span></td>
                    <td class="text-center"><span class="badge bg-secondary">{{ (int) $event->faculty_duties_count }}</span></td>
                    <td class="text-center"><span class="badge bg-warning text-dark">{{ (int) $event->sponsors_count }}</span></td>
                    <td>{{ $event->creator->name ?? '-' }}</td>
                    <td>
                      <span class="badge rounded-pill
                        @if($event->status === 'active') bg-success
                        @elseif($event->status === 'completed') bg-primary
                        @elseif($event->status === 'draft') bg-secondary
                        @else bg-danger @endif">
                        {{ ucfirst($event->status) }}
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="{{ route('event-coordinator.report', $event) }}" class="btn btn-sm btn-outline-info" title="Full Report">
                        <i class="fas fa-file-alt"></i>
                        <span class="ms-1">Report</span>
                      </a>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">No event records found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          @if($events->hasPages())
          <div class="card-footer bg-white">
            {{ $events->links() }}
          </div>
          @endif
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            Recent Programs
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              @forelse($recentPrograms as $program)
              <div class="list-group-item">
                <div class="fw-semibold">{{ $program->name }}</div>
                <div class="small text-muted">{{ optional($program->event)->title ?? '-' }}</div>
                <div class="small text-muted">{{ optional($program->program_date)->format('d M Y') }}{{ $program->venue ? ' - ' . $program->venue : '' }}</div>
              </div>
              @empty
              <div class="list-group-item text-muted">No programs found.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            Recent Faculty Duties
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              @forelse($recentDuties as $duty)
              <div class="list-group-item">
                <div class="fw-semibold">{{ $duty->duty_title }}</div>
                <div class="small text-muted">
                  {{ trim(($duty->faculty->FIRST_NAME ?? '') . ' ' . ($duty->faculty->LAST_NAME ?? '')) ?: '-' }}
                  @if(!empty($duty->faculty->USER_CODE))
                  ({{ $duty->faculty->USER_CODE }})
                  @endif
                </div>
                <div class="small text-muted">{{ $duty->event->title ?? '-' }}{{ $duty->program ? ' - ' . $duty->program->name : '' }}</div>
              </div>
              @empty
              <div class="list-group-item text-muted">No faculty duties found.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
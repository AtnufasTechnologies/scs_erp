@include('includes.header')

<div class="wrapper">
  @include('event-coordinator.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Event Coordinator</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Welcome Banner -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card shadow-sm border-0" style="background: linear-gradient(135deg,#6f42c1,#e83e8c);">
          <div class="card-body py-4 px-5">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h4 class="text-white fw-bold mb-1"><i class="fas fa-star me-2"></i>Event Coordinator</h4>
                <p class="text-white-50 mb-0">Plan, organise, and manage college-level events end-to-end.</p>
              </div>
              <div class="text-end">
                <div class="display-6 text-white fw-bold">{{ date('d M Y') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="row mt-4 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
              style="width:52px;height:52px;background:#6f42c1;"><i class="fas fa-calendar-alt text-white fs-5"></i></div>
            <div>
              <div class="text-muted small">Total Events</div>
              <div class="fw-bold fs-4">{{ $totalEvents }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
              style="width:52px;height:52px;background:#28a745;"><i class="fas fa-check-circle text-white fs-5"></i></div>
            <div>
              <div class="text-muted small">Active Events</div>
              <div class="fw-bold fs-4">{{ $activeEvents }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
              style="width:52px;height:52px;background:#fd7e14;"><i class="fas fa-rupee-sign text-white fs-5"></i></div>
            <div>
              <div class="text-muted small">Total Expenses</div>
              <div class="fw-bold fs-5">₹{{ number_format($totalExpenses, 2) }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
              style="width:52px;height:52px;background:#17a2b8;"><i class="fas fa-handshake text-white fs-5"></i></div>
            <div>
              <div class="text-muted small">Sponsorship Received</div>
              <div class="fw-bold fs-5">₹{{ number_format($totalSponsorship, 2) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Programs -->
    <div class="row mt-4">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom fw-bold py-3">
            <i class="fas fa-clock text-primary me-2"></i>Upcoming Programs
          </div>
          <div class="card-body p-0">
            @forelse($upcomingPrograms as $prog)
            <div class="d-flex align-items-center px-4 py-3 border-bottom">
              <div class="me-3">
                <span class="badge bg-light-info text-info px-3 py-2">{{ $prog->program_date->format('d M') }}</span>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ $prog->name }}</div>
                <small class="text-muted">
                  <a href="{{ route('event-coordinator.events.show', $prog->event_id) }}">{{ $prog->event->title }}</a>
                  @if($prog->venue) &nbsp;·&nbsp; {{ $prog->venue }} @endif
                </small>
              </div>
              @if($prog->registration_fee > 0)
              <span class="badge bg-success ms-2">₹{{ number_format($prog->registration_fee, 0) }}</span>
              @else
              <span class="badge bg-secondary ms-2">Free</span>
              @endif
            </div>
            @empty
            <div class="p-4 text-center text-muted">No upcoming programs.</div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Recent Events -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="fas fa-calendar-check text-purple me-2" style="color:#6f42c1;"></i>Recent Events</span>
            <a href="{{ route('event-coordinator.events.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
          <div class="card-body p-0">
            @forelse($recentEvents as $evt)
            <a href="{{ route('event-coordinator.events.show', $evt) }}"
              class="d-flex align-items-center px-4 py-3 border-bottom text-decoration-none text-dark">
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ $evt->title }}</div>
                <small class="text-muted">
                  {{ $evt->start_date->format('d M Y') }} &mdash; {{ $evt->end_date->format('d M Y') }}
                </small>
              </div>
              <span class="badge rounded-pill
                @if($evt->status==='active') bg-success
                @elseif($evt->status==='draft') bg-secondary
                @elseif($evt->status==='completed') bg-primary
                @else bg-danger @endif ms-2">{{ ucfirst($evt->status) }}</span>
            </a>
            @empty
            <div class="p-4 text-center text-muted">No events yet.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

@include('includes.footer')
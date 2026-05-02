@include('includes.header')

<div class="wrapper">
  @include('event-coordinator.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Events</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">All Events</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>College Events</h5>
        <a href="{{ route('event-coordinator.events.create') }}" class="btn btn-primary">
          <i class="fas fa-plus me-1"></i>New Event
        </a>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="eventsTable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Title</th>
                  <th>Dates</th>
                  <th>Venue</th>
                  <th class="text-center">Programs</th>
                  <th class="text-center">Sponsors</th>
                  <th>Budget</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($events as $event)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td class="fw-semibold">{{ $event->title }}</td>
                  <td>
                    <small>{{ $event->start_date->format('d M Y') }}<br>to {{ $event->end_date->format('d M Y') }}</small>
                  </td>
                  <td>{{ $event->venue ?? '—' }}</td>
                  <td class="text-center">
                    <span class="badge bg-info text-dark">{{ $event->programs_count }}</span>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-warning text-dark">{{ $event->sponsors_count }}</span>
                  </td>
                  <td>₹{{ number_format($event->total_budget, 0) }}</td>
                  <td>
                    <span class="badge rounded-pill
                      @if($event->status==='active') bg-success
                      @elseif($event->status==='draft') bg-secondary
                      @elseif($event->status==='completed') bg-primary
                      @else bg-danger @endif">{{ ucfirst($event->status) }}</span>
                  </td>
                  <td class="text-center">
                    <a href="{{ route('event-coordinator.events.show', $event) }}"
                      class="btn btn-sm btn-outline-primary me-1" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('event-coordinator.events.edit', $event) }}"
                      class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="{{ route('event-coordinator.report', $event) }}"
                      class="btn btn-sm btn-outline-info me-1" title="Report">
                      <i class="fas fa-file-alt"></i>
                    </a>
                    <form action="{{ route('event-coordinator.events.destroy', $event) }}" method="POST"
                      class="d-inline" onsubmit="return confirm('Delete this event?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    No events found. <a href="{{ route('event-coordinator.events.create') }}">Create one now</a>.
                  </td>
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
  </main>
</div>

@include('includes.footer')
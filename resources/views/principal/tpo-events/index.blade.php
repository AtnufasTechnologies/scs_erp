@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <div class="page-wrapper">
    <div class="page-content">
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">TPO Event Approvals</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">TPO Events</li>
            </ol>
          </nav>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">Event Submissions</h6>
          <span class="badge bg-secondary">Total: {{ $events->count() }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0 align-middle">
              <thead>
                <tr>
                  <th>Program</th>
                  <th>Type</th>
                  <th>Campus / Dept</th>
                  <th>Date</th>
                  <th>Participants</th>
                  <th>Report</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($events as $event)
                <tr>
                  <td>
                    <div class="fw-semibold">{{ $event->title }}</div>
                    <div class="small text-muted">Resource Person: {{ $event->resource_person ?: 'N/A' }}</div>
                    <div class="small text-muted">{{ $event->program_description }}</div>
                  </td>
                  <td>{{ $eventTypeOptions[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type ?? 'N/A')) }}</td>
                  <td>
                    <div>{{ $event->campus->name ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $event->subject->title ?? $event->subject->name ?? 'N/A' }}</div>
                  </td>
                  <td>{{ $event->event_date ? $event->event_date->format('d M Y') : 'N/A' }}</td>
                  <td>{{ $event->participant_count }}</td>
                  <td>
                    @if($event->report_path)
                    <a href="{{ Storage::disk('s3')->url($event->report_path) }}" target="_blank">View Report</a>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>
                    @if($event->approval_status === 'approved')
                    <span class="badge bg-success">Approved</span>
                    @elseif($event->approval_status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                    @else
                    <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                    @if($event->approver)
                    <div class="small text-muted mt-1">By: {{ $event->approver->name ?? 'Principal' }}</div>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                      <form action="{{ route('principal.tpo-events.approval', $event->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="approval_status" value="approved">
                        <button class="btn btn-sm btn-success" type="submit">Approve</button>
                      </form>
                      <form action="{{ route('principal.tpo-events.approval', $event->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="approval_status" value="rejected">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No event submissions found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
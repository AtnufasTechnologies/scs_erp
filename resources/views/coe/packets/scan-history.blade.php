@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Scan History</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.packets.barcodes.tracking') }}">Tracking</a></li>
            <li class="breadcrumb-item active" aria-current="page">Scan History</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-history me-2"></i>Scan History</h3>
                  <p class="text-white-50 mb-0">
                    Complete audit log for packet <strong>{{ $packet->packet_number }}</strong>
                  </p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.barcodes.tracking') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Tracking
                  </a>
                  <a href="{{ route('coe.packets.show', $packet->id) }}" class="btn btn-outline-light">
                    <i class="fas fa-eye me-2"></i>Packet Details
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Packet Summary -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-2">
              <strong><i class="fas fa-box me-1"></i>Packet:</strong>
              <span class="badge bg-dark fs-6">{{ $packet->packet_number }}</span>
            </div>
            <div class="col-md-2">
              <strong><i class="fas fa-barcode me-1"></i>Barcode:</strong>
              <code>{{ $packet->barcode ?? 'N/A' }}</code>
            </div>
            <div class="col-md-3">
              <strong><i class="fas fa-book me-1"></i>Subject:</strong>
              {{ $packet->subjectMaster->subject_code ?? '' }} - {{ $packet->subjectMaster->name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
              <strong><i class="fas fa-user me-1"></i>Holder:</strong>
              {{ $packet->current_holder_name ?? 'N/A' }}
            </div>
            <div class="col-md-2">
              <strong>Status:</strong>
              @if($packet->status === 'generated')
              <span class="badge bg-warning text-dark">Generated</span>
              @elseif($packet->status === 'assigned')
              <span class="badge bg-info">Assigned</span>
              @elseif($packet->status === 'evaluating')
              <span class="badge bg-primary">Evaluating</span>
              @elseif($packet->status === 'completed')
              <span class="badge bg-success">Completed</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Scan Log Table -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-list-alt me-2 text-primary"></i>Scan Audit Log ({{ $scanLogs->total() }})</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Action</th>
                  <th>Scanned By</th>
                  <th>Holder</th>
                  <th>Status Change</th>
                  <th>Remarks</th>
                  <th>Device/IP</th>
                  <th>Date & Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse($scanLogs as $index => $log)
                <tr>
                  <td>{{ $scanLogs->firstItem() + $index }}</td>
                  <td><span class="badge {{ $log->action_badge }}">{{ $log->action }}</span></td>
                  <td>
                    <span class="fw-semibold">{{ $log->scanned_by_name }}</span>
                  </td>
                  <td>
                    @if($log->holder_name)
                    {{ $log->holder_name }}
                    @if($log->holder_role)
                    <br><small class="text-muted">{{ $log->holder_role }}</small>
                    @endif
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($log->previous_status !== $log->new_status)
                    <span class="badge bg-secondary">{{ $log->previous_status }}</span>
                    <i class="fas fa-arrow-right mx-1 text-muted"></i>
                    <span class="badge bg-primary">{{ $log->new_status }}</span>
                    @else
                    <span class="text-muted">No change</span>
                    @endif
                  </td>
                  <td>
                    @if($log->remarks)
                    <small>{{ Str::limit($log->remarks, 50) }}</small>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <small class="text-muted">
                      {{ $log->ip_address ?? '-' }}
                      @if($log->latitude && $log->longitude)
                      <br><i class="fas fa-map-pin"></i> {{ number_format($log->latitude, 4) }}, {{ number_format($log->longitude, 4) }}
                      @endif
                    </small>
                  </td>
                  <td>
                    <small>{{ $log->created_at->format('d M Y') }}</small>
                    <br><small class="text-muted">{{ $log->created_at->format('h:i:s A') }}</small>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                    No scan records found for this packet.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($scanLogs->hasPages())
        <div class="card-footer bg-white">
          {{ $scanLogs->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')
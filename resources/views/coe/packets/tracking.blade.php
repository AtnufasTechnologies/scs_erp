@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Packet Tracking</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.packets.index') }}">Packets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tracking</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-map-marker-alt me-2"></i>Packet Tracking Dashboard</h3>
                  <p class="text-white-50 mb-0">Monitor packet movements, current holders, and scan history</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.barcodes.scanner') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-qrcode me-2"></i>Open Scanner
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-barcode text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Barcoded Packets</p>
                  <h4 class="mb-0 fw-bold">{{ $totalBarcoded }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-qrcode text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Scans</p>
                  <h4 class="mb-0 fw-bold">{{ $totalScans }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-users text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Active Holders</p>
                  <h4 class="mb-0 fw-bold">{{ $activeHolders->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-clock text-danger" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Recent Scans (24h)</p>
                  <h4 class="mb-0 fw-bold">{{ $recentScans->where('created_at', '>=', now()->subDay())->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-filter me-2 text-primary"></i>Filter Packets</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('coe.packets.barcodes.tracking') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam Session</label>
              <select name="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($examSessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name ?? 'Session #'.$session->id }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Subject</label>
              <select name="erp_subject_id" class="form-select">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->erp_subject_id }}" {{ request('erp_subject_id') == $subject->erp_subject_id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="generated" {{ request('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="evaluating" {{ request('status') == 'evaluating' ? 'selected' : '' }}>Evaluating</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Search</label>
              <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Barcode / Holder...">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Filter</button>
            </div>
          </form>
        </div>
      </div>

      <div class="row">
        <!-- Tracked Packets Table -->
        <div class="col-lg-8 mb-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-box me-2 text-primary"></i>Tracked Packets ({{ $packets->total() }})</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Packet</th>
                      <th>Barcode</th>
                      <th>Subject</th>
                      <th>Status</th>
                      <th>Current Holder</th>
                      <th>Last Scanned</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($packets as $packet)
                    <tr>
                      <td><span class="badge bg-dark">{{ $packet->packet_number }}</span></td>
                      <td><code class="small">{{ $packet->barcode }}</code></td>
                      <td>{{ $packet->subjectMaster->subject_code ?? '-' }}</td>
                      <td>
                        @if($packet->status === 'generated')
                        <span class="badge bg-warning text-dark">Generated</span>
                        @elseif($packet->status === 'assigned')
                        <span class="badge bg-info">Assigned</span>
                        @elseif($packet->status === 'evaluating')
                        <span class="badge bg-primary">Evaluating</span>
                        @elseif($packet->status === 'completed')
                        <span class="badge bg-success">Completed</span>
                        @endif
                      </td>
                      <td>
                        @if($packet->current_holder_name)
                        <span class="fw-semibold">{{ $packet->current_holder_name }}</span>
                        @if($packet->current_holder_role)
                        <br><small class="text-muted">{{ $packet->current_holder_role }}</small>
                        @endif
                        @else
                        <span class="text-muted">—</span>
                        @endif
                      </td>
                      <td>
                        @if($packet->last_scanned_at)
                        <small>{{ $packet->last_scanned_at->format('d M, h:i A') }}</small>
                        @else
                        <span class="text-muted">Never</span>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('coe.packets.barcodes.history', $packet->id) }}" class="btn btn-sm btn-outline-info" title="Scan History">
                          <i class="fas fa-history"></i>
                        </a>
                        <a href="{{ route('coe.packets.show', $packet->id) }}" class="btn btn-sm btn-outline-secondary" title="Packet Details">
                          <i class="fas fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-barcode fa-2x mb-2 d-block"></i>
                        No barcoded packets found. Generate barcodes from the packets page.
                      </td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            @if($packets->hasPages())
            <div class="card-footer bg-white">
              {{ $packets->appends(request()->query())->links() }}
            </div>
            @endif
          </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4 mb-4">
          <!-- Active Holders -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-user-tag me-2 text-warning"></i>Active Holders</h5>
            </div>
            <div class="card-body p-0">
              @if($activeHolders->count() > 0)
              <div class="list-group list-group-flush">
                @foreach($activeHolders as $holder)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fw-semibold">{{ $holder->current_holder_name }}</span>
                    @if($holder->current_holder_role)
                    <br><small class="text-muted">{{ $holder->current_holder_role }}</small>
                    @endif
                  </div>
                  <span class="badge bg-primary rounded-pill">{{ $holder->packet_count }} packets</span>
                </div>
                @endforeach
              </div>
              @else
              <div class="text-center py-3 text-muted">
                <small>No active holders</small>
              </div>
              @endif
            </div>
          </div>

          <!-- Recent Scan Activity -->
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-stream me-2 text-info"></i>Recent Activity</h5>
            </div>
            <div class="card-body p-0">
              @if($recentScans->count() > 0)
              <div class="list-group list-group-flush">
                @foreach($recentScans->take(15) as $scan)
                <div class="list-group-item py-2">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <span class="badge {{ $scan->action_badge }} me-1">{{ $scan->action }}</span>
                      <span class="fw-semibold small">{{ $scan->packet->packet_number ?? 'Unknown' }}</span>
                    </div>
                    <small class="text-muted">{{ $scan->created_at->diffForHumans() }}</small>
                  </div>
                  <small class="text-muted d-block mt-1">
                    By: {{ $scan->scanned_by_name }}
                    @if($scan->holder_name) &middot; Holder: {{ $scan->holder_name }} @endif
                  </small>
                </div>
                @endforeach
              </div>
              @else
              <div class="text-center py-3 text-muted">
                <small>No scan activity yet</small>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stats-card {
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
  }
</style>

@include('includes.footer')
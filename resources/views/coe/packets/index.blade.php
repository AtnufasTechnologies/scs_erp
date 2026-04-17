@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Packet Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Packets</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-box-open me-2"></i>Packet Management</h3>
                  <p class="text-white-50 mb-0">View and manage answer script packets for evaluation</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.barcodes.scanner') }}" class="btn btn-light me-2">
                    <i class="fas fa-qrcode me-1"></i>Scanner
                  </a>
                  <a href="{{ route('coe.packets.barcodes.tracking') }}" class="btn btn-light me-2">
                    <i class="fas fa-map-marker-alt me-1"></i>Tracking
                  </a>
                  <a href="{{ route('coe.packets.generate') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>Generate Packets
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-box-open text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Packets</p>
                  <h4 class="mb-0 fw-bold">{{ $totalPackets }}</h4>
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
                  <i class="fas fa-cogs text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Generated</p>
                  <h4 class="mb-0 fw-bold">{{ $generatedCount }}</h4>
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
                  <i class="fas fa-user-check text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Assigned</p>
                  <h4 class="mb-0 fw-bold">{{ $assignedCount }}</h4>
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
                  <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Completed</p>
                  <h4 class="mb-0 fw-bold">{{ $completedCount }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Card -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.packets.index') }}" class="row g-3 align-items-end">
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
                <option value="generated" {{ request('status') === 'generated' ? 'selected' : '' }}>Generated</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="evaluating" {{ request('status') === 'evaluating' ? 'selected' : '' }}>Evaluating</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Packet No..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('coe.packets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Barcode Bulk Actions -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <span class="fw-semibold"><i class="fas fa-barcode me-2 text-primary"></i>Barcode Actions</span>
            <small class="text-muted ms-2">Select packets using checkboxes below</small>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="generateBarcodesBtn" disabled>
              <i class="fas fa-barcode me-1"></i>Generate Barcodes
            </button>
            <button type="button" class="btn btn-outline-success" id="printLabelsBtn" disabled>
              <i class="fas fa-print me-1"></i>Print Labels
            </button>
          </div>
        </div>
      </div>

      <!-- Packets Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th width="3%"><input type="checkbox" id="selectAllPackets" class="form-check-input"></th>
                  <th>#</th>
                  <th>Packet Number</th>
                  <th>Barcode</th>
                  <th>Subject</th>
                  <th>Session</th>
                  <th>Total Scripts</th>
                  <th>Status</th>
                  <th>Evaluator</th>
                  <th>Generated By</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($packets as $index => $packet)
                <tr>
                  <td><input type="checkbox" class="form-check-input packet-checkbox" value="{{ $packet->id }}" data-has-barcode="{{ $packet->barcode ? '1' : '0' }}"></td>
                  <td>{{ $packets->firstItem() + $index }}</td>
                  <td><span class="badge bg-dark fs-6">{{ $packet->packet_number }}</span></td>
                  <td>
                    @if($packet->barcode)
                    <code class="small">{{ $packet->barcode }}</code>
                    @else
                    <span class="text-muted small">Not generated</span>
                    @endif
                  </td>
                  <td>
                    @if($packet->subjectMaster)
                    {{ $packet->subjectMaster->subject_code }} - {{ $packet->subjectMaster->name }}
                    @else
                    Subject #{{ $packet->erp_subject_id }}
                    @endif
                  </td>
                  <td>{{ $packet->examSession->name ?? 'Session #'.$packet->exam_session_id }}</td>
                  <td><span class="badge bg-primary">{{ $packet->total_scripts }}</span></td>
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
                  <td>{{ $packet->evaluator->name ?? '—' }}</td>
                  <td>{{ $packet->generatedByUser->name ?? 'N/A' }}</td>
                  <td>{{ $packet->created_at ? $packet->created_at->format('d M Y') : '-' }}</td>
                  <td>
                    <a href="{{ route('coe.packets.show', $packet->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                    @if($packet->status === 'generated')
                    <button type="button" class="btn btn-sm btn-outline-primary assign-evaluator-btn"
                      data-packet-id="{{ $packet->id }}"
                      data-packet-number="{{ $packet->packet_number }}"
                      data-bs-toggle="modal" data-bs-target="#assignEvaluatorModal"
                      title="Assign Evaluator">
                      <i class="fas fa-user-plus"></i>
                    </button>
                    @endif
                    @if(in_array($packet->status, ['assigned', 'evaluating']))
                    <form method="POST" action="{{ route('coe.packets.update-status') }}" class="d-inline">
                      @csrf
                      <input type="hidden" name="packet_id" value="{{ $packet->id }}">
                      <input type="hidden" name="status" value="completed">
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Mark Completed"
                        onclick="return confirm('Mark packet {{ $packet->packet_number }} as completed?')">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="12" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No packets found. <a href="{{ route('coe.packets.generate') }}">Generate packets</a> to get started.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($packets->hasPages())
        <div class="card-footer bg-white">
          {{ $packets->withQueryString()->links() }}
        </div>
        @endif
      </div>

    </div>
  </main>
</div>

<!-- Assign Evaluator Modal -->
<div class="modal fade" id="assignEvaluatorModal" tabindex="-1" aria-labelledby="assignEvaluatorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('coe.packets.assign-evaluator') }}">
        @csrf
        <input type="hidden" name="packet_id" id="assignPacketId">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="assignEvaluatorModalLabel"><i class="fas fa-user-plus me-2"></i>Assign Evaluator</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Packet</label>
            <input type="text" class="form-control" id="assignPacketNumber" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Evaluator <span class="text-danger">*</span></label>
            <select name="evaluator_id" class="form-select" required>
              <option value="">-- Select Evaluator --</option>
              @php
              $evaluators = \App\Models\User::orderBy('name')->get();
              @endphp
              @foreach($evaluators as $evaluator)
              <option value="{{ $evaluator->id }}">{{ $evaluator->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-user-check me-2"></i>Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>

<input type="hidden" id="jsCsrfToken" value="{{ csrf_token() }}">
<input type="hidden" id="jsGenerateBarcodesUrl" value="{{ route('coe.packets.barcodes.generate') }}">
<input type="hidden" id="jsPrintLabelsUrl" value="{{ route('coe.packets.barcodes.print') }}">

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.getElementById('jsCsrfToken').value;
    var generateUrl = document.getElementById('jsGenerateBarcodesUrl').value;
    var printUrl = document.getElementById('jsPrintLabelsUrl').value;

    // Assign evaluator modal handler
    document.querySelectorAll('.assign-evaluator-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.getElementById('assignPacketId').value = this.dataset.packetId;
        document.getElementById('assignPacketNumber').value = this.dataset.packetNumber;
      });
    });

    // Select all checkbox
    var selectAll = document.getElementById('selectAllPackets');
    if (selectAll) {
      selectAll.addEventListener('change', function() {
        document.querySelectorAll('.packet-checkbox').forEach(function(cb) {
          cb.checked = selectAll.checked;
        });
        updateBulkButtons();
      });
    }

    // Individual checkbox change
    document.querySelectorAll('.packet-checkbox').forEach(function(cb) {
      cb.addEventListener('change', updateBulkButtons);
    });

    function getSelectedIds() {
      var ids = [];
      document.querySelectorAll('.packet-checkbox:checked').forEach(function(cb) {
        ids.push(parseInt(cb.value));
      });
      return ids;
    }

    function updateBulkButtons() {
      var selected = getSelectedIds();
      var genBtn = document.getElementById('generateBarcodesBtn');
      var printBtn = document.getElementById('printLabelsBtn');
      genBtn.disabled = selected.length === 0;
      printBtn.disabled = selected.length === 0;
    }

    // Generate barcodes
    document.getElementById('generateBarcodesBtn').addEventListener('click', function() {
      var ids = getSelectedIds();
      if (ids.length === 0) return;

      if (!confirm('Generate barcodes for ' + ids.length + ' selected packet(s)?')) return;

      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';

      // Submit as form POST
      var form = document.createElement('form');
      form.method = 'POST';
      form.action = generateUrl;

      var tokenInput = document.createElement('input');
      tokenInput.type = 'hidden';
      tokenInput.name = '_token';
      tokenInput.value = csrfToken;
      form.appendChild(tokenInput);

      ids.forEach(function(id) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'packet_ids[]';
        input.value = id;
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
    });

    // Print labels
    document.getElementById('printLabelsBtn').addEventListener('click', function() {
      var ids = getSelectedIds();
      if (ids.length === 0) return;

      var params = ids.map(function(id) {
        return 'packet_ids[]=' + id;
      }).join('&');
      window.open(printUrl + '?' + params, '_blank');
    });
  });
</script>

@include('includes.footer')
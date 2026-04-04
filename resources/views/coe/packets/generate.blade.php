@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Generate Packets</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.packets.index') }}">Packets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Generate</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-cogs me-2"></i>Generate Packets</h3>
                  <p class="text-white-50 mb-0">Auto-generate answer script packets from present students after attendance</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-list me-2"></i>View All Packets
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

      <!-- Session & Subject Selection -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-filter me-2 text-primary"></i>Select Session & Subject</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('coe.packets.generate') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Exam Session <span class="text-danger">*</span></label>
              <select name="exam_session_id" class="form-select" required>
                <option value="">-- Select Session --</option>
                @foreach($examSessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name ?? 'Session #'.$session->id }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
              <select name="erp_subject_id" class="form-select" required>
                <option value="">-- Select Subject --</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->erp_subject_id }}" {{ request('erp_subject_id') == $subject->erp_subject_id ? 'selected' : '' }}>
                  {{ $subject->subject_code }} - {{ $subject->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Load Students</button>
            </div>
          </form>
        </div>
      </div>

      @if($selectedSession && $selectedSubject)
      <!-- Info Banner -->
      <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="row">
          <div class="col-md-3">
            <strong><i class="fas fa-calendar me-1"></i>Session:</strong> {{ $selectedSession->name ?? 'Session #'.$selectedSession->id }}
          </div>
          <div class="col-md-3">
            <strong><i class="fas fa-book me-1"></i>Subject:</strong> {{ $selectedSubject->subject_code }} - {{ $selectedSubject->name }}
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-users me-1"></i>Present:</strong> <span class="badge bg-success fs-6">{{ $presentStudents->count() }}</span>
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-check-double me-1"></i>Packeted:</strong> <span class="badge bg-secondary fs-6">{{ $alreadyPacketed->count() }}</span>
          </div>
          <div class="col-md-2">
            <strong><i class="fas fa-exclamation-circle me-1"></i>Remaining:</strong>
            <span class="badge bg-warning text-dark fs-6">{{ $presentStudents->count() - $alreadyPacketed->count() }}</span>
          </div>
        </div>
      </div>

      @if($presentStudents->count() === 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center py-5 text-muted">
          <i class="fas fa-clipboard-list fa-3x mb-3"></i>
          <h5>No Present Students Found</h5>
          <p>No attendance records found for this session/subject. Please mark attendance first before generating packets.</p>
        </div>
      </div>
      @else

      <!-- Generate Packets Form -->
      @if($presentStudents->count() - $alreadyPacketed->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-magic me-2 text-primary"></i>Generate New Packets</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('coe.packets.store') }}" id="generateForm">
            @csrf
            <input type="hidden" name="exam_session_id" value="{{ $selectedSession->id }}">
            <input type="hidden" name="erp_subject_id" value="{{ $selectedSubject->erp_subject_id }}">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Scripts per Packet <span class="text-danger">*</span></label>
                <input type="number" name="packet_size" class="form-control" min="20" max="30" value="25" required>
                <small class="text-muted">Between 20 and 30 scripts per packet</small>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Estimated Packets</label>
                <input type="text" class="form-control" id="estimatedPackets" readonly
                  value="{{ ceil(($presentStudents->count() - $alreadyPacketed->count()) / 25) }}">
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Generate packets for {{ $presentStudents->count() - $alreadyPacketed->count() }} remaining students?')">
                  <i class="fas fa-box-open me-2"></i>Generate Packets
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
      @else
      <div class="alert alert-success border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i>
        <strong>All Done!</strong> All present students have been assigned to packets.
      </div>
      @endif

      <!-- Present Students (not yet packeted) -->
      @php
      $unpacketed = $presentStudents->filter(function($s) use ($alreadyPacketed) {
      return !$alreadyPacketed->contains($s->id);
      });
      @endphp
      @if($unpacketed->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-user-clock me-2 text-warning"></i>Unassigned Present Students ({{ $unpacketed->count() }})</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Roll No</th>
                  <th>Student Name</th>
                  <th>Register No</th>
                </tr>
              </thead>
              <tbody>
                @foreach($unpacketed as $index => $student)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><span class="badge bg-light text-dark">{{ $student->roll_no ?? 'N/A' }}</span></td>
                  <td class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</td>
                  <td>{{ $student->register_no ?? 'N/A' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      <!-- Existing Packets for this Session+Subject -->
      @if($existingPackets->count() > 0)
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-box me-2 text-success"></i>Existing Packets ({{ $existingPackets->count() }})</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Packet Number</th>
                  <th>Total Scripts</th>
                  <th>Status</th>
                  <th>Evaluator</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($existingPackets as $index => $packet)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><span class="badge bg-dark fs-6">{{ $packet->packet_number }}</span></td>
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
                  <td>
                    <a href="{{ route('coe.packets.show', $packet->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="fas fa-eye me-1"></i>View
                    </a>
                    @if($packet->status === 'generated')
                    <button type="button" class="btn btn-sm btn-outline-primary assign-evaluator-btn"
                      data-packet-id="{{ $packet->id }}"
                      data-packet-number="{{ $packet->packet_number }}"
                      data-bs-toggle="modal" data-bs-target="#assignEvaluatorModal"
                      title="Assign Evaluator">
                      <i class="fas fa-user-plus me-1"></i>Assign
                    </button>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      @endif
      @endif

      <!-- Hidden input for JS reference -->
      @if($selectedSession && $selectedSubject)
      <input type="hidden" id="jsRemainingStudents" value="{{ ($presentStudents->count() ?? 0) - ($alreadyPacketed->count() ?? 0) }}">
      @endif
    </div>
  </main>
</div>

<!-- Assign Evaluator Modal -->
@if(isset($selectedSession) && isset($selectedSubject))
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
@endif

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Update estimated packets on packet size change
    var packetSizeInput = document.querySelector('input[name="packet_size"]');
    var estimatedEl = document.getElementById('estimatedPackets');
    var remainingEl = document.getElementById('jsRemainingStudents');
    var remaining = remainingEl ? parseInt(remainingEl.value) : 0;

    if (packetSizeInput && estimatedEl) {
      packetSizeInput.addEventListener('input', function() {
        var size = parseInt(this.value) || 25;
        if (size < 20) size = 20;
        if (size > 30) size = 30;
        estimatedEl.value = Math.ceil(remaining / size);
      });
    }

    // Assign evaluator modal handler
    document.querySelectorAll('.assign-evaluator-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.getElementById('assignPacketId').value = this.dataset.packetId;
        document.getElementById('assignPacketNumber').value = this.dataset.packetNumber;
      });
    });
  });
</script>

@include('includes.footer')
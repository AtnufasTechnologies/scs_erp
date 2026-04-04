@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Packet Detail</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.packets.index') }}">Packets</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $packet->packet_number }}</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-box me-2"></i>Packet: {{ $packet->packet_number }}</h3>
                  <p class="text-white-50 mb-0">Detailed view of packet contents and assignment</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.index') }}" class="btn btn-light me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                  <a href="{{ route('coe.packets.generate', ['exam_session_id' => $packet->exam_session_id, 'erp_subject_id' => $packet->erp_subject_id]) }}" class="btn btn-outline-light">
                    <i class="fas fa-cogs me-2"></i>Generate Page
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

      <!-- Packet Info -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-info-circle me-2"></i>Packet Information</h6>
              <table class="table table-borderless">
                <tr>
                  <th class="text-muted" width="40%">Packet Number</th>
                  <td><span class="badge bg-dark fs-5">{{ $packet->packet_number }}</span></td>
                </tr>
                <tr>
                  <th class="text-muted">Subject</th>
                  <td>
                    @if($packet->subjectMaster)
                    {{ $packet->subjectMaster->subject_code }} - {{ $packet->subjectMaster->name }}
                    @else
                    Subject #{{ $packet->erp_subject_id }}
                    @endif
                  </td>
                </tr>
                <tr>
                  <th class="text-muted">Exam Session</th>
                  <td>{{ $packet->examSession->name ?? 'Session #'.$packet->exam_session_id }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Total Scripts</th>
                  <td><span class="badge bg-primary fs-6">{{ $packet->total_scripts }}</span></td>
                </tr>
                <tr>
                  <th class="text-muted">Status</th>
                  <td>
                    @if($packet->status === 'generated')
                    <span class="badge bg-warning text-dark fs-6">Generated</span>
                    @elseif($packet->status === 'assigned')
                    <span class="badge bg-info fs-6">Assigned</span>
                    @elseif($packet->status === 'evaluating')
                    <span class="badge bg-primary fs-6">Evaluating</span>
                    @elseif($packet->status === 'completed')
                    <span class="badge bg-success fs-6">Completed</span>
                    @endif
                  </td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-user-cog me-2"></i>Assignment & Metadata</h6>
              <table class="table table-borderless">
                <tr>
                  <th class="text-muted" width="40%">Evaluator</th>
                  <td>
                    @if($packet->evaluator)
                    <span class="fw-semibold">{{ $packet->evaluator->name }}</span>
                    @else
                    <span class="text-muted">Not assigned</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th class="text-muted">Assigned At</th>
                  <td>{{ $packet->assigned_at ? $packet->assigned_at->format('d M Y, h:i A') : '—' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Completed At</th>
                  <td>{{ $packet->completed_at ? $packet->completed_at->format('d M Y, h:i A') : '—' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Generated By</th>
                  <td>{{ $packet->generatedByUser->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Generated On</th>
                  <td>{{ $packet->created_at ? $packet->created_at->format('d M Y, h:i A') : '—' }}</td>
                </tr>
              </table>
            </div>
          </div>

          @if($packet->remarks)
          <hr>
          <div class="row">
            <div class="col-12">
              <h6 class="text-muted text-uppercase mb-2"><i class="fas fa-sticky-note me-2"></i>Remarks</h6>
              <p>{{ $packet->remarks }}</p>
            </div>
          </div>
          @endif

          <!-- Quick Actions -->
          <hr>
          <div class="d-flex gap-2">
            @if($packet->status === 'generated')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignEvaluatorModal">
              <i class="fas fa-user-plus me-2"></i>Assign Evaluator
            </button>
            @endif
            @if($packet->status === 'assigned')
            <form method="POST" action="{{ route('coe.packets.update-status') }}" class="d-inline">
              @csrf
              <input type="hidden" name="packet_id" value="{{ $packet->id }}">
              <input type="hidden" name="status" value="evaluating">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-pen me-2"></i>Mark as Evaluating
              </button>
            </form>
            @endif
            @if(in_array($packet->status, ['assigned', 'evaluating']))
            <form method="POST" action="{{ route('coe.packets.update-status') }}" class="d-inline">
              @csrf
              <input type="hidden" name="packet_id" value="{{ $packet->id }}">
              <input type="hidden" name="status" value="completed">
              <button type="submit" class="btn btn-success" onclick="return confirm('Mark this packet as completed?')">
                <i class="fas fa-check-circle me-2"></i>Mark Completed
              </button>
            </form>
            @endif
          </div>
        </div>
      </div>

      <!-- Students in this Packet -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-users me-2 text-primary"></i>Students in Packet ({{ $packet->students->count() }})</h5>
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
                  <th>Dummy Number</th>
                </tr>
              </thead>
              <tbody>
                @forelse($packet->students as $index => $ps)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><span class="badge bg-light text-dark">{{ $ps->student->roll_no ?? 'N/A' }}</span></td>
                  <td class="fw-semibold">{{ $ps->student->first_name ?? '' }} {{ $ps->student->last_name ?? '' }}</td>
                  <td>{{ $ps->student->register_no ?? 'N/A' }}</td>
                  <td>
                    @if($ps->dummy_number)
                    <span class="badge bg-secondary">{{ $ps->dummy_number }}</span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No students found in this packet.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- Assign Evaluator Modal -->
@if($packet->status === 'generated')
<div class="modal fade" id="assignEvaluatorModal" tabindex="-1" aria-labelledby="assignEvaluatorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('coe.packets.assign-evaluator') }}">
        @csrf
        <input type="hidden" name="packet_id" value="{{ $packet->id }}">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="assignEvaluatorModalLabel"><i class="fas fa-user-plus me-2"></i>Assign Evaluator</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Packet</label>
            <input type="text" class="form-control" value="{{ $packet->packet_number }}" readonly>
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

@include('includes.footer')
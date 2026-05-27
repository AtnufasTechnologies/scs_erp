@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FDP Programs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.index') }}">FDP List</a></li>
            <li class="breadcrumb-item active">{{ $fdpProgram->program_code }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="{{ route('hr.fdp.edit', $fdpProgram->id) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('hr.fdp.add-participant', $fdpProgram->id) }}" class="btn btn-success btn-sm">
          <i class="fas fa-user-plus me-1"></i>Add Participant
        </a>
        <a href="{{ route('hr.fdp.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>



    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
      <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-lg border-0 h-100 stat-card position-relative overflow-hidden">
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
            <div class="stat-icon mb-2 bg-gradient-info text-white d-flex align-items-center justify-content-center">
              <i class="fas fa-users "></i>
            </div>
            <div class="text-center">
              <div class="fw-bold text-secondary small mb-1">Registered</div>
              <div class="display-6 fw-semibold text-dark">{{ $stats['total_registered'] }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-lg border-0 h-100 stat-card position-relative overflow-hidden">
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
            <div class="stat-icon mb-2 bg-gradient-success text-white d-flex align-items-center justify-content-center">
              <i class="fas fa-check-circle "></i>
            </div>
            <div class="text-center">
              <div class="fw-bold text-secondary small mb-1">Approved</div>
              <div class="display-6 fw-semibold text-dark">{{ $stats['approved'] }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-lg border-0 h-100 stat-card position-relative overflow-hidden">
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
            <div class="stat-icon mb-2 bg-gradient-purple text-white d-flex align-items-center justify-content-center">
              <i class="fas fa-graduation-cap "></i>
            </div>
            <div class="text-center">
              <div class="fw-bold text-secondary small mb-1">Completed</div>
              <div class="display-6 fw-semibold text-dark">{{ $stats['completed'] }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-lg border-0 h-100 stat-card position-relative overflow-hidden">
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
            <div class="stat-icon mb-2 bg-gradient-danger text-white d-flex align-items-center justify-content-center">
              <i class="fas fa-certificate "></i>
            </div>
            <div class="text-center">
              <div class="fw-bold text-secondary small mb-1">Certificates Issued</div>
              <div class="display-6 fw-semibold text-dark">{{ $stats['certificates_issued'] }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <style>
      .stat-card {
        transition: box-shadow 0.2s;
        border-radius: 1.25rem;
        min-height: 80px;
      }

      .stat-card:hover {
        box-shadow: 0 0 0 0.25rem #17a2b8, 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
      }

      .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        font-size: 2rem;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.10);
        margin-bottom: 0.5rem;
      }

      .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #5bc0de 100%) !important;
      }

      .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #51e67d 100%) !important;
      }

      .bg-gradient-purple {
        background: linear-gradient(135deg, #6f42c1 0%, #b07fff 100%) !important;
      }

      .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #ff758c 100%) !important;
      }
    </style>

    <div class="row">
      {{-- Program Details --}}
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Program Details</h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Program Code:</strong>
                <p class="text-muted">{{ $fdpProgram->program_code }}</p>
              </div>
              <div class="col-md-6">
                <strong>Program Title:</strong>
                <p class="text-muted">{{ $fdpProgram->program_title }}</p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Program Type:</strong>
                <p><span class="badge bg-info">{{ ucfirst($fdpProgram->program_type) }}</span></p>
              </div>
              <div class="col-md-6">
                <strong>Target Audience:</strong>
                <p><span class="badge bg-secondary">{{ ucfirst($fdpProgram->target_audience) }}</span></p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <strong>Start Date:</strong>
                <p class="text-muted">{{ $fdpProgram->start_date ? \Carbon\Carbon::parse($fdpProgram->start_date)->format('d M Y') : 'N/A' }}</p>
              </div>
              <div class="col-md-4">
                <strong>End Date:</strong>
                <p class="text-muted">{{ $fdpProgram->end_date ? \Carbon\Carbon::parse($fdpProgram->end_date)->format('d M Y') : 'N/A' }}</p>
              </div>
              <div class="col-md-4">
                <strong>Duration:</strong>
                <p class="text-muted">{{ $fdpProgram->duration_days ?? 'N/A' }} day(s)</p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Organizer:</strong>
                <p class="text-muted">{{ $fdpProgram->organizer ?? 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <strong>Venue:</strong>
                <p class="text-muted">{{ $fdpProgram->venue ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <strong>Program Fee:</strong>
                <p class="text-muted">{{ $fdpProgram->program_fee ? '₹' . number_format($fdpProgram->program_fee, 2) : 'Free' }}</p>
              </div>
              <div class="col-md-4">
                <strong>Max Participants:</strong>
                <p class="text-muted">{{ $fdpProgram->max_participants ?? 'Unlimited' }}</p>
              </div>
              <div class="col-md-4">
                <strong>Status:</strong>
                <p>
                  @php $statusColors = ['draft'=>'secondary','open'=>'primary','ongoing'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                  <span class="badge bg-{{ $statusColors[$fdpProgram->status] ?? 'secondary' }}">{{ ucfirst($fdpProgram->status) }}</span>
                </p>
              </div>
            </div>
            @if($fdpProgram->description)
            <div class="mb-3">
              <strong>Description:</strong>
              <p class="text-muted">{{ $fdpProgram->description }}</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Coordinator Details --}}
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-user-tie me-1"></i>Coordinator</h6>
          </div>
          <div class="card-body">
            <p><strong>Name:</strong><br><span class="text-muted">{{ $fdpProgram->coordinator_name ?? 'N/A' }}</span></p>
            <p><strong>Contact:</strong><br><span class="text-muted">{{ $fdpProgram->coordinator_contact ?? 'N/A' }}</span></p>
            @if($fdpProgram->attachment)
            <a href="{{ $fdpProgram->attachment }}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
              <i class="fas fa-paperclip me-1"></i>View Attachment
            </a>
            @endif
          </div>
        </div>
        @if($fdpProgram->remarks)
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-sticky-note me-1"></i>Remarks</h6>
          </div>
          <div class="card-body">
            <p class="text-muted mb-0">{{ $fdpProgram->remarks }}</p>
          </div>
        </div>
        @endif
        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i>Meta</h6>
          </div>
          <div class="card-body">
            <small class="text-muted">
              Created by: {{ $fdpProgram->creator->name ?? 'N/A' }}<br>
              Created at: {{ $fdpProgram->created_at ? $fdpProgram->created_at->format('d M Y') : 'N/A' }}
            </small>
          </div>
        </div>
      </div>
    </div>

    {{-- Participants Table --}}
    <div class="card">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-1"></i>Participants ({{ $stats['total_registered'] }})</h5>
        <a href="{{ route('hr.fdp.add-participant', $fdpProgram->id) }}" class="btn btn-sm btn-success">
          <i class="fas fa-user-plus me-1"></i>Add Participant
        </a>
      </div>
      <div class="card-body">
        @if($fdpProgram->participants->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Faculty / Staff</th>
                <th>Type</th>
                <th>Registration Date</th>
                <th>Status</th>
                <th>Attendance</th>
                <th>Certificate</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($fdpProgram->participants as $i => $participant)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                  @if($participant->faculty)
                  {{ $participant->faculty->FIRST_NAME }} {{ $participant->faculty->LAST_NAME }}<br>
                  <small class="text-muted">{{ $participant->faculty->FACULTY_CODE }}</small>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td><span class="badge bg-secondary">{{ ucfirst($participant->participant_type) }}</span></td>
                <td>{{ $participant->registration_date ? \Carbon\Carbon::parse($participant->registration_date)->format('d M Y') : 'N/A' }}</td>
                <td>
                  @php $pColors = ['registered'=>'warning','approved'=>'info','completed'=>'success','cancelled'=>'danger']; @endphp
                  <span class="badge bg-{{ $pColors[$participant->status] ?? 'secondary' }}">{{ ucfirst($participant->status) }}</span>
                </td>
                <td>
                  @if($participant->attendance_status)
                  <span class="badge bg-{{ $participant->attendance_status == 'present' ? 'success' : ($participant->attendance_status == 'partial' ? 'warning' : 'danger') }}">
                    {{ ucfirst($participant->attendance_status) }}
                  </span>
                  @if($participant->days_attended) ({{ $participant->days_attended }} days) @endif
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($participant->certificate_issued)
                  <i class="fas fa-check-circle text-success"></i>
                  @if($participant->certificate_number)
                  <small class="text-muted ms-1">{{ $participant->certificate_number }}</small>
                  @endif
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-1">
                    @if($participant->status == 'registered')
                    <form action="{{ route('hr.fdp.participants.approve', [$fdpProgram->id, $participant->id]) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-xs btn-success" title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                    @endif
                    @if($participant->status == 'approved')
                    <button class="btn btn-xs btn-primary" title="Mark Complete"
                      data-bs-toggle="modal" data-bs-target="#completeModal{{ $participant->id }}">
                      <i class="fas fa-graduation-cap"></i>
                    </button>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
          <i class="fas fa-users fa-2x mb-2 d-block"></i>No participants registered yet.
        </div>
        @endif
      </div>
    </div>

    {{-- Complete Participant Modals --}}
    @foreach($fdpProgram->participants as $participant)
    @if($participant->status == 'approved')
    <div class="modal fade" id="completeModal{{ $participant->id }}" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="{{ route('hr.fdp.participants.complete', [$fdpProgram->id, $participant->id]) }}" method="POST">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Mark as Completed</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted">Participant: <strong>{{ $participant->faculty->FIRST_NAME ?? '' }} {{ $participant->faculty->LAST_NAME ?? '' }}</strong></p>
              <div class="mb-3">
                <label class="form-label">Attendance Status <span class="text-danger">*</span></label>
                <select name="attendance_status" class="form-select" required>
                  <option value="present">Present</option>
                  <option value="partial">Partial</option>
                  <option value="absent">Absent</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Days Attended <span class="text-danger">*</span></label>
                <input type="number" name="days_attended" class="form-control" min="0" max="{{ $fdpProgram->duration_days }}" value="{{ $fdpProgram->duration_days }}" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Certificate Number</label>
                <input type="text" name="certificate_number" class="form-control" maxlength="50">
              </div>
              <div class="mb-3">
                <label class="form-label">Rating (1-5)</label>
                <input type="number" name="rating" class="form-control" min="1" max="5">
              </div>
              <div class="mb-3">
                <label class="form-label">Feedback</label>
                <textarea name="feedback" class="form-control" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
    @endforeach

  </main>
</div>
@include('includes.footer')
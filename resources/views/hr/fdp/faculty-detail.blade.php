@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">FDP Faculty Tracker</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.faculty-tracker') }}">Faculty Tracker</a></li>
            <li class="breadcrumb-item active">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.fdp.faculty-tracker') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
      </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="stat-icon-small mb-2 bg-gradient-info text-white d-inline-flex align-items-center justify-content-center rounded-circle">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h4 class="mb-0">{{ $stats['total_programs'] }}</h4>
            <small class="text-muted">Total Programs</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="stat-icon-small mb-2 bg-gradient-success text-white d-inline-flex align-items-center justify-content-center rounded-circle">
              <i class="fas fa-check-circle"></i>
            </div>
            <h4 class="mb-0">{{ $stats['completed'] }}</h4>
            <small class="text-muted">Completed</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="stat-icon-small mb-2 bg-gradient-warning text-white d-inline-flex align-items-center justify-content-center rounded-circle">
              <i class="fas fa-spinner"></i>
            </div>
            <h4 class="mb-0">{{ $stats['ongoing'] }}</h4>
            <small class="text-muted">Ongoing</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="stat-icon-small mb-2 bg-gradient-danger text-white d-inline-flex align-items-center justify-content-center rounded-circle">
              <i class="fas fa-certificate"></i>
            </div>
            <h4 class="mb-0">{{ $stats['certificates_earned'] }}</h4>
            <small class="text-muted">Certificates</small>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body text-center">
            <div class="mb-3">
              <i class="fas fa-user-circle fa-4x text-secondary"></i>
            </div>
            <h4 class="mb-1">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</h4>
            <div class="mb-2 text-muted">{{ $faculty->FACULTY_CODE ?? $faculty->USER_CODE }}</div>
            <div class="mb-2"><span class="badge bg-info">{{ ucfirst($faculty->DEPARTMENT ?? 'N/A') }}</span></div>
            <div class="mb-2"><i class="fas fa-envelope me-1"></i>{{ $faculty->EMAIL ?? 'N/A' }}</div>
            <div class="mb-2"><i class="fas fa-phone me-1"></i>{{ $faculty->MOBILE_NO ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card h-100">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>FDP Participation History</h5>
          </div>
          <div class="card-body">
            @if(!empty($participations) && count($participations) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Program</th>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Certificate</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($participations as $i => $p)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                      <div class="fw-semibold">{{ $p->fdpProgram->program_title ?? '-' }}</div>
                      <div class="text-muted small">{{ $p->fdpProgram->program_code ?? '' }}</div>
                    </td>
                    <td><span class="badge bg-secondary">{{ ucfirst($p->participant_type) }}</span></td>
                    <td>
                      <span class="text-muted small">
                        {{ $p->fdpProgram->start_date ? \Carbon\Carbon::parse($p->fdpProgram->start_date)->format('d M Y') : 'N/A' }}
                        -
                        {{ $p->fdpProgram->end_date ? \Carbon\Carbon::parse($p->fdpProgram->end_date)->format('d M Y') : 'N/A' }}
                      </span>
                    </td>
                    <td>
                      @php $pColors = ['registered'=>'warning','approved'=>'info','completed'=>'success','cancelled'=>'danger']; @endphp
                      <span class="badge bg-{{ $pColors[$p->status] ?? 'secondary' }}">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td>
                      @if($p->certificate_issued)
                      <i class="fas fa-check-circle text-success"></i>
                      @if($p->certificate_number)
                      <small class="text-muted ms-1">{{ $p->certificate_number }}</small>
                      @endif
                      @else
                      <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @else
            <div class="text-center text-muted py-4">
              <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>No FDP participations found.
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .stat-icon-small {
    width: 48px;
    height: 48px;
  }

  .bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #5bc0de 100%) !important;
  }

  .bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #51e67d 100%) !important;
  }

  .bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffd54f 100%) !important;
  }

  .bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545 0%, #ff758c 100%) !important;
  }
</style>

@include('includes.footer')
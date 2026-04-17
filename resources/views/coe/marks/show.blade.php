@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Marks Detail</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.marks.index') }}">Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Marks Entry Detail</h3>
                  <p class="text-white-50 mb-0">Detailed view of a marks entry record</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.marks.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-user me-2"></i>Student Information</h6>
              <table class="table table-borderless">
                <tr>
                  <th class="text-muted" width="40%">Name</th>
                  <td class="fw-semibold">{{ $mark->student->first_name ?? '' }} {{ $mark->student->last_name ?? '' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Roll No</th>
                  <td><span class="badge bg-light text-dark">{{ $mark->student->roll_no ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                  <th class="text-muted">Register No</th>
                  <td>{{ $mark->student->register_no ?? 'N/A' }}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-book me-2"></i>Exam Information</h6>
              <table class="table table-borderless">
                <tr>
                  <th class="text-muted" width="40%">Session</th>
                  <td>{{ $mark->examSession->name ?? 'Session #'.$mark->exam_session_id }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Subject</th>
                  <td>
                    @if($mark->subjectMaster)
                    {{ $mark->subjectMaster->subject_code }} - {{ $mark->subjectMaster->name }}
                    @else
                    Subject #{{ $mark->erp_subject_id }}
                    @endif
                  </td>
                </tr>
                <tr>
                  <th class="text-muted">Marks</th>
                  <td><span class="badge bg-primary fs-5">{{ $mark->marks }}</span></td>
                </tr>
              </table>
            </div>
          </div>
          <hr>
          <div class="row g-4">
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-shield-alt me-2"></i>Entry Metadata</h6>
              <table class="table table-borderless">
                <tr>
                  <th class="text-muted" width="40%">Entered By</th>
                  <td>{{ $mark->enteredByUser->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">MAC Address</th>
                  <td><code>{{ $mark->mac_address ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                  <th class="text-muted">Entered At</th>
                  <td>{{ $mark->entered_at ? \Carbon\Carbon::parse($mark->entered_at)->format('d M Y, h:i:s A') : '-' }}</td>
                </tr>
                <tr>
                  <th class="text-muted">Last Updated</th>
                  <td>{{ $mark->updated_at ? $mark->updated_at->format('d M Y, h:i:s A') : '-' }}</td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Audit Trail for this entry -->
          @if($auditLogs && $auditLogs->count() > 0)
          <hr>
          <div class="row">
            <div class="col-12">
              <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-history me-2"></i>Change History (Audit Trail)</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Old Marks</th>
                      <th>New Marks</th>
                      <th>Action</th>
                      <th>Changed By</th>
                      <th>MAC Address</th>
                      <th>Remarks</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($auditLogs as $index => $log)
                    <tr class="{{ $log->action === 'coe_override' ? 'table-warning' : '' }}">
                      <td>{{ $index + 1 }}</td>
                      <td>
                        @if($log->old_marks !== null)
                        <span class="badge bg-secondary">{{ $log->old_marks }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                      </td>
                      <td><span class="badge bg-primary">{{ $log->new_marks }}</span></td>
                      <td>
                        @if($log->action === 'created')
                        <span class="badge bg-success">Created</span>
                        @elseif($log->action === 'updated')
                        <span class="badge bg-info">Updated</span>
                        @elseif($log->action === 'coe_override')
                        <span class="badge bg-warning text-dark"><i class="fas fa-user-shield me-1"></i>COE Override</span>
                        @else
                        <span class="badge bg-secondary">{{ $log->action }}</span>
                        @endif
                      </td>
                      <td>{{ $log->changedByUser->name ?? 'N/A' }}</td>
                      <td><code>{{ $log->mac_address ?? 'N/A' }}</code></td>
                      <td>{{ $log->remarks ?? '—' }}</td>
                      <td>{{ $log->created_at ? $log->created_at->format('d M Y, h:i:s A') : '-' }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @endif
        </div>
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
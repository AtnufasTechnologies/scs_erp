@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Promotions</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Promotions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promotion Details</li>
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
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-user-graduate me-2"></i>Promotion Details</h3>
                  <p class="text-muted mb-0">
                    {{ $promotion->student->enrollment_no ?? 'N/A' }} —
                    Semester {{ $promotion->from_semester }} → {{ $promotion->to_semester }}
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Promotion Info -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Promotion Information</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="text-muted fw-semibold" style="width:40%">Student</td>
                  <td class="fw-bold">{{ $promotion->student->enrollment_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Exam Session</td>
                  <td>{{ $promotion->examSession->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Academic Year</td>
                  <td>{{ $promotion->examSession->academic_year ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">From Semester</td>
                  <td><span class="badge bg-secondary">Semester {{ $promotion->from_semester }}</span></td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">To Semester</td>
                  <td><span class="badge bg-primary">Semester {{ $promotion->to_semester }}</span></td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Status</td>
                  <td>
                    @if($promotion->promotion_status === 'promoted')
                    <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i>Promoted</span>
                    @elseif($promotion->promotion_status === 'promoted_with_backlogs')
                    <span class="badge bg-warning text-dark fs-6"><i class="fas fa-exclamation-triangle me-1"></i>Promoted with Backlogs</span>
                    @elseif($promotion->promotion_status === 'withheld')
                    <span class="badge bg-dark fs-6"><i class="fas fa-pause me-1"></i>Withheld</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Promoted On</td>
                  <td>{{ $promotion->created_at->format('d M Y, h:i A') }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Credit Summary -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-success"></i>Credit Summary</h5>
            </div>
            <div class="card-body">
              <div class="row text-center mb-4">
                <div class="col-4">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold text-success">{{ $promotion->earned_credits ?? 0 }}</div>
                    <div class="text-muted small">Earned Credits</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold text-info">{{ $promotion->transferred_credits ?? 0 }}</div>
                    <div class="text-muted small">Transferred Credits</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded p-3">
                    <div class="fs-2 fw-bold text-primary">{{ ($promotion->earned_credits ?? 0) + ($promotion->transferred_credits ?? 0) }}</div>
                    <div class="text-muted small">Total Credits</div>
                  </div>
                </div>
              </div>

              @if($summary)
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="text-muted fw-semibold" style="width:50%">Total Earned (All Semesters)</td>
                  <td class="fw-bold text-success">{{ $summary['total_earned_credits'] ?? 0 }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Total Transferred</td>
                  <td class="fw-bold text-info">{{ $summary['total_transferred_credits'] ?? 0 }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Pending Backlogs</td>
                  <td class="fw-bold text-danger">{{ $summary['pending_backlogs'] ?? 0 }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-semibold">Cleared Backlogs</td>
                  <td class="fw-bold text-success">{{ $summary['cleared_backlogs'] ?? 0 }}</td>
                </tr>
              </table>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Backlog Subjects at Promotion -->
      @if($promotion->backlog_subjects && count($promotion->backlog_subjects) > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-circle me-2 text-warning"></i>Backlog Subjects at Promotion</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th>Credits</th>
                  <th>Attempt</th>
                </tr>
              </thead>
              <tbody>
                @foreach($promotion->backlog_subjects as $index => $bs)
                <tr>
                  <td class="ps-4">{{ $index + 1 }}</td>
                  <td><code>{{ $bs['subject_code'] ?? 'N/A' }}</code></td>
                  <td>{{ $bs['subject_name'] ?? 'N/A' }}</td>
                  <td><span class="badge bg-info">{{ $bs['credits'] ?? 0 }}</span></td>
                  <td><span class="badge bg-secondary">Attempt {{ $bs['attempt'] ?? 1 }}</span></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      <!-- All Backlogs for Student -->
      @if($backlogs->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-redo me-2 text-danger"></i>All Backlogs ({{ $backlogs->count() }})</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Subject</th>
                  <th>Semester</th>
                  <th>Credits</th>
                  <th>Attempt</th>
                  <th>Max Attempts</th>
                  <th>Status</th>
                  <th>Cleared Details</th>
                </tr>
              </thead>
              <tbody>
                @foreach($backlogs as $index => $backlog)
                <tr>
                  <td class="ps-4">{{ $index + 1 }}</td>
                  <td>
                    <div class="fw-semibold">{{ $backlog->subject->name ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $backlog->subject->code ?? '' }}</small>
                  </td>
                  <td><span class="badge bg-secondary">Sem {{ $backlog->semester ?? 'N/A' }}</span></td>
                  <td><span class="badge bg-info">{{ $backlog->credits ?? 0 }}</span></td>
                  <td><span class="badge bg-warning text-dark">{{ $backlog->attempt_count }}</span></td>
                  <td>{{ $backlog->max_attempts ?? '∞' }}</td>
                  <td>
                    @if($backlog->status === 'pending')
                    <span class="badge bg-danger"><i class="fas fa-clock me-1"></i>Pending</span>
                    @elseif($backlog->status === 'cleared')
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Cleared</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($backlog->status) }}</span>
                    @endif
                  </td>
                  <td>
                    @if($backlog->status === 'cleared')
                    <div>
                      <small class="text-muted">Marks:</small> <span class="fw-bold">{{ $backlog->cleared_marks }}</span>
                      <small class="text-muted ms-2">Grade:</small> <span class="fw-bold">{{ $backlog->cleared_grade }}</span>
                    </div>
                    @else
                    <span class="text-muted">—</span>
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

      <!-- Promotion History -->
      @if($history->count() > 0)
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-info"></i>Promotion History</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Semester</th>
                  <th>Session</th>
                  <th>From → To</th>
                  <th>Status</th>
                  <th>Earned Credits</th>
                  <th>Transferred Credits</th>
                  <th>Backlogs</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($history as $record)
                <tr>
                  <td class="ps-4"><span class="badge bg-secondary">Sem {{ $record->semester }}</span></td>
                  <td>{{ $record->examSession->name ?? 'N/A' }}</td>
                  <td>{{ $record->from_semester }} → {{ $record->to_semester }}</td>
                  <td>
                    @if($record->promotion_status === 'promoted')
                    <span class="badge bg-success">Promoted</span>
                    @elseif($record->promotion_status === 'promoted_with_backlogs')
                    <span class="badge bg-warning text-dark">With Backlogs</span>
                    @elseif($record->promotion_status === 'withheld')
                    <span class="badge bg-dark">Withheld</span>
                    @endif
                  </td>
                  <td class="fw-bold text-success">{{ $record->earned_credits ?? 0 }}</td>
                  <td class="fw-bold text-info">{{ $record->transferred_credits ?? 0 }}</td>
                  <td>
                    @if($record->backlog_subjects && count($record->backlog_subjects) > 0)
                    <span class="badge bg-warning text-dark">{{ count($record->backlog_subjects) }}</span>
                    @else
                    <span class="badge bg-success">0</span>
                    @endif
                  </td>
                  <td>{{ $record->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')
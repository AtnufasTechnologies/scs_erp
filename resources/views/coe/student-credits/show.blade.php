@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Academic Credits</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.student-credits.index') }}">Student Credits</a></li>
            <li class="breadcrumb-item active" aria-current="page">Credit #{{ $credit->id }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <!-- Header Card -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2">
                    @if($credit->isEarned())
                    <i class="fas fa-graduation-cap me-2"></i>Earned Credit
                    @else
                    <i class="fas fa-exchange-alt me-2"></i>Transferred Credit
                    @endif
                    <span class="badge bg-{{ $credit->credit_type === 'earned' ? 'success' : 'info' }} fs-6 ms-2">
                      {{ ucfirst($credit->credit_type) }}
                    </span>
                  </h3>
                  <p class="text-muted mb-0">
                    Student: <strong>{{ $credit->student->enrollment_no ?? 'N/A' }}</strong>
                    | Semester: <strong>{{ $credit->semester ?? 'N/A' }}</strong>
                    | Credits: <strong>{{ number_format($credit->credits_earned, 1) }}</strong>
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.student-credits.edit', $credit->id) }}" class="btn btn-warning me-1">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>

                  @if($credit->isTransferred() && $credit->status === 'under_review')
                  <form method="POST" action="{{ route('admin.student-credits.verify', $credit->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success me-1" onclick="return confirm('Verify this transferred credit?')">
                      <i class="fas fa-check-circle me-1"></i>Verify
                    </button>
                  </form>
                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times-circle me-1"></i>Reject
                  </button>
                  @endif

                  @if($credit->student)
                  <a href="{{ route('admin.student-credits.transcript', $credit->exam_student_id) }}" class="btn btn-outline-dark ms-1">
                    <i class="fas fa-file-alt me-1"></i>Transcript
                  </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Credit Details -->
        <div class="col-md-7">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Credit Details</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tbody>
                  <tr>
                    <td class="text-muted" style="width:35%">Credit Type</td>
                    <td>
                      @if($credit->isEarned())
                      <span class="badge bg-success fs-6"><i class="fas fa-graduation-cap me-1"></i>Earned</span>
                      @else
                      <span class="badge bg-info fs-6"><i class="fas fa-exchange-alt me-1"></i>Transferred</span>
                      @endif
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Status</td>
                    <td>
                      @php
                      $sc = ['active' => 'success', 'under_review' => 'warning', 'verified' => 'primary', 'rejected' => 'danger'];
                      $sl = ['active' => 'Active', 'under_review' => 'Under Review', 'verified' => 'Verified', 'rejected' => 'Rejected'];
                      @endphp
                      <span class="badge bg-{{ $sc[$credit->status] ?? 'secondary' }} fs-6">{{ $sl[$credit->status] ?? $credit->status }}</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Semester</td>
                    <td class="fw-semibold">{{ $credit->semester ?? '—' }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Credits Earned</td>
                    <td class="fw-bold fs-5">{{ number_format($credit->credits_earned, 1) }}</td>
                  </tr>
                  @if($credit->subject)
                  <tr>
                    <td class="text-muted">Subject</td>
                    <td>
                      <span class="fw-semibold">{{ $credit->subject->subject_code }}</span> — {{ $credit->subject->name }}
                      <br><small class="text-muted">Max Credits: {{ $credit->subject->credits }} | Type: {{ $credit->subject->type ?? 'N/A' }}</small>
                    </td>
                  </tr>
                  @endif
                  @if($credit->grade)
                  <tr>
                    <td class="text-muted">Grade</td>
                    <td>
                      <span class="fw-bold fs-5">{{ $credit->grade }}</span>
                      @if($credit->grade_point)
                      <span class="text-muted ms-2">(GP: {{ number_format($credit->grade_point, 2) }})</span>
                      @endif
                    </td>
                  </tr>
                  @endif

                  @if($credit->isTransferred())
                  <tr>
                    <td colspan="2">
                      <hr class="my-1">
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Source Institution</td>
                    <td class="fw-semibold">{{ $credit->source_institution }}</td>
                  </tr>
                  @if($credit->source_subject_code || $credit->source_subject_name)
                  <tr>
                    <td class="text-muted">Source Subject</td>
                    <td>
                      @if($credit->source_subject_code)
                      <span class="fw-semibold">{{ $credit->source_subject_code }}</span> —
                      @endif
                      {{ $credit->source_subject_name }}
                    </td>
                  </tr>
                  @endif
                  @if($credit->transfer_date)
                  <tr>
                    <td class="text-muted">Transfer Date</td>
                    <td>{{ $credit->transfer_date->format('d M Y') }}</td>
                  </tr>
                  @endif
                  @if($credit->transfer_reference)
                  <tr>
                    <td class="text-muted">Reference No</td>
                    <td class="font-monospace">{{ $credit->transfer_reference }}</td>
                  </tr>
                  @endif
                  @endif

                  @if($credit->verified_by || $credit->verified_at)
                  <tr>
                    <td colspan="2">
                      <hr class="my-1">
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Verified By</td>
                    <td>{{ $credit->verifier->full_name ?? 'ID: ' . $credit->verified_by }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Verified At</td>
                    <td>{{ $credit->verified_at ? $credit->verified_at->format('d M Y H:i') : '—' }}</td>
                  </tr>
                  @endif

                  @if($credit->remarks)
                  <tr>
                    <td class="text-muted">Remarks</td>
                    <td>{{ $credit->remarks }}</td>
                  </tr>
                  @endif
                  <tr>
                    <td class="text-muted">Created</td>
                    <td>{{ $credit->created_at->format('d M Y H:i') }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Last Updated</td>
                    <td>{{ $credit->updated_at->format('d M Y H:i') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Student Credit Summary -->
        <div class="col-md-5">
          <!-- Summary Card -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-success me-2"></i>Student Credit Summary</h5>
            </div>
            <div class="card-body">
              <div class="row text-center mb-3">
                <div class="col-4">
                  <div class="border rounded p-2">
                    <div class="text-muted small">Earned</div>
                    <div class="fw-bold fs-4 text-success">{{ number_format($totalEarned, 1) }}</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded p-2">
                    <div class="text-muted small">Transferred</div>
                    <div class="fw-bold fs-4 text-info">{{ number_format($totalTransferred, 1) }}</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border rounded p-2 bg-light">
                    <div class="text-muted small">Total</div>
                    <div class="fw-bold fs-4">{{ number_format($grandTotal, 1) }}</div>
                  </div>
                </div>
              </div>

              <!-- Semester-wise breakdown -->
              @php
              $bySemester = $studentCredits->groupBy('semester');
              @endphp
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-3">Sem</th>
                      <th>Subject</th>
                      <th class="text-center">Cr</th>
                      <th>Type</th>
                      <th>Grade</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($bySemester->sortKeys() as $sem => $semCredits)
                    @foreach($semCredits as $idx => $sc)
                    <tr class="{{ $sc->id === $credit->id ? 'table-active' : '' }}">
                      @if($idx === 0)
                      <td class="ps-3 fw-bold" rowspan="{{ $semCredits->count() }}">{{ $sem ?? '—' }}</td>
                      @endif
                      <td>
                        @if($sc->subject)
                        <small>{{ $sc->subject->subject_code }}</small>
                        @elseif($sc->source_subject_code)
                        <small class="text-info">{{ $sc->source_subject_code }}</small>
                        @else
                        <small class="text-muted">{{ Str::limit($sc->source_subject_name, 15) }}</small>
                        @endif
                      </td>
                      <td class="text-center">{{ $sc->credits_earned }}</td>
                      <td>
                        <span class="badge bg-{{ $sc->credit_type === 'earned' ? 'success' : 'info' }} badge-sm">
                          {{ $sc->credit_type === 'earned' ? 'E' : 'T' }}
                        </span>
                      </td>
                      <td>{{ $sc->grade ?? '—' }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    @if($credit->isTransferred() && $credit->status === 'under_review')
    <div class="modal fade" id="rejectModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" action="{{ route('admin.student-credits.reject', $credit->id) }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-2"></i>Reject Transfer Credit</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                <textarea name="remarks" class="form-control" rows="3" required maxlength="500" placeholder="Provide reason for rejection..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-danger"><i class="fas fa-times-circle me-1"></i>Reject</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
  </main>
</div>

@include('includes.footer')
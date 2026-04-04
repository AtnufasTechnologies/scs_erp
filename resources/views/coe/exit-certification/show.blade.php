@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exit Certification</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exit-certification.index') }}">Exit Certification</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $record->certificate_no }}</li>
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

      @php
      $levelColors = ['certificate' => 'info', 'diploma' => 'primary', 'degree' => 'success', 'honors' => 'warning'];
      $levelIcons = ['certificate' => 'scroll', 'diploma' => 'award', 'degree' => 'graduation-cap', 'honors' => 'star'];
      @endphp

      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2">
                    <i class="fas fa-{{ $levelIcons[$record->exit_level] ?? 'certificate' }} me-2"></i>
                    {{ ucfirst($record->exit_level) }} Certification
                  </h3>
                  <p class="text-muted mb-0 font-monospace">{{ $record->certificate_no }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exit-certification.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Back
                  </a>
                  @if($record->status == 'pending')
                  <form method="POST" action="{{ route('admin.exit-certification.approve', $record->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this certification?')">
                      <i class="fas fa-check me-1"></i>Approve
                    </button>
                  </form>
                  @endif
                  @if($record->status == 'approved')
                  <form method="POST" action="{{ route('admin.exit-certification.issue', $record->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info" onclick="return confirm('Issue this certificate?')">
                      <i class="fas fa-stamp me-1"></i>Issue Certificate
                    </button>
                  </form>
                  @endif
                  @if($record->status == 'issued')
                  <a href="{{ route('admin.exit-certification.download', $record->id) }}" class="btn btn-dark">
                    <i class="fas fa-download me-1"></i>Download PDF
                  </a>
                  <form method="POST" action="{{ route('admin.exit-certification.revoke', $record->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Revoke this certification? This cannot be undone.')">
                      <i class="fas fa-ban me-1"></i>Revoke
                    </button>
                  </form>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Certification Details -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Certification Details</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="fw-semibold text-muted" style="width: 40%;">Certificate No</td>
                  <td class="font-monospace fw-bold">{{ $record->certificate_no }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Exit Level</td>
                  <td>
                    <span class="badge bg-{{ $levelColors[$record->exit_level] ?? 'secondary' }} fs-6">
                      <i class="fas fa-{{ $levelIcons[$record->exit_level] ?? 'certificate' }} me-1"></i>
                      {{ ucfirst($record->exit_level) }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Status</td>
                  <td>
                    @if($record->status == 'pending')
                    <span class="badge bg-warning text-dark fs-6">Pending Approval</span>
                    @elseif($record->status == 'approved')
                    <span class="badge bg-info fs-6">Approved</span>
                    @elseif($record->status == 'issued')
                    <span class="badge bg-success fs-6">Issued</span>
                    @elseif($record->status == 'revoked')
                    <span class="badge bg-danger fs-6">Revoked</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Credits Earned</td>
                  <td><span class="fw-bold">{{ $record->total_credits_earned }}</span> / {{ $record->credits_required }} required</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">CGPA</td>
                  <td class="fw-bold {{ $record->cgpa >= 7 ? 'text-success' : ($record->cgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                    {{ number_format($record->cgpa, 2) }}
                  </td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Semesters Completed</td>
                  <td>{{ $record->semesters_completed }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Issue Date</td>
                  <td>{{ $record->issue_date ? $record->issue_date->format('d M Y') : '—' }}</td>
                </tr>
                @if($record->remarks)
                <tr>
                  <td class="fw-semibold text-muted">Remarks</td>
                  <td>{{ $record->remarks }}</td>
                </tr>
                @endif
                @if($record->approver)
                <tr>
                  <td class="fw-semibold text-muted">Approved By</td>
                  <td>{{ $record->approver->name ?? 'N/A' }}</td>
                </tr>
                @endif
                @if($record->issuer)
                <tr>
                  <td class="fw-semibold text-muted">Issued By</td>
                  <td>{{ $record->issuer->name ?? 'N/A' }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>
        </div>

        <!-- Student Details -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2 text-success"></i>Student Details</h5>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="fw-semibold text-muted" style="width: 40%;">Enrollment No</td>
                  <td class="fw-bold">{{ $record->student->enrollment_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">ERP Student ID</td>
                  <td>{{ $record->student->erp_student_id ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Program</td>
                  <td>{{ $record->program->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Current Semester</td>
                  <td>{{ $record->student->current_semester ?? '—' }}</td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Status</td>
                  <td>{{ ucfirst($record->student->status ?? '') }}</td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Credit Summary -->
          @if($record->credit_summary)
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2 text-info"></i>Semester-wise Credits</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-3">Semester</th>
                      <th class="text-center">Credits</th>
                      <th class="text-center">SGPA</th>
                      <th class="text-center">Result</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($record->credit_summary as $sem)
                    <tr>
                      <td class="ps-3">Semester {{ $sem['semester'] }}</td>
                      <td class="text-center fw-bold">{{ $sem['credits_earned'] }}</td>
                      <td class="text-center">{{ isset($sem['sgpa']) ? number_format($sem['sgpa'], 2) : '—' }}</td>
                      <td class="text-center">
                        @if(($sem['result_status'] ?? '') == 'pass')
                        <span class="badge bg-success">Pass</span>
                        @elseif(($sem['result_status'] ?? '') == 'fail')
                        <span class="badge bg-danger">Fail</span>
                        @else
                        <span class="badge bg-secondary">{{ ucfirst($sem['result_status'] ?? '—') }}</span>
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
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
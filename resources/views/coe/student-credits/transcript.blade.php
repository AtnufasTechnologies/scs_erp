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
            <li class="breadcrumb-item active" aria-current="page">Transcript — {{ $student->enrollment_no }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-file-alt me-2"></i>Academic Credit Transcript</h3>
                  <p class="text-muted mb-0">
                    Student: <strong>{{ $student->enrollment_no }}</strong>
                    | Total Credits: <strong>{{ number_format($totalEarned + $totalTransferred, 1) }}</strong>
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.student-credits.create') }}?student_id={{ $student->id }}&type=earned" class="btn btn-success me-1">
                    <i class="fas fa-plus me-1"></i>Add Earned
                  </a>
                  <a href="{{ route('admin.student-credits.create') }}?student_id={{ $student->id }}&type=transferred" class="btn btn-info text-white">
                    <i class="fas fa-exchange-alt me-1"></i>Add Transfer
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="text-muted small">Earned Credits</div>
              <div class="fw-bold fs-3 text-success">{{ number_format($totalEarned, 1) }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="text-muted small">Transferred Credits</div>
              <div class="fw-bold fs-3 text-info">{{ number_format($totalTransferred, 1) }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0 bg-light">
            <div class="card-body text-center">
              <div class="text-muted small">Grand Total</div>
              <div class="fw-bold fs-3">{{ number_format($totalEarned + $totalTransferred, 1) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Semester-wise Credit Details -->
      @foreach($semesterCredits->sortKeys() as $sem => $semCreds)
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">
            <i class="fas fa-layer-group text-primary me-2"></i>Semester {{ $sem ?? 'Unassigned' }}
          </h5>
          <span class="badge bg-primary fs-6">{{ number_format($semCreds->sum('credits_earned'), 1) }} credits</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Subject</th>
                  <th class="text-center">Credits</th>
                  <th>Grade</th>
                  <th>Type</th>
                  <th>Source</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($semCreds as $sc)
                <tr>
                  <td class="ps-4">
                    @if($sc->subject)
                    <div class="fw-semibold">{{ $sc->subject->subject_code }}</div>
                    <small class="text-muted">{{ $sc->subject->name }}</small>
                    @elseif($sc->source_subject_name)
                    <div class="fw-semibold text-info">{{ $sc->source_subject_code ?? 'Transfer' }}</div>
                    <small class="text-muted">{{ $sc->source_subject_name }}</small>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center fw-bold">{{ number_format($sc->credits_earned, 1) }}</td>
                  <td>
                    @if($sc->grade)
                    <span class="fw-semibold">{{ $sc->grade }}</span>
                    @if($sc->grade_point)
                    <small class="text-muted">({{ $sc->grade_point }})</small>
                    @endif
                    @else
                    —
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-{{ $sc->credit_type === 'earned' ? 'success' : 'info' }}">
                      {{ ucfirst($sc->credit_type) }}
                    </span>
                  </td>
                  <td>
                    @if($sc->source_institution)
                    <small>{{ Str::limit($sc->source_institution, 25) }}</small>
                    @else
                    —
                    @endif
                  </td>
                  <td>
                    @php
                    $sc2 = ['active' => 'success', 'under_review' => 'warning', 'verified' => 'primary', 'rejected' => 'danger'];
                    @endphp
                    <span class="badge bg-{{ $sc2[$sc->status] ?? 'secondary' }}">{{ str_replace('_', ' ', ucfirst($sc->status)) }}</span>
                  </td>
                  <td class="text-end pe-4">
                    <a href="{{ route('admin.student-credits.show', $sc->id) }}" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endforeach

      @if($credits->isEmpty())
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5 text-muted">
          <i class="fas fa-coins fa-3x mb-3 d-block"></i>
          No credit records found for this student.
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')
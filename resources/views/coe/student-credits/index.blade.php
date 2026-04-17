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
            <li class="breadcrumb-item active" aria-current="page">Student Credits</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-coins me-2"></i>Academic Credits (ABC)</h3>
                  <p class="text-muted mb-0">Manage earned credits and transferred credits for students</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.student-credits.create') }}?type=earned" class="btn btn-success me-2">
                    <i class="fas fa-plus-circle me-1"></i>Add Earned
                  </a>
                  <a href="{{ route('admin.student-credits.create') }}?type=transferred" class="btn btn-info text-white">
                    <i class="fas fa-exchange-alt me-1"></i>Add Transfer
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

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

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-list-ol text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total Records</div>
                  <div class="fs-4 fw-bold">{{ $totalCredits }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-graduation-cap text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Earned Credits</div>
                  <div class="fs-4 fw-bold">{{ $earnedCount }} <small class="text-muted fs-6">({{ number_format($totalEarnedCredits, 1) }} cr)</small></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-exchange-alt text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Transferred Credits</div>
                  <div class="fs-4 fw-bold">{{ $transferredCount }} <small class="text-muted fs-6">({{ number_format($totalTransferredCredits, 1) }} cr)</small></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-calculator text-warning fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Grand Total</div>
                  <div class="fs-4 fw-bold">{{ number_format($totalEarnedCredits + $totalTransferredCredits, 1) }} <small class="text-muted fs-6">credits</small></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.student-credits.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Student</label>
              <select name="student_id" class="form-select">
                <option value="">All Students</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                  {{ $s->enrollment_no }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Credit Type</label>
              <select name="credit_type" class="form-select">
                <option value="">All Types</option>
                <option value="earned" {{ request('credit_type') == 'earned' ? 'selected' : '' }}>Earned</option>
                <option value="transferred" {{ request('credit_type') == 'transferred' ? 'selected' : '' }}>Transferred</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Semester</label>
              <select name="semester" class="form-select">
                <option value="">All</option>
                @for($i = 1; $i <= 12; $i++)
                  <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Sem {{ $i }}</option>
                  @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
            </div>
            <div class="col-md-3">
              <div class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.student-credits.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Credits Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student</th>
                  <th>Subject</th>
                  <th class="text-center">Sem</th>
                  <th class="text-center">Credits</th>
                  <th>Type</th>
                  <th>Grade</th>
                  <th>Status</th>
                  <th>Source</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($credits as $credit)
                <tr>
                  <td class="ps-4">{{ $credit->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $credit->student->enrollment_no ?? 'N/A' }}</div>
                  </td>
                  <td>
                    @if($credit->subject)
                    <div class="fw-semibold">{{ $credit->subject->subject_code }}</div>
                    <small class="text-muted">{{ Str::limit($credit->subject->name, 30) }}</small>
                    @elseif($credit->source_subject_name)
                    <div class="fw-semibold text-info">{{ $credit->source_subject_code ?? '—' }}</div>
                    <small class="text-muted">{{ Str::limit($credit->source_subject_name, 30) }}</small>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center">{{ $credit->semester ?? '—' }}</td>
                  <td class="text-center">
                    <span class="fw-bold">{{ number_format($credit->credits_earned, 1) }}</span>
                  </td>
                  <td>
                    @if($credit->credit_type === 'earned')
                    <span class="badge bg-success"><i class="fas fa-graduation-cap me-1"></i>Earned</span>
                    @else
                    <span class="badge bg-info"><i class="fas fa-exchange-alt me-1"></i>Transferred</span>
                    @endif
                  </td>
                  <td>
                    @if($credit->grade)
                    <span class="fw-semibold">{{ $credit->grade }}</span>
                    @if($credit->grade_point)
                    <small class="text-muted">({{ $credit->grade_point }})</small>
                    @endif
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @php
                    $statusColors = ['active' => 'success', 'under_review' => 'warning', 'verified' => 'primary', 'rejected' => 'danger'];
                    $statusLabels = ['active' => 'Active', 'under_review' => 'Under Review', 'verified' => 'Verified', 'rejected' => 'Rejected'];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$credit->status] ?? 'secondary' }}">
                      {{ $statusLabels[$credit->status] ?? $credit->status }}
                    </span>
                  </td>
                  <td>
                    @if($credit->source_institution)
                    <small class="text-muted">{{ Str::limit($credit->source_institution, 20) }}</small>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('admin.student-credits.show', $credit->id) }}" class="btn btn-outline-primary" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="{{ route('admin.student-credits.edit', $credit->id) }}" class="btn btn-outline-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                      </a>
                      @if($credit->credit_type === 'transferred' && $credit->status === 'under_review')
                      <form method="POST" action="{{ route('admin.student-credits.verify', $credit->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" title="Verify" onclick="return confirm('Verify this transferred credit?')">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-coins fa-3x mb-3 d-block"></i>
                    No credit records found.
                    <a href="{{ route('admin.student-credits.create') }}" class="d-block mt-2">Add credits</a>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if($credits->hasPages())
          <div class="d-flex justify-content-center py-3">
            {{ $credits->withQueryString()->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
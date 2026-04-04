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
            <li class="breadcrumb-item active" aria-current="page">Student Promotions</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-arrow-up me-2"></i>Student Promotions</h3>
                  <p class="text-muted mb-0">NEP-based automatic promotions with backlog tracking and credit management</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.promotions.export') }}" class="btn btn-outline-primary">
                    <i class="fas fa-download me-2"></i>Export
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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-list-ol text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total</div>
                  <div class="fs-4 fw-bold">{{ $totalPromotions }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Promoted</div>
                  <div class="fs-4 fw-bold">{{ $promotedClean }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">With Backlogs</div>
                  <div class="fs-4 fw-bold">{{ $promotedWithBacklogs }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-dark bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-pause-circle text-dark fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Withheld</div>
                  <div class="fs-4 fw-bold">{{ $withheldCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-redo text-danger fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Pending Backlogs</div>
                  <div class="fs-4 fw-bold">{{ $totalPendingBacklogs }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-check-double text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Cleared Backlogs</div>
                  <div class="fs-4 fw-bold">{{ $totalClearedBacklogs }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.promotions.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam Session</label>
              <select name="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} (Sem {{ $session->semester }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">From Semester</label>
              <select name="from_semester" class="form-select">
                <option value="">All</option>
                @for($i = 1; $i <= 8; $i++)
                  <option value="{{ $i }}" {{ request('from_semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                  @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="promotion_status" class="form-select">
                <option value="">All</option>
                <option value="promoted" {{ request('promotion_status') == 'promoted' ? 'selected' : '' }}>Promoted</option>
                <option value="promoted_with_backlogs" {{ request('promotion_status') == 'promoted_with_backlogs' ? 'selected' : '' }}>With Backlogs</option>
                <option value="withheld" {{ request('promotion_status') == 'withheld' ? 'selected' : '' }}>Withheld</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Enrollment No</label>
              <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Promotions Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student</th>
                  <th>Session</th>
                  <th>From Sem</th>
                  <th>To Sem</th>
                  <th>Credits (Earned/Transferred)</th>
                  <th>Backlogs</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($promotions as $promotion)
                <tr>
                  <td class="ps-4">{{ $promotion->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $promotion->student->enrollment_no ?? 'N/A' }}</div>
                    @if($promotion->student && $promotion->student->erp_student_id)
                    <small class="text-muted">ID: {{ $promotion->student->erp_student_id }}</small>
                    @endif
                  </td>
                  <td>
                    @if($promotion->examSession)
                    <div class="fw-semibold">{{ $promotion->examSession->name }}</div>
                    <small class="text-muted">{{ $promotion->examSession->academic_year }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td><span class="badge bg-secondary">Sem {{ $promotion->from_semester }}</span></td>
                  <td><span class="badge bg-primary">Sem {{ $promotion->to_semester }}</span></td>
                  <td>
                    <span class="fw-bold text-success">{{ $promotion->earned_credits ?? 0 }}</span>
                    <span class="text-muted">/</span>
                    <span class="fw-bold text-info">{{ $promotion->transferred_credits ?? 0 }}</span>
                  </td>
                  <td>
                    @if($promotion->backlog_subjects && count($promotion->backlog_subjects) > 0)
                    <span class="badge bg-warning text-dark">{{ count($promotion->backlog_subjects) }} backlogs</span>
                    @else
                    <span class="badge bg-success">None</span>
                    @endif
                  </td>
                  <td>
                    @if($promotion->promotion_status === 'promoted')
                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Promoted</span>
                    @elseif($promotion->promotion_status === 'promoted_with_backlogs')
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>With Backlogs</span>
                    @elseif($promotion->promotion_status === 'withheld')
                    <span class="badge bg-dark"><i class="fas fa-pause me-1"></i>Withheld</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($promotion->promotion_status ?? 'N/A') }}</span>
                    @endif
                  </td>
                  <td class="text-end pe-4">
                    <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-5">
                    <div class="text-muted">
                      <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                      <p>No promotions found. Promotions are automatically created when results are published.</p>
                    </div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          @if($promotions->hasPages())
          <div class="card-footer bg-white">
            {{ $promotions->withQueryString()->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
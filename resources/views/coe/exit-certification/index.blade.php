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
            <li class="breadcrumb-item active" aria-current="page">Exit Certification</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-certificate me-2"></i>Exit Certification</h3>
                  <p class="text-muted mb-0">NEP-based exit certification — Certificate, Diploma, Degree, Honours</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.exit-certification.create') }}" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>New Certification
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
                  <div class="fs-4 fw-bold">{{ $totalRecords }}</div>
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
                  <i class="fas fa-clock text-warning fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Pending</div>
                  <div class="fs-4 fw-bold">{{ $pendingCount }}</div>
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
                  <i class="fas fa-check-circle text-success fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Issued</div>
                  <div class="fs-4 fw-bold">{{ $issuedCount }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center flex-wrap gap-2">
                @foreach(['certificate' => 'info', 'diploma' => 'primary', 'degree' => 'success', 'honors' => 'warning'] as $lvl => $color)
                <span class="badge bg-{{ $color }}">{{ ucfirst($lvl) }}: {{ $levelCounts[$lvl] ?? 0 }}</span>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exit-certification.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exit Level</label>
              <select name="exit_level" class="form-select">
                <option value="">All Levels</option>
                <option value="certificate" {{ request('exit_level') == 'certificate' ? 'selected' : '' }}>Certificate</option>
                <option value="diploma" {{ request('exit_level') == 'diploma' ? 'selected' : '' }}>Diploma</option>
                <option value="degree" {{ request('exit_level') == 'degree' ? 'selected' : '' }}>Degree</option>
                <option value="honors" {{ request('exit_level') == 'honors' ? 'selected' : '' }}>Honours</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Certificate No / Enrollment..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('admin.exit-certification.index') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Records Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Certificate No</th>
                  <th>Student</th>
                  <th>Exit Level</th>
                  <th class="text-center">Credits</th>
                  <th class="text-center">CGPA</th>
                  <th class="text-center">Semesters</th>
                  <th>Status</th>
                  <th>Issue Date</th>
                  <th class="text-end pe-4">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($records as $record)
                <tr>
                  <td class="ps-4">{{ $record->id }}</td>
                  <td>
                    <span class="fw-semibold font-monospace">{{ $record->certificate_no }}</span>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $record->student->enrollment_no ?? 'N/A' }}</div>
                    <small class="text-muted">{{ $record->program->name ?? '' }}</small>
                  </td>
                  <td>
                    @php
                    $levelColors = ['certificate' => 'info', 'diploma' => 'primary', 'degree' => 'success', 'honors' => 'warning'];
                    $levelIcons = ['certificate' => 'scroll', 'diploma' => 'award', 'degree' => 'graduation-cap', 'honors' => 'star'];
                    @endphp
                    <span class="badge bg-{{ $levelColors[$record->exit_level] ?? 'secondary' }} fs-6">
                      <i class="fas fa-{{ $levelIcons[$record->exit_level] ?? 'certificate' }} me-1"></i>
                      {{ ucfirst($record->exit_level) }}
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="fw-bold">{{ $record->total_credits_earned }}</span>
                    <small class="text-muted">/ {{ $record->credits_required }}</small>
                  </td>
                  <td class="text-center">
                    <span class="fw-bold {{ $record->cgpa >= 7 ? 'text-success' : ($record->cgpa >= 5 ? 'text-warning' : 'text-danger') }}">
                      {{ number_format($record->cgpa, 2) }}
                    </span>
                  </td>
                  <td class="text-center">{{ $record->semesters_completed }}</td>
                  <td>
                    @if($record->status == 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($record->status == 'approved')
                    <span class="badge bg-info">Approved</span>
                    @elseif($record->status == 'issued')
                    <span class="badge bg-success">Issued</span>
                    @elseif($record->status == 'revoked')
                    <span class="badge bg-danger">Revoked</span>
                    @endif
                  </td>
                  <td>{{ $record->issue_date ? $record->issue_date->format('d M Y') : '—' }}</td>
                  <td class="text-end pe-4">
                    <div class="btn-group btn-group-sm">
                      <a href="{{ route('admin.exit-certification.show', $record->id) }}" class="btn btn-outline-primary" title="View">
                        <i class="fas fa-eye"></i>
                      </a>
                      @if($record->status == 'pending')
                      <form method="POST" action="{{ route('admin.exit-certification.approve', $record->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve this certification?')">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                      @endif
                      @if($record->status == 'approved')
                      <form method="POST" action="{{ route('admin.exit-certification.issue', $record->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-info" title="Issue Certificate" onclick="return confirm('Issue this certificate?')">
                          <i class="fas fa-stamp"></i>
                        </button>
                      </form>
                      @endif
                      @if($record->status == 'issued')
                      <a href="{{ route('admin.exit-certification.download', $record->id) }}" class="btn btn-outline-dark" title="Download PDF">
                        <i class="fas fa-download"></i>
                      </a>
                      @endif
                      @if($record->status != 'issued')
                      <form method="POST" action="{{ route('admin.exit-certification.destroy', $record->id) }}" class="d-inline"
                        onsubmit="return confirm('Delete this record?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-certificate fa-3x mb-3 d-block"></i>
                    No exit certifications found.
                    <a href="{{ route('admin.exit-certification.create') }}" class="d-block mt-2">Create one</a>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if($records->hasPages())
          <div class="d-flex justify-content-center py-3">
            {{ $records->withQueryString()->links() }}
          </div>
          @endif
        </div>
      </div>

      <!-- Credit Requirements Reference -->
      <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>NEP Exit Level Requirements</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">Exit Level</th>
                  <th class="text-center">Min Credits</th>
                  <th class="text-center">Min Semesters</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="ps-4"><span class="badge bg-info fs-6"><i class="fas fa-scroll me-1"></i>Certificate</span></td>
                  <td class="text-center fw-bold">40</td>
                  <td class="text-center">2</td>
                  <td>UG Certificate after completing 1 year (2 semesters)</td>
                </tr>
                <tr>
                  <td class="ps-4"><span class="badge bg-primary fs-6"><i class="fas fa-award me-1"></i>Diploma</span></td>
                  <td class="text-center fw-bold">80</td>
                  <td class="text-center">4</td>
                  <td>UG Diploma after completing 2 years (4 semesters)</td>
                </tr>
                <tr>
                  <td class="ps-4"><span class="badge bg-success fs-6"><i class="fas fa-graduation-cap me-1"></i>Degree</span></td>
                  <td class="text-center fw-bold">120</td>
                  <td class="text-center">6</td>
                  <td>Bachelor's Degree after completing 3 years (6 semesters)</td>
                </tr>
                <tr>
                  <td class="ps-4"><span class="badge bg-warning text-dark fs-6"><i class="fas fa-star me-1"></i>Honours</span></td>
                  <td class="text-center fw-bold">160</td>
                  <td class="text-center">8</td>
                  <td>Honours / Research Degree after completing 4 years (8 semesters)</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Backlogs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.backlogs.index') }}">Backlogs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Report</li>
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
                  <h3 class="text-dark fw-bold mb-2"><i class="fas fa-chart-bar me-2"></i>Backlog Report</h3>
                  <p class="text-muted mb-0">Comprehensive overview grouped by student and subject</p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('coe.backlogs.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Backlogs
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.backlogs.report') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Session</label>
              <select name="exam_session_id" class="form-select">
                <option value="">All Sessions</option>
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                  {{ $session->name }} (Sem {{ $session->semester }})
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Exam</label>
              <select name="exam_id" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                  {{ $exam->name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
              <a href="{{ route('coe.backlogs.report') }}" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                  <i class="fas fa-list-ol text-primary fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Total</div>
                  <div class="fs-4 fw-bold">{{ $totalBacklogs }}</div>
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
                  <div class="fs-4 fw-bold">{{ $pendingBacklogs }}</div>
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
                  <i class="fas fa-pen-square text-info fa-lg"></i>
                </div>
                <div>
                  <div class="text-muted small">Registered</div>
                  <div class="fs-4 fw-bold">{{ $registeredBacklogs }}</div>
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
                  <div class="text-muted small">Cleared</div>
                  <div class="fs-4 fw-bold">{{ $clearedBacklogs }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Student-wise Report -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-0">
          <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>Student-wise Summary</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Student</th>
                  <th class="text-center">Total</th>
                  <th class="text-center">Pending</th>
                  <th class="text-center">Registered</th>
                  <th class="text-center">Cleared</th>
                  <th class="text-center">Max Attempt</th>
                </tr>
              </thead>
              <tbody>
                @forelse($studentBacklogs as $key => $data)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    @if($data['student'])
                    <div class="fw-semibold">{{ $data['student']->enrollment_no }}</div>
                    <small class="text-muted">ID: {{ $data['student']->erp_student_id }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $data['total'] }}</span></td>
                  <td class="text-center">
                    @if($data['pending'] > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $data['pending'] }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($data['registered'] > 0)
                    <span class="badge bg-info rounded-pill">{{ $data['registered'] }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($data['cleared'] > 0)
                    <span class="badge bg-success rounded-pill">{{ $data['cleared'] }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <span class="badge bg-{{ $data['max_attempt'] > 2 ? 'danger' : ($data['max_attempt'] > 1 ? 'warning text-dark' : 'secondary') }} rounded-pill">
                      {{ $data['max_attempt'] }}
                    </span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">No data available</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Subject-wise Report -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-0">
          <h5 class="mb-0 fw-bold"><i class="fas fa-book me-2"></i>Subject-wise Summary</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>Subject</th>
                  <th class="text-center">Total Backlogs</th>
                  <th class="text-center">Pending</th>
                  <th class="text-center">Cleared</th>
                  <th class="text-center">Clearance Rate</th>
                </tr>
              </thead>
              <tbody>
                @forelse($subjectBacklogs as $key => $data)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    @if($data['subject'])
                    <div class="fw-semibold">{{ $data['subject']->subject_code }}</div>
                    <small class="text-muted">{{ $data['subject']->name }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td class="text-center"><span class="badge bg-primary rounded-pill">{{ $data['total'] }}</span></td>
                  <td class="text-center">
                    @if($data['pending'] > 0)
                    <span class="badge bg-warning text-dark rounded-pill">{{ $data['pending'] }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($data['cleared'] > 0)
                    <span class="badge bg-success rounded-pill">{{ $data['cleared'] }}</span>
                    @else
                    <span class="text-muted">0</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @php $rate = $data['total'] > 0 ? round(($data['cleared'] / $data['total']) * 100, 1) : 0; @endphp
                    <div class="d-flex align-items-center justify-content-center gap-2">
                      <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                        <div class="progress-bar bg-success" style="width: {{ $rate }}%"></div>
                      </div>
                      <small class="fw-semibold">{{ $rate }}%</small>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No data available</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
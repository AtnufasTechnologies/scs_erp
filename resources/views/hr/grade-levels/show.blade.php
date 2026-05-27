@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Grade Level Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.grade-levels.index') }}">Grade Levels</a></li>
            <li class="breadcrumb-item active">{{ $gradeLevel->name }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.grade-levels.edit', $gradeLevel) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('hr.grade-levels.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>

    {{-- Grade Level Details --}}
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Grade Level Details</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Grade Level Name</label>
              <div class="fw-bold">{{ $gradeLevel->name }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Code</label>
              <div class="fw-bold">{{ $gradeLevel->code ?: 'N/A' }}</div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Minimum Salary</label>
              <div class="fw-bold">₹{{ number_format($gradeLevel->min_salary, 2) }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Maximum Salary</label>
              <div class="fw-bold">₹{{ number_format($gradeLevel->max_salary, 2) }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Salary Range</label>
              <div class="text-success fw-bold">
                ₹{{ number_format($gradeLevel->max_salary - $gradeLevel->min_salary, 2) }}
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Level Order</label>
              <div class="fw-bold">{{ $gradeLevel->level_order }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Status</label>
              <div>
                <span class="badge {{ $gradeLevel->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                  {{ ucfirst($gradeLevel->status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Description</label>
          <div>{{ $gradeLevel->description ?: 'No description provided' }}</div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <label class="text-muted small">Created By</label>
            <div>{{ $gradeLevel->creator->name ?? 'System' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Created On</label>
            <div>{{ $gradeLevel->created_at->format('d M Y, h:i A') }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Statistics --}}
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body text-center">
            <i class="fas fa-users fa-3x text-primary mb-2"></i>
            <h3 class="fw-bold">{{ $gradeLevel->faculties_count }}</h3>
            <div class="text-muted">Assigned Faculty</div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body text-center">
            <i class="fas fa-money-check-alt fa-3x text-success mb-2"></i>
            <h3 class="fw-bold">{{ $gradeLevel->pay_matrices_count }}</h3>
            <div class="text-muted">Pay Matrix Entries</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Assigned Faculty --}}
    @if($gradeLevel->faculties_count > 0)
    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Faculty ({{ $gradeLevel->faculties_count }})</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Faculty ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($gradeLevel->faculties as $faculty)
              <tr>
                <td>{{ $faculty->USER_CODE }}</td>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</td>
                <td>{{ $faculty->MAIL_ID }}</td>
                <td>{{ $faculty->hrDesignation->name ?? 'N/A' }}</td>
                <td>{{ $faculty->department->name ?? $faculty->department->title ?? 'N/A' }}</td>
                <td>
                  <span class="badge {{ $faculty->IS_LEFT == 0 ? 'bg-success' : 'bg-danger' }}">
                    {{ $faculty->IS_LEFT == 0 ? 'Active' : 'Left' }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    {{-- Pay Matrix Entries --}}
    @if($gradeLevel->pay_matrices_count > 0)
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Pay Matrix Entries ({{ $gradeLevel->pay_matrices_count }})</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Designation</th>
                <th>Level</th>
                <th>Cell</th>
                <th>Basic Pay</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($gradeLevel->payMatrices as $payMatrix)
              <tr>
                <td>{{ $payMatrix->designation->name ?? $payMatrix->designation }}</td>
                <td>{{ $payMatrix->level }}</td>
                <td>{{ $payMatrix->cell }}</td>
                <td>₹{{ number_format($payMatrix->basic_pay, 2) }}</td>
                <td>
                  <span class="badge {{ $payMatrix->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst($payMatrix->status) }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

  </main>
</div>
@include('includes.footer')
@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Designation Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.designations.index') }}">Designations</a></li>
            <li class="breadcrumb-item active">{{ $designation->name }}</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.designations.edit', $designation) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('hr.designations.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>

    {{-- Designation Details --}}
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Designation Details</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Designation Name</label>
              <div class="fw-bold">{{ $designation->name }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="text-muted small">Code</label>
              <div class="fw-bold">{{ $designation->code ?: 'N/A' }}</div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Category</label>
              <div>
                @php
                $categoryClasses = [
                'teaching' => 'bg-primary',
                'non-teaching' => 'bg-success',
                'administrative' => 'bg-info',
                'technical' => 'bg-warning',
                'support' => 'bg-secondary'
                ];
                @endphp
                <span class="badge {{ $categoryClasses[$designation->category] ?? 'bg-secondary' }}">
                  {{ ucwords(str_replace('-', ' ', $designation->category)) }}
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Hierarchy Level</label>
              <div class="fw-bold">{{ $designation->hierarchy_level }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="text-muted small">Status</label>
              <div>
                <span class="badge {{ $designation->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                  {{ ucfirst($designation->status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Description</label>
          <div>{{ $designation->description ?: 'No description provided' }}</div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <label class="text-muted small">Created By</label>
            <div>{{ $designation->creator->name ?? 'System' }}</div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Created On</label>
            <div>{{ $designation->created_at->format('d M Y, h:i A') }}</div>
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
            <h3 class="fw-bold">{{ $designation->faculties_count }}</h3>
            <div class="text-muted">Assigned Faculty</div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body text-center">
            <i class="fas fa-money-check-alt fa-3x text-success mb-2"></i>
            <h3 class="fw-bold">{{ $designation->pay_matrices_count }}</h3>
            <div class="text-muted">Pay Matrix Entries</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Assigned Faculty --}}
    @if($designation->faculties_count > 0)
    <div class="card mb-4">
      <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Faculty ({{ $designation->faculties_count }})</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Faculty ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($designation->faculties as $faculty)
              <tr>
                <td>{{ $faculty->USER_CODE }}</td>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</td>
                <td>{{ $faculty->MAIL_ID }}</td>
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
    @if($designation->pay_matrices_count > 0)
    <div class="card mb-4">
      <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Pay Matrix Entries ({{ $designation->pay_matrices_count }})</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Grade Level</th>
                <th>Level</th>
                <th>Cell</th>
                <th>Basic Pay</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($designation->payMatrices as $payMatrix)
              <tr>
                <td>{{ $payMatrix->gradeLevel->name ?? $payMatrix->grade_level }}</td>
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
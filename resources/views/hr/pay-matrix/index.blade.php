@include('includes.header')
@include('hr.sidebar')

<!--start main wrapper-->


<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">HR</div>
  <div class="ps-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item active" aria-current="page">Pay Matrix</li>
      </ol>
    </nav>
  </div>
  <div class="ms-auto">
    <a href="{{ route('hr.pay-matrix.create') }}" class="btn btn-primary">
      <i class="fas fa-plus-circle"></i> Create Pay Matrix
    </a>
  </div>
</div>
<!--end breadcrumb-->

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center mb-3">
      <h5 class="mb-0">Pay Matrix List</h5>
    </div>

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route('hr.pay-matrix.index') }}" class="mb-4">
      <div class="row g-3">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control" placeholder="Search by code, name, designation..."
            value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
          <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
          </select>
        </div>
        <div class="col-md-2">
          <select name="employment_type" class="form-select">
            <option value="">All Types</option>
            <option value="permanent" {{ request('employment_type') == 'permanent' ? 'selected' : '' }}>Permanent
            </option>
            <option value="contractual" {{ request('employment_type') == 'contractual' ? 'selected' : '' }}>
              Contractual</option>
            <option value="adhoc" {{ request('employment_type') == 'adhoc' ? 'selected' : '' }}>Ad-hoc</option>
            <option value="guest" {{ request('employment_type') == 'guest' ? 'selected' : '' }}>Guest</option>
            <option value="visiting" {{ request('employment_type') == 'visiting' ? 'selected' : '' }}>Visiting
            </option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="designation" class="form-select">
            <option value="">All Designations</option>
            @foreach($designations as $designation)
            <option value="{{ $designation }}" {{ request('designation') == $designation ? 'selected' : '' }}>
              {{ $designation }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Search
          </button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Matrix Code</th>
            <th>Matrix Name</th>
            <th>Designation</th>
            <th>Grade Level</th>
            <th>Basic Salary</th>
            <th>Employment Type</th>
            <th>Faculty Count</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payMatrices as $matrix)
          <tr>
            <td><strong>{{ $matrix->matrix_code }}</strong></td>
            <td>{{ $matrix->matrix_name }}</td>
            <td>{{ $matrix->designation }}</td>
            <td><span class="badge bg-info">{{ $matrix->grade_level }}</span></td>
            <td>₹{{ number_format($matrix->basic_salary, 2) }}</td>
            <td>
              <span class="badge bg-secondary">{{ ucfirst($matrix->employment_type) }}</span>
            </td>
            <td>
              <span class="badge bg-primary">{{ $matrix->faculty_count }}</span>
            </td>
            <td>
              <span class="badge bg-{{ $matrix->status_color }}">{{ ucfirst($matrix->status) }}</span>
            </td>
            <td>
              <div class="d-flex gap-2">
                <a href="{{ route('hr.pay-matrix.show', $matrix->id) }}" class="btn btn-sm btn-info"
                  title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('hr.pay-matrix.edit', $matrix->id) }}" class="btn btn-sm btn-warning"
                  title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('hr.pay-matrix.duplicate', $matrix->id) }}" method="POST"
                  style="display: inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-secondary" title="Duplicate">
                    <i class="fas fa-copy"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" class="text-center">No pay matrices found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $payMatrices->links('vendor.pagination.bootstrap-5') }}
    </div>
  </div>
</div>




@include('includes.footer')
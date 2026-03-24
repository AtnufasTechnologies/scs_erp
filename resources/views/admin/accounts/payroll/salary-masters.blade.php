@include('includes.header')
@include('admin.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Faculty Salary Masters</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Salary Masters</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.salary-masters.create') }}" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Salary Master
        </a>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="card bg-dark text-light">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Active Salary Masters</h6>
                <h3 class="mb-0 text-light">{{ $stats['total'] }}</h3>
              </div>
              <i class="fas fa-users fa-3x opacity-50"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card bg-success text-light">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0 text-dark">Total Monthly CTC</h6>
                <h3 class="mb-0 text-light">₹{{ number_format($stats['total_monthly_cost'], 2) }}</h3>
              </div>
              <i class="fas fa-rupee-sign fa-3xopacity-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('admin.payroll.salary-masters') }}">
          <div class="row g-3">
            <div class="col-md-4">
              <select name="faculty_id" class="form-select dselect-example">
                <option value="">All Faculty</option>
                @foreach($faculties as $faculty)
                <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                  {{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter"></i> Filter</button>
            </div>
            <div class="col-md-2">
              <a href="{{ route('admin.payroll.salary-masters') }}" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Salary Masters Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Faculty</th>
                <th>Basic Salary</th>
                <th>Total Earnings</th>
                <th>Total Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
                <th>Effective From</th>
                <th width="150">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($salaryMasters as $master)
              <tr>
                <td>
                  <strong>{{ $master->faculty->USER_CODE ?? '' }}</strong><br>
                  <small>{{ $master->faculty->FIRST_NAME ?? '' }} {{ $master->faculty->LAST_NAME ?? '' }}</small>
                </td>
                <td>₹{{ number_format($master->basic_salary, 2) }}</td>
                <td>₹{{ number_format($master->total_earnings, 2) }}</td>
                <td>₹{{ number_format($master->total_deductions, 2) }}</td>
                <td><strong>₹{{ number_format($master->net_salary, 2) }}</strong></td>
                <td>
                  <span class="badge bg-{{ $master->status_badge }}">{{ ucfirst($master->status) }}</span>
                </td>
                <td>{{ $master->effective_from ? $master->effective_from->format('d M Y') : '-' }}</td>
                <td>
                  <div class="btn-group">
                    <a href="{{ route('admin.payroll.salary-masters.edit', $master->id) }}"
                      class="btn btn-sm btn-warning" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.payroll.salary-masters.toggle-status', $master->id) }}"
                      method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-info"
                        title="{{ $master->status === 'active' ? 'Deactivate' : 'Activate' }}">
                        <i class="fas fa-{{ $master->status === 'active' ? 'ban' : 'check' }}"></i>
                      </button>
                    </form>
                    <form action="{{ route('admin.payroll.salary-masters.destroy', $master->id) }}"
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this salary master?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center py-4">
                  <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                  <p class="text-muted">No salary masters found. <a href="{{ route('admin.payroll.salary-masters.create') }}">Create one</a></p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($salaryMasters->hasPages())
        <div class="mt-3">
          {{ $salaryMasters->links() }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">All Employees</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.salary-masters') }}">Salary Masters</a></li>
            <li class="breadcrumb-item active">Employees List</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.salary-masters') }}" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left"></i> Back to Salary Masters
        </a>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('admin.payroll.employees-list') }}" class="row g-2">
          <div class="col-md-9">
            <input
              type="text"
              name="search"
              value="{{ $search }}"
              class="form-control"
              placeholder="Search by code, name, mobile, designation, type, pay matrix...">
          </div>
          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
          </div>
          <div class="col-md-1 d-grid">
            <a href="{{ route('admin.payroll.employees-list') }}" class="btn btn-light">Clear</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Type</th>
                <th>Mobile</th>
                <th>Pay Matrix</th>
                <th class="text-end">Salary</th>
              </tr>
            </thead>
            <tbody>
              @forelse($employees as $employee)
              <tr>
                <td><strong>{{ $employee->USER_CODE ?? '-' }}</strong></td>
                <td>{{ $employee->FIRST_NAME ?? '' }} {{ $employee->LAST_NAME ?? '' }}</td>
                <td>{{ $employee->designation ?? 'N/A' }}</td>
                <td>{{ $employee->employee_type ?? 'N/A' }}</td>
                <td>{{ $employee->MOBILE_NO ?? 'N/A' }}</td>
                <td>
                  {{ optional(optional($employee->salaryMaster)->payMatrix)->matrix_code ?? 'Not Added' }}
                </td>
                <td class="text-end">₹{{ number_format((float) optional($employee->salaryMaster)->net_salary, 2) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">No employees found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($employees->hasPages())
        <div class="mt-3">
          {{ $employees->links('vendor.pagination.bootstrap-5') }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
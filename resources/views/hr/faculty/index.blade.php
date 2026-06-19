@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Employee Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Employee List</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
      <div class="card-header bg-transparent">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0">Master List</h5>
          </div>
          <div class="col-md-6 text-end">
            <a href="{{ route('hr.faculty.create') }}" class="btn btn-primary">
              <i class="fas fa-plus me-1"></i>Add Employee
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <!-- Search and Filter -->
        <form method="GET" action="{{ route('hr.faculty.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-6">
              <input type="text" name="search" class="form-control" placeholder="Search by name, code, email, or phone" value="{{ $search }}">
            </div>

            <div class="col-md-3">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-1"></i>Search
              </button>
            </div>
          </div>
        </form>

        <!-- Faculty Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Employee Code</th>
                <th>Campus </th>
                <th>Name</th>
                <th>Contact</th>
                <th>Designation</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($faculties as $faculty)
              <tr>
                <td><strong>{{ $faculty->USER_CODE }}</strong></td>
                <td>{{ $faculty->CAMPUS_ID == 1 ? 'Sonada' : 'Siliguri' }}</td>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}
                </td>
                <td>
                  <small>{{ $faculty->MAIL_ID }}</small><br>
                  <small>{{ $faculty->MOBILE_NO }}</small>
                </td>
                <td>{{ $faculty->designation ?? 'N/A' }}</td>
                <td>
                  @if($faculty->IS_LEFT)
                  <span class="badge bg-danger">Left</span>
                  @else
                  <span class="badge bg-success">Active</span>
                  @endif
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('hr.faculty.show', $faculty->id) }}" class="btn btn-outline-primary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('hr.faculty.edit', $faculty->id) }}" class="btn btn-outline-secondary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No faculty members found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $faculties->links('vendor.pagination.bootstrap-5') }}
        </div>
      </div>
    </div>

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
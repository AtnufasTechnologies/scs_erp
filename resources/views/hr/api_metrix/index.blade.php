@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Metrix</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Category Master</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card mt-3">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <h5 class="mb-0">API Metrix Categories</h5>
          <a href="{{ route('hr.api-metrix.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Create Category
          </a>
        </div>

        <form method="GET" action="{{ route('hr.api-metrix.index') }}" class="row g-2 mb-3">
          <div class="col-md-4">
            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by category title">
          </div>
          <div class="col-md-3">
            <select name="status" class="form-select">
              <option value="">All Status</option>
              <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>
          <div class="col-md-3">
            <select name="role_id" class="form-select">
              <option value="">All Roles</option>
              @foreach($roles as $role)
              <option value="{{ $role->id }}" {{ (string) request('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filter</button>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Title</th>
                <th>Applicable Roles</th>
                <th class="text-center">Components</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categories as $category)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $category->title }}</div>
                  <div class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $category->description, 80) }}</div>
                </td>
                <td>
                  @forelse($category->roles as $role)
                  <span class="badge bg-light text-dark border me-1 mb-1">{{ $role->role_name }}</span>
                  @empty
                  <span class="text-muted small">No role selected</span>
                  @endforelse
                </td>
                <td class="text-center"><span class="badge bg-info text-dark">{{ (int) $category->components_count }}</span></td>
                <td>
                  @if($category->status === 'active')
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td class="text-end">
                  <a href="{{ route('hr.api-metrix.show', $category->id) }}" class="btn btn-sm btn-outline-info" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('hr.api-metrix.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form action="{{ route('hr.api-metrix.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">No API Metrix categories found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{ $categories->links() }}
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
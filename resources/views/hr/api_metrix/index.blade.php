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

    <style>
      .amx-index-shell {
        background: radial-gradient(1200px 320px at 0% 0%, #eef6ff 0%, rgba(238, 246, 255, 0) 60%), linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e6edf5;
        border-radius: 16px;
      }

      .amx-index-card {
        border: 1px solid #dde7f2;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 6px 22px rgba(15, 23, 42, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        height: 100%;
      }

      .amx-index-card:hover {
        transform: translateY(-3px);
        border-color: #c9d7e8;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
      }

      .amx-index-title {
        font-weight: 700;
        color: #1f2937;
        font-size: 1rem;
      }

      .amx-kpi {
        border: 1px solid #d7e2f0;
        border-radius: 12px;
        background: #f8fbff;
        padding: 10px;
      }

      .amx-kpi .label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
      }

      .amx-kpi .value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
      }

      .amx-role-cloud {
        min-height: 54px;
      }

      .amx-switch-wrap {
        border: 1px dashed #c7d4e4;
        border-radius: 12px;
        padding: 10px;
        background: #f8fbff;
      }

      .amx-switch-label {
        font-size: 0.78rem;
        color: #475569;
        font-weight: 600;
      }

      .amx-switch-status {
        font-size: 0.78rem;
      }
    </style>

    <div class="card mt-3 amx-index-shell">
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
          <div class="col-md-2">
            <select name="show_in_workdiary" class="form-select">
              <option value="">WorkDiary: All</option>
              <option value="1" {{ request('show_in_workdiary') === '1' ? 'selected' : '' }}>Visible</option>
              <option value="0" {{ request('show_in_workdiary') === '0' ? 'selected' : '' }}>Hidden</option>
            </select>
          </div>
          <div class="col-md-12 col-lg-2 d-grid">
            <button type="submit" class="btn btn-outline-primary">Filter</button>
          </div>
        </form>

        @if($categories->count() > 0)
        <div class="row g-3">
          @foreach($categories as $category)
          <div class="col-12 col-md-6 col-xl-4">
            <div class="amx-index-card p-3">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div>
                  <div class="amx-index-title">{{ $category->title }}</div>
                  <div class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $category->description, 110) }}</div>
                </div>
                <div>
                  @if($category->status === 'active')
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-secondary">Inactive</span>
                  @endif
                </div>
              </div>

              <div class="row g-2 mb-3">
                <div class="col-6">
                  <div class="amx-kpi">
                    <div class="label">Components</div>
                    <div class="value">{{ (int) $category->components_count }}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="amx-kpi">
                    <div class="label">WorkDiary</div>
                    <div class="value">
                      @if((int) $category->show_in_workdiary === 1)
                      <span class="badge bg-success">Visible</span>
                      @else
                      <span class="badge bg-secondary">Hidden</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <div class="small text-muted mb-1">Applicable Roles</div>
                <div class="amx-role-cloud">
                  @forelse($category->roles as $role)
                  <span class="badge bg-light text-dark border me-1 mb-1">{{ $role->role_name }}</span>
                  @empty
                  <span class="text-muted small">No role selected</span>
                  @endforelse
                </div>
              </div>

              <div class="amx-switch-wrap mb-3">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="amx-switch-label">Show In WorkDiary</span>
                  <span class="amx-switch-status {{ (int) $category->show_in_workdiary === 1 ? 'text-success' : 'text-secondary' }}">
                    {{ (int) $category->show_in_workdiary === 1 ? 'Visible' : 'Hidden' }}
                  </span>
                </div>
                <form action="{{ route('hr.api-metrix.toggle-workdiary-visibility', $category->id) }}" method="POST" class="mt-2">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="show_in_workdiary" value="{{ (int) $category->show_in_workdiary === 1 ? 0 : 1 }}">
                  <button type="submit" class="btn btn-sm {{ (int) $category->show_in_workdiary === 1 ? 'btn-outline-secondary' : 'btn-outline-success' }} w-100">
                    {{ (int) $category->show_in_workdiary === 1 ? 'Hide From WorkDiary' : 'Show In WorkDiary' }}
                  </button>
                </form>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('hr.api-metrix.show', $category->id) }}" class="btn btn-sm btn-outline-info" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('hr.api-metrix.edit', $category->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('hr.api-metrix.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="text-center text-muted py-5">No API Metrix categories found.</div>
        @endif

        {{ $categories->links() }}
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
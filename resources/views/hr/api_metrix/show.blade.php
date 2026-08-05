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
            <li class="breadcrumb-item"><a href="{{ route('hr.api-metrix.index') }}">Category Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
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
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h4 class="mb-1">{{ $category->title }}</h4>
            <div class="text-muted">{{ $category->description ?: 'No description provided' }}</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('hr.api-metrix.edit', $category->id) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('hr.api-metrix.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">Status</div>
              @if($category->status === 'active')
              <span class="badge bg-success mt-1">Active</span>
              @else
              <span class="badge bg-secondary mt-1">Inactive</span>
              @endif
            </div>
          </div>
          <div class="col-md-8">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Applicable Roles</div>
              @forelse($category->roles as $role)
              <span class="badge bg-light text-dark border me-1 mb-1">{{ $role->role_name }}</span>
              @empty
              <span class="text-muted small">No role mapping available.</span>
              @endforelse
            </div>
          </div>
        </div>

        <div class="mt-4">
          <h5 class="mb-3">Components</h5>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 80px;">#</th>
                  <th>Component Title</th>
                  <th style="width: 140px;">Score</th>
                  <th style="width: 220px;">Verified By Role</th>
                  <th style="width: 140px;">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($category->components as $component)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $component->title }}</td>
                  <td>{{ number_format((float) $component->score, 2) }}</td>
                  <td>{{ optional($component->verifierRole)->role_name ?? '-' }}</td>
                  <td>
                    @if($component->is_active)
                    <span class="badge bg-success">Active</span>
                    @else
                    <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">No components configured.</td>
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
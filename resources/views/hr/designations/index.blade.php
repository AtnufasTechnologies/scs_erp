@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Designations</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Designations</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.designations.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus-circle me-1"></i>Add Designation
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Code</th>
                <th>Category</th>
                <th>Hierarchy</th>
                <th>Faculty Count</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($designations as $i => $designation)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $designation->name }}</td>
                <td><span class="badge bg-secondary">{{ $designation->code }}</span></td>
                <td><span class="badge bg-info">{{ ucfirst($designation->category) }}</span></td>
                <td>{{ $designation->hierarchy_level }}</td>
                <td>{{ $designation->faculties_count }}</td>
                <td>
                  <span class="badge bg-{{ $designation->status == 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($designation->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('hr.designations.show', $designation->id) }}" class="btn btn-xs btn-info" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('hr.designations.edit', $designation->id) }}" class="btn btn-xs btn-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No designations found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
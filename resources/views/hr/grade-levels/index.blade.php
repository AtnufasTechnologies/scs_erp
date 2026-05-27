@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Grade Levels</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Grade Levels</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.grade-levels.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus-circle me-1"></i>Add Grade Level
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
                <th>Salary Range</th>
                <th>Level Order</th>
                <th>Faculty Count</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($gradeLevels as $i => $level)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $level->name }}</td>
                <td><span class="badge bg-secondary">{{ $level->code }}</span></td>
                <td>
                  @if($level->min_salary && $level->max_salary)
                  ₹{{ number_format($level->min_salary, 0) }} - ₹{{ number_format($level->max_salary, 0) }}
                  @else
                  <span class="text-muted">Not set</span>
                  @endif
                </td>
                <td>{{ $level->level_order }}</td>
                <td>{{ $level->faculties_count }}</td>
                <td>
                  <span class="badge bg-{{ $level->status == 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($level->status) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('hr.grade-levels.show', $level->id) }}" class="btn btn-xs btn-info" title="View">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="{{ route('hr.grade-levels.edit', $level->id) }}" class="btn btn-xs btn-warning" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No grade levels found.</td>
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
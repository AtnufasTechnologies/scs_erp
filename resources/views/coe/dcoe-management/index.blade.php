@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Deputy COE Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">D.COE Management</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-4">
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createDcoeModal">
          <i class="fa fa-plus-circle"></i> Create Deputy COE
        </button>
      </div>
      <div class="col-lg-4 offset-lg-4">
        <input type="text" id="dcoeSearch" class="form-control" placeholder="Search by name or email...">
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- DCOE Users List --}}
    <div class="row" id="dcoeList">
      @forelse ($dcoeUsers as $dcoe)
      <div class="col-lg-4 mb-4 dcoe-card">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="card-title text-capitalize mb-1 dcoe-name">{{ $dcoe->name }}</h5>
                <small class="text-muted dcoe-email">{{ $dcoe->email }}</small>
              </div>
              <span class="badge {{ $dcoe->status === 'ACTIVE' ? 'bg-success' : 'bg-danger' }}">
                {{ $dcoe->status }}
              </span>
            </div>

            <div class="mb-2">
              <strong>Campus:</strong>
              @if($dcoe->campuspermission && $dcoe->campuspermission->campus)
              <span class="badge bg-info">{{ $dcoe->campuspermission->campus->name }}</span>
              @else
              <span class="badge bg-warning">Not Assigned</span>
              @endif
            </div>

            <div class="mb-3">
              <strong>Menu Permissions:</strong>
              <div class="mt-1">
                @foreach($dcoe->dcoeMenuPermissions as $perm)
                <span class="badge bg-secondary me-1 mb-1">{{ $menuItems[$perm->menu_slug] ?? $perm->menu_slug }}</span>
                @endforeach
              </div>
            </div>

            <div class="d-flex gap-2">
              <a href="{{ route('coe.dcoe.edit', $dcoe->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-edit"></i> Edit
              </a>
              <form action="{{ route('coe.dcoe.toggle-status', $dcoe->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $dcoe->status === 'ACTIVE' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                  <i class="fa {{ $dcoe->status === 'ACTIVE' ? 'fa-ban' : 'fa-check' }}"></i>
                  {{ $dcoe->status === 'ACTIVE' ? 'Deactivate' : 'Activate' }}
                </button>
              </form>
              <form action="{{ route('coe.dcoe.destroy', $dcoe->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this D.COE account?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="fa fa-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="text-center py-5">
          <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
          <p class="text-muted">No Deputy COE accounts created yet.</p>
        </div>
      </div>
      @endforelse
    </div>

    {{-- Create DCOE Modal --}}
    <div class="modal fade" id="createDcoeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Create Deputy COE Account</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('coe.dcoe.store') }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" name="email" class="form-control" required placeholder="Enter email address">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Password <span class="text-danger">*</span> <small>(min 6 characters)</small></label>
                  <input type="text" name="password" class="form-control" required minlength="6" placeholder="Enter password">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Assign Campus <span class="text-danger">*</span></label>
                  <select name="campus_id" class="form-control" required>
                    <option value="">Select Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Sidebar Menu Permissions <span class="text-danger">*</span></label>
                <p class="text-muted small mb-2">Select which menu items the D.COE can access. Only selected items will appear in their sidebar.</p>
                <div class="d-flex gap-2 mb-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">Select All</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">Deselect All</button>
                </div>
                <div class="row">
                  @foreach($menuItems as $slug => $label)
                  <div class="col-md-4 mb-2">
                    <div class="form-check">
                      <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" value="{{ $slug }}" id="menu_{{ $slug }}">
                      <label class="form-check-label" for="menu_{{ $slug }}">{{ $label }}</label>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Create D.COE Account</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  // Search functionality
  document.getElementById('dcoeSearch').addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.dcoe-card').forEach(card => {
      const name = card.querySelector('.dcoe-name').textContent.toLowerCase();
      const email = card.querySelector('.dcoe-email').textContent.toLowerCase();
      card.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
    });
  });

  // Select/Deselect all checkboxes
  function toggleAll(checked) {
    document.querySelectorAll('.menu-checkbox').forEach(cb => cb.checked = checked);
  }
</script>
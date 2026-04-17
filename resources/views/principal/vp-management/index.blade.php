@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">VP Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Vice-Principal Management</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-4">
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createVpModal">
          <i class="fa fa-plus-circle"></i> Create Vice-Principal
        </button>
      </div>
      <div class="col-lg-4 offset-lg-4">
        <input type="text" id="vpSearch" class="form-control" placeholder="Search by name or email...">
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

    {{-- VP Users List --}}
    <div class="row" id="vpList">
      @forelse ($vpUsers as $vp)
      <div class="col-lg-4 mb-4 vp-card">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="card-title text-capitalize mb-1 vp-name">{{ $vp->name }}</h5>
                <small class="text-muted vp-email">{{ $vp->email }}</small>
              </div>
              <span class="badge {{ $vp->status === 'ACTIVE' ? 'bg-success' : 'bg-danger' }}">
                {{ $vp->status }}
              </span>
            </div>

            <div class="mb-3">
              <strong>Campus Access:</strong>
              @if($vp->campuspermission && $vp->campuspermission->campus)
              <span class="badge bg-info">{{ $vp->campuspermission->campus->name }}</span>
              @else
              <span class="badge bg-warning">Not Assigned</span>
              @endif
            </div>

            <div class="d-flex gap-2 flex-wrap">
              {{-- Edit Campus --}}
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVpModal{{ $vp->id }}">
                <i class="fa fa-edit"></i> Campus
              </button>
              {{-- Toggle Status --}}
              <form action="{{ route('principal.vp.toggle-status', $vp->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $vp->status === 'ACTIVE' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                  <i class="fa {{ $vp->status === 'ACTIVE' ? 'fa-ban' : 'fa-check' }}"></i>
                  {{ $vp->status === 'ACTIVE' ? 'Deactivate' : 'Activate' }}
                </button>
              </form>
              {{-- Delete --}}
              <form action="{{ route('principal.vp.destroy', $vp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Vice-Principal account?')">
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

      {{-- Edit Campus Modal --}}
      <div class="modal fade" id="editVpModal{{ $vp->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Update Campus - {{ $vp->name }}</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('principal.vp.update', $vp->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Assign Campus <span class="text-danger">*</span></label>
                  <select name="campus_id" class="form-control" required>
                    <option value="">Select Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ ($vp->campuspermission && $vp->campuspermission->campus_id == $campus->id) ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Campus</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="text-center py-5">
          <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
          <p class="text-muted">No Vice-Principal accounts created yet.</p>
        </div>
      </div>
      @endforelse
    </div>

    {{-- Create VP Modal --}}
    <div class="modal fade" id="createVpModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Create Vice-Principal Account</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('principal.vp.store') }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Enter full name">
              </div>
              <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="Enter email address">
              </div>
              <div class="mb-3">
                <label class="form-label">Password <span class="text-danger">*</span> <small>(min 6 characters)</small></label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="Enter password">
              </div>
              <div class="mb-3">
                <label class="form-label">Assign Campus <span class="text-danger">*</span></label>
                <select name="campus_id" class="form-control" required>
                  <option value="">Select Campus</option>
                  @foreach($campuses as $campus)
                  <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Create Vice-Principal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.getElementById('vpSearch').addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.vp-card').forEach(card => {
      const name = card.querySelector('.vp-name').textContent.toLowerCase();
      const email = card.querySelector('.vp-email').textContent.toLowerCase();
      card.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
    });
  });
</script>
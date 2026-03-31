@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">MAC Whitelist</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.marks.index') }}">Marks</a></li>
            <li class="breadcrumb-item active" aria-current="page">MAC Whitelist</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-shield-alt me-2"></i>Device MAC Whitelist</h3>
                  <p class="text-white-50 mb-0">Manage authorized devices for marks entry. Only whitelisted MAC addresses can submit marks.</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                    <i class="fas fa-plus-circle me-2"></i>Add Device
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Stats -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-laptop text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Devices</p>
                  <h4 class="mb-0 fw-bold">{{ $whitelists->total() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Search -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.marks.whitelist') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Search by MAC address..." value="{{ request('search') }}">
            </div>
            <div class="col-md-6">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Search</button>
              <a href="{{ route('coe.marks.whitelist') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Whitelist Table -->
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
          <h5 class="mb-0 fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Whitelisted Devices</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>MAC Address</th>
                  <th>Added At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($whitelists as $index => $entry)
                <tr>
                  <td>{{ $whitelists->firstItem() + $index }}</td>
                  <td><code class="fs-6">{{ $entry->mac_address }}</code></td>
                  <td>{{ $entry->added_at ? $entry->added_at->format('d M Y, h:i A') : ($entry->created_at ? $entry->created_at->format('d M Y, h:i A') : '-') }}</td>
                  <td>
                    <form method="POST" action="{{ route('coe.marks.whitelist.destroy', $entry->id) }}" class="d-inline" onsubmit="return confirm('Remove this device from the whitelist?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">
                    <i class="fas fa-shield-alt fa-2x mb-2 d-block"></i>
                    No devices whitelisted yet. Click <strong>Add Device</strong> to get started.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($whitelists->hasPages())
        <div class="card-footer bg-white">
          {{ $whitelists->withQueryString()->links() }}
        </div>
        @endif
      </div>

    </div>
  </main>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-labelledby="addDeviceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('coe.marks.whitelist.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addDeviceModalLabel"><i class="fas fa-plus-circle me-2"></i>Add Device to Whitelist</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">MAC Address <span class="text-danger">*</span></label>
            <input type="text" name="mac_address" class="form-control" placeholder="XX:XX:XX:XX:XX:XX" value="{{ old('mac_address') }}" required maxlength="17" pattern="^([0-9A-Fa-f]{2}[:\-]){5}([0-9A-Fa-f]{2})$">
            <div class="form-text">Format: XX:XX:XX:XX:XX:XX (e.g., A1:B2:C3:D4:E5:F6)</div>
          </div>
          <div class="alert alert-warning border-0 mb-0">
            <small><i class="fas fa-info-circle me-1"></i>Only devices with whitelisted MAC addresses will be allowed to enter or modify marks.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add to Whitelist</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')
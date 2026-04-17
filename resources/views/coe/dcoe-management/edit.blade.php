@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Edit Deputy COE</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.dcoe.index') }}">D.COE Management</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
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

    <div class="row mt-4">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
          <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
            <h5 class="mb-0">Edit D.COE: {{ $dcoeUser->name }}</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('coe.dcoe.update', $dcoeUser->id) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input type="text" class="form-control" value="{{ $dcoeUser->name }}" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="text" class="form-control" value="{{ $dcoeUser->email }}" disabled>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Assign Campus <span class="text-danger">*</span></label>
                <select name="campus_id" class="form-control" required>
                  <option value="">Select Campus</option>
                  @foreach($campuses as $campus)
                  <option value="{{ $campus->id }}" {{ ($dcoeUser->campuspermission && $dcoeUser->campuspermission->campus_id == $campus->id) ? 'selected' : '' }}>
                    {{ $campus->name }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Sidebar Menu Permissions <span class="text-danger">*</span></label>
                <p class="text-muted small mb-2">Select which menu items the D.COE can access.</p>
                <div class="d-flex gap-2 mb-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">Select All</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">Deselect All</button>
                </div>
                <div class="row">
                  @foreach($menuItems as $slug => $label)
                  <div class="col-md-4 mb-2">
                    <div class="form-check">
                      <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" value="{{ $slug }}" id="menu_{{ $slug }}" {{ in_array($slug, $assignedMenus) ? 'checked' : '' }}>
                      <label class="form-check-label" for="menu_{{ $slug }}">{{ $label }}</label>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Permissions</button>
                <a href="{{ route('coe.dcoe.index') }}" class="btn btn-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  function toggleAll(checked) {
    document.querySelectorAll('.menu-checkbox').forEach(cb => cb.checked = checked);
  }
</script>
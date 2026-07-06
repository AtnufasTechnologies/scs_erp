@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <h3 class="text-uppercase mb-0">Shift Master</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
      <i class="fa fa-plus-circle"></i> Add Shift
    </button>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="exportTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Slug</th>
              <th>Order</th>
              <th>Status</th>
              <th>Type</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->title }}</td>
              <td><code>{{ $item->slug }}</code></td>
              <td>{{ $item->sort_order }}</td>
              <td>
                <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                  {{ $item->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <span class="badge {{ $item->is_system ? 'bg-info text-dark' : 'bg-light text-dark border' }}">
                  {{ $item->is_system ? 'System' : 'Custom' }}
                </span>
              </td>
              <td class="d-flex gap-2">
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $item->id }}">
                  Edit
                </button>

                <form action="{{ route('admin.shift-master.toggle', $item->id) }}" method="post">
                  @csrf
                  <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                    {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editShiftModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="{{ route('admin.shift-master.update', $item->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" value="{{ $item->title }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" value="{{ $item->slug }}" disabled>
                        <small class="text-muted">Slug is locked to avoid breaking existing mapped data.</small>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" min="0" value="{{ $item->sort_order }}">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted">No shifts available.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Shift</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.shift-master.store') }}" method="post">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required placeholder="Example: Evening">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug (optional)</label>
            <input type="text" class="form-control" name="slug" placeholder="example-evening">
            <small class="text-muted">Leave empty to auto-generate from title.</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" min="0" value="100">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Create Shift</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
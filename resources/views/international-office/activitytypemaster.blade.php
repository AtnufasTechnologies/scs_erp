@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Activity Type Master</h4>
      <small class="text-muted">Create and manage International Office activity types.</small>
    </div>
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

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">Add Activity Type</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.activity-type-master.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Example: Faculty Exchange">
          </div>
          <div class="col-md-3">
            <label class="form-label">Slug (optional)</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="faculty-exchange">
            <small class="text-muted">If empty, auto-generated from title.</small>
          </div>
          <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', 100) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" value="{{ old('description') }}" maxlength="1000" placeholder="Optional short note">
          </div>
        </div>
        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Create Type
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Existing Activity Types</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Slug</th>
              <th>Description</th>
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
              <td>{{ $item->description ?: '-' }}</td>
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
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editActivityTypeModal{{ $item->id }}">
                  Edit
                </button>
                <form action="{{ route('international-office.activity-type-master.toggle', $item->id) }}" method="POST">
                  @csrf
                  <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                    {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
                @if(!$item->is_system)
                <form action="{{ route('international-office.activity-type-master.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this activity type?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
                @endif
              </td>
            </tr>

            <div class="modal fade" id="editActivityTypeModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Activity Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('international-office.activity-type-master.update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ $item->title }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Slug</label>
                        @if($item->is_system)
                        <input type="text" class="form-control" value="{{ $item->slug }}" disabled>
                        <small class="text-muted">System type slug is locked.</small>
                        @else
                        <input type="text" name="slug" class="form-control" value="{{ $item->slug }}">
                        @endif
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="1000">{{ $item->description }}</textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" min="0" name="sort_order" class="form-control" value="{{ $item->sort_order }}">
                      </div>
                      <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                          <option value="1" {{ $item->is_active ? 'selected' : '' }}>Active</option>
                          <option value="0" {{ !$item->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
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
              <td colspan="8" class="text-center text-muted">No activity types found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


@include('includes.footer')
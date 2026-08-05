@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Concern Category Master</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addConcernCategoryModal">Add Concern Category</button>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Categories</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($categories as $index => $category)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $category->name }}</td>
                <td>{{ $category->description ?: '-' }}</td>
                <td>{{ $category->sort_order }}</td>
                <td>
                  @if($category->is_active)
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editConcernCategoryModal{{ $category->id }}">Edit</button>
                    <form action="{{ route('dean.concern-categories.toggle', $category->id) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-warning">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                  </div>
                </td>
              </tr>

              <div class="modal fade" id="editConcernCategoryModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Concern Category</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('dean.concern-categories.update', $category->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-body">
                        <div class="mb-2">
                          <label class="form-label">Name</label>
                          <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="mb-2">
                          <label class="form-label">Description</label>
                          <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
                        </div>
                        <div class="mb-2">
                          <label class="form-label">Sort Order</label>
                          <input type="number" name="sort_order" class="form-control" min="0" value="{{ $category->sort_order }}">
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No concern categories found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addConcernCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Concern Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('dean.concern-categories.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" min="0" value="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
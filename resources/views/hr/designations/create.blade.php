@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Designation Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.designations.index') }}">Designations</a></li>
            <li class="breadcrumb-item active">Create</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.designations.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i>Please fix the errors below.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Designation</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('hr.designations.store') }}" method="POST">
          @csrf

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Designation Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name') }}" maxlength="255" required autofocus>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Code</label>
              <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                value="{{ old('code') }}" maxlength="50" placeholder="e.g., PROF, ASSOC_PROF">
              @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Unique short code for identification</small>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="">-- Select Category --</option>
                <option value="teaching" {{ old('category') == 'teaching' ? 'selected' : '' }}>Teaching</option>
                <option value="non-teaching" {{ old('category') == 'non-teaching' ? 'selected' : '' }}>Non-Teaching</option>
                <option value="administrative" {{ old('category') == 'administrative' ? 'selected' : '' }}>Administrative</option>
                <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="support" {{ old('category') == 'support' ? 'selected' : '' }}>Support</option>
              </select>
              @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Hierarchy Level <span class="text-danger">*</span></label>
              <input type="number" name="hierarchy_level" class="form-control @error('hierarchy_level') is-invalid @enderror"
                value="{{ old('hierarchy_level', 0) }}" min="0" required>
              @error('hierarchy_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Lower = Higher rank</small>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Create Designation
            </button>
            <a href="{{ route('hr.designations.index') }}" class="btn btn-secondary ms-2">
              <i class="fas fa-times me-1"></i>Cancel
            </a>
          </div>

        </form>
      </div>
    </div>

  </main>
</div>
@include('includes.footer')
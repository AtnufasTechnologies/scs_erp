@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Grade Level Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.grade-levels.index') }}">Grade Levels</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.grade-levels.show', $gradeLevel) }}" class="btn btn-info btn-sm">
          <i class="fas fa-eye me-1"></i>View
        </a>
        <a href="{{ route('hr.grade-levels.index') }}" class="btn btn-secondary btn-sm">
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
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Grade Level</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('hr.grade-levels.update', $gradeLevel) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Grade Level Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $gradeLevel->name) }}" maxlength="255" required autofocus>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Code</label>
              <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                value="{{ old('code', $gradeLevel->code) }}" maxlength="50" placeholder="e.g., L14, L13, L12">
              @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Unique short code for identification</small>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Minimum Salary</label>
              <input type="number" name="min_salary" class="form-control @error('min_salary') is-invalid @enderror"
                value="{{ old('min_salary', $gradeLevel->min_salary) }}" min="0" step="0.01" placeholder="0.00">
              @error('min_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Maximum Salary</label>
              <input type="number" name="max_salary" class="form-control @error('max_salary') is-invalid @enderror"
                value="{{ old('max_salary', $gradeLevel->max_salary) }}" min="0" step="0.01" placeholder="0.00">
              @error('max_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Must be ≥ min salary</small>
            </div>
            <div class="col-md-4">
              <label class="form-label">Level Order <span class="text-danger">*</span></label>
              <input type="number" name="level_order" class="form-control @error('level_order') is-invalid @enderror"
                value="{{ old('level_order', $gradeLevel->level_order) }}" min="0" required>
              @error('level_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Lower = Higher grade</small>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', $gradeLevel->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $gradeLevel->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $gradeLevel->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Update Grade Level
            </button>
            <a href="{{ route('hr.grade-levels.show', $gradeLevel) }}" class="btn btn-info ms-2">
              <i class="fas fa-eye me-1"></i>View
            </a>
            <a href="{{ route('hr.grade-levels.index') }}" class="btn btn-secondary ms-2">
              <i class="fas fa-times me-1"></i>Cancel
            </a>
          </div>

        </form>
      </div>
    </div>

  </main>
</div>
@include('includes.footer')
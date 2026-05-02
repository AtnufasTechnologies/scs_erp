@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Mentorship</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.index') }}">My Groups</a></li>
            <li class="breadcrumb-item active">{{ isset($group) ? 'Edit Group' : 'New Group' }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="fw-bold mb-0">
                <i class="bx bx-group text-primary me-2"></i>
                {{ isset($group) ? 'Edit Mentorship Group' : 'Create Mentorship Group' }}
              </h5>
            </div>
            <div class="card-body">
              @if(isset($group))
              <form method="POST" action="{{ route('faculty.mentorship.group.update', $group->id) }}">
                @method('PUT')
                @else
                <form method="POST" action="{{ route('faculty.mentorship.group.store') }}">
                  @endif
                  @csrf

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Group Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                      value="{{ old('name', $group->name ?? '') }}" required placeholder="e.g. 2023 Batch - Section A">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                      rows="3" placeholder="Brief description of this mentorship group...">{{ old('description', $group->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>


                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Batch</label>
                      <select name="academic_year" class="form-select">
                        <option value="">Select Batch</option>
                        @foreach($batches ?? [] as $batch)
                        <option value="{{ $batch->batch_name ?? $batch->id }}" {{ old('academic_year', $group->academic_year ?? '') == ($batch->batch_name ?? $batch->id) ? 'selected' : '' }}>
                          {{ $batch->batch_name ?? ($batch->id) }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Semester</label>
                      <select name="semester" class="form-select">
                        <option value="">Select Semester</option>
                        @foreach($semesters ?? [] as $semester)
                        <option value="{{ $semester->name ?? $semester->id }}" {{ old('semester', $group->semester ?? '') == ($semester->name ?? $semester->id) ? 'selected' : '' }}>
                          {{ $semester->name ?? ($semester->id) }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  @isset($group)
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                      <option value="active" {{ $group->status === 'active' ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ $group->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                      <option value="archived" {{ $group->status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                  </div>
                  @endisset

                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                      <i class="bx bx-save me-1"></i> {{ isset($group) ? 'Update Group' : 'Create Group' }}
                    </button>
                    <a href="{{ route('faculty.mentorship.index') }}" class="btn btn-outline-secondary">Cancel</a>
                  </div>
                </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
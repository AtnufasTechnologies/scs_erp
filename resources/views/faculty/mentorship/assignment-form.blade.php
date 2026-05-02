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
            <li class="breadcrumb-item"><a href="{{ route('faculty.mentorship.group.show', $group->id) }}">{{ $group->name }}</a></li>
            <li class="breadcrumb-item active">New Assignment</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="fw-bold mb-0"><i class="bx bx-task text-warning me-2"></i>Create Assignment</h5>
              <small class="text-muted">Group: {{ $group->name }}</small>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('faculty.mentorship.assignment.store', $group->id) }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                  <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                  <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" required placeholder="Assignment title...">
                  @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Description / Instructions <span class="text-danger">*</span></label>
                  <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                    rows="5" required placeholder="Describe the assignment in detail...">{{ old('description') }}</textarea>
                  @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                      value="{{ old('due_date') }}" min="{{ now()->addDay()->format('Y-m-d') }}">
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Maximum Marks <span class="text-danger">*</span></label>
                    <input type="number" name="max_marks" class="form-control @error('max_marks') is-invalid @enderror"
                      value="{{ old('max_marks', 100) }}" required min="1" max="1000">
                    @error('max_marks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Attachment (PDF/Doc/Image)</label>
                  <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                  <div class="form-text">Max 5MB</div>
                  @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-warning text-dark"><i class="bx bx-save me-1"></i>Create Assignment</button>
                  <a href="{{ route('faculty.mentorship.group.show', $group->id) }}" class="btn btn-outline-secondary">Cancel</a>
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
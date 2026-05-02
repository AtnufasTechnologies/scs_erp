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
            <li class="breadcrumb-item active">New Session</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="fw-bold mb-0"><i class="bx bx-calendar-plus text-success me-2"></i>Schedule Mentorship Session</h5>
              <small class="text-muted">Group: {{ $group->name }}</small>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('faculty.mentorship.session.store', $group->id) }}">
                @csrf

                <div class="mb-3">
                  <label class="form-label fw-semibold">Session Title <span class="text-danger">*</span></label>
                  <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" required placeholder="e.g. Monthly Academic Review">
                  @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Agenda / Topics to Cover</label>
                  <textarea name="agenda" class="form-control @error('agenda') is-invalid @enderror"
                    rows="3" placeholder="List the topics to be discussed...">{{ old('agenda') }}</textarea>
                  @error('agenda')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Session Date <span class="text-danger">*</span></label>
                    <input type="date" name="session_date" class="form-control @error('session_date') is-invalid @enderror"
                      value="{{ old('session_date', now()->format('Y-m-d')) }}" required>
                    @error('session_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Mode <span class="text-danger">*</span></label>
                  <select name="mode" class="form-select">
                    <option value="in-person" {{ old('mode') === 'in-person' ? 'selected' : '' }}>In-Person</option>
                    <option value="online" {{ old('mode') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="hybrid" {{ old('mode') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                  </select>
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-success"><i class="bx bx-save me-1"></i>Create & Mark Attendance</button>
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
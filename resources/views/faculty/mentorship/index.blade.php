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
            <li class="breadcrumb-item active">My Mentorship Groups</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-0">Mentorship Groups</h4>
          <p class="text-muted mb-0">Manage your mentees, sessions and assignments</p>
        </div>
        <a href="{{ route('faculty.mentorship.group.create') }}" class="btn btn-primary">
          <i class="bx bx-plus"></i> New Group
        </a>
      </div>

      @if($groups->isEmpty())
      <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
          <i class="bx bx-group" style="font-size:60px;color:#dee2e6;"></i>
          <h5 class="mt-3 text-muted">No mentorship groups yet</h5>
          <p class="text-muted">Create your first group to start mentoring students.</p>
          <a href="{{ route('faculty.mentorship.group.create') }}" class="btn btn-primary mt-2">
            <i class="bx bx-plus"></i> Create Group
          </a>
        </div>
      </div>
      @else
      <div class="row g-4">
        @foreach($groups as $group)
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="fw-bold mb-0">{{ $group->name }}</h5>
                <span class="badge bg-{{ $group->status === 'active' ? 'success' : ($group->status === 'archived' ? 'secondary' : 'warning') }}">
                  {{ ucfirst($group->status) }}
                </span>
              </div>
              @if($group->description)
              <p class="text-muted small mb-3">{{ Str::limit($group->description, 80) }}</p>
              @endif
              @if($group->academic_year || $group->semester)
              <p class="text-muted small mb-3">
                @if($group->academic_year)<span class="me-2"><i class="bx bx-calendar"></i> {{ $group->academic_year }}</span>@endif
                @if($group->semester)<span><i class="bx bx-book"></i> Sem {{ $group->semester }}</span>@endif
              </p>
              @endif
              <div class="row text-center g-2 mb-3">
                <div class="col-4">
                  <div class="bg-secondary bg-opacity-10 rounded p-2">
                    <div class="fw-bold text-primary">{{ $group->students_count }}</div>
                    <div class="text-muted" style="font-size:11px;">Students</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-secondary bg-opacity-10 rounded p-2">
                    <div class="fw-bold text-success">{{ $group->sessions_count }}</div>
                    <div class="text-muted" style="font-size:11px;">Sessions</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-secondary bg-opacity-10 rounded p-2">
                    <div class="fw-bold text-warning">{{ $group->assignments_count }}</div>
                    <div class="text-muted" style="font-size:11px;">Assignments</div>
                  </div>
                </div>
              </div>
              <a href="{{ route('faculty.mentorship.group.show', $group->id) }}" class="btn btn-sm btn-outline-primary w-100">
                <i class="bx bx-show"></i> View Group
              </a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')
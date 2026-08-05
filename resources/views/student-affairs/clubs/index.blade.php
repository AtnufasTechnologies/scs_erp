@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Clubs, Cells & Associations</h3>

      <div class="card mb-3 shadow-sm">
        <div class="card-header">Create Club/Cell/Association</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.clubs.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3"><input name="name" class="form-control" placeholder="Name" required></div>
            <div class="col-md-2">
              <select name="club_type" class="form-select" required>
                <option value="club">Club</option>
                <option value="cell">Cell</option>
                <option value="association">Association</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="faculty_coordinator_id" class="dselect-example">
                <option value="">Faculty Coordinator</option>
                @foreach($faculty as $f)
                <option value="{{ $f->id }}">{{$f->USER_CODE}} - {{ trim(($f->FIRST_NAME ?? '').' '.($f->LAST_NAME ?? '')) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2"><input type="date" name="established_on" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Create</button></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Directory</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Coordinator</th>
                <th>Members</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($clubs as $club)
              <tr>
                <td>{{ $club->name }}</td>
                <td>{{ ucfirst($club->club_type) }}</td>
                <td>{{ trim(($club->coordinator->FIRST_NAME ?? '').' '.($club->coordinator->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ $club->memberships_count }}</td>
                <td>{{ $club->status }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('dean.clubs.show', $club->id) }}">Members</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No clubs/cells/associations found for this campus.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $clubs->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
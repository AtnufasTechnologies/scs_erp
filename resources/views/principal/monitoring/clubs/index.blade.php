@include('includes.header')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(120deg, #a16207 0%, #1d4ed8 100%);">
        <div class="card-body text-white py-4">
          <h3 class="mb-1">Clubs and Cells Monitor</h3>
          <p class="mb-0 opacity-75">View-only directory of clubs, cells, and associations with membership visibility.</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">Directory</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered align-middle">
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
                <td>{{ ucfirst((string) $club->club_type) }}</td>
                <td>{{ trim(($club->coordinator->FIRST_NAME ?? '') . ' ' . ($club->coordinator->LAST_NAME ?? '')) ?: '-' }}</td>
                <td>{{ (int) $club->memberships_count }}</td>
                <td>{{ ucfirst((string) ($club->status ?? '-')) }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('principal.monitoring.clubs.show', $club->id) }}">View Members</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No clubs/cells/associations found.</td>
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
@include('includes.header')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="container-fluid py-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $club->name }} Members</h3>
        <a href="{{ route('principal.monitoring.clubs') }}" class="btn btn-outline-secondary">Back to Clubs/Cells</a>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">Membership Directory (View-only)</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered align-middle">
            <thead>
              <tr>
                <th>Student</th>
                <th>Roll No</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Left</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($memberships as $m)
              <tr>
                <td>{{ ($m->student->first_name ?? '') . ' ' . ($m->student->last_name ?? '') }}</td>
                <td>{{ $m->student->roll_no ?? '-' }}</td>
                <td>{{ $m->role_title ?? '-' }}</td>
                <td>{{ optional($m->joined_on)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ optional($m->left_on)->format('d-M-Y') ?: '-' }}</td>
                <td>{{ ucfirst((string) ($m->status ?? '-')) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No members found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $memberships->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
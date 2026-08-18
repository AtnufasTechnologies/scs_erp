@include('includes.header')
@include('admin.sidebar')

<div class="wrapper">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">ITCELL</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Active Logged-in Users</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h5 class="mb-1 fw-bold">Currently Logged-in Users</h5>
            <div class="text-muted small">
              Session Driver: {{ $sessionDriver }} | Session Lifetime: {{ (int) $sessionLifetimeMinutes }} min
            </div>
          </div>
          <span class="badge bg-primary">Active Sessions: {{ collect($activeUsers ?? [])->count() }}</span>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @foreach(($warnings ?? []) as $warning)
      <div class="alert alert-warning">{{ $warning }}</div>
      @endforeach

      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">#</th>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role(s)</th>
                  <th>Roll No</th>
                  <th>Status</th>
                  <th>Last Activity</th>
                  <th>Session ID</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($activeUsers ?? []) as $index => $row)
                <tr>
                  <td class="ps-4 fw-semibold">{{ $index + 1 }}</td>
                  <td>
                    <div class="fw-semibold">{{ $row['name'] ?? '-' }}</div>
                    <small class="text-muted">UID: {{ (int) ($row['user_id'] ?? 0) }}</small>
                  </td>
                  <td>{{ $row['email'] ?? '-' }}</td>
                  <td>
                    @php
                    $roles = collect($row['roles'] ?? [])->filter()->values();
                    @endphp
                    @if($roles->isEmpty())
                    <span class="text-muted">-</span>
                    @else
                    {{ $roles->implode(', ') }}
                    @endif
                  </td>
                  <td>{{ $row['roll_no'] ?? '-' }}</td>
                  <td>
                    @php
                    $status = strtoupper(trim((string) ($row['status'] ?? '-')));
                    @endphp
                    <span class="badge {{ $status === 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">{{ $status }}</span>
                  </td>
                  <td>{{ $row['last_activity_at'] ?? '-' }}</td>
                  <td><small class="text-muted">{{ $row['session_id'] ?? '-' }}</small></td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    No active logged-in users found for the current session driver.
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
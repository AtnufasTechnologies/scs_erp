@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Training Analytics</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.index') }}">Training & Placement</a></li>
            <li class="breadcrumb-item active" aria-current="page">Analytics</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-bold">Training Completion Analytics</h6>
          <a href="{{ route('tpo.training-placement.index') }}" class="btn btn-sm btn-outline-secondary">Back to Module</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Training</th>
                  <th>Target Roles</th>
                  <th>Assigned Users</th>
                  <th>Completed Users</th>
                  <th>Completion Rate</th>
                </tr>
              </thead>
              <tbody>
                @forelse($analytics as $index => $item)
                @php
                $rate = $item['completion_rate'];
                $barClass = $rate >= 80 ? 'bg-success' : ($rate >= 40 ? 'bg-warning' : 'bg-danger');
                @endphp
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $item['title'] }}</td>
                  <td>{{ $item['target_roles']->map(fn($role) => ucfirst(str_replace('-', ' ', $role)))->implode(', ') }}</td>
                  <td>{{ $item['assigned_users'] }}</td>
                  <td>{{ $item['completed_users'] }}</td>
                  <td>
                    <span class="badge {{ $barClass }}">{{ $rate }}%</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No training analytics available.</td>
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
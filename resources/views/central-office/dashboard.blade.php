@include('includes.header')
@include('central-office.sidebar')

<div class="container-fluid">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Central Office Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Active Students</div>
            <div class="display-6 fw-bold text-success">{{ $activeStudents }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Left Students</div>
            <div class="display-6 fw-bold text-danger">{{ $leftStudents }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Total Employees (Siliguri Campus)</div>
            <div class="display-6 fw-bold text-dark">{{ $totalEmployees }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Total Batches</div>
            <div class="display-6 fw-bold text-primary">{{ $totalBatches }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Recent Admission Registrations</h6>
        <a href="{{ route('central-office.admissions.batch-wise') }}" class="btn btn-sm btn-outline-primary">View Batch-wise Data</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Batch</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAdmissions as $i => $item)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) ?: 'N/A' }}</td>
                <td>{{ $item->application_type ?: 'N/A' }}</td>
                <td>{{ $item->batch ?: 'N/A' }}</td>
                <td>{{ optional($item->created_at)->format('d M Y, h:i A') ?? 'N/A' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-4">No admission records found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
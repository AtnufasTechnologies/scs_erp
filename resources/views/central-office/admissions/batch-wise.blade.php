@include('includes.header')
@include('central-office.sidebar')

<div class="container-fluid">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Central Office</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('central-office.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Admission Data Batch-wise</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <form method="GET" action="{{ route('central-office.admissions.batch-wise') }}" class="row g-2">
          <div class="col-md-4">
            <label class="form-label">Batch</label>
            <select class="form-select" name="batch">
              <option value="">All Batches</option>
              @foreach($batchOptions as $batchOption)
              <option value="{{ $batchOption }}" {{ $batch === $batchOption ? 'selected' : '' }}>{{ $batchOption }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Apply</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Batch-wise Admission Summary</h6>
        <a href="{{ route('central-office.admissions.batch-wise.export', ['batch' => $batch]) }}" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead>
              <tr>
                <th>Batch</th>
                <th>Total Registrations</th>
                <th>UG Registrations</th>
                <th>PG Registrations</th>
                <th>Applications Submitted</th>
                <th>Phase 1</th>
                <th>Phase 2</th>
                <th>Enrolled</th>
              </tr>
            </thead>
            <tbody>
              @forelse($admissionByBatch as $row)
              <tr>
                <td>{{ $row->batch_name }}</td>
                <td>{{ (int) $row->total_registrations }}</td>
                <td>{{ (int) $row->ug_registrations }}</td>
                <td>{{ (int) $row->pg_registrations }}</td>
                <td>{{ (int) $row->submitted_applications }}</td>
                <td>{{ (int) $row->phase1_count }}</td>
                <td>{{ (int) $row->phase2_count }}</td>
                <td>{{ (int) $row->enrolled_count }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center py-4">No admission data found.</td>
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
@include('includes.header')
@include('admin.accounts.sidebar')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0"><span class="text-uppercase">Full Fee Exemption History</span></h3>
  <a href="{{ route('full.fee.exemptions') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fa fa-arrow-left me-1"></i>Back to Exemption Console
  </a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" action="{{ route('full.fee.exemptions.history') }}" class="row g-2 align-items-end">
      <div class="col-lg-3">
        <label class="form-label">Student</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Roll no or name">
      </div>
      <div class="col-lg-2">
        <label class="form-label">Batch</label>
        <select name="batch_filter" class="form-select">
          <option value="">All</option>
          @foreach($batches as $batch)
          <option value="{{ $batch->id }}" {{ (string) request('batch_filter') === (string) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Revoked</option>
        </select>
      </div>
      <div class="col-lg-2">
        <label class="form-label">From</label>
        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
      </div>
      <div class="col-lg-2">
        <label class="form-label">To</label>
        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
      </div>
      <div class="col-lg-1 d-grid">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
      </div>
      <div class="col-lg-2 d-grid">
        <a href="{{ route('full.fee.exemptions.history') }}" class="btn btn-light border">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Roll No</th>
            <th>Student</th>
            <th>Batch</th>
            <th>Academic Pathway</th>
            <th>Degree Track</th>
            <th>Reason</th>
            <th>Approved By</th>
            <th>Approved At</th>
            <th>Status</th>
            <th>Revoked By</th>
            <th>Revoked At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($histories as $index => $row)
          <tr>
            <td>{{ $histories->firstItem() + $index }}</td>
            <td class="text-uppercase">{{ $row->student->roll_no ?? 'N/A' }}</td>
            <td>{{ trim(($row->student->first_name ?? '') . ' ' . ($row->student->last_name ?? '')) }}</td>
            <td>{{ $row->student->batchmaster->batch_name ?? 'N/A' }}</td>
            <td>{{ $row->student->academicpathway->name ?? 'N/A' }}</td>
            <td>{{ $row->student->degreetrack->name ?? 'N/A' }}</td>
            <td>{{ $row->reason }}</td>
            <td>{{ $row->approver->name ?? 'N/A' }}</td>
            <td>{{ $row->approved_at ? $row->approved_at->format('d-M-Y H:i') : 'N/A' }}</td>
            <td>
              @if($row->is_active)
              <span class="badge bg-success">Active</span>
              @else
              <span class="badge bg-secondary">Revoked</span>
              @endif
            </td>
            <td>{{ $row->revoker->name ?? 'N/A' }}</td>
            <td>{{ $row->revoked_at ? $row->revoked_at->format('d-M-Y H:i') : 'N/A' }}</td>
            <td>
              @if($row->is_active)
              <form action="{{ route('revoke.full.fee.exemption', $row->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this full fee exemption?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="fa fa-ban me-1"></i>Revoke
                </button>
              </form>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="13" class="text-center">No full-fee exemption history found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $histories->links('pagination::bootstrap-5') }}
  </div>
</div>

@include('includes.footer')
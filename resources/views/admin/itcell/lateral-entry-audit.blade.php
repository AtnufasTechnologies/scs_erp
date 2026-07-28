@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1">Lateral Entry Audit Trail</h4>
      <p class="text-muted mb-0">Track who added a student via lateral entry and when it happened.</p>
    </div>
    <a href="{{ route('itcell.lateral-entry.index') }}" class="btn btn-outline-secondary">Back to Form</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Student</th>
              <th>Roll No</th>
              <th>Added By</th>
              <th>Entry Type</th>
              <th>Remarks</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->student->first_name ?? '' }} {{ $log->student->last_name ?? '' }}</td>
              <td>{{ $log->student->roll_no ?? 'N/A' }}</td>
              <td>{{ $log->user->name ?? 'System' }}</td>
              <td>{{ ucfirst(str_replace('-', ' ', $log->entry_type)) }}</td>
              <td>{{ $log->remarks }}</td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-muted">No audit logs found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $logs->links('vendor.pagination.bootstrap-5') }}
    </div>
  </div>
</div>

@include('includes.footer')
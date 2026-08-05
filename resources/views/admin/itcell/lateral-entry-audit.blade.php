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
              <th>Application Form</th>
              <th>Snapshot</th>
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
              <td>
                @if(!empty($log->application_form_path))
                <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ Storage::disk('public')->url($log->application_form_path) }}">View File</a>
                @elseif(!empty($log->sourced_application_code))
                <span class="badge bg-light text-dark">{{ $log->sourced_application_code }}</span>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @php($snapshot = $log->application_snapshot ?? [])
                @if(!empty($snapshot))
                <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#auditSnap{{ $log->id }}" aria-expanded="false" aria-controls="auditSnap{{ $log->id }}">View</button>
                @else
                <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '' }}</td>
            </tr>
            @if(!empty($snapshot))
            <tr class="collapse" id="auditSnap{{ $log->id }}">
              <td colspan="9" class="bg-light">
                <div class="row g-2 small">
                  <div class="col-md-3"><strong>City:</strong> {{ data_get($snapshot, 'address.city', '-') }}</div>
                  <div class="col-md-3"><strong>District:</strong> {{ data_get($snapshot, 'address.district', '-') }}</div>
                  <div class="col-md-3"><strong>State:</strong> {{ data_get($snapshot, 'address.state', '-') }}</div>
                  <div class="col-md-3"><strong>Pincode:</strong> {{ data_get($snapshot, 'address.pincode', '-') }}</div>
                  <div class="col-md-2"><strong>X%:</strong> {{ data_get($snapshot, 'academic.x_percentage', '-') }}</div>
                  <div class="col-md-2"><strong>XII%:</strong> {{ data_get($snapshot, 'academic.xii_percentage', '-') }}</div>
                  <div class="col-md-2"><strong>UG%:</strong> {{ data_get($snapshot, 'academic.ug_percentage', '-') }}</div>
                  <div class="col-md-1"><strong>S1:</strong> {{ data_get($snapshot, 'academic.sgpa1', '-') }}</div>
                  <div class="col-md-1"><strong>S2:</strong> {{ data_get($snapshot, 'academic.sgpa2', '-') }}</div>
                  <div class="col-md-1"><strong>S3:</strong> {{ data_get($snapshot, 'academic.sgpa3', '-') }}</div>
                  <div class="col-md-1"><strong>S4:</strong> {{ data_get($snapshot, 'academic.sgpa4', '-') }}</div>
                  <div class="col-md-1"><strong>S5:</strong> {{ data_get($snapshot, 'academic.sgpa5', '-') }}</div>
                  <div class="col-md-1"><strong>S6:</strong> {{ data_get($snapshot, 'academic.sgpa6', '-') }}</div>
                </div>
              </td>
            </tr>
            @endif
            @empty
            <tr>
              <td colspan="9" class="text-muted">No audit logs found.</td>
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
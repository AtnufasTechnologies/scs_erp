@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Submit IQAC Report</h4>
      <small class="text-muted">Upload and manage IQAC submissions for this event.</small>
    </div>
    <a href="{{ route('international-office.events.index') }}" class="btn btn-outline-secondary btn-sm">Back to Events</a>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3"><strong>Activity:</strong> {{ optional($event->activityType)->title ?: '-' }}</div>
        <div class="col-md-5"><strong>Institution:</strong> {{ $event->visiting_institution_name }}</div>
        <div class="col-md-4"><strong>Trip:</strong> {{ optional($event->trip_start_date)->format('d M Y') }} - {{ optional($event->trip_end_date)->format('d M Y') }}</div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">New IQAC Submission</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.events.iqac-reports.store', $event->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Report Title</label>
            <input type="text" name="report_title" class="form-control" value="{{ old('report_title') }}" maxlength="255" placeholder="e.g. Exchange Program Completion Report">
          </div>
          <div class="col-md-3">
            <label class="form-label">Submitted On <span class="text-danger">*</span></label>
            <input type="date" name="submitted_on" class="form-control" value="{{ old('submitted_on', now()->format('Y-m-d')) }}" required>
          </div>
          <div class="col-md-5">
            <label class="form-label">Report File <span class="text-danger">*</span></label>
            <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png" required>
            <small class="text-muted">Allowed: pdf, doc/docx, ppt/pptx, xls/xlsx, jpg/png (max 10MB)</small>
          </div>
          <div class="col-md-12">
            <label class="form-label">Submission Note</label>
            <textarea name="submission_note" class="form-control" rows="2" maxlength="2000">{{ old('submission_note') }}</textarea>
          </div>
        </div>
        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Submit to IQAC</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Submitted Reports</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Submitted On</th>
              <th>Title</th>
              <th>File</th>
              <th>Note</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ optional($report->submitted_on)->format('d M Y') }}</td>
              <td>{{ $report->report_title ?: '-' }}</td>
              <td><a href="{{ asset('storage/' . $report->report_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a></td>
              <td>{{ $report->submission_note ?: '-' }}</td>
              <td class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editIqacReportModal{{ $report->id }}">Edit</button>
                <form method="POST" action="{{ route('international-office.events.iqac-reports.destroy', [$event->id, $report->id]) }}" onsubmit="return confirm('Delete this IQAC report?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editIqacReportModal{{ $report->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit IQAC Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('international-office.events.iqac-reports.update', [$event->id, $report->id]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Report Title</label>
                        <input type="text" name="report_title" class="form-control" value="{{ $report->report_title }}" maxlength="255">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Submitted On <span class="text-danger">*</span></label>
                        <input type="date" name="submitted_on" class="form-control" value="{{ optional($report->submitted_on)->format('Y-m-d') }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Replace Report File</label>
                        <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png">
                      </div>
                      <div class="mb-0">
                        <label class="form-label">Submission Note</label>
                        <textarea name="submission_note" class="form-control" rows="3" maxlength="2000">{{ $report->submission_note }}</textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted">No IQAC reports submitted yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
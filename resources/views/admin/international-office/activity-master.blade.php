@include('includes.header')
@include('admin.sidebar')

@php
use Illuminate\Support\Facades\Storage;

$resolveDocumentUrl = function ($path) {
if (!$path) {
return null;
}

if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
return $path;
}

try {
return Storage::disk('public')->url($path);
} catch (\Throwable $e) {
return asset('storage/' . ltrim($path, '/'));
}
};
@endphp

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
    <h3 class="text-uppercase mb-0">International Office - Activity Master</h3>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
      <h5 class="mb-0">Add New Activity</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.international-office.activity-master.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Activity Title <span class="text-danger">*</span></label>
            <input type="text" name="activity_title" class="form-control" value="{{ old('activity_title') }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Activity Date <span class="text-danger">*</span></label>
            <input type="date" name="activity_date" class="form-control" value="{{ old('activity_date') }}" required>
          </div>
        </div>

        <hr>
        <h6 class="fw-bold mb-3">1) MoU Related</h6>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name of Institution <span class="text-danger">*</span></label>
            <input type="text" name="institution_name" class="form-control" value="{{ old('institution_name') }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Having Any MoU (Y/N)</label>
            <select name="has_mou" class="form-select">
              <option value="1" {{ old('has_mou') == '1' ? 'selected' : '' }}>Yes</option>
              <option value="0" {{ old('has_mou', '0') == '0' ? 'selected' : '' }}>No</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Date of Signing</label>
            <input type="date" name="mou_signing_date" class="form-control" value="{{ old('mou_signing_date') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Copy of MoU</label>
            <input type="file" name="mou_copy" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          </div>
        </div>

        <hr>
        <h6 class="fw-bold mb-3">2) Activities Related</h6>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nature / Type <span class="text-danger">*</span></label>
            <select name="activity_type" class="form-select" required>
              <option value="">Select Type</option>
              <option value="Faculty Exchange" {{ old('activity_type') === 'Faculty Exchange' ? 'selected' : '' }}>Faculty Exchange</option>
              <option value="Student Exchange" {{ old('activity_type') === 'Student Exchange' ? 'selected' : '' }}>Student Exchange</option>
              <option value="Faculty Research Collaboration" {{ old('activity_type') === 'Faculty Research Collaboration' ? 'selected' : '' }}>Faculty Research Collaboration</option>
              <option value="Curriculum Development" {{ old('activity_type') === 'Curriculum Development' ? 'selected' : '' }}>Curriculum Development</option>
              <option value="Joint Academic Event (Seminar/Conference)" {{ old('activity_type') === 'Joint Academic Event (Seminar/Conference)' ? 'selected' : '' }}>Joint Academic Event (Seminar/Conference)</option>
              <option value="Study Trip / Symposium" {{ old('activity_type') === 'Study Trip / Symposium' ? 'selected' : '' }}>Study Trip / Symposium</option>
              <option value="Other" {{ old('activity_type') === 'Other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Nature</label>
            <select name="participant_type" class="form-select" required>
              <option value="student_only" {{ old('participant_type') === 'student_only' ? 'selected' : '' }}>Student Only</option>
              <option value="faculty_only" {{ old('participant_type') === 'faculty_only' ? 'selected' : '' }}>Faculty Only</option>
              <option value="both" {{ old('participant_type', 'both') === 'both' ? 'selected' : '' }}>Both</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Dept Scope</label>
            <select name="department_scope" class="form-select" required>
              <option value="one" {{ old('department_scope') === 'one' ? 'selected' : '' }}>One</option>
              <option value="multiple" {{ old('department_scope', 'multiple') === 'multiple' ? 'selected' : '' }}>Multiple</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Department Name(s)</label>
            <input type="text" name="department_details" class="form-control" value="{{ old('department_details') }}" placeholder="Example: Management, Computer Science">
          </div>

          <div class="col-md-3">
            <label class="form-label">Approval</label>
            <select name="approval_status" class="form-select">
              <option value="">Select</option>
              <option value="Pending" {{ old('approval_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
              <option value="Approved" {{ old('approval_status') === 'Approved' ? 'selected' : '' }}>Approved</option>
              <option value="Not Required" {{ old('approval_status') === 'Not Required' ? 'selected' : '' }}>Not Required</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
              <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Report (IQAC Format)</label>
            <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
          </div>

          <div class="col-md-6">
            <label class="form-label">Geotagged Photos</label>
            <input type="file" name="geotagged_photos[]" class="form-control" accept=".jpg,.jpeg,.png" multiple>
            <small class="text-muted">Upload one or more photos.</small>
          </div>
        </div>

        <hr>
        <h6 class="fw-bold mb-3">3) Finance</h6>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Kind of Grant</label>
            <input type="text" name="finance_grant_kind" class="form-control" value="{{ old('finance_grant_kind') }}" placeholder="Grant/Funding name">
          </div>
          <div class="col-md-3">
            <label class="form-label">Count</label>
            <input type="number" min="0" name="finance_count" class="form-control" value="{{ old('finance_count') }}">
          </div>
          <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3" maxlength="1000" placeholder="Optional notes">{{ old('remarks') }}</textarea>
          </div>
        </div>

        <div class="text-end mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Save Activity
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light">
      <h5 class="mb-0">Activity Records</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Title</th>
              <th>Institution</th>
              <th>Type</th>
              <th>MoU</th>
              <th>Finance</th>
              <th>Files</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activities as $activity)
            @php
            $mouUrl = $resolveDocumentUrl($activity->mou_copy_path);
            $reportUrl = $resolveDocumentUrl($activity->report_path);
            $photoPaths = is_array($activity->geotagged_photo_paths) ? $activity->geotagged_photo_paths : [];
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ optional($activity->activity_date)->format('d M Y') }}</td>
              <td>{{ $activity->activity_title }}</td>
              <td>{{ $activity->institution_name }}</td>
              <td>{{ $activity->activity_type }}<br><small class="text-muted">{{ strtoupper(str_replace('_', ' ', $activity->participant_type)) }}</small></td>
              <td>
                <span class="badge {{ $activity->has_mou ? 'bg-success' : 'bg-secondary' }}">{{ $activity->has_mou ? 'Yes' : 'No' }}</span>
                @if($activity->mou_signing_date)
                <div><small>{{ optional($activity->mou_signing_date)->format('d M Y') }}</small></div>
                @endif
              </td>
              <td>
                {{ $activity->finance_grant_kind ?: '-' }}
                <div><small>Count: {{ is_null($activity->finance_count) ? '-' : $activity->finance_count }}</small></div>
              </td>
              <td>
                @if($mouUrl)
                <a href="{{ $mouUrl }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">MoU</a>
                @endif
                @if($reportUrl)
                <a href="{{ $reportUrl }}" target="_blank" class="btn btn-sm btn-outline-success mb-1">Report</a>
                @endif
                @if(!empty($photoPaths))
                <span class="badge bg-info text-dark">Photos: {{ count($photoPaths) }}</span>
                @endif
              </td>
              <td>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editActivityModal{{ $activity->id }}">Edit</button>
                <form action="{{ route('admin.international-office.activity-master.destroy', $activity->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this activity?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editActivityModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('admin.international-office.activity-master.update', $activity->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Activity Title <span class="text-danger">*</span></label>
                          <input type="text" name="activity_title" class="form-control" value="{{ $activity->activity_title }}" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Activity Date <span class="text-danger">*</span></label>
                          <input type="date" name="activity_date" class="form-control" value="{{ optional($activity->activity_date)->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Name of Institution <span class="text-danger">*</span></label>
                          <input type="text" name="institution_name" class="form-control" value="{{ $activity->institution_name }}" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Having Any MoU (Y/N)</label>
                          <select name="has_mou" class="form-select">
                            <option value="1" {{ $activity->has_mou ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !$activity->has_mou ? 'selected' : '' }}>No</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Date of Signing</label>
                          <input type="date" name="mou_signing_date" class="form-control" value="{{ optional($activity->mou_signing_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Nature / Type <span class="text-danger">*</span></label>
                          <select name="activity_type" class="form-select" required>
                            <option value="Faculty Exchange" {{ $activity->activity_type === 'Faculty Exchange' ? 'selected' : '' }}>Faculty Exchange</option>
                            <option value="Student Exchange" {{ $activity->activity_type === 'Student Exchange' ? 'selected' : '' }}>Student Exchange</option>
                            <option value="Faculty Research Collaboration" {{ $activity->activity_type === 'Faculty Research Collaboration' ? 'selected' : '' }}>Faculty Research Collaboration</option>
                            <option value="Curriculum Development" {{ $activity->activity_type === 'Curriculum Development' ? 'selected' : '' }}>Curriculum Development</option>
                            <option value="Joint Academic Event (Seminar/Conference)" {{ $activity->activity_type === 'Joint Academic Event (Seminar/Conference)' ? 'selected' : '' }}>Joint Academic Event (Seminar/Conference)</option>
                            <option value="Study Trip / Symposium" {{ $activity->activity_type === 'Study Trip / Symposium' ? 'selected' : '' }}>Study Trip / Symposium</option>
                            <option value="Other" {{ $activity->activity_type === 'Other' ? 'selected' : '' }}>Other</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Nature</label>
                          <select name="participant_type" class="form-select" required>
                            <option value="student_only" {{ $activity->participant_type === 'student_only' ? 'selected' : '' }}>Student Only</option>
                            <option value="faculty_only" {{ $activity->participant_type === 'faculty_only' ? 'selected' : '' }}>Faculty Only</option>
                            <option value="both" {{ $activity->participant_type === 'both' ? 'selected' : '' }}>Both</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Dept Scope</label>
                          <select name="department_scope" class="form-select" required>
                            <option value="one" {{ $activity->department_scope === 'one' ? 'selected' : '' }}>One</option>
                            <option value="multiple" {{ $activity->department_scope === 'multiple' ? 'selected' : '' }}>Multiple</option>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Department Name(s)</label>
                          <input type="text" name="department_details" class="form-control" value="{{ $activity->department_details }}">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Approval</label>
                          <select name="approval_status" class="form-select">
                            <option value="">Select</option>
                            <option value="Pending" {{ $activity->approval_status === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ $activity->approval_status === 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Not Required" {{ $activity->approval_status === 'Not Required' ? 'selected' : '' }}>Not Required</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Status</label>
                          <select name="is_active" class="form-select">
                            <option value="1" {{ $activity->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$activity->is_active ? 'selected' : '' }}>Inactive</option>
                          </select>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Kind of Grant</label>
                          <input type="text" name="finance_grant_kind" class="form-control" value="{{ $activity->finance_grant_kind }}">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Count</label>
                          <input type="number" min="0" name="finance_count" class="form-control" value="{{ $activity->finance_count }}">
                        </div>
                        <div class="col-md-12">
                          <label class="form-label">Remarks</label>
                          <textarea name="remarks" class="form-control" rows="2" maxlength="1000">{{ $activity->remarks }}</textarea>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Replace MoU Copy</label>
                          <input type="file" name="mou_copy" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Replace/Append Report</label>
                          <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-12">
                          <label class="form-label">Add More Geotagged Photos</label>
                          <input type="file" name="geotagged_photos[]" class="form-control" accept=".jpg,.jpeg,.png" multiple>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="9" class="text-center text-muted">No activity records found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
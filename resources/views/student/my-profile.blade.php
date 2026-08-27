@include('includes.header')

@include('student.sidebar')

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if($errors->any())
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Please correct the following:</strong>
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h4>
              <p class="mb-0 text-muted">View your profile details and manage your placement documents.</p>
            </div>
            <a href="{{ route('student.console.placement') }}" class="btn btn-outline-secondary">
              <i class="fas fa-briefcase me-1"></i>Go to Placement
            </a>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">Profile Details</div>
            <div class="card-body">
              <div class="small text-muted mb-1">Name</div>
              <div class="fw-semibold mb-3">{{ trim(($studentRecord->first_name ?? '') . ' ' . ($studentRecord->last_name ?? '')) ?: 'N/A' }}</div>

              <div class="small text-muted mb-1">Roll Number</div>
              <div class="fw-semibold mb-3">{{ $studentRecord->roll_no ?: 'N/A' }}</div>

              <div class="small text-muted mb-1">Library Code</div>
              <div class="fw-semibold mb-3">{{ $studentRecord->library_code ?: 'N/A' }}</div>


              <div class="small text-muted mb-1">Enrolled Program</div>
              <div class="fw-semibold mb-3">{{ $studentRecord->stdprogramenrolled->name ?? 'N/A' }}</div>

              <div class="small text-muted mb-1">Campus</div>
              <div class="fw-semibold mb-3">{{ $studentRecord->campusmaster->name ?? 'N/A' }}</div>

              <div class="small text-muted mb-1">Batch</div>
              <div class="fw-semibold">{{ $studentRecord->batchmaster->batch_name ?? 'N/A' }}</div>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-bold">Update Contact Details</div>
            <div class="card-body">
              <form action="{{ route('student.console.profile.contact.update') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone Number</label>
                  <input
                    type="text"
                    name="mobile_no"
                    class="form-control"
                    value="{{ old('mobile_no', $studentRecord->mobile_no) }}"
                    maxlength="20"
                    required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email</label>
                  <input
                    type="email"
                    name="mail_id"
                    class="form-control"
                    value="{{ old('mail_id', $studentRecord->mail_id) }}"
                    required>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update Contact
                  </button>
                </div>
              </form>
            </div>
          </div>



          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">My Documents
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadMyDocModal">
                <i class="fas fa-upload me-1"></i>Upload
              </button>

            </div>
            <div class="card-body">
              <p class="text-muted mb-0">Upload your required placement documents from a popup form.</p>

              @if(($myDocuments ?? collect())->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Title</th>
                      <th>Type</th>
                      <th>Resume</th>
                      <th>Uploaded</th>
                      <th>File</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($myDocuments as $doc)
                    <tr>
                      <td>{{ $doc->title }}</td>
                      <td>{{ $documentationLabelMap[$doc->document_key] ?? ucwords(str_replace('_', ' ', (string) $doc->document_key)) ?: 'N/A' }}</td>
                      <td>{!! $doc->is_resume ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light text-dark">No</span>' !!}</td>
                      <td>{{ optional($doc->created_at)->format('d M Y h:i A') }}</td>
                      <td><a href="{{ Storage::disk('s3')->url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @else
              <div class="alert alert-info mb-0">No documents uploaded yet.</div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="uploadMyDocModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload To My Docs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="myDocsUploadForm" action="{{ route('student.console.placement.docs.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
          @csrf
          <div class="col-md-6">
            <label class="form-label fw-semibold">Document Type</label>
            <select name="document_key" class="form-select" required>
              <option value="" selected disabled>Select type</option>
              @foreach(($documentationLabelMap ?? []) as $docKey => $docLabel)
              <option value="{{ $docKey }}">{{ $docLabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Upload File (max 10MB)</label>
            <input type="file" name="document_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="myDocsUploadForm" id="myDocsUploadSubmitBtn" class="btn btn-success">
          <i class="fas fa-upload me-1"></i>Submit Document
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var uploadForm = document.getElementById('myDocsUploadForm');
    var submitBtn = document.getElementById('myDocsUploadSubmitBtn');

    if (!uploadForm || !submitBtn) {
      return;
    }

    uploadForm.addEventListener('submit', function() {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Please wait...';
    });
  });
</script>

@include('student.footer')
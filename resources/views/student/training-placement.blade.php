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
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Training & Placement</h4>
            <p class="mb-0 text-muted">Submit your form, track approval status, and access the training portal once approved.</p>
          </div>
          <a href="{{ route('student.console.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-semibold">Application Status</div>
              <div class="small text-muted">This status is updated by TPO after review.</div>
            </div>
            @if($tpStatus === 'approved')
            <span class="badge bg-success">Approved</span>
            @elseif($tpStatus === 'rejected')
            <span class="badge bg-danger">Rejected</span>
            @elseif($tpStatus === 'in_review')
            <span class="badge bg-warning text-dark">In Review</span>
            @elseif($tpStatus === 'submitted')
            <span class="badge bg-info text-dark">Submitted</span>
            @else
            <span class="badge bg-secondary">Not Submitted</span>
            @endif
          </div>

          <div class="mt-3 small text-muted">
            @if($trainingPlacementOptIn && !empty($trainingPlacementOptIn->opted_at))
            Submitted on {{ optional($trainingPlacementOptIn->opted_at)->format('d M Y h:i A') }}
            @else
            Submission date not available.
            @endif
            @if($tpStatus === 'approved' && !empty($trainingPlacementOptIn->approved_at))
            <br>Approved on {{ optional($trainingPlacementOptIn->approved_at)->format('d M Y h:i A') }}
            @endif
            @if($tpStatus === 'rejected' && !empty($trainingPlacementOptIn->rejected_at))
            <br>Rejected on {{ optional($trainingPlacementOptIn->rejected_at)->format('d M Y h:i A') }}
            @endif
          </div>

          @if($tpStatus === 'rejected' && !empty($trainingPlacementOptIn->rejection_reason))
          <div class="alert alert-danger mt-3 mb-0">
            <div class="fw-semibold">Rejection Reason</div>
            <div class="small mt-1">{{ $trainingPlacementOptIn->rejection_reason }}</div>
            <div class="small mt-2">Please update your form and submit again.</div>
          </div>
          @endif

          @if($trainingPlacementOptIn && !empty($trainingPlacementOptIn->form_file_path))
          <div class="mt-3">
            <a href="{{ Storage::disk('s3')->url($trainingPlacementOptIn->form_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-file-alt me-1"></i>View Submitted Form
            </a>
          </div>
          @endif
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent fw-bold">Form Template</div>
        <div class="card-body">
          @if(!empty($trainingPlacementFormTemplate?->file_path))
          <a href="{{ Storage::disk('s3')->url($trainingPlacementFormTemplate->file_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-download me-1"></i>Download Official T&P Form
          </a>
          <span class="ms-2 text-muted small">Fill, sign, and upload below.</span>
          @else
          <div class="text-warning small"><i class="fas fa-info-circle me-1"></i>TPO has not uploaded the official template yet.</div>
          @endif
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent fw-bold">Submit / Update Form</div>
        <div class="card-body">
          @if($tpStatus === 'approved')
          <div class="alert alert-success mb-0">
            <i class="fas fa-check-circle me-1"></i>Your form is approved. Re-upload is disabled.
          </div>
          @else
          <form action="{{ route('student.console.training-placement.opt-in') }}" method="POST" enctype="multipart/form-data" id="trainingPlacementOptInForm">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Upload T&P Opt-In Form (PDF/DOC, max 10MB)</label>
                <input type="file" name="tp_optin_form" class="form-control" accept=".pdf,.doc,.docx" {{ $trainingPlacementOptIn ? '' : 'required' }}>
                <small class="text-muted">{{ $trainingPlacementOptIn ? 'Upload again to replace existing form before approval.' : 'Form upload is mandatory for first submission.' }}</small>
              </div>
              <div class="col-md-12">
                <div style="border:1px solid #d7deec;border-radius:8px;background:#fff;padding:.75rem;">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="tncAccepted" name="tnc_accepted" required>
                    <label class="form-check-label" for="tncAccepted" style="font-size:.86rem;line-height:1.4;">
                      I have read and accepted the Terms and Conditions of the Training & Placement Policy.
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-check-circle me-1"></i>{{ $trainingPlacementOptIn ? 'Update Submission' : 'Submit Form' }}
                </button>
              </div>
            </div>
          </form>
          @endif
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Training Portal Access</div>
        <div class="card-body">
          @if($tpStatus === 'approved')
          <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <div class="fw-semibold">Training Portal is Active</div>
              <div class="small">You can now access assigned training modules.</div>
            </div>
            <a href="{{ route('student.fa1.index') }}" class="btn btn-success btn-sm">
              <i class="fas fa-play-circle me-1"></i>Open Training Portal
            </a>
          </div>
          @else
          <div class="alert alert-secondary mb-0">
            <i class="fas fa-lock me-1"></i>Training portal will be active after TPO approval.
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('student.footer')
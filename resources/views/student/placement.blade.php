@include('includes.header')

@include('student.sidebar')

<style>
  .placement-hero {
    border: 0;
    border-radius: 16px;
    background: linear-gradient(135deg, #0b3c6f 0%, #0f6d9f 55%, #12a0b0 100%);
    color: #fff;
    box-shadow: 0 14px 36px rgba(11, 60, 111, 0.22);
  }

  .hero-pill {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.24);
    color: #fff;
    border-radius: 999px;
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
  }

  .metric-card {
    border: 1px solid #e7eef6;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 20px rgba(15, 40, 80, 0.06);
  }

  .metric-label {
    color: #607086;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .metric-value {
    font-size: 1.45rem;
    font-weight: 700;
    color: #132238;
    line-height: 1;
  }

  .panel-card {
    border: 1px solid #e7eef6;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 20px rgba(15, 40, 80, 0.06);
  }

  .panel-head {
    border-bottom: 1px solid #edf2f8;
    padding: 0.9rem 1rem;
    font-weight: 700;
    color: #1f3247;
  }

  .job-card {
    border: 1px solid #dfe8f3;
    border-radius: 14px;
    padding: 1rem;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }

  .job-card:hover {
    transform: translateY(-2px);
    border-color: #c7d9ee;
    box-shadow: 0 10px 24px rgba(13, 53, 95, 0.08);
  }

  .job-meta {
    font-size: 0.86rem;
    color: #5b6d82;
  }

  .doc-pill {
    display: inline-block;
    border: 1px solid #d7e3f1;
    border-radius: 999px;
    padding: 0.2rem 0.55rem;
    font-size: 0.76rem;
    color: #24466e;
    background: #f6faff;
    margin: 0 0.35rem 0.35rem 0;
  }

  .section-muted {
    color: #607086;
    font-size: 0.88rem;
  }
</style>

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid py-4">
      @php
      $jobs = collect($availableJobs ?? []);
      $documents = collect($myDocuments ?? []);
      $applications = collect($myApplications ?? []);
      $resumeDocs = $documents->filter(fn($doc) => (int) ($doc->is_resume ?? 0) === 1)->values();
      $appliedCount = $applications->pluck('placement_opportunity_id')->unique()->count();
      $statusBadgeClass = $tpStatus === 'approved' ? 'bg-success' : ($tpStatus === 'rejected' ? 'bg-danger' : ($tpStatus === 'in_review' ? 'bg-warning text-dark' : 'bg-secondary'));
      $statusLabel = $tpStatus === 'approved' ? 'Approved' : ($tpStatus === 'rejected' ? 'Rejected' : ($tpStatus === 'in_review' ? 'In Review' : 'Not Submitted'));
      @endphp



      <div class="placement-hero p-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <div class="hero-pill d-inline-flex align-items-center mb-2">
              <i class="fas fa-compass me-2"></i>Career Console
            </div>
            <h3 class="mb-1 fw-bold text-light">Find And Apply To Opportunities</h3>
            <p class="mb-0" style="opacity:0.92;">Track placement status, maintain documents, and apply to relevant openings from one workspace.</p>
          </div>

          <div class="ms-lg-auto" style="min-width:290px;max-width:420px;width:100%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);border-radius:12px;padding:0.9rem;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">Placement Form Status</div>
              <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="small" style="opacity:0.95;line-height:1.5;">
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

            <div class="d-flex flex-wrap gap-2 mt-3">

              @if($trainingPlacementOptIn && !empty($trainingPlacementOptIn->form_file_path))
              <a href="{{ Storage::disk('s3')->url($trainingPlacementOptIn->form_file_path) }}" target="_blank" class="btn btn-light btn-sm">
                <i class="fas fa-file-alt me-1"></i>Submitted Form
              </a>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6">
          <div class="metric-card p-3 h-100">
            <div class="metric-label">Open Opportunities</div>
            <div class="metric-value mt-2">{{ $jobs->count() }}</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="metric-card p-3 h-100">
            <div class="metric-label">Applications Sent</div>
            <div class="metric-value mt-2">{{ $appliedCount }}</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="metric-card p-3 h-100">
            <div class="metric-label">Documents In Vault</div>
            <div class="metric-value mt-2">{{ $documents->count() }}</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="metric-card p-3 h-100">
            <div class="metric-label">Placement Status</div>
            <div class="mt-2">
              @if($tpStatus === 'approved')
              <span class="badge bg-success">Approved</span>
              @elseif($tpStatus === 'rejected')
              <span class="badge bg-danger">Rejected</span>
              @elseif($tpStatus === 'in_review')
              <span class="badge bg-warning text-dark">In Review</span>
              @else
              <span class="badge bg-secondary">Not Submitted</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="panel-card mb-3">
        <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span><i class="fas fa-briefcase me-2"></i>Job Listings</span>
          <span class="section-muted">Applicable opportunities based on your profile</span>
        </div>
        <div class="p-3">
          @if($jobs->count() > 0)
          <div class="row g-3">
            @foreach($jobs as $job)
            @php
            $requiredDocKeys = collect((array) ($job->documentation_required ?? []))
            ->map(fn($value) => strtolower(trim((string) $value)))
            ->filter()
            ->unique()
            ->values();
            $existingApplication = ($applicationMap ?? collect())->get($job->id);
            @endphp

            <div class="col-12">
              <div class="job-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                  <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                      <h5 class="mb-0 fw-bold">{{ $job->title }}</h5>
                      <span class="badge bg-light text-dark border">{{ ucfirst((string) ($job->category ?? 'Opportunity')) }}</span>
                      @if($existingApplication)
                      <span class="badge bg-success">Applied</span>
                      @endif
                    </div>

                    <div class="job-meta mb-2">
                      <span class="me-3"><i class="fas fa-building me-1"></i>{{ $job->company_name ?: 'Company not specified' }}</span>
                      <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i>{{ $job->location }}{{ $job->country ? ', ' . $job->country : '' }}</span>
                      <span><i class="far fa-calendar-alt me-1"></i>Apply by {{ $job->apply_deadline ? $job->apply_deadline->format('d M Y') : 'N/A' }}</span>
                    </div>

                    <p class="mb-2">{{ $job->description }}</p>

                    <div>
                      @if($requiredDocKeys->isNotEmpty())
                      @foreach($requiredDocKeys as $key)
                      <span class="doc-pill">{{ $documentationLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key)) }}</span>
                      @endforeach
                      @else
                      <span class="section-muted">No additional required documents.</span>
                      @endif
                    </div>

                    @if($existingApplication)
                    <div class="mt-2 section-muted">Applied on {{ optional($existingApplication->applied_at)->format('d M Y h:i A') }}</div>
                    @endif
                  </div>

                  <div class="text-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyJobModal{{ $job->id }}">
                      {{ $existingApplication ? 'Update Application' : 'Apply Now' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="applyJobModal{{ $job->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Apply: {{ $job->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form action="{{ route('student.console.placement.apply', $job->id) }}" method="POST" enctype="multipart/form-data">
                      @csrf

                      <div class="alert alert-info">
                        Resume is mandatory. You can select an existing resume from My Docs or upload a new one.
                      </div>

                      <div class="row g-3 mb-3">
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Select Resume From My Docs</label>
                          <select name="resume_document_id" class="form-select">
                            <option value="">Select resume</option>
                            @foreach($resumeDocs as $resumeDoc)
                            <option value="{{ $resumeDoc->id }}">{{ $resumeDoc->title }} ({{ optional($resumeDoc->created_at)->format('d M Y') }})</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-semibold">Or Upload New Resume</label>
                          <input type="file" name="resume_upload" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                      </div>

                      @if($requiredDocKeys->isNotEmpty())
                      <div class="border rounded p-3">
                        <h6 class="mb-2">Required Documents</h6>
                        <div class="row g-3">
                          @foreach($requiredDocKeys as $docKey)
                          @php
                          $matchedDocs = collect($myDocuments ?? [])->filter(function ($doc) use ($docKey) {
                          return strtolower(trim((string) ($doc->document_key ?? ''))) === $docKey;
                          })->values();
                          @endphp
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $documentationLabelMap[$docKey] ?? ucwords(str_replace('_', ' ', $docKey)) }}</label>
                            <select name="required_document_ids[{{ $docKey }}]" class="form-select mb-2">
                              <option value="">Select from My Docs</option>
                              @foreach($matchedDocs as $doc)
                              <option value="{{ $doc->id }}">{{ $doc->title }} ({{ optional($doc->created_at)->format('d M Y') }})</option>
                              @endforeach
                            </select>
                            <input type="file" name="required_document_uploads[{{ $docKey }}]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                          </div>
                          @endforeach
                        </div>
                      </div>
                      @endif

                      <div class="mt-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @else
          <div class="alert alert-info mb-0">No job descriptions available for your profile right now.</div>
          @endif
        </div>
      </div>


    </div>
  </main>
</div>

@include('student.footer')
@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Job Applications</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Applications</li>
          </ol>
        </nav>
      </div>
    </div>

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

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-user-tie me-2 text-primary"></i>Student Job Applications</h4>
              <p class="text-muted mb-0">Review resumes and submitted documentation for each application.</p>
            </div>
          </div>

          <form method="GET" action="{{ route('tpo.training-placement.job-applications.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Search Applications</label>
              <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by student, roll no, register no, job title or company">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Job Description</label>
              <select name="placement_id" class="form-select">
                <option value="">All Jobs</option>
                @foreach(($placementsForFilter ?? collect()) as $placementFilter)
                <option value="{{ $placementFilter->id }}" {{ (int) ($placementId ?? 0) === (int) $placementFilter->id ? 'selected' : '' }}>
                  {{ $placementFilter->title }} @if(!empty($placementFilter->company_name)) ({{ $placementFilter->company_name }}) @endif
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold mb-1">Progress</label>
              <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(($statusOptions ?? []) as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" {{ ($selectedStatus ?? '') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Filter</button>
              @if(!empty($search) || !empty($placementId) || !empty($selectedStatus))
              <a href="{{ route('tpo.training-placement.job-applications.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          @if($applications->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Applied On</th>
                  <th>Student</th>
                  <th>Job Description</th>
                  <th>Status</th>
                  <th>Resume</th>
                  <th>Documents (As Required)</th>
                  <th>Update Progress</th>
                </tr>
              </thead>
              <tbody>
                @foreach($applications as $application)
                @php
                $submittedDocuments = (array) ($application->submitted_documents ?? []);
                $submittedNormalized = collect($submittedDocuments)->mapWithKeys(function ($doc, $docKey) {
                return [strtolower(trim((string) $docKey)) => $doc];
                })->all();
                $requiredDocKeys = collect((array) ($application->placement->documentation_required ?? []))
                ->map(fn($value) => strtolower(trim((string) $value)))
                ->filter()
                ->unique()
                ->values();
                $additionalSubmittedKeys = collect(array_keys($submittedNormalized))
                ->reject(fn($docKey) => $requiredDocKeys->contains($docKey))
                ->values();
                @endphp
                <tr>
                  <td>{{ optional($application->applied_at)->format('d M Y h:i A') ?: optional($application->created_at)->format('d M Y h:i A') }}</td>
                  <td>
                    <div class="fw-semibold">{{ trim(($application->student->first_name ?? '') . ' ' . ($application->student->last_name ?? '')) ?: 'N/A' }}</div>
                    <div class="small text-muted">Roll: {{ $application->student->roll_no ?? 'N/A' }}</div>
                    <div class="small text-muted">Reg: {{ $application->student->register_no ?? 'N/A' }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $application->placement->title ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $application->placement->company_name ?? 'N/A' }}</div>
                  </td>
                  <td><span class="badge bg-info text-dark text-uppercase">{{ str_replace('_', ' ', $application->status ?? 'submitted') }}</span></td>
                  <td>
                    @if(!empty($application->resume_file_path))
                    <a href="{{ Storage::disk('s3')->url($application->resume_file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">View Resume</a>
                    @else
                    <span class="text-muted small">Not available</span>
                    @endif
                  </td>
                  <td>
                    @if($requiredDocKeys->isNotEmpty())
                    <div class="d-flex flex-column gap-1">
                      @foreach($requiredDocKeys as $docKey)
                      @php
                      $doc = $submittedNormalized[$docKey] ?? null;
                      $docLabel = $documentationLabelMap[$docKey] ?? ucwords(str_replace('_', ' ', $docKey));
                      @endphp
                      <div class="small">
                        <span class="fw-semibold">{{ $docLabel }}:</span>
                        @if(!empty($doc['file_path']))
                        <a href="{{ Storage::disk('s3')->url($doc['file_path']) }}" target="_blank">View</a>
                        @else
                        <span class="text-danger">Missing</span>
                        @endif
                      </div>
                      @endforeach
                    </div>
                    @if($additionalSubmittedKeys->isNotEmpty())
                    <div class="small text-muted mt-1">Additional:</div>
                    <div class="d-flex flex-column gap-1">
                      @foreach($additionalSubmittedKeys as $docKey)
                      @php $additionalDoc = $submittedNormalized[$docKey] ?? null; @endphp
                      <div class="small">
                        <span class="fw-semibold">{{ $documentationLabelMap[$docKey] ?? ucwords(str_replace('_', ' ', $docKey)) }}:</span>
                        @if(!empty($additionalDoc['file_path']))
                        <a href="{{ Storage::disk('s3')->url($additionalDoc['file_path']) }}" target="_blank">View</a>
                        @else
                        <span class="text-muted">N/A</span>
                        @endif
                      </div>
                      @endforeach
                    </div>
                    @endif
                    @elseif(!empty($submittedDocuments))
                    <div class="d-flex flex-column gap-1">
                      @foreach($submittedDocuments as $docKey => $doc)
                      <div class="small">
                        <span class="fw-semibold">{{ $documentationLabelMap[strtolower(trim((string) $docKey))] ?? ucwords(str_replace('_', ' ', $docKey)) }}:</span>
                        @if(!empty($doc['file_path']))
                        <a href="{{ Storage::disk('s3')->url($doc['file_path']) }}" target="_blank">View</a>
                        @else
                        <span class="text-muted">N/A</span>
                        @endif
                      </div>
                      @endforeach
                    </div>
                    @else
                    <span class="text-muted small">No required docs</span>
                    @endif
                  </td>
                  <td>
                    <form method="POST" action="{{ route('tpo.training-placement.job-applications.progress.update', $application->id) }}" class="d-flex flex-column gap-2">
                      @csrf
                      <select name="status" class="form-select form-select-sm" required>
                        @foreach(($statusOptions ?? []) as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" {{ ($application->status ?? 'submitted') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                      </select>
                      <textarea name="remarks" rows="2" class="form-control form-control-sm" maxlength="1000" placeholder="Add update note (optional)">{{ $application->remarks ?? '' }}</textarea>
                      <button type="submit" class="btn btn-sm btn-success">Update</button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-3">
            {{ $applications->links() }}
          </div>
          @else
          <div class="alert alert-info mb-0">No job applications found.</div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
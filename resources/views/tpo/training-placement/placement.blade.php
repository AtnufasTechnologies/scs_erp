@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Job Listing Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Job Description</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @php
      $totalPlacements = $placements->count();
      $activePlacements = $placements->where('is_active', 1)->count();
      $placementCategoryCount = $placements->filter(fn($placement) => in_array((string) $placement->category, ['placements', 'placement'], true))->count();
      $multiCampusCoverageCount = $placements->filter(function ($placement) use ($subjects) {
      $ids = collect($placement->subject_ids ?? [])->map(fn($id) => (int) $id)->values();
      if ($ids->isEmpty() && $placement->subject_id) {
      $ids = collect([(int) $placement->subject_id]);
      }

      $campusCount = $subjects
      ->whereIn('id', $ids)
      ->pluck('campus_id')
      ->filter()
      ->unique()
      ->count();

      return $campusCount > 1;
      })->count();
      @endphp

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
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Job Description Management</h4>
              <p class="text-muted mb-0">Catalog internships, apprenticeships, job descriptions, and project opportunities with campus and department applicability.</p>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCatalogItemModal">
                <i class="fas fa-plus-circle me-1"></i>Add Job Description
              </button>
            </div>



          </div>
          <form method="GET" action="{{ route('tpo.training-placement.job-description.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Search Job Descriptions</label>
              <input type="text" name="search" value="{{ $placementSearch ?? '' }}" class="form-control" placeholder="Search by title, company, location, category, campus or department">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold mb-1">Category</label>
              <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach($categoryOptions as $value => $label)
                <option value="{{ $value }}" {{ ($selectedCategory ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold mb-1">Drive Date From</label>
              <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold mb-1">Drive Date To</label>
              <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control">
            </div>
            <div class="col-md-1 d-flex justify-content-end gap-2 pt-1">
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
              @if(!empty($placementSearch) || !empty($selectedCategory) || !empty($dateFrom) || !empty($dateTo))
              <a href="{{ route('tpo.training-placement.job-description.index') }}" class="btn btn-outline-secondary">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>



      <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
          <div class="stat-card">
            <div class="card-body">
              <div class="text-muted small">Total Openings</div>
              <h4 class="mb-0 fw-bold">{{ $totalPlacements }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card">
            <div class="card-body">
              <div class="text-muted small">Active Openings</div>
              <h4 class="mb-0 fw-bold text-success">{{ $activePlacements }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card">
            <div class="card-body">
              <div class="text-muted small">Job Description Category</div>
              <h4 class="mb-0 fw-bold text-primary">{{ $placementCategoryCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card">
            <div class="card-body">
              <div class="text-muted small">Both-Campus Coverage</div>
              <h4 class="mb-0 fw-bold text-info">{{ $multiCampusCoverageCount }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">


        <div class="col-xl-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">Catalog Listings</h6>
            </div>
            <div class="card-body">

              @forelse($placements as $placement)
              @php
              $selectedSubjectIds = collect($placement->subject_ids ?? [])->map(fn($id) => (int) $id)->values();
              if ($selectedSubjectIds->isEmpty() && $placement->subject_id) {
              $selectedSubjectIds = collect([(int) $placement->subject_id]);
              }
              $selectedSubjectNames = $selectedSubjectIds
              ->map(function ($id) use ($subjectLookup) {
              $subject = $subjectLookup->get($id);
              return $subject ? ($subject->title ?? $subject->name ?? ('Subject #' . $id)) : null;
              })
              ->filter()
              ->values();
              $allSubjectIds = $subjects->pluck('id')->map(fn($id) => (int) $id)->sort()->values();
              $campusSubjectIds = $placement->campus_id
              ? $subjects->where('campus_id', $placement->campus_id)->pluck('id')->map(fn($id) => (int) $id)->sort()->values()
              : collect();
              $normalizedSelectedIds = $selectedSubjectIds->sort()->values();
              $isBothCampusesAllDepartments = $allSubjectIds->isNotEmpty() && $normalizedSelectedIds->values()->all() === $allSubjectIds->values()->all();
              $isSelectedCampusAllDepartments = !$isBothCampusesAllDepartments && $placement->campus_id && $campusSubjectIds->isNotEmpty() && $normalizedSelectedIds->values()->all() === $campusSubjectIds->values()->all();
              $applicabilityScope = $isBothCampusesAllDepartments ? 'both_campuses_all_departments' : ($isSelectedCampusAllDepartments ? 'selected_campus_all_departments' : 'selected_departments');
              $placementDocKeys = collect((array) ($placement->documentation_required ?? []))
              ->map(fn($value) => strtolower(trim((string) $value)))
              ->filter()
              ->values();
              $knownDocKeys = collect(array_keys($documentationRequirementOptions ?? []));
              $customPlacementDocKeys = $placementDocKeys->reject(fn($key) => $knownDocKeys->contains($key))->values();
              $placementDocLabels = $placementDocKeys->map(function ($docKey) use ($documentationRequirementOptions) {
              return $documentationRequirementOptions[$docKey] ?? ucwords(str_replace('_', ' ', $docKey));
              })->values();
              @endphp
              <div class="border rounded-3 p-2 mb-2 bg-white shadow-sm">
                <div class="row g-2 align-items-start">
                  <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                      <h6 class="mb-0 fw-bold">{{ $placement->title }}</h6>
                      <span class="badge bg-info text-dark">{{ $categoryOptions[$placement->category] ?? ucfirst($placement->category ?? 'N/A') }}</span>
                      <span class="badge bg-secondary">{{ $monthOptions[$placement->month] ?? 'Month N/A' }}</span>
                      @if($placement->is_active)
                      <span class="badge bg-success">Active</span>
                      @else
                      <span class="badge bg-secondary">Inactive</span>
                      @endif
                    </div>

                    @if($placement->logo_path)
                    <div class="mb-2">
                      <img src="{{ Storage::disk('s3')->url($placement->logo_path) }}" alt="logo" style="height: 42px; width: auto; border-radius: 6px;">
                    </div>
                    @endif

                    <div class="mb-2">{!! $placement->description !!}</div>

                    <div class="row g-1 small text-muted">
                      <div class="col-md-6"><span class="fw-semibold text-dark">Campus:</span> {{ $isBothCampusesAllDepartments ? 'Both Campuses' : ($placement->campus->name ?? 'N/A') }}</div>
                      <div class="col-md-6"><span class="fw-semibold text-dark">Applicable Year:</span> {{ $yearOptions[(int) $placement->student_year] ?? ($placement->student_year ?: 'N/A') }}</div>
                      <div class="col-md-6"><span class="fw-semibold text-dark">Location:</span> {{ $placement->location }}{{ $placement->country ? ', ' . $placement->country : '' }}</div>
                      <div class="col-md-6"><span class="fw-semibold text-dark">Departments:</span> {{ $isBothCampusesAllDepartments ? 'All Departments (Both Campuses)' : ($isSelectedCampusAllDepartments ? 'All Departments (Selected Campus)' : ($selectedSubjectNames->isNotEmpty() ? $selectedSubjectNames->implode(', ') : 'N/A')) }}</div>
                      @if($placement->category === 'internship')
                      <div class="col-md-6"><span class="fw-semibold text-dark">Internship Type:</span> {{ $placement->internship_stipend_type === 'stipend' ? 'Stipend' : ($placement->internship_stipend_type === 'non_stipend' ? 'Non Stipend' : 'N/A') }}</div>
                      @endif
                      @if(in_array((string) $placement->category, ['placements', 'placement'], true))
                      <div class="col-md-6"><span class="fw-semibold text-dark">Placement Type:</span> {{ $placementTypeOptions[$placement->placement_type] ?? 'N/A' }}</div>
                      <div class="col-md-6"><span class="fw-semibold text-dark">Opening Type:</span> {{ $openingTypeOptions[$placement->opening_type] ?? 'N/A' }}</div>
                      <div class="col-12"><span class="fw-semibold text-dark">Documentation Needed:</span> {{ $placementDocLabels->isNotEmpty() ? $placementDocLabels->implode(', ') : 'N/A' }}</div>
                      @endif
                    </div>
                  </div>

                  <div class="col-lg-4">
                    <div class="border rounded-3 p-2 h-100 bg-light">
                      <div class="small text-muted mb-1">Applications Received</div>
                      <div class="h5 fw-bold mb-2">{{ (int) ($placement->applications_count ?? 0) }}</div>

                      <div class="small text-muted mb-1">Company</div>
                      <div class="fw-semibold mb-2">{{ $placement->company_name ?: 'N/A' }}</div>

                      <div class="small text-muted mb-1">Drive Date</div>
                      <div class="mb-2">{{ $placement->drive_date ? $placement->drive_date->format('d M Y') : 'N/A' }}</div>

                      <div class="small text-muted mb-1">Apply Deadline</div>
                      <div class="mb-2">{{ $placement->apply_deadline ? $placement->apply_deadline->format('d M Y') : 'N/A' }}</div>

                      <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#placementManage{{ $placement->id }}">Edit</button>
                        <form action="{{ route('tpo.training-placement.job-description.destroy', $placement->id) }}" method="POST" onsubmit="return confirm('Delete this item?')">
                          @csrf
                          @method('DELETE')
                          <button
                            class="btn btn-sm btn-outline-danger w-100"
                            {{ ((int) ($placement->applications_count ?? 0)) > 0 ? 'disabled' : '' }}
                            title="{{ ((int) ($placement->applications_count ?? 0)) > 0 ? 'Cannot delete: students have already applied.' : 'Delete this item' }}">
                            Delete
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="collapse mt-3" id="placementManage{{ $placement->id }}">
                  <form action="{{ route('tpo.training-placement.job-description.update', $placement->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 js-placement-form">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                      <div class="col-lg-6">
                        <div class="card h-100">
                          <div class="card-header">Settings</div>
                          <div class="card-body">
                            <div class="row g-2">
                              <div class="col-12">
                                <label class="form-label fw-semibold">Title</label>
                                <input class="form-control" name="title" value="{{ $placement->title }}" required>
                              </div>
                              <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control js-placement-description" name="description" rows="3">{{ $placement->description }}</textarea>
                              </div>
                              <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="category" class="form-select js-placement-category" required>
                                  @foreach($categoryOptions as $value => $label)
                                  <option value="{{ $value }}" {{ ($placement->category === $value || ($placement->category === 'placement' && $value === 'placements')) ? 'selected' : '' }}>{{ $label }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold">Applicable Student Year</label>
                                <select name="student_year" class="form-select" required>
                                  @foreach($yearOptions as $yearValue => $yearLabel)
                                  <option value="{{ $yearValue }}" {{ (int) $placement->student_year === (int) $yearValue ? 'selected' : '' }}>{{ $yearLabel }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="col-lg-4 col-md-6">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="month" class="form-select" required>
                                  @foreach($monthOptions as $monthValue => $monthLabel)
                                  <option value="{{ $monthValue }}" {{ (int) $placement->month === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                  @endforeach
                                </select>
                              </div>

                              <div class="col-12 js-internship-only" style="display:none;">
                                <label class="form-label fw-semibold d-block">Internship Type</label>
                                <div class="d-flex gap-4 flex-wrap">
                                  <div class="form-check">
                                    <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="internshipStipend{{ $placement->id }}" value="stipend" {{ $placement->internship_stipend_type === 'stipend' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="internshipStipend{{ $placement->id }}">Stipend</label>
                                  </div>
                                  <div class="form-check">
                                    <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="internshipNonStipend{{ $placement->id }}" value="non_stipend" {{ $placement->internship_stipend_type === 'non_stipend' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="internshipNonStipend{{ $placement->id }}">Non Stipend</label>
                                  </div>
                                </div>
                              </div>

                              <div class="col-12 js-placement-only" style="display:none;">
                                <div class="border rounded p-2">
                                  <div class="row g-2">
                                    <div class="col-md-6">
                                      <label class="form-label fw-semibold">Placement Type</label>
                                      <select name="placement_type" class="form-select js-placement-required-field">
                                        <option value="">Select type</option>
                                        @foreach($placementTypeOptions as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}" {{ $placement->placement_type === $typeValue ? 'selected' : '' }}>{{ $typeLabel }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                    <div class="col-md-6">
                                      <label class="form-label fw-semibold">Opening Type</label>
                                      <select name="opening_type" class="form-select js-placement-required-field">
                                        <option value="">Select opening type</option>
                                        @foreach($openingTypeOptions as $openingValue => $openingLabel)
                                        <option value="{{ $openingValue }}" {{ $placement->opening_type === $openingValue ? 'selected' : '' }}>{{ $openingLabel }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-12">
                                <label class="form-label fw-semibold">Documentation Needed</label>
                                <div class="row g-2">
                                  @foreach($documentationRequirementOptions as $docValue => $docLabel)
                                  <div class="col-lg-4 col-sm-6">
                                    <div class="form-check">
                                      <input class="form-check-input" type="checkbox" name="documentation_required[]" id="docEdit{{ $placement->id }}{{ $docValue }}" value="{{ $docValue }}" {{ $placementDocKeys->contains($docValue) ? 'checked' : '' }}>
                                      <label class="form-check-label" for="docEdit{{ $placement->id }}{{ $docValue }}">{{ $docLabel }}</label>
                                    </div>
                                  </div>
                                  @endforeach
                                </div>
                                <div class="mt-2">
                                  <label class="form-label fw-semibold mb-1">Other Required Documents (Optional)</label>
                                  <textarea name="documentation_required_custom" rows="2" class="form-control" placeholder="One per line, e.g. community certificate">{{ $customPlacementDocKeys->implode(PHP_EOL) }}</textarea>
                                </div>
                              </div>

                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Location</label>
                                <input class="form-control" name="location" value="{{ $placement->location }}" required>
                              </div>
                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Country (Optional)</label>
                                <input class="form-control" name="country" value="{{ $placement->country }}">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-6">
                        <div class="card h-100">
                          <div class="card-header">Application Info</div>
                          <div class="card-body">
                            <div class="row g-2">
                              <div class="col-lg-6">
                                <label class="form-label fw-semibold">Applicability</label>
                                <select name="applicability_scope" class="form-select js-applicability-scope" required>
                                  <option value="selected_departments" {{ $applicabilityScope === 'selected_departments' ? 'selected' : '' }}>Selected Campus + Selected Departments</option>
                                  <option value="selected_campus_all_departments" {{ $applicabilityScope === 'selected_campus_all_departments' ? 'selected' : '' }}>Selected Campus + All Departments</option>
                                  <option value="both_campuses_all_departments" {{ $applicabilityScope === 'both_campuses_all_departments' ? 'selected' : '' }}>Both Campuses + All Departments</option>
                                </select>
                              </div>
                              <div class="col-lg-6 js-applicability-campus">
                                <label class="form-label fw-semibold">Campus</label>
                                <select name="campus_id" class="form-select js-campus-select" required>
                                  <option value="" disabled>Select campus</option>
                                  @foreach($campuses as $campus)
                                  <option value="{{ $campus->id }}" {{ (int) $placement->campus_id === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                                  @endforeach
                                </select>
                              </div>

                              <div class="col-12 js-applicability-subject">
                                <label class="form-label fw-semibold">Departments / Subjects</label>
                                <div class="border rounded p-2" style="max-height: 220px; overflow: auto;">
                                  @foreach($subjects as $subject)
                                  <div class="form-check js-subject-item" data-campus-id="{{ $subject->campus_id }}">
                                    <input
                                      class="form-check-input js-subject-checkbox"
                                      type="checkbox"
                                      name="subject_ids[]"
                                      value="{{ $subject->id }}"
                                      id="editSubject{{ $placement->id }}_{{ $subject->id }}"
                                      data-campus-id="{{ $subject->campus_id }}"
                                      {{ $selectedSubjectIds->contains((int) $subject->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="editSubject{{ $placement->id }}_{{ $subject->id }}">
                                      {{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}
                                    </label>
                                  </div>
                                  @endforeach
                                </div>
                                <small class="text-muted">Select one or more departments under the selected campus.</small>
                              </div>

                              <div class="col-12">
                                <p class="fw-bold mb-1">Company Info</p>
                              </div>
                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Logo (Optional)</label>
                                <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                              </div>
                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Company (Optional)</label>
                                <input class="form-control" name="company_name" value="{{ $placement->company_name }}">
                              </div>
                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Drive Date</label>
                                <input type="date" class="form-control" name="drive_date" value="{{ $placement->drive_date ? $placement->drive_date->format('Y-m-d') : '' }}">
                              </div>
                              <div class="col-lg-6 col-md-6">
                                <label class="form-label fw-semibold">Apply Deadline</label>
                                <input type="date" class="form-control" name="apply_deadline" value="{{ $placement->apply_deadline ? $placement->apply_deadline->format('Y-m-d') : '' }}">
                              </div>

                              <div class="col-12">
                                <div class="form-check mt-1">
                                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activePlacement{{ $placement->id }}" {{ $placement->is_active ? 'checked' : '' }}>
                                  <label class="form-check-label" for="activePlacement{{ $placement->id }}">Active</label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No job description catalog items added.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addCatalogItemModal" tabindex="-1" aria-labelledby="addCatalogItemModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addCatalogItemModalLabel">Add Job Description</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('tpo.training-placement.job-description.store') }}" method="POST" enctype="multipart/form-data" class="js-placement-form">
          @csrf
          <div class="card">
            <div class="row">
              <div class="mb-2">
                <label class="form-label fw-semibold">Title *</label>
                <input type="text" name="title" class="form-control" required>
              </div>

              <div class="mb-2">
                <label class="form-label fw-semibold"> Description *</label>
                <textarea name="description" class="form-control js-placement-description"></textarea>
              </div>
            </div>
          </div>


          <div class="row g-2 mb-2">


            <div class="col-lg-6">
              <div class="card">
                <div class="card-header">Settings</div>
                <div class="row">

                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Category *</label>
                    <select name="category" class="form-select js-placement-category" required>
                      <option value="" selected disabled>Select category</option>
                      @foreach($categoryOptions as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Applicable Year *</label>
                    <select name="student_year" class="form-select" required>
                      <option value="" selected disabled>Select year</option>
                      @foreach($yearOptions as $yearValue => $yearLabel)
                      <option value="{{ $yearValue }}">{{ $yearLabel }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Month *</label>
                    <select name="month" class="form-select" required>
                      <option value="" selected disabled>Select month</option>
                      @foreach($monthOptions as $monthValue => $monthLabel)
                      <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-lg-12">
                    <div class="mb-2 js-internship-only" style="display:none;">
                      <label class="form-label fw-semibold d-block">Internship Type</label>
                      <div class="d-flex gap-4 flex-wrap">
                        <div class="form-check">
                          <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="createInternshipStipend" value="stipend">
                          <label class="form-check-label" for="createInternshipStipend">Stipend</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="createInternshipNonStipend" value="non_stipend">
                          <label class="form-check-label" for="createInternshipNonStipend">Non Stipend</label>
                        </div>
                      </div>
                    </div>
                  </div>


                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" required>
                  </div>


                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Country (Optional)</label>
                    <input type="text" name="country" class="form-control">
                  </div>


                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Placement Type</label>
                    <select name="placement_type" class="form-select js-placement-required-field">
                      <option value="" selected>Select type</option>
                      @foreach($placementTypeOptions as $typeValue => $typeLabel)
                      <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Opening Type</label>
                    <select name="opening_type" class="form-select js-placement-required-field">
                      <option value="" selected>Select opening type</option>
                      @foreach($openingTypeOptions as $openingValue => $openingLabel)
                      <option value="{{ $openingValue }}">{{ $openingLabel }}</option>
                      @endforeach
                    </select>
                  </div>


                  <div class="col-12">
                    <label class="form-label fw-semibold">Documentation Needed</label>
                    <div class="row g-2">
                      @foreach($documentationRequirementOptions as $docValue => $docLabel)
                      <div class="col-md-4 col-sm-6">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="documentation_required[]" id="docCreate{{ $docValue }}" value="{{ $docValue }}">
                          <label class="form-check-label" for="docCreate{{ $docValue }}">{{ $docLabel }}</label>
                        </div>
                      </div>
                      @endforeach
                    </div>
                    <div class="mt-2">
                      <label class="form-label fw-semibold mb-1">Other Required Documents (Optional)</label>
                      <textarea name="documentation_required_custom" rows="2" class="form-control" placeholder="One per line, e.g. community certificate"></textarea>
                    </div>
                  </div>




                </div>
              </div>

            </div>


            <div class="col-lg-6">
              <div class="card">
                <div class="card-header">Application Info</div>
                <div class="row">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Applicability</label>
                    <select name="applicability_scope" class="form-select js-applicability-scope" required>
                      <option value="selected_departments" selected>Selected Campus + Selected Departments</option>
                      <option value="selected_campus_all_departments">Selected Campus + All Departments</option>
                      <option value="both_campuses_all_departments">Both Campuses + All Departments</option>
                    </select>
                  </div>

                  <div class="col-md-6 js-applicability-campus">
                    <label class="form-label fw-semibold">Campus</label>
                    <select name="campus_id" class="form-select js-campus-select" required>
                      <option value="" selected disabled>Select campus</option>
                      @foreach($campuses as $campus)
                      <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                      @endforeach
                    </select>
                  </div>


                </div>

                <div class="col-md-12 js-applicability-subject">
                  <label class="form-label fw-semibold">Departments / Subjects</label>
                  <div class="border rounded p-2" style="max-height: 220px; overflow: auto;">
                    @foreach($subjects as $subject)
                    <div class="form-check js-subject-item" data-campus-id="{{ $subject->campus_id }}">
                      <input
                        class="form-check-input js-subject-checkbox"
                        type="checkbox"
                        name="subject_ids[]"
                        value="{{ $subject->id }}"
                        id="createSubject{{ $subject->id }}"
                        data-campus-id="{{ $subject->campus_id }}">
                      <label class="form-check-label" for="createSubject{{ $subject->id }}">
                        {{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}
                      </label>
                    </div>
                    @endforeach
                  </div>
                  <small class="text-muted">Select one or more departments under the selected campus.</small>
                </div>

                <p class="fw-bold">Company Info</p>

                <div class="col-md-12">
                  <label class="form-label fw-semibold">Logo (Optional)</label>
                  <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Company (Optional)</label>
                  <input type="text" name="company_name" class="form-control">
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Drive Date</label>
                    <input type="date" name="drive_date" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Apply Deadline</label>
                    <input type="date" name="apply_deadline" class="form-control">
                  </div>
                </div>
              </div>


            </div>

          </div>









          <div class="border rounded p-2 mb-2 js-placement-only" style="display:none;">
            <div class="row g-2">

            </div>
          </div>

          <div class="row g-2 mb-2">

          </div>
          <div class="row g-2">

          </div>

          <div class="mt-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary" type="submit">Create Job Description</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var forms = document.querySelectorAll('.js-placement-form');
    var ckEditors = new Map();

    var ckConfig = {
      removePlugins: [
        'CKFinderUploadAdapter',
        'CKFinder',
        'EasyImage',
        'Image',
        'ImageCaption',
        'ImageStyle',
        'ImageToolbar',
        'ImageUpload',
        'MediaEmbed'
      ]
    };

    function stripHtml(value) {
      return String(value || '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/&nbsp;/gi, ' ')
        .trim();
    }

    function initEditorsIn(container) {
      if (typeof ClassicEditor === 'undefined' || !container) {
        return;
      }

      var textareas = container.querySelectorAll('.js-placement-description');
      textareas.forEach(function(textarea) {
        if (ckEditors.has(textarea)) {
          return;
        }

        ClassicEditor.create(textarea, ckConfig)
          .then(function(editor) {
            ckEditors.set(textarea, editor);
          })
          .catch(function(error) {
            console.error(error);
          });
      });
    }

    var createModal = document.getElementById('addCatalogItemModal');
    if (createModal) {
      createModal.addEventListener('shown.bs.modal', function() {
        initEditorsIn(createModal);
      });
    }

    document.querySelectorAll('[id^="placementManage"]').forEach(function(panel) {
      panel.addEventListener('shown.bs.collapse', function() {
        initEditorsIn(panel);
      });
    });

    forms.forEach(function(form) {
      var categorySelect = form.querySelector('.js-placement-category') || form.querySelector('select[name="category"]');
      var applicabilityScopeSelect = form.querySelector('.js-applicability-scope');
      var applicabilityCampusWrapper = form.querySelector('.js-applicability-campus');
      var applicabilitySubjectWrapper = form.querySelector('.js-applicability-subject');
      var campusSelect = form.querySelector('.js-campus-select');
      var subjectCheckboxes = Array.prototype.slice.call(form.querySelectorAll('.js-subject-checkbox'));
      var subjectItems = Array.prototype.slice.call(form.querySelectorAll('.js-subject-item'));

      function applyPlacementCategoryRules() {
        if (!categorySelect) {
          return;
        }

        var isPlacementCategory = categorySelect.value === 'placements' || categorySelect.value === 'placement';
        var isInternshipCategory = categorySelect.value === 'internship';
        form.querySelectorAll('.js-placement-only').forEach(function(section) {
          section.style.display = isPlacementCategory ? '' : 'none';
        });

        form.querySelectorAll('.js-internship-only').forEach(function(section) {
          section.style.display = isInternshipCategory ? '' : 'none';
        });

        form.querySelectorAll('.js-placement-required-field').forEach(function(input) {
          if (isPlacementCategory) {
            input.setAttribute('required', 'required');
          } else {
            input.removeAttribute('required');
          }
        });

        form.querySelectorAll('.js-internship-required').forEach(function(input) {
          if (isInternshipCategory) {
            input.setAttribute('required', 'required');
          } else {
            input.removeAttribute('required');
          }
        });
      }

      function applyCampusSubjectFilter() {
        if (!campusSelect || subjectCheckboxes.length === 0) {
          return;
        }

        var campusId = campusSelect.value;

        // Keep the same option nodes to preserve native multi-select behavior.
        subjectItems.forEach(function(item) {
          var optionCampusId = item.getAttribute('data-campus-id');
          var checkbox = item.querySelector('.js-subject-checkbox');
          if (!checkbox || !optionCampusId) {
            return;
          }

          var isVisible = !!campusId && String(optionCampusId) === String(campusId);
          item.style.display = isVisible ? '' : 'none';
          checkbox.disabled = !isVisible;

          if (!isVisible) {
            checkbox.checked = false;
          }
        });
      }

      function applySubjectRequiredRule(isRequired) {
        var visibleEnabledCheckboxes = subjectCheckboxes.filter(function(checkbox) {
          var item = checkbox.closest('.js-subject-item');
          var isVisible = item && item.style.display !== 'none';
          return isVisible && !checkbox.disabled;
        });

        visibleEnabledCheckboxes.forEach(function(checkbox) {
          checkbox.required = false;
        });

        if (!isRequired || visibleEnabledCheckboxes.length === 0) {
          return;
        }

        var anyChecked = visibleEnabledCheckboxes.some(function(checkbox) {
          return checkbox.checked;
        });

        if (!anyChecked) {
          visibleEnabledCheckboxes[0].required = true;
        }
      }

      function applyApplicabilityScopeRules() {
        if (!applicabilityScopeSelect) {
          return;
        }

        var scope = applicabilityScopeSelect.value || 'selected_departments';
        var showCampus = scope !== 'both_campuses_all_departments';
        var showSubjects = scope === 'selected_departments';

        if (applicabilityCampusWrapper) {
          applicabilityCampusWrapper.style.display = showCampus ? '' : 'none';
        }
        if (applicabilitySubjectWrapper) {
          applicabilitySubjectWrapper.style.display = showSubjects ? '' : 'none';
        }

        if (campusSelect) {
          if (showCampus) {
            campusSelect.setAttribute('required', 'required');
          } else {
            campusSelect.removeAttribute('required');
            campusSelect.value = '';
          }
        }

        if (subjectCheckboxes.length > 0) {
          if (!showSubjects) {
            subjectCheckboxes.forEach(function(checkbox) {
              checkbox.checked = false;
              checkbox.required = false;
            });
          }
        }

        applyCampusSubjectFilter();
        applySubjectRequiredRule(showSubjects);
      }

      if (categorySelect) {
        categorySelect.addEventListener('change', applyPlacementCategoryRules);
        applyPlacementCategoryRules();
      }

      if (campusSelect) {
        campusSelect.addEventListener('change', applyCampusSubjectFilter);
        campusSelect.addEventListener('change', function() {
          var showSubjects = applicabilityScopeSelect && applicabilityScopeSelect.value === 'selected_departments';
          applySubjectRequiredRule(!!showSubjects);
        });
        applyCampusSubjectFilter();
      }

      if (subjectCheckboxes.length > 0) {
        subjectCheckboxes.forEach(function(checkbox) {
          checkbox.addEventListener('change', function() {
            var showSubjects = applicabilityScopeSelect && applicabilityScopeSelect.value === 'selected_departments';
            applySubjectRequiredRule(!!showSubjects);
          });
        });
      }

      if (applicabilityScopeSelect) {
        applicabilityScopeSelect.addEventListener('change', applyApplicabilityScopeRules);
        applyApplicabilityScopeRules();
      }

      form.addEventListener('submit', function(event) {
        var descriptions = form.querySelectorAll('.js-placement-description');
        var hasInvalidDescription = false;

        descriptions.forEach(function(textarea) {
          var editor = ckEditors.get(textarea);

          if (editor) {
            var data = editor.getData();
            textarea.value = data;
            if (stripHtml(data) === '') {
              hasInvalidDescription = true;
            }
            return;
          }

          if (stripHtml(textarea.value) === '') {
            hasInvalidDescription = true;
          }
        });

        if (hasInvalidDescription) {
          event.preventDefault();
          alert('Please enter description before submitting.');
        }
      });
    });

    initEditorsIn(document);
  });
</script>

@include('includes.footer')
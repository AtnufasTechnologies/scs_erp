@include('includes.header')

<div class="wrapper">
  @include('admin.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Training & Placement Office</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Training & Placement</li>
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
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Training and Placement Office</h4>
              <p class="text-muted mb-0">Manage role-based training, learning resources, survey outcomes, and placement opportunities.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-pie me-1"></i>Dashboard
              </a>
              <a href="{{ route('tpo.training-placement.analytics') }}" class="btn btn-primary">
                <i class="fas fa-chart-line me-1"></i>View Completion Analytics
              </a>
            </div>
          </div>
        </div>
      </div>

      <ul class="nav nav-tabs mb-3" id="tpoTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="training-tab" data-bs-toggle="tab" data-bs-target="#training-pane" type="button" role="tab">Training</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="placement-tab" data-bs-toggle="tab" data-bs-target="#placement-pane" type="button" role="tab">Placement</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="my-training-tab" data-bs-toggle="tab" data-bs-target="#my-training-pane" type="button" role="tab">My Trainings</button>
        </li>
      </ul>

      <div class="tab-content" id="tpoTabContent">
        <div class="tab-pane fade show active" id="training-pane" role="tabpanel" aria-labelledby="training-tab">
          <div class="row g-4">
            <div class="col-xl-5">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0 fw-bold">Add New Training</h6>
                </div>
                <div class="card-body">
                  <form action="{{ route('tpo.training-placement.training.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Training Title</label>
                      <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Description</label>
                      <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Applicable Roles</label>
                      <select name="applicable_roles[]" class="form-select" multiple size="8" required>
                        @foreach($roleOptions as $role)
                        <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                        @endforeach
                      </select>
                      <small class="text-muted">Use Ctrl/Cmd + click to select multiple roles.</small>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-plus me-1"></i>Create Training</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-xl-7">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0 fw-bold">All Trainings</h6>
                </div>
                <div class="card-body">
                  @forelse($trainings as $training)
                  <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                      <div>
                        <h6 class="mb-1 fw-bold">{{ $training->title }}</h6>
                        <p class="mb-1 text-muted">{{ $training->description ?: 'No description added.' }}</p>
                        <small>
                          <strong>Applicable for:</strong>
                          {{ $training->targetRoles->pluck('role_name')->map(fn($role) => ucfirst(str_replace('-', ' ', $role)))->implode(', ') }}
                        </small>
                      </div>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#trainingManage{{ $training->id }}">Manage</button>
                        <form action="{{ route('tpo.training-placement.training.destroy', $training->id) }}" method="POST" onsubmit="return confirm('Delete this training?')">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                      </div>
                    </div>

                    <div class="collapse mt-3" id="trainingManage{{ $training->id }}">
                      <div class="row g-3">
                        <div class="col-lg-12">
                          <form action="{{ route('tpo.training-placement.training.update', $training->id) }}" method="POST" class="border rounded p-3">
                            @csrf
                            @method('PUT')
                            <h6 class="fw-semibold mb-2">Update Training</h6>
                            <div class="row g-2">
                              <div class="col-md-6">
                                <input type="text" class="form-control" name="title" value="{{ $training->title }}" required>
                              </div>
                              <div class="col-md-6">
                                <select name="applicable_roles[]" class="form-select" multiple required>
                                  @foreach($roleOptions as $role)
                                  <option value="{{ $role['value'] }}" {{ $training->targetRoles->pluck('role_name')->contains($role['value']) ? 'selected' : '' }}>{{ $role['label'] }}</option>
                                  @endforeach
                                </select>
                              </div>
                              <div class="col-12">
                                <textarea name="description" class="form-control" rows="2">{{ $training->description }}</textarea>
                              </div>
                              <div class="col-12">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeTraining{{ $training->id }}" {{ $training->is_active ? 'checked' : '' }}>
                                  <label class="form-check-label" for="activeTraining{{ $training->id }}">Active</label>
                                </div>
                              </div>
                              <div class="col-12">
                                <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                              </div>
                            </div>
                          </form>
                        </div>

                        <div class="col-lg-6">
                          <form action="{{ route('tpo.training-placement.resource.store', $training->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3">
                            @csrf
                            <h6 class="fw-semibold mb-2">Upload Resource</h6>
                            <input type="text" name="resource_title" class="form-control mb-2" placeholder="Resource title">
                            <input type="file" name="resource_file" class="form-control mb-2" accept=".ppt,.pptx,.doc,.docx,.pdf" required>
                            <button class="btn btn-sm btn-success" type="submit">Upload</button>
                            <small class="d-block text-muted mt-2">Allowed: PPT, DOC, PDF (Max 50MB)</small>
                          </form>

                          <div class="border rounded p-3 mt-2">
                            <h6 class="fw-semibold mb-2">Resources</h6>
                            @forelse($training->resources as $resource)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <a href="{{ Storage::disk('s3')->url($resource->file_path) }}" target="_blank">{{ $resource->resource_title ?: $resource->file_name }}</a>
                              <form action="{{ route('tpo.training-placement.resource.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Remove this resource?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">X</button>
                              </form>
                            </div>
                            @empty
                            <small class="text-muted">No resources uploaded.</small>
                            @endforelse
                          </div>
                        </div>

                        <div class="col-lg-6">
                          <form action="{{ route('tpo.training-placement.survey-question.store', $training->id) }}" method="POST" class="border rounded p-3">
                            @csrf
                            <h6 class="fw-semibold mb-2">Add Survey Question</h6>
                            <input type="text" name="question_text" class="form-control mb-2" placeholder="Question text" required>
                            <textarea name="options_text" class="form-control mb-2" rows="4" placeholder="Option 1|5&#10;Option 2|4&#10;Option 3|3" required></textarea>
                            <small class="d-block text-muted mb-2">One option per line in format: option text|score</small>
                            <button class="btn btn-sm btn-warning" type="submit">Add Question</button>
                          </form>

                          <div class="border rounded p-3 mt-2">
                            <h6 class="fw-semibold mb-2">Survey Questions</h6>
                            @forelse($training->surveyQuestions as $question)
                            <div class="mb-2">
                              <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                  <div class="fw-semibold">{{ $question->question_text }}</div>
                                  <small class="text-muted">{{ $question->options->map(fn($opt) => $opt->option_text . ' (' . $opt->score . ')')->implode(' | ') }}</small>
                                </div>
                                <form action="{{ route('tpo.training-placement.survey-question.destroy', $question->id) }}" method="POST" onsubmit="return confirm('Delete this question?')">
                                  @csrf
                                  @method('DELETE')
                                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                              </div>
                            </div>
                            @empty
                            <small class="text-muted">No survey questions added.</small>
                            @endforelse
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @empty
                  <div class="alert alert-info mb-0">No trainings found.</div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="placement-pane" role="tabpanel" aria-labelledby="placement-tab">
          <div class="row g-4">
            <div class="col-12">
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminPlacementModal">
                  <i class="fas fa-plus me-1"></i>Add Placement Opportunity
                </button>
              </div>
            </div>

            <div class="col-xl-12">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0 fw-bold">Placement Listings</h6>
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
                  @endphp
                  <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                      <div class="w-100">
                        <h6 class="mb-1 fw-bold">{{ $placement->title }}</h6>
                        <div class="small text-muted mb-1">Category: {{ $categoryOptions[$placement->category] ?? ucfirst($placement->category ?? 'N/A') }}</div>
                        <div class="small text-muted mb-1">Campus: {{ $isBothCampusesAllDepartments ? 'Both Campuses' : ($placement->campus->name ?? 'N/A') }}</div>
                        <div class="small text-muted mb-1">Location: {{ $placement->location ?: 'N/A' }}{{ $placement->country ? ', ' . $placement->country : '' }}</div>
                        <div class="small text-muted mb-1">Departments: {{ $isBothCampusesAllDepartments ? 'All Departments (Both Campuses)' : ($isSelectedCampusAllDepartments ? 'All Departments (Selected Campus)' : ($selectedSubjectNames->isNotEmpty() ? $selectedSubjectNames->implode(', ') : 'N/A')) }}</div>
                        @if($placement->category === 'internship')
                        <div class="small text-muted mb-1">Internship Type: {{ $placement->internship_stipend_type === 'stipend' ? 'Stipend' : ($placement->internship_stipend_type === 'non_stipend' ? 'Non Stipend' : 'N/A') }}</div>
                        @endif
                        @if($placement->category === 'placements')
                        <div class="small text-muted mb-1">Placement Type: {{ $placementTypeOptions[$placement->placement_type] ?? 'N/A' }}</div>
                        <div class="small text-muted mb-1">Opening Type: {{ $openingTypeOptions[$placement->opening_type] ?? 'N/A' }}</div>
                        <div class="small text-muted mb-1">Documentation Needed: {{ !empty($placement->documentation_required) ? implode(', ', $placement->documentation_required) : 'N/A' }}</div>
                        @endif
                        <div class="small text-muted mb-1">Company: {{ $placement->company_name ?: 'N/A' }}</div>
                        <div class="small text-muted mb-1">Drive: {{ $placement->drive_date ? $placement->drive_date->format('d M Y') : 'N/A' }} | Deadline: {{ $placement->apply_deadline ? $placement->apply_deadline->format('d M Y') : 'N/A' }}</div>
                        <p class="mb-1">{{ $placement->description ?: 'No description provided.' }}</p>
                      </div>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#placementManage{{ $placement->id }}">Edit</button>
                        <form action="{{ route('tpo.training-placement.placement.destroy', $placement->id) }}" method="POST" onsubmit="return confirm('Delete this opportunity?')">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </div>
                    </div>

                    <div class="collapse mt-3" id="placementManage{{ $placement->id }}">
                      <form action="{{ route('tpo.training-placement.placement.update', $placement->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 js-placement-form">
                        @csrf
                        @method('PUT')
                        <div class="row g-2">
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Title</label>
                            <input class="form-control" name="title" value="{{ $placement->title }}" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select js-placement-category" required>
                              @foreach($categoryOptions as $value => $label)
                              <option value="{{ $value }}" {{ $placement->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Month</label>
                            <select name="month" class="form-select" required>
                              @foreach($monthOptions as $monthValue => $monthLabel)
                              <option value="{{ $monthValue }}" {{ (int) $placement->month === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Applicability</label>
                            <select name="applicability_scope" class="form-select js-applicability-scope" required>
                              <option value="selected_departments" {{ $applicabilityScope === 'selected_departments' ? 'selected' : '' }}>Selected Campus + Selected Departments</option>
                              <option value="selected_campus_all_departments" {{ $applicabilityScope === 'selected_campus_all_departments' ? 'selected' : '' }}>Selected Campus + All Departments</option>
                              <option value="both_campuses_all_departments" {{ $applicabilityScope === 'both_campuses_all_departments' ? 'selected' : '' }}>Both Campuses + All Departments</option>
                            </select>
                          </div>
                          <div class="col-md-6 js-applicability-campus">
                            <label class="form-label fw-semibold">Campus</label>
                            <select name="campus_id" class="form-select js-campus-select" required>
                              @foreach($campuses as $campus)
                              <option value="{{ $campus->id }}" {{ (int) $placement->campus_id === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Applicable Student Year</label>
                            <select name="student_year" class="form-select" required>
                              @foreach($yearOptions as $year)
                              <option value="{{ $year }}" {{ $placement->student_year === $year ? 'selected' : '' }}>{{ $year }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6 js-applicability-subject">
                            <label class="form-label fw-semibold">Departments / Subjects</label>
                            <select name="subject_ids[]" class="form-select select-multiple js-subject-select" multiple size="7" required>
                              @foreach($subjects as $subject)
                              <option value="{{ $subject->id }}" data-campus-id="{{ $subject->campus_id }}" {{ $selectedSubjectIds->contains((int) $subject->id) ? 'selected' : '' }}>{{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="description" rows="2" required>{{ $placement->description }}</textarea>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input class="form-control" name="location" value="{{ $placement->location }}" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Country (Optional)</label>
                            <input class="form-control" name="country" value="{{ $placement->country }}">
                          </div>

                          <div class="col-12 js-placement-only" style="display:none;">
                            <div class="border rounded p-2">
                              <div class="row g-2">
                                <div class="col-md-6">
                                  <label class="form-label fw-semibold">Placement Type</label>
                                  <select name="placement_type" class="form-select js-placement-required">
                                    <option value="">Select type</option>
                                    @foreach($placementTypeOptions as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}" {{ $placement->placement_type === $typeValue ? 'selected' : '' }}>{{ $typeLabel }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label fw-semibold">Opening Type</label>
                                  <select name="opening_type" class="form-select js-placement-required">
                                    <option value="">Select opening type</option>
                                    @foreach($openingTypeOptions as $openingValue => $openingLabel)
                                    <option value="{{ $openingValue }}" {{ $placement->opening_type === $openingValue ? 'selected' : '' }}>{{ $openingLabel }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-12">
                                  <label class="form-label fw-semibold">Documentation Needed</label>
                                  <textarea name="documentation_required_text" rows="3" class="form-control js-placement-required">{{ !empty($placement->documentation_required) ? implode(PHP_EOL, $placement->documentation_required) : '' }}</textarea>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="col-12 js-internship-only" style="display:none;">
                            <label class="form-label fw-semibold d-block">Internship Type</label>
                            <div class="d-flex gap-4 flex-wrap">
                              <div class="form-check">
                                <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="adminInternshipStipend{{ $placement->id }}" value="stipend" {{ $placement->internship_stipend_type === 'stipend' ? 'checked' : '' }}>
                                <label class="form-check-label" for="adminInternshipStipend{{ $placement->id }}">Stipend</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="adminInternshipNonStipend{{ $placement->id }}" value="non_stipend" {{ $placement->internship_stipend_type === 'non_stipend' ? 'checked' : '' }}>
                                <label class="form-check-label" for="adminInternshipNonStipend{{ $placement->id }}">Non Stipend</label>
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Company (Optional)</label>
                            <input class="form-control" name="company_name" value="{{ $placement->company_name }}">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-semibold">Logo (Optional)</label>
                            <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label fw-semibold">Drive Date</label>
                            <input type="date" class="form-control" name="drive_date" value="{{ $placement->drive_date ? $placement->drive_date->format('Y-m-d') : '' }}">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label fw-semibold">Apply Deadline</label>
                            <input type="date" class="form-control" name="apply_deadline" value="{{ $placement->apply_deadline ? $placement->apply_deadline->format('Y-m-d') : '' }}">
                          </div>
                          <div class="col-12">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activePlacement{{ $placement->id }}" {{ $placement->is_active ? 'checked' : '' }}>
                              <label class="form-check-label" for="activePlacement{{ $placement->id }}">Active</label>
                            </div>
                          </div>
                          <div class="col-12">
                            <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                  @empty
                  <div class="alert alert-info mb-0">No placement opportunities added.</div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="addAdminPlacementModal" tabindex="-1" aria-labelledby="addAdminPlacementModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAdminPlacementModalLabel">Add Placement Opportunity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form action="{{ route('tpo.training-placement.placement.store') }}" method="POST" enctype="multipart/form-data" class="js-placement-form">
                  @csrf
                  <div class="mb-2">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" required>
                  </div>
                  <div class="mb-2">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select js-placement-category" required>
                      <option value="" selected disabled>Select category</option>
                      @foreach($categoryOptions as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Month</label>
                      <select name="month" class="form-select" required>
                        <option value="" selected disabled>Select month</option>
                        @foreach($monthOptions as $monthValue => $monthLabel)
                        <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Applicability</label>
                      <select name="applicability_scope" class="form-select js-applicability-scope" required>
                        <option value="selected_departments" selected>Selected Campus + Selected Departments</option>
                        <option value="selected_campus_all_departments">Selected Campus + All Departments</option>
                        <option value="both_campuses_all_departments">Both Campuses + All Departments</option>
                      </select>
                    </div>
                  </div>
                  <div class="row g-2 mb-2">
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
                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Applicable Student Year</label>
                      <select name="student_year" class="form-select" required>
                        <option value="" selected disabled>Select year</option>
                        @foreach($yearOptions as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-6 js-applicability-subject">
                      <label class="form-label fw-semibold">Departments / Subjects</label>
                      <select name="subject_ids[]" class="form-select select-multiple js-subject-select" multiple size="7" required>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" data-campus-id="{{ $subject->campus_id }}">{{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="3" class="form-control" required></textarea>
                  </div>

                  <div class="mb-2 js-internship-only" style="display:none;">
                    <label class="form-label fw-semibold d-block">Internship Type</label>
                    <div class="d-flex gap-4 flex-wrap">
                      <div class="form-check">
                        <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="addAdminInternshipStipend" value="stipend">
                        <label class="form-check-label" for="addAdminInternshipStipend">Stipend</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input js-internship-required" type="radio" name="internship_stipend_type" id="addAdminInternshipNonStipend" value="non_stipend">
                        <label class="form-check-label" for="addAdminInternshipNonStipend">Non Stipend</label>
                      </div>
                    </div>
                  </div>
                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Location</label>
                      <input type="text" name="location" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Country (Optional)</label>
                      <input type="text" name="country" class="form-control">
                    </div>
                  </div>

                  <div class="border rounded p-2 mb-2 js-placement-only" style="display:none;">
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Placement Type</label>
                        <select name="placement_type" class="form-select js-placement-required">
                          <option value="" selected>Select type</option>
                          @foreach($placementTypeOptions as $typeValue => $typeLabel)
                          <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Opening Type</label>
                        <select name="opening_type" class="form-select js-placement-required">
                          <option value="" selected>Select opening type</option>
                          @foreach($openingTypeOptions as $openingValue => $openingLabel)
                          <option value="{{ $openingValue }}">{{ $openingLabel }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">Documentation Needed</label>
                        <textarea name="documentation_required_text" rows="3" class="form-control js-placement-required" placeholder="Aadhaar Card&#10;Resume&#10;Marksheet"></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Company (Optional)</label>
                      <input type="text" name="company_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Logo (Optional)</label>
                      <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                  </div>
                  <div class="row g-2 mb-2">
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Drive Date</label>
                      <input type="date" name="drive_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Apply Deadline</label>
                      <input type="date" name="apply_deadline" class="form-control">
                    </div>
                  </div>

                  <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Create Opportunity</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="tab-pane fade" id="my-training-pane" role="tabpanel" aria-labelledby="my-training-tab">

          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">My Assigned Trainings</h6>
            </div>
            <div class="card-body">
              @forelse($myTrainings as $training)
              @php
              $attempt = $training->attempts->where('user_id', auth()->id())->first();
              @endphp
              <div class="d-flex flex-wrap justify-content-between align-items-center border rounded p-3 mb-2">
                <div>
                  <h6 class="mb-1">{{ $training->title }}</h6>
                  <small class="text-muted">{{ $training->description ?: 'No description available.' }}</small>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                  @if($attempt && $attempt->completed_at)
                  <span class="badge bg-success">Completed</span>
                  @else
                  <span class="badge bg-warning text-dark">Pending</span>
                  @endif
                  <a href="{{ route('tpo.training-placement.attempt', $training->id) }}" class="btn btn-sm btn-primary">Attempt Survey</a>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No trainings assigned to your role.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-transparent">
          <h6 class="mb-0 fw-bold">Quick Analytics (Completion Rate)</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Training</th>
                  <th>Target Roles</th>
                  <th>Assigned Users</th>
                  <th>Completed</th>
                  <th>Completion %</th>
                </tr>
              </thead>
              <tbody>
                @forelse($analytics as $item)
                <tr>
                  <td>{{ $item['title'] }}</td>
                  <td>{{ $item['target_roles']->map(fn($role) => ucfirst(str_replace('-', ' ', $role)))->implode(', ') }}</td>
                  <td>{{ $item['assigned_users'] }}</td>
                  <td>{{ $item['completed_users'] }}</td>
                  <td>
                    <span class="badge {{ $item['completion_rate'] >= 80 ? 'bg-success' : ($item['completion_rate'] >= 40 ? 'bg-warning text-dark' : 'bg-danger') }}">
                      {{ $item['completion_rate'] }}%
                    </span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">No analytics data available.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var forms = document.querySelectorAll('.js-placement-form');

    forms.forEach(function(form) {
      var categorySelect = form.querySelector('.js-placement-category') || form.querySelector('select[name="category"]');
      var applicabilityScopeSelect = form.querySelector('.js-applicability-scope');
      var applicabilityCampusWrapper = form.querySelector('.js-applicability-campus');
      var applicabilitySubjectWrapper = form.querySelector('.js-applicability-subject');
      var campusSelect = form.querySelector('.js-campus-select');
      var subjectSelect = form.querySelector('.js-subject-select');

      function applyPlacementCategoryRules() {
        if (!categorySelect) {
          return;
        }

        var isPlacementCategory = categorySelect.value === 'placements';
        var isInternshipCategory = categorySelect.value === 'internship';
        form.querySelectorAll('.js-placement-only').forEach(function(section) {
          section.style.display = isPlacementCategory ? '' : 'none';
        });

        form.querySelectorAll('.js-internship-only').forEach(function(section) {
          section.style.display = isInternshipCategory ? '' : 'none';
        });

        form.querySelectorAll('.js-placement-required').forEach(function(input) {
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
        if (!campusSelect || !subjectSelect) {
          return;
        }

        var campusId = campusSelect.value;
        Array.prototype.slice.call(subjectSelect.options).forEach(function(option) {
          var optionCampusId = option.getAttribute('data-campus-id');
          if (!option.value || !optionCampusId || !campusId) {
            option.hidden = false;
            option.disabled = false;
            return;
          }

          var isMatch = String(optionCampusId) === String(campusId);
          option.hidden = !isMatch;
          option.disabled = !isMatch;
          if (!isMatch) {
            option.selected = false;
          }
        });
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

        if (subjectSelect) {
          if (showSubjects) {
            subjectSelect.setAttribute('required', 'required');
          } else {
            subjectSelect.removeAttribute('required');
            Array.prototype.slice.call(subjectSelect.options).forEach(function(option) {
              option.selected = false;
            });
          }
        }

        applyCampusSubjectFilter();
      }

      if (categorySelect) {
        categorySelect.addEventListener('change', applyPlacementCategoryRules);
        applyPlacementCategoryRules();
      }

      if (campusSelect) {
        campusSelect.addEventListener('change', applyCampusSubjectFilter);
        applyCampusSubjectFilter();
      }

      if (applicabilityScopeSelect) {
        applicabilityScopeSelect.addEventListener('change', applyApplicabilityScopeRules);
        applyApplicabilityScopeRules();
      }
    });
  });
</script>

@include('includes.footer')
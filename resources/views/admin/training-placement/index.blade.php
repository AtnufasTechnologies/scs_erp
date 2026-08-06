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
            <div class="col-xl-5">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0 fw-bold">Add Placement Opportunity</h6>
                </div>
                <div class="card-body">
                  <form action="{{ route('tpo.training-placement.placement.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                      <label class="form-label fw-semibold">Title</label>
                      <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label fw-semibold">Company</label>
                      <input type="text" name="company_name" class="form-control">
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
                    <div class="mb-2">
                      <label class="form-label fw-semibold">Description</label>
                      <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-semibold">Applicable Roles</label>
                      <select name="applicable_roles[]" class="form-select" multiple size="7" required>
                        @foreach($roleOptions as $role)
                        <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Create Opportunity</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-xl-7">
              <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                  <h6 class="mb-0 fw-bold">Placement Listings</h6>
                </div>
                <div class="card-body">
                  @forelse($placements as $placement)
                  <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                      <div>
                        <h6 class="mb-1 fw-bold">{{ $placement->title }}</h6>
                        <div class="small text-muted mb-1">Company: {{ $placement->company_name ?: 'N/A' }}</div>
                        <div class="small text-muted mb-1">Drive: {{ $placement->drive_date ? $placement->drive_date->format('d M Y') : 'N/A' }} | Deadline: {{ $placement->apply_deadline ? $placement->apply_deadline->format('d M Y') : 'N/A' }}</div>
                        <p class="mb-1">{{ $placement->description ?: 'No description provided.' }}</p>
                        <small><strong>Applicable for:</strong> {{ $placement->targetRoles->pluck('role_name')->map(fn($role) => ucfirst(str_replace('-', ' ', $role)))->implode(', ') }}</small>
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
                      <form action="{{ route('tpo.training-placement.placement.update', $placement->id) }}" method="POST" class="border rounded p-3">
                        @csrf
                        @method('PUT')
                        <div class="row g-2">
                          <div class="col-md-6"><input class="form-control" name="title" value="{{ $placement->title }}" required></div>
                          <div class="col-md-6"><input class="form-control" name="company_name" value="{{ $placement->company_name }}"></div>
                          <div class="col-md-6"><input type="date" class="form-control" name="drive_date" value="{{ $placement->drive_date ? $placement->drive_date->format('Y-m-d') : '' }}"></div>
                          <div class="col-md-6"><input type="date" class="form-control" name="apply_deadline" value="{{ $placement->apply_deadline ? $placement->apply_deadline->format('Y-m-d') : '' }}"></div>
                          <div class="col-12"><textarea class="form-control" name="description" rows="2">{{ $placement->description }}</textarea></div>
                          <div class="col-12">
                            <select name="applicable_roles[]" class="form-select" multiple required>
                              @foreach($roleOptions as $role)
                              <option value="{{ $role['value'] }}" {{ $placement->targetRoles->pluck('role_name')->contains($role['value']) ? 'selected' : '' }}>{{ $role['label'] }}</option>
                              @endforeach
                            </select>
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

@include('includes.footer')
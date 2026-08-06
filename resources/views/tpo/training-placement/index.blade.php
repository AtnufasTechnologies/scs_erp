@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

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
              <p class="text-muted mb-0">Manage role-based training, learning resources, survey outcomes, and completion tracking.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-pie me-1"></i>Dashboard
              </a>
              <a href="{{ route('tpo.training-placement.placement.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-briefcase me-1"></i>Go to Placement
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
                      <label class="form-label fw-semibold">Applicable To Roles</label>
                      <select name="applicable_roles[]" class="select-multiple" multiple size="8" required>
                        @foreach($roleOptions as $role)
                        <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button class="btn btn-success" type="submit"><i class="fas fa-plus me-1"></i>Create Training</button>
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
                                <select name="applicable_roles[]" class="select-multiple" multiple required>
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
                          <form action="{{ route('tpo.training-placement.survey-question.store', $training->id) }}" method="POST" class="border rounded p-3 js-ajax-survey-question-form" data-training-id="{{ $training->id }}">
                            @csrf
                            <h6 class="fw-semibold mb-2">Add Survey Question</h6>
                            <input type="text" name="question_text" class="form-control mb-2" placeholder="Question text" required>
                            <textarea name="options_text" class="form-control mb-2" rows="4" placeholder="Option 1|5&#10;Option 2|4&#10;Option 3|3" required></textarea>
                            <small class="d-block text-muted mb-2">One option per line in format: option text|score</small>
                            <div class="js-survey-question-feedback small mb-2"></div>
                            <button class="btn btn-sm btn-warning" type="submit">Add Question</button>
                          </form>

                          <div class="border rounded p-3 mt-2">
                            <h6 class="fw-semibold mb-2">Survey Questions</h6>
                            <div id="survey-question-list-{{ $training->id }}">
                              @forelse($training->surveyQuestions as $question)
                              <div class="mb-2 js-survey-question-item" data-question-id="{{ $question->id }}">
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
                              <small class="text-muted js-survey-question-empty">No survey questions added.</small>
                              @endforelse
                            </div>
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
  (function() {
    function escapeHtml(text) {
      return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function buildQuestionHtml(question) {
      const optionsText = (question.options || [])
        .map(function(opt) {
          return escapeHtml(opt.option_text) + ' (' + escapeHtml(opt.score) + ')';
        })
        .join(' | ');

      return '' +
        '<div class="mb-2 js-survey-question-item" data-question-id="' + escapeHtml(question.id) + '">' +
        '  <div class="d-flex justify-content-between align-items-start gap-2">' +
        '    <div>' +
        '      <div class="fw-semibold">' + escapeHtml(question.question_text) + '</div>' +
        '      <small class="text-muted">' + optionsText + '</small>' +
        '    </div>' +
        '    <form action="' + escapeHtml(question.delete_url) + '" method="POST" onsubmit="return confirm(\'Delete this question?\')">' +
        '      <input type="hidden" name="_token" value="{{ csrf_token() }}">' +
        '      <input type="hidden" name="_method" value="DELETE">' +
        '      <button class="btn btn-sm btn-outline-danger">Delete</button>' +
        '    </form>' +
        '  </div>' +
        '</div>';
    }

    function setFeedback(container, message, isError) {
      if (!container) {
        return;
      }

      container.classList.remove('text-danger', 'text-success');
      container.classList.add(isError ? 'text-danger' : 'text-success');
      container.textContent = message || '';
    }

    document.addEventListener('submit', function(event) {
      const form = event.target.closest('.js-ajax-survey-question-form');
      if (!form) {
        return;
      }

      event.preventDefault();

      const trainingId = form.getAttribute('data-training-id');
      const listContainer = document.getElementById('survey-question-list-' + trainingId);
      const emptyNode = listContainer ? listContainer.querySelector('.js-survey-question-empty') : null;
      const feedbackNode = form.querySelector('.js-survey-question-feedback');
      const submitButton = form.querySelector('button[type="submit"]');

      if (submitButton) {
        submitButton.disabled = true;
      }
      setFeedback(feedbackNode, '', false);

      fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: new FormData(form)
        })
        .then(function(response) {
          if (!response.ok) {
            return response.json().then(function(payload) {
              throw payload;
            });
          }
          return response.json();
        })
        .then(function(payload) {
          if (emptyNode) {
            emptyNode.remove();
          }

          if (listContainer && payload.question) {
            listContainer.insertAdjacentHTML('afterbegin', buildQuestionHtml(payload.question));
          }

          form.reset();
          setFeedback(feedbackNode, payload.message || 'Survey question added successfully.', false);
        })
        .catch(function(errorPayload) {
          let message = 'Failed to add survey question.';

          if (errorPayload) {
            if (errorPayload.message) {
              message = errorPayload.message;
            }

            if (errorPayload.errors) {
              const allErrors = Object.values(errorPayload.errors).flat();
              if (allErrors.length > 0) {
                message = allErrors.join(' ');
              }
            }
          }

          setFeedback(feedbackNode, message, true);
        })
        .finally(function() {
          if (submitButton) {
            submitButton.disabled = false;
          }
        });
    });
  })();
</script>

@include('includes.footer')
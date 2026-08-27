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



      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <form method="GET" action="{{ route('tpo.training-placement.index') }}" class="row g-2 align-items-end">
            <div class="col-md-9">
              <label class="form-label fw-semibold mb-1">Search Training</label>
              <input type="text" name="search" value="{{ $trainingSearch ?? '' }}" class="form-control" placeholder="Search by training title or description">
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Search</button>
              @if(!empty($trainingSearch))
              <a href="{{ route('tpo.training-placement.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center gap-2 flex-wrap">
              <h6 class="mb-0 fw-bold">All Trainings</h6>
              <button class="btn btn-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                <i class="fas fa-plus me-1"></i>Add Training
              </button>
            </div>
            <div class="card-body">
              @forelse($trainings as $training)
              @if($loop->first)
              <div class="accordion" id="allTrainingsAccordion">
                @endif
                <div class="accordion-item mb-3 border rounded">
                  @php
                  $resourceCount = $training->resources->count();
                  $questionCount = $training->surveyQuestions->count();
                  @endphp
                  <h2 class="accordion-header" id="trainingHeading{{ $training->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trainingCollapse{{ $training->id }}" aria-expanded="false" aria-controls="trainingCollapse{{ $training->id }}">
                      <div class="w-100 pe-3 d-flex flex-wrap align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">{{$loop->iteration}}. {{ $training->title }}</h6>
                        @forelse($training->targetRoles as $role)
                        <span class="badge badge-warning text-dark"> {{ ucfirst(str_replace('-', ' ', $role->role_name)) }}</span>
                        @empty
                        <span class="badge badge-warning text-muted border">No roles assigned</span>
                        @endforelse
                        <span class="badge {{ $resourceCount > 0 ? 'bg-success' : 'bg-danger' }}">{{ $resourceCount > 0 ? $resourceCount . ' Resource' . ($resourceCount > 1 ? 's' : '') : 'No Resources' }}</span>
                        <span class="badge {{ $questionCount > 0 ? 'bg-success' : 'bg-danger' }}">{{ $questionCount > 0 ? $questionCount . ' Q&A' : 'No Q&A' }}</span>
                      </div>
                    </button>
                  </h2>
                  <div id="trainingCollapse{{ $training->id }}" class="accordion-collapse collapse" aria-labelledby="trainingHeading{{ $training->id }}" data-bs-parent="#allTrainingsAccordion">
                    <div class="accordion-body">
                      <p class="mb-2 text-muted">{{ $training->description ?: 'No description added.' }}</p>

                      <div class="d-flex justify-content-end mb-3">
                        <form action="{{ route('tpo.training-placement.training.destroy', $training->id) }}" method="POST" onsubmit="return confirm('Delete this training?')">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete Training</button>
                        </form>
                      </div>

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
                                <textarea name="description" class="form-control" rows="5">{{ $training->description }}</textarea>
                              </div>
                              <div class="col-12">
                                <label class="form-label fw-semibold d-block mb-1">Training Status</label>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                  <span class="badge {{ $training->is_active ? 'bg-secondary' : 'bg-warning text-dark' }}">Draft</span>
                                  <input type="hidden" name="is_active" value="0">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeTraining{{ $training->id }}" {{ $training->is_active ? 'checked' : '' }}>
                                  </div>
                                  <span class="badge {{ $training->is_active ? 'bg-success' : 'bg-secondary' }}">Publish</span>
                                </div>
                                <small class="text-muted">Toggle right to publish, keep left to save as draft.</small>
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
                </div>
                @if($loop->last)
              </div>
              @endif
              @empty
              <div class="alert alert-info mb-0">No trainings found.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="addTrainingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Training</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tpo.training-placement.training.store') }}" method="POST">
              @csrf
              <div class="modal-body">
                @if($errors->has('title') || $errors->has('description') || $errors->has('applicable_roles') || $errors->has('applicable_roles.*'))
                <div class="alert alert-danger mb-3">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
                @endif

                <div class="mb-3">
                  <label class="form-label fw-semibold">Training Title</label>
                  <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Description</label>
                  <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="mb-0">
                  <label class="form-label fw-semibold">Applicable To Roles</label>
                  <select name="applicable_roles[]" class="select-multiple" multiple size="8" required>
                    @foreach($roleOptions as $role)
                    <option value="{{ $role['value'] }}" {{ collect(old('applicable_roles', []))->contains($role['value']) ? 'selected' : '' }}>{{ $role['label'] }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="mt-3">
                  <label class="form-label fw-semibold d-block mb-1">Training Status</label>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge {{ old('is_active', 0) ? 'bg-secondary' : 'bg-warning text-dark' }}">Draft</span>
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch m-0">
                      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createTrainingStatusToggle" {{ old('is_active', 0) ? 'checked' : '' }}>
                    </div>
                    <span class="badge {{ old('is_active', 0) ? 'bg-success' : 'bg-secondary' }}">Publish</span>
                  </div>
                  <small class="text-muted">Toggle right to publish, keep left to save as draft.</small>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="submit"><i class="fas fa-plus me-1"></i>Create Training</button>
              </div>
            </form>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const hasCreateTrainingErrors = JSON.parse('@json($errors->has("title") || $errors->has("description") || $errors->has("applicable_roles") || $errors->has("applicable_roles.*"))');

    if (!hasCreateTrainingErrors) {
      return;
    }

    const modalNode = document.getElementById('addTrainingModal');
    if (!modalNode || typeof bootstrap === 'undefined') {
      return;
    }

    const modal = new bootstrap.Modal(modalNode);
    modal.show();
  });
</script>

@include('includes.footer')
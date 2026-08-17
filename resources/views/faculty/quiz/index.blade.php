@include('includes.header')

<style>
  :root {
    --quiz-accent: #0f4c81;
    --quiz-accent-soft: #e8f0f8;
    --quiz-border: #d9e1ea;
    --quiz-text-muted: #5e6b78;
  }

  .quiz-page .card {
    border: 1px solid var(--quiz-border);
    border-radius: 10px;
  }

  .quiz-page .card-header {
    background: linear-gradient(90deg, #f7f9fc 0%, #eef3f8 100%);
    border-bottom: 1px solid var(--quiz-border);
    padding: 0.75rem 1rem;
  }

  .quiz-page .form-label {
    margin-bottom: 0.25rem;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--quiz-text-muted);
  }

  .quiz-page .form-control,
  .quiz-page .form-select {
    min-height: 38px;
  }

  .quiz-page .toolbar {
    background: var(--quiz-accent-soft);
    border: 1px solid var(--quiz-border);
    border-radius: 8px;
    padding: 0.6rem 0.8rem;
  }

  .quiz-page .table thead th {
    white-space: nowrap;
    font-size: 0.78rem;
    text-transform: uppercase;
    color: var(--quiz-text-muted);
  }

  .quiz-page .question-card {
    border-left: 4px solid var(--quiz-accent);
  }
</style>

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content quiz-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Quiz</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Moodle Style Quiz</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('faculty.fa1.my-quizzes') }}" class="btn btn-outline-primary btn-sm">Go To My Quizzes</a>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold text-uppercase" style="color: var(--quiz-accent); font-size: 0.95rem;">Create FA1 Quiz</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('faculty.fa1.store') }}" id="quizForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label fw-bold">CIA Component</label>
                <input type="hidden" name="sup_cia_component_id" value="{{ $fa1Component->id ?? '' }}">
                <input type="text" class="form-control" value="{{ $fa1Component->name ?? 'FA1 component not configured' }}" readonly>
                @if(!$fa1Component)
                <small class="text-danger">Please ask admin to configure CIA component as FA1.</small>
                @endif
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Total Marks</label>
                <input type="number" step="0.01" min="1" class="form-control" name="total_marks" value="{{ old('total_marks') }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Allotted Subject / Course</label>
                <select class="dselect-example" name="syllabus_assignment" required>
                  <option value="">Select</option>
                  @foreach(($assignmentOptions ?? collect()) as $option)
                  <option value="{{ $option['value'] }}" @selected(old('syllabus_assignment')==$option['value'])>
                    {{ $option['course_code'] ?? 'NA' }}
                    - {{ $option['course_title'] ?? 'N/A' }}
                    | {{ $option['semester_title'] ?? 'Semester' }}
                    | {{ $option['batch_name'] ?? 'Batch' }}
                    | {{ $option['delivery_type'] ?? 'N/A' }}
                    | Shift: {{ $option['shift'] ?? 'Common' }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Open Time</label>
                <input type="datetime-local" class="form-control" name="open_at" value="{{ old('open_at') }}" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Close Time (optional)</label>
                <input type="datetime-local" class="form-control" name="close_at" value="{{ old('close_at') }}">
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold">Time Limit (Minutes)</label>
                <input type="number" min="1" max="300" class="form-control" name="time_limit_minutes" value="{{ old('time_limit_minutes') }}" placeholder="e.g. 30">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Bulk Upload Questions (Excel/CSV)</label>
                <input type="file" class="form-control" name="bulk_questions_file" accept=".xlsx,.xls,.csv">
                <small class="text-muted">
                  Columns required: <strong>question_text, option_1, option_2, option_3, option_4, correct_option</strong>.
                  Correct option accepts 1-4 or A-D.
                </small>
                <div class="mt-2">
                  <a href="{{ route('faculty.fa1.bulk-template.download') }}" class="btn btn-sm btn-outline-primary">
                    Download Excel Template
                  </a>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <div class="toolbar d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Manual Questions (MCQ)</h6>
              <button type="button" class="btn btn-sm btn-primary" id="addQuestionBtn">Add Question</button>
            </div>

            <div id="questionsWrapper"></div>

            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-success" @disabled(!$fa1Component)>Create FA1 Quiz</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
  (function() {
    let questionIndex = 0;
    const questionsWrapper = document.getElementById('questionsWrapper');
    const addQuestionBtn = document.getElementById('addQuestionBtn');

    function addQuestionBlock() {
      const q = questionIndex++;
      const block = document.createElement('div');
      block.className = 'card question-card mb-2';
      block.innerHTML = `
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Question ${q + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remove</button>
          </div>
          <div class="mb-2">
            <label class="form-label">Question Text</label>
            <textarea class="form-control" name="questions[${q}][question_text]" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Question Image (optional)</label>
            <input type="file" class="form-control" name="questions[${q}][question_image]" accept="image/*">
          </div>

          <div class="row g-2">
            ${[0,1,2,3].map(i => `
              <div class="col-md-6">
                <label class="form-label">Option ${i + 1}</label>
                <input type="text" class="form-control" name="questions[${q}][options][${i}]" required>
                <label class="form-label mt-2">Option ${i + 1} Image (optional)</label>
                <input type="file" class="form-control" name="questions[${q}][option_images][${i}]" accept="image/*">
              </div>
            `).join('')}
          </div>

          <div class="mt-2">
            <label class="form-label">Correct Option</label>
            <select class="form-select" name="questions[${q}][correct_option]" required>
              <option value="0">Option 1</option>
              <option value="1">Option 2</option>
              <option value="2">Option 3</option>
              <option value="3">Option 4</option>
            </select>
          </div>
        </div>
      `;

      block.querySelector('.remove-question').addEventListener('click', function() {
        block.remove();
      });

      questionsWrapper.prepend(block);
    }

    addQuestionBtn.addEventListener('click', addQuestionBlock);
    addQuestionBlock();
  })();
</script>

@include('includes.footer')
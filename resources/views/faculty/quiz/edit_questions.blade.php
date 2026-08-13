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

  .quiz-page .toolbar {
    background: var(--quiz-accent-soft);
    border: 1px solid var(--quiz-border);
    border-radius: 8px;
    padding: 0.6rem 0.8rem;
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
            <li class="breadcrumb-item"><a href="{{ route('faculty.fa1.my-quizzes') }}">My Quizzes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Questions</li>
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

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <h5 class="mb-0 fw-bold" style="color: var(--quiz-accent);">Add / Re-Add Questions</h5>
          <small class="text-muted">
            {{ $quiz->title }} | Existing Questions: {{ $quiz->questions_count }}
          </small>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('faculty.fa1.results', $quiz->id) }}" class="btn btn-outline-primary btn-sm">View Results</a>
          <a href="{{ route('faculty.fa1.my-quizzes') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold text-uppercase" style="font-size:0.9rem; color: var(--quiz-accent);">Append Questions To Quiz</h6>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('faculty.fa1.questions.store', $quiz->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
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

            <hr class="my-4">

            <div class="toolbar d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Manual Questions (MCQ)</h6>
              <button type="button" class="btn btn-sm btn-primary" id="addQuestionBtn">Add Question</button>
            </div>

            <div id="questionsWrapper"></div>

            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-success">Save Questions</button>
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
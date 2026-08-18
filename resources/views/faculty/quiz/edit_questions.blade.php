@include('includes.header')

@php
use Illuminate\Support\Facades\Storage;

$cloudDisk = config('filesystems.cloud', 's3');
$resolveImageUrl = function ($path) use ($cloudDisk) {
if (!$path) {
return null;
}

if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
return $path;
}

try {
return Storage::disk($cloudDisk)->url($path);
} catch (\Throwable $e) {
return asset('storage/' . ltrim((string) $path, '/'));
}
};
@endphp

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
      <div id="ajaxDeleteMessage"></div>

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
          <h6 class="mb-0 fw-bold text-uppercase" style="font-size:0.9rem; color: var(--quiz-accent);">Edit Existing + Add New Questions</h6>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('faculty.fa1.questions.store', $quiz->id) }}" enctype="multipart/form-data">
            @csrf

            <div class="toolbar d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Quiz Behavior</h6>
              <small class="text-muted">Both are optional and can be changed anytime.</small>
            </div>

            <div class="row g-2 mb-4">
              <div class="col-md-6">
                <input type="hidden" name="shuffle_questions" value="0">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="shuffle_questions"
                    id="shuffle_questions"
                    value="1"
                    @checked(old('shuffle_questions', $quiz->shuffle_questions))>
                  <label class="form-check-label" for="shuffle_questions">Shuffle Questions (optional)</label>
                </div>
              </div>
              <div class="col-md-6">
                <input type="hidden" name="shuffle_options" value="0">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="shuffle_options"
                    id="shuffle_options"
                    value="1"
                    @checked(old('shuffle_options', $quiz->shuffle_options))>
                  <label class="form-check-label" for="shuffle_options">Shuffle Options (optional)</label>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <div class="toolbar d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Existing Questions (Editable)</h6>
              <small class="text-muted">Update text, replace images, or change correct answers.</small>
            </div>

            @forelse($quiz->questions as $qIndex => $question)
            @php
            $correctIndex = $question->options->search(function ($option) {
            return (bool) $option->is_correct;
            });
            $correctIndex = $correctIndex === false ? 0 : (int) $correctIndex;
            @endphp
            <div class="card question-card existing-question-card mb-3" data-existing-question="1">
              <div class="card-body">
                <input type="hidden" name="existing_questions[{{ $qIndex }}][id]" value="{{ $question->id }}">

                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0">Existing Question {{ $qIndex + 1 }}</h6>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border">Position: {{ $question->position ?? ($qIndex + 1) }}</span>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger js-question-delete-btn"
                      data-action="{{ route('faculty.fa1.questions.destroy', ['id' => $quiz->id, 'questionId' => $question->id]) }}">
                      Delete Question
                    </button>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label">Question Text</label>
                  <textarea class="form-control" name="existing_questions[{{ $qIndex }}][question_text]" required>{{ old("existing_questions.$qIndex.question_text", $question->question_text) }}</textarea>
                </div>

                <div class="mb-2">
                  <label class="form-label">Replace Question Image (optional)</label>
                  <input type="file" class="form-control" name="existing_questions[{{ $qIndex }}][question_image]" accept="image/*">
                </div>

                @if(!empty($question->question_image))
                <div class="mb-2">
                  <img src="{{ $resolveImageUrl($question->question_image) }}" alt="Question image" style="max-height:120px; max-width:100%; border-radius:6px; border:1px solid #dde6f1;">
                </div>
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="existing_questions[{{ $qIndex }}][remove_question_image]" id="remove_question_image_{{ $qIndex }}" value="1">
                  <label class="form-check-label" for="remove_question_image_{{ $qIndex }}">Remove current question image</label>
                </div>
                @endif

                <div class="row g-2">
                  @foreach($question->options as $oIndex => $option)
                  <div class="col-md-6">
                    <input type="hidden" name="existing_questions[{{ $qIndex }}][options][{{ $oIndex }}][id]" value="{{ $option->id }}">
                    <label class="form-label">Option {{ $oIndex + 1 }}</label>
                    <input type="text" class="form-control" name="existing_questions[{{ $qIndex }}][options][{{ $oIndex }}][option_text]" value="{{ old("existing_questions.$qIndex.options.$oIndex.option_text", $option->option_text) }}" required>

                    <label class="form-label mt-2">Replace Option {{ $oIndex + 1 }} Image (optional)</label>
                    <input type="file" class="form-control" name="existing_questions[{{ $qIndex }}][options][{{ $oIndex }}][option_image]" accept="image/*">

                    @if(!empty($option->option_image))
                    <div class="mt-2">
                      <img src="{{ $resolveImageUrl($option->option_image) }}" alt="Option image" style="max-height:90px; max-width:100%; border-radius:6px; border:1px solid #dde6f1;">
                    </div>
                    <div class="form-check mt-1">
                      <input class="form-check-input" type="checkbox" name="existing_questions[{{ $qIndex }}][options][{{ $oIndex }}][remove_option_image]" id="remove_option_image_{{ $qIndex }}_{{ $oIndex }}" value="1">
                      <label class="form-check-label" for="remove_option_image_{{ $qIndex }}_{{ $oIndex }}">Remove current option image</label>
                    </div>
                    @endif
                  </div>
                  @endforeach
                </div>

                <div class="mt-2">
                  <label class="form-label">Correct Option</label>
                  <select class="form-select" name="existing_questions[{{ $qIndex }}][correct_option]" required>
                    @foreach($question->options as $oIndex => $option)
                    <option value="{{ $oIndex }}" @selected((int) old("existing_questions.$qIndex.correct_option", $correctIndex)===(int) $oIndex)>
                      Option {{ $oIndex + 1 }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            @empty
            <div class="alert alert-info">No existing questions found in this quiz.</div>
            @endforelse

            <div class="mb-3">
              <label class="form-label fw-bold">Bulk Upload Questions (Excel/CSV) </label> <small class="text-danger">Do Not Include Uploaded Questions or it will be Double</small>
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
              <h6 class="fw-bold mb-0">Add New Questions (MCQ)</h6>
              <button type="button" class="btn btn-sm btn-primary" id="addQuestionBtn">Add Question</button>
            </div>

            <div id="questionsWrapper"></div>

            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-success">Save Quiz Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  (function() {
    const csrfToken = '{{ csrf_token() }}';
    const messageBox = document.getElementById('ajaxDeleteMessage');

    function showMessage(type, text) {
      if (!messageBox) {
        return;
      }
      messageBox.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          ${text}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
    }

    function renumberExistingQuestions() {
      const cards = document.querySelectorAll('.existing-question-card');
      cards.forEach(function(card, index) {
        const heading = card.querySelector('h6.mb-0');
        if (heading) {
          heading.textContent = 'Existing Question ' + (index + 1);
        }

        const positionBadge = card.querySelector('.badge.bg-light.text-dark.border');
        if (positionBadge) {
          positionBadge.textContent = 'Position: ' + (index + 1);
        }
      });
    }

    function bindQuestionDelete() {
      document.querySelectorAll('.js-question-delete-btn').forEach(function(button) {
        if (button.dataset.boundDeleteHandler === '1') {
          return;
        }

        button.dataset.boundDeleteHandler = '1';

        button.addEventListener('click', async function() {
          const confirmed = window.confirm('Delete this question? This will also remove related answers from attempts.');
          if (!confirmed) {
            return;
          }

          const formData = new FormData();
          formData.append('_token', csrfToken);
          formData.append('_method', 'DELETE');
          const actionUrl = button.dataset.action;

          button.disabled = true;

          try {
            const response = await fetch(actionUrl, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: formData
            });

            const payload = await response.json().catch(function() {
              return {};
            });

            if (!response.ok || payload.status !== true) {
              throw new Error(payload.message || 'Failed to delete question.');
            }

            const card = button.closest('.existing-question-card');
            if (card) {
              card.remove();
            }

            renumberExistingQuestions();
            showMessage('success', payload.message || 'Question deleted successfully.');
          } catch (error) {
            showMessage('danger', error.message || 'Unable to delete question.');
          } finally {
            button.disabled = false;
          }
        });
      });
    }

    bindQuestionDelete();

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
  })();
</script>

@include('includes.footer')
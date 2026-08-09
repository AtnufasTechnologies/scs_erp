@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
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

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">Create New Quiz</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('faculty.quiz.store') }}" id="quizForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Quiz Title</label>
                <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">CIA Component</label>
                <select class="form-select" name="sup_cia_component_id" required>
                  <option value="">Select</option>
                  @foreach($components as $component)
                  <option value="{{ $component->id }}" @selected(old('sup_cia_component_id')==$component->id)>
                    {{ $component->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Total Marks</label>
                <input type="number" step="0.01" min="1" class="form-control" name="total_marks" value="{{ old('total_marks') }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Allotted Subject / Course</label>
                <select class="form-select" name="syllabus_id" required>
                  <option value="">Select</option>
                  @foreach($syllabi as $syllabus)
                  <option value="{{ $syllabus->id }}" @selected(old('syllabus_id')==$syllabus->id)>
                    {{ $syllabus->subject->title ?? 'N/A' }}
                    - {{ $syllabus->coursemaster->course_title ?? 'N/A' }}
                    ({{ $syllabus->coursemaster->course_code ?? 'NA' }})
                    | {{ $syllabus->semestermaster->title ?? 'Semester' }}
                    | {{ $syllabus->batchmaster->batch_name ?? 'Batch' }}
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
              <div class="col-md-3 d-flex align-items-end">
                <div class="form-check me-3">
                  <input class="form-check-input" type="checkbox" name="shuffle_questions" id="shuffle_questions" value="1" @checked(old('shuffle_questions'))>
                  <label class="form-check-label" for="shuffle_questions">Shuffle Questions</label>
                </div>
              </div>
              <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="shuffle_options" id="shuffle_options" value="1" @checked(old('shuffle_options'))>
                  <label class="form-check-label" for="shuffle_options">Shuffle Options</label>
                </div>
              </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">Questions (MCQ)</h6>
              <button type="button" class="btn btn-sm btn-primary" id="addQuestionBtn">Add Question</button>
            </div>

            <div id="questionsWrapper"></div>

            <div class="mt-3">
              <button type="submit" class="btn btn-success">Create Quiz</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold">My Quizzes</h5>
        </div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Course</th>
                <th>Component</th>
                <th>Total Marks</th>
                <th>Open At</th>
                <th>Questions</th>
                <th>Time</th>
                <th>Shuffle</th>
                <th>Results</th>
                <th>CIA Group ID</th>
              </tr>
            </thead>
            <tbody>
              @forelse($quizzes as $quiz)
              <tr>
                <td>{{ $quiz->title }}</td>
                <td>{{ $quiz->subject->title ?? 'N/A' }}</td>
                <td>{{ $quiz->course->course_title ?? 'N/A' }}</td>
                <td>{{ $quiz->ciaComponent->name ?? 'N/A' }}</td>
                <td>{{ $quiz->total_marks }}</td>
                <td>{{ optional($quiz->open_at)->format('d M Y h:i A') }}</td>
                <td>{{ $quiz->questions_count }}</td>
                <td>{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' mins' : 'No limit' }}</td>
                <td>
                  Q: {{ $quiz->shuffle_questions ? 'Yes' : 'No' }}<br>
                  O: {{ $quiz->shuffle_options ? 'Yes' : 'No' }}
                </td>
                <td>
                  <a href="{{ route('faculty.quiz.results', $quiz->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                </td>
                <td>{{ $quiz->cia_group_id }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="11" class="text-center text-muted">No quizzes created yet.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
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
      block.className = 'card border mb-3';
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

      questionsWrapper.appendChild(block);
    }

    addQuestionBtn.addEventListener('click', addQuestionBlock);
    addQuestionBlock();
  })();
</script>

@include('includes.footer')
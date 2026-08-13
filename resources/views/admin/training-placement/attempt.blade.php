@include('includes.header')

<div class="wrapper">
  @include('admin.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Training Survey Attempt</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.index') }}">Training & Placement</a></li>
            <li class="breadcrumb-item active" aria-current="page">Attempt</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
          <h4 class="mb-2 fw-bold">{{ $training->title }}</h4>
          <p class="text-muted mb-0">{{ $training->description ?: 'No description available.' }}</p>
          @if($attempt && $attempt->completed_at)
          <span class="badge bg-success mt-2">Completed on {{ $attempt->completed_at->format('d M Y h:i A') }}</span>
          @endif
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent">
          <h6 class="mb-0 fw-bold">Training Resources</h6>
        </div>
        <div class="card-body">
          @forelse($training->resources as $resource)
          <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
            <div>{{ $resource->resource_title ?: $resource->file_name }}</div>
            <a href="{{ Storage::disk('s3')->url($resource->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
          </div>
          @empty
          <div class="alert alert-info mb-0">No resources uploaded for this training.</div>
          @endforelse
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent">
          <h6 class="mb-0 fw-bold">Survey</h6>
        </div>
        <div class="card-body">
          @if($training->surveyQuestions->isEmpty())
          <div class="alert alert-warning mb-0">No survey questions configured yet.</div>
          @else
          <form action="{{ route('tpo.training-placement.attempt.submit', $training->id) }}" method="POST">
            @csrf
            @foreach($training->surveyQuestions as $index => $question)
            <div class="border rounded p-3 mb-3">
              <h6 class="fw-semibold">Q{{ $index + 1 }}. {{ $question->question_text }}</h6>
              @foreach($question->options as $option)
              <div class="form-check mb-1">
                <input class="form-check-input" type="radio" name="responses[{{ $question->id }}]" value="{{ $option->id }}" id="question{{ $question->id }}option{{ $option->id }}" required>
                <label class="form-check-label" for="question{{ $question->id }}option{{ $option->id }}">{{ $option->option_text }}</label>
              </div>
              @endforeach
            </div>
            @endforeach

            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.index') }}" class="btn btn-secondary">Back</a>
              <button class="btn btn-primary" type="submit">Submit Survey</button>
            </div>
          </form>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
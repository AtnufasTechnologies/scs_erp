@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Internal Marks (FA II and III)</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Internal Marks</li>
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

      <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
          <h5 class="mb-0 fw-bold"><i class="fas fa-pen-alt text-primary me-2"></i>FA Marks Manual Entry</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('faculty.internal-marks.enter') }}" method="GET" id="selectForm">
            <input type="hidden" name="syllabus_id" id="syllabusIdInput">
            <div class="row g-3 align-items-end">
              <div class="col-md-6">
                <label class="form-label fw-bold">Faculty Assigned Course</label>
                <select name="rec_id" id="routineSelect" class="form-select" required>
                  <option value="">-- Select Assigned Course --</option>
                  @foreach(($syllabusAssignments ?? collect()) as $item)
                  <option value="{{ $item->id }}" data-syllabus-id="{{ $item->syllabus->id ?? '' }}">
                    {{ $item->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}
                    ({{ $item->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }})
                    - {{ $item->syllabus->semestermaster->title ?? 'N/A' }}
                    | Batch: {{ $item->syllabus->batchmaster->batch_name ?? 'N/A' }}
                    | Shift: {{ ucfirst($item->shift ?? 'common') }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">FA Component</label>
                <select name="component_id" class="form-select" required>
                  <option value="">-- Select Component --</option>
                  @foreach(($faComponents ?? collect()) as $component)
                  <option value="{{ $component->id }}">{{ $component->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">Academic Year</label>
                <input type="number" name="academic_year" class="form-control" placeholder="e.g. {{ date('Y') }}" value="{{ date('Y') }}">
              </div>
              <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-check-circle me-1"></i> Enter
                </button>
                <button type="submit" class="btn btn-outline-secondary" formaction="{{ route('faculty.internal-marks.view') }}">
                  <i class="fas fa-eye me-1"></i>View
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  (function() {
    const routineSelect = document.getElementById('routineSelect');
    const syllabusInput = document.getElementById('syllabusIdInput');
    const form = document.getElementById('selectForm');

    function syncSyllabusId() {
      const option = routineSelect?.options[routineSelect.selectedIndex];
      syllabusInput.value = option?.dataset?.syllabusId || '';
    }

    if (routineSelect) {
      routineSelect.addEventListener('change', syncSyllabusId);
      syncSyllabusId();
    }

    if (form) {
      form.addEventListener('submit', function(event) {
        syncSyllabusId();
        if (!syllabusInput.value) {
          event.preventDefault();
          alert('Please select a valid assigned course.');
        }
      });
    }
  })();
</script>

@include('includes.footer')
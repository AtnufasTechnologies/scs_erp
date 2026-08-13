@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Curriculam Defaulters</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.curriculam.index') }}">Curriculam</a></li>
            <li class="breadcrumb-item active" aria-current="page">Defaulters</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Combinations Without Curriculam</h5>
        <div class="d-flex gap-2">
          <span class="badge bg-danger">Missing Curriculum: {{ $totalDefaulters }}</span>
        </div>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('principal.curriculam.defaulters') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select" {{ $isVicePrincipal ? 'disabled' : '' }}>
              <option value="">All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ (int) $selectedCampusId === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
            @if($isVicePrincipal)
            <input type="hidden" name="campus_id" value="{{ (int) $selectedCampusId }}">
            <small class="text-muted">Campus is fixed for vice-principal.</small>
            @endif
          </div>

          <div class="col-md-3">
            <label class="form-label">Batch</label>
            <select name="batch_id" class="form-select">
              <option value="">All Batches</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ (int) $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Subject / Department</label>
            <select name="subject_id" class="form-select">
              <option value="">All Subjects</option>
              @foreach(($subjects ?? collect()) as $subject)
              <option value="{{ $subject->id }}" {{ (int) ($selectedSubjectId ?? 0) === (int) $subject->id ? 'selected' : '' }}>
                {{ $subject->code ? $subject->code . ' - ' : '' }}{{ $subject->title }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-success w-100"><i class="fa fa-search me-1"></i>Apply</button>
            <a href="{{ route('principal.curriculam.defaulters') }}" class="btn btn-outline-secondary w-100">Clear</a>
          </div>
        </form>
      </div>
    </div>

    @if(!empty($integratedProgramsExcluded))
    <div class="alert alert-info mt-3 mb-0">
      Integrated programs with configured sublayers are excluded from this defaulter list.
      <span class="badge bg-info text-dark ms-1">Excluded Program Types: {{ (int) ($integratedProgramCount ?? 0) }}</span>
    </div>
    @endif

    @if($programRows->isEmpty())
    <div class="alert alert-info mt-3 mb-0">No defaulters found for the selected filters.</div>
    @else
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-body">
        <div class="list-group list-group-flush">
          @foreach($programRows as $row)
          <div class="list-group-item px-0 py-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
              <div>
                <div class="fw-semibold text-dark">{{ $row->program_code }} - {{ $row->program_name }}</div>
                <div class="small text-muted">Subject: {{ $row->subject_code !== '' ? $row->subject_code . ' - ' : '' }}{{ $row->subject_name }}</div>
                <div class="small text-muted">Batch: {{ $row->batch_name }} | Campus: {{ $row->campus_name }} | Program Type: {{ $row->program_type_name ?: '-' }}</div>
              </div>
              <span class="badge bg-danger">Curriculum not done</span>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </main>
</div>

@include('includes.footer')
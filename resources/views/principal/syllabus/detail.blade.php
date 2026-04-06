@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">{{ $subject->title }} - Syllabus</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.syllabus.index') }}">Subject Syllabus</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $subject->title }}</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Subject Header --}}
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-body p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem;">
        <div class="row text-white">
          <div class="col-md-8">
            <h3 class="mb-1">{{ $subject->title }}</h3>
            <div class="d-flex flex-wrap gap-3 mt-2">
              <span><i class="fas fa-code me-1"></i> {{ $subject->code }}</span>
              <span><i class="fas fa-building me-1"></i> {{ $subject->campusmaster ? $subject->campusmaster->name : '-' }}</span>
            </div>
          </div>
          <div class="col-md-4 text-end">
            <form method="GET" action="{{ route('principal.syllabus.detail', $subject->id) }}" class="d-flex gap-2 justify-content-end flex-wrap">
              <select name="batch_id" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach($batches as $batch)
                <option value="{{ $batch->id }}" {{ (string)$selectedBatch === (string)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
                @endforeach
              </select>
              <select name="semester_id" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                <option value="">All Semesters</option>
                @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" {{ (string)$selectedSemester === (string)$sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
                @endforeach
              </select>
              @if($selectedBatch || $selectedSemester)
              <a href="{{ route('principal.syllabus.detail', $subject->id) }}" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
              @endif
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-primary">{{ $totalCourses }}</div>
            <div class="text-muted small">Total Courses</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-info">{{ $totalSubunits }}</div>
            <div class="text-muted small">Total CSO Subunits</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold text-success">{{ $completedSubunits }}</div>
            <div class="text-muted small">Completed</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <div class="fs-3 fw-bold {{ $avgCompletion >= 75 ? 'text-success' : ($avgCompletion >= 50 ? 'text-warning' : 'text-danger') }}">{{ $avgCompletion }}%</div>
            <div class="text-muted small">Avg Completion</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Syllabus Grouped by Year --}}
    @forelse($groupedByBatch as $batchName => $batchSyllabi)
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
          <i class="fas fa-calendar-alt me-2 text-primary"></i>Batch: <strong>{{ $batchName }}</strong>
          <span class="badge bg-primary ms-2">{{ $batchSyllabi->count() }} course(s)</span>
        </h6>
        @php $batchAvg = round($batchSyllabi->avg('completion_percent'), 1); @endphp
        <span class="badge {{ $batchAvg >= 75 ? 'bg-success' : ($batchAvg >= 50 ? 'bg-warning' : 'bg-danger') }} fs-6">{{ $batchAvg }}%</span>
      </div>
      <div class="card-body">
        {{-- Group by Semester within each batch --}}
        @php $semGrouped = $batchSyllabi->groupBy(fn($s) => $s->semestermaster ? $s->semestermaster->title : 'Unknown'); @endphp
        @foreach($semGrouped as $semTitle => $semSyllabi)
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-dark">{{ $semTitle }}</span>
            <div class="flex-grow-1" style="height: 1px; background: #dee2e6;"></div>
          </div>
          <div class="row g-3">
            @foreach($semSyllabi as $syl)
            <div class="col-12 col-lg-6">
              <div class="card border h-100">
                <div class="card-body p-3">
                  {{-- Course Header --}}
                  <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                      <span class="badge bg-dark me-1">{{ $syl->course_code }}</span>
                      <span class="badge bg-secondary">{{ $syl->course_type_name }}</span>
                    </div>
                    <span class="fw-bold {{ $syl->completion_percent >= 75 ? 'text-success' : ($syl->completion_percent >= 50 ? 'text-warning' : 'text-danger') }}">{{ $syl->completion_percent }}%</span>
                  </div>
                  <h6 class="fw-bold mb-1">{{ $syl->course_title_pcm }}</h6>
                  <div class="text-muted small mb-2">
                    <i class="fas fa-chalkboard-teacher me-1"></i>{{ $syl->faculty_name }}
                  </div>

                  {{-- Completion Progress --}}
                  <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                      <small class="text-muted">Subunit Progress</small>
                      <small class="fw-semibold">{{ $syl->completed_subunits }}/{{ $syl->total_subunits }}</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar {{ $syl->completion_percent >= 75 ? 'bg-success' : ($syl->completion_percent >= 50 ? 'bg-warning' : 'bg-danger') }}"
                        role="progressbar" style="width: {{ $syl->completion_percent }}%"></div>
                    </div>
                  </div>

                  {{-- Feedback --}}
                  <div class="d-flex align-items-center gap-2 mb-2">
                    @if($syl->avg_rating)
                    <span class="badge bg-primary">{{ number_format($syl->avg_rating, 1) }} / 5</span>
                    <small class="text-muted">({{ $syl->feedback_count }} feedback)</small>
                    @else
                    <small class="text-muted">No feedback yet</small>
                    @endif
                  </div>

                  {{-- CSO Subunits --}}
                  @if($syl->syllabusunits->count())
                  <div class="mt-2" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.78rem;">
                      <thead class="table-light sticky-top">
                        <tr>
                          <th>#</th>
                          <th>CSO Subunit</th>
                          <th>Taxonomy</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $suSl = 1; @endphp
                        @foreach($syl->syllabusunits as $su)
                        <tr>
                          <td>{{ $suSl++ }}</td>
                          <td>{{ $su->csoSubunit ? $su->csoSubunit->title : 'N/A' }}</td>
                          <td>
                            @if($su->csoSubunit && $su->csoSubunit->taxomonylevel)
                            <span class="badge bg-info-subtle text-info" style="font-size: 0.65rem;">{{ $su->csoSubunit->taxomonylevel->title ?? '-' }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                          </td>
                          <td>
                            @if($su->is_completed)
                            <span class="badge bg-success" style="font-size: 0.65rem;"><i class="fas fa-check"></i></span>
                            @else
                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;"><i class="fas fa-clock"></i></span>
                            @endif
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @else
                  <div class="text-center text-muted small py-2 mt-2">No CSO subunits mapped</div>
                  @endif
                </div>

                {{-- Link to course detail --}}
                <div class="card-footer bg-white p-2 text-end">
                  <a href="{{ route('principal.courses.detail', $syl->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chart-bar me-1"></i> Course Analytics
                  </a>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
    @empty
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-scroll fa-3x mb-3"></i>
        <h5>No syllabus records found</h5>
        <p>No syllabus entries exist for this subject with the current filters.</p>
      </div>
    </div>
    @endforelse

    {{-- Back Button --}}
    <div class="mt-3 mb-4">
      <a href="{{ route('principal.syllabus.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Subjects
      </a>
    </div>
  </main>
</div>

@include('includes.footer')
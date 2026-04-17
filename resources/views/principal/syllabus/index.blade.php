@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Subject Syllabus</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Subject Syllabus</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3 g-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <div class="fs-2 fw-bold text-primary">{{ $subjects->count() }}</div>
            <div class="text-muted small">Total Subjects</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php $withSyllabus = $subjects->filter(fn($s) => $s->all_syllabi->count() > 0)->count(); @endphp
            <div class="fs-2 fw-bold text-success">{{ $withSyllabus }}</div>
            <div class="text-muted small">With Syllabus</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php $totalSyll = $subjects->sum(fn($s) => $s->all_syllabi->count()); @endphp
            <div class="fs-2 fw-bold text-info">{{ $totalSyll }}</div>
            <div class="text-muted small">Total Syllabi</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            @php
            $allSyl = $subjects->flatMap(fn($s) => $s->all_syllabi);
            $avgComp = $allSyl->count() > 0 ? round($allSyl->avg('completion_percent'), 1) : 0;
            @endphp
            <div class="fs-2 fw-bold text-warning">{{ $avgComp }}%</div>
            <div class="text-muted small">Avg Completion</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Filters --}}
    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0"><i class="fas fa-scroll me-2"></i>Subject Syllabus Overview</h5>
        <form method="GET" action="{{ route('principal.syllabus.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
          <select name="campus_id" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          <select name="batch_id" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ (string)$selectedBatch === (string)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
            @endforeach
          </select>
          <select name="semester_id" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Semesters</option>
            @foreach($semesters as $sem)
            <option value="{{ $sem->id }}" {{ (string)$selectedSemester === (string)$sem->id ? 'selected' : '' }}>{{ $sem->title }}</option>
            @endforeach
          </select>
          @if($selectedCampus || $selectedBatch || $selectedSemester)
          <a href="{{ route('principal.syllabus.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
          @endif
        </form>
      </div>

      <div class="card-body">
        <div class="row g-4">
          @forelse($subjects as $subject)
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body p-0">
                {{-- Subject Header --}}
                <div class="p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem 0.5rem 0 0;">
                  <div class="d-flex align-items-center justify-content-between text-white">
                    <div>
                      <h6 class="mb-0 fw-bold">{{ $subject->title }}</h6>
                      <small class="opacity-75">{{ $subject->code }}</small>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-light text-dark">{{ $subject->campusmaster ? $subject->campusmaster->name : '-' }}</span>
                    </div>
                  </div>
                </div>

                <div class="p-3">
                  {{-- Stats Row --}}
                  <div class="d-flex gap-2 mb-3">
                    <div class="text-center flex-fill p-2 rounded" style="background: #f8f9fa;">
                      <div class="fw-bold text-primary">{{ $subject->all_syllabi->count() }}</div>
                      <div class="text-muted" style="font-size: 0.7rem;">Courses</div>
                    </div>
                    <div class="text-center flex-fill p-2 rounded" style="background: #f8f9fa;">
                      <div class="fw-bold text-success">{{ $subject->all_syllabi->sum('total_subunits') }}</div>
                      <div class="text-muted" style="font-size: 0.7rem;">Subunits</div>
                    </div>
                    <div class="text-center flex-fill p-2 rounded" style="background: #f8f9fa;">
                      <div class="fw-bold text-info">{{ $subject->all_syllabi->sum('completed_subunits') }}</div>
                      <div class="text-muted" style="font-size: 0.7rem;">Completed</div>
                    </div>
                    @php $avgSubjComp = $subject->all_syllabi->count() > 0 ? round($subject->all_syllabi->avg('completion_percent'), 1) : 0; @endphp
                    <div class="text-center flex-fill p-2 rounded" style="background: #f8f9fa;">
                      <div class="fw-bold {{ $avgSubjComp >= 75 ? 'text-success' : ($avgSubjComp >= 50 ? 'text-warning' : 'text-danger') }}">{{ $avgSubjComp }}%</div>
                      <div class="text-muted" style="font-size: 0.7rem;">Completion</div>
                    </div>
                  </div>

                  {{-- Syllabus by Year --}}
                  @if($subject->grouped_syllabi->count())
                  <div style="max-height: 180px; overflow-y: auto;">
                    @foreach($subject->grouped_syllabi as $year => $yearSyllabi)
                    <div class="mb-2">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-dark">{{ $year }}</span>
                        <small class="text-muted">{{ $yearSyllabi->count() }} course(s)</small>
                      </div>
                      @foreach($yearSyllabi as $syl)
                      <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 rounded" style="background: #f8f9fa; font-size: 0.78rem;">
                        <div>
                          <span class="fw-semibold">{{ $syl->course_code }}</span>
                          <span class="text-muted ms-1">{{ Str::limit($syl->course_title_pcm, 25) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                          <span class="badge {{ $syl->completion_percent >= 75 ? 'bg-success' : ($syl->completion_percent >= 50 ? 'bg-warning' : 'bg-danger') }}" style="font-size: 0.65rem;">{{ $syl->completion_percent }}%</span>
                          <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $syl->semestermaster ? $syl->semestermaster->title : '-' }}</span>
                        </div>
                      </div>
                      @endforeach
                    </div>
                    @endforeach
                  </div>
                  @else
                  <div class="text-center text-muted small py-2">No syllabus records</div>
                  @endif
                </div>
              </div>

              {{-- Card Footer --}}
              <div class="card-footer bg-white border-top-0 p-3 pt-0">
                <a href="{{ route('principal.syllabus.detail', $subject->id) }}{{ $selectedBatch ? '?batch_id='.$selectedBatch : '' }}{{ $selectedSemester ? ($selectedBatch ? '&' : '?').'semester_id='.$selectedSemester : '' }}" class="btn btn-sm btn-outline-primary w-100">
                  <i class="fas fa-eye me-1"></i> View Full Syllabus
                </a>
              </div>
            </div>
          </div>
          @empty
          <div class="col-12">
            <div class="alert alert-info text-center py-4 mb-0">No subjects found</div>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
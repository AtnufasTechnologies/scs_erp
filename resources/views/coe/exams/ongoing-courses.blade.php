@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA2 Examinations</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.exams.fa2') }}">FA2 Exams</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ongoing Courses</li>
          </ol>
        </nav>
      </div>
    </div>



    <div class="row g-3 mb-4">
      <div class="col-md-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm stat-card stat-courses h-100">
          <div class="card-body">
            <div class="text-uppercase text-muted fw-semibold small mb-2">Active Courses</div>
            <h3 class="mb-0 fw-bold">{{ (int) ($summary['course_count'] ?? 0) }}</h3>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="stat-card border-0 shadow-sm stat-card stat-students h-100">
          <div class="card-body">
            <div class="text-uppercase text-muted fw-semibold small mb-2">Distinct Students</div>
            <h3 class="mb-0 fw-bold">{{ (int) ($summary['student_count'] ?? 0) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-bottom py-3">
        <form method="GET" action="{{ route('coe.exams.ongoing-courses') }}" class="row g-2 align-items-center">
          <div class="col-md-4">
            <input
              type="text"
              name="search"
              value="{{ $search }}"
              class="form-control"
              placeholder="Search by course code or title">
          </div>
          <div class="col-md-2">
            <select name="program_type" class="form-select">
              <option value="">All Program Types</option>
              @foreach(($programTypes ?? collect()) as $type)
              <option value="{{ $type }}" {{ ($programType ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <select name="batch_id" class="form-select">
              <option value="">All Batches</option>
              @foreach(($batches ?? collect()) as $batch)
              <option value="{{ (int) ($batch->batch_id ?? 0) }}" {{ (int) ($batchId ?? 0) === (int) ($batch->batch_id ?? 0) ? 'selected' : '' }}>
                {{ $batch->batch_name ?: ('Batch #' . (int) ($batch->batch_id ?? 0)) }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="semester_id" class="form-select">
              <option value="">All Semesters</option>
              @foreach(($semesters ?? collect()) as $sem)
              <option value="{{ (int) ($sem->semester_id ?? 0) }}" {{ (int) ($semesterId ?? 0) === (int) ($sem->semester_id ?? 0) ? 'selected' : '' }}>
                {{ $sem->semester_title ?: ('Semester ' . (int) ($sem->semester_id ?? 0)) }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="submit">
              <i class="fas fa-search me-1"></i>Search
            </button>
          </div>
          @if($search !== '' || ($programType ?? '') !== '' || (int) ($batchId ?? 0) > 0 || (int) ($semesterId ?? 0) > 0)
          <div class="col-auto">
            <a href="{{ route('coe.exams.ongoing-courses') }}" class="btn btn-outline-secondary">
              Clear
            </a>
          </div>
          @endif
        </form>
      </div>

      <div class="card-body p-0">
        @if($ongoingCourses->isEmpty())
        <div class="p-5 text-center text-muted">
          <i class="fas fa-inbox fa-2x mb-3"></i>
          <p class="mb-0">No ongoing course roster data found for the selected filter.</p>
        </div>
        @else
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 ongoing-table">
            <thead>
              <tr>
                <th class="ps-4">Course</th>
                <th>Title</th>
                <th>Program Type</th>
                <th>Batch</th>
                <th>Semester</th>
                <th class="text-end pe-4">Enrolled Students</th>
              </tr>
            </thead>
            <tbody>
              @foreach($ongoingCourses as $course)
              <tr>
                <td class="ps-4 fw-semibold">{{ $course->course_code ?: '-' }}</td>
                <td>{{ $course->course_title ?: '-' }}</td>
                <td>{{ strtoupper((string) ($course->program_type ?? '-')) }}</td>
                <td>{{ $course->batch_name ?: ('Batch #' . (int) ($course->batch_id ?? 0)) }}</td>
                <td>{{ $course->semester_title ?: ('Semester ' . (int) ($course->semester_id ?? 0)) }}</td>
                <td class="text-end pe-4">
                  <span class="badge rounded-pill enrollment-badge">{{ (int) $course->enrolled_students }}</span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

    </div>
</div>
</main>
</div>

<style>
  :root {
    --coe-hero-a: #0f766e;
    --coe-hero-b: #0e7490;
    --coe-accent: #f59e0b;
    --coe-surface: #f8fafc;
    --coe-border: #e2e8f0;
  }

  .hero-card {
    background: linear-gradient(130deg, var(--coe-hero-a) 0%, var(--coe-hero-b) 58%, #0369a1 100%);
    overflow: hidden;
    position: relative;
  }

  .hero-card::after {
    content: '';
    position: absolute;
    width: 220px;
    height: 220px;
    right: -70px;
    top: -80px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.45), rgba(245, 158, 11, 0));
  }

  .text-white-75 {
    color: rgba(255, 255, 255, 0.85);
  }

  .stat-card {
    border: 1px solid var(--coe-border);
    background: var(--coe-surface);
  }

  .stat-courses {
    border-left: 4px solid #0ea5e9;
  }

  .stat-students {
    border-left: 4px solid var(--coe-accent);
  }

  .ongoing-table thead th {
    background: #f1f5f9;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
  }

  .enrollment-badge {
    background: #ecfeff;
    color: #0f766e;
    border: 1px solid #a5f3fc;
    font-size: 0.85rem;
    min-width: 74px;
    padding: 0.5rem 0.8rem;
  }

  @media (max-width: 767px) {
    .hero-card .btn {
      width: 100%;
    }
  }
</style>

@include('includes.footer')
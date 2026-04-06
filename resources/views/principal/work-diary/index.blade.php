@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Work Diary</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Monthly Work Diary</li>
          </ol>
        </nav>
      </div>
    </div>

    <!--filters-->
    <div class="card mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('principal.work-diary.overview') }}" class="row g-2 align-items-end">
          <div class="col-md-2">
            <label class="form-label">Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
          </div>
          <div class="col-md-2">
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">All Departments</option>
              @foreach($departments as $dept)
              <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2 d-flex gap-1 align-items-end">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            <a href="{{ route('principal.work-diary.overview') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
          </div>
        </form>
      </div>
    </div>



    <!--summary stats-->
    <div class="row mt-3">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h3 class="fw-bold text-primary">{{ count($submitted) + count($notSubmitted) }}</h3>
            <p class="text-muted mb-0">Total Faculty</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h3 class="fw-bold text-success">{{ count($submitted) }}</h3>
            <p class="text-muted mb-0">Submitted</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h3 class="fw-bold text-danger">{{ count($notSubmitted) }}</h3>
            <p class="text-muted mb-0">Not Submitted</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <div class="d-flex justify-content-around">
              <div>
                <h5 class="fw-bold text-info mb-0">{{ $totalTeaching }}</h5>
                <small class="text-muted">Teaching</small>
              </div>
              <div>
                <h5 class="fw-bold text-warning mb-0">{{ $totalExtra }}</h5>
                <small class="text-muted">Extra</small>
              </div>
              <div>
                <h5 class="fw-bold text-secondary mb-0">{{ $totalSubstitution }}</h5>
                <small class="text-muted">Substitution</small>
              </div>
            </div>
            <p class="text-muted mb-0 mt-1"><small>Total Classes</small></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Faculty Filter -->
    <div class="row mt-3">
      <div class="col-12 col-md-6 col-lg-4 mb-2">
        <label for="quickFacultyFilter" class="form-label">Quick Faculty Filter</label>
        <select id="quickFacultyFilter" class="form-select form-select-sm dselect-example">
          <option value="">All Faculty</option>
          @foreach(array_merge($submitted, $notSubmitted) as $row)
          <option value="faculty-{{ $row['faculty']->id }}">{{ $row['faculty']->FIRST_NAME }} {{ $row['faculty']->LAST_NAME }} ({{ $row['faculty']->USER_CODE }})</option>
          @endforeach
        </select>
      </div>
    </div>

    <!-- Modern Card Grid: Submitted Work Diaries -->
    <div class="card mt-3">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Submitted Work Diary <span class="badge bg-success ms-2">{{ count($submitted) }}</span></h5>
      </div>
      <div class="card-body">
        @if(count($submitted) > 0)
        <div class="row g-3">
          @foreach($submitted as $index => $row)
          <div class="col-12 col-md-6 col-lg-4 faculty-card faculty-{{ $row['faculty']->id }}">
            <div class="card h-100 border-0 shadow-sm submitted-faculty-card position-relative">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 text-capitalize fw-bold">{{ $row['faculty']->FIRST_NAME }} {{ $row['faculty']->LAST_NAME }}</h5>
                    <div class="text-muted small mb-1">{{ $row['department'] }}</div>
                    <span class="badge bg-primary text-uppercase">{{ $row['faculty']->USER_CODE }}</span>
                  </div>
                  <a href="{{ route('principal.faculty.work-diary', ['id' => $row['faculty']->id, 'month' => $selectedMonth]) }}" class="btn btn-sm btn-outline-primary ms-2" title="View Diary">
                    <i class="fas fa-eye"></i>
                  </a>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-info">{{ $row['teaching_classes'] }}</div>
                    <small class="text-muted">Teaching</small>
                  </div>
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-warning">{{ $row['extra_classes'] }}</div>
                    <small class="text-muted">Extra</small>
                  </div>
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-secondary">{{ $row['substitution_classes'] }}</div>
                    <small class="text-muted">Substitution</small>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <span class="badge bg-success">Approved: {{ $row['approved'] }}</span>
                  </div>
                  <div>
                    <span class="badge bg-danger">Pending: {{ $row['pending'] }}</span>
                  </div>
                  <div>
                    <span class="badge bg-dark">Entries: {{ $row['total_entries'] }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle me-2"></i>No faculty have submitted work diary entries for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}.
        </div>
        @endif
      </div>
    </div>

    <!-- Modern Card Grid: Not Submitted Work Diaries -->
    <div class=" mt-3 mb-4">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Not Submitted <span class="badge bg-danger ms-2">{{ count($notSubmitted) }}</span></h5>
      </div>
      <div class="card-body">
        @if(count($notSubmitted) > 0)
        <div class="row g-3">
          @foreach($notSubmitted as $index => $row)
          <div class="col-12 col-md-6 col-lg-4 faculty-card faculty-{{ $row['faculty']->id }}">
            <div class="card h-100 border-0 shadow-sm not-submitted-faculty-card position-relative">
              <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                  <div class="flex-grow-1">
                    <h5 class="mb-1 text-capitalize fw-bold">{{ $row['faculty']->FIRST_NAME }} {{ $row['faculty']->LAST_NAME }}</h5>
                    <div class="text-muted small mb-1">{{ $row['department'] }}</div>
                    <span class="badge bg-primary text-uppercase">{{ $row['faculty']->USER_CODE }}</span>
                  </div>
                  <span class="badge bg-danger ms-2">Not Submitted</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-info">{{ $row['teaching_classes'] }}</div>
                    <small class="text-muted">Teaching</small>
                  </div>
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-warning">{{ $row['extra_classes'] }}</div>
                    <small class="text-muted">Extra</small>
                  </div>
                  <div class="text-center flex-fill">
                    <div class="fw-bold text-secondary">{{ $row['substitution_classes'] }}</div>
                    <small class="text-muted">Substitution</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-success mb-0">
          <i class="fas fa-check-circle me-2"></i>All faculty have submitted their work diary for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}.
        </div>
        @endif
      </div>
    </div>

  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('quickFacultyFilter');
    filter.addEventListener('change', function() {
      const value = this.value;
      document.querySelectorAll('.faculty-card').forEach(card => {
        if (!value || card.classList.contains(value)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
</script>
@include('includes.footer')
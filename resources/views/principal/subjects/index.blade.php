@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Academic Departments</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Departments</li>
          </ol>
        </nav>
      </div>
    </div>

    @php
    $facultyCount = $departmentSummaries->sum(fn($department) => count($department->faculties ?? []));
    $inchargeCount = $departmentSummaries->filter(fn($department) => trim((string) ($department->incharge_name ?? '')) !== '' && trim((string) ($department->incharge_name ?? '')) !== 'Not assigned')->count();
    $shiftCount = $departmentSummaries->flatMap(fn($department) => $department->applicable_shifts ?? [])->unique()->count();
    @endphp

    <div class="card mt-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #5d0e99 0%, #0ea5e9 100%);">
      <div class="card-body p-4 p-lg-5 text-white">
        <div class="row align-items-center g-3">
          <div class="col-lg-8">
            <h4 class="fw-bold mb-2 text-white">Academic Departments</h4>
            <p class="mb-0 text-white-50">A clean overview of department-wise faculties, incharge, and applicable shifts for the principal office.</p>
          </div>
          <div class="col-lg-4 text-lg-end">
            <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-4 px-3 py-2">
              <i class="fas fa-layer-group fa-2x"></i>
              <div class="text-start">
                <div class="fw-semibold">{{ $departmentSummaries->count() }} Departments</div>
                <small class="text-white-50">Click a card to view faculties</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3 g-3">
      <div class="col-xl-4 col-md-4">
        <div class="stat-card">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted mb-1">Departments</p>

            </div>
            <div class="">
              <h3 class="mb-0 fw-bold">{{ $departmentSummaries->count() }}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-4">
        <div class="stat-card">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted mb-1">Assigned Faculties</p>

            </div>
            <div class="">
              <h3 class="mb-0 fw-bold">{{ $facultyCount }}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-4">
        <div class="stat-card">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted mb-1">Applicable Shifts</p>

            </div>
            <div class=" ">
              <h3 class="mb-0 fw-bold"> {{ $shiftCount }}</h3>

            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-3 border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-0">Academic Departments</h5>
          <small class="text-muted">Departments, applicable shifts, incharge, and faculties</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary">{{ $departmentSummaries->count() }} Departments</span>
          <span class="badge bg-success">{{ $facultyCount }} Faculties</span>
          <span class="badge bg-info text-dark">{{ $inchargeCount }} Incharges</span>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-md-5 col-lg-4">
            <input type="text" id="departmentSearch" class="form-control form-control-lg" placeholder="Search by department, faculty, or code...">
          </div>
        </div>

        @if($departmentSummaries->isEmpty())
        <div class="alert alert-info mb-0">No departments found.</div>
        @else
        <div class="row g-3" id="departmentList">
          @foreach($departmentSummaries as $department)
          <div class="col-xl-6 department-card">
            <details class="department-panel border-0 rounded-4 h-100 shadow-sm overflow-hidden bg-white">
              <summary class="department-summary d-flex align-items-start justify-content-between gap-3 p-3 p-lg-4">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="badge bg-light text-dark border">{{ $department->code !== '' ? $department->code : 'DEPT' }}</span>
                    <span class="badge bg-primary">{{ count($department->faculties) }} Faculties</span>
                  </div>
                  <div class="fw-semibold department-name fs-5">{{ $department->name }}</div>
                  <div class="small text-muted mt-1">Campus: {{ $department->campus_name }}</div>
                  <div class="small text-muted">Incharge: {{ $department->incharge_name }}{{ $department->incharge_designation !== '' ? ' - ' . $department->incharge_designation : '' }}</div>
                </div>
                <div class="text-end">
                  <div class="small text-muted mb-1">Applicable Shifts</div>
                  <div class="d-flex flex-wrap justify-content-end gap-1">
                    @forelse($department->applicable_shifts as $shift)
                    <span class="badge bg-success text-light shift-badge">{{ $shift }}</span>
                    @empty
                    <span class="text-muted small">Common</span>
                    @endforelse
                  </div>
                </div>
              </summary>

              <div class="px-3 px-lg-4 pb-4">
                <div class="rounded-3 bg-light p-3 border">
                  <div class="small text-muted mb-2">Faculties</div>
                  <div class="d-flex flex-wrap gap-2">
                    @forelse($department->faculties as $faculty)
                    <span class="badge bg-white text-dark border faculty-chip px-3 py-2">
                      {{ $faculty['name'] }}{{ $faculty['code'] !== '' ? ' (' . $faculty['code'] . ')' : '' }}
                    </span>
                    @empty
                    <span class="text-muted small">No faculties assigned</span>
                    @endforelse
                  </div>
                </div>
              </div>
            </details>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('departmentSearch');
    const cards = document.querySelectorAll('.department-card');

    if (!searchInput || !cards.length) {
      return;
    }

    searchInput.addEventListener('input', function() {
      const query = this.value.trim().toLowerCase();

      cards.forEach(function(card) {
        const departmentName = (card.querySelector('.department-name')?.textContent || '').toLowerCase();
        const facultyText = Array.from(card.querySelectorAll('.faculty-chip')).map(function(el) {
          return el.textContent.toLowerCase();
        }).join(' ');
        card.style.display = departmentName.includes(query) || facultyText.includes(query) ? '' : 'none';
      });
    });
  });
</script>

<style>
  .department-summary {
    cursor: pointer;
    list-style: none;
    background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
  }

  .department-summary::-webkit-details-marker {
    display: none;
  }

  .department-panel[open] .department-summary {
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  }
</style>
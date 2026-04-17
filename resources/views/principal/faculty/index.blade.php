@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Faculty Information</li>
          </ol>
        </nav>
      </div>
    </div>

    {{-- Filters --}}
    <div class="card mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('principal.faculty.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
          <select name="campus_id" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
            <option value="{{ $campus->id }}" {{ (string)$selectedCampus === (string)$campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
          </select>
          <select name="department_id" class="form-select form-select-sm" style="width: 200px;" onchange="this.form.submit()">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ (string)$selectedDepartment === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
          </select>
          <input type="text" id="facultySearch" class="form-control form-control-sm" style="width: 220px;" placeholder="Search by name or code...">
          <span class="badge bg-dark ms-auto">{{ count($facultyList) }} Faculty</span>
        </form>
      </div>
    </div>

    {{-- Faculty Cards --}}
    <div class="row mt-2" id="facultyCards">
      @if(count($facultyList))
      @foreach($facultyList as $fac)
      <div class="col-xl-4 col-lg-6 mb-4 faculty-card">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px; overflow: hidden;">
          {{-- Card Header --}}
          <div class="card-header border-0 pb-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="d-flex align-items-center gap-3 pb-3">
              @if($fac->photo)
              <img src="{{ $fac->photo }}" alt="Photo" class="rounded-circle border border-2 border-white" width="55" height="55" style="object-fit: cover;">
              @else
              <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; font-size: 20px; color: #fff; font-weight: 600;">
                {{ strtoupper(substr($fac->FIRST_NAME, 0, 1)) }}{{ strtoupper(substr($fac->LAST_NAME, 0, 1)) }}
              </div>
              @endif
              <div class="text-white">
                <h6 class="mb-0 text-capitalize faculty-name">{{ $fac->FIRST_NAME }} {{ $fac->MIDDLE_NAME }} {{ $fac->LAST_NAME }}</h6>
                <small class="opacity-75 faculty-code">{{ $fac->USER_CODE }}</small>
              </div>
            </div>
          </div>

          <div class="card-body pt-3">
            {{-- Department & Campus --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="badge bg-light text-dark"><i class="fas fa-building me-1"></i>{{ $fac->department_info ? $fac->department_info->name : '-' }}</span>
              <span class="badge bg-light text-dark"><i class="fas fa-map-marker-alt me-1"></i>{{ $fac->department_info && $fac->department_info->campusmaster ? $fac->department_info->campusmaster->name : '-' }}</span>
            </div>

            {{-- Stats Row --}}
            <div class="row text-center mb-3 g-2">
              <div class="col-3">
                <div class="border rounded p-2">
                  <div class="fw-bold text-info">{{ $fac->classes_count }}</div>
                  <small class="text-muted" style="font-size: 0.7rem;">Classes</small>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2">
                  <div class="fw-bold text-warning">{{ $fac->pending_leaves }}</div>
                  <small class="text-muted" style="font-size: 0.7rem;">Pend. Leaves</small>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2">
                  <div class="fw-bold text-success">{{ $fac->approved_leaves }}</div>
                  <small class="text-muted" style="font-size: 0.7rem;">Appr. Leaves</small>
                </div>
              </div>
              <div class="col-3">
                <div class="border rounded p-2">
                  <div class="fw-bold text-secondary">{{ $fac->pending_diary }}</div>
                  <small class="text-muted" style="font-size: 0.7rem;">Diary Pend.</small>
                </div>
              </div>
            </div>

            {{-- Course Completion --}}
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted fw-semibold">Course Completion</small>
                <small class="fw-bold {{ $fac->completion_percent >= 75 ? 'text-success' : ($fac->completion_percent >= 50 ? 'text-warning' : 'text-danger') }}">
                  {{ $fac->completion_percent }}%
                </small>
              </div>
              <div class="progress" style="height: 6px; border-radius: 3px;">
                <div class="progress-bar {{ $fac->completion_percent >= 75 ? 'bg-success' : ($fac->completion_percent >= 50 ? 'bg-warning' : 'bg-danger') }}"
                  role="progressbar" style="width: {{ $fac->completion_percent }}%; border-radius: 3px;"></div>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <small class="text-muted" style="font-size: 0.7rem;">{{ $fac->completed_subunits }}/{{ $fac->total_subunits }} subunits</small>
              </div>
            </div>

            {{-- Contact --}}
            <div class="mb-3" style="font-size: 0.85rem;">
              <div class="mb-1"><i class="fas fa-envelope text-muted me-2"></i><a href="mailto:{{ $fac->MAIL_ID }}" class="text-decoration-none">{{ $fac->MAIL_ID }}</a></div>
              <div><i class="fas fa-phone text-muted me-2"></i>{{ $fac->MOBILE_NO ?? '-' }}</div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('principal.faculty.detail', $fac->id) }}" class="btn btn-sm btn-primary flex-fill" title="View Detail">
                <i class="fas fa-eye me-1"></i>Detail
              </a>
              <a href="{{ route('principal.faculty.timetable', $fac->id) }}" class="btn btn-sm btn-outline-info" title="Timetable">
                <i class="fas fa-calendar-alt"></i>
              </a>
              <a href="{{ route('principal.faculty.work-diary', $fac->id) }}" class="btn btn-sm btn-outline-success" title="Work Diary">
                <i class="fas fa-book"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
      @else
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5">
            <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
            <p class="text-muted">No Faculty Records Found</p>
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.getElementById('facultySearch').addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.faculty-card').forEach(card => {
      const name = card.querySelector('.faculty-name').textContent.toLowerCase();
      const code = card.querySelector('.faculty-code').textContent.toLowerCase();
      card.style.display = (name.includes(term) || code.includes(term)) ? '' : 'none';
    });
  });
</script>
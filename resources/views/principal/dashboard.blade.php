@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Principal Home</li>
          </ol>
        </nav>
      </div>
    </div>

    <!--welcome-->
    <div class="row mt-3">
      <div class="col-12">
        <div class="card shadow-lg border-0 mb-4" style="background: linear-gradient(135deg, #4d2d9d 0%, #0b9da2 100%);">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h4 class="text-white fw-bold mb-2">Welcome to Principal Dashboard, {{ Auth::user()->name }}!</h4>
                <p class="text-white-50 mb-0">Bird's eye view of the entire system across both campuses.</p>
              </div>
              <div class="col-md-4 text-end">
                <i class="fas fa-university fa-4x text-white"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--overview stats-->
    <div class="row">
      <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-1 text-muted">Total Students</p>
                <h3 class="mb-0 fw-bold">{{ $totalStudents }}</h3>
              </div>
              <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                <i class="fas fa-user-graduate fa-2x "></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-1 text-muted">Total Faculty</p>
                <h3 class="mb-0 fw-bold">{{ $totalFaculty }}</h3>
                <small class="text-danger"><i class="fas fa-user-clock"></i> {{ $facultyOnLeaveToday }} on leave today</small>
              </div>
              <div class="rounded-circle  bg-secondary bg-opacity-10 p-3">
                <i class="fas fa-chalkboard-teacher fa-2x "></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-1 text-muted">Today's Classes</p>
                <h3 class="mb-0 fw-bold">{{ $todayClassesCount }}</h3>
              </div>
              <div class="rounded-circle  bg-secondary bg-opacity-10 p-3">
                <i class="fas fa-clock fa-2x "></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-1 text-muted">Pending Leaves</p>
                <h3 class="mb-0 fw-bold">{{ $pendingLeaves }}</h3>
              </div>
              <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                <i class="fas fa-calendar-minus fa-2x "></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--quick links-->
    <div class="row mt-3 mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h6>
          </div>
          <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('principal.students.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-user-graduate me-1"></i> View All Students
              </a>
              <a href="{{ route('principal.faculty.index') }}" class="btn btn-outline-success">
                <i class="fas fa-chalkboard-teacher me-1"></i> View Faculty
              </a>
              <a href="{{ route('principal.classes.index') }}" class="btn btn-outline-info">
                <i class="fas fa-clock me-1"></i> Today's Classes
              </a>
              <a href="{{ route('principal.admissions.index') }}" class="btn btn-outline-warning">
                <i class="fas fa-file-alt me-1"></i> Admissions
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--campus-wise student breakdown-->
    <div class="row mt-3">
      @foreach($studentStats as $campusId => $stat)
      <div class="col-xl-6 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-building me-2"></i>{{ $stat['name'] }}</h6>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6">
                <h4 class="fw-bold text-primary">{{ $stat['total'] }}</h4>
                <p class="text-muted mb-0">Total Students</p>
              </div>
              <div class="col-6">
                <h4 class="fw-bold text-success">{{ $stat['active'] }}</h4>
                <p class="text-muted mb-0">Active Students</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!--more stats-->
    <div class="row mt-3">
      <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-building fa-2x text-secondary mb-2"></i>
            <h4 class="fw-bold">{{ $totalDepartments }}</h4>
            <p class="text-muted mb-0">Departments</p>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-graduation-cap fa-2x text-secondary mb-2"></i>
            <h4 class="fw-bold">{{ $totalPrograms }}</h4>
            <p class="text-muted mb-0">Programs</p>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <i class="fas fa-file-signature fa-2x text-secondary mb-2"></i>
            <h4 class="fw-bold">{{ $totalRegistrations }}</h4>
            <p class="text-muted mb-0">Admission Registrations</p>
          </div>
        </div>
      </div>
    </div>

    <!--admission campus breakdown-->
    <div class="row mt-3">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Admissions by Campus</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="bg-dark">
                  <tr>
                    <th>Campus</th>
                    <th>Registrations</th>
                    <th>Applications</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($campuses as $campus)
                  <tr>
                    <td>{{ $campus->name }}</td>
                    <td><span class="badge bg-primary">{{ $registrationsByCampus[$campus->id] ?? 0 }}</span></td>
                    <td><span class="badge bg-success">{{ $totalApplications }}</span></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Programs, Combo & Curriculum Overview ─────────────────────────── -->
    <div class="row mt-4" id="programs-curriculum">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2 text-primary"></i>Programs, Combos &amp; Curriculum Status</h6>
            <small class="text-muted">Curriculum coverage: semesters with at least one course mapped</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle" style="font-size:0.85rem;">
                <thead class="table-dark">
                  <tr>
                    <th>Campus</th>
                    <th>Program Code</th>
                    <th>Program Name</th>
                    <th>Shift</th>
                    <th>Type</th>
                    <th>Combo 1 (Major A)</th>
                    <th>Combo 2 (Major B)</th>
                    <th class="text-center">Curriculum</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($programsOverview as $prog)
                  @php
                  $covered = $prog->curriculum_covered;
                  $total = $prog->curriculum_total;
                  $pct = $total > 0 ? round(($covered / $total) * 100) : 0;
                  $badgeClass = $covered >= $total && $total > 0 ? 'success' : ($covered > 0 ? 'warning' : 'danger');
                  $badgeText = $covered >= $total && $total > 0 ? 'Complete' : ($covered > 0 ? 'Partial' : 'Not Started');
                  @endphp
                  <tr>
                    <td>{{ $prog->campusmaster->name ?? '—' }}</td>
                    <td><code>{{ $prog->code }}</code></td>
                    <td>{{ Str::title($prog->name) }}</td>
                    <td>
                      @if($prog->shiftmaster)
                      <span class="badge bg-secondary">{{ $prog->shiftmaster->title }}</span>
                      @else
                      <span class="text-muted">Common</span>
                      @endif
                    </td>
                    <td>
                      @if($prog->programtypemaster)
                      <span class="badge bg-info text-dark">{{ $prog->programtypemaster->name }}</span>
                      @else
                      <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>
                      @if($prog->combomap && $prog->combomap->combo1)
                      <span class="badge bg-success">{{ $prog->combomap->combo1->title }}</span>
                      @else
                      <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>
                      @if($prog->combomap && $prog->combomap->combo2)
                      <span class="badge bg-primary">{{ $prog->combomap->combo2->title }}</span>
                      @else
                      <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if(!$prog->has_combos)
                      <span class="badge bg-secondary">No Combinations</span>
                      @else
                      <span class="badge bg-{{ $badgeClass }} me-1">{{ $badgeText }}</span>
                      <small class="text-muted">{{ $covered }}/{{ $total }} sem</small>
                      @if($total > 0)
                      <div class="progress mt-1" style="height:5px;">
                        <div class="progress-bar bg-{{ $badgeClass }}" style="width:{{ $pct }}%"></div>
                      </div>
                      @endif
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-3">No programs found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Subjects: Shifts & Specializations ───────────────────────────── -->
    <div class="row mt-4 mb-4" id="subjects-overview">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="fas fa-book-open me-2 text-success"></i>Subjects — Shifts &amp; Specializations</h6>
            <small class="text-muted">Departments configured in the system</small>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle" style="font-size:0.85rem;">
                <thead class="table-dark">
                  <tr>
                    <th>Campus</th>
                    <th>Subject / Department</th>
                    <th>Shift Delivery</th>
                    <th>Applicable Shifts</th>
                    <th>Specializations</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($subjectsOverview as $sub)
                  <tr>
                    <td>{{ $sub->campusmaster->name ?? '—' }}</td>
                    <td><strong>{{ $sub->title }}</strong> <code class="ms-1 text-muted">{{ $sub->code }}</code></td>
                    <td class="text-center">
                      @if($sub->uses_shifts)
                      <span class="badge bg-success"><i class="fas fa-check me-1"></i>Yes</span>
                      @else
                      <span class="badge bg-secondary">No</span>
                      @endif
                    </td>
                    <td>
                      @if($sub->uses_shifts && count($sub->applicable_shifts) > 0)
                      @foreach($sub->applicable_shifts as $shift)
                      <span class="badge bg-info text-dark me-1">{{ $shift }}</span>
                      @endforeach
                      @elseif($sub->uses_shifts)
                      <span class="text-warning small">Shift mode on, none assigned</span>
                      @else
                      <span class="text-muted small">—</span>
                      @endif
                    </td>
                    <td>
                      @if($sub->specializations_list->isNotEmpty())
                      @foreach($sub->specializations_list as $spec)
                      <span class="badge bg-light text-dark border me-1 mb-1">{{ $spec->name }}</span>
                      @endforeach
                      @else
                      <span class="text-muted small">None</span>
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">No subjects found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

@include('includes.footer')
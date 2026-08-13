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



    <!--overview stats-->
    <div class="row">
      <div class="col-xl-3 col-md-6">
        <div class="stat-card ">
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
        <div class="stat-card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-1 text-muted">Total Faculty</p>

                <small class="text-danger"><i class="fas fa-user-clock"></i> {{ $facultyOnLeaveToday }} on leave today</small>
              </div>
              <div class="rounded-circle  bg-secondary bg-opacity-10 p-3">
                <h3 class="mb-0 fw-bold">{{ $totalFaculty }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
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
        <div class="stat-card">
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
              <a href="{{ route('principal.monitoring.mentoring') }}" class="btn btn-outline-dark">
                <i class="fas fa-hand-holding-heart me-1"></i> Mentoring Monitor
              </a>
              <a href="{{ route('principal.monitoring.student360') }}" class="btn btn-outline-secondary">
                <i class="fas fa-user-shield me-1"></i> Student 360 Monitor
              </a>
              <a href="{{ route('principal.monitoring.clubs') }}" class="btn btn-outline-primary">
                <i class="fas fa-layer-group me-1"></i> Clubs and Cells Monitor
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

    <!--hours by shift-->
    <div class="row mt-4">
      <div class="col-12">
        <div class=" border-0 ">
          <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="mb-0"><i class="fas fa-clock me-2 text-success"></i>Timing by Shift</h6>
            <small class="text-muted">Grouped from hour masters</small>
          </div>
          <div class="card-body">
            @if($hoursByShift->isEmpty())
            <div class="text-muted">No hour setup found.</div>
            @else
            <div class="row g-3">
              @foreach($hoursByShift as $shiftId => $hours)
              <div class="col-xl-4 col-md-6">
                <div class="card border h-100">
                  <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ optional($allShifts->get((int) $shiftId))->title ?? 'Common' }}</span>
                    <span class="badge bg-secondary">{{ $hours->count() }}</span>
                  </div>
                  <div class="card-body">
                    <div class="list-group list-group-flush">
                      @foreach($hours as $hour)
                      <div class="list-group-item px-0 py-2">
                        <div class="fw-semibold">{{ $hour->name }}</div>
                        <div class="small text-muted">{{ \Carbon\Carbon::parse($hour->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($hour->end_time)->format('h:i A') }}</div>
                      </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>



  </main>
</div>

@include('includes.footer')
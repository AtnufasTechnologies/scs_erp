@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <h3 class="text-white fw-bold mb-2"><i class="fas fa-chart-bar me-2"></i>Exam Reports</h3>
              <p class="text-white-50 mb-0">Generate and export reports for attendance, results, and faculty payments</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-book text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Exams</p>
                  <h4 class="mb-0 fw-bold">{{ $stats['total_exams'] }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-clipboard-list text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Registrations</p>
                  <h4 class="mb-0 fw-bold">{{ $stats['total_registrations'] }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-users text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Active Students</p>
                  <h4 class="mb-0 fw-bold">{{ $stats['total_students'] }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-exclamation-triangle text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending Backlogs</p>
                  <h4 class="mb-0 fw-bold">{{ $stats['pending_backlogs'] }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Cards -->
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
              <div class="mb-3">
                <i class="fas fa-user-check text-primary" style="font-size: 3rem;"></i>
              </div>
              <h5 class="fw-bold">Attendance Report</h5>
              <p class="text-muted">View student attendance records by exam, department, and date</p>
              <a href="{{ route('admin.exam-reports.attendance') }}" class="btn btn-primary">
                <i class="fas fa-arrow-right me-1"></i>View Report
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
              <div class="mb-3">
                <i class="fas fa-poll text-success" style="font-size: 3rem;"></i>
              </div>
              <h5 class="fw-bold">Result Report</h5>
              <p class="text-muted">View exam results with SGPA, CGPA & pass/fail statistics</p>
              <a href="{{ route('admin.exam-reports.results') }}" class="btn btn-success">
                <i class="fas fa-arrow-right me-1"></i>View Report
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center p-4">
              <div class="mb-3">
                <i class="fas fa-money-bill-wave text-info" style="font-size: 3rem;"></i>
              </div>
              <h5 class="fw-bold">Faculty Payment Report</h5>
              <p class="text-muted">Track faculty remuneration by duty type, department & status</p>
              <a href="{{ route('admin.exam-reports.remuneration') }}" class="btn btn-info text-white">
                <i class="fas fa-arrow-right me-1"></i>View Report
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
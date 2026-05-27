@include('includes.header')


@include('hr.sidebar')
<div class="container-fluid">
  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">HR Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">HR Home</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!--start welcome section-->
    <div class="row">
      <div class="col-12">
        <div class="card gradient-purple shadow-lg border-0 mb-4">
          <div class="card-body p-5">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h4 class="text-white fw-bold mb-2">Welcome to HR Management Portal</h4>
                <p class="text-white-50 mb-0">Manage faculty, leave applications, FDP programs, and recruitment efficiently.</p>
              </div>
              <div class="col-md-4 text-md-end">
                <div class="display-5 text-white fw-bold">{{ date('d M Y') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end welcome section-->

    <!--start stats cards-->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-users text-primary" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Total Faculty</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\Faculty::where('IS_LEFT', 0)->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-calendar-check text-success" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Pending Leaves</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\FacultyLeaveApplication::where('status', 'pending')->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-graduation-cap text-info" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Active FDP Programs</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\HrFdpProgram::whereIn('status', ['open', 'ongoing'])->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-briefcase text-warning" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Open Vacancies</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\HrVacancy::where('status', 'published')->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--start payroll stats-->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-table text-purple" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Active Pay Matrix</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\HrPayMatrix::where('status', 'active')->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-money-check-alt text-success" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">This Month Slips</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\FacultySalarySlip::where('month', date('m'))->where('year', date('Y'))->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-check-circle text-info" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Paid This Month</h6>
                <h4 class="mb-0 mt-1">{{ \App\Models\FacultySalarySlip::where('month', date('m'))->where('year', date('Y'))->where('status', 'paid')->count() }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-wrapper me-3">
                <i class="fas fa-rupee-sign text-danger" style="font-size: 1.8rem;"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">Monthly Payout</h6>
                <h4 class="mb-0 mt-1">₹{{ number_format(\App\Models\FacultySalarySlip::where('month', date('m'))->where('year', date('Y'))->sum('net_salary') / 100000, 2) }}L</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end payroll stats-->
    <!--end stats cards-->

    <!--start quick actions-->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="row">
              <div class="col-md-3 mb-3">
                <a href="{{ route('hr.faculty.create') }}" class="btn btn-primary w-100">
                  <i class="fas fa-user-plus me-2"></i>Add Faculty
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="{{ route('hr.leave.index') }}" class="btn btn-success w-100">
                  <i class="fas fa-clipboard-list me-2"></i>Manage Leaves
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="{{ route('hr.fdp.create') }}" class="btn btn-info w-100">
                  <i class="fas fa-chalkboard-teacher me-2"></i>Create FDP
                </a>
              </div>
              <div class="col-md-3 mb-3">
                <a href="{{ route('hr.vacancy.create') }}" class="btn btn-warning w-100">
                  <i class="fas fa-plus-circle me-2"></i>Post Vacancy
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--end quick actions-->

    <!--start recent activity-->
    <div class="row">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-header bg-transparent">
            <h5 class="mb-0">Recent Leave Applications</h5>
          </div>
          <div class="card-body">
            @php
            $recentLeaves = \App\Models\FacultyLeaveApplication::with('faculty')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            @endphp

            @if($recentLeaves->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <tbody>
                  @foreach($recentLeaves as $leave)
                  <tr>
                    <td>
                      <strong>{{ $leave->faculty->FIRST_NAME ?? 'N/A' }}</strong><br>
                      <small class="text-muted">{{ $leave->leave_type }}</small>
                    </td>
                    <td>
                      <span class="badge bg-{{ $leave->status == 'pending' ? 'warning' : ($leave->status == 'approved' ? 'success' : 'danger') }}">
                        {{ ucfirst($leave->status) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('hr.leave.show', $leave->id) }}" class="btn btn-sm btn-outline-primary">
                        View
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="text-center mt-3">
              <a href="{{ route('hr.leave.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            @else
            <p class="text-muted text-center">No recent leave applications</p>
            @endif
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-header bg-transparent">
            <h5 class="mb-0">Upcoming FDP Programs</h5>
          </div>
          <div class="card-body">
            @php
            $upcomingFdps = \App\Models\HrFdpProgram::where('start_date', '>', now())
            ->where('status', 'open')
            ->orderBy('start_date', 'asc')
            ->limit(5)
            ->get();
            @endphp

            @if($upcomingFdps->count() > 0)
            <div class="table-responsive">
              <table class="table table-hover">
                <tbody>
                  @foreach($upcomingFdps as $fdp)
                  <tr>
                    <td>
                      <strong>{{ $fdp->program_title }}</strong><br>
                      <small class="text-muted">{{ $fdp->start_date->format('d M Y') }}</small>
                    </td>
                    <td>
                      <span class="badge bg-info">{{ ucfirst($fdp->program_type) }}</span>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('hr.fdp.show', $fdp->id) }}" class="btn btn-sm btn-outline-primary">
                        View
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="text-center mt-3">
              <a href="{{ route('hr.fdp.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            @else
            <p class="text-muted text-center">No upcoming FDP programs</p>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!--end recent activity-->

  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
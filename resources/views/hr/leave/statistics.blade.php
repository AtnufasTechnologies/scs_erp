@include('includes.header')

@include('hr.sidebar')

<!--start main wrapper-->
<main class="page-content">
  <!--start breadcrumb-->
  <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
    <div class="breadcrumb-title pe-3">Leave Management</div>
    <div class="ps-2">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
          <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Leave Applications</a></li>
          <li class="breadcrumb-item active" aria-current="page">Statistics</li>
        </ol>
      </nav>
    </div>
  </div>
  <!--end breadcrumb-->

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <!-- Session Filter -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-6">
              <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Leave Statistics</h5>
            </div>
            <div class="col-md-6 text-end">
              <a href="{{ route('hr.leave.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
            </div>
          </div>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('hr.leave.statistics') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Academic Session</label>
              <select name="session_id" class="form-select" onchange="this.form.submit()">
                @foreach($sessions as $s)
                <option value="{{ $s->id }}" {{ $session && $session->id == $s->id ? 'selected' : '' }}>
                  {{ $s->title ?? $s->session_name ?? $s->id }}
                </option>
                @endforeach
              </select>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Leave Days Banner -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="alert alert-info d-flex align-items-center gap-3 mb-0">
        <i class="fas fa-calendar-check fa-2x"></i>
        <div>
          <h5 class="mb-0">Total Approved Leave Days: <strong>{{ $stats['total_leave_days'] }}</strong></h5>
          <small>Across all approved applications for {{ $session->title ?? $session->session_name ?? 'selected session' }}</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <!-- Leave Breakdown by Type -->
    <div class="col-md-7 mb-4">
      <div class="card h-100">
        <div class="card-header">
          <h6 class="mb-0"><i class="fas fa-list-alt me-2"></i>Leave Breakdown by Type</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Leave Type</th>
                  <th>Code</th>
                  <th class="text-center">Applications</th>
                  <th class="text-center">Approved Days</th>
                  <th style="width:30%;">Progress</th>
                </tr>
              </thead>
              <tbody>
                @forelse($leaveBreakdown as $item)
                @php
                $pct = $stats['total_applications'] > 0 ? round(($item['count'] / $stats['total_applications']) * 100) : 0;
                @endphp
                <tr>
                  <td><strong>{{ $item['name'] }}</strong></td>
                  <td><span class="badge bg-secondary">{{ $item['code'] }}</span></td>
                  <td class="text-center">{{ $item['count'] }}</td>
                  <td class="text-center">{{ $item['days'] }}</td>
                  <td>
                    <div class="progress" style="height:8px;">
                      <div class="progress-bar bg-primary" style="width:{{ $pct }}%;" title="{{ $pct }}%"></div>
                    </div>
                    <small class="text-muted">{{ $pct }}%</small>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-3">No leave types found.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Faculty by Leave Days -->
    <div class="col-md-5 mb-4">
      <div class="card h-100">
        <div class="card-header">
          <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 10 Faculty by Leave Days</h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Faculty</th>
                  <th class="text-center">Days</th>
                </tr>
              </thead>
              <tbody>
                @forelse($topFacultyByLeave as $index => $item)
                <tr>
                  <td>
                    @if($index < 3)
                      <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }}">{{ $index + 1 }}</span>
                      @else
                      {{ $index + 1 }}
                      @endif
                  </td>
                  <td>
                    <div>
                      <strong>{{ $item['faculty']->FIRST_NAME }} {{ $item['faculty']->LAST_NAME }}</strong>
                    </div>
                    <small class="text-muted">{{ $item['faculty']->USER_CODE }}</small>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-info">{{ $item['leave_days'] }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">No data available.</td>
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
<!--end main wrapper-->

@include('includes.footer')
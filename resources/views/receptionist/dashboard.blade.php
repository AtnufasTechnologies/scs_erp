@include('includes.header')

<div class="wrapper">
  @include('receptionist.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
      <div class="breadcrumb-title pe-3">Receptionist Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Overview</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Total Active Faculty</div>
            <div class="display-6 fw-bold text-primary">{{ $totalFaculty }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Appointments Today</div>
            <div class="display-6 fw-bold text-success">{{ $todayAppointments }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">Pending Appointments</div>
            <div class="display-6 fw-bold text-warning">{{ $pendingAppointments }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small">My Diary Entries Today</div>
            <div class="display-6 fw-bold text-dark">{{ $todayDiaryEntries }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="{{ route('receptionist.faculty.index') }}">
          <i class="fas fa-users me-1"></i> View Faculty by Campus
        </a>
        <a class="btn btn-outline-success" href="{{ route('receptionist.appointments.index') }}">
          <i class="fas fa-calendar-plus me-1"></i> Schedule Appointment
        </a>
        <a class="btn btn-outline-dark" href="{{ route('receptionist.work-diary.index') }}">
          <i class="fas fa-book-medical me-1"></i> Add Work Diary
        </a>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <h6 class="mb-0">Upcoming Principal Appointments</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Visitor</th>
                <th>Purpose</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAppointments as $appointment)
              <tr>
                <td>{{ optional($appointment->appointment_date)->format('d M Y') ?? '-' }}</td>
                <td>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '-' }}</td>
                <td>{{ $appointment->visitor_name }}</td>
                <td>{{ $appointment->purpose }}</td>
                <td>
                  <span class="badge bg-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                    {{ ucfirst($appointment->status) }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">No appointments scheduled.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
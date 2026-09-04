@include('includes.header')

<style>
  :root {
    --corp-ink: #1f2a44;
    --corp-muted: #5f6b80;
    --corp-border: #d9dfeb;
    --corp-surface: #f6f8fc;
    --corp-primary: #1f4e8c;
    --corp-primary-soft: #e8f0fb;
    --corp-success-soft: #e7f6ee;
    --corp-warning-soft: #fff4df;
    --corp-danger-soft: #fdecec;
  }

  .corp-page {
    background: linear-gradient(180deg, #f4f7fc 0%, #f8fafd 100%);
    padding-bottom: 24px;
    min-height: calc(100vh - 120px);
  }

  .corp-banner {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    background: linear-gradient(130deg, #ffffff 0%, #f4f8ff 100%);
    box-shadow: 0 8px 24px rgba(28, 46, 87, 0.08);
  }

  .corp-card {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(23, 36, 66, 0.06);
  }

  .corp-card .card-header {
    border-bottom: 1px solid var(--corp-border);
    background: #fff;
  }

  .corp-section-title {
    color: var(--corp-ink);
    font-weight: 700;
    letter-spacing: 0.2px;
  }

  .corp-subtitle {
    color: var(--corp-muted);
    font-size: 0.88rem;
  }

  .corp-input,
  .corp-select {
    border-color: var(--corp-border);
    border-radius: 10px;
    min-height: 42px;
  }

  .corp-input:focus,
  .corp-select:focus {
    border-color: #97b6e3;
    box-shadow: 0 0 0 0.2rem rgba(31, 78, 140, 0.15);
  }

  .corp-btn-primary {
    background: var(--corp-primary);
    border-color: var(--corp-primary);
  }

  .corp-btn-primary:hover {
    background: #183f74;
    border-color: #183f74;
  }

  .corp-filter-wrap {
    background: var(--corp-surface);
    border: 1px solid var(--corp-border);
    border-radius: 12px;
    padding: 10px;
  }

  .corp-table thead th {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #42516a;
    border-bottom: 1px solid var(--corp-border);
    background: #f8fafd;
  }

  .corp-table tbody td {
    vertical-align: middle;
  }

  .corp-row-edit {
    background: #f9fbff;
  }

  .badge-status {
    font-weight: 600;
    border: 1px solid transparent;
    padding: 0.36rem 0.58rem;
  }

  .badge-status.scheduled {
    background: var(--corp-primary-soft);
    color: var(--corp-primary);
    border-color: #c8d9f2;
  }

  .badge-status.rescheduled {
    background: var(--corp-warning-soft);
    color: #8b640f;
    border-color: #f3deaa;
  }

  .badge-status.completed {
    background: var(--corp-success-soft);
    color: #176a3d;
    border-color: #bfdfcb;
  }

  .badge-status.cancelled {
    background: var(--corp-danger-soft);
    color: #9c2f2f;
    border-color: #f2c6c6;
  }
</style>

@php
$appointmentItems = collect($appointments->items());
$scheduledCount = $appointmentItems->where('status', 'scheduled')->count();
$rescheduledCount = $appointmentItems->where('status', 'rescheduled')->count();
$completedCount = $appointmentItems->where('status', 'completed')->count();
@endphp

<div class="wrapper">
  @include('receptionist.sidebar')

  <main class="page-content corp-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Principal Appointments</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Appointments</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card corp-banner mt-3 mb-3">
      <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <h5 class="mb-1 corp-section-title">Executive Appointment Desk</h5>
          <div class="corp-subtitle">Manage principal meetings with clear scheduling controls and conflict-safe slot booking.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge badge-status scheduled">Scheduled: {{ $scheduledCount }}</span>
          <span class="badge badge-status rescheduled">Rescheduled: {{ $rescheduledCount }}</span>
          <span class="badge badge-status completed">Completed: {{ $completedCount }}</span>
        </div>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm">
      {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
      <strong>Please review the form:</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="card corp-card mt-3 mb-3">
      <div class="card-header bg-white">
        <h6 class="mb-0 corp-section-title">Schedule New Appointment</h6>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('receptionist.appointments.store') }}" class="row g-3">
          @csrf
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Visitor Name</label>
            <input class="form-control corp-input" name="visitor_name" value="{{ old('visitor_name') }}" placeholder="Enter visitor name" required>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Phone</label>
            <input class="form-control corp-input" name="visitor_phone" value="{{ old('visitor_phone') }}" placeholder="Contact number">
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Email</label>
            <input type="email" class="form-control corp-input" name="visitor_email" value="{{ old('visitor_email') }}" placeholder="Official email">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Appointment Date</label>
            <input type="date" class="form-control corp-input" name="appointment_date" value="{{ old('appointment_date') }}" required>
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Appointment Time</label>
            <input type="time" class="form-control corp-input" name="appointment_time" value="{{ old('appointment_time') }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Purpose</label>
            <input class="form-control corp-input" name="purpose" value="{{ old('purpose') }}" placeholder="Meeting purpose" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Notes</label>
            <input class="form-control corp-input" name="notes" value="{{ old('notes') }}" placeholder="Optional comments">
          </div>
          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Initial Status</label>
            <select class="form-select corp-select" name="status">
              <option value="scheduled">Scheduled</option>
              <option value="rescheduled">Rescheduled</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="col-12 pt-1">
            <button class="btn corp-btn-primary text-white px-4">Save Appointment</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card corp-card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 corp-section-title">Appointments List</h6>
        <div class="corp-filter-wrap">
          <form method="GET" action="{{ route('receptionist.appointments.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="date" class="form-control form-control-sm corp-input" name="date" value="{{ request('date') }}">
            <select name="status" class="form-select form-select-sm corp-select">
              <option value="">All Status</option>
              @foreach(['scheduled','rescheduled','completed','cancelled'] as $status)
              <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
            <button class="btn btn-sm btn-outline-primary">Filter</button>
          </form>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 corp-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Visitor</th>
                <th>Contact</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($appointments as $appointment)
              <tr>
                <td>{{ optional($appointment->appointment_date)->format('d M Y') }}</td>
                <td>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '-' }}</td>
                <td>{{ $appointment->visitor_name }}</td>
                <td>
                  <div>{{ $appointment->visitor_phone ?: '-' }}</div>
                  <div class="small text-muted">{{ $appointment->visitor_email ?: '-' }}</div>
                </td>
                <td>{{ $appointment->purpose }}</td>
                <td>
                  <span class="badge badge-status {{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-{{ $appointment->id }}">Edit</button>
                  <form method="POST" action="{{ route('receptionist.appointments.destroy', $appointment->id) }}" class="d-inline" onsubmit="return confirm('Delete this appointment?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <tr class="collapse corp-row-edit" id="edit-{{ $appointment->id }}">
                <td colspan="7">
                  <form method="POST" action="{{ route('receptionist.appointments.update', $appointment->id) }}" class="row g-2">
                    @csrf
                    @method('PUT')
                    <div class="col-md-2"><input class="form-control corp-input" name="visitor_name" value="{{ $appointment->visitor_name }}" required></div>
                    <div class="col-md-2"><input class="form-control corp-input" name="visitor_phone" value="{{ $appointment->visitor_phone }}"></div>
                    <div class="col-md-2"><input type="email" class="form-control corp-input" name="visitor_email" value="{{ $appointment->visitor_email }}"></div>
                    <div class="col-md-2"><input type="date" class="form-control corp-input" name="appointment_date" value="{{ optional($appointment->appointment_date)->format('Y-m-d') }}" required></div>
                    <div class="col-md-2"><input type="time" class="form-control corp-input" name="appointment_time" value="{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : '' }}" required></div>
                    <div class="col-md-2">
                      <select class="form-select corp-select" name="status" required>
                        @foreach(['scheduled','rescheduled','completed','cancelled'] as $status)
                        <option value="{{ $status }}" {{ $appointment->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4"><input class="form-control corp-input" name="purpose" value="{{ $appointment->purpose }}" required></div>
                    <div class="col-md-6"><input class="form-control corp-input" name="notes" value="{{ $appointment->notes }}"></div>
                    <div class="col-md-2"><button class="btn corp-btn-primary text-white btn-sm">Update</button></div>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-4 text-muted">No appointments found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">{{ $appointments->links() }}</div>
    </div>
  </main>
</div>

@include('includes.footer')
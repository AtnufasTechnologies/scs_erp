@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid py-4">

    <nav class="navbar navbar-expand-lg navbar-dark mb-4"
      style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('department.offerings.index') }}">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white">
            Registrations —
            {{ $offering->courseType->title ?? '—' }} /
            {{ $offering->batch->batch_name ?? '—' }} /
            {{ $offering->semester->title ?? '—' }}
          </span>
        </a>
      </div>
    </nav>

    <!-- Stats -->
    @php
    $confirmed = $offering->registrations->where('status', 'confirmed')->count();
    $waitlisted = $offering->registrations->where('status', 'waitlisted')->count();
    $cancelled = $offering->registrations->where('status', 'cancelled')->count();
    $available = max(0, $offering->intake_capacity - $confirmed);
    @endphp
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="fs-1 fw-bold text-primary">{{ $offering->intake_capacity }}</div>
            <div class="text-muted small">Total Seats</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="fs-1 fw-bold text-success">{{ $confirmed }}</div>
            <div class="text-muted small">Confirmed</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="fs-1 fw-bold text-warning">{{ $waitlisted }}</div>
            <div class="text-muted small">Waitlisted</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-center border-0 shadow-sm">
          <div class="card-body">
            <div class="fs-1 fw-bold text-info">{{ $available }}</div>
            <div class="text-muted small">Available Seats</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Registration Table -->
    <div class="card shadow-sm">
      <div class="card-header fw-bold">
        <i class="fa fa-list me-2"></i>Student Queue (FIFO Order)
      </div>
      <div class="card-body p-0">
        @if($offering->registrations->whereNotIn('status', ['cancelled'])->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="fa fa-inbox fa-2x mb-2"></i>
          <p>No registrations yet.</p>
        </div>
        @else
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Queue #</th>
                <th>Roll No.</th>
                <th>Student Name</th>
                <th>Status</th>
                <th>Registered At</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($offering->registrations->whereNotIn('status', ['cancelled'])->sortBy('queue_position') as $reg)
              <tr>
                <td><span class="badge bg-secondary">{{ $reg->queue_position }}</span></td>
                <td>{{ $reg->student->roll_no ?? '—' }}</td>
                <td class="text-capitalize">
                  {{ $reg->student->first_name ?? '—' }} {{ $reg->student->last_name ?? '' }}
                </td>
                <td>
                  @if($reg->status === 'confirmed')
                  <span class="badge bg-success">Confirmed</span>
                  @elseif($reg->status === 'waitlisted')
                  <span class="badge bg-warning text-dark">Waitlisted</span>
                  @endif
                </td>
                <td>{{ $reg->created_at->format('d M Y h:i A') }}</td>
                <td class="text-center">
                  <form action="{{ route('department.offerings.cancel-registration', $reg->id) }}"
                    method="POST" style="display:inline;"
                    onsubmit="return confirm('Cancel this student\'s registration and promote the next in queue?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="fa fa-times me-1"></i>Cancel
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>
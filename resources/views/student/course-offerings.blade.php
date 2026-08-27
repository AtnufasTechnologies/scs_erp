@include('includes.header')

<div class="wrapper">
  <main class="page-content" style="overflow-x:hidden;">
    <div class="container-fluid py-4 px-3">

      <!-- Header -->
      <div class="p-4 mb-4 rounded-4 text-white"
        style="background: linear-gradient(135deg, #653dca 0%, #8931f6 100%);">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
          <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-book-open me-2"></i>Course Registration</h4>
            <p class="mb-0 opacity-75 small">
              Seats are allocated on a first-come, first-served basis.
              Batch: <strong>{{ $student->batchmaster->batch_name ?? '—' }}</strong>
            </p>
          </div>

          <form action="{{ route('student.offerings.sync-roster-courses') }}" method="POST"
            onsubmit="return confirm('Sync all your roster courses into your enrolled course list (StudentCourseInfo)?')">
            @csrf
            <button type="submit" class="btn btn-light btn-sm fw-semibold">
              <i class="fas fa-link me-1"></i>Sync Roster Courses
            </button>
          </form>
        </div>
      </div>

      <!-- My Registrations -->
      @if($myRegistrations->isNotEmpty())
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold bg-white border-bottom">
          <i class="fas fa-clipboard-check me-2 text-success"></i>My Registrations
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Department</th>
                  <th>Course Type</th>
                  <th>Batch</th>
                  <th>Semester</th>
                  <th>Queue #</th>
                  <th>Status</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($myRegistrations as $reg)
                <tr>
                  <td class="text-capitalize">{{ $reg->offering->subject->title ?? '—' }}</td>
                  <td>{{ $reg->offering->courseType->title ?? '—' }}</td>
                  <td>{{ $reg->offering->batch->batch_name ?? '—' }}</td>
                  <td>{{ $reg->offering->semester->title ?? '—' }}</td>
                  <td><span class="badge bg-secondary">#{{ $reg->queue_position }}</span></td>
                  <td>
                    @if($reg->status === 'confirmed')
                    <span class="badge bg-success">Confirmed</span>
                    @elseif($reg->status === 'waitlisted')
                    <span class="badge bg-warning text-dark">Waitlisted</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($reg->offering->is_registration_open)
                    <form action="{{ route('student.offerings.cancel', $reg->id) }}" method="POST"
                      style="display:inline;"
                      onsubmit="return confirm('Cancel your registration? If confirmed, the next waitlisted student will be promoted.')">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times me-1"></i>Cancel
                      </button>
                    </form>
                    @else
                    <span class="text-muted small">Closed</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif

      <!-- Available Offerings -->
      <h5 class="fw-bold mb-3"><i class="fas fa-list-alt me-2 text-primary"></i>Available Offerings</h5>

      @if($offerings->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <p>No open registrations for your batch at this time.</p>
      </div>
      @else
      @php
      $semGroups = $offerings->groupBy(fn($o) => $o->semester->title ?? 'Unknown Semester');
      @endphp
      @foreach($semGroups as $semName => $semOfferings)
      <h6 class="text-muted mt-3 mb-2 border-bottom pb-1">
        <i class="fas fa-calendar-alt me-1"></i>{{ $semName }}
      </h6>
      <div class="row g-3 mb-3">
        @foreach($semOfferings as $offering)
        @php
        $confirmed = $offering->confirmedRegistrations->count();
        $waitlisted = $offering->waitlistedRegistrations->count();
        $available = max(0, $offering->intake_capacity - $confirmed);
        $alreadyIn = $offering->my_registration !== null;
        $myReg = $offering->my_registration;
        $fillPct = $offering->intake_capacity > 0
        ? min(100, round(($confirmed / $offering->intake_capacity) * 100))
        : 0;
        @endphp
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm border-0" style="border-radius:14px; overflow:hidden;">
            <div class="card-header text-white fw-semibold"
              style="background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%);">
              {{ $offering->subject->title ?? '—' }}
              <span class="float-end badge bg-light text-dark text-capitalize">
                {{ $offering->courseType->title ?? '—' }}
              </span>
            </div>
            <div class="card-body">
              <!-- Fill bar -->
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1 small">
                  <span>{{ $confirmed }}/{{ $offering->intake_capacity }} seats filled</span>
                  <span>{{ $fillPct }}%</span>
                </div>
                <div class="progress" style="height:8px; border-radius:4px;">
                  <div class="progress-bar
                      @if($fillPct >= 100) bg-danger
                      @elseif($fillPct >= 75) bg-warning
                      @else bg-success @endif"
                    style="width:{{ $fillPct }}%"></div>
                </div>
              </div>

              <div class="d-flex gap-2 flex-wrap mb-3">
                @if($available > 0)
                <span class="badge bg-success">{{ $available }} seat(s) open</span>
                @else
                <span class="badge bg-danger">Full</span>
                @endif
                @if($waitlisted > 0)
                <span class="badge bg-warning text-dark">{{ $waitlisted }} on waitlist</span>
                @endif
              </div>

              @if($offering->registration_closes_at)
              <p class="small text-muted mb-3">
                <i class="fas fa-clock me-1 text-danger"></i>
                Closes: {{ $offering->registration_closes_at->format('d M Y h:i A') }}
              </p>
              @endif

              @if($alreadyIn)
              <div class="alert alert-sm py-2 mb-0
                    {{ $myReg->status === 'confirmed' ? 'alert-success' : 'alert-warning' }}">
                <i class="fas fa-check-circle me-1"></i>
                @if($myReg->status === 'confirmed')
                You are confirmed (Seat #{{ $myReg->queue_position }})
                @else
                Waitlisted (Position #{{ $myReg->queue_position }})
                @endif
              </div>
              @else
              <form action="{{ route('student.offerings.register') }}" method="POST">
                @csrf
                <input type="hidden" name="offering_id" value="{{ $offering->id }}">
                <button type="submit" class="btn btn-primary w-100">
                  @if($available > 0)
                  <i class="fas fa-user-plus me-1"></i>Register
                  @else
                  <i class="fas fa-list-ol me-1"></i>Join Waitlist
                  @endif
                </button>
              </form>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endforeach
      @endif

    </div>
  </main>
</div>
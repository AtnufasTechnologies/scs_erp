@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">TPO Events</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Events</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <form method="GET" action="{{ route('tpo.training-placement.events.index') }}" class="row g-2 align-items-end">
            <div class="col-md-9">
              <label class="form-label fw-semibold mb-1">Search Events</label>
              <input type="text" name="search" value="{{ $eventSearch ?? '' }}" class="form-control" placeholder="Search by title, resource person, description, type, campus or department">
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Search</button>
              @if(!empty($eventSearch))
              <a href="{{ route('tpo.training-placement.events.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">Add Conducted Event</h6>
            </div>
            <div class="card-body">
              <form action="{{ route('tpo.training-placement.events.store') }}" method="POST" enctype="multipart/form-data" class="js-tpo-event-form">
                @csrf
                <div class="mb-2">
                  <label class="form-label fw-semibold">Event Type</label>
                  <select name="event_type" class="form-select" required>
                    <option value="" selected disabled>Select type</option>
                    @foreach($eventTypeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label fw-semibold">Program Title</label>
                  <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-2">
                  <label class="form-label fw-semibold">Resource Person (Optional)</label>
                  <input type="text" name="resource_person" class="form-control" placeholder="Name of speaker/trainer">
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Campus</label>
                    <select name="campus_id" class="form-select js-event-campus" required>
                      <option value="" selected disabled>Select campus</option>
                      @foreach($campuses as $campus)
                      <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="subject_id" class="form-select js-event-subject" required>
                      <option value="" selected disabled>Select department</option>
                      @foreach($subjects as $subject)
                      <option value="{{ $subject->id }}" data-campus-id="{{ $subject->campus_id }}">{{ $subject->title ?? $subject->name ?? ('Department #' . $subject->id) }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Participants</label>
                    <input type="number" min="0" name="participant_count" class="form-control" required>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label fw-semibold">Program Description</label>
                  <textarea name="program_description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Upload Report</label>
                  <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                  <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG (10MB max)</small>
                </div>
                <button class="btn btn-primary" type="submit">Save Event</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-xl-8">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">Conducted Events List</h6>
              <span class="badge bg-secondary">Total: {{ $events->count() }}</span>
            </div>
            <div class="card-body">
              @forelse($events as $event)
              <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                  <div>
                    <h6 class="mb-1 fw-bold">{{ $event->title }}</h6>
                    <div class="small text-muted mb-1">Type: {{ $eventTypeOptions[$event->event_type] ?? ucfirst(str_replace('_', ' ', $event->event_type ?? 'N/A')) }}</div>
                    <div class="small text-muted mb-1">Resource Person: {{ $event->resource_person ?: 'N/A' }}</div>
                    <div class="small text-muted mb-1">Campus: {{ $event->campus->name ?? 'N/A' }}</div>
                    <div class="small text-muted mb-1">Department: {{ $event->subject->title ?? $event->subject->name ?? 'N/A' }}</div>
                    <div class="small text-muted mb-1">Date: {{ $event->event_date ? $event->event_date->format('d M Y') : 'N/A' }}</div>
                    <div class="small text-muted mb-1">Participants: {{ $event->participant_count }}</div>
                    <div class="small text-muted mb-2">Description: {{ $event->program_description }}</div>
                    <div class="small text-muted mb-1">
                      Report:
                      @if($event->report_path)
                      <a href="{{ Storage::disk('s3')->url($event->report_path) }}" target="_blank">View</a>
                      @else
                      N/A
                      @endif
                    </div>
                    <div class="small">
                      Approval:
                      @if($event->approval_status === 'approved')
                      <span class="badge bg-success">Approved</span>
                      @elseif($event->approval_status === 'rejected')
                      <span class="badge bg-danger">Rejected</span>
                      @else
                      <span class="badge bg-warning text-dark">Pending</span>
                      @endif
                      @if($event->approval_status === 'approved' && $event->approved_at)
                      <span class="text-muted">on {{ $event->approved_at->format('d M Y, h:i A') }}</span>
                      @endif
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#eventManage{{ $event->id }}">Edit</button>
                    <form action="{{ route('tpo.training-placement.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Delete this event?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                  </div>
                </div>

                <div class="collapse mt-3" id="eventManage{{ $event->id }}">
                  <form action="{{ route('tpo.training-placement.events.update', $event->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3 js-tpo-event-form">
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Event Type</label>
                        <select name="event_type" class="form-select" required>
                          @foreach($eventTypeOptions as $value => $label)
                          <option value="{{ $value }}" {{ $event->event_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Program Title</label>
                        <input type="text" name="title" class="form-control" value="{{ $event->title }}" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Resource Person (Optional)</label>
                        <input type="text" name="resource_person" class="form-control" value="{{ $event->resource_person }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Campus</label>
                        <select name="campus_id" class="form-select js-event-campus" required>
                          @foreach($campuses as $campus)
                          <option value="{{ $campus->id }}" {{ (int) $event->campus_id === (int) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="subject_id" class="form-select js-event-subject" required>
                          @foreach($subjects as $subject)
                          <option value="{{ $subject->id }}" data-campus-id="{{ $subject->campus_id }}" {{ (int) $event->subject_id === (int) $subject->id ? 'selected' : '' }}>{{ $subject->title ?? $subject->name ?? ('Department #' . $subject->id) }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="event_date" class="form-control" value="{{ $event->event_date ? $event->event_date->format('Y-m-d') : '' }}" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Participants</label>
                        <input type="number" min="0" name="participant_count" class="form-control" value="{{ $event->participant_count }}" required>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">Program Description</label>
                        <textarea name="program_description" class="form-control" rows="3" required>{{ $event->program_description }}</textarea>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">Replace Report</label>
                        <input type="file" name="report_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                      </div>
                      <div class="col-12">
                        <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No events recorded yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var forms = document.querySelectorAll('.js-tpo-event-form');

    forms.forEach(function(form) {
      var campusSelect = form.querySelector('.js-event-campus');
      var subjectSelect = form.querySelector('.js-event-subject');

      function applySubjectFilter() {
        if (!campusSelect || !subjectSelect) {
          return;
        }

        var campusId = campusSelect.value;
        Array.prototype.slice.call(subjectSelect.options).forEach(function(option) {
          var optionCampusId = option.getAttribute('data-campus-id');
          if (!option.value || !optionCampusId || !campusId) {
            option.hidden = false;
            option.disabled = false;
            return;
          }

          var isMatch = String(optionCampusId) === String(campusId);
          option.hidden = !isMatch;
          option.disabled = !isMatch;

          if (!isMatch && option.selected) {
            option.selected = false;
          }
        });
      }

      if (campusSelect) {
        campusSelect.addEventListener('change', applySubjectFilter);
      }

      applySubjectFilter();
    });
  });
</script>

@include('includes.footer')
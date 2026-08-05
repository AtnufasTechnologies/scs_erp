@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Council Meetings: {{ $council->title }}</h3>

      <div class="card shadow-sm mb-3">
        <div class="card-header">Add Meeting</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.student-council.meetings.store', $council->id) }}" enctype="multipart/form-data" class="row g-2">
            @csrf
            <div class="col-md-2"><input name="meeting_no" class="form-control" placeholder="Meeting No" value="{{ old('meeting_no') }}"></div>
            <div class="col-md-4"><input name="title" class="form-control" placeholder="Meeting title" value="{{ old('title') }}" required></div>
            <div class="col-md-2"><input type="date" name="meeting_date" class="form-control" value="{{ old('meeting_date') }}" required></div>
            <div class="col-md-2"><input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}"></div>
            <div class="col-md-2"><input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}"></div>
            <div class="col-md-4"><input name="venue" class="form-control" placeholder="Venue" value="{{ old('venue') }}"></div>
            <div class="col-md-3">
              <select name="status" class="form-select" required>
                <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rescheduled" {{ old('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>
            <div class="col-md-5"><input type="file" name="minutes_pdf" class="form-control" accept="application/pdf"></div>
            <div class="col-12">
              <label class="form-label mb-1">Agenda</label>
              <textarea name="agenda" class="form-control editor" rows="4">{{ old('agenda') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label mb-1">Minutes</label>
              <textarea name="minutes" class="form-control editor2" rows="5">{{ old('minutes') }}</textarea>
            </div>
            <div class="col-12">
              <label class="form-label mb-1">Resolutions</label>
              <textarea name="resolutions" class="form-control" rows="4">{{ old('resolutions') }}</textarea>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Save Meeting</button></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Meetings</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Title</th>
                <th>Venue</th>
                <th>Status</th>
                <th>Minutes</th>
                <th>Documents</th>
              </tr>
            </thead>
            <tbody>
              @forelse($meetings as $meeting)
              <tr>
                <td>{{ optional($meeting->meeting_date)->format('d-M-Y') }}</td>
                <td>{{ $meeting->title }}</td>
                <td>{{ $meeting->venue ?? '-' }}</td>
                <td>{{ $meeting->status }}</td>
                <td>{!! \Illuminate\Support\Str::limit(strip_tags((string) $meeting->minutes), 120, '...') !!}</td>
                <td>
                  @forelse($meeting->documents as $doc)
                  <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">{{ $doc->document_type }}</a>
                  @empty
                  <span class="text-muted">-</span>
                  @endforelse
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No meetings found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
          {{ $meetings->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
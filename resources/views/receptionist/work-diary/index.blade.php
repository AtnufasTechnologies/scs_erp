@include('includes.header')

<div class="wrapper">
  @include('receptionist.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Work Diary</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Daily Work Notes</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3 mb-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Add Daily Work</h6>
        <form method="GET" action="{{ route('receptionist.work-diary.index') }}" class="d-flex gap-2 align-items-center">
          <label class="small text-muted mb-0">Month</label>
          <input type="month" class="form-control form-control-sm" name="month" value="{{ $selectedMonth }}">
          <button class="btn btn-sm btn-outline-primary">Load</button>
        </form>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('receptionist.work-diary.store') }}" class="row g-2">
          @csrf
          <div class="col-md-2"><input type="date" class="form-control" name="entry_date" required></div>
          <div class="col-md-6"><input type="text" class="form-control" name="work_summary" placeholder="Work summary" required></div>
          <div class="col-md-2">
            <select class="form-select" name="status">
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <div class="col-md-10"><input type="text" class="form-control" name="notes" placeholder="Notes"></div>
          <div class="col-md-2"><button class="btn btn-primary w-100">Add Entry</button></div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white">
        <h6 class="mb-0">My Entries</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Work Summary</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($entries as $entry)
              <tr>
                <td>{{ optional($entry->entry_date)->format('d M Y') }}</td>
                <td>{{ $entry->work_summary }}</td>
                <td>{{ $entry->notes ?: '-' }}</td>
                <td><span class="badge bg-{{ $entry->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($entry->status) }}</span></td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-work-{{ $entry->id }}">Edit</button>
                  <form method="POST" action="{{ route('receptionist.work-diary.destroy', $entry->id) }}" class="d-inline" onsubmit="return confirm('Delete this work entry?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <tr class="collapse" id="edit-work-{{ $entry->id }}">
                <td colspan="5">
                  <form method="POST" action="{{ route('receptionist.work-diary.update', $entry->id) }}" class="row g-2">
                    @csrf
                    @method('PUT')
                    <div class="col-md-2"><input type="date" class="form-control" name="entry_date" value="{{ optional($entry->entry_date)->format('Y-m-d') }}" required></div>
                    <div class="col-md-5"><input type="text" class="form-control" name="work_summary" value="{{ $entry->work_summary }}" required></div>
                    <div class="col-md-2">
                      <select class="form-select" name="status" required>
                        <option value="completed" {{ $entry->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ $entry->status === 'pending' ? 'selected' : '' }}>Pending</option>
                      </select>
                    </div>
                    <div class="col-md-3"><input type="text" class="form-control" name="notes" value="{{ $entry->notes }}"></div>
                    <div class="col-md-2"><button class="btn btn-primary btn-sm">Update</button></div>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">No diary entries in this month.</td>
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
@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Work Diary</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.faculty.index') }}">Faculty</a></li>
            <li class="breadcrumb-item active" aria-current="page">Work Diary</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Faculty Info Header --}}
    <div class="card mt-3">
      <div class="card-body d-flex align-items-center gap-3 flex-wrap">
        @if($faculty->photo)
        <img src="{{ asset('storage/' . $faculty->photo) }}" alt="Photo" class="rounded-circle" width="60" height="60" style="object-fit: cover;">
        @else
        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px; font-size: 24px;">
          {{ strtoupper(substr($faculty->FIRST_NAME, 0, 1)) }}
        </div>
        @endif
        <div>
          <h5 class="mb-0 text-capitalize">{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</h5>
          <small class="text-muted">{{ $faculty->USER_CODE }} | {{ $faculty->department_info ? $faculty->department_info->name : '-' }}</small>
        </div>
        <div class="ms-auto d-flex gap-2 flex-wrap">
          @if($pendingEntries > 0)
          <form action="{{ route('principal.work-diary.bulk-approve') }}" method="POST" onsubmit="return confirm('Approve all {{ $pendingEntries }} pending entries for this month?')">
            @csrf
            <input type="hidden" name="faculty_id" value="{{ $faculty->id }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i>Approve All ({{ $pendingEntries }})</button>
          </form>
          @endif
          <a href="{{ route('principal.faculty.detail', $faculty->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
          <a href="{{ route('principal.faculty.timetable', $faculty->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-calendar-alt"></i> Timetable</a>
        </div>
      </div>
    </div>

    {{-- Month Selector --}}
    <div class="card mt-3">
      <div class="card-body d-flex align-items-center justify-content-between">
        <h6 class="mb-0">Work Diary - {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</h6>
        <form method="GET" action="{{ route('principal.faculty.work-diary', $faculty->id) }}" class="d-flex align-items-center gap-2">
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
        </form>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3">
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-primary">{{ $totalEntries }}</h4>
            <small class="text-muted">Total</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-info">{{ $regularClasses }}</h4>
            <small class="text-muted">Regular</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-warning">{{ $extraClasses }}</h4>
            <small class="text-muted">Extra</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-secondary">{{ $substitutionClasses }}</h4>
            <small class="text-muted">Substitution</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-success">{{ $completedEntries }}</h4>
            <small class="text-muted">Completed</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 text-success">{{ $approvedEntries }}</h4>
            <small class="text-muted">Approved</small>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <h4 class="mb-0 {{ $pendingEntries > 0 ? 'text-danger' : 'text-muted' }}">{{ $pendingEntries }}</h4>
            <small class="text-muted">Pending</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Work Diary Entries Grouped by Date --}}
    @if($entriesByDate->count())
    @foreach($entriesByDate as $date => $entries)
    <div class="card mt-3">
      <div class="card-header bg-light d-flex align-items-center justify-content-between">
        <div>
          <strong>{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</strong>
          <span class="badge bg-secondary ms-2">{{ $entries->count() }} entries</span>
        </div>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th style="width: 80px;">Hour</th>
              <th>Description</th>
              <th style="width: 150px;">Methodology</th>
              <th style="width: 120px;">Class Type</th>
              <th style="width: 120px;">Work Type</th>
              <th style="width: 120px;">Status</th>
              <th style="width: 100px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($entries as $entry)
            <tr>
              <td><span class="badge bg-dark">{{ $entry->hour }}</span></td>
              <td>{{ $entry->description }}</td>
              <td>{{ $entry->methodology ?? '-' }}</td>
              <td>
                @if($entry->class_type == 'regular')
                <span class="badge bg-info">Regular</span>
                @elseif($entry->class_type == 'extra')
                <span class="badge bg-warning">Extra</span>
                @elseif($entry->class_type == 'substitution')
                <span class="badge bg-secondary">Substitution</span>
                @else
                <span class="text-muted">{{ $entry->class_type ?? '-' }}</span>
                @endif
              </td>
              <td>{{ $entry->work_type ?? '-' }}</td>
              <td>
                @if($entry->status == 'approved')
                <span class="badge bg-success">Approved</span>
                @elseif($entry->status == 'completed')
                <span class="badge bg-primary">Completed</span>
                @else
                <span class="badge bg-warning">Pending</span>
                @endif
              </td>
              <td>
                @if($entry->status == 'pending' || $entry->status == 'completed')
                <form action="{{ route('principal.work-diary.approve', $entry->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-xs btn-success" title="Approve">
                    <i class="fas fa-check"></i>
                  </button>
                </form>
                @else
                <i class="fas fa-check-circle text-success"></i>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endforeach
    @else
    <div class="card mt-3">
      <div class="card-body text-center py-5">
        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
        <p class="text-muted">No work diary entries found for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}.</p>
      </div>
    </div>
    @endif
  </main>
</div>

@include('includes.footer')
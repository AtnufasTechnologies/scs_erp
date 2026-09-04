@include('includes.header')

<style>
  :root {
    --corp-ink: #1f2a44;
    --corp-muted: #5f6b80;
    --corp-border: #d9dfeb;
    --corp-surface: #f6f8fc;
    --corp-primary: #1f4e8c;
    --corp-primary-soft: #e8f0fb;
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

  .corp-section-title {
    color: var(--corp-ink);
    font-weight: 700;
    letter-spacing: 0.2px;
  }

  .corp-subtitle {
    color: var(--corp-muted);
    font-size: 0.88rem;
  }

  .corp-chip {
    background: var(--corp-primary-soft);
    color: var(--corp-primary);
    border: 1px solid #c8d9f2;
    border-radius: 999px;
    font-weight: 600;
    padding: 0.38rem 0.66rem;
  }

  .corp-card {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(23, 36, 66, 0.06);
  }

  .corp-filter-wrap {
    background: #fff;
    border: 1px solid var(--corp-border);
    border-radius: 14px;
  }

  .corp-label {
    color: #4e5f7a;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 700;
    margin-bottom: 6px;
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

  .faculty-card {
    border: 1px solid var(--corp-border);
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  .faculty-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(24, 49, 91, 0.12);
  }

  .faculty-card-header {
    background: linear-gradient(140deg, #213d72 0%, #2f5da2 100%);
    color: #fff;
    padding: 12px 14px;
  }

  .faculty-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
  }

  .meta-item {
    border: 1px solid var(--corp-border);
    border-radius: 10px;
    background: #fbfcff;
    padding: 8px 10px;
    font-size: 0.84rem;
  }

  .leave-badge {
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    padding: 0.28rem 0.56rem;
    border: 1px solid transparent;
  }

  .leave-badge.leave-active {
    background: #fdecec;
    color: #9c2f2f;
    border-color: #f2c6c6;
  }

  .leave-badge.leave-available {
    background: #e7f6ee;
    color: #176a3d;
    border-color: #bfdfcb;
  }
</style>

@php
$facultyItems = collect($facultyList->items());
$shownCount = $facultyItems->count();
$campusCount = $facultyItems
->map(fn($item) => optional(optional($item->department_info)->campusmaster)->name)
->filter(fn($value) => !empty($value))
->unique()
->count();
@endphp

<div class="wrapper">
  @include('receptionist.sidebar')

  <main class="page-content corp-page">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty List</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('receptionist.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Campus-wise Faculty</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card corp-banner mt-3 mb-3">
      <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <h5 class="mb-1 corp-section-title">Campus-wise Faculty Directory</h5>
          <div class="corp-subtitle">Professional faculty listing for front office operations and quick timetable access.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <span class="corp-chip">Showing: {{ $shownCount }}</span>
          <span class="corp-chip">Campuses in Result: {{ $campusCount }}</span>
        </div>
      </div>
    </div>

    <div class="card corp-card corp-filter-wrap mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('receptionist.faculty.index') }}" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label corp-label">Campus</label>
            <select name="campus_id" class="form-select corp-select">
              <option value="">All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ (string) $selectedCampus === (string) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label corp-label">Department</label>
            <select name="department_id" class="form-select corp-select">
              <option value="">All Departments</option>
              @foreach($departments as $department)
              <option value="{{ $department->id }}" {{ (string) $selectedDepartment === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label corp-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control corp-input" placeholder="Name or faculty code">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn corp-btn-primary text-white" type="submit">Apply Filter</button>
            <a class="btn btn-light" href="{{ route('receptionist.faculty.index') }}">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="row mt-3">
      @forelse($facultyList as $faculty)
      <div class="col-lg-4 mb-3">
        <div class="card faculty-card h-100">
          <div class="faculty-card-header d-flex align-items-center gap-2">
            <div class="faculty-avatar">
              {{ strtoupper(substr((string) $faculty->FIRST_NAME, 0, 1)) }}{{ strtoupper(substr((string) $faculty->LAST_NAME, 0, 1)) }}
            </div>
            <div>
              <div class="fw-semibold">{{ trim($faculty->FIRST_NAME . ' ' . $faculty->MIDDLE_NAME . ' ' . $faculty->LAST_NAME) }}</div>
              <div class="small opacity-75">{{ $faculty->USER_CODE }}</div>
            </div>
            <div class="ms-auto">
              @if(!empty($faculty->is_on_leave_today))
              <span class="leave-badge leave-active">On Leave Today</span>
              @else
              <span class="leave-badge leave-available">Available</span>
              @endif
            </div>
          </div>
          <div class="card-body">
            <div class="small mb-3 d-grid gap-2">
              <div class="meta-item"><strong>Department:</strong> {{ optional($faculty->department_info)->name ?? '-' }}</div>
              <div class="meta-item"><strong>Campus:</strong> {{ optional(optional($faculty->department_info)->campusmaster)->name ?? '-' }}</div>
              <div class="meta-item"><strong>Phone:</strong> {{ $faculty->MOBILE_NO ?? '-' }}</div>
            </div>
            <a href="{{ route('receptionist.faculty.timetable', $faculty->id) }}" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-calendar-alt me-1"></i> View Timetable
            </a>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="card corp-card border-0 shadow-sm">
          <div class="card-body text-center text-muted py-5">No faculty found for the selected filters.</div>
        </div>
      </div>
      @endforelse
    </div>

    <div class="mt-2">{{ $facultyList->links('vendor.pagination.bootstrap-5') }}</div>
  </main>
</div>

@include('includes.footer')
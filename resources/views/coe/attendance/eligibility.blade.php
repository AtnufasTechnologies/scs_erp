@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Attendance</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Eligibility</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div class="row mb-2">
        <div class="col-lg-2">
          <div class="bg-warning threshold-pill">Threshold {{ (int) $threshold }}%</div>

        </div>
      </div>


      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="bg-primary radius-10">
            <div class="card-body">
              <div class="small text-uppercase text-light fw-semibold mb-1">Total Students</div>
              <h4 class="mb-0 fw-bold text-light">{{ (int) ($summary['total'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="bg-success radius-10">
            <div class="card-body">
              <div class="small text-uppercase text-light fw-semibold mb-1">Eligible (>= {{ (int) $threshold }}%)</div>
              <h4 class="mb-0 fw-bold text-light">{{ (int) ($summary['eligible'] ?? 0) }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="bg-danger radius-10">
            <div class="card-body">
              <div class="small text-uppercase text-light fw-semibold mb-1">Attendance Shortage (< {{ (int) $threshold }}%)</div>
                  <h4 class="mb-2 fw-bold text-light">{{ (int) ($summary['shortage'] ?? 0) }}</h4>

              </div>
            </div>
          </div>
        </div>

        @if($selectedStudentSummary)
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <div class="text-muted small text-uppercase fw-semibold">Selected Student Total Attendance</div>
              <h5 class="mb-1 fw-bold">
                {{ trim(($selectedStudentSummary->first_name ?? '') . ' ' . ($selectedStudentSummary->last_name ?? '')) ?: '-' }}
              </h5>
              <div class="text-muted">Roll No: {{ $selectedStudentSummary->roll_no ?: '-' }}</div>
            </div>
            <div class="d-flex gap-3 flex-wrap">
              <div class="badge bg-light text-dark p-3">Attended: {{ (int) ($selectedStudentSummary->attended_classes ?? 0) }}</div>
              <div class="badge bg-light text-dark p-3">Total: {{ (int) ($selectedStudentSummary->total_classes ?? 0) }}</div>
              <div class="badge {{ ((float) ($selectedStudentSummary->attendance_percentage ?? 0) >= (float) $threshold) ? 'bg-success' : 'bg-danger' }} p-3">
                {{ number_format((float) ($selectedStudentSummary->attendance_percentage ?? 0), 2) }}%
              </div>
            </div>
          </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
          <div class="card-header bg-transparent border-bottom py-3">
            <form method="GET" action="{{ route('coe.attendance.eligibility') }}" class="row g-2 align-items-center">
              <div class="col-lg-4 col-md-6">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Roll No, register no, student name">
              </div>
              <div class="col-lg-2 col-md-6">
                <select name="program_type" class="form-select">
                  <option value="">All Program Types</option>
                  @foreach($programTypeOptions as $opt)
                  <option value="{{ $opt }}" {{ ($programType ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-1 col-md-6">
                <select name="batch_id" class="form-select">
                  <option value=""> Batches</option>
                  @foreach($batchOptions as $batch)
                  <option value="{{ (int) ($batch->batch_id ?? 0) }}" {{ (int) ($batchId ?? 0) === (int) ($batch->batch_id ?? 0) ? 'selected' : '' }}>
                    {{ $batch->batch_name ?: ((int) ($batch->batch_id ?? 0)) }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-2 col-md-6">
                <select name="semester_id" class="form-select">
                  <option value="">All Semesters</option>
                  @foreach($semesterOptions as $sem)
                  <option value="{{ (int) ($sem->semester_id ?? 0) }}" {{ (int) ($semesterId ?? 0) === (int) ($sem->semester_id ?? 0) ? 'selected' : '' }}>
                    {{ $sem->semester_title ?: ('Semester ' . (int) ($sem->semester_id ?? 0)) }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-1 col-md-6">
                <select name="eligibility" class="form-select">
                  <option value="">All Status</option>
                  <option value="eligible" {{ ($eligibility ?? '') === 'eligible' ? 'selected' : '' }}>Eligible</option>
                  <option value="shortage" {{ ($eligibility ?? '') === 'shortage' ? 'selected' : '' }}>Shortage</option>
                </select>
              </div>
              <div class="col-lg-1 col-md-6">
                <select name="per_page" class="form-select">
                  <option value="25" {{ (int) ($perPage ?? 50) === 25 ? 'selected' : '' }}>25</option>
                  <option value="50" {{ (int) ($perPage ?? 50) === 50 ? 'selected' : '' }}>50</option>
                  <option value="100" {{ (int) ($perPage ?? 50) === 100 ? 'selected' : '' }}>100</option>
                  <option value="200" {{ (int) ($perPage ?? 50) === 200 ? 'selected' : '' }}>200</option>
                </select>
              </div>
              <div class="col-lg-1 col-md-6 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
              </div>
              <div class="col-auto">
                <a
                  href="{{ route('coe.attendance.eligibility.export', request()->except('page')) }}"
                  class="btn btn-outline-success">
                  <i class="fas fa-file-excel me-1"></i>Export Excel
                </a>
              </div>
              <div class="col-auto">
                <a href="{{ route('coe.attendance.eligibility') }}" class="btn btn-outline-secondary">Reset</a>
              </div>
            </form>
          </div>

          <div class="card-body p-0">
            @if($rows->isEmpty())
            <div class="p-5 text-center text-muted">No attendance data found for the selected filters.</div>
            @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">Roll No</th>
                    <th>Student</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Semester</th>
                    <th class="text-end">Attended</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Attendance %</th>
                    <th class="text-center pe-3">Eligibility</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($rows as $row)
                  @php
                  $pct = (float) ($row->attendance_percentage ?? 0);
                  $isEligible = $pct >= (float) $threshold;
                  @endphp
                  <tr>
                    <td class="ps-3 fw-semibold text-uppercase">{{ $row->roll_no ?: '-' }}</td>
                    <td>{{ trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: '-' }}</td>
                    <td>{{ strtoupper((string) ($row->program_type ?? '-')) }}</td>
                    <td>{{ $row->batch_name ?: ( (int) ($row->batch_id ?? 0)) }}</td>
                    <td>{{ $row->semester_title ?: ('Semester ' . (int) ($row->semester_id ?? 0)) }}</td>
                    <td class="text-end">{{ (int) ($row->attended_classes ?? 0) }}</td>
                    <td class="text-end">{{ (int) ($row->total_classes ?? 0) }}</td>
                    <td class="text-end fw-semibold {{ $isEligible ? 'text-success' : 'text-danger' }}">{{ number_format($pct, 2) }}%</td>
                    <td class="text-center pe-3">
                      @if($isEligible)
                      <span class="badge bg-success">Eligible</span>
                      @else
                      <span class="badge bg-danger">Shortage</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="p-3 border-top">
              {{ $rows->appends(request()->except('page'))->links('vendor.pagination.bootstrap-5') }}
            </div>
            @endif
          </div>
        </div>
      </div>
  </main>
</div>

<style>
  .eligibility-hero {
    background: linear-gradient(125deg, #0f172a 0%, #0f766e 55%, #0ea5e9 100%);
  }

  .text-white-80 {
    color: rgba(255, 255, 255, 0.85);
  }

  .threshold-pill {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 999px;
    padding: 0.45rem 0.9rem;
    font-weight: 600;
  }

  .stat-card {
    border: 1px solid #e2e8f0;
  }

  .stat-ok {
    border-left: 4px solid #16a34a;
  }

  .stat-risk {
    border-left: 4px solid #dc2626;
  }
</style>

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Reports</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Result Report</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-7">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-poll me-2"></i>Result Report</h3>
                  <p class="text-white-50 mb-0">Exam results with SGPA, CGPA, and pass/fail analysis</p>
                </div>
                <div class="col-md-5 text-md-end">
                  <form method="POST" action="{{ route('admin.exam-reports.export-pdf') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="results">
                    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <button type="submit" class="btn btn-light me-2"><i class="fas fa-file-pdf me-1"></i>Export PDF</button>
                  </form>
                  <form method="POST" action="{{ route('admin.exam-reports.export-excel') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="report_type" value="results">
                    <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="date" value="{{ request('date') }}">
                    <button type="submit" class="btn btn-outline-light"><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Results</p>
              <h4 class="mb-0 fw-bold">{{ $totalResults }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Published</p>
              <h4 class="mb-0 fw-bold text-success">{{ $publishedCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Pass</p>
              <h4 class="mb-0 fw-bold text-success">{{ $passCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Fail</p>
              <h4 class="mb-0 fw-bold text-danger">{{ $failCount }}</h4>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body text-center">
              <p class="text-muted mb-1" style="font-size: 0.85rem;">Avg SGPA</p>
              <h4 class="mb-0 fw-bold text-primary">{{ $avgSgpa ? number_format($avgSgpa, 2) : '-' }}</h4>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('admin.exam-reports.results') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Exam</label>
              <select name="exam_id" class="form-select">
                <option value="">All Exams</option>
                @foreach($exams as $exam)
                <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Department</label>
              <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->name }}" {{ request('department') == $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Published Date</label>
              <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Status</label>
              <select name="result_status" class="form-select">
                <option value="">All</option>
                <option value="published" {{ request('result_status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="unpublished" {{ request('result_status') === 'unpublished' ? 'selected' : '' }}>Unpublished</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
              <a href="{{ route('admin.exam-reports.results') }}" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i></a>
            </div>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  <th>Exam</th>
                  <th>Session</th>
                  <th>SGPA</th>
                  <th>CGPA</th>
                  <th>Percentage</th>
                  <th>Credits</th>
                  <th>Result</th>
                  <th>Published</th>
                </tr>
              </thead>
              <tbody>
                @forelse($results as $result)
                <tr>
                  <td>{{ $loop->iteration + ($results->currentPage() - 1) * $results->perPage() }}</td>
                  <td>
                    @if($result->student && $result->student->studentMaster)
                    <div class="fw-semibold">{{ $result->student->studentMaster->first_name ?? '' }} {{ $result->student->studentMaster->last_name ?? '' }}</div>
                    <small class="text-muted">{{ $result->student->studentMaster->department ?? '' }}</small>
                    @else
                    <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>{{ $result->exam->name ?? 'N/A' }}</td>
                  <td>{{ $result->examSession->name ?? '-' }}</td>
                  <td class="fw-bold">{{ $result->sgpa ? number_format($result->sgpa, 2) : '-' }}</td>
                  <td class="fw-bold">{{ $result->cgpa ? number_format($result->cgpa, 2) : '-' }}</td>
                  <td>{{ $result->percentage ? number_format($result->percentage, 2) . '%' : '-' }}</td>
                  <td>{{ $result->earned_credits ?? '-' }}</td>
                  <td>
                    @if($result->result_status === 'pass')
                    <span class="badge bg-success">Pass</span>
                    @elseif($result->result_status === 'fail')
                    <span class="badge bg-danger">Fail</span>
                    @elseif($result->result_status === 'promoted_with_backlogs')
                    <span class="badge bg-warning text-dark">Promoted (Backlogs)</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($result->result_status ?? 'Pending') }}</span>
                    @endif
                  </td>
                  <td>
                    @if($result->is_published)
                    <span class="badge bg-success">Yes</span>
                    @else
                    <span class="badge bg-secondary">No</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="10" class="text-center py-4">
                    <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2 mb-0">No result records found</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if($results->hasPages())
        <div class="card-footer bg-white">
          {{ $results->appends(request()->query())->links() }}
        </div>
        @endif
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
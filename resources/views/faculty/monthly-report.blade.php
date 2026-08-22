@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a>
            </li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.workdiary') }}">Work Diary</a></li>
            <li class="breadcrumb-item active" aria-current="page">Monthly Report</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Header Section -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm bg-gradient-primary text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h2 class="mb-1 fw-bold text-white">Monthly Work Diary Report</h2>
                  <p class="mb-0 text-white-50">{{ $month->format('F Y') }}</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <a href="{{ route('faculty.workdiary.monthly.report', ['month' => $month->copy()->subMonth()->format('Y-m-d')]) }}"
                    class="btn btn-light btn-sm">
                    <i class="bx bx-chevron-left"></i> Previous
                  </a>
                  <input type="month" id="monthSelector" class="form-control form-control-sm"
                    value="{{ $month->format('Y-m') }}" style="width: 180px;">
                  <a href="{{ route('faculty.workdiary.monthly.report', ['month' => $month->copy()->addMonth()->format('Y-m-d')]) }}"
                    class="btn btn-light btn-sm">
                    Next <i class="bx bx-chevron-right"></i>
                  </a>
                  <a href="{{ route('faculty.workdiary.monthly.report.pdf', ['month' => $month->format('Y-m-d')]) }}" class="btn btn-success btn-sm">
                    <i class="bx bx-download"></i> Download PDF
                  </a>
                  <a href="{{ route('faculty.workdiary') }}" class="btn btn-outline-light btn-sm">
                    <i class="bx bx-arrow-back"></i> Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      @if(isset($categoryAnalytics) && $categoryAnalytics->count() > 0)
      <div class="row mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
              <h5 class="mb-0 fw-bold"><i class="bx bx-check-shield me-2"></i>Category Verification Analytics</h5>
            </div>
            <div class="card-body">
              <div class="row g-3 mb-3">
                <div class="col-md-2">
                  <div class="p-3 rounded border bg-light text-center">
                    <div class="text-muted small">Filled</div>
                    <div class="h4 mb-0 fw-bold">{{ $verificationSummary['filled_count'] ?? 0 }}</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="p-3 rounded border bg-light text-center">
                    <div class="text-muted small">Approved</div>
                    <div class="h4 mb-0 fw-bold text-success">{{ $verificationSummary['approved_count'] ?? 0 }}</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="p-3 rounded border bg-light text-center">
                    <div class="text-muted small">Pending</div>
                    <div class="h4 mb-0 fw-bold text-warning">{{ $verificationSummary['pending_count'] ?? 0 }}</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="p-3 rounded border bg-light text-center">
                    <div class="text-muted small">Approval %</div>
                    <div class="h4 mb-0 fw-bold">{{ $verificationSummary['approval_rate'] ?? 0 }}%</div>
                  </div>
                </div>
                {{-- API score cards kept for future enablement --}}
                {{--
                <div class="col-md-2">
                  <div class="p-3 rounded border bg-light text-center">
                    <div class="text-muted small">API Score (Filled)</div>
                    <div class="h4 mb-0 fw-bold text-primary">{{ number_format((float) ($verificationSummary['total_api_score'] ?? 0), 2) }}
              </div>
            </div>
          </div>
          <div class="col-md-2">
            <div class="p-3 rounded border bg-light text-center">
              <div class="text-muted small">API Score (Approved)</div>
              <div class="h4 mb-0 fw-bold text-success">{{ number_format((float) ($verificationSummary['approved_api_score'] ?? 0), 2) }}</div>
            </div>
          </div>
          --}}
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Category</th>
                <th class="text-center">Filled</th>
                <th class="text-center">Approved</th>
                <th class="text-center">Pending</th>
                <th>Pending With</th>
                {{-- API score columns kept for future enablement --}}
                {{--
                      <th class="text-end">API Score (Filled)</th>
                      <th class="text-end">API Score (Approved)</th>
                      --}}
              </tr>
            </thead>
            <tbody>
              @foreach($categoryAnalytics as $row)
              <tr>
                <td>{{ $row['category_title'] }}</td>
                <td class="text-center"><span class="badge bg-primary">{{ $row['filled_count'] }}</span></td>
                <td class="text-center"><span class="badge bg-success">{{ $row['approved_count'] }}</span></td>
                <td class="text-center"><span class="badge bg-warning text-dark">{{ $row['pending_count'] }}</span></td>
                <td>{{ $row['pending_with'] ?? '—' }}</td>
                {{--
                      <td class="text-end fw-semibold">{{ number_format((float) $row['total_api_score'], 2) }}</td>
                <td class="text-end fw-semibold text-success">{{ number_format((float) $row['approved_api_score'], 2) }}</td>
                --}}
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
</div>
@endif

<!-- Charts Row -->
<div class="row mb-4">
  <!-- Work Type Breakdown for Remedial Classes -->
  @if($extraCount > 0)
  <div class="col-lg-6 mb-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0">
        <h5 class="mb-0 fw-bold"><i class="bx bx-pie-chart-alt-2 me-2"></i>Remedial Classes Breakdown</h5>
      </div>
      <div class="card-body">
        @if($workTypeBreakdown->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Type of Work</th>
                <th class="text-center">Count</th>
                <th class="text-end">Percentage</th>
              </tr>
            </thead>
            <tbody>
              @foreach($workTypeBreakdown as $type => $count)
              <tr>
                <td>
                  <span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($type ?: 'Not Specified') }}</span>
                </td>
                <td class="text-center fw-bold">{{ $count }}</td>
                <td class="text-end">
                  <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar"
                      style="width: {{ ($count / $extraCount) * 100 }}%">
                      {{ round(($count / $extraCount) * 100, 1) }}%
                    </div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-4 text-muted">
          <i class="bx bx-info-circle" style="font-size: 48px;"></i>
          <p class="mt-2">No work type data available</p>
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif

  <!-- Methodology Breakdown -->
  @if($methodologyBreakdown->count() > 0)
  <div class="col-lg-6 mb-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0">
        <h5 class="mb-0 fw-bold"><i class="bx bx-bar-chart me-2"></i>Methodology Usage</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Methodology</th>
                <th class="text-center">Count</th>
                <th class="text-end">%</th>
              </tr>
            </thead>
            <tbody>
              @foreach($methodologyBreakdown->sortDesc()->take(10) as $methodology => $count)
              <tr>
                <td>{{ $methodology }}</td>
                <td class="text-center fw-bold">{{ $count }}</td>
                <td class="text-end">{{ round(($count / $totalClasses) * 100, 1) }}%</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>

<!-- Weekly Breakdown -->
@if($weeklyBreakdown->count() > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0">
        <h5 class="mb-0 fw-bold"><i class="bx bx-calendar-week me-2"></i>Weekly Breakdown</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="table-light">
              <tr>
                <th>Week</th>
                <th class="text-center">Total</th>
                <th class="text-center">Teaching</th>
                <th class="text-center">Extra</th>
                <th class="text-center">Substitution</th>
              </tr>
            </thead>
            <tbody>
              @foreach($weeklyBreakdown as $weekNum => $data)
              <tr>
                <td><strong>Week {{ $weekNum }}</strong></td>
                <td class="text-center"><span class="badge bg-primary">{{ $data['total'] }}</span></td>
                <td class="text-center"><span class="badge bg-success">{{ $data['regular'] }}</span></td>
                <td class="text-center"><span class="badge bg-info">{{ $data['extra'] }}</span></td>
                <td class="text-center"><span class="badge bg-warning">{{ $data['substitution'] }}</span></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Daily Entries Detail -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0">
        <h5 class="mb-0 fw-bold"><i class="bx bx-list-ul me-2"></i>Daily Work Entries</h5>
      </div>
      <div class="card-body">
        @if($dailyEntries->count() > 0)
        <div class="accordion" id="dailyEntriesAccordion">
          @foreach($dailyEntries as $date => $dayEntries)
          @php
          $dateObj = \Carbon\Carbon::parse($date);
          $collapseId = 'collapse' . $dateObj->format('Ymd');
          @endphp
          <div class="accordion-item border mb-2 rounded">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}">
                <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                  <div>
                    <strong>{{ $dateObj->format('l, F d, Y') }}</strong>
                  </div>
                  <div class="d-flex gap-2">
                    <span class="badge bg-primary">{{ $dayEntries->count() }} entries</span>
                    @if($dayEntries->where('class_type', 'regular')->count() > 0)
                    <span class="badge bg-success">{{ $dayEntries->where('class_type', 'regular')->count() }}
                      Teaching</span>
                    @endif
                    @if($dayEntries->where('class_type', 'extra')->count() > 0)
                    <span class="badge bg-info">{{ $dayEntries->where('class_type', 'extra')->count() }}
                      Extra</span>
                    @endif
                    @if($dayEntries->where('class_type', 'substitution')->count() > 0)
                    <span class="badge bg-warning">{{ $dayEntries->where('class_type', 'substitution')->count()
                            }} Sub</span>
                    @endif
                  </div>
                </div>
              </button>
            </h2>
            <div id="{{ $collapseId }}" class="accordion-collapse collapse"
              data-bs-parent="#dailyEntriesAccordion">
              <div class="accordion-body">
                <div class="table-responsive">
                  <table class="table table-sm table-hover">
                    <thead>
                      <tr>
                        <th style="width: 80px;">Period</th>
                        <th style="width: 120px;">Class Type</th>
                        <th style="width: 150px;">Component Title</th>
                        <th>Description</th>
                        <th style="width: 100px;">Document</th>
                        <th>Updated</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($dayEntries->sortBy('hour') as $entry)
                      <tr>
                        <td><strong>Hour {{ $entry->hour }}</strong></td>
                        <td>
                          @if($entry->class_type == 'regular')
                          <span class="badge bg-success  ">Teaching</span>
                          @elseif($entry->class_type == 'extra')
                          <span class="badge bg-info  ">Extra</span>
                          @elseif($entry->class_type == 'substitution')
                          <span class="badge bg-warning ">Substitution</span>
                          @else
                          <small class="text-muted">N/A ({{ $entry->class_type ?? 'null' }})</small>
                          @endif
                        </td>
                        <td>
                          @if($entry->methodology)
                          <small class="text-muted">{{ $entry->methodology }}</small>
                          @else
                          <small class="text-muted">—</small>
                          @endif
                        </td>
                        <td>{{ Str::limit($entry->description, 100) }}</td>
                        <td>
                          @if($entry->document_path)
                          <a href="{{ Storage::disk('s3')->url($entry->document_path) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-file"></i> View
                          </a>
                          @else
                          <small class="text-muted">—</small>
                          @endif
                        </td>
                        <td>{{ date('Y-m-d', strtotime($entry->updated_at)) }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="text-center py-5">
          <i class="bx bx-calendar-x text-muted" style="font-size: 64px;"></i>
          <h5 class="mt-3 text-muted">No Entries Found</h5>
          <p class="text-muted">No work diary entries recorded for {{ $month->format('F Y') }}</p>
          <a href="{{ route('faculty.workdiary') }}" class="btn btn-primary mt-2">
            <i class="bx bx-plus"></i> Add Entry
          </a>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

</div>
</main>
<!--end main wrapper-->
</div>

<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  @media print {

    .page-breadcrumb,
    .btn,
    .accordion-button,
    .sidebar {
      display: none !important;
    }

    .accordion-collapse {
      display: block !important;
    }

    .card {
      page-break-inside: avoid;
    }
  }
</style>

@include('includes.footer')

<script>
  $(document).ready(function() {
    // Month selector
    $('#monthSelector').on('change', function() {
      const selectedMonth = $(this).val() + '-01';
      window.location.href = '{{ route("faculty.workdiary.monthly.report") }}?month=' + selectedMonth;
    });
  });
</script>
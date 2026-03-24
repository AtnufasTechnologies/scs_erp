@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Subjects</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">My Subjects</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid mt-4">
      <!-- Header Card -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm border-0 bg-gradient-primary text-white">
            <div class="card-body py-4">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h4 class="mb-2 fw-bold text-white"><i class="fas fa-book-open me-2"></i>My Assigned Subjects</h4>
                  <p class="mb-0 text-white-50">View and track your teaching assignments organized by batch and semester</p>
                </div>
                <div class="text-end">
                  <div class="display-6 text-white fw-bold">{{ count($batchWiseSubjects) }}</div>
                  <small class="text-white-50">Batches</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subjects by Batch -->
      @forelse($batchWiseSubjects as $batchName => $subjects)
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
              <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold">
                  <i class="fas fa-users text-primary me-2"></i>
                  Batch: {{ $batchName }}
                </h5>
                <div class="d-flex gap-2">
                  <span class="badge bg-light-info text-info px-3 py-2">
                    <i class="fas fa-book me-1"></i>{{ count($subjects) }} Subject{{ count($subjects) > 1 ? 's' : '' }}
                  </span>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              @php
              // Group by semester
              $semesterGroups = $subjects->groupBy(function($item) {
              return $item->syllabus->semestermaster->id ?? 0;
              });
              @endphp

              @foreach($semesterGroups as $semesterId => $semesterSubjects)
              @php
              $firstSubject = $semesterSubjects->first();
              $semesterName = $firstSubject->syllabus->semestermaster->title ?? 'Unknown Semester';
              @endphp

              <!-- Semester Section -->
              <div class="semester-section border-bottom">
                <div class="semester-header bg-light px-4 py-3">
                  <h6 class="mb-0 text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>{{ $semesterName }}
                  </h6>
                </div>

                <!-- Courses in this Semester -->
                <div class="accordion accordion-flush" id="accordion{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}">
                  @foreach($semesterSubjects as $index => $subjectData)
                  @php
                  $syllabus = $subjectData->syllabus;
                  $subject = $syllabus->subject ?? null;
                  $courseMaster = $syllabus->courseLink->courseMaster ?? null;
                  $courseType = $courseMaster->coursetypemaster ?? null;
                  $syllabusUnits = $syllabus->syllabusunits ?? collect();

                  $completedUnits = $syllabusUnits->where('is_completed', 1)->count();
                  $totalUnits = $syllabusUnits->count();
                  $completionPercentage = $totalUnits > 0 ? round(($completedUnits / $totalUnits) * 100) : 0;
                  @endphp

                  <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="heading{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}">
                      <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}"
                        aria-expanded="false">
                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                          <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3 mb-2">
                              @if($courseType)
                              <span class="badge bg-primary">{{ $courseType->title }}</span>
                              @endif
                              <h6 class="mb-0 fw-bold">{{ $subject->title ?? 'N/A' }}</h6>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-muted">
                              <small>
                                <i class="fas fa-code me-1"></i>{{ $courseMaster->course_code ?? 'N/A' }}
                              </small>
                              <small class="text-muted">|</small>
                              <small>{{ $courseMaster->course_title ?? 'N/A' }}</small>
                            </div>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            @if($courseMaster)
                            <div class="text-center">
                              <div class="badge bg-light-warning text-warning px-3 py-2">
                                <i class="fas fa-star me-1"></i>{{ $courseMaster->credits ?? 0 }} Credits
                              </div>
                            </div>
                            <div class="text-center">
                              <small class="text-muted d-block">Marks</small>
                              <strong class="text-primary">{{ $courseMaster->internal ?? 0 }}</strong> /
                              <strong class="text-success">{{ $courseMaster->external ?? 0 }}</strong>
                            </div>
                            <div class="text-center">
                              <small class="text-muted d-block">Hours</small>
                              <strong>{{ $courseMaster->total_alloted_hours ?? 0 }}</strong>
                            </div>
                            @endif
                            @if($totalUnits > 0)
                            <div class="text-center">
                              <div class="progress" style="width: 80px; height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                  style="width: {{ $completionPercentage }}%"
                                  aria-valuenow="{{ $completionPercentage }}"
                                  aria-valuemin="0" aria-valuemax="100"></div>
                              </div>
                              <small class="text-muted">{{ $completedUnits }}/{{ $totalUnits }} units</small>
                            </div>
                            @endif
                          </div>
                        </div>
                      </button>
                    </h2>
                    <div id="collapse{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}{{ $index }}"
                      class="accordion-collapse collapse"
                      data-bs-parent="#accordion{{ str_replace(' ', '', $batchName) }}{{ $semesterId }}">
                      <div class="accordion-body px-4 py-4 bg-light">
                        @if($syllabusUnits->count() > 0)
                        <!-- Learning Units Table -->
                        <div class="card border-0 shadow-sm">
                          <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 text-primary">
                              <i class="fas fa-list-ul me-2"></i>Learning Units
                            </h6>
                          </div>
                          <div class="card-body p-0">
                            <div class="table-responsive">
                              <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                  <tr>
                                    <th style="width: 5%;" class="text-center">#</th>
                                    <th style="width: 50%;">Unit Title</th>
                                    <th style="width: 20%;" class="text-center">Taxonomy Level</th>
                                    <th style="width: 15%;" class="text-center">Status</th>
                                    <th style="width: 10%;" class="text-center">Action</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($syllabusUnits as $unit)
                                  <tr>
                                    <td class="text-center">
                                      <span class="badge bg-light text-dark">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                      <div class="d-flex align-items-center">
                                        <i class="fas fa-bookmark text-muted me-2"></i>
                                        <span class="fw-500">{{ $unit->csoSubunit->title ?? 'N/A' }}</span>
                                      </div>
                                    </td>
                                    <td class="text-center">
                                      @if($unit->csoSubunit && $unit->csoSubunit->taxomonylevel)
                                      <span class="badge bg-info-subtle text-dark px-3 py-2">
                                        <strong>{{ $unit->csoSubunit->taxomonylevel->shortname }}</strong> -
                                        {{ $unit->csoSubunit->taxomonylevel->fullname }}
                                      </span>
                                      @else
                                      <span class="text-muted">-</span>
                                      @endif
                                    </td>
                                    <td class="text-center">
                                      @if($unit->is_completed == 1)
                                      <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>Completed
                                      </span>
                                      @else
                                      <span class="badge bg-warning px-3 py-2">
                                        <i class="fas fa-clock me-1"></i>Pending
                                      </span>
                                      @endif
                                    </td>
                                    <td class="text-center">
                                      <a href="{{ route('faculty.toggle.subunitcompletion', $unit->id) }}"
                                        class="btn btn-sm {{ $unit->is_completed == 1 ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                        title="Toggle completion status"
                                        onclick="return confirm('Are you sure you want to toggle the completion status?')">
                                        <i class="fas fa-sync-alt"></i>
                                      </a>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                  <tr>
                                    <td colspan="5" class="text-end">
                                      <strong>Progress:</strong>
                                      <span class="badge bg-success ms-2">{{ $completedUnits }} Completed</span>
                                      <span class="badge bg-warning ms-1">{{ $totalUnits - $completedUnits }} Pending</span>
                                      <span class="badge bg-primary ms-1">{{ $completionPercentage }}% Complete</span>
                                    </td>
                                  </tr>
                                </tfoot>
                              </table>
                            </div>
                          </div>
                        </div>
                        @else
                        <div class="alert alert-info mb-0">
                          <i class="fas fa-info-circle me-2"></i>
                          Syllabus Not Added Yet for this subject. Please contact your department to update the syllabus details.
                        </div>
                        @endif
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
              <div class="mb-4">
                <i class="fas fa-book-open text-muted" style="font-size: 4rem;"></i>
              </div>
              <h5 class="text-muted">No Subjects Assigned</h5>
              <p class="text-muted mb-0">You don't have any subjects assigned yet. Please contact your department.</p>
            </div>
          </div>
        </div>
      </div>
      @endforelse
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<style>
  .bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .bg-light-info {
    background-color: #e7f3ff;
  }

  .bg-light-warning {
    background-color: #fff8e6;
  }

  .text-info {
    color: #0dcaf0 !important;
  }

  .text-warning {
    color: #ffc107 !important;
  }

  .fw-500 {
    font-weight: 500;
  }

  .semester-section:last-child {
    border-bottom: none !important;
  }

  .semester-header {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #212529;
  }

  .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0, 0, 0, .125);
  }

  .bg-info-subtle {
    background-color: #cfe2ff;
  }

  .table-hover tbody tr:hover {
    background-color: #f8f9fa;
  }

  @media (max-width: 768px) {
    .accordion-button {
      flex-direction: column;
      align-items: flex-start !important;
    }

    .accordion-button>div {
      width: 100%;
    }

    .accordion-button .d-flex.gap-3 {
      flex-wrap: wrap;
      margin-top: 10px;
    }
  }
</style>

<script>
  // Auto-collapse other accordions when one is opened
  document.addEventListener('DOMContentLoaded', function() {
    const accordions = document.querySelectorAll('.accordion-button');

    accordions.forEach(function(accordion) {
      accordion.addEventListener('click', function() {
        // Smooth scroll to the opened accordion
        setTimeout(() => {
          if (!this.classList.contains('collapsed')) {
            this.scrollIntoView({
              behavior: 'smooth',
              block: 'nearest'
            });
          }
        }, 350);
      });
    });
  });
</script>
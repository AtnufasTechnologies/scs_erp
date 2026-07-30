<?php

use App\Models\BatchMaster;
use Illuminate\Support\Str;

$batches = BatchMaster::all();
$subjectUsesShifts = $subjectUsesShifts ?? false;
$shiftTitleMap = collect($shiftOptions ?? [])->pluck('title', 'slug')->toArray();
$selectedProgramType = strtoupper((string) ($selectedProgramType ?? request('filter_program_type', 'UG')));
$selectedProgramType = $selectedProgramType === 'PG' ? 'PG' : 'UG';

?>
@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <h3 class="text-capitalize">Syllabus Manager - {{$data['slug']}}</h3>

  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="syllabusStatusToast" class="toast align-items-center border-0 text-bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
      <div class="d-flex">
        <div id="syllabusStatusToastBody" class="toast-body"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="alert alert-warning">
    <p>Note: We dont not need to define any COMBO1 or COMBO2 logic in here. Simply select the courses you will be offering from your department for the batch and semester and design its contents.
      You dont need to <b>duplicate</b> the same Course as COMBO1 or COMBO2. how ever it can be done if the syllabus is different.
    </p>

  </div>

  <div class="no-print mb-4">
    <div class=" ">
      <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-column flex-xl-row gap-3 gap-xl-4 align-items-xl-center justify-content-between">
          <div class="d-flex flex-wrap gap-2 gap-lg-3">
            <button class="cst-button" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addSyllabus">
              <span class="button-decor"></span>
              <div class="button-content">
                <div class="button__icon">
                  <i class="fa fa-plus-circle"></i>
                </div>
                <span class="button__text">Add New</span>
              </div>
            </button>

            <button class="btn btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#pdfBatchModal">
              <i class="fa fa-file-pdf"></i>
              <span>Download PDF</span>
            </button>

            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadRefPdfModal">
              <i class="fa fa-upload"></i>
              <span>Upload Ref PDF</span>
            </button>
          </div>

          <form action="{{ route('department.syllabus.manager', ['id' => $data['id'],'slug' => $data['slug']]) }}" method="get" class="w-100 w-xl-auto">
            <div class="row g-2 align-items-center">
              <div class="col-12 col-md-4">
                <select name="filter_batch" class="form-select">
                  <option value="" selected>--Select Batch--</option>
                  @foreach ($batches as $batch)
                  <option value="{{$batch->id}}" {{ request('filter_batch') == $batch->id ? 'selected' : '' }}>{{$batch->batch_name}}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-6 col-md-3">
                <select name="filter_program_type" class="form-select">
                  <option value="UG" {{ $selectedProgramType === 'UG' ? 'selected' : '' }}>UG</option>
                  <option value="PG" {{ $selectedProgramType === 'PG' ? 'selected' : '' }}>PG</option>
                </select>
              </div>

              @if($subjectUsesShifts)
              <div class="col-6 col-md-3">
                <select name="filter_shift" class="form-select">
                  <option value="" selected>--Shift--</option>
                  @foreach ($shiftOptions as $shiftOption)
                  <option value="{{ $shiftOption->slug }}" {{ request('filter_shift') === $shiftOption->slug ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                  @endforeach
                </select>
              </div>
              @endif

              <div class="col-12 col-md-2 d-grid">
                <input type="hidden" name="id" value="{{$data['id']}}">
                <input type="hidden" name="slug" value="{{$data['slug']}}">
                <button class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2">
                  <i class="fa fa-search"></i>
                  <span>Filter</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- PDF Batch Select Modal -->
  <div class="modal fade" id="pdfBatchModal" tabindex="-1" aria-labelledby="pdfBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="pdfBatchModalLabel"><i class="fa fa-file-pdf text-danger"></i> Generate Syllabus PDF</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('department.syllabus.download.pdf') }}" method="get" target="_blank">
          <input type="hidden" name="id" value="{{ $data['id'] }}">
          <input type="hidden" name="slug" value="{{ $data['slug'] }}">
          <div class="modal-body">
            <label for="pdf_filter_batch" class="form-label fw-semibold">Select Batch <span class="text-danger">*</span></label>
            <select name="filter_batch" id="pdf_filter_batch" class="form-select" required>
              <option value="">-- Select a Batch --</option>
              @foreach ($batches as $batch)
              <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
              @endforeach
            </select>
            <label for="pdf_filter_program_type" class="form-label fw-semibold mt-3">Program Type <span class="text-danger">*</span></label>
            <select name="filter_program_type" id="pdf_filter_program_type" class="form-select" required>
              <option value="UG" {{ $selectedProgramType === 'UG' ? 'selected' : '' }}>UG</option>
              <option value="PG" {{ $selectedProgramType === 'PG' ? 'selected' : '' }}>PG</option>
            </select>
            @if($subjectUsesShifts)
            <label for="pdf_filter_shift" class="form-label fw-semibold mt-3">Select Shift</label>
            <select name="filter_shift" id="pdf_filter_shift" class="form-select">
              <option value="">All</option>
              @foreach ($shiftOptions as $shiftOption)
              <option value="{{ $shiftOption->slug }}">{{ $shiftOption->title }}</option>
              @endforeach
            </select>
            @endif
            <small class="text-muted mt-2 d-block">Only the selected batch's syllabus will be included in the PDF.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger"><i class="fa fa-file-pdf"></i> Generate PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="addSyllabus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Design New Syllabus - {{$data['slug']}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('department.create.syllabus')}}" method="post">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="row">
                <div class="col-lg-4">
                  <label for="">Select Batch *</label>
                  <select name="batch" class="form-select">
                    <option value="" selected>--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                  @error('batch')
                  <small class="text-danger">{{$message}}</small>
                  @enderror

                </div>

                @if($subjectUsesShifts)
                <div class="col-lg-4">
                  <label for="">Shift *</label>
                  <select name="shift" id="syllabus_shift" class="form-select mb-3">
                    @foreach ($shiftOptions as $shiftOption)
                    <option value="{{ $shiftOption->slug }}" {{ $shiftOption->slug === 'common' ? 'selected' : '' }}>{{ $shiftOption->title }}</option>
                    @endforeach
                  </select>
                  @error('shift')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label class="form-label d-block">Create Mode</label>
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="create_all_shifts" name="create_all_shifts">
                    <label class="form-check-label" for="create_all_shifts">
                      Create this syllabus for all active shifts
                    </label>
                  </div>
                </div>
                @endif

                <div class="col-lg-4">
                  <label for="">Program Type *</label>
                  <select name="program_type" id="syllabus_program_type" class="form-select mb-3" required>
                    <option value="UG" {{ old('program_type', $selectedProgramType) === 'UG' ? 'selected' : '' }}>UG</option>
                    <option value="PG" {{ old('program_type', $selectedProgramType) === 'PG' ? 'selected' : '' }}>PG</option>
                  </select>
                  @error('program_type')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label for="">Select Semester *</label>
                  <select name="semester" class="form-select mb-3">
                    <option value="" selected>--Select--</option>
                    @foreach ($semesters as $sem)
                    <option value="{{$sem->id}}">{{$sem->title}}</option>
                    @endforeach
                  </select>
                  @error('semester')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label for="">Syllabus Status *</label>
                  <select name="status" class="form-select mb-3">
                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                  </select>
                  @error('status')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-12">
                  <label for="">Select a Course from your Course Master Bucket*</label>
                  <select name="co_id" id="course_objective" class="dselect-example mb-3">
                    <option value="" selected>--Select--</option>
                    @foreach ($cos as $item)
                    <option value="{{$item->course_master_id}}">
                      ({{$item->courseMaster->course_code ?? '-'}})
                      {{$item->courseMaster->course_title ?? '-'}}
                      - ({{$item->courseMaster->coursetypemaster->title ?? '-'}})
                    </option>
                    @endforeach
                  </select>
                  @error('co_id')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-12">
                  <label for="">Select CSO *</label>
                  <div id="cso_checkboxes" class="border rounded p-3 mb-2" style="max-height: 220px; overflow-y: auto;">
                    <p class="text-muted mb-0">--Select Course First--</p>
                  </div>
                  <small class="text-muted d-block mb-2">Select one or more CSOs to load their subunits.</small>
                  @error('cso_id')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-12">
                  <label for="">Select CSO Sub Unit(s)</label>
                  <div id="cso_subunit_checkboxes" class="border p-3 rounded mb-3" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-muted">--Select CSO First--</p>
                  </div>
                  @error('cso_subunit_map')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>
              </div>

              <input type="hidden" name="subject_id" value="{{$data['id']}}">

            </div>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Udemy-Style Syllabus Display -->
  <div class="mt-4">
    @forelse ($data['organized_syllabus'] ?? [] as $batchName => $programGroups)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-graduation-cap"></i> Batch: {{ $batchName }}</h5>
      </div>
      <div class="card-body p-0">
        @foreach ($programGroups as $programType => $semesterGroups)
        <div class="border-bottom">
          <div class="p-3" style="background:#f5f0ff;">
            <h6 class="mb-0"><i class="fa fa-layer-group"></i> Program Type: <span class="badge badge-success">{{ strtoupper((string) $programType) }}</span></h6>
          </div>

          @foreach ($semesterGroups as $semesterName => $courses)
          <div class="border-top">
            <div class="p-3 bg-light">
              <h6 class="mb-0"><i class="fa fa-calendar"></i> {{ $semesterName }}</h6>
            </div>
            <div class="accordion" id="accordion{{ Str::slug($batchName . $programType . $semesterName) }}">
              @foreach ($courses as $courseKey => $courseData)
              <?php
              $firstCso = $courseData['csos'][0] ?? null;
              $seatKey  = $firstCso ? "{$firstCso->batch_id}_{$firstCso->semester_id}_{$firstCso->co_id}" : null;
              $seatAlloc = $seatKey ? ($seatAllocations[$seatKey] ?? null) : null;
              $refPdf    = $seatKey ? ($syllabuspdfs[$seatKey] ?? null) : null;
              $shiftSlug = $firstCso->shift ?? 'common';
              $shiftTitle = $shiftTitleMap[$shiftSlug] ?? Str::title($shiftSlug);
              $statusSet = collect($courseData['csos'] ?? [])
                ->pluck('status')
                ->map(fn($status) => strtolower((string) ($status ?? 'draft')))
                ->unique()
                ->values();
              $isMixedStatus = $statusSet->count() > 1;
              $currentStatus = $isMixedStatus ? 'mixed' : ($statusSet->first() ?? 'draft');
              $statusLabel = $isMixedStatus ? 'Mixed' : ucfirst($currentStatus);
              $statusBadgeClass = $currentStatus === 'published' ? 'bg-success' : ($isMixedStatus ? 'bg-warning text-dark' : 'bg-secondary');

              $totalUnits = 0;
              $completedUnits = 0;
              foreach (($courseData['csos'] ?? []) as $courseSyllabus) {
                $subunits = collect($courseSyllabus->syllabusSubunits ?? []);
                $totalUnits += $subunits->count();
                $completedUnits += $subunits->where('is_completed', 1)->count();
              }
              $completionPercent = $totalUnits > 0 ? (int) round(($completedUnits / $totalUnits) * 100) : 0;
              $isLowCompletion = $totalUnits > 0 && $completionPercent < 50;
              $progressBarClass = $completionPercent >= 75 ? 'bg-success' : ($completionPercent >= 50 ? 'bg-warning' : 'bg-danger');
              ?>
              <div class="accordion-item {{ $isLowCompletion ? 'border border-danger' : '' }}">
                <div class="accordion-header d-flex align-items-center">
                  <button class="accordion-button collapsed flex-grow-1" type="button" data-bs-toggle="collapse"
                    data-bs-target="#course{{ Str::slug($batchName . $programType . $semesterName . $courseKey) }}"
                    aria-expanded="false">

                    <strong>{{ $courseData['course']->course_code ?? 'N/A' }}</strong>
                    <span class="badge badge-warning">{{ $courseData['course']->coursetypemaster->title ?? $courseData['course']->course_type ?? 'N/A' }}</span>
                    <span class="ms-2">{{ $courseData['course']->course_title ?? 'Unknown Course' }}</span>
                    @if($subjectUsesShifts)
                    <span class="badge bg-info text-dark ms-2">Shift: {{ $shiftTitle }}</span>
                    @endif
                    <span class="badge bg-dark ms-2">Program: {{ strtoupper((string) ($firstCso->program_type ?? $selectedProgramType)) }}</span>
                    <span class="badge js-course-status-badge {{ $statusBadgeClass }} ms-2">Status: {{ $statusLabel }}</span>
                    <span class="badge bg-light text-dark ms-2">{{ $completedUnits }}/{{ $totalUnits }} Units</span>
                    <span class="badge {{ $isLowCompletion ? 'bg-danger' : 'bg-success' }} ms-2">{{ $completionPercent }}% Complete</span>
                    <span class="badge bg-secondary ms-auto me-2">{{ $courseData['course']->credits ?? '0' }} Credits</span>
                    <!-- @if($seatAlloc)
                    <span class="badge {{ $seatAlloc->is_open ? 'bg-success' : 'bg-secondary' }} me-2" title="Total Seats">
                      <i class="fa fa-chair me-1"></i>{{ $seatAlloc->total_seats }} Seats
                      {{ $seatAlloc->is_open ? '(Open)' : '(Closed)' }}
                    </span>
                    @else
                    <span class="badge bg-warning text-dark me-2" title="No seat allocation set">
                      <i class="fa fa-chair me-1"></i>Seats N/A
                    </span>
                    @endif -->
                    @if($refPdf)
                    <a href="{{ Storage::disk('s3')->url($refPdf->file_path) }}" target="_blank"
                      class="badge bg-danger text-white me-2 text-decoration-none no-print"
                      title="View Reference PDF: {{ $refPdf->original_name }}" onclick="event.stopPropagation()">
                      <i class="fa fa-file-pdf me-1"></i>Ref PDF
                    </a>
                    @else
                    <span class="badge bg-light text-muted border me-2 no-print" title="No reference PDF uploaded">
                      <i class="fa fa-file-pdf me-1"></i>No PDF
                    </span>
                    @endif
                  </button>
                  @if($firstCso)
                  <form action="{{ route('department.syllabus.co.toggle-status', [$data['id'], $firstCso->batch_id, $firstCso->semester_id, $firstCso->co_id]) }}"
                    method="POST" class="no-print me-2 js-toggle-syllabus-status">
                    @csrf
                    <input type="hidden" name="shift" value="{{ $firstCso->shift ?? 'common' }}">
                    <input type="hidden" name="program_type" value="{{ strtoupper((string) ($firstCso->program_type ?? $selectedProgramType)) }}">
                    <button type="submit" class="btn btn-sm js-toggle-status-btn {{ $currentStatus === 'published' ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                      title="Toggle Draft/Published">
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </form>

                  <form action="{{ route('department.syllabus.co.delete', [$data['id'], $firstCso->batch_id, $firstCso->semester_id, $firstCso->co_id]) }}"
                    method="POST" class="no-print me-2 js-delete-course-form"
                    onsubmit="return confirm('Remove this course and all its CSOs & subunits from this batch/semester?')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="program_type" value="{{ strtoupper((string) ($firstCso->program_type ?? $selectedProgramType)) }}">
                    <button type="submit" class="btn btn-sm btn-danger" title="Remove course from this batch & semester">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                  @endif
                </div>
                <div id="course{{ Str::slug($batchName . $programType . $semesterName . $courseKey) }}"
                  class="accordion-collapse collapse"
                  data-bs-parent="#accordion{{ Str::slug($batchName . $programType . $semesterName) }}">
                  <div class="accordion-body">
                    <!-- Course Completion Analytics -->
                    <div class="mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Course Completion Progress</small>
                        <small class="fw-semibold">{{ $completionPercent }}%</small>
                      </div>
                      <progress class="w-100" value="{{ $completionPercent }}" max="100"></progress>
                      <small class="text-muted">Completed {{ $completedUnits }} of {{ $totalUnits }} learning units</small>
                    </div>

                    @if($isLowCompletion)
                    <div class="alert alert-danger py-2" role="alert">
                      <i class="fa fa-exclamation-triangle me-1"></i>
                      This course is below 50% completion. Consider prioritizing pending units.
                    </div>
                    @endif

                    <!-- Reference PDF Panel -->
                    <div class="alert {{ $refPdf ? 'alert-danger' : 'alert-light border' }} d-flex align-items-center justify-content-between py-2 mb-3 no-print" role="alert">
                      <div>
                        <i class="fa fa-file-pdf me-2 text-danger"></i>
                        @if($refPdf)
                        <strong>Reference PDF:</strong>
                        <a href="{{ Storage::disk('s3')->url($refPdf->file_path) }}" target="_blank" class="ms-1">
                          {{ $refPdf->original_name }}
                        </a>
                        <small class="text-muted ms-2">uploaded {{ $refPdf->updated_at->diffForHumans() }}</small>
                        @else
                        <span class="text-muted">No reference PDF uploaded for this course.</span>
                        @endif
                      </div>
                      <div class="d-flex gap-2">
                        @if($firstCso)
                        <!-- Replace / Upload -->
                        <button class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#uploadRefPdfModal"
                          data-batch="{{ $firstCso->batch_id }}"
                          data-semester="{{ $firstCso->semester_id }}"
                          data-course="{{ $firstCso->co_id }}"
                          onclick="prefillPdfModal(this)">
                          <i class="fa fa-upload me-1"></i>{{ $refPdf ? 'Replace' : 'Upload' }}
                        </button>
                        @endif
                        @if($refPdf)
                        <form action="{{ route('department.syllabus.pdf.destroy', $refPdf->id) }}" method="POST"
                          onsubmit="return confirm('Remove this reference PDF?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash"></i>
                          </button>
                        </form>
                        @endif
                      </div>
                    </div>

                    <!-- Course Details -->
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <small class="text-muted">Internal Marks:</small>
                        <strong>{{ $courseData['course']->internal ?? '-' }}</strong>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">External Marks:</small>
                        <strong>{{ $courseData['course']->external ?? '-' }}</strong>
                      </div>
                      <div class="col-md-4">
                        <small class="text-muted">Total Hours:</small>
                        <strong>{{ $courseData['course']->total_alloted_hours ?? '-' }}</strong>
                      </div>
                    </div>

                    <!-- CSOs -->
                    <h6 class="border-bottom pb-2 mb-3">Course Specific Objectives (CSOs)</h6>
                    @foreach ($courseData['csos'] as $syllabus)
                    <div class="card mb-3">
                      <div class="card-header bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                          <span>
                            <strong>{{ $syllabus->cso->title ?? 'N/A' }}</strong>
                            @if($subjectUsesShifts)
                            @php
                            $csoShiftSlug = $syllabus->shift ?? 'common';
                            $csoShiftTitle = $shiftTitleMap[$csoShiftSlug] ?? Str::title($csoShiftSlug);
                            @endphp
                            <span class="badge bg-dark ms-1">{{ $csoShiftTitle }}</span>
                            @endif
                          </span>
                          <span class="badge bg-light text-dark">{{ $syllabus->cso->lectures_needed ?? '0' }} Lectures</span>
                        </div>
                      </div>
                      <div class="card-body">
                        <h6 class="mb-3">Learning Units</h6>
                        <div class="list-group">
                          @foreach ($syllabus->syllabusSubunits ?? [] as $syllabusSubunit)
                          <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                              <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $syllabusSubunit->csoSubunit->title ?? 'N/A' }}</h6>
                                <small class="text-muted">
                                  <span class="badge bg-primary">
                                    @foreach ($syllabusSubunit->csoSubunit->taxonomies ?? [] as $taxonomy)
                                    {{ $taxonomy->rbtmaster->shortname ?? '-' }} - {{ $taxonomy->rbtmaster->fullname ?? '-' }}
                                    @endforeach
                                  </span>
                                </small>
                              </div>
                              <div class="d-flex align-items-center gap-2">
                                @if ($syllabusSubunit->is_completed == 1)
                                <span class="badge bg-success" title="Completed">
                                  <i class="fa fa-check-circle"></i> Completed
                                </span>
                                @else
                                <span class="badge bg-warning" title="Pending">
                                  <i class="fa fa-clock"></i> Pending
                                </span>
                                @endif
                                <form action="{{ route('department.syllabus.subunit.delete', $syllabusSubunit->id) }}" method="POST" class="no-print js-delete-subunit-form"
                                  onsubmit="return confirm('Remove this subunit from syllabus?')">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                  </button>
                                </form>
                              </div>
                            </div>
                          </div>
                          @endforeach
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
        @endforeach
      </div>
    </div>
    @empty
    <div class="alert alert-info text-center" role="alert">
      <i class="fa fa-info-circle"></i> No syllabus data available. Click "Add New" to create your first syllabus.
    </div>
    @endforelse
  </div>
</div>




</div>

<!-- ═══════════════════════════════════════════════════════
     Upload Reference PDF Modal
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="uploadRefPdfModal" tabindex="-1" aria-labelledby="uploadRefPdfModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadRefPdfModalLabel">
          <i class="fa fa-upload text-primary me-2"></i>Upload Reference PDF Syllabus
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('department.syllabus.pdf.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
            <select name="batch_id" id="refPdfBatch" class="form-select" required>
              <option value="">— Select Batch —</option>
              @foreach($batches as $b)
              <option value="{{ $b->id }}">{{ $b->batch_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
            <select name="semester_id" id="refPdfSemester" class="form-select" required>
              <option value="">— Select Semester —</option>
              @foreach($semesters as $s)
              <option value="{{ $s->id }}">{{ $s->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
            <select name="course_master_id" id="refPdfCourse" class="form-select" required>
              <option value="">— Select Course —</option>
              @foreach($cos as $item)
              <option value="{{ $item->course_master_id }}">
                ({{ $item->courseMaster->course_code ?? '—' }}) {{ $item->courseMaster->course_title ?? '—' }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">PDF File <span class="text-danger">*</span></label>
            <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
            <small class="text-muted">Max size: 10 MB. Uploading a new file replaces any existing PDF for the same batch/semester/course.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-upload me-1"></i>Upload
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')

<!-- CSO AJAX Script -->
<script>
  function showSyllabusStatusToast(status, message) {
    const toastElement = document.getElementById('syllabusStatusToast');
    const toastBody = document.getElementById('syllabusStatusToastBody');
    if (!toastElement || !toastBody || typeof bootstrap === 'undefined') {
      return;
    }

    toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-info');
    toastElement.classList.add(status === 'success' ? 'text-bg-success' : (status === 'info' ? 'text-bg-info' : 'text-bg-danger'));
    toastBody.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toastElement).show();
  }

  function updateStatusBadgeAndButton(form, nextStatus) {
    const header = form.closest('.accordion-header');
    if (!header) return;

    const badge = header.querySelector('.js-course-status-badge');
    if (badge) {
      badge.classList.remove('bg-success', 'bg-secondary', 'bg-warning', 'text-dark');
      if (nextStatus === 'published') {
        badge.classList.add('bg-success');
      } else {
        badge.classList.add('bg-secondary');
      }
      badge.textContent = 'Status: ' + (nextStatus === 'published' ? 'Published' : 'Draft');
    }

    const button = form.querySelector('.js-toggle-status-btn');
    if (button) {
      button.classList.remove('btn-outline-secondary', 'btn-outline-success');
      button.classList.add(nextStatus === 'published' ? 'btn-outline-secondary' : 'btn-outline-success');
    }
  }

  // Pre-fill the Reference PDF upload modal when triggered from a course row
  function prefillPdfModal(btn) {
    document.getElementById('refPdfBatch').value = btn.dataset.batch || '';
    document.getElementById('refPdfSemester').value = btn.dataset.semester || '';
    document.getElementById('refPdfCourse').value = btn.dataset.course || '';
  }

  document.addEventListener('DOMContentLoaded', function() {
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    document.querySelectorAll('form.js-toggle-syllabus-status').forEach(function(form) {
      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (!confirm('Toggle syllabus status for this course?')) {
          return;
        }

        const button = form.querySelector('button[type="submit"]');
        if (button) {
          button.disabled = true;
        }

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: new FormData(form)
          });

          const payload = await response.json().catch(function() {
            return {};
          });

          if (!response.ok || !payload.status) {
            throw new Error(payload.message || 'Unable to toggle syllabus status.');
          }

          updateStatusBadgeAndButton(form, String(payload.next_status || '').toLowerCase());
          showSyllabusStatusToast('success', payload.message || 'Syllabus status updated.');
        } catch (error) {
          showSyllabusStatusToast('error', error.message || 'Unable to toggle syllabus status.');
        } finally {
          if (button) {
            button.disabled = false;
          }
        }
      });
    });

    document.querySelectorAll('form.js-delete-course-form').forEach(function(form) {
      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        if (button) {
          button.disabled = true;
        }

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: new FormData(form)
          });

          const payload = await response.json().catch(function() {
            return {};
          });

          if (!response.ok || !payload.status) {
            throw new Error(payload.message || 'Unable to remove course.');
          }

          const accordionItem = form.closest('.accordion-item');
          if (accordionItem) {
            accordionItem.remove();
          }

          showSyllabusStatusToast('success', payload.message || 'Course removed successfully.');
        } catch (error) {
          showSyllabusStatusToast('error', error.message || 'Unable to remove course.');
        } finally {
          if (button) {
            button.disabled = false;
          }
        }
      });
    });

    document.querySelectorAll('form.js-delete-subunit-form').forEach(function(form) {
      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        if (button) {
          button.disabled = true;
        }

        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: new FormData(form)
          });

          const payload = await response.json().catch(function() {
            return {};
          });

          if (!response.ok || !payload.status) {
            throw new Error(payload.message || 'Unable to remove subunit.');
          }

          const subunitItem = form.closest('.list-group-item');
          if (subunitItem) {
            subunitItem.remove();
          }

          showSyllabusStatusToast('success', payload.message || 'Subunit removed successfully.');
        } catch (error) {
          showSyllabusStatusToast('error', error.message || 'Unable to remove subunit.');
        } finally {
          if (button) {
            button.disabled = false;
          }
        }
      });
    });

    const courseSelect = document.getElementById('course_objective');
    const csoCheckboxes = document.getElementById('cso_checkboxes');
    const csoSubunitCheckboxes = document.getElementById('cso_subunit_checkboxes');
    let csoCache = [];

    function renderSubunitsForSelectedCsos() {
      csoSubunitCheckboxes.innerHTML = '';

      const selectedCsoIds = Array.from(
          csoCheckboxes.querySelectorAll('input[name="cso_id[]"]:checked')
        )
        .map(input => input.value)
        .filter(value => value !== '');

      if (selectedCsoIds.length === 0) {
        csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';
        return;
      }

      let hasAnySubunit = false;

      selectedCsoIds.forEach(function(csoId) {
        const cso = csoCache.find(item => String(item.id) === String(csoId));
        if (!cso) {
          return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3 p-2 border rounded bg-light';

        const heading = document.createElement('div');
        heading.className = 'd-flex justify-content-between align-items-center mb-2';

        const title = document.createElement('strong');
        title.textContent = cso.title || 'Untitled CSO';
        heading.appendChild(title);

        const selectAllWrap = document.createElement('div');
        selectAllWrap.className = 'form-check';
        const selectAllId = `select_all_subunits_${cso.id}`;
        selectAllWrap.innerHTML = `
          <input class="form-check-input" type="checkbox" id="${selectAllId}">
          <label class="form-check-label" for="${selectAllId}">Select All</label>
        `;
        heading.appendChild(selectAllWrap);
        wrapper.appendChild(heading);

        const subunits = Array.isArray(cso.csosubunits) ? cso.csosubunits : [];
        if (subunits.length === 0) {
          const empty = document.createElement('p');
          empty.className = 'text-muted mb-0';
          empty.textContent = 'No sub units found for this CSO';
          wrapper.appendChild(empty);
          csoSubunitCheckboxes.appendChild(wrapper);
          return;
        }

        hasAnySubunit = true;
        const list = document.createElement('div');

        subunits.forEach(function(subunit) {
          const row = document.createElement('div');
          row.className = 'form-check mb-1';

          const inputId = `cso_${cso.id}_subunit_${subunit.id}`;
          const taxonomyLabel = subunit.taxomonylevel?.rbtmaster?.fullname ?? 'N/A';

          row.innerHTML = `
            <input class="form-check-input cso-subunit-checkbox cso-${cso.id}-subunit" type="checkbox" name="cso_subunit_map[${cso.id}][]" value="${subunit.id}" id="${inputId}">
            <label class="form-check-label" for="${inputId}">${subunit.title} (${taxonomyLabel})</label>
          `;

          list.appendChild(row);
        });

        wrapper.appendChild(list);
        csoSubunitCheckboxes.appendChild(wrapper);

        const selectAllCheckbox = wrapper.querySelector(`#${selectAllId}`);
        const subunitCheckboxes = wrapper.querySelectorAll(`.cso-${cso.id}-subunit`);

        selectAllCheckbox.addEventListener('change', function() {
          subunitCheckboxes.forEach(cb => cb.checked = this.checked);
        });

        subunitCheckboxes.forEach(function(checkbox) {
          checkbox.addEventListener('change', function() {
            const allChecked = Array.from(subunitCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
          });
        });
      });

      if (!hasAnySubunit) {
        csoSubunitCheckboxes.innerHTML = '<p class="text-muted">No sub units found for selected CSO(s)</p>';
      }
    }

    if (courseSelect) {
      courseSelect.addEventListener('change', function() {
        const courseId = this.value;

        // Reset CSO and sub unit checkboxes
        csoCache = [];
        csoCheckboxes.innerHTML = '<p class="text-muted mb-0">--Select Course First--</p>';
        csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';

        if (!courseId) {
          return;
        }

        // Show loading state
        csoCheckboxes.innerHTML = '<p class="text-muted mb-0">Loading CSOs...</p>';

        // Fetch CSOs for the selected course
        const shiftSelect = document.getElementById('syllabus_shift');
        const selectedShift = shiftSelect ? shiftSelect.value : '';
        const programTypeSelect = document.getElementById('syllabus_program_type');
        const selectedProgramType = programTypeSelect ? programTypeSelect.value : 'UG';
        const query = new URLSearchParams();
        query.append('subject_id', '{{ $data["id"] }}');
        if (selectedShift) query.append('shift', selectedShift);
        if (selectedProgramType) query.append('program_type', selectedProgramType);
        const queryString = query.toString();
        const endpoint = `/erp/deptartment/course/${courseId}/cso-list${queryString ? `?${queryString}` : ''}`;

        fetch(endpoint)
          .then(response => response.json())
          .then(data => {
            csoCache = Array.isArray(data) ? data : [];

            csoCheckboxes.innerHTML = '';

            if (csoCache.length > 0) {
              csoCache.forEach(function(cso) {
                const row = document.createElement('div');
                row.className = 'form-check mb-2';

                const inputId = `cso_option_${cso.id}`;
                row.innerHTML = `
                  <input class="form-check-input" type="checkbox" name="cso_id[]" value="${cso.id}" id="${inputId}">
                  <label class="form-check-label" for="${inputId}">${cso.title} (Lectures: ${cso.lectures_needed})</label>
                `;

                csoCheckboxes.appendChild(row);
              });
            } else {
              csoCheckboxes.innerHTML = '<p class="text-muted mb-0">No CSOs found for this course</p>';
            }
          })
          .catch(() => {
            csoCheckboxes.innerHTML = '<p class="text-danger mb-0">Failed to load CSOs</p>';
            csoCache = [];
          });
      });
    }

    if (csoCheckboxes) {
      csoCheckboxes.addEventListener('change', function(event) {
        if (!event.target.matches('input[name="cso_id[]"]')) {
          return;
        }
        renderSubunitsForSelectedCsos();
      });
    }

    const shiftSelect = document.getElementById('syllabus_shift');
    const programTypeSelect = document.getElementById('syllabus_program_type');
    if (shiftSelect && courseSelect) {
      shiftSelect.addEventListener('change', function() {
        if (courseSelect.value) {
          courseSelect.dispatchEvent(new Event('change'));
        }
      });
    }

    if (programTypeSelect && courseSelect) {
      programTypeSelect.addEventListener('change', function() {
        if (courseSelect.value) {
          courseSelect.dispatchEvent(new Event('change'));
        }
      });
    }
  });
</script>

<!-- Print Styles -->
<style>
  @media print {

    /* Hide navigation, sidebar, and buttons */
    .no-print,
    .sidebar,
    nav,
    .navbar,
    .btn,
    button,
    .modal,
    .form-control,
    .input-group,
    form {
      display: none !important;
    }

    /* Expand all accordions for printing */
    .accordion-collapse {
      display: block !important;
      height: auto !important;
    }

    .accordion-button {
      display: block !important;
    }

    .accordion-button::after {
      display: none !important;
    }

    /* Adjust layout for print */
    .main-content {
      margin: 0 !important;
      padding: 20px !important;
      width: 100% !important;
    }

    .card {
      page-break-inside: avoid;
      border: 1px solid #ddd !important;
      box-shadow: none !important;
    }

    .card-header {
      background-color: #f8f9fa !important;
      color: #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Ensure badges are visible */
    .badge {
      border: 1px solid #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Page breaks */
    .accordion-item {
      page-break-inside: avoid;
    }

    /* Remove unnecessary spacing */
    .mt-4,
    .mb-4 {
      margin-top: 10px !important;
      margin-bottom: 10px !important;
    }
  }
</style>
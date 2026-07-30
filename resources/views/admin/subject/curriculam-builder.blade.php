<?php

use App\Models\AcademicPathwayMaster;
use App\Models\DegreeTrackMaster;
use App\Models\ProgramCourseMaster;
use App\Models\Semester;
use App\Models\SpecializationMaster;

$semesters = Semester::all();
$papers = ProgramCourseMaster::all();
$pathways = AcademicPathwayMaster::orderBy('name')->get();
$degreeTracks = DegreeTrackMaster::orderBy('name')->get();
$pathwayNameMap = $pathways->pluck('name', 'id');
$trackNameMap = $degreeTracks->pluck('name', 'id');
$publishedCoursesBySemester = collect($publishedCoursesBySemester ?? []);
$publishedCourseOptions = $publishedCoursesBySemester
  ->flatMap(fn($courses) => collect($courses))
  ->unique('id')
  ->sortBy(fn($course) => ($course['course_code'] ?? '') . ' ' . ($course['course_title'] ?? ''))
  ->values();
$selectedSemester = (int) ($selectedSemester ?? 0);
$generatedCourses = collect($generatedCourses ?? []);
$pageData = $data ?? null;
$combo1DepartmentId = (int) (($comboBoundary['combo1'] ?? null) ?? optional(optional($pageData)->combomap)->combo_id_1 ?? optional($pageData)->subject_id ?? 0);
$combo2DepartmentId = (int) (($comboBoundary['combo2'] ?? null) ?? optional(optional($pageData)->combomap)->combo_id_2 ?? 0);
$isSingleMajorCourse = $combo1DepartmentId > 0 && $combo1DepartmentId === $combo2DepartmentId;
$combinationSpecializationIds = collect(optional($pageData)->specialization_ids ?? [])->map(fn($id) => (int) $id)->filter()->unique()->values();
$availableSpecializations = collect();
if ($isSingleMajorCourse) {
  $availableSpecializations = SpecializationMaster::query()
    ->where('subject_id', (int) (optional($pageData)->subject_id ?? 0))
    ->where('is_active', 1)
    ->when($combinationSpecializationIds->isNotEmpty(), fn($query) => $query->whereIn('id', $combinationSpecializationIds->all()))
    ->orderBy('name')
    ->get(['id', 'name']);
}
$availableSpecializationsPayload = $availableSpecializations->map(function ($specialization) {
  return [
    'id' => (int) $specialization->id,
    'name' => (string) $specialization->name,
  ];
})->values();
$availableSpecializationsNameMap = $availableSpecializations->pluck('name', 'id');
$typeLabelMap = [
  'AUTO' => 'Compulsory',
  'STUDENT_CHOICE' => 'Elective',
  'DEPARTMENT_CHOICE' => 'Optional',
];
?>
@include('includes.header')
@include('includes.dept-sidebar')
<style>
  :root {
    --swc-bg: #f4f7fb;
    --swc-ink: #102a43;
    --swc-muted: #486581;
    --swc-primary: #0f766e;
    --swc-primary-soft: #dff8f2;
    --swc-accent: #7c3aed;
    --swc-card: #ffffff;
    --swc-border: #d9e2ec;
    --swc-shadow: 0 12px 28px rgba(16, 42, 67, 0.08);
  }

  .swc-page {
    background: radial-gradient(circle at 10% 0%, #e6f4ff 0%, #f4f7fb 45%, #f4f7fb 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
  }

  .swc-headline {
    color: var(--swc-ink);
    font-weight: 800;
    letter-spacing: 0.04rem;
    margin-bottom: 0.5rem;
  }

  .swc-subtitle {
    color: var(--swc-muted);
    margin-bottom: 1.2rem;
  }

  .swc-info {
    background: linear-gradient(135deg, #e8fdf8 0%, #d7f4ff 100%);
    border: 1px solid #cdeee6;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    box-shadow: var(--swc-shadow);
  }

  .swc-form-card,
  .swc-list-card {
    background: var(--swc-card);
    border: 1px solid var(--swc-border);
    border-radius: 14px;
    box-shadow: var(--swc-shadow);
  }

  .swc-form-card {
    padding: 1rem;
    margin-top: 1rem;
  }

  .swc-list-card {
    margin-top: 1rem;
    overflow: hidden;
  }

  .swc-list-card .card-header {
    background: linear-gradient(135deg, #f5fbff 0%, #eef8ff 100%);
    border-bottom: 1px solid var(--swc-border);
    color: var(--swc-ink);
    font-weight: 700;
  }

  .swc-summary {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
    font-size: 0.85rem;
  }

  .swc-pill {
    border-radius: 999px;
    padding: 0.3rem 0.65rem;
    font-weight: 700;
  }

  .swc-pill-total {
    background: #e3f2fd;
    color: #1e3a8a;
  }

  .swc-pill-auto {
    background: #dcfce7;
    color: #166534;
  }

  .swc-pill-student-choice {
    background: #ede9fe;
    color: #5b21b6;
  }

  .swc-pill-dept-choice {
    background: #fef3c7;
    color: #92400e;
  }

  .swc-type-badge {
    font-size: 0.78rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    font-weight: 700;
    display: inline-block;
  }

  .swc-type-auto {
    background: #dcfce7;
    color: #166534;
  }

  .swc-type-student-choice {
    background: #ede9fe;
    color: #5b21b6;
  }

  .swc-type-dept-choice {
    background: #fef3c7;
    color: #92400e;
  }

  .swc-empty {
    border: 1px dashed #b8c7d9;
    border-radius: 14px;
    background: #f8fbff;
    color: #486581;
    text-align: center;
    padding: 2rem 1rem;
    margin-top: 1rem;
  }

  .swc-drag-handle {
    cursor: grab;
    color: #64748b;
    font-size: 1rem;
  }

  .swc-dragging {
    opacity: 0.6;
  }

  .swc-order-status {
    font-size: 0.8rem;
    color: #0f766e;
    margin-left: 0.4rem;
  }

  .toggle-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
  }

  /* Hide the default checkbox */
  .toggle-input {
    display: none;
  }

  /* The track of the switch */
  .toggle-track {
    position: relative;
    width: 240px;
    height: 50px;
    background-color: #e2e8f0;
    border-radius: 25px;
    transition: background-color 0.3s ease;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* The sliding thumb/button */
  .toggle-thumb {
    position: absolute;
    top: 4px;
    left: 4px;
    width: 116px;
    height: 42px;
    background-color: #ffffff;
    border-radius: 21px;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 2;
  }

  /* Text labels inside the track */
  .toggle-labels {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    box-sizing: border-box;
    font-weight: 600;
    font-size: 14px;
    z-index: 1;
  }

  .label-compulsory {
    color: #10b981;
    /* Red color for Compulsory */
    transition: color 0.3s ease;
    width: 90px;
    text-align: center;
  }

  .label-elective {
    color: #94a3b8;
    /* Muted color when inactive */
    transition: color 0.3s ease;
    width: 90px;
    text-align: center;
  }

  /* --- Checked / ON State (Elective) --- */

  /* Move the thumb to the right */
  .toggle-input:checked+.toggle-track .toggle-thumb {
    transform: translateX(116px);
  }

  /* Change background track color when checked if desired */
  .toggle-input:checked+.toggle-track {
    background-color: #f1f5f9;
  }

  /* Fade out the compulsory label colors */
  .toggle-input:checked+.toggle-track .label-compulsory {
    color: #94a3b8;
  }

  /* Highlight the elective label */
  .toggle-input:checked+.toggle-track .label-elective {
    color: #10b981;
  }

  @media (max-width: 991px) {
    .swc-form-card .row>div {
      margin-bottom: 0.75rem;
    }

    .swc-form-card .row>div:last-child {
      margin-bottom: 0;
    }
  }
</style>
<div class="main-content swc-page">
  <div class="container-fluid">
    <h3 class="swc-headline text-uppercase"> <span class="text-danger">Curriculum Builder</span> Engine</h3>
    <p class="swc-subtitle">This is the <strong>CORE of Academic System</strong> Select Semester, click Generate, then choose offered courses and mark each as Compulsory or Elective.</p>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
      <div id="curriculumToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500" data-success-message="{{ session('success') }}" data-error-message="{{ session('error') }}">
        <div class="d-flex">
          <div id="curriculumToastBody" class="toast-body"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-7">
        <div class="swc-info">
          <h5 class="mb-1">Batch: {{$data->batchmaster->batch_name}} | {{$data->studentprograminfo->code}} - {{$data->studentprograminfo->name}} <span class="badge badge-danger">{{$data->program_type }}</span></h5>
          <p class="mb-0">
            <i class="fa fa-link"></i> Connected:
            <span class="badge badge-warning">{{$data->combomap->combo1->id ?? '1 Not Set'}} - {{$data->combomap->combo1->title ?? '1 Not Set'}}</span>
            <span class="badge badge-warning">{{$data->combomap->combo2->id ?? '1 Not Set'}} - {{$data->combomap->combo2->title ?? '2 Not Set'}}</span>
          </p>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="">
          <!-- <form action="{{route('combo.course.fetching')}}" method="get"> -->
          <form id="curriculumGenerateForm" action="{{route('combo.course.fetching')}}" method="get">
            <div class="row align-items-end">
              <div class="col-lg-6">
                <label class="form-label">Semester</label>
                <select name="semester" id="curriculumSemesterSelect" class="form-control" required>
                  <option value=""> --Select Semester-- </option>
                  @foreach ($semesters as $semester)
                  <option value="{{$semester->id}}" {{$selectedSemester === (int) $semester->id ? 'selected' : ''}}>{{$semester->title}}</option>
                  @endforeach
                </select>
                <input type="hidden" name="student_program_id" value="{{$data->id}}">
                <input type="hidden" name="combo1" value="{{$combo1DepartmentId}}">
                <input type="hidden" name="combo2" value="{{$combo2DepartmentId}}">
                <input type="hidden" name="batch" value="{{$data->batchmaster->id}}">

              </div>
              <div class="col-lg-2">
                <button type="submit" class="btn btn-primary w-100">Generate</button>
              </div>
              <div class="col-lg-4"></div>
            </div>
          </form>
        </div>

      </div>
    </div>


    <div class="swc-form-card">
      <form id="curriculumMappingForm" action="{{route('store.curriculam.mapping')}}" method="post">
        @csrf
        <input type="hidden" name="semester" id="mappingSemesterInput" value="{{$selectedSemester}}">
        <input type="hidden" name="id" value="{{$data->id}}">
        <input type="hidden" name="batch" value="{{$data->batchmaster->id}}">
        <div id="curriculumMappingFeedback" class="d-none"></div>
        <div class="row align-items-center">
          <div class="col-lg-2">
            <select name="academic_pathway_id" class="form-control">
              <option value="">All Pathways</option>
              @foreach($pathways as $pathway)
              <option value="{{$pathway->id}}">{{$pathway->name}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-2">
            <select name="degree_track_id" class="form-control">
              <option value="">All Tracks</option>
              @foreach($degreeTracks as $track)
              <option value="{{$track->id}}">{{$track->name}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3">
            <button type="submit" class="btn btn-success w-100">Save Selected Courses</button>
          </div>

          <div class="col-12 mt-3">
            <h6 class="mb-2">
              All Offered Courses (Published Syllabus) - Semester <span id="generatedSemesterLabel">{{ $selectedSemester > 0 ? $selectedSemester : '-' }}</span>
              | Program Type <span id="generatedProgramTypeLabel">{{ strtoupper((string) ($data->program_type ?? 'UG')) === 'PG' ? 'PG' : 'UG' }}</span>
            </h6>
            <div class="alert alert-warning">Select ony those MDC courses offered by your Department</div>
            <div id="generatedCoursesEmpty" class="alert alert-warning mb-0 {{$generatedCourses->isEmpty() ? '' : 'd-none'}}">No published syllabus courses found for this semester.</div>
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width:50px;">Select</th>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Offered By Deparment</th>
                    <th>Course Type</th>
                    <th style="width:150px;">Delivery Preview</th>
                    <th style="width:190px;">Mark As</th>
                    @if($isSingleMajorCourse)
                    <th style="width:180px;">Apply As</th>
                    <th style="width:220px;">Specialization</th>
                    @endif
                  </tr>
                </thead>
                <tbody id="generatedCoursesTbody">
                  @foreach($generatedCourses as $course)
                  @php
                  $courseId = (int) ($course['id'] ?? 0);
                  $courseTypeTitle = strtoupper(trim((string) ($course['course_type_title'] ?? '')));
                  $derivedCourseType = strtoupper(trim((string) ($course['course_type'] ?? '')));
                  $sourceSubjectId = (int) ($course['source_subject_id'] ?? 0);
                  $previewDelivery = $derivedCourseType !== '' ? $derivedCourseType : 'COMMON';
                  if (in_array($previewDelivery, ['COREA', 'CORE-A', 'CORE A', 'MAJOR_COMBO1'], true)) {
                  $previewDelivery = 'COMBO1';
                  } elseif (in_array($previewDelivery, ['COREB', 'CORE-B', 'CORE B', 'MAJOR_COMBO2'], true)) {
                  $previewDelivery = 'COMBO2';
                  }
                  if (!in_array($previewDelivery, ['COMBO1', 'COMBO2', 'MDC', 'COMMON'], true)) {
                  if ($courseTypeTitle === 'MDC') {
                  $previewDelivery = 'MDC';
                  } elseif ($courseTypeTitle === 'MAJ') {
                  if ($combo1DepartmentId > 0 && $sourceSubjectId === $combo1DepartmentId) {
                  $previewDelivery = 'COMBO1';
                  } elseif ($combo2DepartmentId > 0 && $sourceSubjectId === $combo2DepartmentId) {
                  $previewDelivery = 'COMBO2';
                  } else {
                  $previewDelivery = 'COMMON';
                  }
                  } else {
                  $previewDelivery = 'COMMON';
                  }
                  }
                  $previewClass = $previewDelivery === 'COMBO1' ? 'bg-primary' : ($previewDelivery === 'COMBO2' ? 'bg-info text-dark' : ($previewDelivery === 'MDC' ? 'bg-warning text-dark' : 'bg-success'));
                  @endphp
                  <tr class="generated-course-row" data-course-type-title="{{ $courseTypeTitle }}" data-course-type="{{ $derivedCourseType }}" data-source-subject-id="{{ $sourceSubjectId }}">
                    <td>
                      <input type="checkbox" name="selected_courses[]" value="{{$courseId}}" class="form-check-input course-selector" data-course-id="{{$courseId}}">
                    </td>
                    <td>{{ $course['course_code'] ?? 'NA' }}</td>
                    <td>{{ $course['course_title'] ?? 'Untitled Course' }}</td>
                    <td>
                      {{ $course['source_subject'] ?? 'NA' }}
                      @if(!empty($course['source_subject_code']))
                      <small class="text-muted">({{ $course['source_subject_code'] }})</small>
                      @endif
                    </td>
                    <td>{{ $course['course_type_title'] ?? 'NA' }}</td>
                    <td>
                      <span class="badge generated-delivery-preview {{ $previewClass }}">{{ $previewDelivery }}</span>
                      <input type="hidden" name="delivery_category_map[{{$courseId}}]" class="delivery-category-map-input" value="{{ $previewDelivery }}">
                    </td>

                    <td>
                      <select name="course_type_map[{{$courseId}}]" class="form-control form-control-sm" disabled>
                        <option value="AUTO" selected>Compulsory</option>
                        <option value="STUDENT_CHOICE">Elective</option>
                      </select>
                    </td>
                    @if($isSingleMajorCourse)
                    <td>
                      <select name="specialization_mode_map[{{$courseId}}]" class="form-control form-control-sm specialization-mode-selector" disabled>
                        <option value="COMMON" selected>Common</option>
                        <option value="SPECIALIZATION">Specialization</option>
                      </select>
                    </td>
                    <td>
                      <select name="specialization_ids_map[{{$courseId}}][]" class="form-control form-control-sm specialization-selector select-multiple d-none" multiple size="4" disabled>
                        <option value="">Select specialization</option>
                        @foreach($availableSpecializations as $specialization)
                        <option value="{{$specialization->id}}">{{$specialization->name}}</option>
                        @endforeach
                      </select>
                    </td>
                    @endif
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </form>
    </div>

    @forelse(($coursesBySemester ?? collect()) as $semesterId => $semesterCourses)
    @php
    $semesterTitle = optional($semesterCourses->first()->semestermaster)->title ?? 'Semester ' . $semesterId;
    $autoCount = $semesterCourses->where('course_type', 'AUTO')->count();
    $studentChoiceCount = $semesterCourses->where('course_type', 'STUDENT_CHOICE')->count();
    $departmentChoiceCount = $semesterCourses->where('course_type', 'DEPARTMENT_CHOICE')->count();
    @endphp
    <div class="swc-list-card card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>
          {{ $semesterTitle }}
          <small class="swc-order-status d-none" data-order-status="{{ $semesterId }}">Saving order...</small>
        </span>
        <span class="swc-summary">
          <span class="swc-pill swc-pill-total">Total: {{ $semesterCourses->count() }}</span>
          <span class="swc-pill swc-pill-auto">Compulsory: {{ $autoCount }}</span>
          <span class="swc-pill swc-pill-student-choice">Elective: {{ $studentChoiceCount }}</span>
          <span class="swc-pill swc-pill-dept-choice">Optional: {{ $departmentChoiceCount }}</span>
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:50px"></th>
                <th style="width:70px">#</th>
                <th>Course</th>
                <th>Title</th>
                <th style="width:180px">Type</th>
                <th style="width:170px">Delivery Category</th>
                @if($isSingleMajorCourse)
                <th style="width:220px">Specialization</th>
                @endif
                <th>Pathway/Track</th>
                <th style="width:110px">Order</th>
                <th style="width:110px">Status</th>
                <th style="width:120px">Action</th>
              </tr>
            </thead>
            <tbody class="swc-sortable-body" data-semester="{{ $semesterId }}" data-combination="{{ $data->id }}">
              @foreach($semesterCourses as $index => $mappedCourse)
              @php
              $type = strtoupper((string) $mappedCourse->course_type);
              $typeClass = $type === 'AUTO' ? 'swc-type-auto' : ($type === 'STUDENT_CHOICE' ? 'swc-type-student-choice' : 'swc-type-dept-choice');
              $typeLabel = $typeLabelMap[$type] ?? $type;
              $deliveryCategory = (string) ($mappedCourse->delivery_category ?? '');
              $deliveryLabel = $deliveryCategory !== '' ? strtoupper(str_replace('_', ' ', $deliveryCategory)) : 'NOT DERIVED';
              $deliveryBadgeClass = in_array(strtoupper((string) $deliveryCategory), ['COMBO1', 'CORE-A', 'MAJOR_COMBO1'], true) ? 'bg-primary' : (in_array(strtoupper((string) $deliveryCategory), ['COMBO2', 'CORE-B', 'MAJOR_COMBO2'], true) ? 'bg-info text-dark' : (in_array(strtoupper((string) $deliveryCategory), ['COMMON', 'PROGRAMME_COMMON'], true) ? 'bg-success' : (in_array(strtoupper((string) $deliveryCategory), ['MDC', 'OPEN_CHOICE'], true) ? 'bg-warning text-dark' : 'bg-secondary')));
              $specializationMode = strtoupper((string) ($mappedCourse->specialization_mode ?? 'COMMON'));
              $specializationLabelClass = $specializationMode === 'SPECIALIZATION' ? 'bg-warning text-dark' : 'bg-secondary';
              $selectedSpecializationIds = collect((array) ($mappedCourse->specialization_master_ids ?? []))
              ->map(fn($id) => (int) $id)
              ->filter()
              ->unique()
              ->values();
              if ($selectedSpecializationIds->isEmpty() && !empty($mappedCourse->specialization_master_id)) {
              $selectedSpecializationIds = collect([(int) $mappedCourse->specialization_master_id]);
              }
              $selectedSpecializationNames = $selectedSpecializationIds
              ->map(fn($id) => $availableSpecializationsNameMap[$id] ?? null)
              ->filter()
              ->values();
              $pathwayText = $mappedCourse->academic_pathway_id ? ($pathwayNameMap[$mappedCourse->academic_pathway_id] ?? ('Pathway #' . $mappedCourse->academic_pathway_id)) : 'All Pathways';
              $trackText = $mappedCourse->degree_track_id ? ($trackNameMap[$mappedCourse->degree_track_id] ?? ('Track #' . $mappedCourse->degree_track_id)) : 'All Tracks';
              @endphp
              <tr data-mapping-id="{{ $mappedCourse->id }}">
                <td class="text-center"><i class="fa fa-bars swc-drag-handle" title="Drag to reorder"></i></td>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($mappedCourse->programinfo)->course_code ?? 'NA' }}</td>
                <td>{{ optional($mappedCourse->programinfo)->course_title ?? 'Untitled Course' }}</td>
                <td>
                  <span class="swc-type-badge {{ $typeClass }}">
                    {{ $typeLabel }}
                  </span>
                </td>
                <td>
                  <span class="badge {{ $deliveryBadgeClass }}">{{ $deliveryLabel }}</span>
                </td>
                @if($isSingleMajorCourse)
                <td>
                  <span class="badge {{ $specializationLabelClass }}">{{ $specializationMode === 'SPECIALIZATION' ? 'Specialization' : 'Common' }}</span>
                  @if($specializationMode === 'SPECIALIZATION' && $selectedSpecializationNames->isNotEmpty())
                  <div class="mt-1 d-flex gap-1 flex-wrap">
                    @foreach($selectedSpecializationNames as $specName)
                    <span class="badge bg-light text-dark">{{ $specName }}</span>
                    @endforeach
                  </div>
                  @endif
                </td>
                @endif
                <td>
                  <div class="small"><strong>{{ $pathwayText }}</strong></div>
                  <div class="small text-muted">{{ $trackText }}</div>
                </td>
                <td>{{ $mappedCourse->display_order ?? 1 }}</td>
                <td>
                  @if((int)($mappedCourse->is_active ?? 1) === 1)
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-outline-primary mb-1" data-bs-toggle="modal" data-bs-target="#editMapping{{$mappedCourse->id}}">Edit</button>
                  <form action="{{ route('delete.program.semster.courses.mapping', $mappedCourse->id) }}" method="POST" onsubmit="return confirm('Delete this mapping? Delete is blocked if marks or attendance already exist for mapped students.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>

              <div class="modal fade" id="editMapping{{$mappedCourse->id}}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Mapping</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('update.program.semster.courses.mapping', $mappedCourse->id)}}" method="post">
                      @csrf
                      <div class="modal-body">
                        <div class="row g-3">
                          <div class="col-md-6">
                            <label class="form-label">Semester *</label>
                            <select name="semester" class="form-control" required>
                              @foreach($semesters as $semester)
                              <option value="{{$semester->id}}" {{$mappedCourse->semester == $semester->id ? 'selected' : ''}}>{{$semester->title}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Course *</label>
                            <select name="course_id" class="form-control" required>
                              @php
                              $existingOption = $publishedCourseOptions->firstWhere('id', (int) $mappedCourse->course_id);
                              $editOptions = $publishedCourseOptions;
                              if (!$existingOption && $mappedCourse->programinfo) {
                              $editOptions = collect([[
                              'id' => (int) $mappedCourse->course_id,
                              'course_code' => (string) ($mappedCourse->programinfo->course_code ?? 'NA'),
                              'course_title' => (string) ($mappedCourse->programinfo->course_title ?? 'Untitled Course'),
                              ]])->merge($publishedCourseOptions);
                              }
                              @endphp
                              @foreach($editOptions as $paper)
                              <option value="{{$paper['id']}}" {{$mappedCourse->course_id == $paper['id'] ? 'selected' : ''}}>{{$paper['course_code']}} {{$paper['course_title']}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Course Type *</label>
                            <select name="course_type" class="form-control" required>
                              <option value="AUTO" {{$mappedCourse->course_type === 'AUTO' ? 'selected' : ''}}>Compulsory</option>
                              <option value="STUDENT_CHOICE" {{$mappedCourse->course_type === 'STUDENT_CHOICE' ? 'selected' : ''}}>Elective</option>
                              <option value="DEPARTMENT_CHOICE" {{$mappedCourse->course_type === 'DEPARTMENT_CHOICE' ? 'selected' : ''}}>Optional</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Academic Pathway</label>
                            <select name="academic_pathway_id" class="form-control">
                              <option value="">All Pathways</option>
                              @foreach($pathways as $pathway)
                              <option value="{{$pathway->id}}" {{$mappedCourse->academic_pathway_id == $pathway->id ? 'selected' : ''}}>{{$pathway->name}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Degree Track</label>
                            <select name="degree_track_id" class="form-control">
                              <option value="">All Tracks</option>
                              @foreach($degreeTracks as $track)
                              <option value="{{$track->id}}" {{$mappedCourse->degree_track_id == $track->id ? 'selected' : ''}}>{{$track->name}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Display Order *</label>
                            <input type="number" name="display_order" class="form-control" value="{{$mappedCourse->display_order ?? 1}}" min="1" max="999" required>
                          </div>
                          <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" id="isActive{{$mappedCourse->id}}" name="is_active" {{$mappedCourse->is_active ? 'checked' : ''}}>
                              <label class="form-check-label" for="isActive{{$mappedCourse->id}}">Active mapping</label>
                            </div>
                          </div>
                          @if($isSingleMajorCourse)
                          @php
                          $editSpecializationMode = strtoupper((string) ($mappedCourse->specialization_mode ?? 'COMMON'));
                          $editSelectedSpecializationIds = collect((array) ($mappedCourse->specialization_master_ids ?? []))
                          ->map(fn($id) => (int) $id)
                          ->filter()
                          ->unique()
                          ->values();
                          if ($editSelectedSpecializationIds->isEmpty() && !empty($mappedCourse->specialization_master_id)) {
                          $editSelectedSpecializationIds = collect([(int) $mappedCourse->specialization_master_id]);
                          }
                          @endphp
                          <div class="col-md-6">
                            <label class="form-label">Apply As</label>
                            <select name="specialization_mode" class="form-control edit-specialization-mode" data-target="#edit-specialization-select-{{$mappedCourse->id}}">
                              <option value="COMMON" {{$editSpecializationMode === 'COMMON' ? 'selected' : ''}}>Common</option>
                              <option value="SPECIALIZATION" {{$editSpecializationMode === 'SPECIALIZATION' ? 'selected' : ''}}>Specialization</option>
                            </select>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label">Specialization</label>
                            <select id="edit-specialization-select-{{$mappedCourse->id}}" name="specialization_master_ids[]" class="form-control specialization-selector select-multiple" multiple size="4" {{$editSpecializationMode === 'SPECIALIZATION' ? '' : 'disabled'}}>
                              <option value="">Select specialization</option>
                              @foreach($availableSpecializations as $specialization)
                              <option value="{{$specialization->id}}" {{$editSelectedSpecializationIds->contains((int) $specialization->id) ? 'selected' : ''}}>{{$specialization->name}}</option>
                              @endforeach
                            </select>
                          </div>
                          @endif
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @empty
    <div class="swc-empty">
      <h5 class="mb-1">No mapped courses yet</h5>
      <p class="mb-0">Use the form above to add course mappings semester-wise.</p>
    </div>
    @endforelse

  </div>
</div>

@include('includes.footer')
<script id="availableSpecializationsJson" type="application/json">
  @json($availableSpecializationsPayload)
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
  (function() {
    const token = '{{ csrf_token() }}';
    const orderUrl = "{{ route('update.program.semster.courses.order') }}";
    let combo1DepartmentId = Number('{{ $combo1DepartmentId }}');
    let combo2DepartmentId = Number('{{ $combo2DepartmentId }}');
    let isSingleMajorCourse = combo1DepartmentId > 0 && combo1DepartmentId === combo2DepartmentId;
    const generatedProgramTypeLabel = document.getElementById('generatedProgramTypeLabel');
    const defaultProgramType = String(generatedProgramTypeLabel?.textContent || 'UG').trim().toUpperCase() === 'PG' ? 'PG' : 'UG';
    const availableSpecializations = (() => {
      const raw = document.getElementById('availableSpecializationsJson')?.textContent || '[]';
      try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch (error) {
        return [];
      }
    })();
    const generateForm = document.getElementById('curriculumGenerateForm');
    const generateFeedback = document.getElementById('curriculumGenerateFeedback');
    const generatedCoursesTbody = document.getElementById('generatedCoursesTbody');
    const generatedCoursesEmpty = document.getElementById('generatedCoursesEmpty');
    const generatedSemesterLabel = document.getElementById('generatedSemesterLabel');
    const mappingSemesterInput = document.getElementById('mappingSemesterInput');
    const mappingForm = document.getElementById('curriculumMappingForm');
    const mappingFeedback = document.getElementById('curriculumMappingFeedback');
    const toastElement = document.getElementById('curriculumToast');
    const toastBodyElement = document.getElementById('curriculumToastBody');

    function initSpecializationMultiSelect(scopeElement) {
      if (!window.jQuery || typeof jQuery.fn.bsMultiSelect === 'undefined') {
        return;
      }

      const scope = scopeElement || document;
      scope.querySelectorAll('select.specialization-selector').forEach((selectEl) => {
        const $select = jQuery(selectEl);
        try {
          $select.bsMultiSelect('Dispose');
        } catch (e) {
          // Ignore if plugin has not been initialized yet.
        }
        $select.bsMultiSelect();
      });
    }

    function showToast(status, message) {
      if (!toastElement || !toastBodyElement || typeof bootstrap === 'undefined') {
        return;
      }

      toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-info');
      toastElement.classList.add('text-bg-success');
      toastBodyElement.textContent = message;
      bootstrap.Toast.getOrCreateInstance(toastElement).show();
    }

    function setGenerateFeedback(status, message) {
      showToast(status, message);
    }

    function normalizeCourseTypeLabel(value) {
      const normalized = String(value || '').trim().toUpperCase().replace(/[_-]+/g, ' ');
      if (['COMBO1', 'COMBO 1', 'CORE A', 'COREA', 'MAJOR COMBO1'].includes(normalized)) return 'COMBO1';
      if (['COMBO2', 'COMBO 2', 'CORE B', 'COREB', 'MAJOR COMBO2'].includes(normalized)) return 'COMBO2';
      return normalized;
    }

    function deriveDeliveryPreview(courseTypeTitle, sourceSubjectId, providedCourseType) {
      const normalizedProvided = normalizeCourseTypeLabel(providedCourseType);
      if (['COMBO1', 'COMBO2', 'MDC', 'COMMON'].includes(normalizedProvided)) {
        return normalizedProvided;
      }

      const normalizedType = String(courseTypeTitle || '').trim().toUpperCase();
      const deptId = Number(sourceSubjectId || 0);

      if (normalizedType === 'MDC') {
        return 'MDC';
      }

      if (normalizedType === 'MAJ') {
        if (combo1DepartmentId > 0 && deptId === combo1DepartmentId) {
          return 'COMBO1';
        }

        if (combo2DepartmentId > 0 && deptId === combo2DepartmentId) {
          return 'COMBO2';
        }

        return 'COMMON';
      }

      return 'COMMON';
    }

    function previewBadgeClass(delivery) {
      if (delivery === 'COMBO1') return 'bg-primary';
      if (delivery === 'COMBO2') return 'bg-info text-dark';
      if (delivery === 'MDC') return 'bg-warning text-dark';
      return 'bg-success';
    }

    function specializationOptionsHtml() {
      const options = ['<option value="">Select specialization</option>'];
      availableSpecializations.forEach((item) => {
        options.push('<option value="' + String(item.id) + '">' + escapeHtml(item.name) + '</option>');
      });
      return options.join('');
    }

    function toggleSpecializationSelector(row) {
      if (!row) {
        return;
      }

      const checkbox = row.querySelector('.course-selector');
      const modeSelect = row.querySelector('.specialization-mode-selector');
      const specializationSelect = row.querySelector('.specialization-selector');

      if (!modeSelect || !specializationSelect) {
        return;
      }

      const checked = checkbox ? checkbox.checked : false;
      modeSelect.disabled = !checked;
      const mode = String(modeSelect.value || 'COMMON').toUpperCase();
      const specializationActive = checked && mode === 'SPECIALIZATION';

      specializationSelect.classList.toggle('d-none', !specializationActive);
      specializationSelect.disabled = !specializationActive;
      specializationSelect.required = specializationActive;

      const pluginContainer = specializationSelect.parentElement ? specializationSelect.parentElement.querySelector('.dashboardcode-bsmultiselect') : null;
      if (pluginContainer) {
        pluginContainer.classList.toggle('d-none', !specializationActive);
      }

      if (!specializationActive) {
        Array.from(specializationSelect.options || []).forEach((option) => {
          option.selected = false;
        });
      }
    }

    function renderGeneratedDeliveryPreview() {
      document.querySelectorAll('tr.generated-course-row').forEach((row) => {
        const badge = row.querySelector('.generated-delivery-preview');
        if (!badge) return;

        const courseTypeTitle = row.getAttribute('data-course-type-title');
        const providedCourseType = row.getAttribute('data-course-type');
        const sourceSubjectId = row.getAttribute('data-source-subject-id');
        const delivery = deriveDeliveryPreview(courseTypeTitle, sourceSubjectId, providedCourseType);

        badge.className = 'badge generated-delivery-preview ' + previewBadgeClass(delivery);
        badge.textContent = delivery;

        const deliveryInput = row.querySelector('.delivery-category-map-input');
        if (deliveryInput) {
          deliveryInput.value = delivery;
        }
      });
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function renderGeneratedCourses(courses) {
      if (!generatedCoursesTbody) return;

      if (!Array.isArray(courses) || courses.length === 0) {
        generatedCoursesTbody.innerHTML = '';
        if (generatedCoursesEmpty) {
          generatedCoursesEmpty.classList.remove('d-none');
        }
        return;
      }

      if (generatedCoursesEmpty) {
        generatedCoursesEmpty.classList.add('d-none');
      }

      const rows = courses.map((course) => {
        const courseId = Number(course.id || 0);
        const courseTypeTitle = String(course.course_type_title || 'NA').trim().toUpperCase();
        const courseType = normalizeCourseTypeLabel(course.course_type || course.course_type_title || 'NA');
        const sourceSubjectId = Number(course.source_subject_id || 0);
        const delivery = deriveDeliveryPreview(courseTypeTitle, sourceSubjectId, course.course_type);
        const badgeClass = previewBadgeClass(delivery);
        const sourceCode = course.source_subject_code ? '<small class="text-muted">(' + escapeHtml(course.source_subject_code) + ')</small>' : '';
        const specializationColumns = isSingleMajorCourse ?
          '<td><select name="specialization_mode_map[' + String(courseId) + ']" class="form-control form-control-sm specialization-mode-selector" disabled><option value="COMMON" selected>Common</option><option value="SPECIALIZATION">Specialization</option></select></td>' +
          '<td><select name="specialization_ids_map[' + String(courseId) + '][]" class="form-control form-control-sm specialization-selector select-multiple d-none" multiple size="4" disabled>' + specializationOptionsHtml() + '</select></td>' :
          '';

        return '<tr class="generated-course-row" data-course-type-title="' + escapeHtml(courseTypeTitle) + '" data-course-type="' + escapeHtml(courseType) + '" data-source-subject-id="' + String(sourceSubjectId) + '">' +
          '<td><input type="checkbox" name="selected_courses[]" value="' + String(courseId) + '" class="form-check-input course-selector" data-course-id="' + String(courseId) + '"></td>' +
          '<td>' + escapeHtml(course.course_code || 'NA') + '</td>' +
          '<td>' + escapeHtml(course.course_title || 'Untitled Course') + '</td>' +
          '<td>' + escapeHtml(course.source_subject || 'NA') + ' ' + sourceCode + '</td>' +
          '<td>' + escapeHtml(course.course_type_title || 'NA') + '</td>' +
          '<td><span class="badge generated-delivery-preview ' + badgeClass + '">' + escapeHtml(delivery) + '</span><input type="hidden" name="delivery_category_map[' + String(courseId) + ']" class="delivery-category-map-input" value="' + escapeHtml(delivery) + '"></td>' +
          '<td><select name="course_type_map[' + String(courseId) + ']" class="form-control form-control-sm" disabled><option value="AUTO" selected>Compulsory</option><option value="STUDENT_CHOICE">Elective</option></select></td>' +
          specializationColumns +
          '</tr>';
      }).join('');

      generatedCoursesTbody.innerHTML = rows;
      initSpecializationMultiSelect(generatedCoursesTbody);
    }

    renderGeneratedDeliveryPreview();
    initSpecializationMultiSelect(document);

    if (generateForm) {
      generateForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const generateBtn = generateForm.querySelector('button[type="submit"]');
        const originalText = generateBtn ? generateBtn.textContent : '';

        if (generateBtn) {
          generateBtn.disabled = true;
          generateBtn.textContent = 'Fetching...';
        }

        setGenerateFeedback('info', 'Fetching courses...');

        try {
          const params = new URLSearchParams(new FormData(generateForm));
          const response = await fetch(generateForm.action + '?' + params.toString(), {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });

          const data = await response.json().catch(() => ({}));
          if (!response.ok || !data.status) {
            throw new Error(data.message || 'Unable to fetch courses.');
          }

          combo1DepartmentId = Number(data.combo1 || combo1DepartmentId || 0);
          combo2DepartmentId = Number(data.combo2 || combo2DepartmentId || 0);
          isSingleMajorCourse = combo1DepartmentId > 0 && combo1DepartmentId === combo2DepartmentId;

          if (generatedSemesterLabel) {
            generatedSemesterLabel.textContent = String(data.semester || params.get('semester') || '-');
          }

          if (mappingSemesterInput) {
            mappingSemesterInput.value = String(data.semester || params.get('semester') || '');
          }

          if (generatedProgramTypeLabel) {
            generatedProgramTypeLabel.textContent = String(data.program_type || defaultProgramType);
          }

          renderGeneratedCourses(data.data || []);
          renderGeneratedDeliveryPreview();
          const activeProgramType = String(data.program_type || generatedProgramTypeLabel?.textContent || defaultProgramType);
          setGenerateFeedback('success', `Courses fetched successfully for ${activeProgramType}.`);
        } catch (error) {
          setGenerateFeedback('error', error.message || 'Unable to fetch courses.');
        } finally {
          if (generateBtn) {
            generateBtn.disabled = false;
            generateBtn.textContent = originalText;
          }
        }
      });
    }

    function setMappingFeedback(status, message) {
      showToast(status, message);
    }

    const initialSuccessMessage = toastElement ? toastElement.dataset.successMessage : '';
    const initialErrorMessage = toastElement ? toastElement.dataset.errorMessage : '';

    if (initialSuccessMessage) {
      showToast('success', initialSuccessMessage);
    }

    if (initialErrorMessage) {
      showToast('error', initialErrorMessage);
    }

    if (mappingForm) {
      mappingForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (isSingleMajorCourse) {
          const selectedRows = Array.from(mappingForm.querySelectorAll('tr.generated-course-row')).filter((row) => {
            const checkbox = row.querySelector('.course-selector');
            return checkbox && checkbox.checked;
          });

          for (const row of selectedRows) {
            const modeSelect = row.querySelector('.specialization-mode-selector');
            const specializationSelect = row.querySelector('.specialization-selector');
            if (!modeSelect || !specializationSelect) {
              continue;
            }

            const mode = String(modeSelect.value || 'COMMON').toUpperCase();
            const selectedSpecializationValues = Array.from(specializationSelect.selectedOptions || []).map((option) => String(option.value || '')).filter((value) => value !== '');
            if (mode === 'SPECIALIZATION' && selectedSpecializationValues.length === 0) {
              setMappingFeedback('error', 'Please select specialization for each course marked as Specialization.');
              return;
            }
          }
        }

        const submitBtn = mappingForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.textContent : '';

        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Saving...';
        }

        setMappingFeedback('info', 'Saving selected courses...');

        try {
          const response = await fetch(mappingForm.action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: new FormData(mappingForm)
          });

          const data = await response.json().catch(() => ({}));
          if (!response.ok || !data.status) {
            let message = data.message || 'Unable to save curriculum mapping.';
            if (response.status === 422 && data.errors) {
              message = Object.values(data.errors).flat().join(' ');
            }
            throw new Error(message);
          }

          setMappingFeedback('success', data.message || 'Curriculum mapping saved.');
          setTimeout(() => {
            window.location.reload();
          }, 700);
        } catch (error) {
          setMappingFeedback('error', error.message || 'Unable to save curriculum mapping.');
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
          }
        }
      });
    }

    document.addEventListener('change', function(event) {
      const checkbox = event.target.closest('.course-selector');
      const modeSelector = event.target.closest('.specialization-mode-selector');
      const editModeSelector = event.target.closest('.edit-specialization-mode');

      if (checkbox) {
        const row = checkbox.closest('tr');
        const typeSelect = row ? row.querySelector('select[name^="course_type_map"]') : null;
        if (typeSelect) {
          typeSelect.disabled = !checkbox.checked;
        }

        toggleSpecializationSelector(row);
        initSpecializationMultiSelect(document);
        return;
      }

      if (modeSelector) {
        toggleSpecializationSelector(modeSelector.closest('tr'));
        initSpecializationMultiSelect(document);
        return;
      }

      if (editModeSelector) {
        const targetSelector = editModeSelector.getAttribute('data-target');
        if (!targetSelector) {
          return;
        }

        const select = document.querySelector(targetSelector);
        if (!select) {
          return;
        }

        const mode = String(editModeSelector.value || 'COMMON').toUpperCase();
        const active = mode === 'SPECIALIZATION';
        select.disabled = !active;
        if (!active) {
          Array.from(select.options || []).forEach((option) => {
            option.selected = false;
          });
        }

        initSpecializationMultiSelect(document);
      }
    });

    document.querySelectorAll('.generated-course-row').forEach((row) => {
      toggleSpecializationSelector(row);
    });

    document.querySelectorAll('.modal').forEach((modalEl) => {
      modalEl.addEventListener('shown.bs.modal', function() {
        initSpecializationMultiSelect(modalEl);
      });
    });

    function setOrderStatus(semester, visible, message) {
      const status = document.querySelector('[data-order-status="' + semester + '"]');
      if (!status) return;
      status.textContent = message || 'Saving order...';
      status.classList.toggle('d-none', !visible);
    }

    function refreshIndexes(tbody) {
      const rows = tbody.querySelectorAll('tr');
      rows.forEach((row, idx) => {
        const indexCell = row.children[1];
        if (indexCell) {
          indexCell.textContent = String(idx + 1);
        }
      });
    }

    document.querySelectorAll('.swc-sortable-body').forEach((tbody) => {
      new Sortable(tbody, {
        animation: 150,
        handle: '.swc-drag-handle',
        ghostClass: 'swc-dragging',
        onEnd: async function() {
          const semester = tbody.getAttribute('data-semester');
          const combinationId = tbody.getAttribute('data-combination');
          const mappingIds = Array.from(tbody.querySelectorAll('tr[data-mapping-id]')).map((row) => Number(row.getAttribute('data-mapping-id')));

          refreshIndexes(tbody);
          setOrderStatus(semester, true, 'Saving order...');

          try {
            const response = await fetch(orderUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                combination_id: Number(combinationId),
                semester: Number(semester),
                mapping_ids: mappingIds
              })
            });

            const data = await response.json();
            if (!response.ok || !data.status) {
              throw new Error(data.message || 'Unable to save order');
            }

            setOrderStatus(semester, true, 'Saved');
            setTimeout(() => setOrderStatus(semester, false), 1200);
          } catch (error) {
            setOrderStatus(semester, true, 'Save failed');
            setTimeout(() => setOrderStatus(semester, false), 2000);
          }
        }
      });
    });
  })();
</script>
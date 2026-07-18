<?php

use App\Models\AcademicPathwayMaster;
use App\Models\DegreeTrackMaster;
use App\Models\ProgramCourseMaster;
use App\Models\Semester;

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
    <p class="swc-subtitle">This is the CORE of <strong>Academic System</strong> Select Semester, click Generate, then choose offered courses and mark each as Compulsory or Elective.</p>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="swc-info">
      <h5 class="mb-1">Batch: {{$data->batchmaster->batch_name}} | {{$data->studentprograminfo->code}} - {{$data->studentprograminfo->name}}</h5>
      <p class="mb-0">
        <i class="fa fa-robot"></i> Two Connected:
        <span class="badge badge-warning">{{$data->combomap->combo1->title ?? '1 Not Set'}}</span>
        <span class="badge badge-warning"> {{$data->combomap->combo2->title ?? '2 Not Set'}}</span>
      </p>
    </div>

    <div class="swc-form-card mb-3">
      <form action="{{route('combo.course.fetching')}}" method="get">
        <!-- <form id="curriculumGenerateForm" action="{{route('combo.course.fetching')}}" method="get"> -->
        <div class="row align-items-end">
          <div class="col-lg-3">
            <label class="form-label">Semester</label>
            <select name="semester" id="curriculumSemesterSelect" class="form-control" required>
              <option value=""> --Select Semester-- </option>
              @foreach ($semesters as $semester)
              <option value="{{$semester->id}}" {{$selectedSemester === (int) $semester->id ? 'selected' : ''}}>{{$semester->title}}</option>
              @endforeach
            </select>
            <input type="hidden" name="combination_id" value="{{$data->id}}">
            <input type="hidden" name="combo1" value="{{$combo1DepartmentId}}">
            <input type="hidden" name="combo2" value="{{$combo2DepartmentId}}">

          </div>
          <div class="col-lg-2">
            <button type="submit" class="btn btn-primary w-100">Generate</button>
          </div>
          <div class="col-lg-4">
            <div id="curriculumGenerateFeedback" class="alert d-none mb-0" role="alert"></div>
          </div>
        </div>
      </form>
    </div>

    <div class="swc-form-card">
      <form id="curriculumMappingForm" action="{{route('store.curriculam.mapping')}}" method="post">
        @csrf
        <input type="hidden" name="semester" id="mappingSemesterInput" value="{{$selectedSemester}}">
        <input type="hidden" name="id" value="{{$data->id}}">
        <input type="hidden" name="batch" value="{{$data->batchmaster->id}}">
        <div id="curriculumMappingFeedback" class="alert d-none" role="alert"></div>
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
            <h6 class="mb-2">All Offered Courses (Published Syllabus) - Semester <span id="generatedSemesterLabel">{{ $selectedSemester > 0 ? $selectedSemester : '-' }}</span></h6>
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
                  </tr>
                </thead>
                <tbody id="generatedCoursesTbody">
                  @foreach($generatedCourses as $course)
                  @php
                  $courseId = (int) ($course['id'] ?? 0);
                  $courseTypeTitle = strtoupper(trim((string) ($course['course_type_title'] ?? '')));
                  $sourceSubjectId = (int) ($course['source_subject_id'] ?? 0);
                  $previewDelivery = 'COMMON';
                  if ($courseTypeTitle === 'MDC') {
                  $previewDelivery = 'MDC';
                  } elseif ($courseTypeTitle === 'MAJ') {
                  if ($combo1DepartmentId > 0 && $sourceSubjectId === $combo1DepartmentId) {
                  $previewDelivery = 'CORE-A';
                  } elseif ($combo2DepartmentId > 0 && $sourceSubjectId === $combo2DepartmentId) {
                  $previewDelivery = 'CORE-B';
                  }
                  }
                  $previewClass = $previewDelivery === 'CORE-A' ? 'bg-primary' : ($previewDelivery === 'CORE-B' ? 'bg-info text-dark' : ($previewDelivery === 'MDC' ? 'bg-warning text-dark' : 'bg-success'));
                  @endphp
                  <tr class="generated-course-row" data-course-type-title="{{ $courseTypeTitle }}" data-source-subject-id="{{ $sourceSubjectId }}">
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
                    </td>

                    <td>
                      <select name="course_type_map[{{$courseId}}]" class="form-control form-control-sm" disabled>
                        <option value="AUTO">Compulsory</option>
                        <option value="STUDENT_CHOICE" selected>Elective</option>
                      </select>
                    </td>
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
              $deliveryBadgeClass = in_array($deliveryCategory, ['CORE-A', 'major_combo1'], true) ? 'bg-primary' : (in_array($deliveryCategory, ['CORE-B', 'major_combo2'], true) ? 'bg-info text-dark' : (in_array($deliveryCategory, ['COMMON', 'programme_common'], true) ? 'bg-success' : (in_array($deliveryCategory, ['MDC', 'open_choice'], true) ? 'bg-warning text-dark' : 'bg-secondary')));
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
  (function() {
    const token = '{{ csrf_token() }}';
    const orderUrl = "{{ route('update.program.semster.courses.order') }}";
    let combo1DepartmentId = Number('{{ $combo1DepartmentId }}');
    let combo2DepartmentId = Number('{{ $combo2DepartmentId }}');
    const generateForm = document.getElementById('curriculumGenerateForm');
    const generateFeedback = document.getElementById('curriculumGenerateFeedback');
    const generatedCoursesTbody = document.getElementById('generatedCoursesTbody');
    const generatedCoursesEmpty = document.getElementById('generatedCoursesEmpty');
    const generatedSemesterLabel = document.getElementById('generatedSemesterLabel');
    const mappingSemesterInput = document.getElementById('mappingSemesterInput');
    const mappingForm = document.getElementById('curriculumMappingForm');
    const mappingFeedback = document.getElementById('curriculumMappingFeedback');

    function setGenerateFeedback(status, message) {
      if (!generateFeedback) return;
      generateFeedback.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
      generateFeedback.classList.add(status === 'success' ? 'alert-success' : (status === 'info' ? 'alert-info' : 'alert-danger'));
      generateFeedback.textContent = message;
    }

    function deriveDeliveryPreview(courseTypeTitle, sourceSubjectId) {
      const normalizedType = String(courseTypeTitle || '').trim().toUpperCase();
      const deptId = Number(sourceSubjectId || 0);

      if (normalizedType === 'MDC') {
        return 'MDC';
      }

      if (normalizedType === 'MAJ') {
        if (combo1DepartmentId > 0 && deptId === combo1DepartmentId) {
          return 'CORE-A';
        }

        if (combo2DepartmentId > 0 && deptId === combo2DepartmentId) {
          return 'CORE-B';
        }

        return 'COMMON';
      }

      return 'COMMON';
    }

    function previewBadgeClass(delivery) {
      if (delivery === 'CORE-A') return 'bg-primary';
      if (delivery === 'CORE-B') return 'bg-info text-dark';
      if (delivery === 'MDC') return 'bg-warning text-dark';
      return 'bg-success';
    }

    function renderGeneratedDeliveryPreview() {
      document.querySelectorAll('tr.generated-course-row').forEach((row) => {
        const badge = row.querySelector('.generated-delivery-preview');
        if (!badge) return;

        const courseTypeTitle = row.getAttribute('data-course-type-title');
        const sourceSubjectId = row.getAttribute('data-source-subject-id');
        const delivery = deriveDeliveryPreview(courseTypeTitle, sourceSubjectId);

        badge.className = 'badge generated-delivery-preview ' + previewBadgeClass(delivery);
        badge.textContent = delivery;
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
        const sourceSubjectId = Number(course.source_subject_id || 0);
        const delivery = deriveDeliveryPreview(courseTypeTitle, sourceSubjectId);
        const badgeClass = previewBadgeClass(delivery);
        const sourceCode = course.source_subject_code ? '<small class="text-muted">(' + escapeHtml(course.source_subject_code) + ')</small>' : '';

        return '<tr class="generated-course-row" data-course-type-title="' + escapeHtml(courseTypeTitle) + '" data-source-subject-id="' + String(sourceSubjectId) + '">' +
          '<td><input type="checkbox" name="selected_courses[]" value="' + String(courseId) + '" class="form-check-input course-selector" data-course-id="' + String(courseId) + '"></td>' +
          '<td>' + escapeHtml(course.course_code || 'NA') + '</td>' +
          '<td>' + escapeHtml(course.course_title || 'Untitled Course') + '</td>' +
          '<td>' + escapeHtml(course.source_subject || 'NA') + ' ' + sourceCode + '</td>' +
          '<td>' + escapeHtml(course.course_type_title || 'NA') + '</td>' +
          '<td><span class="badge generated-delivery-preview ' + badgeClass + '">' + escapeHtml(delivery) + '</span></td>' +
          '<td><select name="course_type_map[' + String(courseId) + ']" class="form-control form-control-sm" disabled><option value="AUTO">Compulsory</option><option value="STUDENT_CHOICE" selected>Elective</option></select></td>' +
          '</tr>';
      }).join('');

      generatedCoursesTbody.innerHTML = rows;
    }

    renderGeneratedDeliveryPreview();

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

          if (generatedSemesterLabel) {
            generatedSemesterLabel.textContent = String(data.semester || params.get('semester') || '-');
          }

          if (mappingSemesterInput) {
            mappingSemesterInput.value = String(data.semester || params.get('semester') || '');
          }

          renderGeneratedCourses(data.data || []);
          renderGeneratedDeliveryPreview();
          setGenerateFeedback('success', 'Courses fetched successfully.');
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
      if (!mappingFeedback) return;
      mappingFeedback.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
      mappingFeedback.classList.add(status === 'success' ? 'alert-success' : (status === 'info' ? 'alert-info' : 'alert-danger'));
      mappingFeedback.textContent = message;
    }

    if (mappingForm) {
      mappingForm.addEventListener('submit', async function(event) {
        event.preventDefault();

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
      if (!checkbox) {
        return;
      }

      const row = checkbox.closest('tr');
      const typeSelect = row ? row.querySelector('select[name^="course_type_map"]') : null;
      if (typeSelect) {
        typeSelect.disabled = !checkbox.checked;
      }
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
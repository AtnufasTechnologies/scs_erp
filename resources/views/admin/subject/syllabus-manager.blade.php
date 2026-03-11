<?php

use App\Models\BatchMaster;

$batches = BatchMaster::all();

?>
@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <h3 class="text-capitalize">Syllabus Manager - {{$data['slug']}}</h3>

  <div class="row no-print">
    <div class="col-lg-2">
      <!-- Button trigger modal -->
      <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addSyllabus">
        <span class="button-decor"></span>
        <div class="button-content">
          <div class="button__icon">
            <i class="fa fa-plus-circle"></i>
          </div>
          <span class="button__text">Add New</span>
        </div>
      </button>
    </div>



    <div class="col-lg-2">
      <!-- PDF Download Button -->
      <a href="{{ route('department.syllabus.download.pdf', ['id' => $data['id'], 'slug' => $data['slug'], 'filter_batch' => request('filter_batch')]) }}" class="btn btn-danger mb-3">
        <i class="fa fa-file-pdf"></i> PDF
      </a>
    </div>

    <div class="col-lg-3 offset-lg-3">
      <form action="{{ route('department.syllabus.manager', ['id' => $data['id'],'slug' => $data['slug']]) }}" method="get">
        <div class="input-group">
          <select name="filter_batch" class="form-select">
            <option value="" selected>--Select Batch--</option>
            @foreach ($batches as $batch)
            <option value="{{$batch->id}}" {{ request('batch') == $batch->id ? 'selected' : '' }}>{{$batch->batch_name}}</option>
            @endforeach
          </select>
          <input type="hidden" name="id" value="{{$data['id']}}">
          <input type="hidden" name="slug" value="{{$data['slug']}}">
          <button class="btn btn-outline-success"><i class="fa fa-search"></i></button>
        </div>

      </form>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="addSyllabus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                <div class="col-lg-6">
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

                <div class="col-lg-6">
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

                <div class="col-lg-12">
                  <label for="">Select Course *</label>
                  <select name="co_id" id="course_objective" class="form-select mb-3">
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
                  <select name="cso_id" id="cso_select" class="form-select mb-3">
                    <option value="" selected>--Select Course First--</option>
                  </select>
                  @error('cso_id')
                  <small class="text-danger">{{$message}}</small>
                  @enderror
                </div>

                <div class="col-lg-12">
                  <label for="">Select CSO Sub Unit(s)</label>
                  <div id="cso_subunit_checkboxes" class="border p-3 rounded mb-3" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-muted">--Select CSO First--</p>
                  </div>
                  @error('cso_subunit')
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
    @forelse ($data['organized_syllabus'] ?? [] as $batchName => $semesters)
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-graduation-cap"></i> Batch: {{ $batchName }}</h5>
      </div>
      <div class="card-body p-0">
        @foreach ($semesters as $semesterName => $courses)
        <div class="border-bottom">
          <div class="p-3 bg-light">
            <h6 class="mb-0"><i class="fa fa-calendar"></i> {{ $semesterName }}</h6>
          </div>
          <div class="accordion" id="accordion{{ Str::slug($batchName . $semesterName) }}">
            @foreach ($courses as $courseKey => $courseData)
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                  data-bs-target="#course{{ Str::slug($batchName . $semesterName . $courseKey) }}"
                  aria-expanded="false">
                  <strong>{{ $courseData['course']->course_code ?? 'N/A' }}</strong>
                  <span class="ms-2">{{ $courseData['course']->course_title ?? 'Unknown Course' }}</span>
                  <span class="badge bg-secondary ms-auto me-2">{{ $courseData['course']->credits ?? '0' }} Credits</span>
                </button>
              </h2>
              <div id="course{{ Str::slug($batchName . $semesterName . $courseKey) }}"
                class="accordion-collapse collapse"
                data-bs-parent="#accordion{{ Str::slug($batchName . $semesterName) }}">
                <div class="accordion-body">
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
                        <span><strong>{{ $syllabus->cso->title ?? 'N/A' }}</strong></span>
                        <span class="badge bg-light text-dark">{{ $syllabus->cso->lectures_needed ?? '0' }} Lectures</span>
                      </div>
                    </div>
                    <div class="card-body">
                      <h6 class="mb-3">Learning Units</h6>
                      <div class="list-group">
                        @foreach ($syllabus->cso->csosubunits ?? [] as $subunit)
                        <div class="list-group-item">
                          <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                              <h6 class="mb-1">{{ $subunit->title }}</h6>
                              <small class="text-muted">
                                <span class="badge bg-primary">
                                  {{ $subunit->taxomonylevel->shortname ?? '-' }} -
                                  {{ $subunit->taxomonylevel->fullname ?? '-' }}
                                </span>
                              </small>
                            </div>
                            <div>
                              @if ($subunit->is_completed == 1)
                              <span class="badge bg-success" title="Completed">
                                <i class="fa fa-check-circle"></i> Completed
                              </span>
                              @else
                              <span class="badge bg-warning" title="Pending">
                                <i class="fa fa-clock"></i> Pending
                              </span>
                              @endif
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
    </div>
    @empty
    <div class="alert alert-info text-center" role="alert">
      <i class="fa fa-info-circle"></i> No syllabus data available. Click "Add New" to create your first syllabus.
    </div>
    @endforelse
  </div>
</div>




</div>
@include('includes.footer')

<!-- CSO AJAX Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_objective');
    const csoSelect = document.getElementById('cso_select');
    const csoSubunitCheckboxes = document.getElementById('cso_subunit_checkboxes');

    if (courseSelect) {
      courseSelect.addEventListener('change', function() {
        const courseId = this.value;

        // Reset CSO and sub unit checkboxes
        csoSelect.innerHTML = '<option value="" selected>--Select--</option>';
        csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';

        if (!courseId) {
          csoSelect.innerHTML = '<option value="" selected>--Select Course First--</option>';
          return;
        }

        // Show loading state
        csoSelect.innerHTML = '<option value="" selected>Loading CSOs...</option>';

        // Fetch CSOs for the selected course
        fetch(`/erp/deptartment/course/${courseId}/cso-list`)
          .then(response => response.json())
          .then(data => {

            if (data.length > 0) {
              csoSelect.innerHTML = '<option value="" selected>--Select CSO--</option>';
              data.forEach(function(cso) {
                const option = document.createElement('option');
                option.value = cso.id;
                option.textContent = `${cso.title} (Lectures: ${cso.lectures_needed})`;
                option.dataset.cso = JSON.stringify(cso);
                csoSelect.appendChild(option);
              });
            } else {
              csoSelect.innerHTML = '<option value="" selected>No CSOs found for this course</option>';
            }
          })
          .catch(() => {
            csoSelect.innerHTML = '<option value="" selected>Failed to load CSOs</option>';
          });
      });
    }

    if (csoSelect) {
      csoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        // Reset sub unit checkboxes
        csoSubunitCheckboxes.innerHTML = '';

        if (!this.value || !selectedOption.dataset.cso) {
          csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';
          return;
        }

        try {
          const cso = JSON.parse(selectedOption.dataset.cso);

          if (cso.csosubunits && cso.csosubunits.length > 0) {
            // Add Select All checkbox
            const selectAllDiv = document.createElement('div');
            selectAllDiv.className = 'form-check mb-3 pb-2 border-bottom';

            const selectAllCheckbox = document.createElement('input');
            selectAllCheckbox.className = 'form-check-input';
            selectAllCheckbox.type = 'checkbox';
            selectAllCheckbox.id = 'select_all_subunits';

            const selectAllLabel = document.createElement('label');
            selectAllLabel.className = 'form-check-label fw-bold';
            selectAllLabel.htmlFor = 'select_all_subunits';
            selectAllLabel.textContent = 'Select All';

            selectAllDiv.appendChild(selectAllCheckbox);
            selectAllDiv.appendChild(selectAllLabel);
            csoSubunitCheckboxes.appendChild(selectAllDiv);

            // Add individual subunit checkboxes
            cso.csosubunits.forEach(function(subunit, index) {
              const checkboxDiv = document.createElement('div');
              checkboxDiv.className = 'form-check mb-2';

              const checkbox = document.createElement('input');
              checkbox.className = 'form-check-input cso-subunit-checkbox';
              checkbox.type = 'checkbox';
              checkbox.name = 'cso_subunit[]';
              checkbox.value = subunit.id;
              checkbox.id = `cso_subunit_${subunit.id}`;

              const label = document.createElement('label');
              label.className = 'form-check-label';
              label.htmlFor = `cso_subunit_${subunit.id}`;
              label.textContent = `${subunit.title} (${subunit.taxomonylevel ? subunit.taxomonylevel.fullname : 'N/A'})`;

              checkboxDiv.appendChild(checkbox);
              checkboxDiv.appendChild(label);
              csoSubunitCheckboxes.appendChild(checkboxDiv);
            });

            // Select All functionality
            selectAllCheckbox.addEventListener('change', function() {
              const subunitCheckboxes = document.querySelectorAll('.cso-subunit-checkbox');
              subunitCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            // Update Select All state when individual checkboxes change
            const subunitCheckboxes = document.querySelectorAll('.cso-subunit-checkbox');
            subunitCheckboxes.forEach(function(checkbox) {
              checkbox.addEventListener('change', function() {
                const allChecked = Array.from(subunitCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
              });
            });
          } else {
            csoSubunitCheckboxes.innerHTML = '<p class="text-muted">No sub units found for this CSO</p>';
          }
        } catch (e) {
          csoSubunitCheckboxes.innerHTML = '<p class="text-muted text-danger">Error loading sub units</p>';
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
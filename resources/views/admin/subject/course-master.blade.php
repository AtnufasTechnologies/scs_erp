<?php

use App\Http\Controllers\StaticController;
use App\Models\PaperTypeMaster;
use App\Models\SubjectTypeMaster;

$coursetypes = SubjectTypeMaster::all();
$papertypes = PaperTypeMaster::all();
$userType = StaticController::fetchUserRole();
?>

@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">

  <div class="container-fluid py-4">
    <!-- <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
          <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / Course Objective Master</span>
        </a>
        <div class="d-flex">
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #0002;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
        </div>
      </div>
    </nav> -->

    <style>
      .co-toolbar {
        background: linear-gradient(135deg, #fbf6ff 0%, #fdfdfd 100%);
        border: 1px solid #e8defe;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 6px 18px rgba(87, 64, 180, 0.08);
      }

      .co-toolbar__content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
      }

      .co-toolbar__title {
        margin: 0;
        color: #2f2167;
        font-weight: 800;
        letter-spacing: 0.01em;
      }

      .co-toolbar__subtitle {
        margin: 4px 0 0;
        color: #5f5878;
        font-size: 0.95rem;
      }

      .co-toolbar__count {
        display: inline-block;
        margin-left: 6px;
        padding: 2px 10px;
        border-radius: 999px;
        background: #ece4ff;
        color: #4a2fb0;
        font-weight: 700;
      }

      .co-toolbar__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      .co-action-btn {
        border: 0;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
      }

      .co-action-btn i {
        margin-right: 6px;
      }

      @media (max-width: 768px) {
        .co-toolbar {
          padding: 14px;
        }

        .co-toolbar__actions {
          width: 100%;
        }

        .co-action-btn {
          width: 100%;
        }
      }
    </style>

    <div class="co-toolbar mb-4">
      <div class="co-toolbar__content">
        <div>
          <h3 class="co-toolbar__title">{{ $data->title ?? '-' }} / My CO List</h3>
          <p class="co-toolbar__subtitle">Total mapped courses <span class="co-toolbar__count">{{ count($mycourses) }}</span></p>
        </div>

        <div class="co-toolbar__actions">
          <!-- Button to trigger modal for new course -->
          <button type="button" class="btn btn-success co-action-btn" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fa fa-plus-circle"></i>Add New Course
          </button>

          <!-- Button to trigger modal -->
          <button type="button" class="btn btn-warning co-action-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="fa fa-layer-group"></i>From Existing Course Master
          </button>
        </div>
      </div>
    </div>


    <!-- Modal for adding new course -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="addCourseForm" action="{{ route('department.create.course.master') }}" method="post">
            @csrf
            <div class="modal-body">

              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="courseCode" class="form-label">Course Code *</label>
                    <input type="text" class="form-control" id="courseCode" name="course_code" autocomplete="off" required>
                    <small id="courseCodeFeedback" class="d-block mt-1 text-muted"><i class="fa fa-info-circle me-1"></i>Enter a course code to check availability.</small>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-3">
                    <label for="courseType" class="form-label">Course Type *</label>
                    <select class="form-select dselect-example" id="" name="course_type" required>
                      <option value="">Select Course Type</option>
                      @foreach($coursetypes as $type)
                      <option value="{{ $type->id }}">{{ $type->title }} - {{$type->description}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="paperType" class="form-label">Paper Type *</label>
                    <select class="form-select" id="paperType" name="paper_type" required>
                      <option value="">Select Paper Type</option>
                      @foreach($papertypes as $papertype)
                      <option value="{{ $papertype->id }}">{{ $papertype->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="credits" class="form-label">Credits *</label>
                    <input type="number" class="form-control" id="credits" name="credits" step="0.5" required>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="teachingHours" class="form-label">Teaching Hours *</label>
                    <input type="number" class="form-control" id="teachingHours" name="total_alloted_hours" min="0">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="internalMarks" class="form-label">Internal Marks *</label>
                    <input type="number" class="form-control" id="internalMarks" name="internal" min="0">
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="mb-3">
                    <label for="externalMarks" class="form-label">External Marks *</label>
                    <input type="number" class="form-control" id="externalMarks" name="external" min="0">
                  </div>
                </div>
                <div class="col-lg-12">
                  <div class="mb-3">
                    <label for="courseTitle" class="form-label">Course Title *</label>
                    <textarea name="course_title" id="courseTitle" class="form-control" required></textarea>
                  </div>
                </div>
              </div>

              <input type="hidden" name="subject_id" value="{{ $data->id }}">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success submit-btn" id="createCourseBtn" disabled>
                <span class="submit-text">Create Course</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <span class="loading-text d-none">Processing...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Add Courses</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('department.add.course.master') }}" method="post">
            @csrf
            <div class="modal-body">
              <label for="">Select Master Course *</label>
              <select name="courses[]" class="select-multiple" multiple>
                @foreach ($course_master as $course)
                <option value="{{ $course->id }}">{{ $course->id }} - {{ $course->course_code }} - {{ $course->course_title }} ({{$course->coursetypemaster->title ?? '-'   }})</option>
                @endforeach
              </select>


              <input type="hidden" name="subject_id" value="{{ $data->id }}">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary submit-btn">
                <span class="submit-text">Save changes</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <span class="loading-text d-none">Processing...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <div class="container-fluid">
      @if(count($mycourses))
      <div class="row mt-3">
        <div class="col-12 col-lg-6">
          <label for="courseMasterSearchInput" class="form-label fw-semibold">Search Courses</label>
          <input
            type="text"
            id="courseMasterSearchInput"
            class="form-control"
            placeholder="Search by code, title, type, paper type, credits, marks...">
          <small class="text-muted">Live filter on loaded rows.</small>
        </div>
        <div class="col-12 col-lg-6 d-flex align-items-end justify-content-lg-end mt-2 mt-lg-0">
          <div class="text-muted">Showing: <strong id="courseMasterVisibleCount">{{ count($mycourses) }}</strong> / {{ count($mycourses) }}</div>
        </div>
      </div>

      <div class="table-responsive mt-4">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>RefId#</th>
              <th>Code</th>
              <th>Course Title</th>
              <th>CO/CSO Applicability</th>
              <th>Reason</th>
              <th>Type</th>
              <th>Credits</th>
              <th>Paper Type</th>
              <th>Teaching Hours</th>
              <th>Internal</th>
              <th>External</th>
              <th>Action</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($mycourses as $course)
            @php
            $isNoCoCsoApplicable = (int) ($course->co_cso_not_applicable ?? 0) === 1;
            $noCoCsoReason = trim((string) ($course->co_cso_not_applicable_note ?? ''));
            $courseSearchText = strtolower(trim(
            '#' . ($course->id ?? '') . ' ' .
            (string) ($course->courseMaster->course_code ?? '') . ' ' .
            (string) ($course->courseMaster->course_title ?? '') . ' ' .
            ($isNoCoCsoApplicable ? 'co cso not applicable declared not applicable' : 'co cso applicable') . ' ' .
            (string) $noCoCsoReason . ' ' .
            (string) ($course->courseMaster->coursetypemaster->title ?? '') . ' ' .
            (string) ($course->courseMaster->coursetypemaster->description ?? '') . ' ' .
            (string) ($course->courseMaster->papertypemaster->name ?? '') . ' ' .
            (string) ($course->courseMaster->credits ?? '') . ' ' .
            (string) ($course->courseMaster->total_alloted_hours ?? '') . ' ' .
            (string) ($course->courseMaster->internal ?? '') . ' ' .
            (string) ($course->courseMaster->external ?? '')
            ));
            @endphp
            <tr class="course-row" data-course-search="{{ $courseSearchText }}">
              <td>#{{ $course->id }}</td>
              <td>{{ $course->courseMaster->course_code ?? '' }}</td>
              <td>{{ $course->courseMaster->course_title ?? '' }}</td>
              <td>
                @if($isNoCoCsoApplicable)
                <span class="badge bg-warning text-dark">Not Applicable</span>
                @else
                <span class="badge bg-success">Applicable</span>
                @endif
              </td>
              <td>
                @if($isNoCoCsoApplicable)
                {{ $noCoCsoReason !== '' ? $noCoCsoReason : '-' }}
                @else
                -
                @endif
              </td>
              <td>{{ $course->courseMaster->coursetypemaster->title ?? '-' }} - {{ $course->courseMaster->coursetypemaster->description ?? '-' }}</td>
              <td>{{ $course->courseMaster->credits ?? '-' }}</td>
              <td>{{ $course->courseMaster->papertypemaster->name ?? '-' }}</td>
              <td>{{ $course->courseMaster->total_alloted_hours ?? '-' }}</td>
              <td>{{ $course->courseMaster->internal ?? '-' }}</td>
              <td>{{ $course->courseMaster->external ?? '-' }}</td>
              <td>
                <a href="{{ route('department.view.cso', $course->course_master_id  ) }}" class="btn btn-outline-success btn-sm mb-1">Design</a>
              </td>
              <td>
                <button class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#editCourseModal{{ $course->id }}"><i class="fa fa-edit"></i></button>


                <!-- Edit Course Modal -->
                <div class="modal fade" id="editCourseModal{{ $course->id }}" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form class="edit-course-form" action="{{ route('department.update.course.master',$course->courseMaster->id ?? '') }}" method="post" data-course-master-id="{{ (int) ($course->courseMaster->id ?? 0) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="subject_id" value="{{ (int) ($data->id ?? 0) }}">
                        <div class="modal-body">
                          <div class="row">
                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">Course Code *</label>
                                <input type="text" class="form-control edit-course-code" name="course_code" value="{{ $course->courseMaster->course_code ?? '' }}" data-initial-code="{{ strtoupper(trim((string) ($course->courseMaster->course_code ?? ''))) }}" autocomplete="off" required>
                                <small class="edit-course-code-feedback d-block mt-1 text-muted">Update the course code to check availability.</small>
                              </div>
                            </div>
                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">Credits *</label>
                                <input type="number" class="form-control" name="credits" value="{{ $course->courseMaster->credits ?? '' }}" step="0.5" required>
                              </div>
                            </div>
                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">Total Teaching Hours</label>
                                <input type="number" class="form-control" name="total_alloted_hours" value="{{ $course->courseMaster->total_alloted_hours ?? '' }}">
                              </div>
                            </div>

                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">Internal</label>
                                <input type="number" class="form-control internal-marks" name="internal" value="{{ $course->courseMaster->internal ?? '' }}" required>
                              </div>
                            </div>

                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">External</label>
                                <input type="number" class="form-control external-marks" name="external" value="{{ $course->courseMaster->external ?? '' }}" required>
                              </div>
                            </div>

                            <div class="col-lg-4">
                              <div class="mb-3">
                                <label class="form-label">Full</label>
                                <input type="number" class="form-control full-marks" value="{{ ($course->courseMaster->internal ?? 0) + ($course->courseMaster->external ?? 0) }}" name="full" readonly>
                              </div>
                            </div>

                            <script>
                              document.querySelectorAll('.internal-marks, .external-marks').forEach(input => {
                                input.addEventListener('input', function() {
                                  const parent = this.closest('.modal-content');
                                  const internal = parseFloat(parent.querySelector('.internal-marks').value) || 0;
                                  const external = parseFloat(parent.querySelector('.external-marks').value) || 0;
                                  parent.querySelector('.full-marks').value = (internal + external);
                                });
                              });
                            </script>
                            <div class="col-lg-3">
                              <div class="mb-3">
                                <label class="form-label">Paper Type *</label>
                                <select name="paper_type" id="paper_type" class="form-select" required>
                                  <option value="">Select Paper Type</option>
                                  @foreach($papertypes as $papertype)
                                  <option value="{{ $papertype->id }}" {{ ($course->courseMaster->papertypemaster->id ?? '') == $papertype->id ? 'selected' : '' }}>{{ $papertype->name }}</option>
                                  @endforeach

                                </select>
                              </div>
                            </div>
                            <div class="col-lg-9">
                              <div class="mb-3">
                                <label class="form-label">Course Type *</label>
                                <select class="form-select dselect-example" name="course_type" required>
                                  <option value="">Select Course Type</option>
                                  @foreach($coursetypes as $type)
                                  <option value="{{ $type->id }}" {{ ($course->courseMaster->coursetypemaster->id ?? '') == $type->id ? 'selected' : '' }}>{{ $type->title }} - {{$type->description}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="col-lg-12">
                              <div class="mb-3">
                                <label class="form-label">Course Title *</label>
                                <input type="text" class="form-control" name="course_title" value="{{ $course->courseMaster->course_title ?? '' }}" required>
                              </div>
                            </div>

                            <div class="col-lg-12">
                              <div class="border rounded p-3 bg-light">
                                <div class="form-check">
                                  <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="noCoCsoApplicable{{ $course->id }}"
                                    name="co_cso_not_applicable"
                                    value="1"
                                    {{ (int) ($course->co_cso_not_applicable ?? 0) === 1 ? 'checked' : '' }}>
                                  <label class="form-check-label" for="noCoCsoApplicable{{ $course->id }}">
                                    I Hereby Declare CO and CSO not applicable for this department-course
                                  </label>
                                </div>
                                <div class="mt-2">
                                  <label class="form-label mb-1" for="noCoCsoNote{{ $course->id }}">Reason (optional)</label>
                                  <input
                                    type="text"
                                    class="form-control"
                                    id="noCoCsoNote{{ $course->id }}"
                                    name="co_cso_not_applicable_note"
                                    maxlength="255"
                                    value="{{ (string) ($course->co_cso_not_applicable_note ?? '') }}"
                                    placeholder="Example: Practical paper without CO/CSO structure">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" class="btn btn-success submit-btn edit-course-submit-btn">
                            <span class="submit-text">Update Course</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="loading-text d-none">Processing...</span>
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <form action="{{ route('department.course.delete', $course->id) }}" method="POST" style="display:inline;" id="citadel" onsubmit="return confirm('Delete this course?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
            @empty
            <tr id="courseMasterEmptyStateRow">
              <td colspan="13" class="text-center text-muted">No courses found.</td>
            </tr>
            @endforelse
            @if(count($mycourses) > 0)
            <tr id="courseMasterNoSearchResultRow" style="display:none;">
              <td colspan="13" class="text-center text-muted">No matching courses found for your search.</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
      @else
      <p class="text-center display-5 mt-5">No Records Found</p>
      @endif
    </div>
  </div>
</div>

</div>
</div>

<script>
  (function() {
    const courseCodeInput = document.getElementById('courseCode');
    const courseCodeFeedback = document.getElementById('courseCodeFeedback');
    const createCourseBtn = document.getElementById('createCourseBtn');
    const addCourseForm = document.getElementById('addCourseForm');
    const addCourseModal = document.getElementById('addCourseModal');
    const checkCodeUrl = "{{ route('department.course-master.check-code') }}";

    if (!courseCodeInput || !courseCodeFeedback || !createCourseBtn || !addCourseForm) {
      return;
    }

    let debounceTimer = null;
    let latestRequestToken = 0;
    let isCodeAvailable = false;

    function setFeedback(message, type) {
      courseCodeFeedback.classList.remove('text-muted', 'text-danger', 'text-success');

      if (type === 'error') {
        courseCodeFeedback.classList.add('text-danger');
      } else if (type === 'success') {
        courseCodeFeedback.classList.add('text-success');
      } else {
        courseCodeFeedback.classList.add('text-muted');
      }

      const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
      courseCodeFeedback.innerHTML = `<i class="fa ${iconClass} me-1"></i>${message}`;
    }

    function setCreateButtonState(enabled) {
      createCourseBtn.disabled = !enabled;
      isCodeAvailable = enabled;
    }

    async function checkCourseCodeAvailability(courseCode) {
      const requestToken = ++latestRequestToken;
      setFeedback('Checking availability...', 'neutral');
      setCreateButtonState(false);

      try {
        const response = await fetch(`${checkCodeUrl}?course_code=${encodeURIComponent(courseCode)}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        const payload = await response.json();
        if (requestToken !== latestRequestToken) {
          return;
        }

        if (!response.ok) {
          setFeedback(payload.message || 'Unable to validate course code right now.', 'error');
          setCreateButtonState(false);
          return;
        }

        if (payload.available) {
          setFeedback('Available. You can create this course.', 'success');
          setCreateButtonState(true);
        } else {
          setFeedback('Course code already exists. It cannot be created.', 'error');
          setCreateButtonState(false);
        }
      } catch (error) {
        if (requestToken !== latestRequestToken) {
          return;
        }

        setFeedback('Error checking course code. Please try again.', 'error');
        setCreateButtonState(false);
      }
    }

    courseCodeInput.addEventListener('input', function() {
      const normalizedCode = this.value.toUpperCase().trim();
      this.value = normalizedCode;

      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }

      if (normalizedCode.length === 0) {
        setFeedback('Enter a course code to check availability.', 'neutral');
        setCreateButtonState(false);
        return;
      }

      debounceTimer = setTimeout(() => {
        checkCourseCodeAvailability(normalizedCode);
      }, 350);
    });

    addCourseForm.addEventListener('submit', function(event) {
      if (!isCodeAvailable) {
        event.preventDefault();
        setFeedback('Please use an available course code before creating.', 'error');
      }
    });

    if (addCourseModal) {
      addCourseModal.addEventListener('hidden.bs.modal', function() {
        courseCodeInput.value = '';
        latestRequestToken++;
        if (debounceTimer) {
          clearTimeout(debounceTimer);
        }
        setFeedback('Enter a course code to check availability.', 'neutral');
        setCreateButtonState(false);
      });
    }
  })();

  (function() {
    const checkCodeUrl = "{{ route('department.course-master.check-code') }}";
    const editCourseForms = document.querySelectorAll('.edit-course-form');

    if (!editCourseForms.length) {
      return;
    }

    editCourseForms.forEach((form) => {
      const courseMasterId = Number(form.getAttribute('data-course-master-id') || 0);
      const codeInput = form.querySelector('.edit-course-code');
      const feedback = form.querySelector('.edit-course-code-feedback');
      const submitBtn = form.querySelector('.edit-course-submit-btn');
      const modal = form.closest('.modal');

      if (!courseMasterId || !codeInput || !feedback || !submitBtn) {
        return;
      }

      let debounceTimer = null;
      let latestRequestToken = 0;
      let isCodeAvailable = true;
      const initialCode = (codeInput.getAttribute('data-initial-code') || '').toUpperCase().trim();

      function setFeedback(message, type) {
        feedback.classList.remove('text-muted', 'text-danger', 'text-success');

        if (type === 'error') {
          feedback.classList.add('text-danger');
        } else if (type === 'success') {
          feedback.classList.add('text-success');
        } else {
          feedback.classList.add('text-muted');
        }

        const iconClass = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle');
        feedback.innerHTML = `<i class="fa ${iconClass} me-1"></i>${message}`;
      }

      function setSubmitState(enabled) {
        submitBtn.disabled = !enabled;
        isCodeAvailable = enabled;
      }

      async function checkCourseCodeAvailability(courseCode) {
        const requestToken = ++latestRequestToken;
        setFeedback('Checking availability...', 'neutral');
        setSubmitState(false);

        try {
          const response = await fetch(`${checkCodeUrl}?course_code=${encodeURIComponent(courseCode)}&exclude_id=${courseMasterId}`, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          const payload = await response.json();
          if (requestToken !== latestRequestToken) {
            return;
          }

          if (!response.ok) {
            setFeedback(payload.message || 'Unable to validate course code right now.', 'error');
            setSubmitState(false);
            return;
          }

          if (payload.available) {
            setFeedback('Course code is available.', 'success');
            setSubmitState(true);
          } else {
            setFeedback('Course code already exists. It cannot be updated to this code.', 'error');
            setSubmitState(false);
          }
        } catch (error) {
          if (requestToken !== latestRequestToken) {
            return;
          }

          setFeedback('Error checking course code. Please try again.', 'error');
          setSubmitState(false);
        }
      }

      function handleCodeInput() {
        const normalizedCode = codeInput.value.toUpperCase().trim();
        codeInput.value = normalizedCode;

        if (debounceTimer) {
          clearTimeout(debounceTimer);
        }

        if (normalizedCode.length === 0) {
          setFeedback('Course code is required.', 'error');
          setSubmitState(false);
          return;
        }

        if (normalizedCode === initialCode) {
          setFeedback('Current course code is valid.', 'success');
          setSubmitState(true);
          return;
        }

        debounceTimer = setTimeout(() => {
          checkCourseCodeAvailability(normalizedCode);
        }, 350);
      }

      codeInput.addEventListener('input', handleCodeInput);

      form.addEventListener('submit', function(event) {
        if (!isCodeAvailable) {
          event.preventDefault();
          setFeedback('Please use an available course code before updating.', 'error');
        }
      });

      if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
          latestRequestToken++;
          if (debounceTimer) {
            clearTimeout(debounceTimer);
          }
          setFeedback('Current course code is valid.', 'success');
          setSubmitState(true);
        });
      }
    });
  })();

  // Prevent multiple form submissions
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('.submit-btn');

      if (submitBtn && !submitBtn.disabled) {
        // Disable the button
        submitBtn.disabled = true;

        // Toggle visibility of text and spinner
        const submitText = submitBtn.querySelector('.submit-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        const loadingText = submitBtn.querySelector('.loading-text');

        if (submitText) submitText.classList.add('d-none');
        if (spinner) spinner.classList.remove('d-none');
        if (loadingText) loadingText.classList.remove('d-none');
      }
    });
  });

  (function() {
    const searchInput = document.getElementById('courseMasterSearchInput');
    const rows = Array.from(document.querySelectorAll('tr.course-row'));
    const noResultRow = document.getElementById('courseMasterNoSearchResultRow');
    const visibleCountElement = document.getElementById('courseMasterVisibleCount');

    if (!searchInput || rows.length === 0) {
      return;
    }

    function applyCourseFilter() {
      const term = String(searchInput.value || '').toLowerCase().trim();
      let visibleCount = 0;

      rows.forEach(function(row) {
        const haystack = String(row.getAttribute('data-course-search') || '').toLowerCase();
        const match = term === '' || haystack.includes(term);
        row.style.display = match ? '' : 'none';
        if (match) {
          visibleCount++;
        }
      });

      if (visibleCountElement) {
        visibleCountElement.textContent = String(visibleCount);
      }

      if (noResultRow) {
        noResultRow.style.display = visibleCount === 0 ? '' : 'none';
      }
    }

    searchInput.addEventListener('input', applyCourseFilter);
    applyCourseFilter();
  })();
</script>

@include('includes.footer')
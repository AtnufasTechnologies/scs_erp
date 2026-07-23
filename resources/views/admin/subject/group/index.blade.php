<?php

use App\Models\BatchMaster;

$batches = $batches ?? BatchMaster::all();
$selectedBatch = (int) ($selected_batch ?? request('batch', 0));
$offeredCoursesByProgramType = $offered_courses_by_program_type ?? collect();
?>
@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  <div class="row">
    <div class="col-lg-6">
      <div class="alert alert-info">
        <h4>Student Group Allocation</h4>
        Divind your students in groups to manage your classes in a more structured way
      </div>
    </div>
    <div class="col-lg-2">
      <form method="get">
        <div class="input-group">
          <select name="batch" class="form-control">
            <option value="">--Select--</option>
            @foreach ($batches as $batch)
            <option value="{{$batch->id}}" {{ $selectedBatch === (int) $batch->id ? 'selected' : '' }}>{{$batch->batch_name}}</option>
            @endforeach
          </select>
          <input type="hidden" name="subject_id" value="{{$subject->id}}">
          <button type="submit" class="btn-sm btn-white"><i class="fa fa-search"></i></button>
        </div>
      </form>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-lg-12">
      <div class=" ">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Offered Courses by Semester</h5>
          <span class="badge bg-info text-dark">Batch: {{ $selectedBatch > 0 ? ($batches->firstWhere('id', $selectedBatch)->batch_name ?? '-') : '-' }}</span>
        </div>
        <div class="card-body">
          @if($selectedBatch <= 0)
            <div class="alert alert-warning mb-0">Please select a batch to fetch courses.
        </div>
        @elseif($offeredCoursesByProgramType->isEmpty())
        <div class="alert alert-light border mb-0">No offered courses found for the selected batch.</div>
        @else
        @foreach($offeredCoursesByProgramType as $programType => $semesterGroups)
        <div class="card border mb-3">
          <div class="card-header d-flex justify-content-between align-items-center" style="background: #f8fafc;">
            <h6 class="mb-0">{{ $programType === 'UNSPECIFIED' ? 'Program Type: Unspecified' : ('Program Type: ' . $programType) }}</h6>
            <span class="badge {{ $programType === 'UG' ? 'bg-success' : ($programType === 'PG' ? 'bg-primary' : 'bg-secondary') }}">{{ $programType }}</span>
          </div>
          <div class="card-body">
            <div class="accordion" id="offeredCourseSemesterAccordion{{ $programType }}">
              @foreach($semesterGroups as $semesterGroup)
              @php
              $programTypeKey = preg_replace('/[^A-Za-z0-9]/', '', (string) $programType);
              $semesterDomKey = $programTypeKey . $semesterGroup['semester_id'];
              @endphp
              <div class="accordion-item mb-2 offered-course-item" data-offered-search="{{ strtolower($programType . ' ' . ($semesterGroup['semester_title'] ?? '')) }}">
                <h2 class="accordion-header" id="semesterHeading{{ $semesterDomKey }}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#semesterCollapse{{ $semesterDomKey }}" aria-expanded="false" aria-controls="semesterCollapse{{ $semesterDomKey }}">
                    <span class="fw-semibold">{{ $semesterGroup['semester_title'] }}</span>
                    <span class="badge bg-secondary ms-2">{{ count($semesterGroup['courses'] ?? []) }} courses</span>
                  </button>
                </h2>
                <div id="semesterCollapse{{ $semesterDomKey }}" class="accordion-collapse collapse" aria-labelledby="semesterHeading{{ $semesterDomKey }}" data-bs-parent="#offeredCourseSemesterAccordion{{ $programType }}">
                  <div class="accordion-body p-2 p-md-3">
                    @foreach(($semesterGroup['courses'] ?? []) as $course)
                    @php
                    $courseSearch = strtolower(
                    $programType . ' ' .
                    ($semesterGroup['semester_title'] ?? '') . ' ' .
                    ($course['course_code'] ?? '') . ' ' .
                    ($course['course_title'] ?? '') . ' ' .
                    collect($course['students'] ?? [])->pluck('roll_no')->implode(' ') . ' ' .
                    collect($course['students'] ?? [])->pluck('name')->implode(' ')
                    );
                    @endphp
                    @php
                    $courseSplitKey = preg_replace('/[^A-Za-z0-9]/', '', (string) ($programType . '-' . $semesterGroup['semester_id'] . '-' . ($course['course_id'] ?? 0)));
                    @endphp
                    <div class="border rounded p-2 p-md-3 mb-3">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div>
                          <span class="badge bg-warning text-dark">{{ $course['paper_type'] ?? '-' }}</span>
                          <span class="fw-semibold ms-2">{{ $course['course_code'] ?? '-' }} - {{ $course['course_title'] ?? '-' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          @if(!empty($course['course_types']))
                          <span class="badge bg-light text-dark border">{{ implode(', ', $course['course_types']) }}</span>
                          @endif
                          <span class="badge bg-primary">Enrolled: {{ $course['student_count'] ?? 0 }}</span>
                        </div>
                      </div>

                      <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-3">
                          <label class="form-label mb-1 small text-muted">Number of Groups</label>
                          <input type="number" class="form-control form-control-sm" id="splitValue{{ $courseSplitKey }}" min="1" value="2">
                        </div>
                        <div class="col-12 col-md-3">
                          <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="splitRandom{{ $courseSplitKey }}">
                            <label class="form-check-label small" for="splitRandom{{ $courseSplitKey }}">Randomize students</label>
                          </div>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                          <div class="d-flex justify-content-md-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCourseStudentsToDefault('{{ $courseSplitKey }}')">
                              <i class="fa fa-undo"></i> Reset Group A
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="splitCourseStudents('{{ $courseSplitKey }}')">
                              <i class="fa fa-object-group"></i> Split
                            </button>
                          </div>
                        </div>
                      </div>

                      <form method="post" action="{{ route('department.student.group.allocation.save', [$subject->id, $subject->slug ?? $subject->title ?? 'subject']) }}">
                        @csrf
                        <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                        <div id="allocationInputs{{ $courseSplitKey }}">
                          @foreach(($course['students'] ?? []) as $student)
                          @if(!empty($student['student_course_info_id']))
                          <input type="hidden" name="allocations[{{ $loop->index }}][student_course_info_id]" value="{{ $student['student_course_info_id'] }}">
                          <input type="hidden" data-group-input="true" name="allocations[{{ $loop->index }}][allocation_group_id]" value="{{ !empty($student['allocation_group_id']) ? (int) $student['allocation_group_id'] : '' }}">
                          @endif
                          @endforeach
                        </div>

                        <div class="table-responsive">
                          <table class="table table-sm table-striped mb-0">
                            <thead>
                              <tr>
                                <th style="width: 70px;">#</th>
                                <th style="width: 180px;">Roll No</th>
                                <th>Student Name</th>
                                <th style="width: 140px;">Group</th>
                              </tr>
                            </thead>
                            <tbody id="courseStudentRows{{ $courseSplitKey }}">
                              @forelse(($course['students'] ?? []) as $student)
                              <tr data-sci-id="{{ $student['student_course_info_id'] ?? 0 }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student['roll_no'] ?? '-' }}</td>
                                <td>{{ $student['name'] ?? '-' }}</td>
                                <td>
                                  @php
                                  $groupId = isset($student['allocation_group_id']) ? (int) ($student['allocation_group_id'] ?? 0) : 0;
                                  @endphp
                                  @if($groupId > 0)
                                  @php
                                  $groupText = $groupId <= 26 ? ('Group ' . chr(64 + $groupId)) : (' Group ' . $groupId);
                                  @endphp
                                  <span class="badge {{ $groupId === 1 ? 'bg-light text-dark border' : 'bg-info text-dark' }} group-label">{{ $groupText }}</span>
                                  @else
                                  <span class="badge bg-secondary group-label">Unassigned</span>
                                  @endif
                                </td>
                              </tr>
                              @empty
                              <tr>
                                <td colspan="4" class="text-center text-muted">No enrolled students in this course.</td>
                              </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>

                        <div class="mt-3 text-end">
                          <button type="submit" class="btn btn-sm btn-success" {{ empty($course['students']) ? 'disabled' : '' }}>
                            <i class="fa fa-save"></i> Save Group Allocation
                          </button>
                        </div>
                      </form>
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
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  function groupLabelFromId(groupId) {
    let n = parseInt(groupId || ' 1', 10);
                                    if (!Number.isFinite(n) || n < 1) {
                                    n=1;
                                    }

                                    let label='' ;
                                    while (n> 0) {
                                    const rem = (n - 1) % 26;
                                    label = String.fromCharCode(65 + rem) + label;
                                    n = Math.floor((n - 1) / 26);
                                    }

                                    return 'Group ' + label;
                                    }

                                    function applyGroupAssignments(courseKey, assigned) {
                                    const rowContainer = document.getElementById('courseStudentRows' + courseKey);
                                    const inputContainer = document.getElementById('allocationInputs' + courseKey);

                                    if (!rowContainer || !inputContainer) {
                                    return;
                                    }

                                    const hiddenInputs = Array.from(inputContainer.querySelectorAll('input[data-group-input="true"]'));
                                    hiddenInputs.forEach(function(input) {
                                    const match = input.name.match(/allocations\[(\d+)\]\[allocation_group_id\]/);
                                    if (!match) {
                                    return;
                                    }

                                    const index = parseInt(match[1], 10);
                                    const sciInput = inputContainer.querySelector('input[name="allocations[' + index + '][student_course_info_id]"]');
                                    const sciId = parseInt((sciInput && sciInput.value) ? sciInput.value : '0', 10);
                                    if (sciId > 0 && assigned[sciId]) {
                                    input.value = String(assigned[sciId]);
                                    }
                                    });

                                    const rows = Array.from(rowContainer.querySelectorAll('tr[data-sci-id]'));
                                    rows.forEach(function(row) {
                                    const sciId = parseInt(row.getAttribute('data-sci-id') || '0', 10);
                                    const label = row.querySelector('.group-label');
                                    if (!label || !assigned[sciId]) {
                                    return;
                                    }

                                    const groupId = assigned[sciId];
                                    label.textContent = groupLabelFromId(groupId);
                                    label.className = groupId === 1 ? 'badge bg-light text-dark border group-label' : 'badge bg-info text-dark group-label';
                                    });
                                    }

                                    function resetCourseStudentsToDefault(courseKey) {
                                    const rowContainer = document.getElementById('courseStudentRows' + courseKey);
                                    if (!rowContainer) {
                                    return;
                                    }

                                    const rows = Array.from(rowContainer.querySelectorAll('tr[data-sci-id]'));
                                    const assigned = {};

                                    rows.forEach(function(row) {
                                    const sciId = parseInt(row.getAttribute('data-sci-id') || '0', 10);
                                    if (sciId > 0) {
                                    assigned[sciId] = 1;
                                    }
                                    });

                                    applyGroupAssignments(courseKey, assigned);
                                    }

                                    function splitCourseStudents(courseKey) {
                                    const valueEl = document.getElementById('splitValue' + courseKey);
                                    const randomEl = document.getElementById('splitRandom' + courseKey);
                                    const rowContainer = document.getElementById('courseStudentRows' + courseKey);
                                    const inputContainer = document.getElementById('allocationInputs' + courseKey);

                                    if (!valueEl || !rowContainer || !inputContainer) {
                                    return;
                                    }

                                    const rows = Array.from(rowContainer.querySelectorAll('tr[data-sci-id]'));
                                    if (!rows.length) {
                                    return;
                                    }

                                    const entries = rows.map(function(row, index) {
                                    const sciId = parseInt(row.getAttribute('data-sci-id') || '0', 10);
                                    return {
                                    sciId: sciId,
                                    row: row,
                                    originalIndex: index,
                                    };
                                    }).filter(function(item) {
                                    return item.sciId > 0;
                                    });

                                    if (!entries.length) {
                                    return;
                                    }

                                    if (randomEl && randomEl.checked) {
                                    for (let i = entries.length - 1; i > 0; i--) {
                                    const j = Math.floor(Math.random() * (i + 1));
                                    const temp = entries[i];
                                    entries[i] = entries[j];
                                    entries[j] = temp;
                                    }
                                    }

                                    const raw = parseInt(valueEl.value || '0', 10);
                                    const requestedGroups = Number.isFinite(raw) && raw > 0 ? raw : 1;
                                    const groupCount = Math.max(1, Math.min(requestedGroups, entries.length));

                                    // Split into near-equal contiguous groups.
                                    const grouped = [];
                                    const baseSize = Math.floor(entries.length / groupCount);
                                    const remainder = entries.length % groupCount;
                                    let cursor = 0;

                                    for (let i = 0; i < groupCount; i++) {
                                      const currentGroupSize=baseSize + (i < remainder ? 1 : 0);
                                      const nextCursor=cursor + currentGroupSize;
                                      grouped.push(entries.slice(cursor, nextCursor));
                                      cursor=nextCursor;
                                      }

                                      const assigned={};
                                      grouped.forEach(function(groupRows, idx) {
                                      const groupId=idx + 1;
                                      groupRows.forEach(function(entry) {
                                      assigned[entry.sciId]=groupId;
                                      });
                                      });

                                      applyGroupAssignments(courseKey, assigned);
                                      }
                                      </script>

                                      @include('includes.footer')
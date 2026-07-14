@include('includes.header')
@include('admin.sidebar')
<style>
  .bulk-wrap {
    min-height: 100vh;
    background: radial-gradient(circle at top right, #eef8ff, #f7fafc 40%, #f5f7fb 100%);
    padding: 1.25rem;
  }

  .bulk-card {
    border: 1px solid #d8e2ef;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(18, 38, 63, 0.06);
  }

  .bulk-badge {
    display: inline-block;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.28rem 0.65rem;
  }

  .badge-comp {
    background: #dcfce7;
    color: #166534;
  }

  .badge-elec {
    background: #ede9fe;
    color: #5b21b6;
  }

  .stat-pill {
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    background: #f8fbff;
    border: 1px solid #dce8f8;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .mini-title {
    letter-spacing: 0.04rem;
    font-weight: 700;
    color: #1f3a56;
    text-transform: uppercase;
    font-size: 0.8rem;
  }

  .empty-box {
    border: 1px dashed #bfd2e8;
    border-radius: 12px;
    background: #f8fbff;
    color: #4d6480;
    padding: 1.2rem;
  }
</style>

@php
$autoCourses = collect($mappedCourses ?? [])->where('course_type', 'AUTO')->values();
$studentChoiceCourses = collect($mappedCourses ?? [])->where('course_type', 'STUDENT_CHOICE')->values();
$departmentChoiceCourses = collect($mappedCourses ?? [])->where('course_type', 'DEPARTMENT_CHOICE')->values();
$choiceCourses = $studentChoiceCourses->merge($departmentChoiceCourses)->values();
$oldElectives = collect(old('elective_course_ids', []))->map(fn($id) => (int) $id)->toArray();
$oldTargetScope = old('target_scope', 'all');
$oldSelectedStudent = (int) old('student_id', 0);
@endphp

<div class="main-content bulk-wrap">
  <div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-1">Bulk Course Enrollment</h4>
        <p class="text-muted mb-0">Auto-enroll mapped AUTO courses for all students in the selected program combination.</p>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="bulk-card p-3 mb-3">
      <form action="{{ route('bulk.student.course.enrollment') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-lg-6">
          <label class="form-label">Program Combination</label>
          <select name="combination_id" class="form-control dselect-example" id="combinationSelect" required>
            <option value="">Select combination</option>
            @foreach(($combinations ?? collect()) as $combination)
            <option value="{{ $combination->id }}" {{ (int)($selectedCombinationId ?? 0) === (int)$combination->id ? 'selected' : '' }}>
              {{ optional($combination->batchmaster)->batch_name }} | {{ optional($combination->studentprograminfo)->code }} - {{ optional($combination->studentprograminfo)->name }} | {{ optional($combination->campusmaster)->name }}
            </option>
            @endforeach
          </select>
          @error('combination_id')
          <span class="text-danger small">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-lg-4">
          <label class="form-label">Semester</label>
          <select name="semester_id" class="form-control" id="semesterSelect" {{ empty($selectedCombinationId) ? 'disabled' : '' }} required>
            <option value="">Select semester</option>
            @foreach(($mappedSemesters ?? collect()) as $sem)
            <option value="{{ $sem->semester }}" {{ (int)($selectedSemesterId ?? 0) === (int)$sem->semester ? 'selected' : '' }}>
              {{ optional($sem->semestermaster)->title ?? ('Semester ' . $sem->semester) }}
            </option>
            @endforeach
          </select>
          @error('semester_id')
          <span class="text-danger small">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-lg-2">
          <button type="submit" class="btn btn-primary w-100">Load Mapping</button>
        </div>
      </form>
    </div>

    @if(!empty($selectedCombination) && !empty($selectedSemesterId))
    <div class="row g-3 mb-3">
      <div class="col-lg-4">
        <div class="stat-pill">Students: <strong>{{ ($eligibleStudents ?? collect())->count() }}</strong></div>
      </div>
      <div class="col-lg-4">
        <div class="stat-pill">AUTO Courses: <strong>{{ $autoCourses->count() }}</strong></div>
      </div>
      <div class="col-lg-4">
        <div class="stat-pill">Choice Courses: <strong>{{ $choiceCourses->count() }}</strong></div>
      </div>
    </div>

    <form action="{{ route('bulk.student.course.enrollment.store') }}" method="POST">
      @csrf
      <input type="hidden" name="combination_id" value="{{ (int)$selectedCombinationId }}">
      <input type="hidden" name="semester_id" value="{{ (int)$selectedSemesterId }}">

      <div class="row g-3">
        <div class="col-12">
          <div class="bulk-card p-3">
            <div class="mini-title mb-2">Student Selection</div>
            <div class="row g-3 align-items-end">
              <div class="col-lg-6">
                <div class="form-check mb-1">
                  <input class="form-check-input" type="radio" name="target_scope" id="targetScopeAll" value="all" {{ $oldTargetScope === 'all' ? 'checked' : '' }}>
                  <label class="form-check-label" for="targetScopeAll">Enroll all eligible students</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="target_scope" id="targetScopeSingle" value="single" {{ $oldTargetScope === 'single' ? 'checked' : '' }}>
                  <label class="form-check-label" for="targetScopeSingle">Enroll only one student</label>
                </div>
                @error('target_scope')
                <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>

              <div class="col-lg-6">
                <label class="form-label">Select Student (for single mode)</label>
                <select name="student_id" id="singleStudentSelect" class="form-control dselect-example" {{ $oldTargetScope !== 'single' ? 'disabled' : '' }}>
                  <option value="">Select one student</option>
                  @foreach(($eligibleStudents ?? collect()) as $student)
                  <option value="{{ $student->id }}" {{ $oldSelectedStudent === (int)$student->id ? 'selected' : '' }}>
                    {{ $student->roll_no ?? 'NA' }} - {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                  </option>
                  @endforeach
                </select>
                @error('student_id')
                <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="bulk-card p-3 h-100">
            <div class="mini-title mb-2">Auto-Enroll AUTO Courses</div>
            @if($autoCourses->isEmpty())
            <div class="empty-box">No AUTO mapped courses found for this semester.</div>
            @else
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width: 60px;">#</th>
                    <th>Course</th>
                    <th style="width: 130px;">Type</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($autoCourses as $index => $course)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($course->programinfo)->course_code }} - {{ optional($course->programinfo)->course_title }}</td>
                    <td><span class="bulk-badge badge-comp">AUTO</span></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </div>
        </div>

        <div class="col-lg-6">
          <div class="bulk-card p-3 h-100">
            <div class="mini-title mb-2">Choice Options (Select To Enroll)</div>
            @if($choiceCourses->isEmpty())
            <div class="empty-box">No choice mapped courses found for this semester.</div>
            @else
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width: 70px;">Pick</th>
                    <th>Course</th>
                    <th style="width: 110px;">Type</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($choiceCourses as $course)
                  @php
                  $courseId = (int) $course->course_id;
                  $courseTypeLabel = strtoupper((string) $course->course_type);
                  @endphp
                  <tr>
                    <td>
                      <input type="checkbox" name="elective_course_ids[]" value="{{ $courseId }}" {{ in_array($courseId, $oldElectives, true) ? 'checked' : '' }}>
                    </td>
                    <td>{{ optional($course->programinfo)->course_code }} - {{ optional($course->programinfo)->course_title }}</td>
                    <td><span class="bulk-badge badge-elec">{{ $courseTypeLabel }}</span></td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endif
          </div>
        </div>
      </div>

      <div class="bulk-card p-3 mt-3">
        <div class="row g-3 align-items-center">
          <div class="col-lg-9">
            <div class="small text-muted">
              All eligible students in this mapped batch/program/campus will be processed. AUTO courses are always enrolled automatically. Selected choice courses above will also be enrolled.
            </div>
          </div>
          <div class="col-lg-3 text-lg-end">
            <button type="submit" class="btn btn-success w-100">Run Bulk Enrollment</button>
          </div>
        </div>
      </div>
    </form>

    <div class="bulk-card p-3 mt-3">
      <div class="mini-title mb-2">Eligible Students Preview</div>
      @if(($eligibleStudents ?? collect())->isEmpty())
      <div class="empty-box">No students found in this combination for enrollment.</div>
      @else
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 70px;">#</th>
              <th>Roll No</th>
              <th>Name</th>
            </tr>
          </thead>
          <tbody>
            @foreach(($eligibleStudents ?? collect()) as $index => $student)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $student->roll_no ?? 'NA' }}</td>
              <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
    @endif
  </div>
</div>

<script>
  (function() {
    const combination = document.getElementById('combinationSelect');
    if (!combination) return;

    combination.addEventListener('change', function() {
      const semester = document.getElementById('semesterSelect');
      if (semester) {
        semester.value = '';
      }
      this.form.submit();
    });

    const allRadio = document.getElementById('targetScopeAll');
    const singleRadio = document.getElementById('targetScopeSingle');
    const studentSelect = document.getElementById('singleStudentSelect');

    function syncStudentMode() {
      if (!studentSelect || !allRadio || !singleRadio) return;

      if (singleRadio.checked) {
        studentSelect.disabled = false;
      } else {
        studentSelect.disabled = true;
        studentSelect.value = '';
      }
    }

    if (allRadio && singleRadio && studentSelect) {
      allRadio.addEventListener('change', syncStudentMode);
      singleRadio.addEventListener('change', syncStudentMode);
      syncStudentMode();
    }
  })();
</script>


@include('includes.footer')
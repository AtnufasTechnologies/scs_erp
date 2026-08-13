@include('includes.header')
@include('includes.dept-sidebar')
<div class="main-content">
  <h4>My Specialization Master</h4>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="alert alert-warning">
    Create your own specializations and combine them with Programs. Applicants get to choose which one they want to get enrolled during admission. Note: it can only be used if status is set to <span class="badge badge-success">Active </span>
  </div>

  <form action="{{route('department.store.specialization')}}" method="post">
    @csrf
    <div class="row">
      <div class="col-lg-4 mb-3">
        <input type="text" name="name" class="form-control" placeholder="Type Title Here...">
      </div>
      <div class="col-lg-2 mb-3">
        <div class="input-group">
          <select name="status" class="form-control">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
          <input type="hidden" name="subject_id" value="{{$subject->id}}">
          <button type="submit" class="btn btn-success"><i class="fa fa-plus-circle"></i>New</button>
        </div>


      </div>
    </div>
  </form>

  <hr>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
      <h5 class="mb-0">Manual Student Specialization Assignment</h5>
      <small class="text-muted">Select batch, choose program offering specializations, filter students, and assign specialization in bulk.</small>
    </div>
    <div class="card-body">
      @php
      $selectedBatchId = (int) ($selectedBatchId ?? 0);
      $selectedProgramComboId = (int) ($selectedProgramComboId ?? 0);
      $studentSearch = $studentSearch ?? '';
      $batchOptions = $batchOptions ?? collect();
      $offeredProgramCombinations = $offeredProgramCombinations ?? collect();
      $selectedProgramCombination = $selectedProgramCombination ?? null;
      $students = $students ?? collect();
      $availableSpecializationsForSelectedProgram = $availableSpecializationsForSelectedProgram ?? collect();
      $selectedIntegratedLayer = $selectedIntegratedLayer ?? 'all';
      $showIntegratedLayerFilter = (bool) ($showIntegratedLayerFilter ?? false);
      $integratedLayerOptions = $integratedLayerOptions ?? collect();
      $integratedProgramIdsWithSublayers = collect($integratedProgramIdsWithSublayers ?? [])->map(fn($v) => (int) $v);
      $studentAssignmentMap = $studentAssignmentMap ?? collect();
      $specializationLookup = $specializationLookup ?? collect();
      @endphp

      <form method="get" action="{{ route('department.specialization.master', [$subject->id, $subject->slug ?? $subject->title ?? 'subject']) }}" class="mb-4" id="specializationFilterForm">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3 col-md-6">
            <label class="form-label">Batch</label>
            <select name="batch" id="specBatchSelect" class="form-control" required>
              <option value="">Select batch</option>
              @foreach($batchOptions as $batch)
              <option value="{{ $batch->id }}" {{ $selectedBatchId === (int) $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-4 col-md-6">
            <label class="form-label">Program (Offered with Specializations)</label>
            <select name="program_combo" id="specProgramSelect" class="form-control" {{ $selectedBatchId > 0 ? '' : 'disabled' }}>
              <option value="">Select program</option>
              @foreach($offeredProgramCombinations as $combo)
              @php

              $programName = $combo->studentprograminfo->name ?? 'Program';
              $programType = $combo->studentprograminfo->programtypemaster->name ?? ($combo->program_type ?? 'N/A');
              $programId = (int) ($combo->student_program_id ?? 0);
              $isIntegratedProgram = $integratedProgramIdsWithSublayers->contains($programId);
              @endphp
              <option value="{{ $combo->id }}" {{ $selectedProgramComboId === (int) $combo->id ? 'selected' : '' }}>
                {{ $programName }} ({{ $programType }}){{ $isIntegratedProgram ? ' - Integrated' : '' }}
              </option>
              @endforeach
            </select>
          </div>

          @if($showIntegratedLayerFilter)
          <div class="col-lg-2 col-md-6">
            <label class="form-label">Integrated Layer</label>
            <select name="integrated_layer" class="form-control">
              @foreach($integratedLayerOptions as $layer)
              <option value="{{ $layer['value'] }}" {{ $selectedIntegratedLayer === $layer['value'] ? 'selected' : '' }}>{{ $layer['label'] }}</option>
              @endforeach
            </select>
          </div>
          @endif

          <div class="col-lg-3 col-md-6">
            <label class="form-label">Search Student</label>
            <input type="text" name="student_search" class="form-control" value="{{ $studentSearch }}" placeholder="Roll no / Name">
          </div>

          <div class="col-lg-2 col-md-6">
            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter"></i> Generate</button>
          </div>
        </div>
      </form>

      @if($selectedBatchId > 0 && $offeredProgramCombinations->isEmpty())
      <div class="alert alert-info mb-0">
        No programs with active specialization mapping found for this batch.
      </div>
      @endif

      @if($selectedProgramCombination)
      @php
      $specializationWiseCounts = collect();
      $totalGeneratedStudents = $students->count();
      $assignedGeneratedStudents = 0;

      if ($students->isNotEmpty()) {
      $specializationWiseCounts = $students
      ->map(function ($student) use ($studentAssignmentMap, $specializationLookup, &$assignedGeneratedStudents) {
      $assignment = $studentAssignmentMap->get((int) $student->id);
      if (!$assignment) {
      return 'Not Assigned';
      }

      $assignedGeneratedStudents++;
      $spec = $specializationLookup->get((int) $assignment->specialization_id);
      return $spec->name ?? 'Unknown';
      })
      ->countBy()
      ->sortDesc();
      }
      @endphp

      <form method="post" action="{{ route('department.specialization.assign.students', [$subject->id, $subject->slug ?? $subject->title ?? 'subject']) }}">
        @csrf
        <input type="hidden" name="batch" value="{{ $selectedBatchId }}">
        <input type="hidden" name="program_combo_id" value="{{ $selectedProgramComboId }}">
        <input type="hidden" name="student_search" value="{{ $studentSearch }}">
        <input type="hidden" name="integrated_layer" value="{{ $selectedIntegratedLayer }}">
        <input type="hidden" name="assignment_action" id="assignmentAction" value="assign">

        <div class="alert alert-light border mb-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <strong>Student Count by Specialization (Generated List)</strong>
            <div>
              <span class="badge badge-dark">Total: {{ $totalGeneratedStudents }}</span>
              <span class="badge badge-success">Assigned: {{ $assignedGeneratedStudents }}</span>
              <span class="badge badge-secondary">Unassigned: {{ max($totalGeneratedStudents - $assignedGeneratedStudents, 0) }}</span>
            </div>
          </div>

          @if($specializationWiseCounts->isNotEmpty())
          <div class="d-flex flex-wrap gap-2">
            @foreach($specializationWiseCounts as $specializationName => $count)
            <span class="badge {{ $specializationName === 'Not Assigned' ? 'badge-secondary' : 'badge-info' }}">
              {{ $specializationName }}: {{ $count }}
            </span>
            @endforeach
          </div>
          @endif
        </div>

        <div class="row g-3 mb-3">
          <div class="col-lg-5 col-md-6">
            <label class="form-label">Choose Specialization</label>
            <select name="specialization_id" class="form-control" required>
              <option value="">Select specialization</option>
              @foreach($availableSpecializationsForSelectedProgram as $spec)
              <option value="{{ $spec->id }}">{{ $spec->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-5 col-md-6">
            <label class="form-label">Quick Filter in Generated List</label>
            <input type="text" id="studentQuickFilter" class="form-control" placeholder="Type roll number or name to filter instantly...">
          </div>

          <div class="col-lg-2 col-md-12 d-flex align-items-end">
            <div class="d-flex w-100 gap-2">
              <button type="submit" id="assignSpecializationBtn" class="btn btn-success w-100" {{ $students->isEmpty() ? 'disabled' : '' }}>
                <i class="fa fa-save"></i> Assign
              </button>
              <button type="submit" id="resetSpecializationBtn" class="btn btn-outline-danger w-100" {{ $students->isEmpty() ? 'disabled' : '' }}>
                <i class="fa fa-undo"></i> Reset
              </button>
            </div>
          </div>
        </div>

        @if($availableSpecializationsForSelectedProgram->isEmpty())
        <div class="alert alert-warning">
          No active specialization is available for the selected program. Add/activate specialization and map it to the program first.
        </div>
        @endif

        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle" id="studentSpecTable">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;">
                  <input type="checkbox" id="selectAllStudents">
                </th>
                <th style="width: 80px;">#</th>
                <th style="width: 180px;">Roll No</th>
                <th>Student Name</th>
                <th style="width: 140px;">Current Year</th>
                <th style="width: 260px;">Current Specialization</th>
              </tr>
            </thead>
            <tbody>
              @forelse($students as $index => $student)
              @php
              $currentAssignment = $studentAssignmentMap->get((int) $student->id);
              $currentSpec = $currentAssignment ? $specializationLookup->get((int) $currentAssignment->specialization_id) : null;
              @endphp
              <tr class="student-row">
                <td>
                  <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox">
                </td>
                <td>{{ $index + 1 }}</td>
                <td class="student-roll">{{ $student->roll_no ?: '-' }}</td>
                <td class="student-name">{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: '-' }}</td>
                <td>{{ (int) ($student->current_year ?? 0) > 0 ? (int) $student->current_year : '-' }}</td>
                <td>
                  @if($currentSpec)
                  <span class="badge badge-info">{{ $currentSpec->name }}</span>
                  @else
                  <span class="badge badge-secondary">Not Assigned</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted">No students found for selected batch/program.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </form>
      @endif
    </div>
  </div>

  <div class="row">
    @foreach ($data as $item)
    <div class="col-lg-3">
      <div class="card shadow">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <span class="badge {{$item->is_active == 1 ? 'badge-success': 'badge-danger'}}">{{$item->is_active == 1 ? 'Active': 'Inactive'}}</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#edit-specialization-{{$item->id}}" aria-expanded="false" aria-controls="edit-specialization-{{$item->id}}">
              Edit
            </button>
          </div>
        </div>
        <div class="card-body">
          <i class="fal fa-stars fa-4x"></i>
          <strong>
            <div class="badge badge-warning"> {{$item->slug}}</div>
          </strong> <br>
          {{$item->name}}
          <div class="collapse mt-3" id="edit-specialization-{{$item->id}}">
            <form action="{{route('department.update.specialization', $item->id)}}" method="post">
              @csrf
              @method('PUT')
              <div class="mb-2">
                <input type="text" name="name" value="{{$item->name}}" class="form-control" placeholder="Type Title Here..." required>
              </div>
              <div class="mb-2">
                <select name="status" class="form-control" required>
                  <option value="1" {{$item->is_active == 1 ? 'selected' : ''}}>Active</option>
                  <option value="0" {{$item->is_active == 0 ? 'selected' : ''}}>Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-warning btn-sm">Update</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    @endforeach
  </div>



</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var batchSelect = document.getElementById('specBatchSelect');
    var programSelect = document.getElementById('specProgramSelect');
    var filterForm = document.getElementById('specializationFilterForm');
    var selectAll = document.getElementById('selectAllStudents');
    var studentCheckboxes = document.querySelectorAll('.student-checkbox');
    var quickFilter = document.getElementById('studentQuickFilter');
    var studentRows = document.querySelectorAll('#studentSpecTable tbody .student-row');
    var assignmentAction = document.getElementById('assignmentAction');
    var assignButton = document.getElementById('assignSpecializationBtn');
    var resetButton = document.getElementById('resetSpecializationBtn');

    if (batchSelect && filterForm) {
      batchSelect.addEventListener('change', function() {
        if (programSelect) {
          programSelect.value = '';
        }
        filterForm.submit();
      });
    }

    if (selectAll) {
      selectAll.addEventListener('change', function() {
        studentCheckboxes.forEach(function(checkbox) {
          checkbox.checked = selectAll.checked;
        });
      });
    }

    if (quickFilter) {
      quickFilter.addEventListener('input', function() {
        var search = quickFilter.value.toLowerCase().trim();

        studentRows.forEach(function(row) {
          var roll = (row.querySelector('.student-roll')?.textContent || '').toLowerCase();
          var name = (row.querySelector('.student-name')?.textContent || '').toLowerCase();
          var visible = !search || roll.indexOf(search) !== -1 || name.indexOf(search) !== -1;
          row.style.display = visible ? '' : 'none';
        });
      });
    }

    if (assignButton && assignmentAction) {
      assignButton.addEventListener('click', function() {
        assignmentAction.value = 'assign';
      });
    }

    if (resetButton && assignmentAction) {
      resetButton.addEventListener('click', function(event) {
        assignmentAction.value = 'reset';

        var checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        if (checkedCount <= 0) {
          event.preventDefault();
          alert('Please select at least one student to reset specialization.');
          return;
        }

        if (!confirm('Reset specialization for selected students? This will mark them as Not Assigned.')) {
          event.preventDefault();
        }
      });
    }
  });
</script>

@include('includes.footer')
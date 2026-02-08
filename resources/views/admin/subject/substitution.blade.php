<?php

use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;

$courses = SubjectCourseMaster::where('subject_id', $data->id)->with('courseMaster.coursetypemaster')->get()->map(function ($item) {
  $item->course_title = $item->courseMaster->course_title ?? 'N/A';
  return $item;
});

$faculties = SubjectFacultyMaster::where('subject_id', $data->id)->with('faculty')->get()->map(function ($item) {
  $item->faculty_name = $item->faculty->FIRST_NAME ?? 'N/A' . ' ' . $item->faculty->LAST_NAME ?? 'N/A';;
  return $item;
});
?>
@include('includes.header')
<style>
  .custom-navbar {
    background: linear-gradient(135deg, #5740b4 0%, #8931f6 100%);
    border-radius: 0.75rem;
    box-shadow: 0 4px 16px #5740b433;
  }

  .custom-card {
    border-radius: 1rem;
    box-shadow: 0 2px 12px #5740b433;
    border: none;
    background: #fff;
  }

  .custom-table th,
  .custom-table td {
    border: 1px solid #eee;
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
    transition: background 0.2s;
  }

  .custom-table th {
    background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
    color: #5740b4;
    font-weight: 600;
    letter-spacing: 0.05em;
  }

  .custom-table tr:hover td {
    background: #f7f7fa;
  }

  .substitution-card {
    border: 2px solid #e9f0ff;
    border-radius: 0.8rem;
    padding: 1rem;
    margin: 0.5rem 0;
    background: linear-gradient(45deg, #f8f9ff 0%, #fff 100%);
    transition: all 0.3s ease;
  }

  .substitution-card:hover {
    border-color: #5740b4;
    box-shadow: 0 4px 16px #5740b433;
  }

  .btn-primary {
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    border: none;
    box-shadow: 0 2px 8px #5740b433;
    font-weight: 600;
    letter-spacing: 0.03em;
  }

  .btn-primary:hover {
    background: #8931f6;
    box-shadow: 0 4px 16px #5740b433;
  }

  .btn-success {
    background: linear-gradient(90deg, #3bb54a 0%, #6be585 100%);
    border: none;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #fff;
    box-shadow: 0 2px 8px #3bb54a33;
  }

  .btn-success:hover {
    background: #3bb54a;
    box-shadow: 0 4px 16px #3bb54a33;
  }

  .btn-warning {
    background: linear-gradient(90deg, #f39c12 0%, #f1c40f 100%);
    border: none;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #fff;
    box-shadow: 0 2px 8px #f39c1233;
  }

  .btn-warning:hover {
    background: #e67e22;
    box-shadow: 0 4px 16px #f39c1233;
  }

  .form-select,
  .form-label {
    font-size: 1.05em;
    font-weight: 500;
    letter-spacing: 0.02em;
  }

  .form-select {
    border-radius: 0.5em;
    box-shadow: 0 2px 8px #5740b433;
  }

  .card-title {
    font-size: 1.25em;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #5740b4;
  }

  .navbar-brand img {
    filter: drop-shadow(0 2px 8px #5740b433);
  }

  .substitution-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0.2rem;
  }

  .badge-original {
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    color: white;
  }

  .badge-substitute {
    background: linear-gradient(90deg, #e74c3c 0%, #f39c12 100%);
    color: white;
  }
</style>

<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / Substitution Management</span>
      </a>
      <div class="d-flex">
        @if(Auth::user()->userroletype == 'dept-admin-erp')
        <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #5740b433;">
          <i class="fa fa-step-backward me-1"></i> back
        </a>
        @else
        <a href="{{ url('erp/admin/master/view-subject?id='.$data->id.'/slug='.$data->slug) }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #5740b433;">
          <i class="fa fa-step-backward me-1"></i> back
        </a>
        @endif
      </div>
    </div>
  </nav>

  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card custom-card mb-4">
          <div class="card-body">
            <h5 class="card-title text-dark mb-3">
              <i class="fas fa-exchange-alt me-2"></i>Teacher Substitution
            </h5>
            <form id="substitutionSelectForm">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">
                    <i class="fas fa-users me-1"></i>Batch
                  </label>
                  <select name="batch_id" class="form-select" id="batchSelect" style="border-radius:0.5em;">
                    <option value="">Select Batch</option>
                    @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">
                    <i class="fas fa-calendar-day me-1"></i>Day
                  </label>
                  <select name="day" class="form-select" id="daySelect" style="border-radius:0.5em;">
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <button type="button" class="btn btn-primary w-100" id="generateSubstitutionBtn" style="border-radius:0.5em;">
                    <i class="fa fa-search me-2"></i>Generate Substitution Schedule
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Substitution Schedule Area -->
        <div id="substitutionScheduleArea" style="display: none;">
          <div class="card custom-card">
            <div class="card-body">
              <h5 class="card-title text-dark mb-3">
                <i class="fas fa-clock me-2"></i>Substitution Schedule
                <small class="text-muted ms-2" id="scheduleDetails"></small>
              </h5>
              <div class="mb-3">
                <button type="button" class="btn btn-success btn-sm me-2" onclick="saveSubstitutions()">
                  <i class="fa fa-save me-1"></i> Save All Substitutions
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="clearAllSubstitutions()">
                  <i class="fa fa-undo me-1"></i> Clear All
                </button>
              </div>
              <div id="substitutionGrid"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for substitution assignment -->
  <div class="modal fade" id="substitutionModal" tabindex="-1" aria-labelledby="substitutionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="substitutionModalLabel">Assign Substitute Teacher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-chalkboard-teacher me-1"></i>Original Teacher
            </label>
            <input type="text" class="form-control" id="originalTeacher" readonly style="border-radius:0.5em; background-color: #f8f9fa;">
          </div>
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-user-plus me-1"></i>Substitute Teacher
            </label>
            <select class="form-select" id="substituteTeacher" style="border-radius:0.5em;">
              <option value="">Select Substitute Teacher</option>
              @foreach ($faculties ?? [] as $fac)
              <option value="{{ $fac->faculty->id ?? $fac->id }}">{{ $fac->faculty->USER_CODE ?? $fac->id }} - {{ $fac->faculty->FIRST_NAME ?? $fac->id }} {{ $fac->faculty->LAST_NAME ?? $fac->id }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-comment me-1"></i>Reason for Substitution
            </label>
            <input type="text" class="form-control" id="substitutionReason" placeholder="Enter reason..." style="border-radius:0.5em;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:0.5em;">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveSubstitutionBtn" style="border-radius:0.5em;">
            <i class="fa fa-save me-1"></i>Assign Substitute
          </button>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" id="subjectIdInput" value="{{ $data->id }}">

  <script>
    let substitutionData = [];
    let originalScheduleData = [];

    // Document ready
    document.addEventListener('DOMContentLoaded', function() {
      // Setup generate button
      const generateBtn = document.getElementById('generateSubstitutionBtn');
      if (generateBtn) {
        generateBtn.addEventListener('click', function() {
          const batchId = document.getElementById('batchSelect').value;
          const day = document.getElementById('daySelect').value;

          if (!batchId || !day) {
            alert('Please select both Batch and Day');
            return;
          }

          loadOriginalSchedule(batchId, day);
        });
      }

      // Setup save substitution button
      const saveSubstitutionBtn = document.getElementById('saveSubstitutionBtn');
      if (saveSubstitutionBtn) {
        saveSubstitutionBtn.addEventListener('click', function() {
          saveSubstitution();
        });
      }
    });

    function loadOriginalSchedule(batchId, day) {
      const subjectId = document.getElementById('subjectIdInput').value;

      console.log('Loading schedule for batch:', batchId, 'day:', day);

      // Show loading state
      const generateBtn = document.getElementById('generateSubstitutionBtn');
      generateBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Loading...';
      generateBtn.disabled = true;

      // Fetch original timetable data for the selected day
      const loadUrl = `{{ route("department.timetable.data", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}`
        .replace('BATCH_ID', batchId)
        .replace('SEMESTER_ID', '1'); // Default to first semester or adjust as needed

      fetch(loadUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          console.log('Schedule data loaded:', response);
          if (response.success) {
            originalScheduleData = (response.data || []).filter(item => item.day_of_week === day);
            renderSubstitutionSchedule(batchId, day);
          } else {
            alert('No schedule found for selected batch and day');
            console.error('Failed to load schedule:', response.message);
          }
        })
        .catch(err => {
          console.error('Error loading schedule:', err);
          alert('Error loading schedule. Please try again.');
        })
        .finally(() => {
          generateBtn.innerHTML = '<i class="fa fa-search me-2"></i>Generate Substitution Schedule';
          generateBtn.disabled = false;
        });
    }

    function renderSubstitutionSchedule(batchId, day) {
      const scheduleArea = document.getElementById('substitutionScheduleArea');
      const scheduleDetails = document.getElementById('scheduleDetails');
      const substitutionGrid = document.getElementById('substitutionGrid');

      // Update schedule details
      const batchName = document.querySelector(`#batchSelect option[value="${batchId}"]`).textContent;
      scheduleDetails.textContent = `${batchName} - ${day}`;

      if (originalScheduleData.length === 0) {
        substitutionGrid.innerHTML = `
          <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            No scheduled classes found for ${batchName} on ${day}
          </div>
        `;
        scheduleArea.style.display = 'block';
        return;
      }

      let gridHTML = '';

      originalScheduleData.forEach((schedule, index) => {
        const isSubstituted = substitutionData.some(sub =>
          sub.routine_id === schedule.routine_id ||
          (sub.hour_number === schedule.hour_number && sub.day_of_week === schedule.day_of_week)
        );

        const substitution = substitutionData.find(sub =>
          sub.routine_id === schedule.routine_id ||
          (sub.hour_number === schedule.hour_number && sub.day_of_week === schedule.day_of_week)
        );

        gridHTML += `
          <div class="substitution-card ${isSubstituted ? 'border-warning' : ''}">
            <div class="row align-items-center">
              <div class="col-md-3">
                <h6 class="mb-1">
                  <i class="fas fa-clock me-1"></i>Hour ${schedule.hour_number}
                </h6>
                <div class="fw-bold text-primary">${schedule.course_title || schedule.subject_name || 'No Course'}</div>
              </div>
              <div class="col-md-4">
                <div class="mb-1">
                  <span class="substitution-badge badge-original">
                    <i class="fas fa-user me-1"></i>Original: ${schedule.teacher_name || 'No Teacher'}
                  </span>
                </div>
                ${isSubstituted ? `
                  <span class="substitution-badge badge-substitute">
                    <i class="fas fa-user-plus me-1"></i>Substitute: ${substitution.substitute_name}
                  </span>
                ` : ''}
              </div>
              <div class="col-md-3">
                ${substitution?.reason ? `
                  <small class="text-muted"><i class="fas fa-comment me-1"></i>${substitution.reason}</small>
                ` : ''}
              </div>
              <div class="col-md-2 text-end">
                <button class="btn btn-sm ${isSubstituted ? 'btn-warning' : 'btn-primary'}" 
                        onclick="openSubstitutionModal(${index})">
                  <i class="fas fa-${isSubstituted ? 'edit' : 'plus-circle'} me-1"></i>
                  ${isSubstituted ? 'Edit' : 'Assign'}
                </button>
                ${isSubstituted ? `
                  <button class="btn btn-sm btn-danger ms-1" 
                          onclick="removeSubstitution(${index})">
                    <i class="fas fa-times"></i>
                  </button>
                ` : ''}
              </div>
            </div>
          </div>
        `;
      });

      substitutionGrid.innerHTML = gridHTML;
      scheduleArea.style.display = 'block';
    }

    function openSubstitutionModal(scheduleIndex) {
      const schedule = originalScheduleData[scheduleIndex];
      const existingSubstitution = substitutionData.find(sub =>
        sub.routine_id === schedule.routine_id ||
        (sub.hour_number === schedule.hour_number && sub.day_of_week === schedule.day_of_week)
      );

      document.getElementById('originalTeacher').value = schedule.teacher_name || 'No Teacher Assigned';
      document.getElementById('substituteTeacher').value = existingSubstitution?.substitute_teacher_id || '';
      document.getElementById('substitutionReason').value = existingSubstitution?.reason || '';

      // Store current schedule index for saving
      document.getElementById('saveSubstitutionBtn').setAttribute('data-schedule-index', scheduleIndex);

      const modal = new bootstrap.Modal(document.getElementById('substitutionModal'));
      modal.show();
    }

    function saveSubstitution() {
      const scheduleIndex = document.getElementById('saveSubstitutionBtn').getAttribute('data-schedule-index');
      const schedule = originalScheduleData[scheduleIndex];
      const substituteTeacherId = document.getElementById('substituteTeacher').value;
      const reason = document.getElementById('substitutionReason').value;

      if (!substituteTeacherId) {
        alert('Please select a substitute teacher');
        return;
      }

      const substituteTeacherName = document.querySelector(`#substituteTeacher option[value="${substituteTeacherId}"]`).textContent;

      // Remove existing substitution if any
      substitutionData = substitutionData.filter(sub =>
        sub.routine_id !== schedule.routine_id &&
        !(sub.hour_number === schedule.hour_number && sub.day_of_week === schedule.day_of_week)
      );

      // Add new substitution
      substitutionData.push({
        routine_id: schedule.routine_id,
        hour_number: schedule.hour_number,
        day_of_week: schedule.day_of_week,
        original_teacher_id: schedule.teacher_id,
        substitute_teacher_id: substituteTeacherId,
        substitute_name: substituteTeacherName,
        reason: reason
      });

      // Re-render the schedule
      const batchId = document.getElementById('batchSelect').value;
      const day = document.getElementById('daySelect').value;
      renderSubstitutionSchedule(batchId, day);

      // Hide modal
      bootstrap.Modal.getInstance(document.getElementById('substitutionModal')).hide();
    }

    function removeSubstitution(scheduleIndex) {
      const schedule = originalScheduleData[scheduleIndex];

      // Remove substitution
      substitutionData = substitutionData.filter(sub =>
        sub.routine_id !== schedule.routine_id &&
        !(sub.hour_number === schedule.hour_number && sub.day_of_week === schedule.day_of_week)
      );

      // Re-render the schedule
      const batchId = document.getElementById('batchSelect').value;
      const day = document.getElementById('daySelect').value;
      renderSubstitutionSchedule(batchId, day);
    }

    function saveSubstitutions() {
      if (substitutionData.length === 0) {
        alert('No substitutions to save');
        return;
      }

      console.log('Saving substitutions:', substitutionData);
      // TODO: Implement API call to save substitutions
      alert('Substitution saving functionality will be implemented');
    }

    function clearAllSubstitutions() {
      if (confirm('Are you sure you want to clear all substitutions?')) {
        substitutionData = [];
        const batchId = document.getElementById('batchSelect').value;
        const day = document.getElementById('daySelect').value;
        renderSubstitutionSchedule(batchId, day);
      }
    }
  </script>

  @include('includes.footer')
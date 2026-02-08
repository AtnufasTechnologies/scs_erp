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

  .add-slot-btn {
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px #5740b433;
  }

  .add-slot-btn:hover {
    background: #5740b4;
    color: #fff;
    box-shadow: 0 4px 16px #5740b433;
  }

  .slot-info .badge {
    font-size: 0.95em;
    padding: 0.5em 1em;
    border-radius: 0.5em;
    margin-top: 0.25em;
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    color: #fff;
    box-shadow: 0 2px 8px #5740b433;
  }

  .modal-content {
    border-radius: 1rem;
    box-shadow: 0 4px 24px #5740b433;
  }

  .modal-header {
    background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
  }

  .modal-title {
    color: #5740b4;
    font-weight: 700;
    letter-spacing: 0.03em;
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
</style>
<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">{{ $data->code ?? '-' }} - {{ $data->title ?? '-' }} / TimeTable Management</span>
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
  </nav>

  <div class="container-fluid py-4">
    <div class="row">
      <div class="col-12">
        <div class="card custom-card mb-4">
          <div class="card-body">
            <h5 class="card-title text-dark mb-3">Select Batch & Semester</h5>
            <form id="timetableSelectForm">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">Batch</label>
                  <select name="batch_id" class="form-select" id="batchSelect" style="border-radius:0.5em;">
                    @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Semester</label>
                  <select name="semester_id" class="form-select" id="semesterSelect" style="border-radius:0.5em;">
                    @foreach ($semesterMasters as $sem)
                    <option value="{{ $sem->id }}">{{ $sem->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <button type="button" class="btn btn-primary w-100" id="generateTimetableBtn" style="border-radius:0.5em;">
                    <i class="fa fa-table"></i> Generate Timetable
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div id="timetableGridArea"></div>
      </div>
    </div>
  </div>

  <!-- Modal for slot assignment -->
  <div class="modal fade" id="slotModal" tabindex="-1" aria-labelledby="slotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="slotModalLabel">Assign Course & Teacher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Course</label>
            <select class="form-select" id="modalCourse" style="border-radius:0.5em;">
              <option value="">Select Course</option>
              @foreach ($courses ?? [] as $course)
              <option value="{{ $course->courseMaster->id ?? $course->id }}">{{$course->courseMaster->coursetypemaster->title ?? ''}} - {{ $course->courseMaster->course_code ??'-'}} - {{ $course->courseMaster->course_title ?? $course->courseMaster->course_title}}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Teacher</label>
            <select class="form-select" id="modalTeacher" style="border-radius:0.5em;">
              <option value="">Select Teacher</option>
              @foreach ($faculties ?? [] as $fac)
              <option value="{{ $fac->faculty->id ?? $fac->id }}">{{ $fac->faculty->USER_CODE ?? $fac->id }} -{{ $fac->faculty->FIRST_NAME ?? $fac->id }}{{ $fac->faculty->LAST_NAME ?? $fac->id }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:0.5em;">Close</button>
          <button type="button" class="btn btn-primary" id="saveSlotBtn" style="border-radius:0.5em;">Save Slot</button>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" id="subjectIdInput" value="{{ $data->id }}">

  <script>
    let timetableData = [];
    const totalHours = 6;
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Global function to test remove slot
    window.testRemoveSlot = function(routineId, hour, day) {
      console.log('Testing remove slot with routine ID:', routineId, 'Hour:', hour, 'Day:', day);
      removeSlot(null, routineId, hour, day);
    };

    // Global function to test clear all
    window.testClearAll = function() {
      console.log('Testing clear all');
      clearTimetable();
    };

    function loadTimetable() {
      const batchId = document.getElementById('batchSelect')?.value;
      const semesterId = document.getElementById('semesterSelect')?.value;

      if (!batchId || !semesterId) {
        alert('Please select Batch and Semester');
        return;
      }

      console.log('Loading timetable for batch:', batchId, 'semester:', semesterId);

      // Fetch existing timetable data from backend
      const loadUrl = '{{ route("department.timetable.data", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'.replace('BATCH_ID', batchId).replace('SEMESTER_ID', semesterId);

      fetch(loadUrl, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(response => {
          console.log('Timetable data loaded:', response);
          if (response.success) {
            timetableData = response.data || [];
            renderTimetable();
          } else {
            console.error('Failed to load timetable:', response.message);
            timetableData = [];
            renderTimetable();
          }
        })
        .catch(err => {
          console.error('Error loading timetable:', err);
          // Initialize empty timetable on error
          timetableData = [];
          renderTimetable();
        });
    }

    function renderTimetable() {
      const timetableGridArea = document.getElementById('timetableGridArea');
      const batchId = document.getElementById('batchSelect').value;
      const semesterId = document.getElementById('semesterSelect').value;

      if (!batchId || !semesterId) {
        return;
      }

      let tableBody = '';
      for (let hour = 1; hour <= totalHours; hour++) {
        tableBody += `
          <tr>
            <td class="text-center fw-bold" style="background:#f3e9ff;color:#5740b4;font-weight:600;">
              <strong>Hour ${hour}</strong>
            </td>
        `;

        days.forEach(day => {
          const existing = timetableData.find(t =>
            t.hour_number == hour && t.day_of_week == day
          );

          if (existing) {
            tableBody += `
              <td class="period-cell filled" style="position:relative;cursor:pointer;" onclick="openSubjectModal(${hour}, '${day}')">
                <button class="btn btn-sm btn-danger" style="position:absolute;top:2px;right:2px;padding:2px 6px;z-index:10;" onclick="event.stopPropagation(); removeSlot(event, ${existing.routine_id || 'null'}, ${hour}, '${day}'); return false;">
                  <i class="fas fa-times"></i>
                </button>
                <div class="subject-card">
                  <strong style="font-size:12px;">${existing.subject_name || 'Subject'}</strong>
                  <small class="d-block"><i class="fas fa-user"></i> ${existing.teacher_name || 'Teacher'}</small>
                </div>
              </td>
            `;
          } else {
            tableBody += `
              <td class="period-cell" onclick="openSubjectModal(${hour}, '${day}')" style="cursor:pointer;padding:15px;">
                <small class="text-muted"><i class="fas fa-plus-circle"></i> Add</small>
              </td>
            `;
          }
        });

        tableBody += `</tr>`;
      }

      timetableGridArea.innerHTML = `
        <div class='card custom-card'>
          <div class='card-body'>
            <h5 class='card-title text-dark mb-3'>Timetable Grid</h5>
            <div class="mb-3">
              <button type="button" class="btn btn-warning btn-sm me-2" onclick="copyFromDay()">
                <i class="fa fa-copy"></i> Copy Day
              </button>
              <button type="button" class="btn btn-danger btn-sm me-2" onclick="clearTimetable()">
                <i class="fa fa-trash"></i> Clear All
              </button>
              <button type="button" class="btn btn-primary btn-sm" onclick="saveTimetable()">
                <i class="fa fa-save"></i> Save Timetable
              </button>
            </div>
            <div class='table-responsive'>
              <table class='table custom-table align-middle' id='timetableGrid'>
                <thead>
                  <tr>
                    <th>Hour</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                    <th>Saturday</th>
                  </tr>
                </thead>
                <tbody>
                  ${tableBody}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    }

    function openSubjectModal(hourNumber, day) {
      document.getElementById('selectedHour').value = hourNumber;
      document.getElementById('selectedDay').value = day;

      // Check if slot already has data
      const existing = timetableData.find(t =>
        t.hour_number == hourNumber && t.day_of_week == day
      );

      if (existing) {
        document.getElementById('modalCourse').value = existing.subject_id;
        document.getElementById('modalTeacher').value = existing.teacher_id;
      } else {
        document.getElementById('modalCourse').value = '';
        document.getElementById('modalTeacher').value = '';
      }

      const modal = new bootstrap.Modal(document.getElementById('slotModal'));
      modal.show();
    }

    function saveSlot() {
      const hourNumber = document.getElementById('selectedHour').value;
      const day = document.getElementById('selectedDay').value;
      const subjectId = document.getElementById('modalCourse').value;
      const teacherId = document.getElementById('modalTeacher').value;

      if (!subjectId) {
        alert('Please select a subject');
        return;
      }

      const subjectText = document.querySelector(`#modalCourse option[value="${subjectId}"]`).textContent;
      const teacherText = teacherId ? document.querySelector(`#modalTeacher option[value="${teacherId}"]`).textContent : '';

      // Remove existing if any
      timetableData = timetableData.filter(t =>
        !(t.hour_number == hourNumber && t.day_of_week == day)
      );

      // Add new entry
      timetableData.push({
        hour_number: parseInt(hourNumber),
        day_of_week: day,
        subject_id: subjectId,
        teacher_id: teacherId || null,
        subject_name: subjectText,
        teacher_name: teacherText,
        course_title: subjectText // Add course_title for consistency
      });

      renderTimetable();
      bootstrap.Modal.getInstance(document.getElementById('slotModal')).hide();
    }

    function removeSlot(event, routineId, hourNumber, day) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      console.log('Removing slot with routine ID:', routineId, 'Hour:', hourNumber, 'Day:', day);

      if (confirm('Remove this slot?')) {
        if (routineId && routineId !== 'null') {
          // Delete from database using routine ID
          console.log('Deleting routine with ID:', routineId);

          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          const deleteUrl = '{{ route("department.timetable.delete", "ROUTINE_ID") }}'.replace('ROUTINE_ID', routineId);

          fetch(deleteUrl, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              console.log('Delete response:', data);
              if (data.success) {
                // Remove from local array
                timetableData = timetableData.filter(t => t.routine_id != routineId);
                renderTimetable();
                console.log('Slot deleted successfully');
              } else {
                alert('Error deleting slot: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error deleting slot:', error);
              alert('Error deleting slot. Please try again.');
            });
        } else {
          // Fallback to local removal for unsaved entries
          console.log('Removing unsaved entry locally');
          const initialLength = timetableData.length;
          timetableData = timetableData.filter(t => {
            const shouldKeep = !(parseInt(t.hour_number) === parseInt(hourNumber) && t.day_of_week === day);
            return shouldKeep;
          });
          console.log('Items removed:', initialLength - timetableData.length);
          renderTimetable();
        }
      }

      return false;
    }

    function saveTimetable() {
      const batchId = document.getElementById('batchSelect').value;
      const semesterId = document.getElementById('semesterSelect').value;

      if (!batchId || !semesterId) {
        alert('Please select Batch and Semester');
        return;
      }

      if (timetableData.length === 0) {
        alert('Please add at least one hour to the timetable');
        return;
      }

      const data = {
        batch_id: batchId,
        semester_id: semesterId,
        timetable: timetableData
      };

      console.log('Saving timetable data:', data);

      // Debug: Check if URL is constructed correctly
      const saveUrl = '{{ route("department.timetable.store", [$data->id, "BATCH_ID", "SEMESTER_ID"]) }}'.replace('BATCH_ID', batchId).replace('SEMESTER_ID', semesterId);
      console.log('Save URL:', saveUrl);

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page and try again.');
        return;
      }

      const saveButton = document.querySelector('button[onclick="saveTimetable()"]');
      if (saveButton) {
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
      }

      fetch(saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify(data)
        })
        .then(async response => {
          console.log('Raw response:', response);
          console.log('Response status:', response.status);

          const contentType = response.headers.get('content-type');
          console.log('Content type:', contentType);

          if (contentType && contentType.indexOf('application/json') !== -1) {
            return response.json();
          } else {
            // Handle non-JSON response
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Server returned non-JSON response: ' + text);
          }
        })
        .then(response => {
          console.log('Response received:', response);
          if (response.success) {
            alert('Timetable saved successfully!');
            // Reload timetable data instead of full page reload
            loadTimetable();
          } else {
            alert('Error: ' + (response.message || 'Unknown error'));
          }
        })
        .catch(err => {
          console.error('Error saving timetable:', err);
          alert('Error saving timetable: ' + err.message);
        })
        .finally(() => {
          // Re-enable button
          if (saveButton) {
            saveButton.disabled = false;
            saveButton.innerHTML = '<i class="fa fa-save"></i> Save Timetable';
          }
        });
    }

    function clearTimetable() {
      if (confirm('Are you sure you want to clear the entire timetable? This will permanently delete all timetable data for this subject/batch/semester.')) {
        const subjectId = document.getElementById('subjectIdInput').value;
        const batchId = document.getElementById('batchSelect').value;
        const semesterId = document.getElementById('semesterSelect').value;

        if (!batchId || !semesterId) {
          alert('Please select Batch and Semester first');
          return;
        }

        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Clearing...';
        button.disabled = true;

        fetch(`{{ route('department.timetable.clear', ['subjectId' => 'SUBJECT_ID', 'batchId' => 'BATCH_ID', 'semesterId' => 'SEMESTER_ID']) }}`
            .replace('SUBJECT_ID', subjectId)
            .replace('BATCH_ID', batchId)
            .replace('SEMESTER_ID', semesterId), {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
              }
            })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              timetableData = [];
              renderTimetable();
              alert(data.message || 'Timetable cleared successfully');
            } else {
              alert(data.message || 'Failed to clear timetable');
            }
          })
          .catch(error => {
            console.error('Error clearing timetable:', error);
            alert('An error occurred while clearing the timetable');
          })
          .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
          });
      }
    }

    function copyFromDay() {
      const sourceDay = prompt('Copy from which day? (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday)');
      if (!sourceDay || !days.includes(sourceDay)) {
        alert('Invalid day name');
        return;
      }

      const targetDay = prompt('Copy to which day? (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday)');
      if (!targetDay || !days.includes(targetDay)) {
        alert('Invalid day name');
        return;
      }

      const sourcePeriods = timetableData.filter(t => t.day_of_week === sourceDay);

      // Remove existing target day periods
      timetableData = timetableData.filter(t => t.day_of_week !== targetDay);

      // Copy periods
      sourcePeriods.forEach(period => {
        timetableData.push({
          ...period,
          day_of_week: targetDay
        });
      });

      renderTimetable();
    }

    // Document ready
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM Content Loaded - Setting up event listeners');

      // Add hidden inputs for modal
      if (!document.getElementById('selectedHour')) {
        const hiddenInputs = `
          <input type="hidden" id="selectedHour" value="">
          <input type="hidden" id="selectedDay" value="">
        `;
        document.body.insertAdjacentHTML('beforeend', hiddenInputs);
      }

      // Setup generate button with retry mechanism
      function setupGenerateButton() {
        const generateBtn = document.getElementById('generateTimetableBtn');
        console.log('Looking for generate button:', generateBtn);

        if (generateBtn) {
          console.log('Generate button found, adding event listener');
          generateBtn.addEventListener('click', function(e) {
            console.log('Generate button clicked!');
            e.preventDefault();

            const batchId = document.getElementById('batchSelect').value;
            const semesterId = document.getElementById('semesterSelect').value;

            console.log('Batch ID:', batchId, 'Semester ID:', semesterId);

            if (!batchId || !semesterId) {
              alert('Please select both batch and semester');
              return;
            }

            loadTimetable();
          });
          return true;
        } else {
          console.log('Generate button not found, will retry...');
          return false;
        }
      }

      // Try to setup generate button immediately
      if (!setupGenerateButton()) {
        // If button not found, try again after a short delay
        setTimeout(setupGenerateButton, 100);
      }

      // Setup save button
      const saveSlotBtn = document.getElementById('saveSlotBtn');
      if (saveSlotBtn) {
        saveSlotBtn.addEventListener('click', saveSlot);
      }
    });
  </script>

  @include('includes.footer')
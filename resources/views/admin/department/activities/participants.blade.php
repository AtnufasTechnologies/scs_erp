@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <h3 class="text-primary">{{ $activity->title }}</h3>
  <p>Date: {{ $activity->formatted_date }}</p>
  <h4>Participants - {{ $activity->participants->count() ?? '0' }}</h4>
  <button class="btn btn-sm btn-success mb-3" data-bs-toggle="modal" data-bs-target="#appParticipantsModal">
    <i class="fas fa-plus-circle"></i>Participants
  </button>

  <!-- View Activity Modal -->
  <div class="modal fade" id="appParticipantsModal" tabindex="-1" aria-labelledby="appParticipantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="appParticipantsModalLabel">Activity Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('department.activities.participants.store', $activity->id)}}" method="post" id="participantForm">
          @csrf
          <div class="modal-body" style="padding: 24px;">
            <div class="row">
              <div class="col-lg-3">
                <div class="mb-3">
                  <label for="participantType">Participant Type</label>
                  <select name="participant_type" id="participantType" class="form-select">
                    <option value="">--Select--</option>
                    <option value="internal">Internal</option>
                    <option value="external">External</option>
                  </select>
                </div>
              </div>

              <div class="col-lg-3">
                <div class="mb-3">
                  <label for="participantCategory">Participant Category</label>
                  <select name="participant_category" id="participantCategory" class="form-select">
                    <option value="">--Select--</option>
                    <option value="faculty">Faculty</option>
                    <option value="student">Student</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="institutionName">Institution Name</label>
                  <input type="text" class="form-control" id="institutionName" name="institution_name">
                </div>
              </div>
            </div>
            <hr>
            <strong>Add Participant Details</strong>

            <div class="row">
              <div class="col-lg-12" id="internalFacultyContainer" style="display: none;">
                <div class="mb-3">
                  <label for="internalFacultySelect" class="form-label">Select Department Faculty</label>
                  <select class="form-select" id="internalFacultySelect" name="internal_faculty_id">
                    <option value="">-- Select Faculty --</option>
                    @foreach(($internalFaculties ?? []) as $faculty)
                    <option value="{{ $faculty['id'] }}"
                      data-name="{{ $faculty['name'] }}"
                      data-email="{{ $faculty['email'] ?? '' }}"
                      data-phone="{{ $faculty['phone'] ?? '' }}">
                      {{ $faculty['name'] }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-lg-12" id="inchargeCheckboxContainer" style="display: none;">
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" value="1" id="isIncharge" name="is_incharge">
                  <label class="form-check-label" for="isIncharge">
                    Mark selected internal faculty as Incharge
                  </label>
                  <div class="form-text">Leave unchecked to store as Participant.</div>
                </div>
              </div>

              <div class="col-lg-6" id="hoursSpentContainer" style="display: none;">
                <div class="mb-3">
                  <label for="hoursSpent" class="form-label">Hours Spent at Activity <span class="text-danger">*</span></label>
                  <input type="number" step="0.25" min="0" max="999.99" class="form-control" id="hoursSpent" name="hours_spent" placeholder="e.g. 3.50">
                  <div class="form-text">Required only when selected faculty is marked as incharge.</div>
                </div>
              </div>

              <div class="col-lg-12" id="internalStudentContainer" style="display: none;">
                <div class="mb-3">
                  <label for="internalStudentSelect" class="form-label">Select Department Student</label>
                  <select class="dselect-example" id="internalStudentSelect" name="internal_student_id">
                    <option value="">-- Select Student --</option>
                    @foreach(($internalStudents ?? []) as $student)
                    <option value="{{ $student['id'] }}"
                      data-name="{{ $student['name'] }}"
                      data-email="{{ $student['email'] ?? '' }}"
                      data-phone="{{ $student['phone'] ?? '' }}"
                      data-rollno="{{ $student['roll_no'] ?? '' }}">
                      {{ $student['name'] }}{{ !empty($student['roll_no']) ? ' - ' . $student['roll_no'] . '' : '' }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="participantName" class="form-label"> Name *</label>
                  <input type="text" class="form-control" id="participantName" name="participant_name">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="participantEmail" class="form-label"> Email</label>
                  <input type="email" class="form-control" id="participantEmail" name="participant_email">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="mb-3">
                  <label for="participantPhone" class="form-label"> Phone</label>
                  <input type="text" class="form-control" id="participantPhone" name="participant_phone">
                </div>
              </div>
              <div class="col-lg-6" id="rollNoContainer" style="display: none;">
                <div class="mb-3">
                  <label for="studentRollNo" class="form-label">Student RollNo (Linking for Students)</label>
                  <input type="text" class="form-control" id="studentRollNo" name="participant_rollno">
                </div>
              </div>
              <input type="hidden" name="activityId" value="{{ $activity->id }}">
            </div>


          </div>
          <div class=" modal-footer">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-modern table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Type</th>
          <th>Role</th>
          <th>Hours Spent</th>
          <th>Institution</th>
          <th>Category</th>
          <th>Joined At</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        @forelse($participants as $item)
        <tr>
          <td>{{$loop->iteration}}</td>
          <td><span class="text-capitalize">{{ $item->participant_name }}</span></td>
          <td>{{ $item->participant_email }}</td>
          <td>{{ $item->participant_phone }}</td>
          <td>{{ $item->participation_type }}</td>
          <td>
            @if($item->participation_type === 'internal' && $item->participant_category === 'faculty')
            <span class="badge {{ $item->is_incharge ? 'bg-primary' : 'bg-secondary' }}">
              {{ $item->is_incharge ? 'Incharge' : 'Participant' }}
            </span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td>
            @if($item->participation_type === 'internal' && $item->participant_category === 'faculty' && $item->is_incharge)
            <form action="{{ route('department.activities.participants.hours.update', $item->id) }}" method="POST" class="d-flex align-items-center gap-2">
              @csrf
              <input
                type="number"
                name="hours_spent"
                class="form-control form-control-sm"
                style="max-width: 110px;"
                min="0"
                max="999.99"
                step="0.25"
                value="{{ !is_null($item->hours_spent) ? number_format((float)$item->hours_spent, 2, '.', '') : '' }}"
                placeholder="Hours"
                required>
              <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
            </form>
            @if(!is_null($item->hours_spent))
            <small class="text-muted">Current: {{ number_format((float)$item->hours_spent, 2) }} hrs</small>
            @endif
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td>{{ $item->institution_name }}</td>
          <td>{{ $item->participant_category }}</td>
          <td>{{ $item->created_at->format('d M Y, h:i A') }}</td>
          <td>
            <form action="{{ route('department.activities.participants.remove', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this participant?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Remove</button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="11" class="text-center">No participants found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>


  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const participantType = document.getElementById('participantType');
      const participantCategory = document.getElementById('participantCategory');
      const institutionName = document.getElementById('institutionName');
      const rollNoContainer = document.getElementById('rollNoContainer');
      const participantName = document.getElementById('participantName');
      const participantEmail = document.getElementById('participantEmail');
      const participantPhone = document.getElementById('participantPhone');
      const participantRollNo = document.getElementById('studentRollNo');
      const internalFacultyContainer = document.getElementById('internalFacultyContainer');
      const internalStudentContainer = document.getElementById('internalStudentContainer');
      const inchargeCheckboxContainer = document.getElementById('inchargeCheckboxContainer');
      const hoursSpentContainer = document.getElementById('hoursSpentContainer');
      const internalFacultySelect = document.getElementById('internalFacultySelect');
      const internalStudentSelect = document.getElementById('internalStudentSelect');
      const isIncharge = document.getElementById('isIncharge');
      const hoursSpent = document.getElementById('hoursSpent');
      const participantForm = document.getElementById('participantForm');

      function fillParticipantFields(name, email, phone, rollNo) {
        participantName.value = name || '';
        participantEmail.value = email || '';
        participantPhone.value = phone || '';
        participantRollNo.value = rollNo || '';
      }

      function toggleFieldLock(isLocked) {
        participantName.readOnly = isLocked;
        participantEmail.readOnly = isLocked;
        participantPhone.readOnly = isLocked;
      }

      function resetInternalSelectionUI() {
        internalFacultyContainer.style.display = 'none';
        internalStudentContainer.style.display = 'none';
        inchargeCheckboxContainer.style.display = 'none';
        hoursSpentContainer.style.display = 'none';
        internalFacultySelect.value = '';
        internalStudentSelect.value = '';
        isIncharge.checked = false;
        hoursSpent.value = '';
        hoursSpent.required = false;
      }

      function updateInchargeHoursVisibility() {
        const shouldShow = participantType.value === 'internal' && participantCategory.value === 'faculty' && isIncharge.checked;
        hoursSpentContainer.style.display = shouldShow ? 'block' : 'none';
        hoursSpent.required = shouldShow;

        if (!shouldShow) {
          hoursSpent.value = '';
        }
      }

      function handleParticipantSourceUI() {
        const type = participantType.value;
        const category = participantCategory.value;
        const isInternal = type === 'internal';

        resetInternalSelectionUI();

        if (category === 'student') {
          rollNoContainer.style.display = 'block';
        } else {
          rollNoContainer.style.display = 'none';
          participantRollNo.value = '';
        }

        if (isInternal && category === 'faculty') {
          internalFacultyContainer.style.display = 'block';
          inchargeCheckboxContainer.style.display = 'block';
          toggleFieldLock(true);
          fillParticipantFields('', '', '', '');
          participantRollNo.readOnly = false;
          participantRollNo.value = '';
          updateInchargeHoursVisibility();
        } else if (isInternal && category === 'student') {
          internalStudentContainer.style.display = 'block';
          toggleFieldLock(true);
          fillParticipantFields('', '', '', '');
          participantRollNo.readOnly = true;
          updateInchargeHoursVisibility();
        } else {
          toggleFieldLock(false);
          participantRollNo.readOnly = false;
          updateInchargeHoursVisibility();
        }
      }

      participantCategory.addEventListener('change', handleParticipantSourceUI);

      // Handle participant type change to auto-fill institution name
      participantType.addEventListener('change', function() {
        if (this.value === 'internal') {
          institutionName.value = 'Salesian College Autonomous';
          institutionName.readOnly = true;
        } else {
          if (institutionName.value === 'Salesian College Autonomous') {
            institutionName.value = '';
          }
          institutionName.readOnly = false;
        }

        handleParticipantSourceUI();
      });

      internalFacultySelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        fillParticipantFields(
          selected?.dataset?.name,
          selected?.dataset?.email,
          selected?.dataset?.phone,
          ''
        );
      });

      internalStudentSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        fillParticipantFields(
          selected?.dataset?.name,
          selected?.dataset?.email,
          selected?.dataset?.phone,
          selected?.dataset?.rollno
        );
      });

      isIncharge.addEventListener('change', updateInchargeHoursVisibility);

      participantForm.addEventListener('submit', function(e) {
        const type = participantType.value;
        const category = participantCategory.value;

        if (type === 'internal' && category === 'faculty' && !internalFacultySelect.value) {
          e.preventDefault();
          alert('Please select a department faculty.');
          return;
        }

        if (type === 'internal' && category === 'student' && !internalStudentSelect.value) {
          e.preventDefault();
          alert('Please select a department student.');
          return;
        }

        if (type === 'internal' && category === 'faculty' && isIncharge.checked && !hoursSpent.value) {
          e.preventDefault();
          alert('Please enter hours spent for incharge.');
        }
      });

      handleParticipantSourceUI();
    });
  </script>

  @include('includes.footer')
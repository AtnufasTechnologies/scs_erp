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
        <form action="{{route('department.activities.participants.store', $activity->id)}}" method="post">
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
    <table class="table table-modern table-striped" id="exportTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Type</th>
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
          <td colspan="9" class="text-center">No participants found.</td>
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

      // Handle participant category change to show/hide roll number field
      participantCategory.addEventListener('change', function() {
        if (this.value === 'student') {
          rollNoContainer.style.display = 'block';
        } else {
          rollNoContainer.style.display = 'none';
        }
      });

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
      });
    });
  </script>

  @include('includes.footer')
<?php

use App\Helpers\Qs;
use App\Models\StudentProgram;
use App\Http\Controllers\StaticController;
use App\Models\MainProgram;
use App\Models\StudentMaster;

$userRoleType = StaticController::fetchUserRole();
$programs = Qs::getProgramGroups();
$selectedList = Qs::selectedApplicants('UG');
$campus = MainProgram::with('campus')->get();
?>
@include('includes.header')
@include('admin.admission.sidebar')

<div class="container-fluid mb-5">
  <div class="row mb-3">
    <div class="col-lg-4">
      <h3>Admission | UG - Enrollment </h3>
      Records Found - {{ $selectedList->count() }}
    </div>
    <div class="col-lg-4">
      <input type="text" id="liveSearchInput" class="form-control" placeholder="Search by name, mobile, email, or code...">
    </div>
    <div class="col-lg-4 text-end">
      <a href="{{ route('admission.ug.phase2.export') }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export to Excel
      </a>
    </div>
  </div>
  <!-- <div class="card shadow">

    <form method="POST" action="{{ route('send.phase2.notification') }}">
      @csrf
      <label for="" class="form-label">Select Applicant(s) to call for Final Enrollment</label>
      <div class="input-group">

        <select name="applicants[]" class="form-select select-multiple" multiple>
          @foreach($selectedList as $item)
          <option value="{{ $item->id }}">
            {{$item->applicationinfo->application_code}} - {{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}
          </option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-main"> <i class="fas fa-sms"></i> Send </button>
      </div>
    </form>

  </div> -->



  <div class="row">
    @foreach ($data as $item)
    <div class="col-lg-4">
      <div class="profile-card mb-5 shadow">
        <div class="profile-image">
          <img src="{{Storage::disk('s3')->url($item->applicationinfo->photo)}}" alt="proile picture" />
        </div>
        <div class="profile-info">
          @php
          $rollNo = StudentMaster::where('user_code', $item->applicationinfo->application_code)->value('roll_no');
          @endphp
          @if($rollNo != null)
          <span class="badge bg-success mb-2">Roll No: {{ $rollNo }}</span>
          <!-- Button trigger modal -->
          <span data-bs-toggle="modal" data-bs-target="#programShifter{{$item->id}}" class="mx-5 badge badge-warning">
            <i class="fa fa-user-cog"></i>
          </span>
          @endif

          <div class="profile-title"> Application Code# <b>{{ $item->applicationinfo->application_code ?? '-' }} </b> </div>
          <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
          <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
          <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
          <div class="profile-bio alert alert-success">
            {{ $item->applicationinfo->stdCourseMaster->code ?? '-' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '-' }}
          </div>
          <label for="">Interview Slot: <span><i class="fa fa-clock"></i></span> {{$item->interview_datetime}}</label>

        </div>
        @if($item->contract_ecopy != null)
        <i class="fa fa-check-circle text-success"></i> Contract eCopy is uploaded. <a href="{{ Storage::disk('s3')->url($item->contract_ecopy) }}" target="_blank" class="btn btn-sm btn-success">View Contract</a>
        @endif
        <div class="stats d-flex justify-content-around">
          <div class="stat-item">
            <div class="stat-value">
              <i class="fas fa-id-card-alt fa-2x {{$item->icard_generated == 1 ? 'text-success' : 'text-danger'}}"></i>
            </div>
          </div>
          <div class="stat-item">
            <div class="stat-value">
              <i class=" fal fa-file-signature fa-2x {{$item->contract_signed == 1 ? 'text-success' : 'text-danger'}}"></i>
            </div>
          </div>
        </div>

        <div class="dropdown mt-3">
          <div class="row">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}"><i class="fa fa-cog"></i> Action</button>
          </div>

        </div>
      </div>
    </div>
    <!-- Modal for Update Status -->
    <div class="modal fade" id="updateStatusModal{{ $item->id }}" tabindex="-1" aria-labelledby="updateStatusModalLabel{{ $item->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="updateStatusModalLabel{{ $item->id }}">Update Status - {{ $item->applicationinfo->application_code }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('admission.ug.phase2.update-status', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
              <label>Applicant Name</label>
              <input type="text" value="{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}" class="form-control mb-3" readonly>


              <div class="mb-3">
                <label for="contract_signed{{ $item->id }}" class="form-label">Contract Signed</label>
                <select class="form-select" id="contract_signed{{ $item->id }}" name="contract_signed" {{ in_array($userRoleType, ['principal', 'bursar','rector']) ? '' : 'disabled' }}>
                  <option value="0" {{ $item->contract_signed == 0 ? 'selected' : '' }}>No</option>
                  <option value="1" {{ $item->contract_signed == 1 ? 'selected' : '' }}>Yes</option>
                </select>
              </div>

              @if($userRoleType == 'admission-incharge')
              @if($item->contract_ecopy == null)
              <div class="mb-3">
                <label>Upload Contract eCopy (Accepted: Pdf, Max Size: 10MB)</label>
                <input type="file" class="form-control" name="contract_ecopy">
                @error('contract_ecopy')
                <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>


              @endif
              @endif


              @if($userRoleType == 'admission-incharge')
              @if($item->registrationmaster->is_enrolled == 0 || empty($rollNo))
              <div class="mb-3">
                <label for="enroll_status{{ $item->id }}" class="form-label">Enrollment Status <b>(Warning : Use Carefully)</b> <br>
                  <small class="text-danger">*This will Auto Add Applicant
                    to Student List and Activate RollNo.
                    The system will first fill any vacant RollNo sequence, then generate the next new RollNo.
                  </small></label>
                <select class="form-select" id="enroll_status{{ $item->id }}" name="enroll_status">
                  <option value="0" {{ $item->enroll_status == 0 ? 'selected' : '' }}>Pending</option>
                  <option value="1" {{ $item->enroll_status == 1 ? 'selected' : '' }}>Enroll Now</option>
                </select>
              </div>
              @else
              <div class="alert alert-info">

                Applicant is already enrolled. {{ $rollNo }}
              </div>
              @endif


              @endif

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="programShifter{{ $item->id }}" tabindex="-1" aria-labelledby="programShifter" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('enrolled.student.shifter') }}">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="campusShifterLabel{{ $item->id }}"> Shift <span class="text-capitalize">{{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-info">
                <p> <u>Warning:</u> Feature to be used <span class="badge badge-danger">Cautiously</span>
                </p>
                <p>This Action will update the Student's enrolled Program and reassign RollNo. <br>
                  Roll assignment is gap-first: vacant sequence numbers are filled before generating a new number. <br>
                  Old RollNo will be replaced. <br>
                  Inform Account office since fee structure will also get updated.</p>
              </div>
              <div class="mb-3">
                <label for="">Select Campus <span class="text-danger">*</span></label>
                <select name="campus" class="form-select" required id="transferCampus{{ $item->id }}">
                  <option value="">--Select--</option>
                  @foreach ($campus as $c)
                  <option value="{{ $c->id }}">
                    {{$c->campus->name}} - {{ $c->name }}
                  </option>
                  @endforeach

                </select>
              </div>
              <div class="mb-3">
                <label for="">Select Department <span class="text-danger">*</span></label>
                <select name="department" class="form-select" required id="transferDepartment{{ $item->id }}">
                  <option value="">--Select--</option>
                </select>
              </div>

              <script>
                document.getElementById('transferCampus{{ $item->id }}').addEventListener('change', function() {
                  const campusId = this.value;
                  const departmentSelect = document.getElementById('transferDepartment{{ $item->id }}');

                  if (campusId) {
                    fetch("{{ route('get.departments.by.campusprogram', '') }}/" + campusId)
                      .then(response => response.json())
                      .then(data => {
                        departmentSelect.innerHTML = '<option value="">--Select--</option>';
                        data.forEach(dept => {
                          const option = document.createElement('option');
                          option.value = dept.id;
                          option.textContent = dept.title;
                          departmentSelect.appendChild(option);
                        });
                      })
                      .catch(error => console.error('Error:', error));
                  }
                });
              </script>

              <div class="mb-3">
                <label for="">Select Course <span class="text-danger">*</span></label>
                <select name="course" class="form-select" required id="transferCourse{{ $item->id }}">
                  <option value="">--Select--</option>
                </select>
              </div>

              <script>
                document.getElementById('transferDepartment{{ $item->id }}').addEventListener('change', function() {
                  const departmentId = this.value;
                  const campusId = document.getElementById('transferCampus{{ $item->id }}').value;
                  const courseSelect = document.getElementById('transferCourse{{ $item->id }}');

                  if (departmentId) {
                    fetch("{{ route('get.programs.bydepartment', ['', '']) }}/" + departmentId + "/" + campusId)
                      .then(response => response.json())
                      .then(data => {
                        courseSelect.innerHTML = '<option value="">--Select--</option>';
                        data.forEach(program => {
                          const option = document.createElement('option');
                          option.value = program.student_program_id;
                          option.textContent = program.studentprograminfo.code + ' - ' + program.studentprograminfo.name;
                          courseSelect.appendChild(option);
                        });
                      })
                      .catch(error => console.error('Error:', error));
                  }
                });
              </script>
              <input type="hidden" name="application_id" value="{{ $item->applicationinfo->id }}">
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Shift and Reassign RollNo</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endforeach

  </div>

</div>
@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Live search functionality
    const searchInput = document.getElementById('liveSearchInput');
    searchInput.addEventListener('keyup', function() {
      const filter = searchInput.value.toLowerCase();
      const cards = document.querySelectorAll('.profile-card');
      cards.forEach(card => {
        const appno = card.querySelector('.application-no')?.textContent.toLowerCase() || '';
        const name = card.querySelector('.profile-name')?.textContent.toLowerCase() || '';
        const mobile = card.querySelector('.profile-title')?.textContent.toLowerCase() || '';
        const email = card.querySelectorAll('.profile-title')[1]?.textContent.toLowerCase() || '';
        const code = card.querySelector('.profile-bio')?.textContent.toLowerCase() || '';
        if (
          appno.includes(filter) ||
          name.includes(filter) ||
          mobile.includes(filter) ||
          email.includes(filter) ||
          code.includes(filter)
        ) {
          card.parentElement.style.display = '';
        } else {
          card.parentElement.style.display = 'none';
        }
      });
    });

  });
</script>
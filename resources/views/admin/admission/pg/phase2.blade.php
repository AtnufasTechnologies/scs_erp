<?php

use App\Helpers\Qs;
use App\Models\StudentProgram;
use App\Http\Controllers\StaticController;
use App\Models\StudentMaster;

$userRoleType = StaticController::fetchUserRole();
$programs = Qs::getProgramGroups();
$selectedList = Qs::selectedApplicants('PG');
?>
@include('includes.header')
@include('admin.admission.sidebar')

<div class="container-fluid mb-5">
  <div class="row">
    <div class="col-lg-4">
      <h3>Admission | PG - Enrollment </h3>
      Records Found - {{ $data->count() }}
    </div>
    <div class="col-lg-4 offset-lg-4">

      <input type="text" id="liveSearchInput" class="form-control mb-3" placeholder="Search by name, mobile, email, or code...">
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
</div>

<div class="row">
  @foreach ($data as $item)

  <div class="col-lg-4">
    <div class="profile-card mb-5 shadow">
      <div class="profile-image">
        <img src="{{Storage::disk('s3')->url($item->applicationinfo->photo)}}" alt="proile picture" />
        @php
        $rollNo = StudentMaster::where('user_code', $item->applicationinfo->application_code)->value('roll_no');
        @endphp
        @if($rollNo != null)
        <span class="badge bg-success mb-2">Roll No: {{ $rollNo }}</span>
        @endif
      </div>
      <div class="profile-info">
        <!-- @if($item->registrationmaster->is_enrolled == 1)
        <span class="badge bg-success mb-2">Enrolled</span>
        @else
        <a href="{{ route('activate.admission.payment', ['id' => $item->registrationmaster->id]) }}" onclick="return confirm('Are you sure you want to activate payment for this applicant?')">
          <button class="btn btn-success">Activate Payment</button>
        </a>
        @endif -->
        <div class="profile-title"> Application Code# <b>{{ $item->applicationinfo->application_code ?? '-' }} </b> </div>
        <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
        <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
        <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
        <div class="profile-bio alert alert-success">
          {{ $item->applicationinfo->stdCourseMaster->code ?? '-' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '-' }}
        </div>
        <label for="">Alloted Slot: <span><i class="fa fa-clock"></i></span> {{$item->interview_datetime}}</label>
      </div>
      <div class="stats d-flex justify-content-around">

        <div class="stat-item">
          <div class="stat-value">
            <i class="fa fa-id-card fa-2x {{$item->icard_generated == 1 ? 'text-success' : 'text-danger'}}"></i>
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
        <form action="{{ route('admission.ug.phase2.update-status', $item->id) }}" method="POST">
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
            <div class="mb-3">
              <label>Upload Contract eCopy</label>
              <input type="file" class="form-control" name="contract_ecopy">
            </div>
            @endif


            @if($userRoleType == 'admission-incharge')
            @if($item->registrationmaster->is_enrolled == 0)
            <div class="mb-3">
              <label for="enroll_status{{ $item->id }}" class="form-label">Enrollment Status <b>(Warning : Use Carefully)</b> <br>
                <small class="text-danger">*This will Auto Add Applicant
                  to Student List and Activate RollNo
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
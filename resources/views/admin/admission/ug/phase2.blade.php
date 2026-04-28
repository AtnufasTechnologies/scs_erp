<?php

use App\Helpers\Qs;
use App\Models\StudentProgram;

$programs = Qs::getProgramGroups();

?>
@include('includes.header')
@include('admin.admission.sidebar')

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-3">
      <h3>Admission | Final Phase </h3>
      Records Found - {{ $data->count() }}
    </div>
    <div class="col-lg-3 offset-lg-6">
      <form action="{{ route('admission.ug.phase2') }}" method="GET" class="search-form">
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search by application no." value="{{ request('search') }}">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </form>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-8">
      <form method="POST" action="{{ route('send.phase2.notification') }}">
        @csrf
        <div class="row ">
          <div class="col-lg-6">
            <label for="programGroup" class="form-label">Select Enrolled Programs</label>
            <select name="programs[]" class="form-select select-multiple" multiple>

              @foreach($programs as $program)
              <option value="{{ $program->id }}">
                {{$program->code}} - {{ $program->name }} ({{ count($program->applicationCount)  }})
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-5">
            <label for="interviewDate" class="form-label">Phase 2 Date</label>
            <div class="input-group">
              <input type="datetime-local" name="interview_time" class="form-control" required>
              <button type="submit" class="btn btn-main">
                <i class="fas fa-sms"></i> Send Phase 2 SMS
              </button>
            </div>

          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="row">
  @foreach ($data as $item)
  <div class="col-lg-4">
    <div class="profile-card">
      <div class="profile-image">
        <img src="{{Storage::disk('s3')->url($item->applicationinfo->photo)}}" alt="proile picture" />
      </div>
      <div class="profile-info">

        @if($item->registrationmaster->is_enrolled == 1)
        <span class="badge bg-success mb-2">Enrolled</span>
        @else
        <a href="{{ route('activate.admission.payment', ['id' => $item->registrationmaster->id]) }}" onclick="return confirm('Are you sure you want to activate payment for this applicant?')">
          <button class="btn btn-success">Activate Payment</button>
        </a>
        @endif
        <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
        <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
        <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
        <div class="profile-bio">
          {{ $item->applicationinfo->stdCourseMaster->code ?? '-' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '-' }}
        </div>
        <label for="">Interview Slot: {{$item->interview_datetime}}</label>

      </div>


      <div class="stats d-flex justify-content-around">
        <!-- <div class="stat-item">
          <div class="stat-value">
            <i class="fa fa-id-card fa-2x {{$item->icard_generated == 1 ? 'text-success' : 'text-danger'}}"></i>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-value">
            <i class=" fal fa-file-signature fa-2x {{$item->contract_signed == 1 ? 'text-success' : 'text-danger'}}"></i>
          </div>
        </div> -->

      </div>

      <div class="dropdown mt-3">
        <div class="row">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}">Take Action</button>
        </div>

      </div>
    </div>
  </div>
  <!-- Modal for Update Status -->
  <div class="modal fade" id="updateStatusModal{{ $item->id }}" tabindex="-1" aria-labelledby="updateStatusModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="updateStatusModalLabel{{ $item->id }}">Update Status - {{ $item->applicationinfo->application_id }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admission.ug.phase2.update-status', $item->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-3">
              <label for="is_doc_validated{{ $item->id }}" class="form-label">Document Validated</label>
              <select class="form-select" id="is_doc_validated{{ $item->id }}" name="is_doc_validated">
                <option value="0" {{ $item->is_doc_validated == 0 ? 'selected' : '' }}>Not Validated</option>
                <option value="1" {{ $item->is_doc_validated == 1 ? 'selected' : '' }}>Validated</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="is_subject_selected{{ $item->id }}" class="form-label">Subject Selection</label>
              <select class="form-select" id="is_subject_selected{{ $item->id }}" name="is_subject_selected">
                <option value="0" {{ $item->is_subject_selected == 0 ? 'selected' : '' }}>Not Selected</option>
                <option value="1" {{ $item->is_subject_selected == 1 ? 'selected' : '' }}>Selected</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="icard_generated{{ $item->id }}" class="form-label">ID Card Generated</label>
              <select class="form-select" id="icard_generated{{ $item->id }}" name="icard_generated">
                <option value="0" {{ $item->icard_generated == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ $item->icard_generated == 1 ? 'selected' : '' }}>Yes</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="contract_signed{{ $item->id }}" class="form-label">Contract Signed</label>
              <select class="form-select" id="contract_signed{{ $item->id }}" name="contract_signed">
                <option value="0" {{ $item->contract_signed == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ $item->contract_signed == 1 ? 'selected' : '' }}>Yes</option>
              </select>

            </div>
            <div class="mb-3">
              <label for="enroll_status{{ $item->id }}" class="form-label">Enrollment Status <br>
                <small class="text-danger">*This will Auto Add Applicant
                  to Student List and Activate RollNo
                </small></label>
              <select class="form-select" id="enroll_status{{ $item->id }}" name="enroll_status">
                <option value="0" {{ $item->enroll_status == 0 ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ $item->enroll_status == 1 ? 'selected' : '' }}>Enroll Now</option>
              </select>
            </div>


          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endforeach

</div>

</div>
@include('includes.footer')
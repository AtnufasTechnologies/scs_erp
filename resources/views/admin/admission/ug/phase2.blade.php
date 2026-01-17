<?php

use App\Models\StudentProgram;
?>

@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-4">
      <h3>Admission | Final Phase </h3>
      Records Found - {{ $data->count() }}
    </div>
    <div class="col-lg-3 offset-lg-5">
      <form action="{{ route('admission.ug.phase2') }}" method="GET" class="search-form">
        <div class="input-group">
          <input type="text" name="search" class="form-control" placeholder="Search by application no." value="{{ request('search') }}">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    @foreach ($data as $item)
    <div class="col-lg-3">

      <div class="profile-card">
        <div class="profile-image">
          <img src="{{asset('admin/images/logo.png')}}" alt="proile picture" />
        </div>
        <div class="profile-info">
          @if(Qs::fetchPhase1FinalStatus($item->reg_id) == 1)
          <span class="badge bg-success mb-2">Selected</span>
          @else
          <span class="badge bg-danger mb-2">Not Selected</span>
          @endif

          @if($item->enroll_status == 1)
          <span class="badge bg-success mb-2">Enrolled</span>
          @else
          <span class="badge bg-warning text-dark mb-2">Pending Enrollment</span>
          @endif
          <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
          <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
          <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
          <div class="profile-bio">
            {{ $item->applicationinfo->stdprogramMaster->code }} - {{ $item->applicationinfo->stdprogramMaster->name }}
          </div>
          <label for="">Interview Slot: {{$item->interview_datetime}}</label>

        </div>
        <div class="social-links">
          <label for="">Doc Validated</label>
          <button class="social-btn ">
            @if($item->is_doc_validated == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
          <label for="">Subject Selection </label>
          <button class="social-btn ">
            @if($item->is_subject_selected == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
          <label for="">Fee Payment </label>
          <button class="social-btn ">
            @if($item->fee_paid == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
        </div>


        <a href="{{route('admin.admission.ug.application-single', ['id' => $item->applicationinfo->id])}}"><button class="cta-button">View Application# {{$item->applicationinfo->application_id}}</button></a>
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
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}">Action</button>
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
                <label for="fee_paid{{ $item->id }}" class="form-label">Fee Payment</label>
                <select class="form-select" id="fee_paid{{ $item->id }}" name="fee_paid">
                  <option value="0" {{ $item->fee_paid == 0 ? 'selected' : '' }}>Not Paid</option>
                  <option value="1" {{ $item->fee_paid == 1 ? 'selected' : '' }}>Paid</option>
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
                <label for="enroll_status{{ $item->id }}" class="form-label">Enrollment Status
                  <small>*This will Auto Add Applicant
                    to Student Master
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
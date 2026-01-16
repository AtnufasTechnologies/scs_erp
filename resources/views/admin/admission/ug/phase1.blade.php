<?php

use App\Models\StudentProgram;
?>

@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-4">
      <h3>Interview | Selection First Phase </h3>
      Records Found - {{ $data->count() }}
    </div>
    <div class="col-lg-3 offset-lg-5">
      <form action="{{ route('admission.ug.phase1') }}" method="GET" class="search-form">
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
      <!-- From Uiverse.io by narmesh_sah -->
      <div class="profile-card">
        <div class="profile-image">
          <img src="{{asset('admin/images/logo.png')}}" alt="proile picture" />
        </div>
        <div class="profile-info">
          @if($item->final_status == 1)
          <span class="badge bg-success mb-2">Selected</span>
          @else
          <span class="badge bg-warning text-dark mb-2">Pending</span>
          @endif
          <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
          <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
          <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
          <div class="profile-bio">
            {{ $item->applicationinfo->stdprogramMaster->code }} - {{ $item->applicationinfo->stdprogramMaster->name }}
          </div>
          <label for="">{{$item->interview_datetime}}</label>
        </div>
        <div class="social-links">
          <label for="">Docs</label>
          <button class="social-btn ">
            @if($item->document_verified == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
          <label for="">Department </label>
          <button class="social-btn ">
            @if($item->dept_interview == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
          <label for="">Management </label>
          <button class="social-btn ">
            @if($item->mgt_interview_status == 1 )
            <i class="fa fa-check-circle text-success fa-2x"></i>
            @else
            <i class="fa fa-times-circle text-danger fa-2x"></i>
            @endif
          </button>
        </div>
        <a href="{{route('admin.admission.ug.application-single', ['id' => $item->applicationinfo->id])}}"><button class="cta-button">View Application# {{$item->applicationinfo->application_id}}</button></a>
        <div class="stats">
          <div class="stat-item">
            <div class="stat-value"> {{$item->proficiency_test_remarks ?? 'No Result'}} </div>
            <div class="stat-label {{$item->proficiency_test_status == 1 ? 'text-success' : 'text-danger'}}">English Proficiency </div>
          </div>
          <div class="stat-item">
            <div class="stat-value">
              @if ($item->dept_interview_remark != null)
              <i class="fa fa-comment-alt text-success" data-bs-toggle="modal" data-bs-target="#deptRemarkModal{{ $item->id }}"></i>
              @else
              N/A
              @endif
            </div>
            <div class="stat-label ">Dept Remark</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">
              @if ($item->mgt_interview_remark != null)
              <i class="fa fa-comment-alt text-success" data-bs-toggle="modal" data-bs-target="#mgtRemarkModal{{ $item->id }}"></i>
              @else
              N/A
              @endif
            </div>
            <div class="stat-label ">Mgt Remark</div>
          </div>
        </div>

        <div class="dropdown mt-3">
          <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="dropdownMenuButton{{ $item->id }}" data-bs-toggle="dropdown" aria-expanded="false">
            Actions
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $item->id }}">
            <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}">Update Status</a></li>
            <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#shiftProgram{{ $item->id }}"> Program Transfer</a></li>
            <li><a class="dropdown-item" href="#">Schedule Interview</a></li>
          </ul>
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
            <form action="{{ route('admission.ug.phase1.update-status', $item->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="modal-body">
                <div class="mb-3">
                  <label for="document_verified{{ $item->id }}" class="form-label">Document Verified</label>
                  <select class="form-select" id="document_verified{{ $item->id }}" name="document_verified">
                    <option value="0" {{ $item->document_verified == 0 ? 'selected' : '' }}>Not Verified</option>
                    <option value="1" {{ $item->document_verified == 1 ? 'selected' : '' }}>Verified</option>
                  </select>
                </div>
                <div class="mp-3">
                  <label for="proficiency_test_status{{ $item->id }}" class="form-label">English Proficiency Test Status</label>
                  <select class="form-select" id="proficiency_test_status{{ $item->id }}" name="proficiency_test_status">
                    <option value="0" {{ $item->proficiency_test_status == 0 ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ $item->proficiency_test_status == 1 ? 'selected' : '' }}>Done</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="proficiency_test_remarks{{ $item->id }}" class="form-label">English Proficiency Test Remarks</label>
                  <input class="form-control" id="proficiency_test_remarks{{ $item->id }}" name="proficiency_test_remarks" value="{{ $item->proficiency_test_remarks }}">
                </div>
                <div class="mb-3">
                  <label for="dept_interview{{ $item->id }}" class="form-label">Department Interview</label>
                  <select class="form-select" id="dept_interview{{ $item->id }}" name="dept_interview">
                    <option value="0" {{ $item->dept_interview == 0 ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ $item->dept_interview == 1 ? 'selected' : '' }}>Completed</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="mgt_interview_status{{ $item->id }}" class="form-label">Management Interview</label>
                  <select class="form-select" id="mgt_interview_status{{ $item->id }}" name="mgt_interview_status">
                    <option value="0" {{ $item->mgt_interview_status == 0 ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ $item->mgt_interview_status == 1 ? 'selected' : '' }}>Completed</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="dept_interview_remark{{ $item->id }}" class="form-label">Department Remark</label>
                  <textarea class="form-control" id="dept_interview_remark{{ $item->id }}" name="dept_interview_remark" rows="2">{{ $item->dept_interview_remark }}</textarea>
                </div>
                <div class="mb-3">
                  <label for="mgt_interview_remark{{ $item->id }}" class="form-label">Management Remark</label>
                  <textarea class="form-control" id="mgt_interview_remark{{ $item->id }}" name="mgt_interview_remark" rows="2">{{ $item->mgt_interview_remark }}</textarea>
                </div>
                <div class="mb-3">
                  <select class="form-select" id="final_status{{ $item->id }}" name="final_status">
                    <option value="0" {{ $item->final_status == 0 ? 'selected' : '' }}>Pending</option>
                    <option value="1" {{ $item->final_status == 1 ? 'selected' : '' }}>Selected</option>
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

    </div>



    <!-- Modal for Dept Remark -->
    <div class="modal fade" id="deptRemarkModal{{ $item->id }}" tabindex="-1" aria-labelledby="deptRemarkModalLabel{{ $item->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deptRemarkModalLabel{{ $item->id }}">Department Remark - {{ $item->applicationinfo->application_id }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>{{ $item->dept_interview_remark }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Management Remark -->
    <div class="modal fade" id="mgtRemarkModal{{ $item->id }}" tabindex="-1" aria-labelledby="mgtRemarkModalLabel{{ $item->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deptRemarkModalLabel{{ $item->id }}">Department Remark - {{ $item->applicationinfo->application_id }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>{{ $item->dept_interview_remark }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Management Remark -->
    <div class="modal fade" id="shiftProgram{{ $item->id }}" tabindex="-1" aria-labelledby="shiftProgram{{ $item->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="shiftProgram{{ $item->id }}">Program Transfer - {{ $item->applicationinfo->application_id }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="{{ route('admission.ug.phase1.shift-program', $item->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="mb-3">
                <?php
                $programs = StudentProgram::where('id', '!=', $item->programme_id)
                  ->where('campus_id', $item->registrationmaster->programinfo->campus_id)->get();

                ?>
                <label for="new_program{{ $item->id }}" class="form-label">Select New Program</label>
                <select class="form-select dselect-example" id="new_program{{ $item->id }}" name="new_program" required>
                  <option value="">Select Program</option>
                  @foreach($programs as $program)
                  <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Transfer Program</button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>

    @endforeach

  </div>

</div>
@include('includes.footer')
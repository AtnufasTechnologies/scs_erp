<?php

use App\Helpers\Qs;
use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\StudentProgram;
use App\Models\SubjectHasStudentProgam;

$userRoleType = StaticController::fetchUserRole();
$programs = Qs::getProgramGroups();

?>

@include('includes.header')


<div class="container-fluid">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar"
    style="background: linear-gradient(135deg, #17472f 0%, #8931f6 100%); border-radius: 0.75rem;">
    <div class=" container-fluid">
      <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
      <h3><span class="text-light">Interview | Selection First Phase</span></h3>
    </div>
  </nav>
</div>
<!-- Live Search Input -->
<div class="container-fluid">
  <div class="mb-3">
    Total Records: <span class="badge bg-primary">{{ $data->count() }}</span>
    Completed Records: <span class="badge bg-success">{{ $data->where('final_status', 1)->count() }}</span>
    Pending Records: <span class="badge bg-dark text-light">{{ $data->where('final_status', 0)->count() }}</span>
    @if(isset($transferredApplicants))
    <span class="ms-3">Transferred Applicants: <span class="badge bg-info">{{ $transferredApplicants->count() }}</span></span>
    @endif
    @if(isset($transferredPendingInterview) && $transferredPendingInterview->count() > 0)
    <span class="badge bg-warning text-dark">
      <i class="fa fa-exclamation-triangle"></i> {{ $transferredPendingInterview->count() }} Transferred Applicant(s) Pending Department Interview
    </span>
    @endif
  </div>
  <div class="row mb-3">
    <div class="col-lg-4">
      <input type="text" id="liveSearchInput" class="form-control" placeholder="Search by name, mobile, email, or code...">
    </div>
    <div class="col-lg-8 text-end">
      <a href="{{ route('admission.ug.phase1.export-all') }}" class="btn btn-primary">
        <i class="fa fa-download"></i> Export All Applicants
      </a>
      <a href="{{ route('admission.ug.phase1.export-selected') }}" class="btn btn-success">
        <i class="fa fa-download"></i> Export Selected Only
      </a>
    </div>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function() {
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
<!-- End Live Search Input -->

<!-- All Applicants Section -->
<div class="container-fluid">

  <div class="card-body">
    <div class="row">



      @foreach ($data as $item)
      <div class="col-lg-3">

        <div class="profile-card shadow {{ $item->dept_interview == 0 && isset($item->programChangeInfo) ? 'border-warning' : '' }}" style="position: relative;">

          @if(isset($item->programChangeInfo))
          <!-- Transfer Info Badge -->
          <div class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
            <span class="badge bg-info" data-bs-toggle="tooltip" title="This applicant was transferred from {{ $item->programChangeInfo->oldProgram->code ?? 'N/A' }}">
              <i class="fa fa-exchange-alt"></i> Transferred
            </span>
          </div>
          @endif

          @if(isset($item->programChangeInfo) && $item->dept_interview == 0)
          <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
            <span class="badge bg-warning text-dark">
              <i class="fa fa-bell"></i> Interview Pending
            </span>
          </div>
          @endif

          <div class="profile-image">
            <img src="{{Storage::disk('s3')->url($item->applicationinfo->photo)}}" alt="proile picture" />
          </div>
          <div class="profile-info">
            <strong>Final Status</strong>
            @if($item->final_status == 1)

            <span class="badge bg-success mb-2">Selected</span>
            @else
            <span class="badge bg-warning text-dark mb-2"> Pending</span>
            @endif
            <a href="{{ route('download.admission.application-form', $item->applicationinfo->application_code) }}">
              <div class="application-no text-success">Application# {{ $item->applicationinfo->application_code }}</div>
            </a>
            <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
            <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
            <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>

            @if(isset($item->programChangeInfo))
            <!-- Transfer Information Alert -->
            <div class="alert alert-info mt-2 p-2" style="font-size: 0.8rem;">
              <strong><i class="fa fa-info-circle"></i> Transfer Details:</strong><br>
              <small>
                <span class="text-muted">From:</span> {{ $item->programChangeInfo->oldProgram->code ?? 'N/A' }} - {{ $item->programChangeInfo->oldProgram->name ?? 'N/A' }}<br>
                <span class="text-muted">To:</span> {{ $item->applicationinfo->stdCourseMaster->code ?? '' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '' }}
                @if($item->programChangeInfo->reason)
                <br><span class="text-muted">Reason:</span> {{ Str::limit($item->programChangeInfo->reason, 40) }}
                @endif
              </small>
            </div>
            @endif

            <div class="profile-bio">
              <strong>{{ isset($item->programChangeInfo) ? 'Current Program:' : 'Program:' }}</strong><br>
              {{ $item->applicationinfo->stdCourseMaster->code ?? '' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '' }}
            </div>
            <label for="">{{$item->interview_datetime}}</label>
          </div>
          <div class="social-links">
            <label for="">Docs @if($item->document_verified == 1 )
              <i class="fa fa-check-circle text-success fa-2x"></i>
              @else
              <i class="fa fa-times-circle text-danger fa-2x"></i>
              @endif</label> <br>

            <label for="">Department <span class="mx-1"> @if($item->dept_interview == 1 )
                <i class="fa fa-check-circle text-success fa-2x"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-2x"></i>
                @endif</span></label>

            <label for="">Management <span>
                @if($item->mgt_interview_status == 1 )
                <i class="fa fa-check-circle text-success fa-2x"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-2x"></i>
                @endif
              </span></label>

          </div>
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
              <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}">
                  <i class="fa fa-edit"></i> Update Status
                </a></li>
              @if($userRoleType == 'dept-admin-erp' || $userRoleType == 'admission-incharge' )
              <li><a class="dropdown-item " data-bs-toggle="modal" data-bs-target="#shiftProgram{{ $item->id }}">
                  <i class="fa fa-exchange-alt"></i> <strong>Program Transfer</strong>
                </a></li>
              @endif
              @if( $userRoleType == 'admission-incharge' )
              <li><a class="dropdown-item text-danger" href="{{ route('admission.ug.phase1.override', $item->id) }}" onclick="return confirm('Are you sure you want to override the status for this applicant? This action cannot be undone.')">
                  <i class="fa check-circle"></i> <strong>Override All</strong>
                </a>
              </li>
              @endif

            </ul>
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
              <form action="{{ route('admission.ug.phase1.update-status', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                  @if($userRoleType == 'admission-incharge')
                  <div class="mb-3">
                    <label for="document_verified{{ $item->id }}" class="form-label">Document Verified</label>
                    <select class="form-select" id="document_verified{{ $item->id }}" name="document_verified">
                      <option value="0" {{ $item->document_verified == 0 ? 'selected' : '' }}>Not Verified</option>
                      <option value="1" {{ $item->document_verified == 1 ? 'selected' : '' }}>Verified</option>
                    </select>
                  </div>
                  @endif
                  @if($userRoleType == 'admission-test-incharge')
                  <div class="mb-3">
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
                  @endif
                  @if($userRoleType == 'dept-admin-erp')
                  <div class="mb-3">
                    <label for="dept_interview{{ $item->id }}" class="form-label">Department Interview</label>
                    <select class="form-select" id="dept_interview{{ $item->id }}" name="dept_interview">
                      <option value="0" {{ $item->dept_interview == 0 ? 'selected' : '' }}>Pending</option>
                      <option value="1" {{ $item->dept_interview == 1 ? 'selected' : '' }}>Completed</option>
                    </select>
                  </div>
                  @endif

                  @if($userRoleType == 'principal' || $userRoleType == 'vice-principal')
                  <div class="mb-3">
                    <label for="mgt_interview_status{{ $item->id }}" class="form-label">Management Interview</label>
                    <select class="form-select" id="mgt_interview_status{{ $item->id }}" name="mgt_interview_status">
                      <option value="0" {{ $item->mgt_interview_status == 0 ? 'selected' : '' }}>Pending</option>
                      <option value="1" {{ $item->mgt_interview_status == 1 ? 'selected' : '' }}>Completed</option>
                    </select>
                  </div>
                  @endif
                  @if($userRoleType == 'dept-admin-erp')
                  <div class="mb-3">
                    <label for="dept_interview_remark{{ $item->id }}" class="form-label">Department Remark</label>
                    <textarea class="form-control" id="dept_interview_remark{{ $item->id }}" name="dept_interview_remark" rows="2">{{ $item->dept_interview_remark }}</textarea>
                  </div>
                  @endif
                  @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'admission-incharge' || $userRoleType == 'bursar')
                  <div class="mb-3">
                    <label for="mgt_interview_remark{{ $item->id }}" class="form-label">Management Remark</label>
                    <textarea class="form-control" id="mgt_interview_remark{{ $item->id }}" name="mgt_interview_remark" rows="2">{{ $item->mgt_interview_remark }}</textarea>
                  </div>

                  <div class="mb-3">
                    <label for="">Final Decision</label>
                    <select class="form-select" id="final_status{{ $item->id }}" name="final_status">
                      <option value="0" {{ $item->final_status == 0 ? 'selected' : '' }}>Pending</option>
                      <option value="1" {{ $item->final_status == 1 ? 'selected' : '' }}>Selected</option>
                    </select>
                  </div>
                  @endif
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

      <!-- Modal for Program Transfer -->
      <div class="modal fade" id="shiftProgram{{ $item->id }}" tabindex="-1" aria-labelledby="shiftProgram{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="shiftProgram{{ $item->id }}">
                <i class="fa fa-exchange-alt"></i> Program Transfer - {{ $item->applicationinfo->application_code }}
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form action="{{ route('admission.ug.phase1.shift-program', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                  <?php
                  $programs = Qs::getAvailableCourseSeats($item->registrationmaster->campus_id);
                  ?>
                  <input type="text" readonly value="{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}" class="form-control mb-2">
                  <label for="">Current Program</label>
                  <input type="text" readonly value="{{ $item->applicationinfo->stdCourseMaster->code ?? '' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '' }}" class="form-control mb-2">
                  <label for="new_program{{ $item->id }}" class="form-label">New Program Availability</label>
                  <select class="form-select dselect-example" id="new_program{{ $item->id }}" name="new_program" required>
                    @foreach ($programs as $program)
                    <option value="{{ $program->studentprograminfo->id ?? ''}}">{{ $program->studentprograminfo->code ?? '-'}} - {{ $program->studentprograminfo->name ?? '-'}} Available ({{ $program->total_available_seats }})</option>
                    @endforeach
                  </select>


                  <div class="mb-3 mt-3">
                    <label for="reason{{ $item->id }}" class="form-label">Reason for Program Transfer</label>
                    <textarea class="form-control" id="reason{{ $item->id }}" name="reason" rows="3" placeholder="Enter reason for program transfer (optional)"></textarea>
                  </div>
                  <input type="hidden" value="{{$item->applicationinfo->id}}" name="application_id">
                  <input type="hidden" value="{{ $item->registrationmaster->id }}" name="registration_id">
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                      <i class="fa fa-exchange-alt"></i> Transfer Program
                    </button>
                  </div>
              </form>
            </div>

          </div>
        </div>
      </div>



      @endforeach



    </div>
  </div>

</div>
@include('includes.footer')
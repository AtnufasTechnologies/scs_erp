<?php

use App\Helpers\Qs;
use App\Http\Controllers\StaticController;


$userRoleType = StaticController::fetchUserRole();
$programs = Qs::getProgramGroups();

?>
@include('includes.header')


<div class="container-fluid mt-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar"
    style="background: linear-gradient(135deg, #681cdb 0%, #4a38be 100%); border-radius: 0.75rem;">
    <div class=" container-fluid">
      <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 80px;" class="me-2">
      <h3><span class="text-light">Interview | Selection First Phase</span></h3>
      <a href="{{route('scms.logout')}}" class="text-light"><i class=" fa fa-sign-out-alt text-light"></i> Logout</a>
    </div>
  </nav>
</div>

<!-- Live Search Input -->

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-8">
      <div class="row">
        <div class="col-lg-4">
          <div class="card shadow">
            <div class="card-body">
              <span class="display-5">{{ $data->count() }}</span>
              <h5>Total <i class="fa fa-user text-primary fa-2x"></i></h5>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow">
            <div class="card-body">
              <span class="display-5">{{ $data->where('proficiency_test_status', 1)->count() }}</span>
              <h5>Completed <i class="fa fa-check-circle text-success fa-2x"></i></h5>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow">
            <div class="card-body">
              <span class="display-5">{{ $data->where('proficiency_test_status', 0)->count() }}</span>
              <h5>Pending <i class="fa fa-hourglass-half text-warning fa-2x"></i></h5>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-lg-4">
      <div class="row">
        <form action="" method="get">
          <div class="row align-items-end">
            <div class="col-lg-12 mb-3">
              <label class="form-label">Search</label>
              <input type="text" id="liveSearchInput" class="form-control" placeholder="Search by name, mobile, email, or code...">
            </div>
            <div class="col-lg-4">
              <label class="form-label">Interview Date</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                <input type="date" name="interview_date" class="form-control" value="{{ request('interview_date') }}">
              </div>
            </div>
            <div class="col-lg-4">
              <label class="form-label">Final Status</label>
              <select name="final_status" class="form-control">
                <option value="">All</option>
                <option value="0" {{ request('final_status') === '0' ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ request('final_status') === '1' ? 'selected' : '' }}>Selected</option>
              </select>
            </div>
            <div class="col-lg-2">
              <button class="btn btn-primary" type="submit">
                <i class="fa fa-search"></i>
              </button>
            </div>
            <div class="col-lg-1">
              @if(request('interview_date') || request('final_status') !== null)
              <a href="{{ route('admission.testincharge.dashboard') }}" class="btn btn-secondary">
                <i class="fa fa-times"></i>
              </a>
              @endif
            </div>

          </div>
        </form>
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

  @if($data->count() > 0)
  <div class="container-fluid">
    <div class="row">

      @foreach ($data as $item)
      <div class="col-lg-3">

        <div class="profile-card shadow">
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
            <div class="application-no">Application# {{ $item->applicationinfo->application_code }}</div>
            <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
            <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
            <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>
            <div class="profile-bio">
              @if($item->applicationinfo->stdprogramMaster != null)
              {{ $item->applicationinfo->stdprogramMaster->code }} - {{ $item->applicationinfo->stdprogramMaster->name }}
              @endif
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
          <a href="{{ route('download.admission.application-form', $item->applicationinfo->application_code) }}"><button class="cta-button">View Application# {{$item->applicationinfo->application_code}}</button></a>
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
            <button class="btn btn-secondary  w-100" type="button" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}">
              Actions
            </button>

          </div>
        </div>

        <!-- Modal for Update Status -->
        <div class=" modal fade" id="updateStatusModal{{ $item->id }}" tabindex="-1" aria-labelledby="updateStatusModalLabel{{ $item->id }}" aria-hidden="true">
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

                  @if($userRoleType == 'admission-test-incharge')
                  <div class="mb-3">
                    <label for="">Applicant Name</label>
                    <input type="text" class="form-control mb-3" value="{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}" readonly>
                  </div>

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

      @endforeach

    </div>

  </div>

  @else
  <div class="container text-center">
    <h3>No Records Found</h3>
  </div>
  @endif
  @include('includes.footer')
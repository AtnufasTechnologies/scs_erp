<?php

use App\Helpers\Qs;
use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\StudentProgram;
use App\Models\SubjectHasStudentProgam;

$userRoleType = StaticController::fetchUserRole();
$programs = Qs::getPgProgramGroups();


?>

@include('includes.header')


<div class="container-fluid mt-3">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar"
    style="background: linear-gradient(135deg, #6615c4 0%, #921bfa 100%); border-radius: 0.75rem;">
    <div class=" container-fluid">
      <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 80px;" class="me-2">
      <h3><span class="text-light">PG - Interview | Selection</span></h3>
      @if($userRoleType == 'admission-incharge')
      <a href="{{route('admission.dashboard')}}"> <button class="btn btn-light">Back to Main</button></a>
      @endif
      @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
      <a href="{{route('principal.dashboard')}}"> <button class="btn btn-light">Back to Main</button></a>
      @endif
      @if($userRoleType == 'dept-admin-erp')
      <a href="{{route('department.dashboard')}}"> <button class="btn btn-light">Back to Main</button></a>
      @endif
    </div>
  </nav>
</div>
<!-- Live Search Input -->
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-2">
      <div class="card shadow">
        <div class="card-body">
          <span class="display-4">{{ $data->count() }}</span>
          <h5>Total <i class="fa fa-user text-primary fa-2x"></i></h5>
        </div>
      </div>
    </div>

    <div class="col-lg-2">
      <div class="card shadow">
        <div class="card-body">
          <span class="display-4">{{ $data->where('final_status', 1)->count() }}</span>
          <h5>Completed <i class="fa fa-check-circle text-success fa-2x"></i></h5>
        </div>
      </div>
    </div>

    <div class="col-lg-2">
      <div class="card shadow">
        <div class="card-body">
          <span class="display-4">{{ $data->where('final_status', 0)->count() }}</span>
          <h5>Pending <i class="fa fa-hourglass-half text-warning fa-2x"></i></h5>
        </div>
      </div>
    </div>

    <div class="col-lg-2">
      <div class="card shadow">
        <div class="card-body">
          <span class="display-4">
            @if(isset($transferredApplicants))
            {{ $transferredApplicants->count() }}
            @endif
          </span>
          <h5>Transferred <i class="fa fa-exchange-alt text-info fa-2x"></i></h5>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow">
        <div class="card-body">
          @if(isset($transferredPendingInterview) && $transferredPendingInterview->count() > 0)
          <span class="display-4">{{ $transferredPendingInterview->count() }}</span>
          <h5>Transferred Pending Dept Interview <i class="fa fa-bell text-danger fa-2x"></i></h5>
          @else
          <span class="display-4">0</span>
          <p>No Transferred Applicants Pending Dept Interview <i class="fa fa-check-circle text-success fa-2x"></i></p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>


@if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
<div class="container-fluid mt-3">
  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow p-3">
        <h5 class="mb-3"><i class="fa fa-filter"></i> Quick Filters</h5>
        <p><strong>Total Records:</strong> {{ $data->count() }} | <strong>Showing:</strong> <span id="filteredCount" class="text-primary">{{ $data->count() }}</span></p>
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-outline-success" id="filterDocsVerified">
            <i class="fa fa-check-circle"></i> Docs Verified Only
          </button>
          <button type="button" class="btn btn-outline-primary" id="filterDeptInterviewDone">
            <i class="fa fa-user-check"></i> Dept Interview Done
          </button>
          <button type="button" class="btn btn-outline-warning" id="filterBothCompleted">
            <i class="fa fa-check-double"></i> Both Completed
          </button>
          <button type="button" class="btn btn-outline-secondary" id="clearFilters">
            <i class="fa fa-times"></i> Clear Filters
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endif


<div class="container-fluid">
  <div class="row mb-3">
    <div class="card shadow p-3">
      <form action="" method="get">
        <div class="row align-items-end">
          <div class="col-lg-3">
            <label class="form-label">Search</label>
            <input type="text" id="liveSearchInput" class="form-control" placeholder="Search by name, mobile, email, or code...">
          </div>
          <div class="col-lg-2">
            <label class="form-label">Interview Date</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa fa-calendar"></i></span>
              <input type="date" name="interview_date" class="form-control" value="{{ request('interview_date') }}">
            </div>
          </div>
          <div class="col-lg-2">
            <label class="form-label">Final Status</label>
            <select name="final_status" class="form-control">
              <option value="">All</option>
              <option value="0" {{ request('final_status') === '0' ? 'selected' : '' }}>Pending</option>
              <option value="1" {{ request('final_status') === '1' ? 'selected' : '' }}>Selected</option>
            </select>
          </div>
          <div class="col-lg-2">
            <button class="btn btn-primary w-100" type="submit">
              <i class="fa fa-filter"></i> Apply Filters
            </button>
          </div>
          <div class="col-lg-2">
            @if(request('interview_date') || request('final_status') !== null)
            <a href="{{ route('admission.pg.phase1') }}" class="btn btn-secondary w-100">
              <i class="fa fa-times"></i> Reset
            </a>
            @endif
          </div>
          <div class="col-lg-1">
            <a href="{{ route('admission.pg.phase1.export-all', [
      'interview_date' => request('interview_date'),
      'final_status' => request('final_status'),
      'search' => request('search')
    ]) }}" class="btn btn-success">
              <i class="fa fa-download"></i> Export
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- All Applicants Section -->
<div class="container-fluid">

  <div class="row">

    @foreach ($data as $item)
    <div class="col-lg-3 mb-4">
      <div class="profile-card shadow {{ $item->dept_interview == 0 && isset($item->programChangeInfo) ? 'border-warning' : '' }}" style="position: relative;" data-applicant-id="{{ $item->id }}">
        @if(isset($item->programChangeInfo))

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
            <div class="profile-title text-success">Application# {{ $item->applicationinfo->application_code }}</div>
          </a>
          <p class="profile-name text-capitalize">{{ $item->registrationmaster->first_name  }} {{ $item->registrationmaster->last_name  }}</p>
          <div class="profile-title">{{ $item->registrationmaster->mobile_no  }}</div>
          <div class="profile-title">{{ $item->registrationmaster->mail_id  }}</div>

          @if(isset($item->programChangeInfo))

          <div class="alert alert-info mt-2 p-2" style="font-size: 1rem;">
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

          <div class="profile-bio alert alert-info">
            <strong>{{ isset($item->programChangeInfo) ? 'Current Program:' : 'Program:' }}</strong><br>
            {{ $item->applicationinfo->stdCourseMaster->code ?? '' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? '' }}
            <br>
            <label for=""> <strong><i class="far fa-clock"></i></strong>
              {{$item->interview_datetime}}</label>
          </div>

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
            </span>
          </label>

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


        <button class="btn btn-primary  mb-3 mt-3" data-bs-toggle="modal" data-bs-target="#updateStatusModal{{ $item->id }}" style="width: 100%;">

          Actions
        </button>


      </div>

      <div class="modal fade" id="updateStatusModal{{ $item->id }}" tabindex="-1" aria-labelledby="updateStatusModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="updateStatusModalLabel{{ $item->id }}">Update Status - {{ $item->applicationinfo->application_code }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admission.pg.phase1.update-status', $item->id) }}" method="POST">
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

                @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
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
                @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
                <div class="mb-3">
                  <label for="mgt_interview_remark{{ $item->id }}" class="form-label">Management Remark</label>
                  <textarea class="form-control" id="mgt_interview_remark{{ $item->id }}" name="mgt_interview_remark" rows="2">{{ $item->mgt_interview_remark }}</textarea>
                </div>

                <div class="mb-3">
                  <div class="alert alert-info">
                    <p> On your Final Decision the Student is Added Selected List and can Proceed to next step of Fee Payment</p>
                  </div>
                  <label for="">Final Decision </label>
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

      <!-- Modal for Dept Remark -->
      <div class="modal fade" id="deptRemarkModal{{ $item->id }}" tabindex="-1" aria-labelledby="deptRemarkModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deptRemarkModalLabel{{ $item->id }}">Department Remark - {{ $item->applicationinfo->application_code }}</h5>
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
              <h5 class="modal-title" id="mgtRemarkModalLabel{{ $item->id }}">Management Remark - {{ $item->applicationinfo->application_code }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>{{ $item->mgt_interview_remark }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Close
              </button>
            </div>
          </div>
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

    // Quick filter buttons for principals/management
    const filterDocsVerified = document.getElementById('filterDocsVerified');
    const filterDeptInterviewDone = document.getElementById('filterDeptInterviewDone');
    const filterBothCompleted = document.getElementById('filterBothCompleted');
    const clearFiltersBtn = document.getElementById('clearFilters');

    if (filterDocsVerified) {
      filterDocsVerified.addEventListener('click', function() {
        filterCards('docs');
        toggleActiveFilter(this);
      });
    }

    if (filterDeptInterviewDone) {
      filterDeptInterviewDone.addEventListener('click', function() {
        filterCards('dept');
        toggleActiveFilter(this);
      });
    }

    if (filterBothCompleted) {
      filterBothCompleted.addEventListener('click', function() {
        filterCards('both');
        toggleActiveFilter(this);
      });
    }

    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function() {
        filterCards('clear');
        document.querySelectorAll('.btn-group button').forEach(btn => {
          btn.classList.remove('active');
        });
      });
    }

    function toggleActiveFilter(button) {
      document.querySelectorAll('.btn-group button').forEach(btn => {
        btn.classList.remove('active');
      });
      button.classList.add('active');
    }

    function filterCards(type) {
      const cards = document.querySelectorAll('.profile-card');
      let visibleCount = 0;

      cards.forEach(card => {
        const parentCol = card.parentElement;
        const socialLinks = card.querySelector('.social-links');

        if (!socialLinks) {
          parentCol.style.display = '';
          visibleCount++;
          return;
        }

        // Check for docs verified icon
        const docsLabel = Array.from(socialLinks.querySelectorAll('label')).find(label =>
          label.textContent.includes('Docs')
        );
        const docsVerified = docsLabel ? docsLabel.querySelector('.fa-check-circle.text-success') !== null : false;

        // Check for dept interview icon
        const deptLabel = Array.from(socialLinks.querySelectorAll('label')).find(label =>
          label.textContent.includes('Department')
        );
        const deptInterviewDone = deptLabel ? deptLabel.querySelector('.fa-check-circle.text-success') !== null : false;

        let shouldShow = false;

        switch (type) {
          case 'docs':
            shouldShow = docsVerified;
            break;
          case 'dept':
            shouldShow = deptInterviewDone;
            break;
          case 'both':
            shouldShow = docsVerified && deptInterviewDone;
            break;
          case 'clear':
            shouldShow = true;
            break;
        }

        parentCol.style.display = shouldShow ? '' : 'none';
        if (shouldShow) visibleCount++;
      });

      // Update the filtered count display
      const filteredCountSpan = document.getElementById('filteredCount');
      if (filteredCountSpan) {
        filteredCountSpan.textContent = visibleCount;
      }
    }

    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const applicantCheckboxes = document.querySelectorAll('.applicant-checkbox');
    const bulkOverrideBtn = document.getElementById('bulkOverrideBtn');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (selectAllCheckbox) {
      // Select/Deselect all functionality
      selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        applicantCheckboxes.forEach(checkbox => {
          const card = checkbox.closest('.col-lg-3');
          // Only select visible cards
          if (card.style.display !== 'none') {
            checkbox.checked = isChecked;
          }
        });
        updateBulkOverrideButton();
      });

      // Individual checkbox change
      applicantCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          updateSelectAllCheckbox();
          updateBulkOverrideButton();
        });
      });

      // Bulk override button click
      bulkOverrideBtn.addEventListener('click', function() {
        const selectedIds = getSelectedApplicantIds();
        if (selectedIds.length === 0) {
          alert('Please select at least one applicant.');
          return;
        }

        if (confirm(`Are you sure you want to override the status for ${selectedIds.length} selected applicant(s)? This action cannot be undone.`)) {
          // Create a form and submit
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ route("admission.ug.phase1.bulk-override") }}';

          // Add CSRF token
          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = '{{ csrf_token() }}';
          form.appendChild(csrfInput);

          // Add applicant IDs
          selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'applicant_ids[]';
            input.value = id;
            form.appendChild(input);
          });

          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function updateSelectAllCheckbox() {
      if (!selectAllCheckbox) return;

      const visibleCheckboxes = Array.from(applicantCheckboxes).filter(checkbox => {
        const card = checkbox.closest('.col-lg-3');
        return card.style.display !== 'none';
      });

      const checkedVisible = visibleCheckboxes.filter(cb => cb.checked);

      if (checkedVisible.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
      } else if (checkedVisible.length === visibleCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
      } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
      }
    }

    function updateBulkOverrideButton() {
      if (!bulkOverrideBtn) return;

      const selectedCount = getSelectedApplicantIds().length;
      selectedCountSpan.textContent = selectedCount;

      if (selectedCount > 0) {
        bulkOverrideBtn.style.display = 'inline-block';
      } else {
        bulkOverrideBtn.style.display = 'none';
      }
    }

    function getSelectedApplicantIds() {
      return Array.from(applicantCheckboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);
    }
  });
</script>
<!-- End Live Search Input -->
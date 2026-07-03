@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.faculty.index') }}"></a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
      <!-- Faculty Profile Card -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            @if($faculty->photo)
            <img src="{{ $faculty->photo }}" alt="{{ $faculty->FIRST_NAME }}" class="rounded-circle mb-3" width="120" height="120">
            @else
            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px; font-size: 48px;">
              {{ substr($faculty->FIRST_NAME, 0, 1) }}
            </div>
            @endif
            <h5>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</h5>
            {{$faculty->CAMPUS_ID == 1 ? 'Sonada' : 'Siliguri' }} Campus
            <p class="text-muted mb-2">{{ $faculty->designation ?? '' }}</p>
            <p class="text-muted small">{{ $faculty->USER_CODE }}</p>
            <div class="d-flex gap-2 justify-content-center">
              <a href="{{ route('hr.faculty.edit', $faculty->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
              <a href="{{ route('hr.faculty.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Leave Statistics</h6>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Total Applied:</span>
              <strong>{{ $leaveStats['total_applied'] }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Approved:</span>
              <strong class="text-success">{{ $leaveStats['approved'] }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Pending:</span>
              <strong class="text-warning">{{ $leaveStats['pending'] }}</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Rejected:</span>
              <strong class="text-danger">{{ $leaveStats['rejected'] }}</strong>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">FDP Statistics</h6>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Total Participated:</span>
              <strong>{{ $fdpStats['total_participated'] }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Completed:</span>
              <strong class="text-success">{{ $fdpStats['completed'] }}</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Ongoing:</span>
              <strong class="text-info">{{ $fdpStats['ongoing'] }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Faculty Details -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0">Personal Information</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <th style="width: 35%;">Employee Code:</th>
                <td>{{ $faculty->USER_CODE }}</td>
              </tr>
              <tr>
                <th>Full Name:</th>
                <td>{{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</td>
              </tr>
              <tr>
                <th>Gender:</th>
                <td>{{ $faculty->GENDER == 1 ? 'Male' : ($faculty->GENDER == 2 ? 'Female' : 'Other')  }}</td>
              </tr>
              <tr>
                <th>Date of Birth:</th>
                <td>{{ $faculty->DOB ? date('d M Y', strtotime($faculty->DOB)) : '-' }}</td>
              </tr>
              <tr>
                <th>Date of Joining / Rejoining:</th>
                <td>{{ $faculty->DOJ ? date('d M Y', strtotime($faculty->DOJ)) : '-' }}</td>
              </tr>
              <tr>
                <th>Date of Leaving:</th>
                <td>{{ $faculty->DOL ? date('d M Y', strtotime($faculty->DOL)) : '-' }}</td>
              </tr>
              <tr>
                <th>Nationality:</th>
                <td>{{ $faculty->nationality->name ?? '-' }}</td>
              </tr>
              <tr>
                <th>Status:</th>
                <td>
                  @if($faculty->IS_LEFT)
                  <span class="badge bg-danger">Left</span>
                  @else
                  <span class="badge bg-success">Active</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th>HR Remark:</th>
                <td>{{ $faculty->hr_remark ?? '-' }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Contact Information</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <th style="width: 35%;">Email:</th>
                <td>{{ $faculty->MAIL_ID }}</td>
              </tr>
              <tr>
                <th>Mobile:</th>
                <td>{{ $faculty->MOBILE_NO }}</td>
              </tr>
              <tr>
                <th>Current Address:</th>
                <td>{{ $faculty->ADDRESS ?? '-' }}</td>
              </tr>
              <tr>
                <th>Permanent Address:</th>
                <td>{{ $faculty->permanent_address ?? '-' }}</td>
              </tr>
              <tr>
                <th>Emergency Contact:</th>
                <td>{{ $faculty->emergency_contact_name ?? '-' }} {{ $faculty->emergency_contact_number ? '('.$faculty->emergency_contact_number.')' : '' }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Professional Details</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <th style="width: 35%;">Employee Type:</th>
                <td>{{ ucfirst($faculty->employee_type ?? '-') }}</td>
              </tr>
              <tr>
                <th>Designation:</th>
                <td>{{ $faculty->designation ?? '-' }}</td>
              </tr>
              <tr>
                <th>Qualification:</th>
                <td>{{ $faculty->qualification ?? '-' }}</td>
              </tr>
              <tr>
                <th>Specialization:</th>
                <td>{{ $faculty->specialization ?? '-' }}</td>
              </tr>
              <tr>
                <th>Experience:</th>
                <td>{{ $faculty->experience_years ? $faculty->experience_years . ' years' : '-' }}</td>
              </tr>
              <tr>
                <th>Responsibility:</th>
                <td>{{ $faculty->responsibility ?? '-' }}</td>
              </tr>
              <tr>
                <th>Paper Publications:</th>
                <td>{{ $faculty->paper_publications_count ?? '0' }}</td>
              </tr>
              <tr>
                <th>ORCID ID:</th>
                <td>{{ $faculty->orcid_id ?? '-' }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Banking & ID Details</h6>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tr>
                <th style="width: 35%;">PAN Number:</th>
                <td>{{ $faculty->pan_number ?? '-' }}</td>
              </tr>
              <tr>
                <th>Aadhar Number:</th>
                <td>{{ $faculty->aadhar_number ?? '-' }}</td>
              </tr>
              <tr>
                <th>Bank Name:</th>
                <td>{{ $faculty->bank_name ?? '-' }}</td>
              </tr>
              <tr>
                <th>Account Number:</th>
                <td>{{ $faculty->bank_account_number ?? '-' }}</td>
              </tr>
              <tr>
                <th>IFSC Code:</th>
                <td>{{ $faculty->bank_ifsc_code ?? '-' }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0">Status History</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Event</th>
                    <th>Status Change</th>
                    <th>Remark</th>
                    <th>Logged At</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($faculty->statusHistories as $history)
                  <tr>
                    <td>{{ $history->status_on ? date('d M Y', strtotime($history->status_on)) : '-' }}</td>
                    <td>
                      @if($history->event_type === 'reactivated')
                      <span class="badge bg-success">Reactivated</span>
                      @else
                      <span class="badge bg-danger">Deactivated</span>
                      @endif
                    </td>
                    <td>
                      {{ (int)$history->old_status === 1 ? 'Left' : 'Active' }}
                      to
                      {{ (int)$history->new_status === 1 ? 'Left' : 'Active' }}
                    </td>
                    <td>{{ $history->remark ?: '-' }}</td>
                    <td>{{ $history->created_at ? $history->created_at->format('d M Y h:i A') : '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted">No status history available</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
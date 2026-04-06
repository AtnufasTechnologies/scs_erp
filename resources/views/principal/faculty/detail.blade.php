@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Detail</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.faculty.index') }}">Faculty</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!--faculty info card-->
    <div class="card mt-3">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Faculty Profile</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-2 text-center">
            @if($faculty->photo)
            <img src="{{ $faculty->photo }}" alt="Photo" class="rounded-circle" width="100" height="100" style="object-fit:cover;">
            @else
            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;">
              <i class="fas fa-user fa-3x text-white"></i>
            </div>
            @endif
          </div>
          <div class="col-md-10">
            <div class="row">
              <div class="col-md-4">
                <p><strong>Name:</strong> {{ $faculty->FIRST_NAME }} {{ $faculty->MIDDLE_NAME }} {{ $faculty->LAST_NAME }}</p>
                <p><strong>Code:</strong> {{ $faculty->USER_CODE }}</p>
                <p><strong>Gender:</strong> {{ $faculty->GENDER == 1 ? 'Male' : 'Female' }}</p>
              </div>
              <div class="col-md-4">
                <p><strong>Department:</strong> {{ $faculty->department_info ? $faculty->department_info->name : '-' }}</p>
                <p><strong>Campus:</strong> {{ $faculty->department_info && $faculty->department_info->campusmaster ? $faculty->department_info->campusmaster->name : '-' }}</p>
                <p><strong>DOB:</strong> {{ $faculty->DOB }}</p>
              </div>
              <div class="col-md-4">
                <p><strong>Email:</strong> {{ $faculty->MAIL_ID }}</p>
                <p><strong>Phone:</strong> {{ $faculty->MOBILE_NO }}</p>
                <p><strong>Date of Joining:</strong> {{ $faculty->DOJ }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--timetable-->
    <div class="card mt-3">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Timetable</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-sm text-center">
            <thead class="bg-dark">
              <tr>
                <th>Day / Hour</th>
                @foreach($hours as $hour)
                <th>{{ $hour->title }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($timetableGrid as $dayId => $dayData)
              <tr>
                <td class="fw-bold bg-light">{{ $dayData['day'] }}</td>
                @foreach($dayData['slots'] as $hourId => $slot)
                <td>
                  @if($slot['routine'])
                  @php
                  $cm = $slot['routine']->subjectCourse && $slot['routine']->subjectCourse->courseMaster ? $slot['routine']->subjectCourse->courseMaster : null;
                  @endphp
                  <small class="d-block text-primary fw-bold">
                    {{ $cm ? $cm->course_code : '-' }}
                  </small>
                  <small class="d-block text-dark">
                    {{ $cm ? Str::limit($cm->course_title, 25) : '' }}
                  </small>
                  <small class="d-block text-muted">
                    {{ $slot['routine']->lecturehallmaster ? $slot['routine']->lecturehallmaster->title : '' }}
                  </small>
                  <small class="d-block text-secondary">
                    {{ $slot['routine']->batch ? $slot['routine']->batch->batch_name : '' }}
                    @if($cm && $cm->semestermaster)
                    | {{ $cm->semestermaster->title }}
                    @endif
                  </small>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
                @endforeach
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!--leaves-->
    <div class="card mt-3">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-calendar-minus me-2"></i>Leave Records</h5>
      </div>
      <div class="card-body">
        @if(count($leaves))
        <div class="table-responsive">
          <table class="table table-hover table-sm">
            <thead class="bg-light">
              <tr>
                <th>#</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Note</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @php $sl = 1; @endphp
              @foreach($leaves as $leave)
              <tr>
                <td>{{ $sl++ }}</td>
                <td>{{ $leave->leaveMaster ? $leave->leaveMaster->leave_type_name : $leave->leave_type }}</td>
                <td>{{ $leave->start_date ? $leave->start_date->format('d M Y') : '-' }}</td>
                <td>{{ $leave->end_date ? $leave->end_date->format('d M Y') : '-' }}</td>
                <td>{{ $leave->total_days }}</td>
                <td>{{ Str::limit($leave->reason, 50) }}</td>
                <td>
                  @if($leave->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                  @elseif($leave->status == 'pending')
                  <span class="badge bg-warning">Pending</span>
                  @elseif($leave->status == 'rejected')
                  <span class="badge bg-danger">Rejected</span>
                  @else
                  <span class="badge bg-secondary">{{ ucfirst($leave->status) }}</span>
                  @endif
                </td>
                <td><small>{{ $leave->admin_remarks ?? '-' }}</small></td>
                <td>
                  @if($leave->status == 'pending')
                  <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#leaveActionModal{{ $leave->id }}">
                    <i class="fas fa-gavel"></i>
                  </button>
                  @else
                  <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <p class="text-muted text-center py-3">No leave records found.</p>
        @endif
      </div>
    </div>

    <!--feedback-->
    <div class="card mt-3 mb-4">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Student Feedback</h5>
      </div>
      <div class="card-body">
        @if(count($feedback))
        <div class="table-responsive">
          <table class="table table-hover table-sm">
            <thead class="bg-light">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Rating</th>
                <th>Feedback</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @php $sl = 1; @endphp
              @foreach($feedback as $fb)
              <tr>
                <td>{{ $sl++ }}</td>
                <td class="text-capitalize">{{ $fb->student ? $fb->student->first_name . ' ' . $fb->student->last_name : '-' }}</td>
                <td>{{ $fb->student ? $fb->student->roll_no : '-' }}</td>
                <td>
                  @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $fb->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.8rem;"></i>
                    @endfor
                </td>
                <td>{{ Str::limit($fb->feedback, 60) }}</td>
                <td>{{ $fb->created_at ? $fb->created_at->format('d M Y') : '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <p class="text-muted text-center py-3">No feedback records found.</p>
        @endif
      </div>
    </div>

    {{-- Leave Action Modals --}}
    @foreach($leaves as $leave)
    @if($leave->status == 'pending')
    <div class="modal fade" id="leaveActionModal{{ $leave->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">Leave Action - {{ $leave->leaveMaster ? $leave->leaveMaster->leave_type_name : ucfirst($leave->leave_type) }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('principal.leaves.action', $leave->id) }}" method="POST">
            @csrf
            <div class="modal-body">
              <div class="mb-3 p-3 bg-light rounded">
                <div class="row">
                  <div class="col-6"><small class="text-muted">From:</small> <strong>{{ $leave->start_date ? $leave->start_date->format('d M Y') : '-' }}</strong></div>
                  <div class="col-6"><small class="text-muted">To:</small> <strong>{{ $leave->end_date ? $leave->end_date->format('d M Y') : '-' }}</strong></div>
                </div>
                <div class="mt-2"><small class="text-muted">Duration:</small> <strong>{{ $leave->total_days }} day(s)</strong></div>
                <div class="mt-2"><small class="text-muted">Reason:</small> {{ $leave->reason ?? 'Not provided' }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Admin Note</label>
                <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Add a note or reason for your decision..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" name="action" value="approved" class="btn btn-success">
                <i class="fas fa-check me-1"></i>Grant Leave
              </button>
              <button type="submit" name="action" value="rejected" class="btn btn-danger">
                <i class="fas fa-times me-1"></i>Deny Leave
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
    @endforeach

  </main>
</div>

@include('includes.footer')
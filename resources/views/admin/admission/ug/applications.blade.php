<?php

use App\Helpers\Qs;

$programs = Qs::getProgramGroups();

?>
@include('includes.header')
@include('admin.sidebar')
<h3>Applications </h3>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-8">
      <form method="POST" action="{{ route('send.phase1.notification') }}">
        @csrf
        <div class="row ">
          <div class="col-lg-6">
            <label for="programGroup" class="form-label">Select Program</label>
            <select name="programs[]" class="form-select select-multiple" multiple>
              <option value="">-- Select Program Group--</option>
              @foreach($programs as $program)
              <option value="{{ $program->id }}">
                {{$program->code}} - {{ $program->name }} ({{ count($program->applicationCount)  }})
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-6">
            <label for="interviewDate" class="form-label">Interview Date</label>
            <div class="input-group">
              <input type="datetime-local" name="interview_time" class="form-control" required>
              <button type="submit" class="btn btn-main">
                <i class="fas fa-sms"></i> Send Interview SMS
              </button>
            </div>

          </div>
        </div>
      </form>
    </div>
  </div>
</div>



<div class="container-fluid mt-4">
  <div class=" ">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span class="h5 mb-0">UG Applications</span>
    </div>
    <div class="card-body">
      <form method="GET" action="" class="mb-3">
        <div class="row g-2">

          <div class="col-md-3">
            <select name="status" class="form-select">
              <option value="">All Statuses</option>
              <option value="0">Pending</option>
              <option value="1">Paid</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">
              <i class="fas fa-search"></i> Filter
            </button>
          </div>
        </div>
      </form>

      <table class="table table-hover" id="exportTable">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Batch</th>
            <th>Code #</th>
            <th>Applicant Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Program</th>
            <th>Application Status</th>
            <th>Applied On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $item)
          <tr>
            <td>{{ $loop->iteration  }}</td>
            <td>{{$item->registrationmaster->batch}}</td>
            <td><a href="{{ route('admin.admission.ug.application-single', $item->id) }}" class="btn btn-main " title="View">{{ $item->application_id }}</a></td>
            <td><span class="text-capitalize">{{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}</span></td>
            <td>{{ $item->registrationmaster->mail_id }}</td>
            <td>{{ $item->registrationmaster->mobile_no }}</td>
            <td>{{ $item->stdprogramMaster->code }} -{{ $item->stdprogramMaster->name }}</td>
            <td>
              @if($item->application_status == 1)
              <span class="badge bg-success">Payment Success</span>
              @else
              <span class="badge bg-primary text-light">Form Saved</span>
              @endif
            </td>
            <td>{{ $item->created_at->format('d M Y') }}</td>
            <td>


              <a href="#" class="btn  btn-dark" title="Edit">
                <i class="fas fa-edit"></i>
              </a>

              <!-- Button to trigger modal -->
              <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#sendSmsModal{{ $item->id }}" title="Send Interview SMS">
                <i class="fas fa-sms"></i>
              </button>

              <!-- Modal -->
              <div class="modal fade" id="sendSmsModal{{ $item->id }}" tabindex="-1" aria-labelledby="sendSmsModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog">
                  <form method="POST" action="{{ route('send.phase1.notification.single') }}">
                    @csrf
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="sendSmsModalLabel{{ $item->id }}">Send Interview SMS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Interview Date & Time</label>
                          <input type="datetime-local" name="interview_time" class="form-control" required>
                        </div>
                        <input type="hidden" name="id" value="{{ $item->registrationmaster->id }}">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-main"><i class="fas fa-sms"></i> Send SMS</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

            </td>
          </tr>
          @endforeach

        </tbody>
      </table>


    </div>
  </div>
</div>




@include('includes.footer')
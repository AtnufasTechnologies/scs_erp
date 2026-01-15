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
  <div class="card shadow-sm">
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
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Application Code</th>
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
            <td>{{ $item->application_id }}</td>
            <td>{{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}</td>
            <td>{{ $item->registrationmaster->mail_id }}</td>
            <td>{{ $item->registrationmaster->mobile_no }}</td>
            <td>{{ $item->program }}</td>
            <td>
              @if($item->payment_gateway_status == 'success')
              <span class="badge bg-success">Payment Success</span>
              @else
              <span class="badge bg-primary text-light">Form Saved</span>
              @endif
            </td>
            <td>{{ $item->created_at->format('d M Y') }}</td>
            <td>
              <a href="{{ route('admin.admission.ug.application-single', $item->id) }}" class="btn btn-sm btn-info" title="View">
                <i class="fas fa-eye"></i>
              </a>


              <a href="#" class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this application?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach

        </tbody>
      </table>


    </div>
  </div>
</div>




@include('includes.footer')
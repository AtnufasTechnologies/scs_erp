<?php

use App\Http\Controllers\StaticController;

?>
@include('includes.header')



<div class="container-fluid mt-4">
  <div class="container-fluid py-4">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar"
      style="background: linear-gradient(135deg, #17472f 0%, #8931f6 100%); border-radius: 0.75rem;">
      <div class=" container-fluid">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <h3><span class="text-light">Admission Application List</span></h3>
        <div class="d-flex">
          @if(StaticController::fetchUserRole() == 'dept-admin-erp')

          <a href="{{ route('department.dashboard') }}" class="btn btn-light">
            << Back
              </a>
              @endif
        </div>
      </div>
    </nav>
    <div class="card">
      <a href="{{route('department.admission.interview-list')}}"><button class="btn btn-main"> Interview List</button></a>

      <div class="card-body">

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

            </tr>
            @endforeach

          </tbody>
        </table>


      </div>
    </div>
  </div>
  @include('includes.footer')
@include('includes.header')
@include('admin.admission.sidebar')
<h3>Applications #{{$data->application_id}}</h3>
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-info text-light">
      <h4 class="mb-0">Applicant Details</h4>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Name:</strong> {{ $data->name }}
        </div>
        <div class="col-md-6">
          <strong>Email:</strong> {{ $data->email }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Date of Birth:</strong> {{ $data->dob }}
        </div>
        <div class="col-md-6">
          <strong>Phone:</strong> {{ $data->phone }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Gender:</strong> {{ $data->gender }}
        </div>
        <div class="col-md-6">
          <strong>Category:</strong> {{ $data->category }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Address:</strong> {{ $data->address }}
        </div>
        <div class="col-md-6">
          <strong>City:</strong> {{ $data->city }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>State:</strong> {{ $data->state }}
        </div>
        <div class="col-md-6">
          <strong>Pin Code:</strong> {{ $data->pincode }}
        </div>
      </div>
      <hr>
      <h5>Academic Details</h5>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Course Applied:</strong> {{ $data->course }}
        </div>
        <div class="col-md-6">
          <strong>Previous Qualification:</strong> {{ $data->qualification }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Marks Obtained:</strong> {{ $data->marks_obtained }}
        </div>
        <div class="col-md-6">
          <strong>Total Marks:</strong> {{ $data->total_marks }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <strong>Percentage:</strong> {{ $data->percentage }}%
        </div>
        <div class="col-md-6">
          <strong>Year of Passing:</strong> {{ $data->year_of_passing }}
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-12 text-end">
          <a href="{{ route('admission.ug.applications') }}" class="btn btn-secondary">Back to List</a>
        </div>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')
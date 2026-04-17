<?php

use App\Models\BloodGroupMaster;
use App\Models\Country;
use App\Models\ReligionMaster;
use App\Models\Subject;
use PhpParser\Node\Expr\Cast;

$bloodgroups = BloodGroupMaster::all();
$religions = ReligionMaster::all();
$countries = Country::all();

?>
@include('includes.header')
@include('admin.sidebar')

<div class="container mt-4">
  <div class="card">
    <div class="card-header ">
      <h4 class="mb-0">Edit {{ $application->registrationmaster->application_type }} Application</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admission.update.ug.application', $application->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
          @if($application->photo)
          <img src="{{ Storage::disk('s3')->url($application->photo) }}" alt="Photo" style="height: 80px;width:80px" class="mt-2 rounded float-end">
          @endif
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="mb-3">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control" value="{{ $application->registrationmaster->first_name }}">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control" value="{{ $application->registrationmaster->last_name }}">
            </div>
          </div>
          <div class="col-lg-4">
            <div class="mb-3">
              <label class="form-label">Campus</label>
              <input type="text" name="campus" class="form-control" value="{{ $application->registrationmaster->campusmaster->name }}" readonly>
              <input type="hidden" id="campusId" value="{{ $application->registrationmaster->campusmaster->id }}">
            </div>
          </div>

          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Application Code</label>
              <input type="text" name="application_code" class="form-control" value="{{ $application->application_code }}" readonly>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-control">
                <option value="male" {{ $application->gender == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ $application->gender == 'female' ? 'selected' : '' }}>Female</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Photo</label>
              <input type="file" name="photo" class="form-control">

            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="dob" class="form-control" value="{{ $application->dob }}">
            </div>
          </div>
          <div class="col-lg-4">

            <label for="">Blood Group <br>
              @error('bloodgroup') <span class="text-danger">{{ $message }}</span> @enderror
              <select class="form-control  radius-20 dark text-capitalize" name="bloodgroup">
                <option value="">-- Select --</option>
                @foreach ($bloodgroups as $blood)
                <option value="{{$blood->id}}" {{ $application->bloodgroup == $blood->id ? 'selected' : '' }}>{{$blood->name}}</option>
                @endforeach
              </select>
          </div>
          <div class="col-lg-4">
            <label for="">Religion</label><br>
            @error('religion') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark text-capitalize" name="religion" id="religion">
              <option value="">-- Select --</option>
              @foreach ($religions as $religion)
              <option value="{{$religion->id}}" {{ $application->religion == $religion->id ? 'selected' : '' }}>{{$religion->name}}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Mother Tongue</label>
              <input type="text" name="mothertongue" class="form-control" value="{{ $application->mothertongue }}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Caste</label>
              <select class="form-control mb-3 radius-20 dark" name="caste">
                <option value="">-- Select --</option>
                <option value="GEN" {{ $application->caste == 'GEN' ? 'selected' : '' }}>GEN </option>
                <option value="SC" {{ $application->caste == 'SC' ? 'selected' : '' }}>SC </option>
                <option value="ST" {{ $application->caste == 'ST' ? 'selected' : '' }}>ST </option>
                <option value="OBC" {{ $application->caste == 'OBC' ? 'selected' : '' }}>OBC</option>

              </select>

            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Physically Challenged</label>
              <select name="phychallenged" class="form-control">
                <option value="No" {{ $application->phychallenged == 'No' ? 'selected' : '' }}>No</option>
                <option value="Yes" {{ $application->phychallenged == 'Yes' ? 'selected' : '' }}>Yes</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Academic Information Section -->
        <h5 class="mb-3 text-secondary border-bottom pb-2">Academic Information</h5>
        <div class="row">


          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Department</label>
              <input type="text" class="form-control" value="{{$application->academicDeptMaster->title}}" readonly>
              <input type="hidden" name="department" value="{{$application->academicDeptMaster->id}}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Course</label>
              <input type="text" class="form-control" value="{{ $application->stdCourseMaster->name }}" readonly>
              <input type="hidden" name="course" value="{{ $application->stdCourseMaster->id }}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Has Laptop</label>
              <select name="has_laptop" class="form-control">
                <option value="1" {{ $application->has_laptop == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ $application->has_laptop == 0 ? 'selected' : '' }}>No</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">From Tea Estate</label>
              <select name="from_teaestate" class="form-control">
                <option value="1" {{ $application->from_teaestate == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ $application->from_teaestate == 0 ? 'selected' : '' }}>No</option>
              </select>
            </div>
          </div>
        </div>
    </div>

    <!-- Parent Information Section -->
    <h5 class="mb-3 text-secondary border-bottom pb-2">Parent/Guardian Information</h5>
    <div class="row">
      <div class="col-md-6">
        <h6 class="mb-2">Father</h6>
        <div class="mb-3">
          <label class="form-label">Father Name</label>
          <input type="text" name="father_name" class="form-control" value="{{ $application->father_name }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Father Contact</label>
          <input type="text" name="father_contact" class="form-control" value="{{ $application->father_contact }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Father Occupation</label>
          <input type="text" name="father_occupation" class="form-control" value="{{ $application->father_occupation }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Father Qualification</label>
          <input type="text" name="father_qualification" class="form-control" value="{{ $application->father_qualification }}">
        </div>
      </div>
      <div class="col-md-6">
        <h6 class="mb-2">Mother</h6>
        <div class="mb-3">
          <label class="form-label">Mother Name</label>
          <input type="text" name="mother_name" class="form-control" value="{{ $application->mother_name }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Mother Contact</label>
          <input type="text" name="mother_contact" class="form-control" value="{{ $application->mother_contact }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Mother Occupation</label>
          <input type="text" name="mother_occupation" class="form-control" value="{{ $application->mother_occupation }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Mother Qualification</label>
          <input type="text" name="mother_qualification" class="form-control" value="{{ $application->mother_qualification }}">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Annual Income</label>
          <input type="number" name="income" class="form-control" value="{{ $application->income }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Guardian Name</label>
          <input type="text" name="guardian_name" class="form-control" value="{{ $application->guardian_name }}">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Guardian Contact</label>
          <input type="text" name="guardian_contact" class="form-control" value="{{ $application->guardian_contact }}">
        </div>
      </div>
    </div>

    <!-- Address Information Section -->
    <h5 class="mb-3 text-secondary border-bottom pb-2">Permanent Address</h5>
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="permanent_address" class="form-control" value="{{ $application->permanent_address }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">District</label>
          <input type="text" name="district" class="form-control" value="{{ $application->district }}">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">City</label>
          <input type="text" name="city" class="form-control" value="{{ $application->city }}">
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">State</label>
          <input type="text" name="state" class="form-control" value="{{ $application->state }}">
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Pincode</label>
          <input type="text" name="pincode" class="form-control" value="{{ $application->pincode }}">
        </div>
      </div>
    </div>

    <!-- Local Address Section -->
    <h5 class="mb-3 text-secondary border-bottom pb-2">Local Address</h5>
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="local_address" class="form-control" value="{{ $application->local_address }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">District</label>
          <input type="text" name="local_district" class="form-control" value="{{ $application->local_district }}">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">City</label>
          <input type="text" name="local_city" class="form-control" value="{{ $application->local_city }}">
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">State</label>
          <input type="text" name="local_state" class="form-control" value="{{ $application->local_state }}">
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Pincode</label>
          <input type="text" name="local_pincode" class="form-control" value="{{ $application->local_pincode }}">
        </div>
      </div>
    </div>

    <!-- 10th Standard Section -->
    <h5 class="mb-3 text-secondary border-bottom pb-2">10th Standard Details</h5>
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Institution</label>
          <input type="text" name="institution10" class="form-control" value="{{ $application->institution10 }}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label">Roll No</label>
          <input type="text" name="rollno10" class="form-control" value="{{ $application->rollno10 }}">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Board</label>
          <input type="text" name="board10" class="form-control" value="{{ $application->board10 }}">
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Passing Year</label>
          <input type="number" name="passingyear10" class="form-control" value="{{ $application->passingyear10 }}">
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Certificate</label>
          <input type="file" name="certificate10" class="form-control">
          @if($application->certificate10)
          <a href="{{ Storage::disk('s3')->url($application->certificate10) }}" target="_blank" class="text-primary">View</a>
          @endif
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <label class="form-label">Subjects & Scores</label>
        @for($i = 1; $i <= 5; $i++)
          <div class="row mb-2">
          <div class="col-md-8">
            <input type="text" name="subject10_{{ $i }}" class="form-control" placeholder="Subject" value="{{ $application->{'subject10_' . $i} ?? '' }}">
          </div>
          <div class="col-md-4">
            <input type="number" name="score10_{{ $i }}" class="form-control" placeholder="Score" value="{{ $application->{'score10_' . $i} ?? '' }}">
          </div>
      </div>
      @endfor
    </div>
  </div>

  <!-- 12th Standard Section -->
  <h5 class="mb-3 text-secondary border-bottom pb-2">12th Standard Details</h5>
  <div class="row">
    <div class="col-md-6">
      <div class="mb-3">
        <label class="form-label">Institution</label>
        <input type="text" name="institution12" class="form-control" value="{{ $application->institution12 }}">
      </div>
    </div>
    <div class="col-md-6">
      <div class="mb-3">
        <label class="form-label">Roll No</label>
        <input type="text" name="rollno12" class="form-control" value="{{ $application->rollno12 }}">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="mb-3">
        <label class="form-label">Board</label>
        <input type="text" name="board12" class="form-control" value="{{ $application->board12 }}">
      </div>
    </div>
    <div class="col-md-4">
      <div class="mb-3">
        <label class="form-label">Passing Year</label>
        <input type="number" name="passingyear12" class="form-control" value="{{ $application->passingyear12 }}">
      </div>
    </div>
    <div class="col-md-4">
      <div class="mb-3">
        <label class="form-label">Certificate</label>
        <input type="file" name="certificate12" class="form-control">
        @if($application->certificate12)
        <a href="{{ Storage::disk('s3')->url($application->certificate12) }}" target="_blank" class="text-primary">View</a>
        @endif
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <label class="form-label">Subjects & Scores</label>
      @for($i = 1; $i <= 4; $i++)
        <div class="row mb-2">
        <div class="col-md-8">
          <input type="text" name="subject12_{{ $i }}" class="form-control" placeholder="Subject" value="{{ $application->{'subject12_' . $i} ?? '' }}">
        </div>
        <div class="col-md-4">
          <input type="number" name="score12_{{ $i }}" class="form-control" placeholder="Score" value="{{ $application->{'score12_' . $i} ?? '' }}">
        </div>
    </div>
    @endfor
  </div>
</div>

<!-- Additional Information -->
<h5 class="mb-3 text-secondary border-bottom pb-2">Additional Information</h5>
<div class="row">


  <div class="col-md-4">
    <div class="mb-3">
      <label for="" class="form-label">Nationality </label>
      <select name="nationality" id="nationality" class="form-control">

        @foreach($countries as $country)
        <option value="{{$country->id}}" {{ $application->registrationmaster->country == $country->id ? 'selected' : '' }}>{{$country->name}}</option>
        @endforeach
      </select>
    </div>
  </div>
  @if ($application->adhaar != null)
  <div class="col-md-4">
    <div class="mb-3">
      <label class="form-label">Adhaar</label>
      <input type="text" name="adhaar" class="form-control" value="{{ $application->adhaar }}">
    </div>
  </div>
  @endif
  @if ($application->adhaar_doc != null)
  <div class="col-md-4">
    <a href="{{Storage::disk('s3')->url($application->adhaar_doc) }}" target="_blank">
      <span class="text-success"> View Adhaar</span></a>
  </div>
  @else
  <div class="col-md-4">
    <span class="text-danger">Not Submitted</span>
  </div>
  @endif
  @if ($application->national_id_proof != null)
  <div class="col-md-4">
    <div class="mb-3">
      <label class="form-label">National ID Proof</label>
      <a href="{{Storage::disk('s3')->url($application->national_id_proof) }}" target="_blank" class="text-primary">View</a>

    </div>
  </div>
  @endif
  @if ($application->baptism != null)
  <div class="col-md-4">
    <div class="mb-3">
      <label class="form-label">Baptism</label>
      <a href="{{Storage::disk('s3')->url($application->baptism) }}" target="_blank" class="text-primary">View</a>
    </div>
  </div>
  @endif
</div>


<div class="row mt-4">
  <div class="col-md-12">
    <button type="submit" class="btn btn-primary">Update Application</button>
    <a href="javascript:history.back()" class="btn btn-secondary  ms-2">Cancel</a>
  </div>
</div>
</form>
</div>
</div>
</div>

@include('includes.footer')
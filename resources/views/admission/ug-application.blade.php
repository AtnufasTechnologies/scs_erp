@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">UG - Online Application Form</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:" aria-label="">
          <i class="fas fa-envelope"></i> admissions@salesiancollege.net
        </a>
        <a href="tel:" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622
        </a>


      </div>
    </div>
  </div>
</header>


<div class="container">
  <div class="col-lg-12">
    <div class="card shadow p-5 application-card radius-30 ">
      <div class="col-lg-6 col-sm-6">
        <a href="{{route('admission.apply.logout')}}">
          <button class="mb-3 btn btn-dark">Logout</button>
        </a>
      </div>
      <form action="{{route('submit.application.form')}}" enctype="multipart/form-data" method="POST" id="admission-application-form">
        @csrf
        <div class="row">
          <div class="col-lg-6">
            <h4>Hi, {{$data->fname}}</h4>
            <label for="">{{$data->mobile_no}} | {{$data->mail_id}}</label>
            <p class="grey">Mandatory field marked with <span class="text-danger">*</span> </p>
          </div>
          <div class="col-lg-6">

            <div class="row">

              <div class="col-lg-6">
                <label for="">Photograph (MaxSize: 5MB)<span class="text-danger">*</span></label><br>
                @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="file" class="form-control mb-3 radius-20 dark" name="photo">

              </div>
              <div class="col-lg-6">
                <label for="">Adhaar (MaxSize: 5MB)<span class="text-danger">*</span></label><br>
                @error('adhaar') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="file" class="form-control mb-3 radius-20 dark" name="adhaar">

              </div>
            </div>
          </div>

        </div>
        <hr>
        <div class="row">
          <h5>{{$data->programInfo->campus->name}} - {{$data->programInfo->name}} Admission Form</h5>
          <div class="col-lg-3 col-sm-12">
            <label for="">Departments <span class="text-danger">*</span></label><br>
            @error('department') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="department" id="department">
              <option value="">-- Select Course--</option>
              @foreach ($departments as $department)
              <option value="{{$department->id}}">{{$department->name}}</option>
              @endforeach

            </select>
          </div>
          <div class="col-lg-3 col-sm-12">
            <label for="">Course <span class="text-danger">*</span></label><br>
            @error('course') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="course" id="course">
              <option value="">-- Select Course--</option>

            </select>
          </div>

          <div class="col-lg-3 col-sm-12">
            <label for="">Date of Birth <span class="text-danger">*</span></label><br>
            @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="date" class="form-control mb-3 radius-20 dark" name="dob" value="{{old('dob')}}">

          </div>

          <div class="col-lg-3">
            <label for="">Blood Group <span class="text-danger">*</span></label><br>
            @error('bloodgroup') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="bloodgroup">
              <option value="">-- Select --</option>
              @foreach ($bloodgroups as $blood)
              <option value="{{$blood}}" {{ old('bloodgroup') == $blood ? 'selected' : '' }}>{{$blood}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3">
            <label for="">Gender <span class="text-danger">*</span></label><br>
            @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="gender">
              <option value="">-- Select --</option>
              <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male </option>
              <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female </option>
              <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other </option>
            </select>
          </div>

          <div class="col-lg-3">
            <label for="">Religion <span class="text-danger">*</span></label><br>
            @error('religion') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="religion" wire:change="getReligionId" id="religion">
              <option value="">-- Select --</option>
              @foreach ($religions as $religion)
              <option value="{{$religion->id}}" {{ old('religion') == $religion->id ? 'selected' : '' }}>{{$religion->name}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3 col-sm-12" id="baptism">
            <label for="">Baptism <span class="text-danger">*</span></label><br>
            @error('baptism') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="file" class="form-control mb-3 radius-20 dark" name="baptism">

          </div>


          <div class="col-lg-3 col-sm-12">
            <label for="">Mother Tongue <span class="text-danger">*</span></label><br>
            @error('mothertongue') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="mothertongue" value="{{old('mothertongue')}}">

          </div>

          <div class="col-lg-2">
            <label for=""><small>Physically Challanged </small><span class="text-danger">*</span></label>
            @error('phychallenged') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="phychallenged">
              <option value="">-- Select --</option>
              <option value="No" {{ old('phychallenged') == 'No' ? 'selected' : '' }}>No </option>
              <option value="Yes" {{ old('phychallenged') == 'Yes' ? 'selected' : '' }}>Yes </option>

            </select>
          </div>
          <div class="col-lg-3">
            <label for=""><small>Caste </small><span class="text-danger">*</span></label><br>
            @error('caste') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="caste">
              <option value="">-- Select --</option>
              <option value="GEN" {{ old('caste') == 'GEN' ? 'selected' : '' }}>GEN </option>
              <option value="SC" {{ old('caste') == 'SC' ? 'selected' : '' }}>SC </option>
              <option value="ST" {{ old('caste') == 'ST' ? 'selected' : '' }}>ST </option>
              <option value="OBC" {{ old('caste') == 'OBC' ? 'selected' : '' }}>OBC</option>

            </select>
          </div>


          <hr>

          <div class="col-lg-4 col-sm-12">
            <label for="">Father's Name <span class="text-danger">*</span></label><br>
            @error('father_name') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="father_name" value="{{old('father_name')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Father's Occupation <span class="text-danger">*</span></label><br>
            @error('father_occupation') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="father_occupation" value="{{old('father_occupation')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Father's Contact <span class="text-danger">*</span></label><br>
            @error('father_contact') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="father_contact" value="{{old('father_contact')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Mother's Name <span class="text-danger">*</span></label><br>
            @error('mother_name') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="mother_name" value="{{old('mother_name')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Mother's Occupation <span class="text-danger">*</span></label><br>
            @error('mother_occupation') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="mother_occupation" value="{{old('mother_occupation')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Mother's Contact <span class="text-danger">*</span></label><br>
            @error('mother_contact') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="mother_contact" value="{{old('mother_contact')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Guardian's Name </label><br>
            @error('guardian_name') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="guardian_name" value="{{old('guardian_name')}}">

          </div>

          <div class="col-lg-4 col-sm-12">
            <label for="">Guardian's Contact </label>
            @error('guardian_contact') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="guardian_contact" value="{{old('guardian_contact')}}">

          </div>

          <div class="col-lg-4"></div>
          <hr>
          <div class="col-lg-4 col-sm-12">
            <label for="">Family Monthly Income <span class="text-danger">*</span></label>
            @error('income') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="income" value="{{old('income')}}" placeholder="ex: 50000">
          </div>

          <hr>

          <div class="col-lg-6">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Permanent Address <span class="text-danger">*</span></label>
                @error('permanent_address') <span class="text-danger">{{ $message }}</span> @enderror
                <textarea name="permanent_address" cols="10" class="form-control">{{old('permanent_address')}}</textarea>
              </div>
              <div class="col-lg-6 col-sm-12">
                <label for="">Pincode <span class="text-danger">*</span></label>
                @error('permanent_address_pincode') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="permanent_address_pincode" value="{{old('permanent_address_pincode')}}">
              </div>

            </div>
          </div>
          <div class="col-lg-6">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="isChecked" name="isChecked">
                  <label for="">Local Address Same as Permanent Address</label>
                </div>
                @error('local_address') <span class=" text-danger">{{ $message }}</span> @enderror
                <textarea name="local_address" cols="10" class="form-control" id="local_address"></textarea>
              </div>
              <div class="col-lg-6 col-sm-12" id="local_pin">
                <label for="">Pincode </label>
                @error('local_address_pincode') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="local_address_pincode">
              </div>

            </div>
          </div>
          <hr>
          <h3>Class X</h3>
          <div class="col-lg-6 col-sm-12">
            <div class="row">

              <div class="col-lg-12 col-sm-12">
                <label for="">Institution Name <span class="text-danger">*</span></label>
                @error('institution10') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="institution10" value="{{old('institution10')}}">
              </div>
            </div>
          </div>

          <div class="col-lg-6 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Class 10 Certificate <span class="text-danger">*</span></label>
                @error('certificate10') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="file" class="form-control mb-3 radius-20 dark" name="certificate10" value="{{old('certificate10')}}">
              </div>
            </div>
          </div>
          <hr>
          <h3>Class XII</h3>
          <div class="col-lg-6 col-sm-12">
            <div class="row">

              <div class="col-lg-12 col-sm-12">
                <label for="">Institution Name <span class="text-danger">*</span></label>
                @error('institution12') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="institution12" value="{{old('institution12')}}">
              </div>
            </div>
          </div>

          <div class="col-lg-6 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Class 12 Certificate <span class="text-danger">*</span></label>
                @error('certificate10') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="file" class="form-control mb-3 radius-20 dark" name="certificate12" value="{{old('certificate12')}}">
              </div>
            </div>
          </div>
          <div class="row">



            <div class="col-lg-6 col-sm-12">

              @error('sub1') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="sub1" placeholder="Subject Name" value="{{old('sub1')}}">
            </div>
            <div class="col-lg-3 col-sm-12">

              @error('score1') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="score1" placeholder="Score" value="{{old('score1')}}">
            </div>
            <div class="col-lg-3 col-sm-12">
              <input type="text" class="form-control mb-3 radius-20 dark" readonly value="100">
            </div>


            <div class="col-lg-6 col-sm-12">

              @error('sub2') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="sub2" placeholder="Subject Name" value="{{old('sub2')}}">
            </div>
            <div class="col-lg-3 col-sm-12">

              @error('score2') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="score2" placeholder="Score" value="{{old('score2')}}">
            </div>
            <div class="col-lg-3 col-sm-12">
              <input type="text" class="form-control mb-3 radius-20 dark" readonly value="100">
            </div>


            <div class="col-lg-6 col-sm-12">

              @error('sub3') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="sub3" placeholder="Subject Name" value="{{old('sub3')}}">
            </div>
            <div class="col-lg-3 col-sm-12">

              @error('score3') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="score3" placeholder="Score" value="{{old('score3')}}">
            </div>
            <div class="col-lg-3 col-sm-12">
              <input type="text" class="form-control mb-3 radius-20 dark" readonly value="100">
            </div>


            <div class="col-lg-6 col-sm-12">

              @error('sub4') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="sub4" placeholder="Subject Name" value="{{old('sub4')}}">
            </div>
            <div class="col-lg-3 col-sm-12">

              @error('score4') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="score4" placeholder="Score" value="{{old('score4')}}">
            </div>
            <div class="col-lg-3 col-sm-12">
              <input type="text" class="form-control mb-3 radius-20 dark" readonly value="100">
            </div>

            <div class="col-lg-6 col-sm-12">

              @error('sub5') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="sub5" placeholder="Subject Name" value="{{old('sub5')}}">
            </div>
            <div class="col-lg-3 col-sm-12">

              @error('score5') <span class="text-danger">{{ $message }}</span> @enderror
              <input type="text" class="form-control mb-3 radius-20 dark" name="score5" placeholder="Score" value="{{old('score4')}}">
            </div>
            <div class="col-lg-3 col-sm-12">
              <input type="text" class="form-control mb-3 radius-20 dark" readonly value="100">
            </div>

          </div>

        </div>
        <button type="submit" class="btn btn-main radius-20 mt-3" id="admission-submitBtn">Continue Payment</button>

      </form>

    </div>
  </div>



</div>


@include('includes.footer')
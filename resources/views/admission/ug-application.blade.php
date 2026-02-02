@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">UG - Online Application Form {{$data->batch_name}}</span></h6>
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
      <form action="{{url('erp/new-admission/submit-ug-application-form')}}" enctype="multipart/form-data" method="POST" id="admission-application-form">
        @csrf
        <div class="row">
          <div class="col-lg-6">
            <h4>Hi, <span class="text-capitalize">{{$data->first_name}}</span></h4>
            <label for="">{{$data->mobile_no}} | {{$data->mail_id}}</label>
            <p class="grey">Mandatory field marked with <span class="text-danger">*</span> </p>
            Upload Recent Passport Size Photograph
            (Max Size: 5MB) Supported Format: JPG, PNG <span class="text-danger">*</span>
            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="file" class="form-control mb-3 radius-20 dark" name="photo" id="photo" accept="image/*" style="margin-top: 10px;">

          </div>

          <div class="col-lg-2 offset-4">
            <div class="photo-upload-container" style="border: 2px dashed #ccc; padding: 15px; text-align: center; border-radius: 10px; background: #f9f9f9;">
              <img id="photo-preview" src="" alt="Photo preview" style="max-width: 100px; max-height: 150px; display: none; object-fit: cover; border: 2px solid #ddd; border-radius: 5px;">
              <div id="upload-placeholder" style="margin: 10px 0;">

              </div>
            </div>
            <label for=""> Preview<span class="text-danger">*</span></label><br>
            @error('photo') <span class="text-danger">{{ $message }}</span> @enderror

          </div>

          <script>
            document.getElementById('photo').addEventListener('change', function(e) {
              const file = e.target.files[0];
              if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                  const preview = document.getElementById('photo-preview');
                  preview.src = e.target.result;
                  preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
              }
            });
          </script>



        </div>
        <hr>
        <div class="row">
          <h5>{{$data->campusmaster->name}} - {{$data->application_type}} </h5>
          <input type="hidden" name="campusId" value="{{$data->campusmaster->id}}" id="campusId">


          <div class="col-lg-4 col-sm-12">
            <label for="">Department Applying For<span class="text-danger">*</span></label><br>
            @error('department') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark text-capitalize" name="department" id="admissiondepartment">
              <option value="">-- Select Department--</option>
              @foreach ($academic_departments as $dept)
              <option value="{{$dept->id}}">{{$dept->title}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-8 col-sm-12">
            <label for="">Departmental Combinations<span class="text-danger">*</span></label><br>
            @error('course') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark " name="course" id="coursecombinations">
              <option value="">-- Select Combination--</option>
            </select>
          </div>



          <div class="col-lg-3 col-sm-12">
            <label for="">Date of Birth <span class="text-danger">*</span></label><br>
            @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="date" class="form-control mb-3 radius-20 dark" name="dob" value="{{old('dob')}}">

          </div>

          <div class="col-lg-2">
            <label for="">Blood Group <span class="text-danger">*</span></label><br>
            @error('bloodgroup') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark text-capitalize" name="bloodgroup">
              <option value="">-- Select --</option>
              @foreach ($bloodgroups as $blood)
              <option value="{{$blood->id}}" {{ old('bloodgroup->id') == $blood->id ? 'selected' : '' }}>{{$blood->name}}</option>
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

          <div class="col-lg-4">
            <label for="">Religion <span class=" text-danger">*</span></label><br>
            @error('religion') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark text-capitalize" name="religion" id="religion">
              <option value="">-- Select --</option>
              @foreach ($religions as $religion)
              <option value="{{$religion->id}}" {{ old('religion') == $religion->id ? 'selected' : '' }}>{{$religion->name}}</option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-3 col-sm-12" id="baptism" style="display: none;">
            <label for="">Baptism <span class="text-danger">*</span></label><br>
            @error('baptism') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="file" class="form-control mb-3 radius-20 dark" name="baptism">
          </div>

          <script>
            document.getElementById('religion').addEventListener('change', function() {
              const selectedText = this.options[this.selectedIndex].text.toLowerCase();
              const baptismField = document.getElementById('baptism');

              if (selectedText.includes('catholic')) {
                baptismField.style.display = 'block';
              } else {
                baptismField.style.display = 'none';
              }
            });

            // Check on page load if religion is already selected (for old values)
            window.addEventListener('DOMContentLoaded', function() {
              const religionSelect = document.getElementById('religion');
              if (religionSelect.value) {
                religionSelect.dispatchEvent(new Event('change'));
              }
            });
          </script>


          <div class="col-lg-3 col-sm-12">
            <label for="">Mother Tongue <span class="text-danger">*</span></label><br>
            @error('mothertongue') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="mothertongue" value="{{old('mothertongue')}}">

          </div>
          @if ($data->countrymaster->name == 'India')
          <div class="col-lg-3 col-sm-12">
            <label for="">Adhaar No </label><br>

            <input type="text" class="form-control mb-3 radius-20 dark" name="adhaar" value="{{old('adhaar')}}">
          </div>


          @else
          <div class="col-lg-4 col-sm-12">
            <label for="">Upload Any {{$data->countrymaster->name}} Govt Identity Proof </label><br>
            <input type="file" name="national_id_proof" class="form-control">
          </div>
          @endif


          <div class="col-lg-3">
            <label for=""><small>Physically Challanged </small><span class="text-danger">*</span></label>
            @error('phychallenged') <span class="text-danger">{{ $message }}</span> @enderror
            <select class="form-control mb-3 radius-20 dark" name="phychallenged">
              <option value="">-- Select --</option>
              <option value="No" {{ old('phychallenged') == 'No' ? 'selected' : '' }}>No </option>
              <option value="Yes" {{ old('phychallenged') == 'Yes' ? 'selected' : '' }}>Yes </option>

            </select>
          </div>
          <div class="col-lg-2">
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
          <div class="row mb-3">
            <div class="col-lg-6">
              <input type="checkbox" name="laptop_checkbox" id=""> Do you have a Laptop/Computer at Home?
            </div>
            <div class="col-lg-6">
              <input type="checkbox" name="teaestate_checkbox" id=""> Do you reside in a Tea Estate?
            </div>
          </div>

          <hr>
          <h5>Parent/Guardian Details</h5>
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

          <div class="col-lg-4 col-sm-12">
            <label for="">Family Monthly Income <span class="text-danger">*</span></label>
            @error('income') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="income" value="{{old('income')}}" placeholder="ex: 50000">
          </div>
          <hr>
          <h5>Address</h5>
          <div class="col-lg-12">
            <div class="row">
              <div class="col-lg-4">
                <div class="col-lg-12 col-sm-12">
                  <label for="">Permanent Address <span class="text-danger">*</span></label>
                  @error('permanent_address') <span class="text-danger">{{ $message }}</span> @enderror
                  <textarea name="permanent_address" cols="10" class="form-control mb-3" id="permanent_address">{{old('permanent_address')}}</textarea>
                </div>

              </div>
              <div class="col-lg-8">
                <div class="row">
                  <div class="col-lg-4 col-sm-12">
                    <label for="">District <span class="text-danger">*</span></label>
                    @error('district') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark" name="district" id="district" value="{{old('district')}}">
                  </div>

                  <div class="col-lg-4 col-sm-12">
                    <label for="">City <span class="text-danger">*</span></label>
                    @error('city') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark" name="city" id="city" value="{{old('city')}}">
                  </div>

                  <div class="col-lg-4 col-sm-12">
                    <label for="">Pincode <span class="text-danger ">*</span></label>
                    @error('pincode') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark " name="pincode" id="pincode" value="{{old('pincode')}}">
                  </div>
                </div>

              </div>


            </div>
          </div>

          <div class="col-lg-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="isChecked" name="isChecked">
              <label for="">Local Address Same as Permanent Address</label>
            </div>
            <div class="row">
              <div class="col-lg-4">
                <div class="col-lg-12 col-sm-12">
                  <label for="">Local Address <span class="text-danger">*</span></label>
                  @error('local_address') <span class="text-danger">{{ $message }}</span> @enderror
                  <textarea name="local_address" cols="10" class="form-control mb-3" id="local_address">{{old('local_address')}}</textarea>
                </div>

              </div>
              <div class="col-lg-8">
                <div class="row">
                  <div class="col-lg-4 col-sm-12">
                    <label for="">District <span class="text-danger">*</span></label>
                    @error('local_district') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark" name="local_district" id="local_district" value="{{old('local_district')}}">
                  </div>

                  <div class="col-lg-4 col-sm-12">
                    <label for="">City <span class="text-danger">*</span></label>
                    @error('local_city') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark" name="local_city" id="local_city" value="{{old('local_city')}}">
                  </div>

                  <div class="col-lg-4 col-sm-12">
                    <label for="">Pincode <span class="text-danger ">*</span></label>
                    @error('local_pincode') <span class="text-danger">{{ $message }}</span> @enderror
                    <input type="text" class="form-control mb-3 radius-20 dark " name="local_pincode" id="local_pincode" value="{{old('local_pincode')}}">
                  </div>
                </div>

              </div>


            </div>
          </div>

          <script>
            document.getElementById('isChecked').addEventListener('change', function() {
              if (this.checked) {
                document.getElementById('local_address').value = document.getElementById('permanent_address').value;
                document.getElementById('local_district').value = document.getElementById('district').value;
                document.getElementById('local_city').value = document.getElementById('city').value;
                document.getElementById('local_pincode').value = document.getElementById('pincode').value;
              } else {
                document.getElementById('local_address').value = '';
                document.getElementById('local_district').value = '';
                document.getElementById('local_city').value = '';
                document.getElementById('local_pincode').value = '';
              }
            });
          </script>

          <hr>
          <h3>Class X</h3>
          <div class="col-lg-5 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Institution Name <span class="text-danger">*</span></label>
                @error('institution10') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="institution10" value="{{old('institution10')}}">
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for=""> RollNo / Unique Id<span class="text-danger">*</span></label>
                @error('rollno10') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="rollno10" value="{{old('rollno10')}}">
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Board<span class="text-danger">*</span></label>
                @error('board') <span class="text-danger">{{ $message }}</span> @enderror
                <select name="board10" class="form-control ">
                  <option value="">Select</option>
                  <option value="icse">ICSE</option>
                  <option value="madhyamik">Madhyamik</option>
                  <option value="cbse">CBSE</option>
                  <option value="nios">NIOS</option>
                  <option value="open board">Open Board</option>
                  <option value="others">Others</option>
                </select>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for=""> Year of Passing<span class="text-danger">*</span></label>
                @error('passingyear') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="number" class="form-control mb-3 radius-20 dark" name="passingyear10" value="{{old('passingyear10')}}" min="1950" max="{{date('Y')}}" placeholder="YYYY">
              </div>
            </div>
          </div>




          <div class="col-lg-5 col-sm-12">
            <label for="">Class 10 Certificate (PDF - Max 5MB) <span class="text-danger">*</span></label>
            @error('certificate10') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="file" class="form-control mb-3 radius-20 dark" name="certificate10" value="{{old('certificate10')}}">
          </div>

          <div class="col-lg-3 col-sm-12">
            <label for="">Percentage (Best of 5)</label>
            @error('percentage10') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="percentage10" placeholder="60" value="{{old('percentage10')}}" id="percentage10">
          </div>


          <div class="row mb-2">
            <div class="col-lg-2">
              <h5>Pass Mark <span class="text-danger">*</span></h5>
            </div>
            <div class="col-lg-2">
              <input type="number" name="passmark10" class="form-control" placeholder=" Mark" min="0" max="100" value="{{old('passmark10')}}">
            </div>
            <div class="col-lg-2">
              <h5>Full Marks </h5>
            </div>
            <div class="col-lg-2">
              <input type="number" name="fullmark10" class="form-control" readonly value="100">
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subject</th>

                  <th>Marks Obtained</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject10_1" placeholder="ex:English" value="{{old('subject10_1')}}">
                    @error('subject10_1') <span class="text-danger">{{ $message }}</span> @enderror
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score10_1" placeholder="Score" value="{{old('score10_1')}}" min="0" max="100">
                    @error('score10_1') <span class="text-danger">{{ $message }}</span> @enderror
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject10_2" placeholder="Major 1" value="{{old('subject10_2')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score10_2" placeholder="Score" value="{{old('score10_2')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject10_3" placeholder="Major 2" value="{{old('subject10_3')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score10_3" placeholder="Score" value="{{old('score10_3')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject10_4" placeholder="Major 3" value="{{old('subject10_4')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score10_4" placeholder="Score" value="{{old('score10_4')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject10_5" placeholder="Major 4" value="{{old('subject10_5')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score10_5" placeholder="Score" value="{{old('score10_5')}}" min="0" max="100">
                  </td>
                </tr>
              </tbody>
            </table>
          </div>


          <hr>
          <h3>Class XII </h3>
          <div class="col-lg-5 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Institution Name <span class="text-danger">*</span></label>
                @error('institution12') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="institution12" value="{{old('institution12')}}">
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for=""> RollNo / Unique Id<span class="text-danger">*</span></label>
                @error('rollno12') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="text" class="form-control mb-3 radius-20 dark" name="rollno12" value="{{old('rollno12')}}">
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for="">Board<span class="text-danger">*</span></label>
                @error('board12') <span class="text-danger">{{ $message }}</span> @enderror
                <select name="board12" class="form-control ">
                  <option value="">Select</option>
                  <option value="isc">ISC</option>
                  <option value="hs">Higher Secondary</option>
                  <option value="cbse">CBSE</option>
                  <option value="nios">NIOS</option>
                  <option value="open board">Open Board</option>
                  <option value="others">Others</option>
                </select>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-sm-12">
            <div class="row">
              <div class="col-lg-12 col-sm-12">
                <label for=""> Year of Passing<span class="text-danger">*</span></label>
                @error('passingyear12') <span class="text-danger">{{ $message }}</span> @enderror
                <input type="number" class="form-control mb-3 radius-20 dark" name="passingyear12" value="{{old('passingyear12')}}" min="1950" max="{{date('Y')}}" placeholder="YYYY">
              </div>
            </div>
          </div>

          <div class="col-lg-5 col-sm-12">
            <label for="">Class 12 Certificate <span class="text-danger">*</span></label>
            @error('certificate12') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="file" class="form-control mb-3 radius-20 dark" name="certificate12" value="{{old('certificate12')}}">
          </div>

          <div class="col-lg-3 col-sm-12">
            <label for="">Percentage (Best of 5)</label>
            @error('percentage12') <span class="text-danger">{{ $message }}</span> @enderror
            <input type="text" class="form-control mb-3 radius-20 dark" name="percentage12" placeholder="60" value="{{old('percentage12')}}" id="percentage12">
          </div>

          <div class="row mb-2">
            <div class="col-lg-2">
              <h5>Pass mark <span class="text-danger">*</span></h5>
            </div>
            <div class="col-lg-2">
              <input type="number" name="passmark12" class="form-control" placeholder=" Mark" min="0" max="100" value="{{old('passmark12')}}">
            </div>
            <div class="col-lg-2">
              <h5>Full Marks </h5>
            </div>
            <div class="col-lg-2">
              <input type="number" name="fullmark12" class="form-control" readonly value="100">
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subject</th>

                  <th> Marks Obtained</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject12_1" placeholder="English" value="{{old('subject12_1')}}">
                    @error('subject12_1') <span class="text-danger">{{ $message }}</span> @enderror
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score12_1" placeholder="Score" value="{{old('score12_1')}}" min="0" max="100">
                    @error('score12_1') <span class="text-danger">{{ $message }}</span> @enderror
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject12_2" placeholder="Major 1" value="{{old('subject12_2')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score12_2" placeholder="Score" value="{{old('score12_2')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject12_3" placeholder="Major 2" value="{{old('subject12_3')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score12_3" placeholder="Score" value="{{old('score12_3')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject12_4" placeholder="Major 3" value="{{old('subject12_4')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score12_4" placeholder="Score" value="{{old('score12_4')}}" min="0" max="100">
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="text" class="form-control" name="subject12_5" placeholder="Major 4" value="{{old('subject12_5')}}">
                  </td>


                  <td>
                    <input type="number" class="form-control" name="score12_5" placeholder="Score" value="{{old('score12_5')}}" min="0" max="100">
                  </td>
                </tr>
              </tbody>
            </table>
          </div>



        </div>
        <center>
          <button type="submit" class="btn btn-main radius-30 mt-3" id="admission-submitBtn">Continue To Payment >> </button>
        </center>

      </form>

    </div>
  </div>



</div>

@include('includes.footer')
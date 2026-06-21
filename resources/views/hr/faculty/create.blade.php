<?php

use App\Models\Campus;

$campuses = Campus::all();
?>
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
            <li class="breadcrumb-item"><a href="{{ route('hr.faculty.index') }}">Faculty List</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Faculty</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Faculty Member</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('hr.faculty.store') }}" method="POST" enctype="multipart/form-data">
              @csrf

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Basic Information</h6>
                </div>

                <div class="col-md-2 mb-3">
                  <label class="form-label">Employee Code <span class="text-danger">*</span></label>
                  <input type="text" name="USER_CODE" class="form-control @error('USER_CODE') is-invalid @enderror" value="{{ old('USER_CODE') }}" required>
                  @error('USER_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" name="FIRST_NAME" class="form-control @error('FIRST_NAME') is-invalid @enderror" value="{{ old('FIRST_NAME') }}" required>
                  @error('FIRST_NAME')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Middle Name</label>
                  <input type="text" name="MIDDLE_NAME" class="form-control @error('MIDDLE_NAME') is-invalid @enderror" value="{{ old('MIDDLE_NAME') }}">
                  @error('MIDDLE_NAME')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="LAST_NAME" class="form-control @error('LAST_NAME') is-invalid @enderror" value="{{ old('LAST_NAME') }}">
                  @error('LAST_NAME')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Gender <span class="text-danger">*</span></label>
                  <select name="GENDER" class="form-select @error('GENDER') is-invalid @enderror" required>
                    <option value="">Select Gender</option>
                    <option value="1" {{ old('GENDER') == '1' ? 'selected' : '' }}>Male</option>
                    <option value="2" {{ old('GENDER') == '2' ? 'selected' : '' }}>Female</option>
                    <option value="3" {{ old('GENDER') == '3' ? 'selected' : '' }}>Other</option>
                  </select>
                  @error('GENDER')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" name="DOB" class="form-control @error('DOB') is-invalid @enderror" value="{{ old('DOB') }}">
                  @error('DOB')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Date of Joining</label>
                  <input type="date" name="DOJ" class="form-control @error('DOJ') is-invalid @enderror" value="{{ old('DOJ') }}">
                  @error('DOJ')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Nationality</label>
                  <select name="NATIONALITY" class="form-select @error('NATIONALITY') is-invalid @enderror dselect-example">
                    <option value="">Select Nationality</option>
                    @foreach($nationalities as $nationality)
                    <option value="{{ $nationality->id }}" {{ old('NATIONALITY') == $nationality->id ? 'selected' : '' }}>{{ $nationality->name }}</option>
                    @endforeach
                  </select>
                  @error('NATIONALITY')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Campus <span class="text-danger">*</span></label>
                  <select name="CAMPUS_ID" class="form-select @error('CAMPUS_ID') is-invalid @enderror" required>
                    <option value="">Select Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ old('CAMPUS_ID') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                  </select>
                </div>
                <!-- <div class="col-md-4 mb-3">
                  <label class="form-label">Photo</label>
                  <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                  @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div> -->
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Contact Information</h6>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" name="MAIL_ID" class="form-control @error('MAIL_ID') is-invalid @enderror" value="{{ old('MAIL_ID') }}" required>
                  @error('MAIL_ID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                  <input type="text" name="MOBILE_NO" class="form-control @error('MOBILE_NO') is-invalid @enderror" value="{{ old('MOBILE_NO') }}" required>
                  @error('MOBILE_NO')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Current Address</label>
                  <textarea name="ADDRESS" class="form-control @error('ADDRESS') is-invalid @enderror" rows="2">{{ old('ADDRESS') }}</textarea>
                  @error('ADDRESS')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Permanent Address</label>
                  <textarea name="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="2">{{ old('permanent_address') }}</textarea>
                  @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Professional Details</h6>
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">Employee Type <span class="text-danger">*</span></label>
                  <select name="employee_type" class="form-select @error('employee_type') is-invalid @enderror">
                    <option value="">Select Type</option>
                    <option value="permanent" {{ old('employee_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contractual" {{ old('employee_type') == 'contractual' ? 'selected' : '' }}>Contractual</option>
                    <option value="adhoc" {{ old('employee_type') == 'adhoc' ? 'selected' : '' }}>Adhoc</option>
                    <option value="guest" {{ old('employee_type') == 'guest' ? 'selected' : '' }}>Guest</option>
                    <option value="visiting" {{ old('employee_type') == 'visiting' ? 'selected' : '' }}>Visiting</option>
                  </select>
                  @error('employee_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-2 mb-3">
                  <label class="form-label">User Type <span class="text-danger">*</span></label>
                  <select name="user_type" class="form-select @error('user_type') is-invalid @enderror">
                    <option value="">Select Type</option>
                    <option value="1" {{ old('user_type') == '1' ? 'selected' : '' }}>Teaching Staff</option>
                    <option value="2" {{ old('user_type') == '2' ? 'selected' : '' }}>Non-Teaching Staff</option>

                  </select>
                  @error('user_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3 mb-3">
                  <label class="form-label">Designation</label>
                  <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}" placeholder="e.g., Professor, Associate Professor">
                  @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Experience (Years)</label>
                  <input type="number" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror" value="{{ old('experience_years') }}" min="0">
                  @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Qualification</label>
                  <input type="text" name="qualification" class="form-control @error('qualification') is-invalid @enderror" value="{{ old('qualification') }}" placeholder="e.g., Ph.D., M.Tech">
                  @error('qualification')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Specialization</label>
                  <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization') }}" placeholder="e.g., Computer Science, Mathematics">
                  @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Responsibility</label>
                  <input type="text" name="responsibility" class="form-control @error('responsibility') is-invalid @enderror" value="{{ old('responsibility') }}" placeholder="e.g., Event Coordinator, HR, Programmer Coordinator">
                  @error('responsibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Paper Publications Count</label>
                  <input type="number" name="paper_publications_count" class="form-control @error('paper_publications_count') is-invalid @enderror" value="{{ old('paper_publications_count', 0) }}" min="0">
                  @error('paper_publications_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">ORCID ID</label>
                  <input type="text" name="orcid_id" class="form-control @error('orcid_id') is-invalid @enderror" value="{{ old('orcid_id') }}" placeholder="e.g., 0000-0002-1234-5678">
                  @error('orcid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Banking & ID Details</h6>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">PAN Number</label>
                  <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" value="{{ old('pan_number') }}">
                  @error('pan_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Aadhar Number</label>
                  <input type="text" name="aadhar_number" class="form-control @error('aadhar_number') is-invalid @enderror" value="{{ old('aadhar_number') }}">
                  @error('aadhar_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Bank Name</label>
                  <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}">
                  @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Bank Account Number</label>
                  <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number') }}">
                  @error('bank_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">IFSC Code</label>
                  <input type="text" name="bank_ifsc_code" class="form-control @error('bank_ifsc_code') is-invalid @enderror" value="{{ old('bank_ifsc_code') }}">
                  @error('bank_ifsc_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Emergency Contact</h6>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Emergency Contact Name</label>
                  <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name') }}">
                  @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Emergency Contact Number</label>
                  <input type="text" name="emergency_contact_number" class="form-control @error('emergency_contact_number') is-invalid @enderror" value="{{ old('emergency_contact_number') }}">
                  @error('emergency_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Faculty</button>
                  <a href="{{ route('hr.faculty.index') }}" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
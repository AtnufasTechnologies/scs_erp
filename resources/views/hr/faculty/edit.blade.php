<?php

use App\Models\Campus;

$campuses = Campus::all();
?>
@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Faculty Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.faculty.index') }}">Faculty List</a></li>
            <li class="breadcrumb-item active">Edit Faculty</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Faculty Member</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('hr.faculty.update', $faculty->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Basic Information</h6>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Employee Code <span class="text-danger">*</span></label>
                  <input type="text" name="USER_CODE" class="form-control @error('USER_CODE') is-invalid @enderror" value="{{ old('USER_CODE', $faculty->USER_CODE) }}" required>
                  @error('USER_CODE')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" name="FIRST_NAME" class="form-control @error('FIRST_NAME') is-invalid @enderror" value="{{ old('FIRST_NAME', $faculty->FIRST_NAME) }}" required>
                  @error('FIRST_NAME')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Middle Name</label>
                  <input type="text" name="MIDDLE_NAME" class="form-control" value="{{ old('MIDDLE_NAME', $faculty->MIDDLE_NAME) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="LAST_NAME" class="form-control" value="{{ old('LAST_NAME', $faculty->LAST_NAME) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Gender <span class="text-danger">*</span></label>
                  <select name="GENDER" class="form-select @error('GENDER') is-invalid @enderror" required>
                    <option value="">Select Gender</option>
                    <option value="1" {{ old('GENDER', $faculty->GENDER) == '1' ? 'selected' : '' }}>Male</option>
                    <option value="2" {{ old('GENDER', $faculty->GENDER) == '2' ? 'selected' : '' }}>Female</option>
                    <option value="3" {{ old('GENDER', $faculty->GENDER) == '3' ? 'selected' : '' }}>Other</option>
                  </select>
                  @error('GENDER')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" name="DOB" class="form-control" value="{{ old('DOB', $faculty->DOB) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Date of Joining</label>
                  <input type="date" name="DOJ" class="form-control" value="{{ old('DOJ', $faculty->DOJ) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Reactivation Date</label>
                  <input type="date" name="reactivation_date" class="form-control @error('reactivation_date') is-invalid @enderror" value="{{ old('reactivation_date', $faculty->reactivation_date) }}">
                  @error('reactivation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">HR Remark</label>
                  <textarea name="hr_remark" class="form-control @error('hr_remark') is-invalid @enderror" rows="2" placeholder="Use for resignation/rejoin notes or status context">{{ old('hr_remark', $faculty->hr_remark) }}</textarea>
                  @error('hr_remark')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Nationality</label>
                  <select name="NATIONALITY" class="form-select dselect-example">
                    <option value="">Select Nationality</option>
                    @foreach($nationalities as $nationality)
                    <option value="{{ $nationality->id }}" {{ old('NATIONALITY', $faculty->NATIONALITY) == $nationality->id ? 'selected' : '' }}>{{ $nationality->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Campus <span class="text-danger">*</span></label>
                  <select name="CAMPUS_ID" class="form-select @error('CAMPUS_ID') is-invalid @enderror" required>
                    <option value="">Select Campus</option>
                    @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ old('CAMPUS_ID', $faculty->CAMPUS_ID) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- <div class="col-md-4 mb-3">
                  <label class="form-label">Photo</label>
                  <input type="file" name="photo" class="form-control" accept="image/*">
                  @if($faculty->photo)
                  <small class="text-muted">Current photo exists</small>
                  @endif
                </div> -->
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Contact Information</h6>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" name="MAIL_ID" class="form-control @error('MAIL_ID') is-invalid @enderror" value="{{ old('MAIL_ID', $faculty->MAIL_ID) }}" required>
                  @error('MAIL_ID')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                  <input type="text" name="MOBILE_NO" class="form-control @error('MOBILE_NO') is-invalid @enderror" value="{{ old('MOBILE_NO', $faculty->MOBILE_NO) }}" required>
                  @error('MOBILE_NO')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Current Address</label>
                  <textarea name="ADDRESS" class="form-control" rows="2">{{ old('ADDRESS', $faculty->ADDRESS) }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Permanent Address</label>
                  <textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address', $faculty->permanent_address) }}</textarea>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Professional Details</h6>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Employee Type</label>
                  <select name="employee_type" class="form-select">
                    <option value="">Select Type</option>
                    <option value="permanent" {{ old('employee_type', $faculty->employee_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contractual" {{ old('employee_type', $faculty->employee_type) == 'contractual' ? 'selected' : '' }}>Contractual</option>
                    <option value="probation" {{ old('employee_type', $faculty->employee_type) == 'probation' ? 'selected' : '' }}>Probation</option>
                    <option value="adhoc" {{ old('employee_type', $faculty->employee_type) == 'adhoc' ? 'selected' : '' }}>Adhoc</option>
                    <option value="guest" {{ old('employee_type', $faculty->employee_type) == 'guest' ? 'selected' : '' }}>Guest</option>
                    <option value="visiting" {{ old('employee_type', $faculty->employee_type) == 'visiting' ? 'selected' : '' }}>Visiting</option>
                    <option value="adjunct" {{ old('employee_type', $faculty->employee_type) == 'adjunct' ? 'selected' : '' }}>Adjunct</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Designation</label>
                  <input type="text" name="designation" class="form-control" value="{{ old('designation', $faculty->designation) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Experience (Years)</label>
                  <input type="number" name="experience_years" class="form-control" value="{{ old('experience_years', $faculty->experience_years) }}" min="0">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Qualification</label>
                  <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $faculty->qualification) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Specialization</label>
                  <input type="text" name="specialization" class="form-control" value="{{ old('specialization', $faculty->specialization) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Responsibility</label>
                  <input type="text" name="responsibility" class="form-control @error('responsibility') is-invalid @enderror" value="{{ old('responsibility', $faculty->responsibility) }}" placeholder="e.g., Event Coordinator, HR, Programmer Coordinator">
                  @error('responsibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Paper Publications Count</label>
                  <input type="number" name="paper_publications_count" class="form-control @error('paper_publications_count') is-invalid @enderror" value="{{ old('paper_publications_count', $faculty->paper_publications_count ?? 0) }}" min="0">
                  @error('paper_publications_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">ORCID ID</label>
                  <input type="text" name="orcid_id" class="form-control @error('orcid_id') is-invalid @enderror" value="{{ old('orcid_id', $faculty->orcid_id) }}" placeholder="e.g., 0000-0002-1234-5678">
                  @error('orcid_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Banking & ID Details</h6>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">PAN Number</label>
                  <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $faculty->pan_number) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Aadhar Number</label>
                  <input type="text" name="aadhar_number" class="form-control" value="{{ old('aadhar_number', $faculty->aadhar_number) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Bank Name</label>
                  <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $faculty->bank_name) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Bank Account Number</label>
                  <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $faculty->bank_account_number) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">IFSC Code</label>
                  <input type="text" name="bank_ifsc_code" class="form-control" value="{{ old('bank_ifsc_code', $faculty->bank_ifsc_code) }}">
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Emergency Contact</h6>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Emergency Contact Name</label>
                  <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $faculty->emergency_contact_name) }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Emergency Contact Number</label>
                  <input type="text" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $faculty->emergency_contact_number) }}">
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Faculty</button>
                  <a href="{{ route('hr.faculty.show', $faculty->id) }}" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
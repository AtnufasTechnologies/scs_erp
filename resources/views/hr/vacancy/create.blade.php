@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Vacancy Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.vacancy.index') }}">Vacancies</a></li>
            <li class="breadcrumb-item active">Create</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('hr.vacancy.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left me-1"></i>Back
        </a>
      </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i>Please fix the errors below.
      <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('hr.vacancy.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      {{-- Basic Information --}}
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Basic Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Vacancy Code <span class="text-danger">*</span></label>
              <input type="text" name="vacancy_code" class="form-control @error('vacancy_code') is-invalid @enderror"
                value="{{ old('vacancy_code') }}" maxlength="50" required>
              @error('vacancy_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Unique identifier for this vacancy</small>
            </div>
            <div class="col-md-8">
              <label class="form-label">Position Title <span class="text-danger">*</span></label>
              <input type="text" name="position_title" class="form-control @error('position_title') is-invalid @enderror"
                value="{{ old('position_title') }}" maxlength="255" required>
              @error('position_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Department</label>
              <select name="department_id" class="dselect-example form-select @error('department_id') is-invalid @enderror">
                <option value="">-- Select Department --</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                  {{ $dept->title }} | {{$dept->campusmaster->name ?? 'N/A' }}
                </option>
                @endforeach
              </select>
              @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Employment Type <span class="text-danger">*</span></label>
              <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                <option value="">-- Select Type --</option>
                <option value="full-time" {{ old('employment_type') == 'full-time' ? 'selected' : '' }}>Full-time</option>
                <option value="part-time" {{ old('employment_type') == 'part-time' ? 'selected' : '' }}>Part-time</option>
                <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                <option value="temporary" {{ old('employment_type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                <option value="visiting" {{ old('employment_type') == 'visiting' ? 'selected' : '' }}>Visiting</option>
              </select>
              @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Recruitment Type <span class="text-danger">*</span></label>
              <select name="recruitment_type" class="form-select @error('recruitment_type') is-invalid @enderror" required>
                <option value="">-- Select Type --</option>
                <option value="regular" {{ old('recruitment_type') == 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="adhoc" {{ old('recruitment_type') == 'adhoc' ? 'selected' : '' }}>Adhoc</option>
                <option value="contractual" {{ old('recruitment_type') == 'contractual' ? 'selected' : '' }}>Contractual</option>
                <option value="guest" {{ old('recruitment_type') == 'guest' ? 'selected' : '' }}>Guest</option>
                <option value="visiting" {{ old('recruitment_type') == 'visiting' ? 'selected' : '' }}>Visiting</option>
              </select>
              @error('recruitment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Number of Positions <span class="text-danger">*</span></label>
              <input type="number" name="number_of_positions" class="form-control @error('number_of_positions') is-invalid @enderror"
                value="{{ old('number_of_positions', 1) }}" min="1" required>
              @error('number_of_positions')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
              <label class="form-label">Salary Range</label>
              <input type="text" name="salary_range" class="form-control @error('salary_range') is-invalid @enderror"
                value="{{ old('salary_range') }}" maxlength="100" placeholder="e.g., ₹30,000 - ₹50,000 per month">
              @error('salary_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      {{-- Job Details --}}
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Job Details</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Job Description</label>
            <textarea name="job_description" class="form-control @error('job_description') is-invalid @enderror" rows="4">{{ old('job_description') }}</textarea>
            @error('job_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Qualifications Required</label>
            <textarea name="qualifications_required" class="form-control @error('qualifications_required') is-invalid @enderror" rows="3">{{ old('qualifications_required') }}</textarea>
            @error('qualifications_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Experience Required</label>
            <textarea name="experience_required" class="form-control @error('experience_required') is-invalid @enderror" rows="2">{{ old('experience_required') }}</textarea>
            @error('experience_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      {{-- Application Timeline --}}
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Application Timeline</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Application Start Date <span class="text-danger">*</span></label>
              <input type="date" name="application_start_date" class="form-control @error('application_start_date') is-invalid @enderror"
                value="{{ old('application_start_date') }}" required>
              @error('application_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Application End Date <span class="text-danger">*</span></label>
              <input type="date" name="application_end_date" class="form-control @error('application_end_date') is-invalid @enderror"
                value="{{ old('application_end_date') }}" required>
              @error('application_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Expected Joining Date</label>
              <input type="date" name="expected_joining_date" class="form-control @error('expected_joining_date') is-invalid @enderror"
                value="{{ old('expected_joining_date') }}">
              @error('expected_joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      {{-- Contact Information --}}
      <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Contact Person</label>
              <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                value="{{ old('contact_person') }}" maxlength="100">
              @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact Email</label>
              <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                value="{{ old('contact_email') }}" maxlength="100">
              @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Contact Phone</label>
              <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                value="{{ old('contact_phone') }}" maxlength="15">
              @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      {{-- Additional Details --}}
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>Additional Details</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Attachment (PDF only, max 5MB)</label>
              <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf">
              @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="1">{{ old('remarks') }}</textarea>
              @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save me-1"></i>Create Vacancy
        </button>
        <a href="{{ route('hr.vacancy.index') }}" class="btn btn-secondary ms-2">
          <i class="fas fa-times me-1"></i>Cancel
        </a>
      </div>

    </form>

  </main>
</div>
@include('includes.footer')
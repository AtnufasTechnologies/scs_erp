@include('includes.header')
@include('admin.sidebar')

<div class="container mt-4">
  <div class="d-flex align-items-center mb-3">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary me-3">
      <i class="bi bi-arrow-left"></i> Back
    </a>
    <h4 class="mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Edit Registration</h4>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
      <h6 class="mb-0">Registration ID: #{{ $registration->id }} — {{ $registration->first_name }} {{ $registration->last_name }}</h6>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.registration.update', $registration->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">

          {{-- Personal Details --}}
          <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold border-bottom pb-1">Personal Details</h6>
          </div>

          <div class="col-md-4">
            <label class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
              value="{{ old('first_name', $registration->first_name) }}" required>
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Last Name <span class="text-danger">*</span></label>
            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
              value="{{ old('last_name', $registration->last_name) }}" required>
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Batch</label>
            <input type="text" class="form-control" value="{{ $registration->batch }}" readonly>
          </div>

          {{-- Contact --}}
          <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold border-bottom pb-1 mt-2">Contact Details</h6>
          </div>

          <div class="col-md-5">
            <label class="form-label">Email ID <span class="text-danger">*</span></label>
            <input type="email" name="mail_id" class="form-control @error('mail_id') is-invalid @enderror"
              value="{{ old('mail_id', $registration->mail_id) }}" required>
            @error('mail_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
            <input type="text" name="mobile_no" class="form-control @error('mobile_no') is-invalid @enderror"
              value="{{ old('mobile_no', $registration->mobile_no) }}" maxlength="10" required>
            @error('mobile_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-3">
            <label class="form-label">Country <span class="text-danger">*</span></label>
            <select name="country" class="form-select @error('country') is-invalid @enderror" required>
              @foreach($countries as $country)
              <option value="{{ $country->id }}" {{ old('country', $registration->country) == $country->id ? 'selected' : '' }}>
                {{ $country->name }}
              </option>
              @endforeach
            </select>
            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Admission Details --}}
          <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold border-bottom pb-1 mt-2">Admission Details</h6>
          </div>

          <div class="col-md-4">
            <label class="form-label">Campus <span class="text-danger">*</span></label>
            <select name="campus_id" class="form-select @error('campus_id') is-invalid @enderror" required>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ old('campus_id', $registration->campus_id) == $campus->id ? 'selected' : '' }}>
                {{ $campus->name }}
              </option>
              @endforeach
            </select>
            @error('campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Application Type <span class="text-danger">*</span></label>
            <select name="application_type" class="form-select @error('application_type') is-invalid @enderror" required>
              @foreach(['UG', 'PG', 'UG and PG'] as $type)
              <option value="{{ $type }}" {{ old('application_type', $registration->application_type) == $type ? 'selected' : '' }}>
                {{ $type }}
              </option>
              @endforeach
            </select>
            @error('application_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          {{-- Status --}}
          <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold border-bottom pb-1 mt-2">Account Status</h6>
          </div>

          <div class="col-md-4">
            <label class="form-label">Account Status <span class="text-danger">*</span></label>
            <select name="account_status" class="form-select @error('account_status') is-invalid @enderror" required>
              <option value="1" {{ old('account_status', $registration->account_status) == 1 ? 'selected' : '' }}>Active</option>
              <option value="0" {{ old('account_status', $registration->account_status) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('account_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">OTP Verification</label>
            <select name="otp_verification" class="form-select">
              <option value="1" {{ old('otp_verification', $registration->otp_verification) == 1 ? 'selected' : '' }}>Verified</option>
              <option value="0" {{ old('otp_verification', $registration->otp_verification) == 0 ? 'selected' : '' }}>Not Verified</option>
            </select>
          </div>

          {{-- Application Info (read-only) --}}
          @if($registration->applicationmaster)
          <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold border-bottom pb-1 mt-2">Application Info</h6>
          </div>
          <div class="col-md-4">
            <label class="form-label">Application Code</label>
            <input type="text" class="form-control" value="{{ $registration->applicationmaster->application_code }}" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Payment Status</label>
            <input type="text" class="form-control" value="{{ ucfirst($registration->applicationmaster->payment_gateway_status ?? 'Not Paid') }}" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label">Edit Full Application</label><br>
            <a href="{{ route('admission.edit.application', $registration->applicationmaster->id) }}" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-pencil-square"></i> Edit Application Form
            </a>
          </div>
          @endif

          <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-save me-1"></i> Save Changes
            </button>
            <a href="javascript:history.back()" class="btn btn-outline-secondary ms-2">Cancel</a>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
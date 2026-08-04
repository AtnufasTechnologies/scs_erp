@include('includes.header')
@include('hr.sidebar')

<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
  <div class="breadcrumb-title pe-3">HR</div>
  <div class="ps-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item"><a href="{{ route('hr.pay-matrix.index') }}">Pay Matrix</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
      </ol>
    </nav>
  </div>
  <div class="ms-auto">
    <a href="{{ route('hr.pay-matrix.index') }}" class="btn btn-secondary btn-sm">
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

<div class="alert alert-info">
  Deductions are managed by Accounts during payroll processing. HR Pay Matrix includes earnings and assignment details only.
</div>

<form action="{{ route('hr.pay-matrix.store') }}" method="POST">
  @csrf

  {{-- Basic Details --}}
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-table me-2"></i>Basic Details</h5>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Matrix Name <span class="text-danger">*</span></label>
          <input type="text" name="matrix_name" class="form-control @error('matrix_name') is-invalid @enderror"
            value="{{ old('matrix_name') }}" maxlength="255" required>
          @error('matrix_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Pay Band</label>
          <input type="text" name="pay_band" class="form-control @error('pay_band') is-invalid @enderror"
            value="{{ old('pay_band') }}" maxlength="100" placeholder="e.g. 9300-34800">
          @error('pay_band')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Grade Pay</label>
          <input type="number" name="grade_pay" class="form-control @error('grade_pay') is-invalid @enderror"
            value="{{ old('grade_pay') }}" min="0">
          @error('grade_pay')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

      </div>
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Designation <span class="text-danger">*</span></label>
          <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
            <option value="">-- Select Designation --</option>
            @foreach($designations as $designation)
            <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
              {{ $designation->name }} ({{ $designation->code }})
            </option>
            @endforeach
          </select>
          @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="text-muted">Select from master or manually enter below</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Grade Level <span class="text-danger">*</span></label>
          <select name="grade_level_id" class="form-select @error('grade_level_id') is-invalid @enderror" required>
            <option value="">-- Select Grade Level --</option>
            @foreach($gradeLevels as $level)
            <option value="{{ $level->id }}" {{ old('grade_level_id') == $level->id ? 'selected' : '' }}>
              {{ $level->name }} ({{ $level->code }}) - ₹{{ number_format($level->min_salary, 0) }} to ₹{{ number_format($level->max_salary, 0) }}
            </option>
            @endforeach
          </select>
          @error('grade_level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="text-muted">Select from master or manually enter below</small>
        </div>
        <div class="col-md-4">
          <label class="form-label">Employment Type <span class="text-danger">*</span></label>
          <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
            <option value="">-- Select Type --</option>
            <option value="permanent" {{ old('employment_type') == 'permanent'    ? 'selected' : '' }}>Permanent</option>
            <option value="contractual" {{ old('employment_type') == 'contractual'  ? 'selected' : '' }}>Contractual</option>
            <option value="adhoc" {{ old('employment_type') == 'adhoc'        ? 'selected' : '' }}>Ad-hoc</option>
            <option value="guest" {{ old('employment_type') == 'guest'        ? 'selected' : '' }}>Guest</option>
            <option value="visiting" {{ old('employment_type') == 'visiting'     ? 'selected' : '' }}>Visiting</option>
            <option value="visiting" {{ old('employment_type') == 'adjunct'     ? 'selected' : '' }}>Adjunct</option>
          </select>
          @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Manual Designation Entry (Optional)</label>
          <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
            value="{{ old('designation') }}" maxlength="255" placeholder="Override with custom designation">
          @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Manual Grade Level Entry (Optional)</label>
          <input type="text" name="grade_level" class="form-control @error('grade_level') is-invalid @enderror"
            value="{{ old('grade_level') }}" maxlength="255" placeholder="Override with custom grade level">
          @error('grade_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="active" {{ old('status', 'active') == 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
          </select>
          @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Default Working Days <span class="text-danger">*</span></label>
          <input type="number" name="default_working_days" class="form-control @error('default_working_days') is-invalid @enderror"
            value="{{ old('default_working_days', 26) }}" min="1" max="31" required>
          @error('default_working_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Effective From</label>
          <input type="date" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror"
            value="{{ old('effective_from') }}">
          @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Effective To</label>
          <input type="date" name="effective_to" class="form-control @error('effective_to') is-invalid @enderror"
            value="{{ old('effective_to') }}">
          @error('effective_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Earnings --}}
  <div class="card mb-4">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Earnings / Allowances</h5>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror"
              value="{{ old('basic_salary', 0) }}" min="0" step="0.01" required>
            @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label">DA (%)</label>
          <input type="number" name="da_percentage" class="form-control @error('da_percentage') is-invalid @enderror"
            value="{{ old('da_percentage', 0) }}" min="0" max="100" step="0.01">
          @error('da_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">DA Fixed (₹)</label>
          <input type="number" name="da_fixed" class="form-control @error('da_fixed') is-invalid @enderror"
            value="{{ old('da_fixed', 0) }}" min="0" step="0.01">
          @error('da_fixed')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">HRA (%)</label>
          <input type="number" name="hra_percentage" class="form-control @error('hra_percentage') is-invalid @enderror"
            value="{{ old('hra_percentage', 0) }}" min="0" max="100" step="0.01">
          @error('hra_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">HRA Fixed (₹)</label>
          <input type="number" name="hra_fixed" class="form-control @error('hra_fixed') is-invalid @enderror"
            value="{{ old('hra_fixed', 0) }}" min="0" step="0.01">
          @error('hra_fixed')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Transport Allowance (₹)</label>
          <input type="number" name="ta" class="form-control @error('ta') is-invalid @enderror"
            value="{{ old('ta', 0) }}" min="0" step="0.01">
          @error('ta')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Medical Allowance (₹)</label>
          <input type="number" name="medical_allowance" class="form-control @error('medical_allowance') is-invalid @enderror"
            value="{{ old('medical_allowance', 0) }}" min="0" step="0.01">
          @error('medical_allowance')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-3">
          <label class="form-label">Special Allowance (₹)</label>
          <input type="number" name="special_allowance" class="form-control @error('special_allowance') is-invalid @enderror"
            value="{{ old('special_allowance', 0) }}" min="0" step="0.01">
          @error('special_allowance')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label">Other Allowances (₹)</label>
          <input type="number" name="other_allowances" class="form-control @error('other_allowances') is-invalid @enderror"
            value="{{ old('other_allowances', 0) }}" min="0" step="0.01">
          @error('other_allowances')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  {{-- Increment & Notes --}}
  <div class="card mb-4">
    <div class="card-header bg-info text-white">
      <h5 class="mb-0"><i class="fas fa-trending-up me-2"></i>Increment & Notes</h5>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Annual Increment (%)</label>
          <input type="number" name="annual_increment_percentage" class="form-control @error('annual_increment_percentage') is-invalid @enderror"
            value="{{ old('annual_increment_percentage', 0) }}" min="0" max="100" step="0.01">
          @error('annual_increment_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
          <label class="form-label">Increment Month</label>
          <select name="increment_month" class="form-select @error('increment_month') is-invalid @enderror">
            <option value="">-- Select Month --</option>
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" {{ old('increment_month') == $m ? 'selected' : '' }}>
              {{ DateTime::createFromFormat('!m', $m)->format('F') }}
              </option>
              @endfor
          </select>
          @error('increment_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Remarks</label>
          <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="2">{{ old('remarks') }}</textarea>
          @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="mb-4">
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-1"></i>Save Pay Matrix
    </button>
    <a href="{{ route('hr.pay-matrix.index') }}" class="btn btn-secondary ms-2">
      <i class="fas fa-times me-1"></i>Cancel
    </a>
  </div>

</form>

@include('includes.footer')
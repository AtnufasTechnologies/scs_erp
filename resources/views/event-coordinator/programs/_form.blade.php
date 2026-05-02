@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">
    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
  </ul>
</div>
@endif

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
      value="{{ old('name', $program->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold"> Type <span class="text-danger">*</span></label>
    <select name="program_type" class="form-select @error('program_type') is-invalid @enderror" required>
      <option value="intra-college" {{ old('program_type', $program->program_type ?? 'intra-college') === 'intra-college' ? 'selected' : '' }}>Intra-College</option>
      <option value="inter-college" {{ old('program_type', $program->program_type ?? '') === 'inter-college' ? 'selected' : '' }}>Inter-College</option>
    </select>
    @error('program_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label fw-semibold"> Scope <span class="text-danger">*</span></label>
    <select name="program_scope" class="form-select @error('program_scope') is-invalid @enderror" required>
      <option value="national" {{ old('program_scope', $program->program_scope ?? 'national') === 'national' ? 'selected' : '' }}>National</option>
      <option value="international" {{ old('program_scope', $program->program_scope ?? '') === 'international' ? 'selected' : '' }}>International</option>
    </select>
    @error('program_scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Description</label>
    <textarea name="description" rows="3"
      class="form-control @error('description') is-invalid @enderror">{{ old('description', $program->description ?? '') }}</textarea>
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Program Date <span class="text-danger">*</span></label>
    <input type="date" name="program_date" class="form-control @error('program_date') is-invalid @enderror"
      value="{{ old('program_date', isset($program) ? $program->program_date->format('Y-m-d') : '') }}" required>
    @error('program_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Start Time</label>
    <input type="time" name="start_time" class="form-control"
      value="{{ old('start_time', $program->start_time ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">End Time</label>
    <input type="time" name="end_time" class="form-control"
      value="{{ old('end_time', $program->end_time ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Venue</label>
    <input type="text" name="venue" class="form-control"
      value="{{ old('venue', $program->venue ?? '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Registration Fee (₹) <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="registration_fee" min="0"
      class="form-control @error('registration_fee') is-invalid @enderror"
      value="{{ old('registration_fee', $program->registration_fee ?? 0) }}" required>
    @error('registration_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Registration Start Date</label>
    <input type="date" name="registration_start_date" class="form-control"
      value="{{ old('registration_start_date', isset($program) && $program->registration_start_date ? $program->registration_start_date->format('Y-m-d') : '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Registration End Date</label>
    <input type="date" name="registration_end_date" class="form-control"
      value="{{ old('registration_end_date', isset($program) && $program->registration_end_date ? $program->registration_end_date->format('Y-m-d') : '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Max Participants (0 = unlimited)</label>
    <input type="number" min="0" name="max_participants" class="form-control"
      value="{{ old('max_participants', $program->max_participants ?? 0) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
      @foreach(['upcoming','ongoing','completed','cancelled'] as $s)
      <option value="{{ $s }}" {{ old('status', $program->status ?? 'upcoming') === $s ? 'selected' : '' }}>
        {{ ucfirst($s) }}
      </option>
      @endforeach
    </select>
  </div>
</div>
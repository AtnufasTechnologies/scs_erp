@include('includes.header')
<div class="wrapper">
  @include('hr.sidebar')
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FDP Programs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.index') }}">FDP List</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.fdp.show', $fdpProgram->id) }}">{{ $fdpProgram->program_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
          </ol>
        </nav>
      </div>
    </div>
    <div class="card">
      <div class="card-header bg-warning text-white">
        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit FDP Program</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('hr.fdp.update', $fdpProgram->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Basic Information --}}
          <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Basic Information</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Program Code <span class="text-danger">*</span></label>
              <input type="text" name="program_code" class="form-control @error('program_code') is-invalid @enderror"
                value="{{ old('program_code', $fdpProgram->program_code) }}" maxlength="50" required>
              @error('program_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Program Title <span class="text-danger">*</span></label>
              <input type="text" name="program_title" class="form-control @error('program_title') is-invalid @enderror"
                value="{{ old('program_title', $fdpProgram->program_title) }}" maxlength="255" required>
              @error('program_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row mb-3">
            @php $pt = old('program_type', $fdpProgram->program_type); @endphp
            <div class="col-md-4">
              <label class="form-label">Program Type <span class="text-danger">*</span></label>
              <select name="program_type" class="form-select @error('program_type') is-invalid @enderror" required>
                <option value="">-- Select Type --</option>
                <option value="workshop" {{ $pt == 'workshop'      ? 'selected' : '' }}>Workshop</option>
                <option value="seminar" {{ $pt == 'seminar'       ? 'selected' : '' }}>Seminar</option>
                <option value="conference" {{ $pt == 'conference'    ? 'selected' : '' }}>Conference</option>
                <option value="training" {{ $pt == 'training'      ? 'selected' : '' }}>Training</option>
                <option value="certification" {{ $pt == 'certification' ? 'selected' : '' }}>Certification</option>
                <option value="other" {{ $pt == 'other'         ? 'selected' : '' }}>Other</option>
              </select>
              @error('program_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @php $ta = old('target_audience', $fdpProgram->target_audience); @endphp
            <div class="col-md-4">
              <label class="form-label">Target Audience <span class="text-danger">*</span></label>
              <select name="target_audience" class="form-select @error('target_audience') is-invalid @enderror" required>
                <option value="">-- Select Audience --</option>
                <option value="faculty" {{ $ta == 'faculty' ? 'selected' : '' }}>Faculty</option>
                <option value="staff" {{ $ta == 'staff'   ? 'selected' : '' }}>Staff</option>
                <option value="both" {{ $ta == 'both'    ? 'selected' : '' }}>Both</option>
              </select>
              @error('target_audience')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @php $st = old('status', $fdpProgram->status); @endphp
            <div class="col-md-4">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="draft" {{ $st == 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="open" {{ $st == 'open'      ? 'selected' : '' }}>Open for Registration</option>
                <option value="ongoing" {{ $st == 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ $st == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $st == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $fdpProgram->description) }}</textarea>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Schedule & Venue --}}
          <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">Schedule & Venue</h6>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date"
                class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', $fdpProgram->start_date ? \Carbon\Carbon::parse($fdpProgram->start_date)->format('Y-m-d') : '') }}" required>
              @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date <span class="text-danger">*</span></label>
              <input type="date" name="end_date"
                class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', $fdpProgram->end_date ? \Carbon\Carbon::parse($fdpProgram->end_date)->format('Y-m-d') : '') }}" required>
              @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Venue</label>
              <input type="text" name="venue" class="form-control @error('venue') is-invalid @enderror"
                value="{{ old('venue', $fdpProgram->venue) }}" maxlength="255">
              @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Organizer</label>
              <input type="text" name="organizer" class="form-control @error('organizer') is-invalid @enderror"
                value="{{ old('organizer', $fdpProgram->organizer) }}" maxlength="255">
              @error('organizer')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Program Fee (₹)</label>
              <input type="number" name="program_fee" class="form-control @error('program_fee') is-invalid @enderror"
                value="{{ old('program_fee', $fdpProgram->program_fee) }}" min="0" step="0.01">
              @error('program_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Max Participants</label>
              <input type="number" name="max_participants" class="form-control @error('max_participants') is-invalid @enderror"
                value="{{ old('max_participants', $fdpProgram->max_participants) }}" min="1">
              @error('max_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Coordinator Details --}}
          <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">Coordinator Details</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Coordinator Name</label>
              <input type="text" name="coordinator_name" class="form-control @error('coordinator_name') is-invalid @enderror"
                value="{{ old('coordinator_name', $fdpProgram->coordinator_name) }}" maxlength="100">
              @error('coordinator_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Coordinator Contact</label>
              <input type="text" name="coordinator_contact" class="form-control @error('coordinator_contact') is-invalid @enderror"
                value="{{ old('coordinator_contact', $fdpProgram->coordinator_contact) }}" maxlength="50">
              @error('coordinator_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Additional Details --}}
          <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">Additional Details</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Attachment <small class="text-muted">(PDF / JPG / PNG, max 5MB)</small></label>
              @if($fdpProgram->attachment)
              <div class="mb-2">
                <a href="{{ $fdpProgram->attachment }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="fas fa-paperclip me-1"></i>Current Attachment
                </a>
                <small class="text-muted ms-2">Upload a new file to replace it.</small>
              </div>
              @endif
              <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                accept=".pdf,.jpg,.jpeg,.png">
              @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $fdpProgram->remarks) }}</textarea>
              @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <button type="submit" class="btn btn-warning text-white"><i class="fas fa-save me-1"></i>Update FDP</button>
              <a href="{{ route('hr.fdp.show', $fdpProgram->id) }}" class="btn btn-secondary ms-2"><i class="fas fa-times me-1"></i>Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
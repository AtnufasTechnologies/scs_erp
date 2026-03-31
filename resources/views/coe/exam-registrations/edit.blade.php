@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')


  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Edit Exam Registration</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-registrations.index') }}" class="text-white">Exam Registrations</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">Edit</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa fa-edit"></i> Edit Registration - {{ $registration->registration_number }}</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('admin.exam-registrations.update', $registration->id) }}" method="POST">
              @csrf
              @method('PUT')

              <div class="row">
                <!-- Student Info (Read Only) -->
                <div class="col-md-12 mb-3">
                  <div class="alert alert-info">
                    <strong>Student:</strong>
                    {{ $registration->student->first_name ?? '' }} {{ $registration->student->last_name ?? '' }}
                    ({{ $registration->student->register_no ?? 'N/A' }})
                  </div>
                </div>

                <!-- Exam Selection -->
                <div class="col-md-6 mb-3">
                  <label for="exam_id" class="form-label">Select Exam <span class="text-danger">*</span></label>
                  <select name="exam_id" id="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
                    <option value="">-- Select Exam --</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}"
                      {{ (old('exam_id', $registration->exam_id) == $exam->id) ? 'selected' : '' }}>
                      {{ $exam->name }} ({{ $exam->exam_type }}) - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                  </select>
                  @error('exam_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Semester Selection -->
                <div class="col-md-6 mb-3">
                  <label for="semester_id" class="form-label">Semester</label>
                  <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                    <option value="">-- Select Semester --</option>
                    @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}"
                      {{ (old('semester_id', $registration->semester_id) == $semester->id) ? 'selected' : '' }}>
                      {{ $semester->title }}
                    </option>
                    @endforeach
                  </select>
                  @error('semester_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Registration Fee -->
                <div class="col-md-6 mb-3">
                  <label for="registration_fee" class="form-label">Registration Fee (₹)</label>
                  <input type="number" step="0.01" name="registration_fee" id="registration_fee"
                    class="form-control @error('registration_fee') is-invalid @enderror"
                    value="{{ old('registration_fee', $registration->registration_fee) }}" min="0">
                  @error('registration_fee')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Payment Reference -->
                <div class="col-md-6 mb-3">
                  <label for="payment_reference" class="form-label">Payment Reference</label>
                  <input type="text" name="payment_reference" id="payment_reference"
                    class="form-control @error('payment_reference') is-invalid @enderror"
                    value="{{ old('payment_reference', $registration->payment_reference) }}"
                    placeholder="Enter payment reference number">
                  @error('payment_reference')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                  <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status', $registration->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status', $registration->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ old('status', $registration->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ old('status', $registration->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                  </select>
                  @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Registration Type Checkboxes -->
                <div class="col-md-6 mb-3">
                  <label class="form-label">Registration Type</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_regular" id="is_regular"
                      value="1" {{ old('is_regular', $registration->is_regular) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_regular">
                      Regular Student
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_backlog" id="is_backlog"
                      value="1" {{ old('is_backlog', $registration->is_backlog) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_backlog">
                      Backlog Exam
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="fee_paid" id="fee_paid"
                      value="1" {{ old('fee_paid', $registration->fee_paid) ? 'checked' : '' }}>
                    <label class="form-check-label" for="fee_paid">
                      Fee Paid
                    </label>
                  </div>
                </div>

                <!-- Remarks -->
                <div class="col-md-12 mb-3">
                  <label for="remarks" class="form-label">Remarks</label>
                  <textarea name="remarks" id="remarks" rows="3"
                    class="form-control @error('remarks') is-invalid @enderror"
                    placeholder="Enter any additional notes...">{{ old('remarks', $registration->remarks) }}</textarea>
                  @error('remarks')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Approval Info (if approved) -->
                @if($registration->approved_at)
                <div class="col-md-12 mb-3">
                  <div class="alert alert-success">
                    <strong>Approved on:</strong> {{ \Carbon\Carbon::parse($registration->approved_at)->format('d M Y, h:i A') }}
                  </div>
                </div>
                @endif
              </div>

              <!-- Form Actions -->
              <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.exam-registrations.index') }}" class="btn btn-secondary">
                  <i class="fa fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                  <i class="fa fa-save"></i> Update Registration
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')
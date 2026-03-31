@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')


  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Create Exam Registration</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.exam-registrations.index') }}" class="text-white">Exam Registrations</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">Create</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa fa-plus-circle"></i> New Exam Registration</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('admin.exam-registrations.store') }}" method="POST">
              @csrf

              <div class="row">
                <!-- Exam Selection -->
                <div class="col-md-6 mb-3">
                  <label for="exam_id" class="form-label">Select Exam <span class="text-danger">*</span></label>
                  <select name="exam_id" id="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
                    <option value="">-- Select Exam --</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                      {{ $exam->name }} ({{ $exam->exam_type }}) - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                  </select>
                  @error('exam_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Student Selection -->
                <div class="col-md-6 mb-3">
                  <label for="exam_student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                  <select name="exam_student_id" id="exam_student_id" class="form-select @error('exam_student_id') is-invalid @enderror" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('exam_student_id') == $student->id ? 'selected' : '' }}>
                      {{ $student->first_name }} {{ $student->last_name }} ({{ $student->register_no }})
                    </option>
                    @endforeach
                  </select>
                  @error('exam_student_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Semester Selection -->
                <div class="col-md-6 mb-3">
                  <label for="semester_id" class="form-label">Semester</label>
                  <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                    <option value="">-- Select Semester --</option>
                    @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
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
                    value="{{ old('registration_fee', 0) }}" min="0">
                  @error('registration_fee')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                  <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                      value="1" {{ old('is_regular', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_regular">
                      Regular Student
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_backlog" id="is_backlog"
                      value="1" {{ old('is_backlog') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_backlog">
                      Backlog Exam
                    </label>
                  </div>
                </div>

                <!-- Remarks -->
                <div class="col-md-12 mb-3">
                  <label for="remarks" class="form-label">Remarks</label>
                  <textarea name="remarks" id="remarks" rows="3"
                    class="form-control @error('remarks') is-invalid @enderror"
                    placeholder="Enter any additional notes...">{{ old('remarks') }}</textarea>
                  @error('remarks')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Form Actions -->
              <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.exam-registrations.index') }}" class="btn btn-secondary">
                  <i class="fa fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                  <i class="fa fa-save"></i> Create Registration
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

<script>
  // Initialize select2 or similar for better UX (if available)
  document.addEventListener('DOMContentLoaded', function() {
    // Add any JavaScript enhancements here
  });
</script>
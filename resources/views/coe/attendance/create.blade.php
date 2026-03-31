@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Exam Attendance</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mark Attendance</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid">
      <!-- Header Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-header gradient-coe text-white py-3">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h5 class="mb-0 fw-bold"><i class="fas fa-user-check me-2"></i>Mark Exam Attendance</h5>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="{{ route('admin.exam-attendance.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to List
              </a>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <!-- Attendance Form -->
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <form action="{{ route('admin.exam-attendance.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                  <label for="exam_id" class="form-label fw-bold">
                    <i class="fas fa-clipboard-list me-2"></i>Select Exam <span class="text-danger">*</span>
                  </label>
                  <select name="exam_id" id="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
                    <option value="">-- Select Exam --</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                      {{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                  </select>
                  @error('exam_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="exam_student_id" class="form-label fw-bold">
                    <i class="fas fa-user me-2"></i>Select Student <span class="text-danger">*</span>
                  </label>
                  <select name="exam_student_id" id="exam_student_id" class="dselect-example form-select @error('exam_student_id') is-invalid @enderror" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('exam_student_id') == $student->id ? 'selected' : '' }}>
                      {{ $student->roll_no ?? 'N/A' }} - {{ $student->full_name }} ({{ $student->registration_no ?? 'N/A' }})
                    </option>
                    @endforeach
                  </select>
                  @error('exam_student_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="date" class="form-label fw-bold">
                    <i class="fas fa-calendar me-2"></i>Date <span class="text-danger">*</span>
                  </label>
                  <input type="date" name="date" id="date"
                    class="form-control @error('date') is-invalid @enderror"
                    value="{{ old('date', date('Y-m-d')) }}"
                    max="{{ date('Y-m-d') }}"
                    required>
                  @error('date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="status" class="form-label fw-bold">
                    <i class="fas fa-check-circle me-2"></i>Status <span class="text-danger">*</span>
                  </label>
                  <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="">-- Select Status --</option>
                    <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>
                      <i class="fas fa-check"></i> Present
                    </option>
                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>
                      <i class="fas fa-times"></i> Absent
                    </option>
                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>
                      <i class="fas fa-clock"></i> Late
                    </option>
                  </select>
                  @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="remarks" class="form-label fw-bold">
                    <i class="fas fa-comment me-2"></i>Remarks (Optional)
                  </label>
                  <textarea name="remarks" id="remarks" rows="3"
                    class="form-control @error('remarks') is-invalid @enderror"
                    placeholder="Add any additional notes...">{{ old('remarks') }}</textarea>
                  @error('remarks')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="alert alert-primary border-0">
                  <div class="d-flex align-items-start">
                    <i class="fas fa-lightbulb me-3 mt-1 fs-4"></i>
                    <div>
                      <h6 class="mb-2"><strong>💡 Bulk Marking Available!</strong></h6>
                      <p class="mb-2">This form is for marking <strong>individual student attendance</strong> records.</p>
                      <p class="mb-0">For marking <strong>multiple students at once</strong>, use the <a href="{{ route('coe.attendance.index') }}" class="alert-link fw-bold">Room-wise Attendance</a> feature - it's faster and more efficient!</p>
                    </div>
                  </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('admin.exam-attendance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                  </a>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Attendance
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

<script>
  // Initialize Select2 for better dropdowns (if available)
  $(document).ready(function() {
    if (typeof $.fn.select2 !== 'undefined') {
      $('#exam_student_id').select2({
        placeholder: '-- Search Student --',
        allowClear: true,
        width: '100%'
      });
    }
  });
</script>

@include('includes.footer')
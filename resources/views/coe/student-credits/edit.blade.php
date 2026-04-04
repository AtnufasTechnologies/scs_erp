@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Academic Credits</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.student-credits.index') }}">Student Credits</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Credit #{{ $credit->id }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="text-dark fw-bold mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Credit Entry
                    <span class="badge bg-{{ $credit->credit_type === 'earned' ? 'success' : 'info' }} fs-6 ms-2">
                      {{ ucfirst($credit->credit_type) }}
                    </span>
                  </h3>
                  <p class="text-muted mb-0">
                    Student: <strong>{{ $credit->student->enrollment_no ?? 'N/A' }}</strong>
                    | Credit #{{ $credit->id }}
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <a href="{{ route('admin.student-credits.show', $credit->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-1"></i>View
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="row">
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold">
                <i class="fas fa-{{ $credit->isTransferred() ? 'exchange-alt text-info' : 'graduation-cap text-success' }} me-2"></i>
                Update Credit Details
              </h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('admin.student-credits.update', $credit->id) }}">
                @csrf
                @method('PUT')

                <!-- Student (read-only) -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Student</label>
                  <input type="text" class="form-control" value="{{ $credit->student->enrollment_no ?? 'N/A' }}" disabled>
                  <small class="text-muted">Student cannot be changed after creation.</small>
                </div>

                <!-- Semester -->
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                      @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ old('semester', $credit->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                  </div>
                </div>

                @if($credit->isEarned())
                <!-- Earned: Subject -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                  <select name="exam_subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ old('exam_subject_id', $credit->exam_subject_id) == $sub->id ? 'selected' : '' }}>
                      {{ $sub->subject_code }} — {{ $sub->name }} ({{ $sub->credits }} cr)
                    </option>
                    @endforeach
                  </select>
                </div>
                @else
                <!-- Transferred: Source Details -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Source Institution <span class="text-danger">*</span></label>
                  <input type="text" name="source_institution" class="form-control" value="{{ old('source_institution', $credit->source_institution) }}" required>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Source Subject Code</label>
                    <input type="text" name="source_subject_code" class="form-control" value="{{ old('source_subject_code', $credit->source_subject_code) }}">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Source Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="source_subject_name" class="form-control" value="{{ old('source_subject_name', $credit->source_subject_name) }}" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Transfer Date <span class="text-danger">*</span></label>
                    <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', $credit->transfer_date ? $credit->transfer_date->format('Y-m-d') : '') }}" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Reference / Document No</label>
                    <input type="text" name="transfer_reference" class="form-control" value="{{ old('transfer_reference', $credit->transfer_reference) }}">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Map to Local Subject <small class="text-muted">(optional)</small></label>
                  <select name="exam_subject_id" class="form-select">
                    <option value="">— Not Mapped —</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ old('exam_subject_id', $credit->exam_subject_id) == $sub->id ? 'selected' : '' }}>
                      {{ $sub->subject_code }} — {{ $sub->name }} ({{ $sub->credits }} cr)
                    </option>
                    @endforeach
                  </select>
                </div>
                @endif

                <!-- Credits & Grade -->
                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Credits Earned <span class="text-danger">*</span></label>
                    <input type="number" name="credits_earned" class="form-control" value="{{ old('credits_earned', $credit->credits_earned) }}" step="0.5" min="0.5" max="30" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Grade</label>
                    <input type="text" name="grade" class="form-control" value="{{ old('grade', $credit->grade) }}" maxlength="5">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Grade Point</label>
                    <input type="number" name="grade_point" class="form-control" value="{{ old('grade_point', $credit->grade_point) }}" step="0.01" min="0" max="10">
                  </div>
                </div>

                <!-- Remarks -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="2" maxlength="500">{{ old('remarks', $credit->remarks) }}</textarea>
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update Credit
                  </button>
                  <a href="{{ route('admin.student-credits.show', $credit->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Cancel
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Info Panel -->
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle text-info me-2"></i>Information</h5>
            </div>
            <div class="card-body">
              <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Credit type</strong> and <strong>student</strong> cannot be changed after creation.
              </div>
              <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Credit records <strong>cannot be deleted</strong>. You may only edit the details.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
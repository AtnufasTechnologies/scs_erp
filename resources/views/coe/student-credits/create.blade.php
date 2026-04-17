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
            <li class="breadcrumb-item active" aria-current="page">Add {{ ucfirst($creditType) }} Credits</li>
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
                    @if($creditType === 'transferred')
                    <i class="fas fa-exchange-alt me-2"></i>Add Transferred Credits
                    @else
                    <i class="fas fa-graduation-cap me-2"></i>Add Earned Credits
                    @endif
                  </h3>
                  <p class="text-muted mb-0">
                    @if($creditType === 'transferred')
                    Record credits transferred from another institution
                    @else
                    Record credits earned through examinations
                    @endif
                  </p>
                </div>
                <div class="col-md-6 text-md-end">
                  <div class="btn-group">
                    <a href="{{ route('admin.student-credits.create') }}?type=earned"
                      class="btn {{ $creditType === 'earned' ? 'btn-success' : 'btn-outline-success' }}">
                      <i class="fas fa-graduation-cap me-1"></i>Earned
                    </a>
                    <a href="{{ route('admin.student-credits.create') }}?type=transferred"
                      class="btn {{ $creditType === 'transferred' ? 'btn-info text-white' : 'btn-outline-info' }}">
                      <i class="fas fa-exchange-alt me-1"></i>Transferred
                    </a>
                  </div>
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
        <!-- Credit Form -->
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold">
                <i class="fas fa-{{ $creditType === 'transferred' ? 'exchange-alt text-info' : 'graduation-cap text-success' }} me-2"></i>
                Credit Details
              </h5>
            </div>
            <div class="card-body">
              <form method="POST" action="{{ route('admin.student-credits.store') }}">
                @csrf
                <input type="hidden" name="credit_type" value="{{ $creditType }}">

                <!-- Student Selection -->
                <div class="row mb-3">
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                    <select name="exam_student_id" class="form-select" required>
                      <option value="">Select Student</option>
                      @foreach($students as $s)
                      <option value="{{ $s->id }}" {{ old('exam_student_id', request('student_id')) == $s->id ? 'selected' : '' }}>
                        {{ $s->enrollment_no }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                      <option value="">Select</option>
                      @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                  </div>
                </div>

                @if($creditType === 'earned')
                <!-- Earned: Subject Selection -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                  <select name="exam_subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ old('exam_subject_id') == $sub->id ? 'selected' : '' }}>
                      {{ $sub->subject_code }} — {{ $sub->name }} ({{ $sub->credits }} cr)
                    </option>
                    @endforeach
                  </select>
                </div>
                @else
                <!-- Transferred: Source Institution Details -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Source Institution <span class="text-danger">*</span></label>
                  <input type="text" name="source_institution" class="form-control" value="{{ old('source_institution') }}" placeholder="University / Institution name" required>
                </div>

                <div class="row mb-3">
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Source Subject Code</label>
                    <input type="text" name="source_subject_code" class="form-control" value="{{ old('source_subject_code') }}" placeholder="e.g. CS101">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label fw-semibold">Source Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="source_subject_name" class="form-control" value="{{ old('source_subject_name') }}" placeholder="Subject name from source institution" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Transfer Date <span class="text-danger">*</span></label>
                    <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date') }}" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Reference / Document No</label>
                    <input type="text" name="transfer_reference" class="form-control" value="{{ old('transfer_reference') }}" placeholder="Transfer order / Reference no">
                  </div>
                </div>

                <!-- Optional: Map to local subject -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Map to Local Subject <small class="text-muted">(optional)</small></label>
                  <select name="exam_subject_id" class="form-select">
                    <option value="">— Not Mapped —</option>
                    @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ old('exam_subject_id') == $sub->id ? 'selected' : '' }}>
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
                    <input type="number" name="credits_earned" class="form-control" value="{{ old('credits_earned') }}" step="0.5" min="0.5" max="30" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Grade</label>
                    <input type="text" name="grade" class="form-control" value="{{ old('grade') }}" placeholder="e.g. A+, B, O" maxlength="5">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label fw-semibold">Grade Point</label>
                    <input type="number" name="grade_point" class="form-control" value="{{ old('grade_point') }}" step="0.01" min="0" max="10" placeholder="0.00 - 10.00">
                  </div>
                </div>

                <!-- Remarks -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="2" maxlength="500" placeholder="Optional notes...">{{ old('remarks') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Credit Entry
                  </button>
                  <a href="{{ route('admin.student-credits.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Student Credit Summary (if student selected) -->
        <div class="col-md-4">
          @if($studentCredits && $studentCredits->count() > 0)
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-chart-pie text-primary me-2"></i>Credit Summary</h5>
            </div>
            <div class="card-body p-0">
              @php
              $earned = $studentCredits->where('credit_type', 'earned');
              $transferred = $studentCredits->where('credit_type', 'transferred')->whereIn('status', ['active', 'verified']);
              @endphp
              <div class="p-3">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Earned Credits</span>
                  <span class="fw-bold text-success">{{ number_format($earned->sum('credits_earned'), 1) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Transferred Credits</span>
                  <span class="fw-bold text-info">{{ number_format($transferred->sum('credits_earned'), 1) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                  <span class="fw-bold">Total Credits</span>
                  <span class="fw-bold fs-5">{{ number_format($earned->sum('credits_earned') + $transferred->sum('credits_earned'), 1) }}</span>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-3">Sem</th>
                      <th>Subject</th>
                      <th class="text-center">Cr</th>
                      <th>Type</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($studentCredits as $sc)
                    <tr>
                      <td class="ps-3">{{ $sc->semester ?? '—' }}</td>
                      <td>
                        @if($sc->subject)
                        <small>{{ $sc->subject->subject_code }}</small>
                        @elseif($sc->source_subject_code)
                        <small class="text-info">{{ $sc->source_subject_code }}</small>
                        @else
                        <small class="text-muted">—</small>
                        @endif
                      </td>
                      <td class="text-center">{{ $sc->credits_earned }}</td>
                      <td>
                        @if($sc->credit_type === 'earned')
                        <span class="badge bg-success badge-sm">E</span>
                        @else
                        <span class="badge bg-info badge-sm">T</span>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          @else
          <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
              <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
              <p class="text-muted">Select a student and submit to view their credit summary, or add a <code>?student_id=X</code> parameter.</p>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
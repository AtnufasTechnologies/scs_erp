@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Score Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-scores.index') }}">API Scores</a></li>
            <li class="breadcrumb-item active">Create</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create API Score Entry</h5>
          </div>
          <div class="card-body">
            <form action="{{ route('hr.api-scores.store') }}" method="POST">
              @csrf

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Basic Information</h6>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Faculty <span class="text-danger">*</span></label>
                  <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror dselect-example" required>
                    <option value="">Select Faculty</option>
                    @foreach($faculties as $faculty)
                    <option value="{{ $faculty->id }}" {{ old('faculty_id', $selectedFaculty) == $faculty->id ? 'selected' : '' }}>
                      {{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
                    </option>
                    @endforeach
                  </select>
                  @error('faculty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                  <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $selectedYear) == $year->id ? 'selected' : '' }}>
                      {{ $year->year_name }}
                    </option>
                    @endforeach
                  </select>
                  @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="border-bottom pb-2">Category Scores (As per Salesian API System)</h6>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category I: Teaching Output (Max: 10)</label>
                  <input type="number" name="category_i_score" class="form-control @error('category_i_score') is-invalid @enderror"
                    value="{{ old('category_i_score', 0) }}" min="0" max="10" step="0.01">
                  <small class="text-muted">Student feedback + Semester results</small>
                  @error('category_i_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category II: Teaching, Learning & Evaluation (Max: 25)</label>
                  <input type="number" name="category_ii_score" class="form-control @error('category_ii_score') is-invalid @enderror"
                    value="{{ old('category_ii_score', 0) }}" min="0" max="25" step="0.01">
                  <small class="text-muted">Direct teaching, Exam duties, Punctuality, Support hours</small>
                  @error('category_ii_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category III: Cocurricular & Extension Activities (Max: 10)</label>
                  <input type="number" name="category_iii_score" class="form-control @error('category_iii_score') is-invalid @enderror"
                    value="{{ old('category_iii_score', 0) }}" min="0" max="10" step="0.01">
                  <small class="text-muted">Animator, Activities conducted</small>
                  @error('category_iii_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category IV: Managerial Contributions (Max: 25)</label>
                  <input type="number" name="category_iv_score" class="form-control @error('category_iv_score') is-invalid @enderror"
                    value="{{ old('category_iv_score', 0) }}" min="0" max="25" step="0.01">
                  <small class="text-muted">Dean, HoD, Coordinator, Committee members, Seminars</small>
                  @error('category_iv_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category V: Professional Development (Max: 15)</label>
                  <input type="number" name="category_v_score" class="form-control @error('category_v_score') is-invalid @enderror"
                    value="{{ old('category_v_score', 0) }}" min="0" max="15" step="0.01">
                  <small class="text-muted">FDP attended, MOOCS, Refresher courses</small>
                  @error('category_v_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category VI: Academic Activities (Max: 10)</label>
                  <input type="number" name="category_vi_score" class="form-control @error('category_vi_score') is-invalid @enderror"
                    value="{{ old('category_vi_score', 0) }}" min="0" max="10" step="0.01">
                  <small class="text-muted">Publications, Books, Research, Patents, Lectures</small>
                  @error('category_vi_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Category VII: Documentation (Max: 5)</label>
                  <input type="number" name="category_vii_score" class="form-control @error('category_vii_score') is-invalid @enderror"
                    value="{{ old('category_vii_score', 0) }}" min="0" max="5" step="0.01">
                  <small class="text-muted">Timely submission of records and reports</small>
                  @error('category_vii_score')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-12">
                  <label class="form-label">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create API Score</button>
                  <a href="{{ route('hr.api-scores.index') }}" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
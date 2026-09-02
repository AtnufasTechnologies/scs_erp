@include('includes.header')

<div class="wrapper">
  @include('admin.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">ITCELL</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Direct Student Entry</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger mt-3" role="alert">
      <strong class="d-block mb-1">Please fix the following:</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="card shadow-sm border-0 mt-3">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
          <h5 class="mb-0 fw-bold">Direct Student Entry (Student Master)</h5>
          <small class="text-muted">Create a student directly in student_masters and mark current semester</small>
        </div>

        <form method="POST" action="{{ route('itcell.direct-student-entry.store') }}" class="row g-3" id="directStudentEntryForm">
          @csrf

          <div class="col-12">
            <h6 class="mb-1">Identity and Basic Info</h6>
          </div>

          <div class="col-md-2">
            <label class="form-label">Application Code</label>
            <input type="number" min="1" name="user_code" class="form-control" value="{{ old('user_code') }}" placeholder="Optional">
          </div>
          <div class="col-md-3">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-select" required>
              <option value="">Select</option>
              <option value="1" {{ old('gender') === '1' ? 'selected' : '' }}>Male</option>
              <option value="2" {{ old('gender') === '2' ? 'selected' : '' }}>Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">DOB</label>
            <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Roll No *</label>
            <input type="text" name="roll_no" class="form-control" required value="{{ old('roll_no') }}" placeholder="Unique roll no">
          </div>
          <div class="col-md-3">
            <label class="form-label">Register No *</label>
            <input type="text" name="register_no" class="form-control" required value="{{ old('register_no') }}" placeholder="Unique register no">
          </div>
          <div class="col-md-3">
            <label class="form-label">Library Code</label>
            <input type="text" name="library_code" class="form-control" value="{{ old('library_code') }}" placeholder="Optional, unique">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile_no" class="form-control" value="{{ old('mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="mail_id" class="form-control" value="{{ old('mail_id') }}">
          </div>

          <div class="col-12 mt-2">
            <h6 class="mb-1">Academic Mapping</h6>
          </div>

          <div class="col-md-3">
            <label class="form-label">Campus *</label>
            <select name="campus_id" id="campusSelect" class="form-select" required>
              <option value="">Select campus</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Department *</label>
            <select name="department" id="departmentSelect" class="form-select" required>
              <option value="">Select department</option>
              @foreach($departments as $department)
              <option
                value="{{ $department->id }}"
                data-campus="{{ $department->campus_id }}"
                {{ old('department') == $department->id ? 'selected' : '' }}>
                {{ $department->title }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Program *</label>
            <select name="new_program_id" id="programSelect" class="form-select" required>
              <option value="">Select program</option>
              @foreach($programs as $program)
              <option
                value="{{ $program->id }}"
                data-campus="{{ $program->campus_id }}"
                {{ old('new_program_id') == $program->id ? 'selected' : '' }}>
                {{ trim(($program->code ?? '') . ' - ' . ($program->name ?? '')) }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Batch *</label>
            <select name="batch" class="form-select" required>
              <option value="">Select batch</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ old('batch') == $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Admission Date *</label>
            <input type="date" name="admission_date" class="form-control" required value="{{ old('admission_date', date('Y-m-d')) }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Current Year *</label>
            <input type="number" min="1" max="6" name="current_year" class="form-control" required value="{{ old('current_year', 1) }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Current Semester *</label>
            <select name="semester" class="form-select" required>
              <option value="">Select</option>
              @foreach($semesters as $semester)
              <option value="{{ $semester->id }}" {{ old('semester') == $semester->id ? 'selected' : '' }}>{{ $semester->title }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Address line">
          </div>

          <div class="col-12 mt-2">
            <h6 class="mb-1">Family and Other Details</h6>
          </div>

          <div class="col-md-3">
            <label class="form-label">Father Name</label>
            <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Name</label>
            <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Guardian Name</label>
            <input type="text" name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Annual Income</label>
            <input type="number" min="0" step="0.01" name="annual_income" class="form-control" value="{{ old('annual_income') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Father Mobile</label>
            <input type="text" name="fr_mobile_no" class="form-control" value="{{ old('fr_mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Mobile</label>
            <input type="text" name="mr_mobile_no" class="form-control" value="{{ old('mr_mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Guardian Mobile</label>
            <input type="text" name="guardian_mobile_no" class="form-control" value="{{ old('guardian_mobile_no') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Tongue</label>
            <input type="text" name="mother_tongue" class="form-control" value="{{ old('mother_tongue') }}">
          </div>

          <div class="col-md-3">
            <label class="form-label">Father Occupation</label>
            <input type="text" name="fr_occupation" class="form-control" value="{{ old('fr_occupation') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Mother Occupation</label>
            <input type="text" name="mr_occupation" class="form-control" value="{{ old('mr_occupation') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">Blood Group</label>
            <select name="blood_group_id" class="form-select">
              <option value="">Select</option>
              @foreach($bloodGroups as $bloodGroup)
              <option value="{{ $bloodGroup->id }}" {{ old('blood_group_id') == $bloodGroup->id ? 'selected' : '' }}>{{ $bloodGroup->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Religion</label>
            <select name="religion" class="form-select">
              <option value="">Select</option>
              @foreach($religions as $religion)
              <option value="{{ $religion->id }}" {{ old('religion') == $religion->id ? 'selected' : '' }}>{{ $religion->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Nationality</label>
            <select name="nationality" class="form-select">
              <option value="">Select</option>
              @foreach($nationalities as $nationality)
              <option value="{{ $nationality->id }}" {{ old('nationality') == $nationality->id ? 'selected' : '' }}>{{ $nationality->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Caste</label>
            <input type="text" name="caste" class="form-control" value="{{ old('caste') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Aadhaar No</label>
            <input type="text" name="aadhar_no" class="form-control" value="{{ old('aadhar_no') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="1" placeholder="Optional notes">{{ old('remarks') }}</textarea>
          </div>

          <div class="col-12 pt-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-1"></i>Create Student Record
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')
<script>
  (function() {
    const campusSelect = document.getElementById('campusSelect');
    const programSelect = document.getElementById('programSelect');
    const departmentSelect = document.getElementById('departmentSelect');

    function filterByCampus(selectElement, campusId) {
      const options = Array.from(selectElement.options);
      options.forEach((option, index) => {
        if (index === 0) {
          option.hidden = false;
          return;
        }

        const optionCampus = option.getAttribute('data-campus');
        const shouldShow = !campusId || !optionCampus || optionCampus === campusId;
        option.hidden = !shouldShow;

        if (!shouldShow && option.selected) {
          option.selected = false;
        }
      });
    }

    function syncCampusFilters() {
      const campusId = campusSelect ? campusSelect.value : '';
      if (programSelect) {
        filterByCampus(programSelect, campusId);
      }
      if (departmentSelect) {
        filterByCampus(departmentSelect, campusId);
      }
    }

    if (campusSelect) {
      campusSelect.addEventListener('change', syncCampusFilters);
    }

    syncCampusFilters();
  })();
</script>
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Invigilation Duties</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.invigilation-duties.index') }}">Invigilation Duties</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Duty</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-edit me-2"></i>Edit Invigilation Duty</h3>
                  <p class="text-white-50 mb-0">Update the duty assignment details</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.invigilation-duties.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Edit Form -->
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <form action="{{ route('admin.invigilation-duties.update', $duty->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Exam <span class="text-danger">*</span></label>
                <select name="exam_id" class="form-select @error('exam_id') is-invalid @enderror" required>
                  <option value="">Select Exam</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}" {{ old('exam_id', $duty->exam_id) == $exam->id ? 'selected' : '' }}>
                    {{ $exam->name }}
                  </option>
                  @endforeach
                </select>
                @error('exam_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                <select name="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
                  <option value="">Select Faculty</option>
                  @foreach($faculties as $faculty)
                  <option value="{{ $faculty->id }}" {{ old('faculty_id', $duty->faculty_id) == $faculty->id ? 'selected' : '' }}>
                    {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }} - {{ $faculty->DEPARTMENT }}
                  </option>
                  @endforeach
                </select>
                @error('faculty_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Room <span class="text-danger">*</span></label>
                <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                  <option value="">Select Room</option>
                  @foreach($rooms as $room)
                  <option value="{{ $room->id }}" {{ old('room_id', $duty->room_id) == $room->id ? 'selected' : '' }}>
                    {{ $room->name }} (Capacity: {{ $room->capacity }})
                  </option>
                  @endforeach
                </select>
                @error('room_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $duty->date) }}" required>
                @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                <select name="session" class="form-select @error('session') is-invalid @enderror" required>
                  <option value="">Select Session</option>
                  <option value="morning" {{ old('session', $duty->session) === 'morning' ? 'selected' : '' }}>Morning</option>
                  <option value="afternoon" {{ old('session', $duty->session) === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                  <option value="evening" {{ old('session', $duty->session) === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
                @error('session')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                  <option value="">Select Role</option>
                  <option value="chief_invigilator" {{ old('role', $duty->role) === 'chief_invigilator' ? 'selected' : '' }}>Chief Invigilator</option>
                  <option value="invigilator" {{ old('role', $duty->role) === 'invigilator' ? 'selected' : '' }}>Invigilator</option>
                  <option value="reliever" {{ old('role', $duty->role) === 'reliever' ? 'selected' : '' }}>Reliever</option>
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Update Duty
              </button>
              <a href="{{ route('admin.invigilation-duties.index') }}" class="btn btn-outline-secondary btn-lg ms-2">
                <i class="fas fa-times me-2"></i>Cancel
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')
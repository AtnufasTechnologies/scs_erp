@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Admit Cards</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.admit-cards.index') }}">Admit Cards</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bulk Generate</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-file-pdf me-2"></i>Bulk Generate Admit Cards</h3>
                  <p class="text-white-50 mb-0">Generate and download admit cards for multiple students at once</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.admit-cards.index') }}" class="btn btn-outline-light">
                    <i class="fa fa-arrow-left me-1"></i>Back to List
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Information Card -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <div class="alert alert-info border-0 mb-0">
            <div class="d-flex align-items-start">
              <i class="fas fa-info-circle me-3 mt-1" style="font-size: 1.5rem;"></i>
              <div>
                <h6 class="mb-2"><strong>Requirements for Admit Card Generation</strong></h6>
                <p class="mb-0">Admit cards will be generated only for students who have:</p>
                <ul class="mb-0 mt-2">
                  <li>Approved registration status</li>
                  <li>Seating allocation completed</li>
                  <li>Dummy number assigned</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Generation Form -->
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-cogs me-2 text-primary"></i>Select Criteria</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('coe.admit-cards.bulk-download') }}" method="POST" id="bulkGenerateForm">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label for="exam_session_id" class="form-label fw-bold">Select Exam Session <span class="text-danger">*</span></label>
                <select name="exam_session_id" id="exam_session_id" class="form-select @error('exam_session_id') is-invalid @enderror" required>
                  <option value="">-- Select Exam Session --</option>
                  @foreach($examSessions as $session)
                  <option value="{{ $session->id }}" {{ old('exam_session_id') == $session->id ? 'selected' : '' }}>
                    {{ $session->name }} ({{ $session->program_type }}) - Sem {{ $session->semester }} - {{ \Carbon\Carbon::parse($session->start_date)->format('d M Y') }}
                  </option>
                  @endforeach
                </select>
                @error('exam_session_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="semester_id" class="form-label fw-bold">Semester (Optional)</label>
                <select name="semester_id" id="semester_id" class="form-select">
                  <option value="">All Semesters</option>
                  @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" {{ old('semester_id') == $i ? 'selected' : '' }}>
                    Semester {{ $i }}
                    </option>
                    @endfor
                </select>
              </div>

              <div class="col-md-6">
                <label for="programme_id" class="form-label fw-bold">Programme (Optional)</label>
                <select name="programme_id" id="programme_id" class="form-select">
                  <option value="">All Programmes</option>
                  @foreach($programmes as $programme)
                  <option value="{{ $programme->id }}" {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                    {{ $programme->name }}
                  </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label for="department_id" class="form-label fw-bold">Department (Optional)</label>
                <select name="department_id" id="department_id" class="form-select">
                  <option value="">All Departments</option>
                  @foreach($departments as $department)
                  <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                    {{ $department->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="mt-4" style="display: none;">
              <hr>
              <h6 class="fw-bold mb-3"><i class="fa fa-eye me-2"></i>Preview - Students Ready for Generation</h6>
              <div id="previewContent">
                <!-- AJAX content will be loaded here -->
              </div>
            </div>

            <div class="mt-4">
              <button type="button" class="btn btn-info px-4" id="previewBtn">
                <i class="fas fa-eye me-2"></i>Preview Students
              </button>
              <button type="submit" class="btn btn-success px-4" id="generateBtn" disabled>
                <i class="fas fa-file-pdf me-2"></i>Generate & Download All
              </button>
            </div>
          </form>
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
  document.addEventListener('DOMContentLoaded', function() {
    let studentsCount = 0;

    document.getElementById('previewBtn')?.addEventListener('click', function() {
      const examId = document.getElementById('exam_session_id').value;

      if (!examId) {
        alert('Please select an exam first');
        return;
      }

      const btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';

      const formData = {
        exam_session_id: examId,
        semester_id: document.getElementById('semester_id').value,
        programme_id: document.getElementById('programme_id').value,
        department_id: document.getElementById('department_id').value
      };

      const params = new URLSearchParams(formData);

      fetch(`/erp/admin/registrations/export?${params}`)
        .then(response => response.json())
        .then(response => {
          // Filter students who have seating and dummy number
          const readyStudents = response.filter(student =>
            student.seating_allocation && student.dummy_number
          );

          studentsCount = readyStudents.length;

          if (studentsCount > 0) {
            let html = `
              <div class="alert alert-success border-0">
                <i class="fa fa-check-circle me-2"></i>
                <strong>${studentsCount}</strong> student(s) are ready for admit card generation.
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Reg. No.</th>
                      <th>Name</th>
                      <th>Programme</th>
                      <th>Room</th>
                      <th>Seat</th>
                      <th>Dummy No.</th>
                    </tr>
                  </thead>
                  <tbody>
            `;

            readyStudents.forEach(student => {
              html += `
                <tr>
                  <td><strong>${student.student?.register_no || 'N/A'}</strong></td>
                  <td>${student.student?.full_name || 'N/A'}</td>
                  <td>${student.student?.programme?.name || 'N/A'}</td>
                  <td><span class="badge bg-info">${student.seating_allocation?.room?.room_name || 'N/A'}</span></td>
                  <td><strong>${student.seating_allocation?.seat_number || 'N/A'}</strong></td>
                  <td><strong class="text-primary">${student.dummy_number?.dummy_number || 'N/A'}</strong></td>
                </tr>
              `;
            });

            html += '</tbody></table></div>';

            document.getElementById('previewContent').innerHTML = html;
            document.getElementById('previewSection').style.display = 'block';
            document.getElementById('generateBtn').disabled = false;
          } else {
            document.getElementById('previewContent').innerHTML = `
              <div class="alert alert-warning border-0">
                <i class="fa fa-exclamation-triangle me-2"></i>
                No students found with complete information (seating + dummy number).
              </div>
            `;
            document.getElementById('previewSection').style.display = 'block';
            document.getElementById('generateBtn').disabled = true;
          }
        })
        .catch(error => {
          alert('Error loading student data');
          console.error(error);
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-eye me-2"></i>Preview Students';
        });
    });

    document.getElementById('bulkGenerateForm')?.addEventListener('submit', function(e) {
      if (studentsCount === 0) {
        e.preventDefault();
        alert('No students available for admit card generation');
        return false;
      }

      if (!confirm(`Generate admit cards for ${studentsCount} student(s)?`)) {
        e.preventDefault();
        return false;
      }

      const generateBtn = document.getElementById('generateBtn');
      generateBtn.disabled = true;
      generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
    });

    // Reset preview when filters change
    ['exam_session_id', 'semester_id', 'programme_id', 'department_id'].forEach(id => {
      document.getElementById(id)?.addEventListener('change', function() {
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('generateBtn').disabled = true;
        studentsCount = 0;
      });
    });
  });
</script>

@include('includes.footer')
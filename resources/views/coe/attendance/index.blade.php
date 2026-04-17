@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Attendance</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Exam Attendance</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-user-check me-2"></i>Exam Attendance Management</h3>
                  <p class="text-white-50 mb-0">Monitor and manage student attendance for ongoing examinations</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.attendance.view') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-list me-2"></i>View All Records
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

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-calendar-day text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Today's Exams</p>
                  <h4 class="mb-0 fw-bold">{{ $todayExams ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-user-check text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Present Today</p>
                  <h4 class="mb-0 fw-bold">{{ $todayPresent ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-user-times text-danger" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Absent Today</p>
                  <h4 class="mb-0 fw-bold">{{ $todayAbsent ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3">
                  <i class="fas fa-percentage text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Attendance %</p>
                  <h4 class="mb-0 fw-bold">{{ $attendancePercent ?? 0 }}%</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Marking Options -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-check-double me-2 text-primary"></i>Choose Attendance Marking Method</h6>
            </div>
            <div class="card-body p-4">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="method-card border rounded p-4 h-100 text-center" style="border: 2px solid #e0e0e0; transition: all 0.3s;">
                    <div class="mb-3">
                      <i class="fas fa-door-open" style="font-size: 3rem; color: #667eea;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Room-wise Marking</h5>
                    <p class="text-muted mb-3">Mark attendance by room allocation with color-coded interface and AJAX updates</p>
                    <ul class="text-start mb-4" style="list-style: none; padding-left: 0;">
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Visual room tabs</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Quick status toggle</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Real-time updates</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Color-coded rows</li>
                    </ul>
                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="showRoomWiseModal()">
                      <i class="fas fa-door-open me-2"></i>Start Room-wise Marking
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="method-card border rounded p-4 h-100 text-center" style="border: 2px solid #e0e0e0; transition: all 0.3s;">
                    <div class="mb-3">
                      <i class="fas fa-list-check" style="font-size: 3rem; color: #764ba2;"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Subject-wise Marking</h5>
                    <p class="text-muted mb-3">Traditional attendance marking by subject for all enrolled students</p>
                    <ul class="text-start mb-4" style="list-style: none; padding-left: 0;">
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Filter by session</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Subject-specific</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Batch processing</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Bulk operations</li>
                    </ul>
                    <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="$('#subjectWiseSection').slideToggle()">
                      <i class="fas fa-list me-2"></i>Start Subject-wise Marking
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Select Exam Section (Hidden by default) -->
      <div class="row" id="subjectWiseSection" style="display: none;">
        <div class="col-lg-10 col-md-12 mx-auto">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2 text-primary"></i>Select Exam to Mark Attendance</h6>
            </div>
            <div class="card-body p-4">
              @if(!isset($ongoingExams) || $ongoingExams->isEmpty())
              <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                  <i class="fas fa-info-circle me-3" style="font-size: 2rem;"></i>
                  <div>
                    <h6 class="mb-1">No ongoing exams</h6>
                    <p class="mb-0">There are no scheduled exams at the moment. Check back later.</p>
                  </div>
                </div>
              </div>
              @else
              <form id="attendanceForm">
                <!-- Exam Selection -->
                <div class="mb-4">
                  <label for="examSelect" class="form-label fw-bold">
                    <i class="fa fa-clipboard-list me-2"></i>Select Exam
                  </label>
                  <select class="form-select form-select-lg" id="examSelect" name="exam_id">
                    <option value="" selected disabled>Choose an exam...</option>
                    @if(isset($ongoingExams))
                    @foreach($ongoingExams as $exam)
                    <option value="{{ $exam->id }}"
                      data-exam-date="{{ $exam->exam_date }}">
                      {{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
                    </option>
                    @endforeach
                    @endif
                  </select>
                </div>

                <div class="row">
                  <div class="col-lg-6 mb-3">
                    <label for="sessionSelect" class="form-label fw-bold">Exam Session</label>
                    <select id="sessionSelect" class="form-select" name="session_id">
                      <option value="" selected disabled>Choose session...</option>
                      @foreach($sessions as $session)
                      <option value="{{ $session->id }}">{{ $session->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-lg-6 mb-3">
                    <label for="attendanceDate" class="form-label fw-bold">Date</label>
                    <input type="date" id="attendanceDate" name="exam_date" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                  </div>
                </div>

                <div class="mb-4">
                  <label for="subjectSelect" class="form-label fw-bold">
                    <i class="fa fa-book me-2"></i>Subject
                  </label>
                  <select class="form-select" id="subjectSelect" name="subject_id">
                    <option value="" selected disabled>Choose subject...</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-4 text-center">
                  <button type="button" class="btn btn-success btn-lg" id="btnLoadStudents" disabled>
                    <i class="fa fa-users me-2"></i>Load Students for Attendance
                  </button>
                </div>
              </form>
              @endif
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

  .stats-card {
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.05);
  }

  .card {
    transition: all 0.3s ease;
  }

  .card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const examSelect = document.getElementById('examSelect');
    const sessionSelect = document.getElementById('sessionSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const attendanceDate = document.getElementById('attendanceDate');
    const btnLoadStudents = document.getElementById('btnLoadStudents');

    function checkEnableButton() {
      if (examSelect.value && sessionSelect.value && subjectSelect.value && attendanceDate.value) {
        btnLoadStudents.disabled = false;
      } else {
        btnLoadStudents.disabled = true;
      }
    }

    examSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      if (selectedOption.dataset.examDate) {
        attendanceDate.value = selectedOption.dataset.examDate;
      }
      checkEnableButton();
    });

    sessionSelect.addEventListener('change', checkEnableButton);
    subjectSelect.addEventListener('change', checkEnableButton);
    attendanceDate.addEventListener('change', checkEnableButton);

    btnLoadStudents.addEventListener('click', function() {
      const examId = examSelect.value;
      const sessionId = sessionSelect.value;
      const subjectId = subjectSelect.value;
      const date = attendanceDate.value;

      const url = `{{ url('erp/coe/attendance/take') }}?exam_id=${examId}&session_id=${sessionId}&subject_id=${subjectId}&exam_date=${date}`;
      window.location.href = url;
    });
  });

  // Room-wise attendance modal
  function showRoomWiseModal() {
    const modal = document.getElementById('roomWiseModal');
    if (modal) {
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
    }
  }

  function startRoomWiseMarking() {
    const examId = document.getElementById('roomExamSelect')?.value;
    if (!examId) {
      alert('Please select an exam');
      return;
    }
    window.location.href = `{{ url('erp/coe/attendance/room-wise') }}/${examId}`;
  }

  // Method card hover effect
  document.querySelectorAll('.method-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.borderColor = '#667eea';
      this.style.transform = 'translateY(-5px)';
      this.style.boxShadow = '0 10px 20px rgba(102, 126, 234, 0.2)';
    });
    card.addEventListener('mouseleave', function() {
      this.style.borderColor = '#e0e0e0';
      this.style.transform = 'translateY(0)';
      this.style.boxShadow = 'none';
    });
  });
</script>

<!-- Room-wise Attendance Modal -->
<div class="modal fade" id="roomWiseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header gradient-coe text-white">
        <h5 class="modal-title"><i class="fas fa-door-open me-2"></i>Select Exam for Room-wise Attendance</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
          <label for="roomExamSelect" class="form-label fw-bold">Select Exam</label>
          <select class="form-select form-select-lg" id="roomExamSelect">
            <option value="" selected disabled>Choose an exam...</option>
            @if(isset($ongoingExams) && $ongoingExams->isNotEmpty())
            @foreach($ongoingExams as $exam)
            <option value="{{ $exam->id }}">
              {{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}
            </option>
            @endforeach
            @else
            <option value="" disabled>No exams available</option>
            @endif
          </select>
        </div>
        <div class="alert alert-info border-0 mb-0">
          <small>
            <i class="fas fa-info-circle me-1"></i>
            <strong>Room-wise marking features:</strong> Visual room tabs, color-coded status, instant AJAX updates, and bulk marking options.
          </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="startRoomWiseMarking()">
          <i class="fas fa-arrow-right me-2"></i>Continue
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>

@include('includes.footer')
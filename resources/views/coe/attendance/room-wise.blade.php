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
            <li class="breadcrumb-item"><a href="{{ route('coe.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Room-wise Marking</li>
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
              <h5 class="mb-1 fw-bold"><i class="fas fa-door-open me-2"></i>Room-wise Attendance Marking</h5>
              <small>{{ $exam->name }} - {{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}</small>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="{{ route('coe.attendance.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
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

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <p class="mb-1 text-muted">Total Students</p>
                  <h4 class="mb-0 fw-bold">{{ $totalStudents }}</h4>
                </div>
                <div class="widget-icon bg-primary text-white">
                  <i class="fas fa-users"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <p class="mb-1 text-muted">Present</p>
                  <h4 class="mb-0 fw-bold text-success" id="presentCount">0</h4>
                </div>
                <div class="widget-icon bg-success text-white">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <p class="mb-1 text-muted">Absent</p>
                  <h4 class="mb-0 fw-bold text-danger" id="absentCount">{{ $totalStudents }}</h4>
                </div>
                <div class="widget-icon bg-danger text-white">
                  <i class="fas fa-times-circle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 stat-card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <p class="mb-1 text-muted">Malpractice</p>
                  <h4 class="mb-0 fw-bold text-warning" id="malpracticeCount">0</h4>
                </div>
                <div class="widget-icon bg-warning text-white">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
          <div class="d-flex align-items-center gap-4">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i><strong>Status Legend:</strong></small>
            <span class="badge bg-danger">Absent (Default)</span>
            <span class="badge bg-success">Present</span>
            <span class="badge bg-warning text-dark">Malpractice</span>
          </div>
        </div>
      </div>

      <!-- Room Tabs -->
      @if($rooms->isEmpty())
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>No room allocations found for this exam.
      </div>
      @else
      <div class="card shadow-sm">
        <div class="card-body">
          <!-- Tabs Navigation -->
          <ul class="nav nav-tabs mb-4" id="roomTabs" role="tablist">
            @foreach($rooms as $index => $room)
            <li class="nav-item" role="presentation">
              <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                id="room-{{ $room->id }}-tab"
                data-bs-toggle="tab"
                data-bs-target="#room-{{ $room->id }}"
                type="button"
                role="tab">
                <i class="fas fa-door-open me-1"></i>
                {{ $room->room_name }}
                <span class="badge bg-secondary ms-2">{{ $room->students->count() }}</span>
              </button>
            </li>
            @endforeach
          </ul>

          <!-- Tabs Content -->
          <div class="tab-content" id="roomTabsContent">
            @foreach($rooms as $index => $room)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
              id="room-{{ $room->id }}"
              role="tabpanel">

              <!-- Room Actions -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1 fw-bold">{{ $room->room_name }}</h6>
                  <small class="text-muted">
                    <i class="fas fa-building me-1"></i>{{ $room->block->block_name ?? 'N/A' }} |
                    Capacity: {{ $room->capacity ?? 'N/A' }}
                  </small>
                </div>
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-success" onclick="markAllRoom({{ $room->id }}, 'present')">
                    <i class="fas fa-check-double me-1"></i>All Present
                  </button>
                  <button type="button" class="btn btn-sm btn-danger" onclick="markAllRoom({{ $room->id }}, 'absent')">
                    <i class="fas fa-times me-1"></i>All Absent
                  </button>
                </div>
              </div>

              <!-- Search Box -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <input type="text" class="form-control room-search"
                    data-room="{{ $room->id }}"
                    placeholder="Search by name, reg no, seat no...">
                </div>
              </div>

              <!-- Students Table -->
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="5%">#</th>
                      <th width="10%">Seat</th>
                      <th width="12%">Reg No</th>
                      <th width="20%">Student Name</th>
                      <th width="10%">Dummy No</th>
                      <th width="13%" class="text-center">Status</th>
                      <th width="30%">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="room-tbody" data-room="{{ $room->id }}">
                    @forelse($room->students as $idx => $student)
                    @php
                    $attendance = $attendanceData->where('exam_student_id', $student->exam_student_id)->first();
                    $status = $attendance->status ?? 'absent';
                    @endphp
                    <tr class="student-row status-{{ $status }}"
                      data-student="{{ $student->exam_student_id }}"
                      data-status="{{ $status }}"
                      data-search="{{ strtolower($student->student->full_name ?? '') }} {{ strtolower($student->student->register_no ?? '') }} {{ strtolower($student->seat_number) }}">
                      <td>{{ $idx + 1 }}</td>
                      <td><span class="badge bg-info">{{ $student->seat_number }}</span></td>
                      <td><strong>{{ $student->student->register_no ?? 'N/A' }}</strong></td>
                      <td>{{ $student->student->full_name ?? 'N/A' }}</td>
                      <td>
                        @if($student->dummyNumber)
                        <span class="badge bg-primary">{{ $student->dummyNumber->dummy_number }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <span class="status-badge">
                          @if($status === 'present')
                          <span class="badge bg-success"><i class="fas fa-check me-1"></i>Present</span>
                          @elseif($status === 'malpractice')
                          <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Malpractice</span>
                          @else
                          <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Absent</span>
                          @endif
                        </span>
                      </td>
                      <td>
                        <div class="btn-group btn-group-sm" role="group">
                          <button type="button"
                            class="btn btn-outline-success mark-btn {{ $status === 'present' ? 'active' : '' }}"
                            onclick="markAttendance({{ $student->exam_student_id }}, 'present')"
                            data-status="present">
                            <i class="fas fa-check"></i> Present
                          </button>
                          <button type="button"
                            class="btn btn-outline-danger mark-btn {{ $status === 'absent' ? 'active' : '' }}"
                            onclick="markAttendance({{ $student->exam_student_id }}, 'absent')"
                            data-status="absent">
                            <i class="fas fa-times"></i> Absent
                          </button>
                          <button type="button"
                            class="btn btn-outline-warning mark-btn {{ $status === 'malpractice' ? 'active' : '' }}"
                            onclick="markAttendance({{ $student->exam_student_id }}, 'malpractice')"
                            data-status="malpractice">
                            <i class="fas fa-exclamation-triangle"></i> Malpractice
                          </button>
                        </div>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">No students allocated to this room</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .stat-card {
    transition: transform 0.2s;
  }

  .stat-card:hover {
    transform: translateY(-3px);
  }

  .widget-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 1.5rem;
  }

  /* Color-coded rows */
  .student-row.status-absent {
    background-color: #ffe6e6 !important;
  }

  .student-row.status-present {
    background-color: #e6ffe6 !important;
  }

  .student-row.status-malpractice {
    background-color: #fff4e6 !important;
  }

  .student-row {
    transition: all 0.3s ease;
  }

  .mark-btn.active {
    font-weight: bold;
    border-width: 2px;
  }

  .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }

  .nav-tabs .nav-link {
    color: #495057;
    border: none;
    border-bottom: 3px solid transparent;
  }

  .nav-tabs .nav-link.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: bold;
  }

  .nav-tabs .nav-link:hover {
    border-bottom-color: #764ba2;
  }
</style>

<input type="hidden" id="examIdValue" value="{{ $exam->id }}">
<input type="hidden" id="totalStudentsValue" value="{{ $totalStudents }}">
<input type="hidden" id="updateStatusUrl" value="{{ route('coe.attendance.update-status') }}">
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

<script>
  const examId = document.getElementById('examIdValue').value;
  const totalStudentsVal = parseInt(document.getElementById('totalStudentsValue').value) || 0;
  let statusCounts = {
    present: 0,
    absent: totalStudentsVal,
    malpractice: 0
  };

  // Initialize counts on page load
  document.addEventListener('DOMContentLoaded', function() {
    updateStatusCounts();
  });

  // Mark individual attendance via AJAX
  function markAttendance(examStudentId, status) {
    fetch(document.getElementById('updateStatusUrl').value, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.getElementById('csrfToken').value
        },
        body: JSON.stringify({
          exam_id: examId,
          exam_student_id: examStudentId,
          status: status
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update row visual state
          const row = document.querySelector(`tr[data-student="${examStudentId}"]`);
          if (row) {
            // Remove old status classes
            row.classList.remove('status-present', 'status-absent', 'status-malpractice');
            // Add new status class
            row.classList.add(`status-${status}`);

            // Update status badge
            const statusBadge = row.querySelector('.status-badge');
            let badgeHTML = '';
            if (status === 'present') {
              badgeHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Present</span>';
            } else if (status === 'malpractice') {
              badgeHTML = '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Malpractice</span>';
            } else {
              badgeHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Absent</span>';
            }
            statusBadge.innerHTML = badgeHTML;

            // Update button states
            const buttons = row.querySelectorAll('.mark-btn');
            buttons.forEach(btn => {
              btn.classList.remove('active');
              if (btn.dataset.status === status) {
                btn.classList.add('active');
              }
            });

            // Update row data attribute
            row.dataset.status = status;

            // Update counts
            updateStatusCounts();
          }

          // Show success toast
          showToast('success', `Marked as ${status}`);
        } else {
          showToast('error', data.message || 'Failed to update attendance');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Network error occurred');
      });
  }

  // Mark all students in a room
  function markAllRoom(roomId, status) {
    const tbody = document.querySelector(`.room-tbody[data-room="${roomId}"]`);
    const rows = tbody.querySelectorAll('.student-row');

    if (!confirm(`Mark all ${rows.length} students in this room as ${status}?`)) {
      return;
    }

    rows.forEach(row => {
      const studentId = row.dataset.student;
      markAttendance(studentId, status);
    });
  }

  // Update status counts
  function updateStatusCounts() {
    const allRows = document.querySelectorAll('.student-row');
    const counts = {
      present: 0,
      absent: 0,
      malpractice: 0
    };

    allRows.forEach(row => {
      const status = row.dataset.status;
      if (counts[status] !== undefined) {
        counts[status]++;
      }
    });

    document.getElementById('presentCount').textContent = counts.present;
    document.getElementById('absentCount').textContent = counts.absent;
    document.getElementById('malpracticeCount').textContent = counts.malpractice;
  }

  // Search functionality
  document.querySelectorAll('.room-search').forEach(input => {
    input.addEventListener('keyup', function() {
      const searchTerm = this.value.toLowerCase();
      const roomId = this.dataset.room;
      const tbody = document.querySelector(`.room-tbody[data-room="${roomId}"]`);
      const rows = tbody.querySelectorAll('.student-row');

      rows.forEach(row => {
        const searchData = row.dataset.search;
        if (searchData.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });

  // Toast notification
  function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
    toast.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.remove();
    }, 3000);
  }
</script>

@include('includes.footer')
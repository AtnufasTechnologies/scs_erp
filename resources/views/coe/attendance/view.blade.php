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
            <li class="breadcrumb-item"><a href="{{ route('coe.attendance.index') }}">Attendance</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Records</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-list me-2"></i>Attendance Records</h3>
                  <p class="text-white-50 mb-0">View and manage existing exam attendance records</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.attendance.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Mark New Attendance
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <!-- Filter Section -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Records</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('coe.attendance.view') }}" method="GET">
            <div class="row g-3">
              <div class="col-lg-3">
                <label for="filterDate" class="form-label fw-bold">Attendance Date</label>
                <input type="date" name="attendance_date" id="filterDate" class="form-control"
                  value="{{ request('attendance_date') }}" max="{{ date('Y-m-d') }}">
              </div>
              <div class="col-lg-4">
                <label for="filterExam" class="form-label fw-bold">Exam</label>
                <select class="form-select" name="exam_id" id="filterExam">
                  <option value="">All Exams</option>
                  @foreach($exams as $exam)
                  <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                    {{ $exam->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-3">
                <label for="filterSubject" class="form-label fw-bold">Subject</label>
                <select class="form-select" name="subject_id" id="filterSubject">
                  <option value="">All Subjects</option>
                  @foreach($subjects as $subject)
                  <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fa fa-search me-2"></i>Filter
                </button>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-12">
                <a href="{{ route('coe.attendance.view') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="fa fa-refresh me-1"></i>Reset Filters
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Attendance Records Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-calendar-check me-2"></i>Attendance Records</h6>
            @if(!$attendanceRecords->isEmpty())
            <div class="btn-group">
              <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToExcel()">
                <i class="fa fa-file-excel me-1"></i>Export Excel
              </button>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fa fa-print me-1"></i>Print
              </button>
            </div>
            @endif
          </div>
        </div>
        <div class="card-body">
          @if($attendanceRecords->isEmpty())
          <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-3" style="font-size: 2rem;"></i>
              <div>
                <h6 class="mb-1">No attendance records found</h6>
                <p class="mb-0">Try adjusting your filters or mark attendance for an exam first.</p>
              </div>
            </div>
          </div>
          @else
          <!-- Search Box -->
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text bg-white">
                  <i class="fa fa-search"></i>
                </span>
                <input type="text" class="form-control" id="recordSearch"
                  placeholder="Search by student name, reg no, or exam..."
                  autocomplete="off">
              </div>
            </div>
            <div class="col-md-6 text-end">
              <span class="badge bg-secondary fs-6 px-3 py-2">
                Total Records: <strong id="visibleRecordCount">{{ $attendanceRecords->count() }}</strong>
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover attendance-table" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th width="3%">#</th>
                  <th width="10%">Date</th>
                  <th width="15%">Exam</th>
                  <th width="12%">Session</th>
                  <th width="15%">Subject</th>
                  <th width="10%">Reg No</th>
                  <th width="20%">Student Name</th>
                  <th width="8%" class="text-center">Status</th>
                  <th width="12%">Remarks</th>
                  <th width="5%" class="text-center no-print">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($attendanceRecords as $record)
                <tr class="attendance-row"
                  data-search="{{ strtolower($record->student->first_name . ' ' . $record->student->last_name . ' ' . $record->student->roll_no . ' ' . $record->exam->name) }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ \Carbon\Carbon::parse($record->exam->exam_date)->format('d M Y') }}</td>
                  <td>{{ $record->exam->name ?? 'N/A' }}</td>
                  <td>{{ $record->student->batchmaster->batch_name ?? 'N/A' }}</td>
                  <td>{{ $record->subject->name ?? 'N/A' }}</td>
                  <td><span class="text-uppercase fw-bold">{{ $record->student->roll_no ?? 'N/A' }}</span></td>
                  <td class="text-capitalize">{{ $record->student->first_name }} {{ $record->student->last_name }}</td>
                  <td class="text-center">
                    @if($record->status === 'present')
                    <span class="badge bg-success"><i class="fa fa-check"></i> Present</span>
                    @elseif($record->status === 'absent')
                    <span class="badge bg-danger"><i class="fa fa-times"></i> Absent</span>
                    @endif
                  </td>
                  <td>{{ $record->remarks ?? '-' }}</td>
                  <td class="text-center no-print">
                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                      data-id="{{ $record->id }}"
                      data-student="{{ $record->student->first_name }} {{ $record->student->last_name }}"
                      data-date="{{ $record->exam->exam_date }}"
                      title="Delete Record">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
              Showing {{ $attendanceRecords->firstItem() ?? 0 }} to {{ $attendanceRecords->lastItem() ?? 0 }}
              of {{ $attendanceRecords->total() }} records
            </div>
            <div>
              {{ $attendanceRecords->withQueryString()->links() }}
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .attendance-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    white-space: nowrap;
  }

  .attendance-row {
    transition: all 0.2s ease;
  }

  .attendance-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
  }

  @media print {
    .no-print {
      display: none !important;
    }

    .btn-group,
    .input-group,
    .breadcrumb,
    .sidebar {
      display: none !important;
    }

    .card {
      border: none !important;
      box-shadow: none !important;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('recordSearch');
    const visibleRecordCount = document.getElementById('visibleRecordCount');

    searchInput?.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.attendance-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const searchData = row.dataset.search;
        if (searchData.includes(searchTerm)) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });

      visibleRecordCount.textContent = visibleCount;
    });

    // Delete functionality
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const recordId = this.dataset.id;
        const studentName = this.dataset.student;
        const date = this.dataset.date;

        if (confirm(`Are you sure you want to delete attendance record for ${studentName} on ${date}?`)) {
          // Create a form and submit
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `{{ url('erp/coe/attendance/delete') }}/${recordId}`;

          const csrfToken = document.createElement('input');
          csrfToken.type = 'hidden';
          csrfToken.name = '_token';
          csrfToken.value = '{{ csrf_token() }}';

          const methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'DELETE';

          form.appendChild(csrfToken);
          form.appendChild(methodInput);
          document.body.appendChild(form);
          form.submit();
        }
      });
    });
  });
</script>

@verbatim
<script>
  // Export to Excel
  function exportToExcel() {
    const table = document.getElementById('exportTable');
    const clonedTable = table.cloneNode(true);

    // Remove action column
    clonedTable.querySelectorAll('th:last-child, td:last-child').forEach(el => el.remove());

    let html = clonedTable.outerHTML;
    const uri = 'data:application/vnd.ms-excel;base64,';
    const template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Attendance</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
    const base64 = function(s) {
      return window.btoa(unescape(encodeURIComponent(s)))
    };

    const context = {
      table: html
    };

    const result = template.replace(/{(\w+)}/g, function(m, p) {
      return context[p];
    });

    const link = document.createElement('a');
    link.href = uri + base64(result);
    link.download = 'exam_attendance_records_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
  }
</script>
@endverbatim

@include('includes.footer')
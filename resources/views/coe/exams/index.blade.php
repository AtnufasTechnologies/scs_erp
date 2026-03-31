@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Exam Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Exams</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-clipboard-list me-2"></i>Examination Management</h3>
                  <p class="text-white-50 mb-0">Create, manage, and monitor all examination schedules and records</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.exams.create') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Create New Exam
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
                <div class="icon-wrapper me-3 bg-primary-subtle">
                  <i class="fas fa-calendar-alt text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Exams</p>
                  <h4 class="mb-0 fw-bold">{{ $totalExams ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3 bg-warning-subtle">
                  <i class="fas fa-clock text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Upcoming</p>
                  <h4 class="mb-0 fw-bold">{{ $upcomingExams ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3 bg-success-subtle">
                  <i class="fas fa-play-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Ongoing</p>
                  <h4 class="mb-0 fw-bold">{{ $ongoingExams ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card stats-card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper me-3 bg-info-subtle">
                  <i class="fas fa-check-circle text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Completed</p>
                  <h4 class="mb-0 fw-bold">{{ $completedExams ?? 0 }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Exams</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('coe.exams.index') }}" method="GET">
            <div class="row g-3">
              <div class="col-lg-3">
                <label for="filterStatus" class="form-label fw-bold">Status</label>
                <select class="form-select" name="status" id="filterStatus">
                  <option value="">All Status</option>
                  <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                  <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                  <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                  <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
              </div>
              <div class="col-lg-3">
                <label for="filterType" class="form-label fw-bold">Exam Type</label>
                <select class="form-select" name="exam_type" id="filterType">
                  <option value="">All Types</option>
                  <option value="Regular" {{ request('exam_type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                  <option value="Backlog" {{ request('exam_type') == 'Backlog' ? 'selected' : '' }}>Backlog</option>
                  <option value="Improvement" {{ request('exam_type') == 'Improvement' ? 'selected' : '' }}>Improvement</option>
                  <option value="Special" {{ request('exam_type') == 'Special' ? 'selected' : '' }}>Special</option>
                </select>
              </div>
              <div class="col-lg-3">
                <label for="filterProgram" class="form-label fw-bold">Program</label>
                <select class="form-select" name="program_id" id="filterProgram">
                  <option value="">All Programs</option>
                  @foreach($programs as $program)
                  <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                    {{ $program->name }} ({{ $program->code }})
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fa fa-search me-2"></i>Filter
                </button>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-12">
                <a href="{{ route('coe.exams.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="fa fa-refresh me-1"></i>Reset Filters
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Exams List -->
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-list me-2"></i>Exam List</h6>
            @if(!$exams->isEmpty())
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
          @if($exams->isEmpty())
          <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-3" style="font-size: 2rem;"></i>
              <div>
                <h6 class="mb-1">No exams found</h6>
                <p class="mb-0">Create your first exam to get started with examination management.</p>
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
                <input type="text" class="form-control" id="examSearch"
                  placeholder="Search by exam name, program, or type..."
                  autocomplete="off">
              </div>
            </div>
            <div class="col-md-6 text-end">
              <span class="badge bg-secondary fs-6 px-3 py-2">
                Total: <strong id="visibleExamCount">{{ $exams->count() }}</strong>
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th width="5%">#</th>
                  <th width="20%">Exam Name</th>
                  <th width="10%">Program</th>
                  <th width="8%">Type</th>
                  <th width="8%">Semester</th>
                  <th width="10%">Start Date</th>
                  <th width="10%">End Date</th>
                  <th width="10%" class="text-center">Status</th>
                  <th width="19%" class="text-center no-print">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($exams as $exam)
                <tr class="exam-row"
                  data-search="{{ strtolower($exam->name . ' ' . $exam->program->name . ' ' . $exam->exam_type) }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <strong>{{ $exam->name }}</strong>
                    @if($exam->exam_date)
                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ $exam->program->code }}</span><br>
                    <small>{{ $exam->program->name }}</small>
                  </td>
                  <td><span class="badge bg-info">{{ $exam->exam_type }}</span></td>
                  <td><span class="badge bg-purple">{{ $exam->semester ?? 'N/A' }}</span></td>
                  <td>{{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}</td>
                  <td class="text-center">
                    @if($exam->status === 'upcoming')
                    <span class="badge bg-warning"><i class="fa fa-clock"></i> Upcoming</span>
                    @elseif($exam->status === 'ongoing')
                    <span class="badge bg-success"><i class="fa fa-play-circle"></i> Ongoing</span>
                    @elseif($exam->status === 'completed')
                    <span class="badge bg-info"><i class="fa fa-check-circle"></i> Completed</span>
                    @elseif($exam->status === 'cancelled')
                    <span class="badge bg-danger"><i class="fa fa-times-circle"></i> Cancelled</span>
                    @endif
                  </td>
                  <td class="text-center no-print">
                    <div class="btn-group" role="group">
                      <a href="{{ route('coe.exams.show', $exam->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a href="{{ route('coe.exams.edit', $exam->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fa fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                        data-id="{{ $exam->id }}"
                        data-name="{{ $exam->name }}"
                        title="Delete">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
              Showing {{ $exams->firstItem() ?? 0 }} to {{ $exams->lastItem() ?? 0 }}
              of {{ $exams->total() }} exams
            </div>
            <div>
              {{ $exams->withQueryString()->links() }}
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
    align-items-center;
    justify-content: center;
  }

  .exam-row {
    transition: all 0.2s ease;
  }

  .exam-row:hover {
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
    const searchInput = document.getElementById('examSearch');
    const visibleExamCount = document.getElementById('visibleExamCount');

    searchInput?.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.exam-row');
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

      visibleExamCount.textContent = visibleCount;
    });

    // Delete functionality
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const examId = this.dataset.id;
        const examName = this.dataset.name;

        if (confirm(`Are you sure you want to delete the exam "${examName}"? This action cannot be undone.`)) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `{{ url('erp/coe/exams') }}/${examId}`;

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
    const template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Exams</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
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
    link.download = 'exams_list_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
  }
</script>
@endverbatim

@include('includes.footer')
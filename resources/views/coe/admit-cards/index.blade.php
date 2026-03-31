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
            <li class="breadcrumb-item active" aria-current="page">Admit Cards</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-id-card me-2"></i>Admit Cards Management</h3>
                  <p class="text-white-50 mb-0">View, generate and download admit cards for approved examinations</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.admit-cards.generate') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-file-pdf me-2"></i>Bulk Generate
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
                  <i class="fas fa-users text-primary" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Total Students</p>
                  <h4 class="mb-0 fw-bold">{{ $registrations->total() }}</h4>
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
                  <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Cards Ready</p>
                  <h4 class="mb-0 fw-bold">{{ $registrations->filter(fn($r) => $r->seatingAllocation && $r->dummyNumber)->count() }}</h4>
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
                  <i class="fas fa-exclamation-triangle text-warning" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Pending</p>
                  <h4 class="mb-0 fw-bold">{{ $registrations->filter(fn($r) => !$r->seatingAllocation || !$r->dummyNumber)->count() }}</h4>
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
                  <i class="fas fa-calendar-alt text-info" style="font-size: 1.8rem;"></i>
                </div>
                <div>
                  <p class="text-muted mb-1" style="font-size: 0.85rem;">Exam Sessions</p>
                  <h4 class="mb-0 fw-bold">{{ $examSessions->count() }}</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
          <h6 class="mb-0 fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Filter Admit Cards</h6>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('coe.admit-cards.index') }}">
            <div class="row g-3">
              <div class="col-lg-4">
                <label for="exam_session_id" class="form-label fw-bold">Exam Session</label>
                <select name="exam_session_id" id="exam_session_id" class="form-select">
                  <option value="">All Exam Sessions</option>
                  @foreach($examSessions as $session)
                  <option value="{{ $session->id }}" {{ request('exam_session_id') == $session->id ? 'selected' : '' }}>
                    {{ $session->name }} ({{ $session->program_type }}) - Sem {{ $session->semester }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-4">
                <label for="search" class="form-label fw-bold">Search Student</label>
                <input type="text" name="search" id="search" class="form-control"
                  value="{{ request('search') }}"
                  placeholder="Enter name or registration number">
              </div>
              <div class="col-lg-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                  <i class="fa fa-search me-2"></i>Filter
                </button>
                <a href="{{ route('coe.admit-cards.index') }}" class="btn btn-outline-secondary">
                  <i class="fa fa-refresh me-1"></i>Reset
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Admit Cards List -->
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-list me-2"></i>Admit Cards List</h6>
            @if(!$registrations->isEmpty())
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
          @if($registrations->isEmpty())
          <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-3" style="font-size: 2rem;"></i>
              <div>
                <h6 class="mb-1">No admit cards found</h6>
                <p class="mb-0">Approve student registrations and assign seating/dummy numbers to generate admit cards.</p>
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
                <input type="text" class="form-control" id="admitCardSearch"
                  placeholder="Search by name, reg no, room, or dummy number..."
                  autocomplete="off">
              </div>
            </div>
            <div class="col-md-6 text-end">
              <span class="badge bg-secondary fs-6 px-3 py-2">
                Total: <strong id="visibleCount">{{ $registrations->count() }}</strong>
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th width="5%">#</th>
                  <th width="12%">Reg. No.</th>
                  <th width="20%">Student Name</th>
                  <th width="15%">Exam</th>
                  <th width="8%">Semester</th>
                  <th width="12%">Room/Seat</th>
                  <th width="10%">Dummy No.</th>
                  <th width="10%" class="text-center">Status</th>
                  <th width="8%" class="text-center no-print">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($registrations as $registration)
                <tr class="admit-row"
                  data-search="{{ strtolower(($registration->student->register_no ?? '') . ' ' . ($registration->student->full_name ?? '') . ' ' . ($registration->seatingAllocation->room_no ?? '') . ' ' . ($registration->dummyNumber->dummy_number ?? '')) }}">
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $registration->student->register_no ?? 'N/A' }}</strong></td>
                  <td>
                    {{ $registration->student->full_name ?? 'N/A' }}
                    <br><small class="text-muted">Roll: {{ $registration->student->roll_no ?? 'N/A' }}</small>
                  </td>
                  <td>
                    <strong>{{ $registration->examSession->name ?? 'N/A' }}</strong>
                    <br><small class="text-muted">{{ $registration->examSession->program_type ?? '' }}</small>
                  </td>
                  <td><span class="badge bg-purple">Sem {{ $registration->examSession->semester ?? 'N/A' }}</span></td>
                  <td>
                    @if($registration->seatingAllocation)
                    <span class="badge bg-info">Room {{ $registration->seatingAllocation->room_no ?? 'N/A' }}</span>
                    <br><small>Seat: <strong>{{ $registration->seatingAllocation->seat_no }}</strong></small>
                    @else
                    <span class="badge bg-warning">Not Allocated</span>
                    @endif
                  </td>
                  <td>
                    @if($registration->dummyNumber)
                    <strong class="text-primary">{{ $registration->dummyNumber->dummy_number }}</strong>
                    @else
                    <span class="badge bg-warning">Not Assigned</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($registration->seatingAllocation && $registration->dummyNumber)
                    <span class="badge bg-success"><i class="fa fa-check-circle"></i> Ready</span>
                    @else
                    <span class="badge bg-secondary"><i class="fa fa-clock"></i> Pending</span>
                    @endif
                  </td>
                  <td class="text-center no-print">
                    <div class="btn-group" role="group">
                      <a href="{{ route('coe.admit-cards.show', $registration->id) }}"
                        class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="fa fa-eye"></i>
                      </a>
                      @if($registration->seatingAllocation && $registration->dummyNumber)
                      <a href="{{ route('coe.admit-cards.download', $registration->id) }}"
                        class="btn btn-sm btn-outline-primary" title="Download PDF" target="_blank">
                        <i class="fa fa-download"></i>
                      </a>
                      @endif
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
              Showing {{ $registrations->firstItem() ?? 0 }} to {{ $registrations->lastItem() ?? 0 }}
              of {{ $registrations->total() }} registrations
            </div>
            <div>
              {{ $registrations->withQueryString()->links() }}
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
    align-items: center;
    justify-content: center;
  }

  .admit-row {
    transition: all 0.2s ease;
  }

  .admit-row:hover {
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
    const searchInput = document.getElementById('admitCardSearch');
    const visibleCount = document.getElementById('visibleCount');

    searchInput?.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.admit-row');
      let visible = 0;

      rows.forEach(row => {
        const searchData = row.dataset.search;
        if (searchData.includes(searchTerm)) {
          row.style.display = '';
          visible++;
        } else {
          row.style.display = 'none';
        }
      });

      visibleCount.textContent = visible;
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
    const template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Admit Cards</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
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
    link.download = 'admit_cards_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
  }
</script>
@endverbatim

@include('includes.footer')
@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Regulation Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Regulations</li>
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
                  <h3 class="text-white fw-bold mb-0">
                    <i class="fas fa-book me-2"></i>Regulation Master
                  </h3>
                  <p class="text-white-50 mb-0 mt-1">Manage program regulations and academic rules</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.regulations.create') }}" class="btn btn-light">
                    <i class="fa fa-plus-circle me-1"></i>Add New Regulation
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

      <!-- Statistics Cards -->
      <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm stats-card border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-muted mb-1 small">Total Regulations</p>
                  <h4 class="fw-bold mb-0">{{ $totalRegulations }}</h4>
                </div>
                <div class="icon-wrapper bg-primary-subtle">
                  <i class="fas fa-book fa-2x text-primary"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm stats-card border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-muted mb-1 small">Active Regulations</p>
                  <h4 class="fw-bold mb-0 text-success">{{ $activeRegulations }}</h4>
                </div>
                <div class="icon-wrapper bg-success-subtle">
                  <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm stats-card border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-muted mb-1 small">UG Programs</p>
                  <h4 class="fw-bold mb-0 text-info">{{ $ugCount }}</h4>
                </div>
                <div class="icon-wrapper bg-info-subtle">
                  <i class="fas fa-graduation-cap fa-2x text-info"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="card shadow-sm stats-card border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <p class="text-muted mb-1 small">PG Programs</p>
                  <h4 class="fw-bold mb-0 text-warning">{{ $pgCount }}</h4>
                </div>
                <div class="icon-wrapper bg-warning-subtle">
                  <i class="fas fa-user-graduate fa-2x text-warning"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="GET" action="{{ route('coe.regulations.index') }}">
            <div class="row g-3">
              <div class="col-lg-3">
                <label class="form-label">Filter by Type</label>
                <select class="form-select" name="regulation_type">
                  <option value="">All Types</option>
                  <option value="Annual" {{ request('regulation_type') == 'Annual' ? 'selected' : '' }}>Annual</option>
                  <option value="Semester" {{ request('regulation_type') == 'Semester' ? 'selected' : '' }}>Semester</option>
                  <option value="Choice Based" {{ request('regulation_type') == 'Choice Based' ? 'selected' : '' }}>Choice Based</option>
                </select>
              </div>
              <div class="col-lg-3">
                <label class="form-label">Filter by Program</label>
                <select class="form-select" name="program_id">
                  <option value="">All Programs</option>
                  @foreach($programs as $program)
                  <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                    {{ $program->name }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="col-lg-3">
                <label class="form-label">Filter by Year</label>
                <select class="form-select" name="year">
                  <option value="">All Years</option>
                  @for($year = date('Y') + 2; $year >= 2015; $year--)
                  <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                  @endfor
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
                <a href="{{ route('coe.regulations.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="fa fa-refresh me-1"></i>Reset Filters
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Regulations List -->
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-list me-2"></i>Regulation List</h6>
            @if(!$regulations->isEmpty())
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
          @if($regulations->isEmpty())
          <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle me-3" style="font-size: 2rem;"></i>
              <div>
                <h6 class="mb-1">No regulations found</h6>
                <p class="mb-0">Create your first regulation to get started.</p>
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
                <input type="text" class="form-control" id="regulationSearch"
                  placeholder="Search by regulation name or program..."
                  autocomplete="off">
              </div>
            </div>
            <div class="col-md-6 text-end">
              <span class="badge bg-secondary fs-6 px-3 py-2">
                Total: <strong id="visibleRegulationCount">{{ $regulations->count() }}</strong>
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle" id="exportTable">
              <thead class="table-light">
                <tr>
                  <th width="5%">#</th>
                  <th width="25%">Regulation Name</th>
                  <th width="15%">Program</th>
                  <th width="15%">Type</th>
                  <th width="15%">Academic Period</th>
                  <th width="10%">Exams</th>
                  <th width="15%" class="text-center no-print">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($regulations as $regulation)
                <tr class="regulation-row"
                  data-search="{{ strtolower($regulation->regulation_name . ' ' . $regulation->program->name . ' ' . $regulation->regulation_type) }}">
                  <td>{{ $loop->iteration + ($regulations->currentPage() - 1) * $regulations->perPage() }}</td>
                  <td>
                    <strong>{{ $regulation->regulation_name }}</strong>
                  </td>
                  <td>
                    <span class="badge bg-secondary">{{ $regulation->program->code }}</span><br>
                    <small>{{ $regulation->program->name }}</small>
                  </td>
                  <td><span class="badge bg-info">{{ $regulation->regulation_type }}</span></td>
                  <td>
                    <i class="fa fa-calendar text-muted me-1"></i>
                    <strong>{{ $regulation->start_year }}</strong> - <strong>{{ $regulation->end_year }}</strong>
                  </td>
                  <td>
                    <span class="badge bg-primary">{{ $regulation->exams_count ?? 0 }}</span>
                  </td>
                  <td class="text-center no-print">
                    <div class="btn-group" role="group">
                      <a href="{{ route('coe.regulations.show', $regulation->id) }}"
                        class="btn btn-sm btn-outline-primary" title="View">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a href="{{ route('coe.regulations.edit', $regulation->id) }}"
                        class="btn btn-sm btn-outline-secondary" title="Edit">
                        <i class="fa fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="deleteRegulation({{ $regulation->id }})" title="Delete">
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
            <div>
              <p class="text-muted small mb-0">
                Showing {{ $regulations->firstItem() ?? 0 }} to {{ $regulations->lastItem() ?? 0 }}
                of {{ $regulations->total() }} regulations
              </p>
            </div>
            <div>
              {{ $regulations->links() }}
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

  .regulation-row {
    transition: all 0.2s ease;
  }

  .regulation-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
  }

  @media print {
    .no-print {
      display: none !important;
    }
  }
</style>

<script>
  // Search functionality
  document.getElementById('regulationSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.regulation-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const searchData = row.getAttribute('data-search');
      if (searchData.includes(searchTerm)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    document.getElementById('visibleRegulationCount').textContent = visibleCount;
  });

  // Delete confirmation
  function deleteRegulation(id) {
    if (confirm('Are you sure you want to delete this regulation? This action cannot be undone.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/erp/coe/regulations/${id}`;

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
  }

  // Export to Excel function
  function exportToExcel() {
    @verbatim
    const table = document.getElementById('exportTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];

    rows.forEach(row => {
      let cols = row.querySelectorAll('td, th');
      let csvRow = [];
      cols.forEach((col, index) => {
        if (index < cols.length - 1) {
          csvRow.push(col.innerText);
        }
      });
      csv.push(csvRow.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], {
      type: 'text/csv'
    });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'regulations_' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    @endverbatim
  }
</script>

@include('includes.footer')
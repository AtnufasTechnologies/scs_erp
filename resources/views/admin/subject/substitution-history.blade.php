@include('includes.header')
@if(Auth::user()->userroletype != 'dept-admin-erp')
@include('admin.sidebar')
@endif
<style>
  .custom-navbar {
    background: linear-gradient(135deg, #1e5742 0%, #8931f6 100%);
    border-radius: 0.75rem;
    box-shadow: 0 4px 16px #5740b433;
  }

  .custom-card {
    border-radius: 1rem;
    box-shadow: 0 2px 12px #5740b433;
    border: none;
    background: #fff;
  }

  .custom-table th,
  .custom-table td {
    border: 1px solid #eee;
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
    transition: background 0.2s;
  }

  .custom-table th {
    background: linear-gradient(90deg, #f3e9ff 0%, #e9f0ff 100%);
    color: #5740b4;
    font-weight: 600;
    letter-spacing: 0.05em;
  }

  .custom-table tr:hover td {
    background: #f7f7fa;
  }

  .filter-card {
    border: 2px solid #e9f0ff;
    border-radius: 0.8rem;
    padding: 1rem;
    margin: 0.5rem 0;
    background: linear-gradient(45deg, #f8f9ff 0%, #fff 100%);
  }

  .btn-primary {
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    border: none;
    box-shadow: 0 2px 8px #5740b433;
    font-weight: 600;
    letter-spacing: 0.03em;
  }

  .btn-primary:hover {
    background: #8931f6;
    box-shadow: 0 4px 16px #5740b433;
  }

  .btn-success {
    background: linear-gradient(90deg, #3bb54a 0%, #6be585 100%);
    border: none;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #fff;
    box-shadow: 0 2px 8px #3bb54a33;
  }

  .btn-info {
    background: linear-gradient(90deg, #17a2b8 0%, #20c997 100%);
    border: none;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #fff;
    box-shadow: 0 2px 8px #17a2b833;
  }

  .form-select,
  .form-label {
    font-size: 1.05em;
    font-weight: 500;
    letter-spacing: 0.02em;
  }

  .form-select {
    border-radius: 0.5em;
    box-shadow: 0 2px 8px #5740b433;
  }

  .badge-original {
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    color: white;
    font-weight: 600;
  }

  .badge-substitute {
    background: linear-gradient(90deg, #e74c3c 0%, #f39c12 100%);
    color: white;
    font-weight: 600;
  }

  .history-stat {
    padding: 1rem;
    border-radius: 0.8rem;
    text-align: center;
    background: linear-gradient(45deg, #f8f9ff 0%, #fff 100%);
    border: 2px solid #e9f0ff;
  }

  .history-stat .stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #5740b4;
  }

  .history-stat .stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.1em;
  }

  .pagination {
    justify-content: center;
  }

  .pagination .page-link {
    color: #5740b4;
    border-color: #5740b4;
  }

  .pagination .page-item.active .page-link {
    background: linear-gradient(90deg, #5740b4 0%, #8931f6 100%);
    border-color: #5740b4;
  }

  .statistics-section {
    display: flex !important;
    flex-wrap: wrap;
  }
</style>

<div class="container-fluid py-4">
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 custom-navbar">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="{{ asset('admin/images/logo.png') }}" alt="Logo" style="max-height: 50px;" class="me-2">
        <span class="fw-bold text-white text-capitalize">Substitution History</span>
      </a>
      <div class="d-flex">
        <div class="d-flex">
          @if(StaticController::fetchUserRole() == 'dept-admin-erp')
          <a href="{{ route('department.dashboard') }}" class="btn btn-light btn-sm fw-bold ms-auto" style="box-shadow:0 2px 8px #5740b433;">
            <i class="fa fa-step-backward me-1"></i> back
          </a>
          @endif
        </div>
      </div>
    </div>
  </nav>

  <!-- Filter Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card custom-card">
        <div class="card-body filter-card">
          <h5 class="card-title text-dark mb-3">
            <i class="fas fa-filter me-2"></i>Filter Substitution History
          </h5>
          <form id="historyFilterForm">
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <label class="form-label">
                  <i class="fas fa-users me-1"></i>Batch
                </label>
                <select name="batch_id" class="form-select" id="batchFilter">
                  <option value="">All Batches</option>
                  @foreach ($batches as $batch)
                  <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">
                  <i class="fas fa-user me-1"></i>Faculty
                </label>
                <select name="faculty_id" class="form-select dselect-example" id="facultyFilter">
                  <option value="">All Faculty</option>
                  @foreach ($faculties as $faculty)
                  <option value="{{ $faculty->id }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">
                  <i class="fas fa-calendar me-1"></i>Start Date
                </label>
                <input type="date" name="start_date" class="form-control" id="startDate">
              </div>
              <div class="col-md-3">
                <label class="form-label">
                  <i class="fas fa-calendar me-1"></i>End Date
                </label>
                <input type="date" name="end_date" class="form-control" id="endDate">
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-12">
                <button type="button" class="btn btn-primary me-2" id="applyFiltersBtn">
                  <i class="fa fa-search me-1"></i>Apply Filters
                </button>
                <button type="button" class="btn btn-secondary me-2" id="clearFiltersBtn">
                  <i class="fa fa-undo me-1"></i>Clear Filters
                </button>
                <button type="button" class="btn btn-success" id="exportBtn">
                  <i class="fa fa-download me-1"></i>Export to Excel
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Section -->
  <div class="row mb-4 statistics-section">
    <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
      <div class="history-stat">
        <div class="stat-number" id="totalSubstitutions">0</div>
        <div class="stat-label">Total Substitutions</div>
      </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
      <div class="history-stat">
        <div class="stat-number" id="thisMonthSubstitutions">0</div>
        <div class="stat-label">This Month</div>
      </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
      <div class="history-stat">
        <div class="stat-number" id="uniqueFaculties">0</div>
        <div class="stat-label">Unique Faculty</div>
      </div>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 col-6 mb-3">
      <div class="history-stat">
        <div class="stat-number" id="averagePerMonth">0</div>
        <div class="stat-label">Avg per Month</div>
      </div>
    </div>
  </div>


  <!-- History Results -->
  <div class="row">
    <div class="col-12">
      <div class="card custom-card">
        <div class="card-body">
          <h5 class="card-title text-dark mb-3">
            <i class="fas fa-history me-2"></i>Substitution Records
            <small class="text-muted ms-2" id="resultsCount"></small>
          </h5>
          <div id="historyResults">
            <div class="alert alert-info text-center">
              <i class="fas fa-info-circle me-2"></i>
              Use the filters above to search substitution history
            </div>
          </div>
          <div id="historyPagination"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let currentPage = 1;
  let totalPages = 1;

  document.addEventListener('DOMContentLoaded', function() {
    // Apply filters button
    document.getElementById('applyFiltersBtn').addEventListener('click', function() {
      currentPage = 1;
      loadSubstitutionHistory();
    });

    // Clear filters button
    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
      document.getElementById('historyFilterForm').reset();
      document.getElementById('resultsCount').textContent = '';
      document.querySelector('.statistics-section').style.display = 'none';
      document.getElementById('historyResults').innerHTML = `
        <div class="alert alert-info text-center">
          <i class="fas fa-info-circle me-2"></i>
          Use the filters above to search substitution history
        </div>
      `;
      document.getElementById('historyPagination').innerHTML = '';
    });

    // Export button
    document.getElementById('exportBtn').addEventListener('click', function() {
      exportToExcel();
    });

    // Load initial data from last 30 days
    const endDate = new Date().toISOString().split('T')[0];
    const startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    document.getElementById('startDate').value = startDate;
    document.getElementById('endDate').value = endDate;
    loadSubstitutionHistory();
  });

  function loadSubstitutionHistory() {
    const formData = new FormData(document.getElementById('historyFilterForm'));
    const params = new URLSearchParams();

    formData.forEach((value, key) => {
      if (value) params.append(key, value);
    });

    params.append('limit', '20');
    params.append('page', currentPage);

    const historyUrl = `{{ route("department.substitution.history") }}?${params.toString()}`;

    // Show loading state
    const applyBtn = document.getElementById('applyFiltersBtn');
    const originalText = applyBtn.innerHTML;
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Loading...';

    fetch(historyUrl, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displaySubstitutionHistory(data.data, data.pagination);
          updateStatistics(data.data, data.pagination);
        } else {
          alert('Failed to load substitution history: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error loading history:', error);
        alert('Error loading substitution history');
      })
      .finally(() => {
        applyBtn.disabled = false;
        applyBtn.innerHTML = originalText;
      });
  }

  function displaySubstitutionHistory(historyData, pagination) {
    const historyResults = document.getElementById('historyResults');
    const resultsCount = document.getElementById('resultsCount');

    resultsCount.textContent = `(${pagination.total} records found)`;
    totalPages = pagination.last_page;

    if (historyData.length === 0) {
      historyResults.innerHTML = `
        <div class="alert alert-warning text-center">
          <i class="fas fa-exclamation-triangle me-2"></i>
          No substitution records found for the selected criteria
        </div>
      `;
      document.getElementById('historyPagination').innerHTML = '';
      return;
    }

    let tableHtml = `
      <div class="table-responsive">
        <table class="table table-striped custom-table" >
          <thead>
            <tr>
              <th>Date</th>
              <th>Day</th>
              <th>Hour</th>
              <th>Subject/Course</th>
              <th>Semester</th>
              <th>Original Teacher</th>
              <th>Substitute Teacher</th>
              <th>Reason</th>
              <th>Created By</th>
            </tr>
          </thead>
          <tbody>
    `;

    historyData.forEach(history => {
      tableHtml += `
        <tr>
          <td>
            <strong>${new Date(history.substitution_date).toLocaleDateString()}</strong>
          </td>
          <td><span class="badge bg-info">${history.day_of_week}</span></td>
          <td><span class="badge bg-primary">Hour ${history.hour_number}</span></td>
          <td>
            <strong>${history.subject_title}</strong><br>
            <small class="text-muted">${history.course_title}</small>
          </td>
          <td><span class="badge bg-secondary">${history.semester_title}</span></td>
          <td>
            <span class="badge badge-original">${history.original_faculty.name}</span><br>
            <small class="text-muted">${history.original_faculty.code}</small>
          </td>
          <td>
            <span class="badge badge-substitute">${history.substitute_faculty.name}</span><br>
            <small class="text-muted">${history.substitute_faculty.code}</small>
          </td>
          <td>${history.reason || '<em class="text-muted">No reason specified</em>'}</td>
          <td>
            ${history.created_by}<br>
            <small class="text-muted">${new Date(history.created_at).toLocaleDateString()}</small>
          </td>
        </tr>
      `;
    });

    tableHtml += `
          </tbody>
        </table>
      </div>
    `;

    historyResults.innerHTML = tableHtml;
    updatePagination(pagination);
  }

  function updatePagination(pagination) {
    const paginationDiv = document.getElementById('historyPagination');

    if (pagination.last_page <= 1) {
      paginationDiv.innerHTML = '';
      return;
    }

    let paginationHtml = '<nav><ul class="pagination">';

    // Previous button
    if (pagination.current_page > 1) {
      paginationHtml += `
        <li class="page-item">
          <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">Previous</a>
        </li>
      `;
    }

    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
      const activeClass = i === pagination.current_page ? 'active' : '';
      paginationHtml += `
        <li class="page-item ${activeClass}">
          <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>
      `;
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
      paginationHtml += `
        <li class="page-item">
          <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">Next</a>
        </li>
      `;
    }

    paginationHtml += '</ul></nav>';
    paginationDiv.innerHTML = paginationHtml;
  }

  function changePage(page) {
    currentPage = page;
    loadSubstitutionHistory();
  }

  function updateStatistics(historyData, pagination) {
    const statisticsSection = document.querySelector('.statistics-section');

    // Calculate statistics
    const totalSubs = pagination.total;
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();

    const thisMonthSubs = historyData.filter(sub => {
      const subDate = new Date(sub.substitution_date);
      return subDate.getMonth() === currentMonth && subDate.getFullYear() === currentYear;
    }).length;

    const uniqueFacultiesSet = new Set();
    historyData.forEach(sub => {
      uniqueFacultiesSet.add(sub.substitute_faculty.id);
    });

    const avgPerMonth = totalSubs > 0 ? Math.round(totalSubs / 12) : 0;

    // Update DOM
    document.getElementById('totalSubstitutions').textContent = totalSubs;
    document.getElementById('thisMonthSubstitutions').textContent = thisMonthSubs;
    document.getElementById('uniqueFaculties').textContent = uniqueFacultiesSet.size;
    document.getElementById('averagePerMonth').textContent = avgPerMonth;

    statisticsSection.style.display = 'block';
  }

  function exportToExcel() {
    // Gather filter values
    const formData = new FormData(document.getElementById('historyFilterForm'));
    const params = new URLSearchParams();
    formData.forEach((value, key) => {
      if (value) params.append(key, value);
    });
    // Build export URL
    const exportUrl = `{{ route('department.substitution.history.export') }}?${params.toString()}`;
    // Trigger file download
    window.open(exportUrl, '_blank');
  }
</script>

@include('includes.footer')
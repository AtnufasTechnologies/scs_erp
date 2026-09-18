@include('includes.header')
@include('admin.accounts.sidebar')

<style>
  :root {
    --salary-bg: #fff;
    --salary-surface: #ffffff;
    --salary-border: #e5e7eb;
    --salary-text: #0f172a;
    --salary-muted: #6b7280;
    --salary-accent: #0f766e;
    --salary-accent-soft: #ccfbf1;
  }



  .metric-card {
    border: 1px solid var(--salary-border);
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
  }

  .metric-label {
    font-size: 0.73rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--salary-muted);
  }

  .metric-value {
    margin-top: 2px;
    color: var(--salary-text);
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1.1;
  }

  .toolbar-shell {
    border: 1px solid var(--salary-border);
    border-radius: 14px;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
  }

  .toolbar-shell .form-control {
    border-radius: 10px;
    border: 1px solid #d1d5db;
  }

  .toolbar-shell .btn {
    border-radius: 10px;
    font-weight: 600;
  }

  .salary-card {
    background: var(--salary-surface);
    border: 1px solid var(--salary-border);
    border-radius: 16px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .salary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
  }

  .staff-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0f766e, #0ea5a5);
    color: #fff;
    font-size: 0.92rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .staff-title {
    color: var(--salary-text);
    font-weight: 700;
  }

  .staff-meta {
    color: var(--salary-muted);
    font-size: 0.82rem;
  }

  .matrix-chip {
    display: inline-block;
    border-radius: 999px;
    background: var(--salary-accent-soft);
    color: var(--salary-accent);
    font-size: 0.73rem;
    font-weight: 700;
    padding: 4px 10px;
  }

  .salary-label {
    color: var(--salary-muted);
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .salary-value {
    color: #0b1324;
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.1;
  }
</style>

<div class="page-wrapper">
  <div class="page-content">
    <div class="salary-layout">
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Staff Salary List</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active">Salary Masters</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="card-body">
              <small class="metric-label">Total Active Employees</small>
              <h3 class="metric-value">{{ $analytics['total_active_employees'] ?? 0 }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="card-body">
              <small class="metric-label">Pay Matrix Added</small>
              <h3 class="metric-value">{{ $analytics['pay_matrix_added'] ?? 0 }}</h3>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="card-body">
              <small class="metric-label">Pay Matrix Not Added (HR Responsible)</small>
              <h3 class="metric-value">{{ $analytics['pay_matrix_not_added'] ?? 0 }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-3 toolbar-shell">
        <div class="card-body">
          <div class="row g-2 align-items-center">
            <div class="col-md-7">
              <input type="text" id="salaryCardSearch" class="form-control" placeholder="Search by code, name, designation, matrix...">
            </div>
            <div class="col-md-5 text-md-end">
              <a href="{{ route('admin.payroll.employees-list') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> View All Employees Details
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="row" id="salaryCardContainer">
        @forelse($salaryMasters as $master)
        <div class="col-md-6 col-xl-4 mb-3 salary-card-item" data-search="{{ strtolower(trim(($master->faculty->USER_CODE ?? '') . ' ' . ($master->faculty->FIRST_NAME ?? '') . ' ' . ($master->faculty->LAST_NAME ?? '') . ' ' . ($master->faculty->designation ?? '') . ' ' . ($master->faculty->employee_type ?? '') . ' ' . ($master->payMatrix->matrix_code ?? '') . ' ' . ($master->payMatrix->full_designation ?? ''))) }}">
          <div class="card h-100 salary-card">
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-start gap-3 mb-3">
                <div class="staff-avatar">
                  {{ strtoupper(substr($master->faculty->FIRST_NAME ?? 'S', 0, 1)) }}{{ strtoupper(substr($master->faculty->LAST_NAME ?? 'T', 0, 1)) }}
                </div>
                <div>
                  <h6 class="mb-1 staff-title">{{ $master->faculty->FIRST_NAME ?? '' }} {{ $master->faculty->LAST_NAME ?? '' }}</h6>
                  <small class="staff-meta d-block">{{ $master->faculty->USER_CODE ?? '-' }}</small>
                  <small class="staff-meta d-block">{{ $master->faculty->designation ?? 'Designation N/A' }}</small>
                </div>
              </div>

              <div class="mb-2">
                <small class="salary-label d-block mb-1">Applicable Pay Matrix</small>
                @if($master->payMatrix)
                <span class="matrix-chip">{{ $master->payMatrix->matrix_code }}</span>
                <small class="staff-meta d-block mt-1">{{ $master->payMatrix->full_designation }}</small>
                @else
                <small class="staff-meta">N/A</small>
                @endif
              </div>

              <div class="mt-auto d-flex justify-content-between align-items-end pt-3 border-top">
                <div>
                  <small class="salary-label d-block">Salary</small>
                  <strong class="salary-value">₹{{ number_format($master->net_salary, 2) }}</strong>
                </div>
                <small class="staff-meta">{{ $master->faculty->employee_type ?? 'Type N/A' }}</small>

              </div>
            </div>
          </div>
        </div>


        @empty
        <div class="col-12 text-center py-4">
          <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">No HR pay-matrix salary masters found.</p>
        </div>
        @endforelse
      </div>

      @if($salaryMasters->hasPages())
      <div class="card toolbar-shell">
        <div class="card-body">
          {{ $salaryMasters->links('vendor.pagination.bootstrap-5') }}
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('salaryCardSearch');
    const cards = document.querySelectorAll('.salary-card-item');

    if (!searchInput) {
      return;
    }

    searchInput.addEventListener('input', function() {
      const term = this.value.toLowerCase().trim();

      cards.forEach(function(card) {
        const searchable = (card.getAttribute('data-search') || '').toLowerCase();
        card.style.display = searchable.includes(term) ? '' : 'none';
      });
    });
  });
</script>

@include('includes.footer')
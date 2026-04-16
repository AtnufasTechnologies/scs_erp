@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Payroll Management</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active">Salary Slips</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Salary Slip</a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
          <i class="fas fa-magic me-1"></i>Bulk Generate
        </button>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-0 text-muted">Total Slips ({{ $year }})</p>
                <h4 class="mb-0">{{ $stats['total_slips'] }}</h4>
              </div>
              <div class="text-primary"><i class="fas fa-file-invoice fa-2x"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-0 text-muted">Paid</p>
                <h4 class="mb-0 text-success">{{ $stats['paid'] }}</h4>
              </div>
              <div class="text-success"><i class="fas fa-check-circle fa-2x"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-0 text-muted">Draft</p>
                <h4 class="mb-0 text-warning">{{ $stats['draft'] }}</h4>
              </div>
              <div class="text-warning"><i class="fas fa-clock fa-2x"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <p class="mb-0 text-muted">Total Amount</p>
                <h4 class="mb-0">₹{{ number_format($stats['total_amount'], 0) }}</h4>
              </div>
              <div class="text-info"><i class="fas fa-rupee-sign fa-2x"></i></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-2">
            <select name="year" class="form-select form-select-sm">
              <option value="">All Years</option>
              @foreach($availableYears as $availableYear)
              <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="month" class="form-select form-select-sm">
              <option value="">All Months</option>
              @for($m = 1; $m <= 12; $m++)
                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
                @endfor
            </select>
          </div>
          <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
              <option value="">All Status</option>
              <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
              <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
          </div>
          <div class="col-md-3">
            <select name="faculty_id" class="form-select form-select-sm">
              <option value="">All Faculty</option>
              @foreach($faculties as $faculty)
              <option value="{{ $faculty->id }}" {{ $facultyId == $faculty->id ? 'selected' : '' }}>
                {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.payroll.index') }}" class="btn btn-sm btn-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Salary Slips Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Slip Number</th>
                <th>Faculty</th>
                <th>Month/Year</th>
                <th>Gross</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($salarySlips as $slip)
              <tr>
                <td><small class="text-primary fw-bold">{{ $slip->salary_slip_number }}</small></td>
                <td>{{ $slip->faculty->FIRST_NAME ?? '' }} {{ $slip->faculty->LAST_NAME ?? '' }}</td>
                <td>{{ $slip->month_year }}</td>
                <td>₹{{ number_format($slip->gross_salary, 0) }}</td>
                <td>₹{{ number_format($slip->total_deductions, 0) }}</td>
                <td><strong>₹{{ number_format($slip->net_salary, 0) }}</strong></td>
                <td><span class="badge bg-{{ $slip->status_badge }}">{{ ucfirst($slip->status) }}</span></td>
                <td>
                  <a href="{{ route('admin.payroll.show', $slip->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                  @if($slip->status !== 'paid')
                  <a href="{{ route('admin.payroll.edit', $slip->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('admin.payroll.destroy', $slip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary slip?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                  </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted">No salary slips found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $salarySlips->appends(request()->query())->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Bulk Generate Modal -->
<div class="modal fade" id="bulkGenerateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.payroll.bulk-generate') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Bulk Generate Salary Slips</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Month*</label>
            <select name="month" class="form-select" required>
              @for($m = 1; $m <= 12; $m++)
                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $m == date('n') ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
                @endfor
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Year*</label>
            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="auto_approve" value="1" id="autoApproveCheck">
              <label class="form-check-label" for="autoApproveCheck">
                <strong>Auto-approve all generated slips</strong>
              </label>
              <div><small class="text-muted">Check this to automatically approve all salary slips after generation</small></div>
            </div>
          </div>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>Fast Generation:</strong> This will generate salary slips using faculty salary masters. All active loans will be automatically included. Perfect for 250+ faculty!
          </div>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Only faculty with active salary masters will be processed. Set up salary masters first.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Generate</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
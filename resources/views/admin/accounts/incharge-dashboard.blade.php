@include('includes.header')
@include('admin.accounts.sidebar')

<h3 class="mb-4"><i class="fa fa-calculator text-primary me-2"></i>Account Office Dashboard</h3>


{{-- ===== STATS ROW ===== --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-primary">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">Total Fee Collected</p>
            <h4 class="my-1 text-primary">₹ {{ number_format($totalStudentFeeCollected, 2) }}</h4>
            <p class="mb-0 text-secondary small">All time student fees</p>
          </div>
          <div class="ms-auto widget-icon bg-primary text-white">
            <i class="fas fa-rupee-sign"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-success">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">Today's Collection</p>
            <h4 class="my-1 text-success">₹ {{ number_format($todayCollection, 2) }}</h4>
            <p class="mb-0 text-secondary small">{{ now()->format('d M Y') }}</p>
          </div>
          <div class="ms-auto widget-icon bg-success text-white">
            <i class="fas fa-calendar-day"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-warning">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">Admission Fees Collected</p>
            <h4 class="my-1 text-warning">₹ {{ number_format($totalAdmissionFeeCollected, 2) }}</h4>
            <p class="mb-0 text-secondary small">All time admission fees</p>
          </div>
          <div class="ms-auto widget-icon bg-warning text-white">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card radius-10 border-start border-4 border-info">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div>
            <p class="mb-0 text-secondary">Active Faculty</p>
            <h4 class="my-1 text-info">{{ $totalFaculty }}</h4>
            <p class="mb-0 text-secondary small">Currently serving</p>
          </div>
          <div class="ms-auto widget-icon bg-info text-white">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



{{-- ===== RECENT TRANSACTIONS + ADMISSION PAYMENTS ===== --}}
<div class="row g-3">

  {{-- Recent Student Fee Payments --}}
  <div class="col-xl-7">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Recent Student Fee Payments</h6>
        <a href="{{ url('erp/admin/accounts/all-payments') }}" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Invoice</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentTransactions as $idx => $txn)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $txn->studentmaster->first_name ?? '' }} {{ $txn->studentmaster->last_name ?? '' }}</td>
                <td><strong>₹ {{ number_format($txn->amount, 2) }}</strong></td>
                <td>{{ $txn->transaction_date }}</td>
                <td><small class="text-muted">{{ $txn->invoice_id }}</small></td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-3">No transactions found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Admission Application Payments --}}
  <div class="col-xl-5">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Recent Admission Payments</h6>
        <a href="{{ url('erp/admin/accounts/admission-application-fee') }}" class="btn btn-sm btn-outline-warning">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Applicant</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentAdmissionPayments as $idx => $pay)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                  {{ $pay->applicationmaster->registrationmaster->first_name ?? '' }}
                  {{ $pay->applicationmaster->registrationmaster->last_name ?? '' }}
                </td>
                <td><strong>₹ {{ number_format($pay->amount, 2) }}</strong></td>
                <td>{{ date('d-M-Y', strtotime($pay->created_at)) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-3">No records found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>


<div class="row mt-3">
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center">
        <h5 class="text-primary"><i class="fas fa-users"></i> Total Assistants</h5>
        <h2>{{ $totalAssistants }}</h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center">
        <h5 class="text-success"><i class="fas fa-user-check"></i> Active</h5>
        <h2>{{ $activeAssistants }}</h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-0">
      <div class="card-body text-center">
        <h5 class="text-danger"><i class="fas fa-user-times"></i> Inactive</h5>
        <h2>{{ $inactiveAssistants }}</h2>
      </div>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fas fa-key"></i> Quick Actions</h6>
      </div>
      <div class="card-body">
        <a href="{{ route('account-office.assistant-access') }}" class="btn btn-outline-primary btn-block mb-2 w-100">
          <i class="fas fa-user-plus"></i> Manage Assistant Access
        </a>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="fas fa-list"></i> Account Modules</h6>
      </div>
      <div class="card-body">
        @foreach($accountModules as $mod)
        <span class="badge bg-secondary mb-1">{{ $mod->menu_name }}</span>
        @endforeach
        @if($accountModules->isEmpty())
        <p class="text-muted">No account modules configured yet.</p>
        @endif
      </div>
    </div>
  </div>
</div>

@if($assistants->count())
<div class="card shadow-sm mt-4">
  <div class="card-header">
    <h6 class="mb-0">Account Office Assistants</h6>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-hover" id="exportTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        @foreach($assistants as $key => $asst)
        <tr>
          <td>{{ $key + 1 }}</td>
          <td>{{ $asst->name }}</td>
          <td>{{ $asst->email }}</td>
          <td>
            @if($asst->status == 'ACTIVE')
            <span class="badge bg-success">Active</span>
            @else
            <span class="badge bg-danger">Inactive</span>
            @endif
          </td>
          <td>{{ date('d-M-Y', strtotime($asst->created_at)) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@include('includes.footer')
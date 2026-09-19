@include('includes.header')
@include('admin.accounts.sidebar')

<style>
  .accounts-dashboard {
    --dash-bg: linear-gradient(135deg, #fff 0%, #fff 100%);
    --dash-border: #fff;
    --dash-title: #1f3552;
    --dash-muted: #64748b;
    --dash-accent: #1f4d7a;
    --dash-accent-soft: #e8f0f8;
  }

  .accounts-dashboard .dash-shell {
    background: var(--dash-bg);
    border: 1px solid var(--dash-border);
    border-radius: 14px;
  }

  .accounts-dashboard .dash-heading {
    color: var(--dash-title);
    letter-spacing: .15px;
    font-size: 1.45rem;
    font-weight: 700;
  }

  .accounts-dashboard .dash-subtitle {
    font-size: .85rem;
    color: #6b7b90;
  }

  .accounts-dashboard .kpi-card {
    border: 1px solid #dfe8f2;
    border-radius: 14px;
    box-shadow: 0 10px 20px rgba(15, 35, 60, .06);
    height: 100%;
    position: relative;
    overflow: hidden;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  }

  .accounts-dashboard .kpi-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, #1f4d7a 0%, #6f92b5 100%);
  }

  .accounts-dashboard .kpi-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    background: var(--dash-accent-soft);
    color: var(--dash-accent);
    border: 1px solid #c8d9ea;
  }

  .accounts-dashboard .kpi-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #102c4a;
    line-height: 1.2;
    letter-spacing: .2px;
  }

  .accounts-dashboard .kpi-label {
    color: #35506c;
    font-weight: 600;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .35px;
  }

  .accounts-dashboard .kpi-sub {
    color: var(--dash-muted);
    font-size: .73rem;
    margin-top: .2rem;
  }

  .accounts-dashboard .kpi-amount-wrap {
    padding-left: .1rem;
  }

  .accounts-dashboard .panel-card {
    border: 1px solid #fff;
    border-radius: 12px;
    box-shadow: 0 8px 18px rgba(15, 34, 58, .06);
  }

  .accounts-dashboard .panel-card .card-header {
    background: #83e7f4;
    border-bottom: 1px solid #fff;
  }

  .accounts-dashboard .panel-card .card-header h6 {
    font-size: .93rem;
    font-weight: 600;
    color: #1f3552;
  }

  .accounts-dashboard .compact-table th {
    white-space: nowrap;
    font-size: .74rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .3px;
    padding-top: .55rem;
    padding-bottom: .55rem;
  }

  .accounts-dashboard .compact-table td {
    padding-top: .5rem;
    padding-bottom: .5rem;
    font-size: .86rem;
  }

  .accounts-dashboard .module-chip {
    background: #eef4fb;
    color: #2a4365;
    border: 1px solid #d5e3f3;
    border-radius: 999px;
    padding: .3rem .65rem;
    display: inline-block;
    margin: 0 .35rem .45rem 0;
    font-size: .78rem;
    font-weight: 600;
  }

  .accounts-dashboard .assistant-mini-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    padding: .85rem;
    text-align: center;
  }

  .accounts-dashboard .assistant-mini-card .value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f2742;
    line-height: 1.2;
  }

  .accounts-dashboard .btn-accent-outline {
    border-color: #c6d7ea;
    color: var(--dash-accent);
  }

  .accounts-dashboard .btn-accent-outline:hover {
    background: var(--dash-accent);
    border-color: var(--dash-accent);
    color: #fff;
  }
</style>

<div class="accounts-dashboard">
  <div class="dash-shell p-3 p-lg-4 mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
      <div>
        <h3 class="dash-heading mb-1"><i class="fa fa-calculator text-primary me-2"></i>Account Office Dashboard</h3>
        <p class="mb-0 dash-subtitle">Financial overview, transactions and team operations.</p>
      </div>
      <span class="badge badge-warning border px-3 py-2">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</span>
    </div>

    <hr class="my-3">

    <form method="GET" action="{{ route('account-office.dashboard') }}" class="row g-2 align-items-end">
      <div class="col-md-6 col-lg-4">
        <label class="form-label mb-1 fw-semibold">Financial Year</label>
        <select name="financial_year_id" class="form-select">
          @foreach(($financialYears ?? collect()) as $financialYear)
          <option value="{{ $financialYear->id }}" {{ (int) ($selectedFinancialYearId ?? 0) === (int) $financialYear->id ? 'selected' : '' }}>
            {{ $financialYear->title }}{{ $financialYear->is_active ? ' (Active)' : '' }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 col-lg-2 d-grid">
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i>Apply Filter</button>
      </div>
      <div class="col-md-3 col-lg-2 d-grid">
        <a href="{{ route('account-office.dashboard') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <p class="kpi-label mb-0">Today's Collection</p>
            <span class="kpi-icon"><i class="fa fa-calendar-day"></i></span>
          </div>
          <div class="kpi-amount-wrap">
            <div class="kpi-value">₹ {{ number_format($todayCollection, 2) }}</div>
            <p class="kpi-sub mb-0">{{ now()->format('d M Y') }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <p class="kpi-label mb-0">Fee Collection</p>
            <span class="kpi-icon"><i class="fa fa-wallet"></i></span>
          </div>
          <div class="kpi-amount-wrap">
            <div class="kpi-value">₹ {{ number_format($totalStudentFeeCollected, 2) }}</div>
            <p class="kpi-sub mb-0">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</p>
          </div>
        </div>
      </div>
    </div>



    <div class="col-sm-6 col-xl">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <p class="kpi-label mb-0">Admission Fees</p>
            <span class="kpi-icon"><i class="fa fa-id-card"></i></span>
          </div>
          <div class="kpi-amount-wrap">
            <div class="kpi-value">₹ {{ number_format($totalAdmissionFeeCollected, 2) }}</div>
            <p class="kpi-sub mb-0">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <p class="kpi-label mb-0">Late Fines Collected</p>
            <span class="kpi-icon"><i class="fa fa-hourglass-half"></i></span>
          </div>
          <div class="kpi-amount-wrap">
            <div class="kpi-value">₹ {{ number_format($totalLateFineCollected ?? 0, 2) }}</div>
            <p class="kpi-sub mb-0">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl">
      <div class="card kpi-card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <p class="kpi-label mb-0">Total Revenue</p>
            <span class="kpi-icon"><i class="fa fa-chart-line"></i></span>
          </div>
          <div class="kpi-amount-wrap">
            <div class="kpi-value">₹ {{ number_format($totalStudentFeeCollected + $totalAdmissionFeeCollected + ($totalLateFineCollected ?? 0), 2) }}</div>
            <p class="kpi-sub mb-0">Fees + Admissions + Late Fines</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-xl-7">
      <div class="card panel-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0">Recent Student Fee Payments</h6>
            <small class="text-muted">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</small>
          </div>
          <a href="{{ url('erp/admin/accounts/all-payments') }}" class="btn btn-sm btn-accent-outline">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 compact-table align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Roll No</th>
                  <th>Student</th>
                  <th class="text-end">Amount</th>
                  <th>Date</th>
                  <th>Invoice</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentTransactions as $idx => $txn)
                <tr>
                  <td>{{ $idx + 1 }}</td>
                  <td><span class="text-uppercase">{{ $txn->studentmaster->roll_no ?? '' }}</span></td>
                  <td>{{ $txn->studentmaster->first_name ?? '' }} {{ $txn->studentmaster->last_name ?? '' }}</td>
                  <td class="text-end fw-semibold">₹ {{ number_format($txn->amount, 2) }}</td>
                  <td>{{ $txn->transaction_date }}</td>
                  <td><small class="text-muted">{{ $txn->invoice_id }}</small></td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">No transactions found for this financial year.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-5">
      <div class="card panel-card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0">Recent Admission Payments</h6>
            <small class="text-muted">{{ $activeFinancialYearLabel ?? 'Current active financial year' }}</small>
          </div>
          <a href="{{ url('erp/admin/accounts/admission-application-fee') }}" class="btn btn-sm btn-accent-outline">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 compact-table align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Campus</th>
                  <th>Applicant</th>
                  <th class="text-end">Amount</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentAdmissionPayments as $idx => $pay)
                <tr>
                  <td>{{ $idx + 1 }}</td>
                  <td>{{ $pay->applicationmaster->registrationmaster->campus_id == 1 ? 'Sonada' : 'Siliguri' }}</td>
                  <td>
                    {{ $pay->applicationmaster->registrationmaster->first_name ?? '' }}
                    {{ $pay->applicationmaster->registrationmaster->last_name ?? '' }}
                  </td>
                  <td class="text-end fw-semibold">₹ {{ number_format($pay->amount, 2) }}</td>
                  <td>{{ date('d-M-Y', strtotime($pay->created_at)) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No records found for this financial year.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-12">
      <div class="card panel-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Account Office Assistants</h6>
          <a href="{{ route('account-office.assistant-access') }}" class="btn btn-sm btn-accent-outline">
            <i class="fas fa-user-cog me-1"></i>Manage Access
          </a>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <div class="assistant-mini-card">
                <div class="fw-semibold" style="color:#1f4d7a;">Total Assistants</div>
                <div class="value">{{ $totalAssistants }}</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="assistant-mini-card">
                <div class="fw-semibold" style="color:#1f4d7a;">Active</div>
                <div class="value">{{ $activeAssistants }}</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="assistant-mini-card">
                <div class="fw-semibold" style="color:#1f4d7a;">Inactive</div>
                <div class="value">{{ $inactiveAssistants }}</div>
              </div>
            </div>
          </div>

          @if($assistants->count())
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0 compact-table" id="exportTable">
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
          @else
          <p class="text-muted mb-0">No assistants found.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
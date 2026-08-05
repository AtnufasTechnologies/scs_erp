<?php

use App\Http\Controllers\StaticController;

$userRoleType = StaticController::fetchUserRole();
?>
@include('includes.header')

<div class="wrapper">
  @if($userRoleType == 'principal' || $userRoleType == 'vice-principal' || $userRoleType == 'bursar')
  @include('principal.sidebar')
  @else
  @include('event-coordinator.sidebar')
  @endif

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Report</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.events.index') }}">Events</a></li>
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.events.show', $event) }}">{{ Str::limit($event->title, 30) }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Full Report</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4" id="reportContent">

      <!-- Header -->
      <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#6f42c1,#e83e8c);">
        <div class="card-body p-4 text-white">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
              <h3 class="fw-bold text-white mb-1">{{ $event->title }}</h3>
              <p class="mb-1 text-white-75">
                <i class="fas fa-map-marker-alt me-1"></i>{{ $event->venue ?? 'Venue TBD' }}
                &nbsp;·&nbsp;
                <i class="fas fa-calendar me-1"></i>{{ $event->start_date->format('d M Y') }} – {{ $event->end_date->format('d M Y') }}
              </p>
              <p class="mb-0 text-white-75 small">
                Organised by: {{ $event->creator->name ?? 'N/A' }}
                &nbsp;·&nbsp; Status: <strong>{{ ucfirst($event->status) }}</strong>
              </p>
            </div>

          </div>
        </div>
      </div>

      <!-- Financial Summary -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Budget Allocated</div>
            <div class="fw-bold fs-5">₹{{ number_format($event->total_budget, 2) }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Total Income</div>
            <div class="fw-bold fs-5 text-success">₹{{ number_format($totalIncome, 2) }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Total Expenses</div>
            <div class="fw-bold fs-5 text-danger">₹{{ number_format($totalExpense, 2) }}</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center py-3">
            <div class="text-muted small mb-1">Net Balance</div>
            <div class="fw-bold fs-5 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
              ₹{{ number_format(abs($balance), 2) }} {{ $balance < 0 ? '(deficit)' : '' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Programs -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
          <i class="fas fa-list-alt text-info me-2"></i>Programs ({{ $event->programs->count() }})
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Date</th>
                  <th>Venue</th>
                  <th>Reg. Fee</th>
                  <th>Max Participants</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($event->programs as $prog)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $prog->name }}</td>
                  <td>{{ $prog->program_date->format('d M Y') }}</td>
                  <td>{{ $prog->venue ?? '—' }}</td>
                  <td>{{ $prog->registration_fee > 0 ? '₹'.number_format($prog->registration_fee,2) : 'Free' }}</td>
                  <td>{{ $prog->max_participants ?: 'Unlimited' }}</td>
                  <td>{{ ucfirst($prog->status) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">No programs.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Faculty Duties -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
          <i class="fas fa-user-tie text-secondary me-2"></i>Faculty Duty Assignments ({{ $event->facultyDuties->count() }})
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Faculty</th>
                  <th>Program</th>
                  <th>Duty</th>
                  <th>Responsibility</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($event->facultyDuties as $duty)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}</td>
                  <td>{{ $duty->program->name ?? 'Event-wide' }}</td>
                  <td>{{ $duty->duty_title }}</td>
                  <td><small>{{ $duty->responsibility }}</small></td>
                  <td>{{ ucfirst($duty->status) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">No duties assigned.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Sponsors -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
          <i class="fas fa-handshake text-success me-2"></i>Sponsors ({{ $event->sponsors->count() }}) &mdash; Total Received: <span class="text-success">₹{{ number_format($totalSponsorship, 2) }}</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Tier</th>
                  <th>Contact</th>
                  <th>Pledged</th>
                  <th>Received</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($event->sponsors as $sp)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $sp->name }}</td>
                  <td>{{ ucfirst($sp->tier) }}</td>
                  <td>{{ $sp->contact_person ?? '—' }}</td>
                  <td>₹{{ number_format($sp->pledged_amount, 2) }}</td>
                  <td class="text-success fw-semibold">₹{{ number_format($sp->received_amount, 2) }}</td>
                  <td>{{ ucfirst($sp->status) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center text-muted">No sponsors.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Fund Transactions -->
      <div class="row g-4 mb-4">
        <!-- By Category: Expenses -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
              <i class="fas fa-arrow-up text-danger me-2"></i>Expenses by Category
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Category</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($expenseByCategory as $cat => $amount)
                  <tr>
                    <td>{{ ucfirst($cat) }}</td>
                    <td class="text-end text-danger fw-semibold">₹{{ number_format($amount, 2) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="2" class="text-center text-muted">No expenses.</td>
                  </tr>
                  @endforelse
                </tbody>
                @if($expenseByCategory->count())
                <tfoot class="table-light fw-bold">
                  <tr>
                    <td>Total</td>
                    <td class="text-end text-danger">₹{{ number_format($totalExpense, 2) }}</td>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>

        <!-- By Category: Income -->
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
              <i class="fas fa-arrow-down text-success me-2"></i>Income by Category
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Category</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($incomeByCategory as $cat => $amount)
                  <tr>
                    <td>{{ ucfirst($cat) }}</td>
                    <td class="text-end text-success fw-semibold">₹{{ number_format($amount, 2) }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="2" class="text-center text-muted">No income recorded.</td>
                  </tr>
                  @endforelse
                </tbody>
                @if($incomeByCategory->count())
                <tfoot class="table-light fw-bold">
                  <tr>
                    <td>Total</td>
                    <td class="text-end text-success">₹{{ number_format($totalIncome, 2) }}</td>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Full Transaction Ledger -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3">
          <i class="fas fa-book text-primary me-2"></i>Full Transaction Ledger
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Description</th>
                  <th>Mode</th>
                  <th>Receipt</th>
                  <th>Recorded By</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                @forelse($event->fundTransactions->sortBy('transaction_date') as $tx)
                <tr>
                  <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                  <td>
                    <span class="badge {{ $tx->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                      {{ ucfirst($tx->type) }}
                    </span>
                  </td>
                  <td>{{ $tx->category }}</td>
                  <td>{{ $tx->description }}</td>
                  <td>{{ $tx->payment_mode ?? '—' }}</td>
                  <td>{{ $tx->receipt_no ?? '—' }}</td>
                  <td>{{ $tx->recordedBy->name ?? '—' }}</td>
                  <td class="text-end fw-semibold {{ $tx->type === 'income' ? 'text-success' : 'text-danger' }}">
                    ₹{{ number_format($tx->amount, 2) }}
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">No transactions.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /container -->
  </main>
</div>

<style>
  @media print {

    .sidebar-wrapper,
    .page-breadcrumb,
    .no-print {
      display: none !important;
    }

    main {
      margin-left: 0 !important;
    }
  }
</style>

@include('includes.footer')
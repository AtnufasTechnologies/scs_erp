@include('includes.header')

<div class="wrapper">
  @include('event-coordinator.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Events</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.events.index') }}">Events</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($event->title, 40) }}</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="container-fluid mt-4">

      <!-- Event Header Card -->
      <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#6f42c1,#e83e8c);">
        <div class="card-body p-4 text-white">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
              <h4 class="fw-bold text-white mb-1">{{ $event->title }}</h4>
              <p class="mb-1 text-white-75">
                <i class="fas fa-map-marker-alt me-1"></i>{{ $event->venue ?? 'Venue TBD' }}
                &nbsp;·&nbsp;
                <i class="fas fa-calendar me-1"></i>{{ $event->start_date->format('d M Y') }}
                @if(!$event->start_date->eq($event->end_date))
                &rarr; {{ $event->end_date->format('d M Y') }}
                @endif
              </p>
              <span class="badge rounded-pill bg-white text-dark px-3">{{ ucfirst($event->status) }}</span>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('event-coordinator.events.edit', $event) }}" class="btn btn-light btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
              </a>
              <a href="{{ route('event-coordinator.report', $event) }}" class="btn btn-light btn-sm">
                <i class="fas fa-file-alt me-1"></i>Full Report
              </a>
            </div>
          </div>
          <!-- Budget Summary -->
          <div class="row mt-4 g-3">
            <div class="col-6 col-md-2">
              <div class="text-white-50 small">Budget Allocated</div>
              <div class="fw-bold fs-5">₹{{ number_format($event->total_budget, 0) }}</div>
            </div>
            <div class="col-6 col-md-2">
              <div class="text-white-50 small">Total Sponsorship</div>
              <div class="fw-bold fs-5">₹{{ number_format($totalSponsorship, 0) }}</div>
            </div>
            <div class="col-6 col-md-2">
              <div class="text-white-50 small">Total Expense</div>
              <div class="fw-bold fs-5">₹{{ number_format($totalExpense, 0) }}</div>
            </div>
            <div class="col-6 col-md-2">
              <div class="text-white-50 small">Total Income</div>
              <div class="fw-bold fs-5">₹{{ number_format($totalIncome, 0) }}</div>
            </div>
            <div class="col-6 col-md-2">
              <div class="text-white-50 small">Balance</div>
              <div class="fw-bold fs-5 {{ $balance >= 0 ? 'text-success-light' : 'text-warning' }}">
                ₹{{ number_format(abs($balance), 0) }} {{ $balance < 0 ? '(deficit)' : '' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <ul class="nav nav-tabs mb-3" id="eventTabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#tab-programs">
            <i class="fas fa-list-alt me-1"></i>Programs <span class="badge bg-info text-dark ms-1">{{ $programs->count() }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#tab-duties">
            <i class="fas fa-user-tie me-1"></i>Faculty Duties <span class="badge bg-secondary ms-1">{{ $event->facultyDuties->count() }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#tab-fund">
            <i class="fas fa-rupee-sign me-1"></i>Fund Management <span class="badge bg-warning text-dark ms-1">{{ $event->fundTransactions->count() }}</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#tab-sponsors">
            <i class="fas fa-handshake me-1"></i>Sponsors 
            <span class="badge bg-success ms-1">{{ $event->sponsors->count() }}</span>
          </a>
        </li>
      </ul>

      <div class="tab-content">

        <!-- ===== PROGRAMS TAB ===== -->
        <div class="tab-pane fade show active" id="tab-programs">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
              <span><i class="fas fa-list-alt text-info me-2"></i>Programs / Sub-Events</span>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                <i class="fas fa-plus me-1"></i>Add Program
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Type</th>
                      <th>Scope</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Venue</th>
                      <th>Reg. Fee</th>
                      <th>Reg. Dates</th>
                      <th>Max</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($programs as $prog)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td class="fw-semibold">{{ $prog->name }}</td>
                      <td>
                        @if($prog->program_type === 'inter-college')
                        <span class="badge bg-primary">Inter-College</span>
                        @else
                        <span class="badge bg-secondary">Intra-College</span>
                        @endif
                      </td>
                      <td>
                        @if($prog->program_scope === 'international')
                        <span class="badge bg-info text-dark">International</span>
                        @else
                        <span class="badge bg-success">National</span>
                        @endif
                      </td>
                      <td>{{ $prog->program_date->format('d M Y') }}</td>
                      <td>
                        @if($prog->start_time)
                        {{ \Carbon\Carbon::parse($prog->start_time)->format('h:i A') }}
                        @if($prog->end_time) – {{ \Carbon\Carbon::parse($prog->end_time)->format('h:i A') }} @endif
                        @else —
                        @endif
                      </td>
                      <td>{{ $prog->venue ?? '—' }}</td>
                      <td>
                        @if($prog->registration_fee > 0)
                        <span class="badge bg-success">₹{{ number_format($prog->registration_fee, 0) }}</span>
                        @else
                        <span class="badge bg-secondary">Free</span>
                        @endif
                      </td>
                      <td>
                        @if($prog->registration_start_date)
                        <small>{{ $prog->registration_start_date->format('d M') }} – {{ $prog->registration_end_date?->format('d M') ?? '?' }}</small>
                        @else —
                        @endif
                      </td>
                      <td>{{ $prog->max_participants ?: '∞' }}</td>
                      <td>
                        <span class="badge rounded-pill
                          @if($prog->status==='upcoming') bg-info text-dark
                          @elseif($prog->status==='ongoing') bg-warning text-dark
                          @elseif($prog->status==='completed') bg-primary
                          @else bg-danger @endif">{{ ucfirst($prog->status) }}</span>
                      </td>
                      <td>
                        <a href="{{ route('event-coordinator.programs.edit', $prog) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('event-coordinator.programs.destroy', $prog) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Remove this program?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="12" class="text-center py-4 text-muted">No programs added yet.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== FACULTY DUTIES TAB ===== -->
        <div class="tab-pane fade" id="tab-duties">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
              <span><i class="fas fa-user-tie text-secondary me-2"></i>Faculty Duty Assignments</span>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDutyModal">
                <i class="fas fa-plus me-1"></i>Assign Duty
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Faculty</th>
                      <th>Program</th>
                      <th>Duty</th>
                      <th>Responsibility</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($event->facultyDuties as $duty)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td class="fw-semibold">
                        {{ $duty->faculty->FIRST_NAME ?? '' }} {{ $duty->faculty->LAST_NAME ?? '' }}
                      </td>
                      <td>{{ $duty->program->name ?? '<span class="text-muted">Event-wide</span>' }}</td>
                      <td>{{ $duty->duty_title }}</td>
                      <td><small>{{ Str::limit($duty->responsibility, 80) }}</small></td>
                      <td>
                        <span class="badge rounded-pill
                          @if($duty->status==='completed') bg-success
                          @elseif($duty->status==='acknowledged') bg-info text-dark
                          @else bg-secondary @endif">{{ ucfirst($duty->status) }}</span>
                      </td>
                      <td>
                        <form action="{{ route('event-coordinator.duties.destroy', $duty) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Remove this duty?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="7" class="text-center py-4 text-muted">No duties assigned yet.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== FUND MANAGEMENT TAB ===== -->
        <div class="tab-pane fade" id="tab-fund">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
              <span><i class="fas fa-rupee-sign text-warning me-2"></i>Fund Transactions</span>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFundModal">
                <i class="fas fa-plus me-1"></i>Add Transaction
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Date</th>
                      <th>Type</th>
                      <th>Category</th>
                      <th>Description</th>
                      <th>Amount</th>
                      <th>Mode</th>
                      <th>Receipt</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($event->fundTransactions->sortByDesc('transaction_date') as $tx)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                      <td>
                        <span class="badge {{ $tx->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                          {{ ucfirst($tx->type) }}
                        </span>
                      </td>
                      <td>{{ $tx->category }}</td>
                      <td>{{ $tx->description }}</td>
                      <td class="fw-semibold">₹{{ number_format($tx->amount, 2) }}</td>
                      <td>{{ $tx->payment_mode ?? '—' }}</td>
                      <td>{{ $tx->receipt_no ?? '—' }}</td>
                      <td>
                        <form action="{{ route('event-coordinator.fund.destroy', $tx) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Delete this transaction?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="9" class="text-center py-4 text-muted">No transactions recorded yet.</td>
                    </tr>
                    @endforelse
                  </tbody>
                  @if($event->fundTransactions->count())
                  <tfoot class="table-light fw-semibold">
                    <tr>
                      <td colspan="5" class="text-end">Total Income:</td>
                      <td class="text-success">₹{{ number_format($totalIncome, 2) }}</td>
                      <td colspan="3"></td>
                    </tr>
                    <tr>
                      <td colspan="5" class="text-end">Total Expense:</td>
                      <td class="text-danger">₹{{ number_format($totalExpense, 2) }}</td>
                      <td colspan="3"></td>
                    </tr>
                    <tr>
                      <td colspan="5" class="text-end">Balance:</td>
                      <td class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format(abs($balance), 2) }}</td>
                      <td colspan="3"></td>
                    </tr>
                  </tfoot>
                  @endif
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== SPONSORS TAB ===== -->
        <div class="tab-pane fade" id="tab-sponsors">
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
              <div>
                <span><i class="fas fa-handshake text-success me-2"></i>Event Sponsors</span>
                <small class="d-block text-muted mt-1">Total Sponsorship: ₹{{ number_format($totalSponsorship, 0) }}</small>
              </div>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSponsorModal">
                <i class="fas fa-plus me-1"></i>Add Sponsor
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Contact</th>
                      <th>Tier</th>
                      <th>Pledged</th>
                      <th>Received</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($event->sponsors as $sp)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td class="fw-semibold">{{ $sp->name }}</td>
                      <td>
                        {{ $sp->contact_person ?? '' }}
                        @if($sp->phone)<br><small class="text-muted">{{ $sp->phone }}</small>@endif
                      </td>
                      <td>
                        <span class="badge rounded-pill
                          @if($sp->tier==='platinum') bg-dark
                          @elseif($sp->tier==='gold') bg-warning text-dark
                          @elseif($sp->tier==='silver') bg-secondary
                          @elseif($sp->tier==='bronze') bg-danger
                          @else bg-info text-dark @endif">{{ ucfirst($sp->tier) }}</span>
                      </td>
                      <td>₹{{ number_format($sp->pledged_amount, 0) }}</td>
                      <td class="text-success fw-semibold">₹{{ number_format($sp->received_amount, 0) }}</td>
                      <td>
                        <span class="badge rounded-pill
                          @if($sp->status==='received') bg-success
                          @elseif($sp->status==='confirmed') bg-info text-dark
                          @elseif($sp->status==='cancelled') bg-danger
                          @else bg-secondary @endif">{{ ucfirst($sp->status) }}</span>
                      </td>
                      <td>
                        <form action="{{ route('event-coordinator.sponsors.destroy', $sp) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Remove this sponsor?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="8" class="text-center py-4 text-muted">No sponsors added yet.</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /tab-content -->
    </div><!-- /container -->

    <!-- ===== MODALS ===== -->

    <!-- Add Program Modal -->
    <div class="modal fade" id="addProgramModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="fas fa-list-alt me-2"></i>Add Program</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('event-coordinator.programs.store', $event) }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
              <div class="col-md-7">
                <label class="form-label fw-semibold">Program Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold">Program Type <span class="text-danger">*</span></label>
                <select name="program_type" class="form-select" required>
                  <option value="intra-college">Intra-College</option>
                  <option value="inter-college">Inter-College</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-semibold">Program Scope <span class="text-danger">*</span></label>
                <select name="program_scope" class="form-select" required>
                  <option value="national">National</option>
                  <option value="international">International</option>
                  <option value="state">State Level</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" rows="2" class="form-control"></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="program_date" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Start Time</label>
                <input type="time" name="start_time" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">End Time</label>
                <input type="time" name="end_time" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Venue</label>
                <input type="text" name="venue" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Registration Fee (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="registration_fee" class="form-control" value="0" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Reg. Start Date</label>
                <input type="date" name="registration_start_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Reg. End Date</label>
                <input type="date" name="registration_end_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Max Participants (0 = unlimited)</label>
                <input type="number" min="0" name="max_participants" class="form-control" value="0">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                  @foreach(['upcoming','ongoing','completed','cancelled'] as $s)
                  <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Program</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Assign Duty Modal -->
    <div class="modal fade" id="addDutyModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Assign Faculty Duty</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('event-coordinator.duties.store', $event) }}" method="POST">
            @csrf
            <div class="modal-body row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                <select name="faculty_id" class="form-select dselect-example" required>
                  <option value="">-- Select Faculty --</option>
                  @foreach($faculties as $fac)
                  <option value="{{ $fac->id }}">{{$fac->USER_CODE}} - {{ $fac->FIRST_NAME }} {{ $fac->LAST_NAME }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Program (optional)</label>
                <select name="program_id" class="form-select">
                  <option value="">-- Event-wide --</option>
                  @foreach($programs as $prog)
                  <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Duty Title <span class="text-danger">*</span></label>
                <input type="text" name="duty_title" class="form-control" required
                  placeholder="e.g. Stage Manager, Registration Desk, Technical Support">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Responsibility Details</label>
                <textarea name="responsibility" rows="3" class="form-control"
                  placeholder="Describe the responsibilities in detail..."></textarea>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Remarks</label>
                <input type="text" name="remarks" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Assign Duty</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Fund Transaction Modal -->
    <div class="modal fade" id="addFundModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="fas fa-rupee-sign me-2"></i>Add Fund Transaction</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('event-coordinator.fund.store', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                  <option value="expense">Expense</option>
                  <option value="income">Income</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                <input type="text" name="category" class="form-control" required
                  placeholder="e.g. decoration, catering, registration">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                  <option value="">-- Select --</option>
                  <option value="cash">Cash</option>
                  <option value="bank transfer">Bank Transfer</option>
                  <option value="cheque">Cheque</option>
                  <option value="upi">UPI</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Receipt No.</label>
                <input type="text" name="receipt_no" class="form-control">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Linked Program (optional)</label>
                <select name="program_id" class="form-select">
                  <option value="">-- Event-level --</option>
                  @foreach($programs as $prog)
                  <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Attachment (PDF/Image)</label>
                <input type="file" name="attachment" class="form-control"
                  accept=".pdf,.jpg,.jpeg,.png">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Transaction</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Sponsor Modal -->
    <div class="modal fade" id="addSponsorModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title fw-bold mb-0"><i class="fas fa-handshake me-2"></i>Add Sponsor</h5>
              <small class="text-muted">Add a sponsor to this event</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('event-coordinator.sponsors.store', $event) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body row g-3">
              <div class="col-md-8">
                <label class="form-label fw-semibold">Sponsor Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Tier <span class="text-danger">*</span></label>
                <select name="tier" class="form-select" required>
                  @foreach(['platinum','gold','silver','bronze','in_kind'] as $tier)
                  <option value="{{ $tier }}">{{ ucfirst($tier) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Contact Person</label>
                <input type="text" name="contact_person" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Phone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Pledged Amount (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="pledged_amount" class="form-control" value="0" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Received Amount (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="received_amount" class="form-control" value="0" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Benefits Offered</label>
                <textarea name="benefits_offered" rows="2" class="form-control"
                  placeholder="e.g. Logo on banners, mention in program booklet..."></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                  @foreach(['pending','confirmed','received','cancelled'] as $s)
                  <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" rows="2" class="form-control"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Sponsor</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
  // Prevent double form submission with improved handling
  document.addEventListener('DOMContentLoaded', function() {
    // Track all forms that have been submitted
    const submittedForms = new WeakSet();
    
    // Handle all form submissions
    document.addEventListener('submit', function(e) {
      const form = e.target;
      
      // Check if form is already being submitted
      if (submittedForms.has(form)) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log('Form already submitted, preventing duplicate');
        return false;
      }
      
      // Get the submit button
      const submitBtn = form.querySelector('button[type="submit"]');
      
      // Check if button is already disabled
      if (submitBtn && submitBtn.disabled) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log('Submit button already disabled, preventing duplicate');
        return false;
      }
      
      // Mark form as submitted
      submittedForms.add(form);
      
      // Disable submit button
      if (submitBtn) {
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        
        // Re-enable after 5 seconds as fallback (in case of validation errors or slow network)
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
          submittedForms.delete(form);
        }, 5000);
      }
    }, true); // Use capture phase to catch event early
    
    // Reset form state when modal is closed
    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
          const submitBtn = form.querySelector('button[type="submit"]');
          if (submitBtn && submitBtn.disabled) {
            submitBtn.disabled = false;
            // Restore original text if it was changed
            if (submitBtn.innerHTML.includes('Processing')) {
              const formId = form.id || '';
              if (formId.includes('Sponsor')) {
                submitBtn.innerHTML = 'Add Sponsor';
              } else if (formId.includes('Program')) {
                submitBtn.innerHTML = 'Add Program';
              } else if (formId.includes('Duty')) {
                submitBtn.innerHTML = 'Assign Duty';
              } else if (formId.includes('Fund')) {
                submitBtn.innerHTML = 'Save Transaction';
              }
            }
          }
          submittedForms.delete(form);
        }
      });
    });
  });
</script>

@include('includes.footer')
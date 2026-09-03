@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2 mb-3">
    <div class="breadcrumb-title pe-3">International Office</div>
    <div class="ps-2">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 p-0">
          <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Activity Types</div>
          <div class="display-6 fw-bold text-primary">{{ $activityTypeCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Active Activity Types</div>
          <div class="display-6 fw-bold text-success">{{ $activeActivityTypeCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Institutions</div>
          <div class="display-6 fw-bold text-info">{{ $institutionCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">MoU Signed (Institutions)</div>
          <div class="display-6 fw-bold text-success">{{ $institutionMouSignedCount }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Institutions Without MoU</div>
          <div class="display-6 fw-bold text-warning">{{ $institutionWithoutMouCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Events</div>
          <div class="display-6 fw-bold text-primary">{{ $eventCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Events With MoU</div>
          <div class="display-6 fw-bold text-success">{{ $eventMouCount }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Net Expense (Debit - Credit)</div>
          <div class="display-6 fw-bold {{ $netExpense >= 0 ? 'text-danger' : 'text-success' }}">{{ number_format($netExpense, 2) }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Debit</div>
          <div class="display-6 fw-bold text-danger">{{ number_format($totalDebit, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Credit</div>
          <div class="display-6 fw-bold text-success">{{ number_format($totalCredit, 2) }}</div>
        </div>
      </div>
    </div>
  </div>



</div>

@include('includes.footer')
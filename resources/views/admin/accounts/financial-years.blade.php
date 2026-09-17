@include('includes.header')
@include('admin.accounts.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Financial Year Management</h3>
  </div>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  @if($activeFinancialYear)
  <div class="alert alert-info">
    <strong>Active Financial Year:</strong>
    {{ $activeFinancialYear->title }}
    ({{ $activeFinancialYear->start_date ? $activeFinancialYear->start_date->format('d-M-Y') : '' }} to {{ $activeFinancialYear->end_date ? $activeFinancialYear->end_date->format('d-M-Y') : '' }})
  </div>
  @else
  <div class="alert alert-warning">
    No active financial year is set. Accounts reports will show all dates until one is activated.
  </div>
  @endif

  <div class="card mb-4">
    <div class="card-header bg-light"><strong>Create Financial Year</strong></div>
    <div class="card-body">
      <form action="{{ route('accounts.financial-years.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-4">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" placeholder="FY 2026-2027" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Start Date</label>
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">End Date</label>
          <input type="date" name="end_date" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveFy">
            <label class="form-check-label" for="isActiveFy">Set Active</label>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Create Financial Year</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-light"><strong>Configured Financial Years</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($financialYears as $fy)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $fy->title }}</td>
              <td>{{ $fy->start_date ? $fy->start_date->format('d-M-Y') : 'N/A' }}</td>
              <td>{{ $fy->end_date ? $fy->end_date->format('d-M-Y') : 'N/A' }}</td>
              <td>
                @if($fy->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
              <td>
                @if(!$fy->is_active)
                <form action="{{ route('accounts.financial-years.activate', $fy->id) }}" method="POST" onsubmit="return confirm('Set this as active financial year?');">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-primary">Set Active</button>
                </form>
                @else
                <span class="text-muted">Current</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center">No financial years configured yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')
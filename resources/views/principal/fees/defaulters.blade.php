@include('includes.header')

<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Fees</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('principal.fees.index') }}">Fees</a></li>
            <li class="breadcrumb-item active" aria-current="page">Defaulters</li>
          </ol>
        </nav>
      </div>
    </div>

    <!--filters-->
    <div class="card mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('principal.fees.defaulters') }}" class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select form-select-sm">
              <option value="">All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Batch</label>
            <select name="batch_id" class="form-select form-select-sm">
              <option value="">All Batches</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Programme</label>
            <select name="programme_id" class="form-select form-select-sm">
              <option value="">All Programmes</option>
              @foreach($programs as $pg)
              <option value="{{ $pg->id }}" {{ request('programme_id') == $pg->id ? 'selected' : '' }}>{{ $pg->program_code }} - {{ $pg->programInfo->name ?? '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
            <a href="{{ route('principal.fees.defaulters') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
          </div>
        </form>
      </div>
    </div>

    <!--summary-->
    <div class="row mt-3">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <h3 class="fw-bold text-danger">{{ count($defaulters) }}</h3>
            <p class="text-muted mb-0">Total Defaulter Entries</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <h3 class="fw-bold text-warning">{{ collect($defaulters)->unique(fn($d) => $d['student']->id)->count() }}</h3>
            <p class="text-muted mb-0">Unique Students</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center">
            <h3 class="fw-bold text-primary">₹{{ number_format(collect($defaulters)->sum('total_due')) }}</h3>
            <p class="text-muted mb-0">Total Outstanding</p>
          </div>
        </div>
      </div>
    </div>

    <!--defaulters table-->
    <div class="card mt-3 mb-4">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Fee Defaulters</h5>
      </div>
      <div class="card-body">
        @if(count($defaulters) > 0)
        <div class="table-responsive">
          <table class="table table-hover table-striped table-sm align-middle" id="exportTable">
            <thead class="bg-dark text-light">
              <tr>
                <th>#</th>
                <th>Roll No</th>
                <th>Student Name</th>
                <th>Campus</th>
                <th>Batch</th>
                <th>Programme</th>
                <th>Fee Quarter</th>
                <th>Fee Amount</th>
                <th>Due Date</th>
                <th>Late Days</th>
                <th>Late Fee</th>
                <th>Total Due</th>
              </tr>
            </thead>
            <tbody>
              @foreach($defaulters as $index => $d)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-uppercase">{{ $d['student']->roll_no ?? '-' }}</td>
                <td class="text-capitalize">{{ $d['student']->first_name }} {{ $d['student']->last_name }}</td>
                <td>{{ $d['student']->campusmaster->name ?? '-' }}</td>
                <td>{{ $d['student']->batchmaster->batch_name ?? '-' }}</td>
                <td>{{ $d['student']->programgroup->programInfo->name ?? '-' }}</td>
                <td>{{ $d['fee_structure']->quarter_title ?? '-' }}</td>
                <td class="text-end">₹{{ number_format($d['base_amount']) }}</td>
                <td>{{ $d['due_date'] ? \Carbon\Carbon::parse($d['due_date'])->format('d M Y') : '-' }}</td>
                <td><span class="badge bg-danger">{{ $d['late_days'] }}</span></td>
                <td class="text-end">₹{{ number_format($d['late_fee']) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($d['total_due']) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i>No defaulters found. All students are up to date with their fee payments.
        </div>
        @endif
      </div>
    </div>

  </main>
</div>

@include('includes.footer')
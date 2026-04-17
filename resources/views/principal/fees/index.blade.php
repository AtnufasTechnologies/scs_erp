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
            <li class="breadcrumb-item active" aria-current="page">Student Fees</li>
          </ol>
        </nav>
      </div>
    </div>

    <!--filters-->
    <div class="card mt-3">
      <div class="card-body">
        <form method="GET" action="{{ route('principal.fees.index') }}" class="row g-2 align-items-end">
          <div class="col-md-2">
            <label class="form-label">Campus</label>
            <select name="campus_id" class="form-select form-select-sm">
              <option value="">All Campuses</option>
              @foreach($campuses as $campus)
              <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Batch</label>
            <select name="batch_id" class="form-select form-select-sm">
              <option value="">All Batches</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Programme</label>
            <select name="programme_id" class="form-select form-select-sm">
              <option value="">All Programmes</option>
              @foreach($programs as $pg)
              <option value="{{ $pg->id }}" {{ request('programme_id') == $pg->id ? 'selected' : '' }}>{{ $pg->program_code }} - {{ $pg->programInfo->name ?? '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Roll No / Name" value="{{ request('search') }}">
          </div>
          <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
            <a href="{{ route('principal.fees.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
          </div>
        </form>
      </div>
    </div>

    <!--student fee cards-->
    <div class="row mt-3">
      @forelse($students as $item)
      <div class="col-xl-6 col-lg-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-0 text-capitalize">{{ $item['student']->first_name }} {{ $item['student']->last_name }}</h6>
              <small class="text-muted text-uppercase">{{ $item['student']->roll_no }}</small>
              <br>
              <small class="text-muted">
                {{ $item['student']->programgroup->program_code ?? '' }} - {{ $item['student']->programgroup->programInfo->name ?? '' }}
                &bull; Batch: {{ $item['student']->batchmaster->batch_name ?? '-' }}
                &bull; Year: {{ $item['student']->current_year ?? '-' }}
              </small>
            </div>
            <div class="text-end">
              @if($item['total_due'] > 0)
              <span class="badge bg-danger">Due: ₹{{ number_format($item['total_due']) }}</span>
              @else
              <span class="badge bg-success">All Paid</span>
              @endif
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
              <thead class="table-light">
                <tr>
                  <th>Quarter</th>
                  <th>Year</th>
                  <th class="text-end">Amount</th>
                  <th class="text-end">Late Fee</th>
                  <th class="text-end">Payable</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($item['fee_details'] as $fee)
                <tr>
                  <td>{{ $fee['quarter'] }}</td>
                  <td>{{ $fee['year'] }}</td>
                  <td class="text-end">₹{{ number_format($fee['amount']) }}</td>
                  <td class="text-end">{{ $fee['late_fee'] > 0 ? '₹'.number_format($fee['late_fee']) : '-' }}</td>
                  <td class="text-end">₹{{ number_format($fee['payable']) }}</td>
                  <td>
                    @if($fee['status'] === 'Paid')
                    <span class="badge bg-success">Paid</span>
                    @elseif($fee['status'] === 'Late')
                    <span class="badge bg-danger">Late ({{ $fee['late_days'] }}d)</span>
                    @else
                    <span class="badge bg-warning">Due</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="table-light">
                <tr class="fw-bold">
                  <td colspan="2">Total</td>
                  <td class="text-end">₹{{ number_format($item['total_fee']) }}</td>
                  <td></td>
                  <td class="text-end">₹{{ number_format($item['total_due'] + $item['total_paid']) }}</td>
                  <td><span class="text-success">Paid: ₹{{ number_format($item['total_paid']) }}</span></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No students found. Use the filters above to search.</div>
      </div>
      @endforelse
    </div>

    <!--pagination-->
    @if($data->hasPages())
    <div class="d-flex justify-content-center mt-2 mb-4">
      {{ $data->links('pagination::bootstrap-5') }}
    </div>
    @endif

  </main>
</div>

@include('includes.footer')
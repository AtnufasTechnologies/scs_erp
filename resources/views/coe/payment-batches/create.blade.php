@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Payment Batches</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payment-batches.index') }}">Payment Batches</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Batch</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-plus-circle me-2"></i>Create Payment Batch</h3>
                  <p class="text-white-50 mb-0">Select approved remunerations to group into a payment batch</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.payment-batches.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if($remunerations->isEmpty())
      <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
          <i class="fas fa-check-circle text-muted" style="font-size: 3rem;"></i>
          <h5 class="mt-3 text-muted">No Approved Remunerations Available</h5>
          <p class="text-muted">All approved remunerations have already been batched or there are none to process.</p>
          <a href="{{ route('admin.exam-remuneration.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i>Go to Remuneration
          </a>
        </div>
      </div>
      @else
      <form method="POST" action="{{ route('admin.payment-batches.store') }}" id="batchForm">
        @csrf

        <!-- Batch Name -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body p-4">
            <div class="row align-items-end">
              <div class="col-md-6">
                <label for="batch_name" class="form-label fw-semibold">Batch Name <span class="text-danger">*</span></label>
                <input type="text" name="batch_name" id="batch_name" class="form-control @error('batch_name') is-invalid @enderror"
                  value="{{ old('batch_name', 'Batch-' . now()->format('Ymd-His')) }}" required>
                @error('batch_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-3">
                <div class="text-muted">Selected: <strong id="selectedCount">0</strong> items</div>
                <div class="text-muted">Total: <strong id="selectedTotal">0.00</strong></div>
              </div>
              <div class="col-md-3 text-end">
                <button type="button" class="btn btn-outline-primary me-2" id="selectAll">Select All</button>
                <button type="submit" class="btn btn-primary" id="createBtn" disabled>
                  <i class="fas fa-save me-1"></i>Create Batch
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Faculty Groups -->
        @foreach($grouped as $facultyId => $group)
        <div class="card shadow-sm border-0 mb-3">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0 fw-bold">
                <i class="fas fa-user-tie text-primary me-2"></i>{{ $group['faculty']->name ?? 'Unknown Faculty' }}
              </h6>
              <small class="text-muted">{{ $group['faculty']->department ?? '' }} | {{ $group['count'] }} items | Total: {{ number_format($group['total'], 2) }}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary select-group" data-faculty="{{ $facultyId }}">
              Select All
            </button>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 40px;">
                      <input type="checkbox" class="form-check-input group-check" data-faculty="{{ $facultyId }}">
                    </th>
                    <th>Duty Type</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Total Amount</th>
                    <th>Generated At</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($group['items'] as $rem)
                  <tr>
                    <td>
                      <input type="checkbox" name="remuneration_ids[]" value="{{ $rem->id }}"
                        class="form-check-input item-check" data-faculty="{{ $facultyId }}"
                        data-amount="{{ $rem->total_amount }}">
                    </td>
                    <td>
                      @if($rem->duty_type === 'invigilation')
                      <span class="badge bg-primary">Invigilation</span>
                      @elseif($rem->duty_type === 'evaluation')
                      <span class="badge bg-info">Evaluation</span>
                      @else
                      <span class="badge bg-secondary">Moderation</span>
                      @endif
                    </td>
                    <td>{{ $rem->quantity }}</td>
                    <td>{{ number_format($rem->rate, 2) }}</td>
                    <td class="fw-bold">{{ number_format($rem->total_amount, 2) }}</td>
                    <td>{{ $rem->generated_at ? $rem->generated_at->format('d M Y') : '-' }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        @endforeach
      </form>
      @endif
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const checks = document.querySelectorAll('.item-check');
    const countEl = document.getElementById('selectedCount');
    const totalEl = document.getElementById('selectedTotal');
    const createBtn = document.getElementById('createBtn');
    const selectAllBtn = document.getElementById('selectAll');

    function updateSummary() {
      let count = 0,
        total = 0;
      checks.forEach(function(cb) {
        if (cb.checked) {
          count++;
          total += parseFloat(cb.dataset.amount) || 0;
        }
      });
      if (countEl) countEl.textContent = count;
      if (totalEl) totalEl.textContent = total.toFixed(2);
      if (createBtn) createBtn.disabled = count === 0;
    }

    checks.forEach(function(cb) {
      cb.addEventListener('change', updateSummary);
    });

    // Select all button
    if (selectAllBtn) {
      selectAllBtn.addEventListener('click', function() {
        const allChecked = Array.from(checks).every(function(c) {
          return c.checked;
        });
        checks.forEach(function(cb) {
          cb.checked = !allChecked;
        });
        document.querySelectorAll('.group-check').forEach(function(gc) {
          gc.checked = !allChecked;
        });
        updateSummary();
      });
    }

    // Group select buttons
    document.querySelectorAll('.select-group').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const fid = this.dataset.faculty;
        const groupChecks = document.querySelectorAll('.item-check[data-faculty="' + fid + '"]');
        const allChecked = Array.from(groupChecks).every(function(c) {
          return c.checked;
        });
        groupChecks.forEach(function(cb) {
          cb.checked = !allChecked;
        });
        const gc = document.querySelector('.group-check[data-faculty="' + fid + '"]');
        if (gc) gc.checked = !allChecked;
        updateSummary();
      });
    });

    // Group header checkboxes
    document.querySelectorAll('.group-check').forEach(function(gc) {
      gc.addEventListener('change', function() {
        const fid = this.dataset.faculty;
        const groupChecks = document.querySelectorAll('.item-check[data-faculty="' + fid + '"]');
        groupChecks.forEach(function(cb) {
          cb.checked = gc.checked;
        });
        updateSummary();
      });
    });
  });
</script>
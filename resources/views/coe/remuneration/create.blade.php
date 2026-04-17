@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Remuneration</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.exam-remuneration.index') }}">Remuneration</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Entry</li>
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
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-plus-circle me-2"></i>Add Remuneration Entry</h3>
                  <p class="text-white-50 mb-0">Manually add a remuneration record for a faculty member</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('admin.exam-remuneration.index') }}" class="btn btn-outline-light">
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

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-edit text-primary me-2"></i>Remuneration Details</h5>
            </div>
            <div class="card-body p-4">
              <form method="POST" action="{{ route('admin.exam-remuneration.store') }}">
                @csrf

                <div class="mb-3">
                  <label for="faculty_id" class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                  <select name="faculty_id" id="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
                    <option value="">Select Faculty</option>
                    @foreach($faculties as $faculty)
                    <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                      {{ $faculty->name }} — {{ $faculty->department ?? 'N/A' }}
                    </option>
                    @endforeach
                  </select>
                  @error('faculty_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <label for="duty_type" class="form-label fw-semibold">Duty Type <span class="text-danger">*</span></label>
                  <select name="duty_type" id="duty_type" class="form-select @error('duty_type') is-invalid @enderror" required>
                    <option value="">Select Duty Type</option>
                    <option value="invigilation" {{ old('duty_type') === 'invigilation' ? 'selected' : '' }}>Invigilation</option>
                    <option value="evaluation" {{ old('duty_type') === 'evaluation' ? 'selected' : '' }}>Evaluation</option>
                    <option value="moderation" {{ old('duty_type') === 'moderation' ? 'selected' : '' }}>Moderation</option>
                  </select>
                  @error('duty_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror"
                      value="{{ old('quantity', 1) }}" min="1" required>
                    @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Number of sessions, copies, etc.</small>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label for="rate" class="form-label fw-semibold">Rate (per unit) <span class="text-danger">*</span></label>
                    <input type="number" name="rate" id="rate" class="form-control @error('rate') is-invalid @enderror"
                      value="{{ old('rate') }}" min="0" step="0.01" required>
                    @error('rate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-semibold">Total Amount</label>
                  <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="text" id="total_display" class="form-control bg-light" readonly value="0.00">
                  </div>
                  <small class="text-muted">Auto-calculated: Quantity × Rate</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <a href="{{ route('admin.exam-remuneration.index') }}" class="btn btn-outline-secondary">Cancel</a>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Remuneration
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const qty = document.getElementById('quantity');
    const rate = document.getElementById('rate');
    const total = document.getElementById('total_display');

    function calcTotal() {
      const q = parseFloat(qty.value) || 0;
      const r = parseFloat(rate.value) || 0;
      total.value = (q * r).toFixed(2);
    }

    qty.addEventListener('input', calcTotal);
    rate.addEventListener('input', calcTotal);
    calcTotal();
  });
</script>
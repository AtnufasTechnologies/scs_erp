@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Leave Application</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('faculty.leave.index') }}">Leaves</a></li>
            <li class="breadcrumb-item active" aria-current="page">Apply for Leave</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Apply for Leave</h5>
          </div>
          <div class="card-body p-4">
            <form action="{{ route('faculty.leave.store') }}" method="POST" enctype="multipart/form-data">
              @csrf

              <div class="row">
                <!-- Leave Type -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Leave Type <span class="text-danger">*</span></label>
                  <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                    <option value="">Select Leave Type</option>
                    @foreach($leaveTypes as $leaveType)
                    <option value="{{ $leaveType->id }}" {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                      {{ $leaveType->leave_type_name }}
                      @if($leaveType->allowed_days_per_year)
                      ({{ $leaveType->allowed_days_per_year }} days/year)
                      @else
                      (Unlimited)
                      @endif
                    </option>
                    @endforeach
                  </select>
                  @error('leave_type_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- Contact During Leave -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Contact Number During Leave</label>
                  <input type="text" name="contact_during_leave" class="form-control @error('contact_during_leave') is-invalid @enderror"
                    value="{{ old('contact_during_leave') }}" placeholder="Enter contact number">
                  @error('contact_during_leave')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <!-- Start Date -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                  <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                    value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                  @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <!-- End Date -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                  <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                    value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                  @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Reason -->
              <div class="mb-3">
                <label class="form-label fw-bold">Reason for Leave <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
                  placeholder="Please provide detailed reason for leave..." required>{{ old('reason') }}</textarea>
                @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Maximum 1000 characters</small>
              </div>

              <!-- Attachment -->
              <div class="mb-3">
                <label class="form-label fw-bold">Attachment (Optional)</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                  accept=".pdf,.jpg,.jpeg,.png">
                @error('attachment')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Upload medical certificate or supporting documents (PDF, JPG, PNG - Max 5MB)</small>
              </div>

              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Your leave application will be sent to the administration for approval.
                You will be notified once it's processed.
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('faculty.leave.index') }}" class="btn btn-secondary">
                  <i class="fas fa-times me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-paper-plane me-1"></i>Submit Application
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </main>
  <!--end page content-->
</div>

<script>
  // Calculate total days when dates change
  document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');

    function updateEndDateMin() {
      if (startDate.value) {
        endDate.min = startDate.value;
      }
    }

    startDate.addEventListener('change', updateEndDateMin);
  });
</script>

@include('includes.footer')
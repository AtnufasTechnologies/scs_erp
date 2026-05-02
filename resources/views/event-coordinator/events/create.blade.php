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
            <li class="breadcrumb-item active" aria-current="page">Create</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Event</h5>
            </div>
            <div class="card-body">
              <form action="{{ route('event-coordinator.events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('event-coordinator.events._form')
                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Event</button>
                  <a href="{{ route('event-coordinator.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
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

        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
          submittedForms.delete(form);
        }, 5000);
      }
    }, true); // Use capture phase to catch event early
  });
</script>

@include('includes.footer')
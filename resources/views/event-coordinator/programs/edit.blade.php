@include('includes.header')

<div class="wrapper">
  @include('event-coordinator.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Programs</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.events.index') }}">Events</a></li>
            <li class="breadcrumb-item"><a href="{{ route('event-coordinator.events.show', $program->event) }}">{{ Str::limit($program->event->title, 40) }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Program</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-edit text-info me-2"></i>Edit Program</h5>
              <small class="text-muted">Event: {{ $program->event->title }}</small>
            </div>
            <div class="card-body">
              <form action="{{ route('event-coordinator.programs.update', $program) }}" method="POST">
                @csrf @method('PUT')
                @include('event-coordinator.programs._form')
                <div class="d-flex gap-2 mt-4">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Program</button>
                  <a href="{{ route('event-coordinator.events.show', $program->event) }}" class="btn btn-outline-secondary">Cancel</a>
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

    // Reset form state when modal is closed
    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
          const submitBtn = form.querySelector('button[type="submit"]');
          if (submitBtn && submitBtn.disabled) {
            submitBtn.disabled = false;
            if (submitBtn.innerHTML.includes('Processing')) {
              submitBtn.innerHTML = 'Add Sponsor';
            }
          }
          submittedForms.delete(form);
        }
      });
    });
  });
</script>

@include('includes.footer')
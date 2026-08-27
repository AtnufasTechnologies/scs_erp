@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Sent Messages</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.mailbox.index') }}">Inbox</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sent</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">


      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label fw-semibold mb-1">Search Sent Messages</label>
              <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by subject, company, or content">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold mb-1">Filter Company</label>
              <select name="company_id" class="form-select">
                <option value="0">All Companies</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
              <button class="btn btn-primary w-100" type="submit">Apply</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <span class="fw-bold">Sent Mail History</span>
          <span class="badge bg-secondary">Total: {{ $messages->count() }}</span>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('tpo.training-placement.mailbox.bulk-trash') }}" id="bulkTrashForm" onsubmit="return confirmBulkDelete();">
            @csrf

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllSent">
                <label class="form-check-label" for="selectAllSent">Select All</label>
              </div>
              <button type="submit" class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn" disabled>
                <i class="fas fa-trash me-1"></i>Archive Selected
              </button>
            </div>

            @forelse($messages as $message)
            @php
            $thread = $message->thread;
            @endphp
            <div class="border rounded p-3 mb-2">
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                  @if($thread)
                  <div class="form-check m-0">
                    <input class="form-check-input sent-thread-checkbox" type="checkbox" name="thread_ids[]" value="{{ $thread->id }}" id="threadCheck{{ $message->id }}">
                  </div>
                  @endif
                  <div>
                    <h6 class="mb-1 fw-bold">{{ $thread->subject ?? 'No Subject' }}</h6>
                    <div class="small text-muted">{{ $message->sent_at ? $message->sent_at->format('d M Y h:i A') : ($message->created_at ? $message->created_at->format('d M Y h:i A') : 'N/A') }}</div>
                  </div>
                </div>
                @if($thread)
                <a href="{{ route('tpo.training-placement.mailbox.show', $thread->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                @endif
              </div>
            </div>
            @empty
            <div class="alert alert-info mb-0">No sent messages found.</div>
            @endforelse

          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  (function() {
    const selectAll = document.getElementById('selectAllSent');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const checkboxes = Array.from(document.querySelectorAll('.sent-thread-checkbox'));

    if (!selectAll || !bulkBtn || checkboxes.length === 0) {
      return;
    }

    const syncButtonState = function() {
      const checkedCount = checkboxes.filter(function(cb) {
        return cb.checked;
      }).length;

      bulkBtn.disabled = checkedCount === 0;
      selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
      selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    };

    selectAll.addEventListener('change', function() {
      checkboxes.forEach(function(cb) {
        cb.checked = selectAll.checked;
      });
      syncButtonState();
    });

    checkboxes.forEach(function(cb) {
      cb.addEventListener('change', syncButtonState);
    });

    window.confirmBulkDelete = function() {
      const checkedCount = checkboxes.filter(function(cb) {
        return cb.checked;
      }).length;

      if (checkedCount === 0) {
        alert('Please select at least one mail.');
        return false;
      }

      return confirm('Move selected mail threads to trash?');
    };
  })();
</script>
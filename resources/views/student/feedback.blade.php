@include('includes.header')

<div class="wrapper">


  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Feedback</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Subject Feedback</li>
          </ol>
        </nav>

      </div>
    </div>
    <!--end breadcrumb-->

    @include('includes.alert')

    <!-- Header Card -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:14px;background:linear-gradient(135deg,#d4edda 0%,#fff 100%);">
          <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
              <div style="width:52px;height:52px;background:#198754;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-star text-white" style="font-size:1.4rem;"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">Subject Feedback</h5>
                <p class="text-muted mb-0 small">Rate and provide feedback on completed subject subunits. Your honest feedback helps improve the curriculum.</p>
              </div>
              <div class="ms-auto text-end d-none d-md-block">
                @php
                $pending = $completedSubunits->filter(fn($s) => $s['existing_feedback'] === null)->count();
                $reviewed = $completedSubunits->filter(fn($s) => $s['existing_feedback'] !== null)->count();
                @endphp
                <div class="fw-bold fs-4 text-warning">{{ $pending }}</div>
                <div class="text-muted small">Pending</div>
              </div>
              <div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-success">
                  <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Badges -->
    <div class="row mb-4 g-3">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
          <div class="fw-bold fs-3 text-primary">{{ $completedSubunits->count() }}</div>
          <div class="text-muted small">Total Subunits</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
          <div class="fw-bold fs-3 text-success">{{ $reviewed }}</div>
          <div class="text-muted small">Reviewed</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
          <div class="fw-bold fs-3 text-warning">{{ $pending }}</div>
          <div class="text-muted small">Pending</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
          @php $avgRating = $completedSubunits->filter(fn($s) => $s['existing_feedback']?->rating)->avg(fn($s) => $s['existing_feedback']->rating); @endphp
          <div class="fw-bold fs-3 text-info">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</div>
          <div class="text-muted small">Avg Rating</div>
        </div>
      </div>
    </div>

    <!-- Subunits List -->
    @if($completedSubunits->isEmpty())
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
      <div class="card-body text-center py-5">
        <i class="fas fa-star text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3 text-muted">No completed subunits yet</h5>
        <p class="text-muted small">When a faculty member marks a subunit as completed, it will appear here for your feedback.</p>
      </div>
    </div>
    @else

    <!-- Filter tabs -->
    <div class="mb-3 d-flex gap-2">
      <button class="btn btn-sm btn-primary" onclick="filterFeedback('all')" id="filter-all">All ({{ $completedSubunits->count() }})</button>
      <button class="btn btn-sm btn-outline-warning" onclick="filterFeedback('pending')" id="filter-pending">Pending ({{ $pending }})</button>
      <button class="btn btn-sm btn-outline-success" onclick="filterFeedback('reviewed')" id="filter-reviewed">Reviewed ({{ $reviewed }})</button>
    </div>

    <div id="feedback-list">
      @foreach($completedSubunits as $item)
      @php
      $subunit = $item['subunit'];
      $existing = $item['existing_feedback'];
      $isReviewed = $existing !== null;
      @endphp
      <div class="feedback-item {{ $isReviewed ? 'is-reviewed' : 'is-pending' }} card border-0 shadow-sm mb-3"
        style="border-radius:12px; {{ $isReviewed ? 'border-left: 4px solid #198754 !important;' : 'border-left: 4px solid #ffc107 !important;' }}">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-8">
              <div class="d-flex align-items-start gap-3">
                <div style="width:42px;height:42px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:{{ $isReviewed ? '#d1e7dd' : '#fff3cd' }};">
                  <i class="fas fa-{{ $isReviewed ? 'check' : 'clock' }}" style="color:{{ $isReviewed ? '#198754' : '#997404' }};font-size:1rem;"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1">{{ $subunit->csoSubunit->title ?? 'Subunit #'.$subunit->id }}</h6>
                  <div class="text-muted small mb-1">
                    <i class="fas fa-book me-1"></i> {{ $item['subject_title'] }}
                    @if($subunit->csoSubunit?->taxomonylevel)
                    &nbsp;·&nbsp; <span class="badge bg-info text-dark">{{ $subunit->csoSubunit->taxomonylevel->title ?? '' }}</span>
                    @endif
                  </div>
                  @if($isReviewed && $existing->rating)
                  <div class="d-flex gap-1 mt-1">
                    @for($r = 1; $r <= 5; $r++)
                      <i class="fas fa-star" style="color:{{ $r <= $existing->rating ? '#ffc107' : '#dee2e6' }};font-size:.9rem;"></i>
                      @endfor
                      <span class="text-muted small ms-1">{{ $existing->rating }}/5</span>
                  </div>
                  @endif
                  @if($isReviewed && $existing->feedback)
                  <div class="mt-2 text-muted small fst-italic">"{{ Str::limit($existing->feedback, 120) }}"</div>
                  @endif
                </div>
              </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              @if($isReviewed)
              <span class="badge bg-success me-2">Reviewed</span>
              <button class="btn btn-sm btn-outline-secondary" onclick="openFeedbackModal({{ $subunit->id }}, '{{ addslashes($subunit->csoSubunit->title ?? 'Subunit') }}', {{ $existing->rating ?? 'null' }}, '{{ addslashes($existing->feedback ?? '') }}')">
                <i class="fas fa-edit me-1"></i> Edit
              </button>
              @else
              <span class="badge bg-warning text-dark me-2">Pending</span>
              <button class="btn btn-sm btn-primary" onclick="openFeedbackModal({{ $subunit->id }}, '{{ addslashes($subunit->csoSubunit->title ?? 'Subunit') }}', null, '')">
                <i class="fas fa-star me-1"></i> Rate Now
              </button>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

  </main>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <form method="POST" id="feedbackForm">
        @csrf
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">Submit Feedback</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <p class="text-muted small mb-3" id="modal-subunit-title"></p>

          <!-- Star Rating -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Rating</label>
            <div class="d-flex gap-2" id="star-rating-display">
              @for($r = 1; $r <= 5; $r++)
                <i class="fas fa-star fs-4 text-muted" style="cursor:pointer;" data-rating="{{ $r }}"
                onclick="setRating({{ $r }})" onmouseenter="hoverRating({{ $r }})" onmouseleave="resetHover()"></i>
                @endfor
            </div>
            <input type="hidden" name="rating" id="rating-input">
            <div class="text-muted small mt-1" id="rating-label">Click a star to rate (optional)</div>
          </div>

          <!-- Feedback Text -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Your Feedback <span class="text-muted fw-normal">(optional)</span></label>
            <textarea name="feedback" id="feedback-text" class="form-control" rows="4"
              placeholder="Share what you learned, how effective the teaching was, suggestions for improvement..."
              maxlength="1000" style="border-radius:10px;resize:none;"></textarea>
            <div class="text-muted small mt-1">
              <span id="char-count">0</span>/1000 characters
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-paper-plane me-1"></i> Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('student.footer')

@push('scripts')
<script>
  let selectedRating = null;

  function openFeedbackModal(subunitId, title, currentRating, currentFeedback) {
    selectedRating = currentRating;
    document.getElementById('modal-subunit-title').textContent = title;
    document.getElementById('feedbackForm').action = '/erp/student/feedback/' + subunitId;
    document.getElementById('feedback-text').value = currentFeedback || '';
    document.getElementById('char-count').textContent = (currentFeedback || '').length;
    document.getElementById('rating-input').value = currentRating || '';
    updateStars(currentRating || 0);
    updateRatingLabel(currentRating);
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
  }

  function setRating(val) {
    selectedRating = val;
    document.getElementById('rating-input').value = val;
    updateStars(val);
    updateRatingLabel(val);
  }

  function hoverRating(val) {
    updateStars(val);
  }

  function resetHover() {
    updateStars(selectedRating || 0);
  }

  function updateStars(val) {
    document.querySelectorAll('#star-rating-display .fa-star').forEach((s, i) => {
      s.style.color = i < val ? '#ffc107' : '#dee2e6';
    });
  }

  function updateRatingLabel(val) {
    const labels = {
      1: 'Poor',
      2: 'Fair',
      3: 'Good',
      4: 'Very Good',
      5: 'Excellent'
    };
    document.getElementById('rating-label').textContent = val ? labels[val] : 'Click a star to rate (optional)';
  }

  document.getElementById('feedback-text').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
  });

  function filterFeedback(type) {
    const items = document.querySelectorAll('.feedback-item');
    items.forEach(item => {
      if (type === 'all') {
        item.style.display = '';
      } else if (type === 'pending') {
        item.style.display = item.classList.contains('is-pending') ? '' : 'none';
      } else if (type === 'reviewed') {
        item.style.display = item.classList.contains('is-reviewed') ? '' : 'none';
      }
    });
    document.querySelectorAll('[id^=filter-]').forEach(b => b.classList.remove('btn-primary', 'btn-warning', 'btn-success'));
    document.querySelectorAll('[id^=filter-]').forEach(b => {
      b.classList.add('btn-outline-' + (b.id === 'filter-all' ? 'primary' : b.id === 'filter-pending' ? 'warning' : 'success'));
    });
    const clicked = document.getElementById('filter-' + type);
    clicked.classList.remove('btn-outline-primary', 'btn-outline-warning', 'btn-outline-success');
    clicked.classList.add('btn-' + (type === 'all' ? 'primary' : type === 'pending' ? 'warning' : 'success'));
  }
</script>
@endpush
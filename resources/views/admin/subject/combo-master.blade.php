@include('includes.header')
@include('includes.dept-sidebar')

<style>
  .combo-page {
    padding: 28px;
  }

  /* ── Page header ── */
  .combo-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 60%, #7c3aed 100%);
    border-radius: 20px;
    padding: 28px 32px;
    color: white;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
  }

  .combo-hero::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, .06);
    border-radius: 50%;
  }

  .combo-hero::before {
    content: '';
    position: absolute;
    right: 60px;
    bottom: -60px;
    width: 160px;
    height: 160px;
    background: rgba(255, 255, 255, .04);
    border-radius: 50%;
  }

  /* ── Filter bar ── */
  .filter-bar {
    background: white;
    border-radius: 16px;
    padding: 18px 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .filter-bar .form-select {
    border-radius: 10px;
    border: 1.5px solid #e5e7eb;
    font-size: 14px;
    padding: 10px 14px;
    min-width: 200px;
    color: #374151;
    transition: border-color .2s;
  }

  .filter-bar .form-select:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
  }

  .filter-bar .btn-filter {
    background: linear-gradient(135deg, #5b4cdb, #7c3aed);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: 14px;
    font-weight: 600;
    transition: opacity .2s;
  }

  .filter-bar .btn-filter:hover {
    opacity: .9;
  }

  .filter-bar .btn-clear {
    background: #f5f7fa;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s;
  }

  .filter-bar .btn-clear:hover {
    background: #e5e7eb;
  }

  /* ── Stat chips ── */
  .stat-chip {
    background: linear-gradient(135deg, rgba(124, 58, 237, .1), rgba(91, 76, 219, .08));
    border: 1.5px solid rgba(124, 58, 237, .2);
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 13px;
    color: #5b4cdb;
    font-weight: 600;
  }

  /* ── Combo cards ── */
  .combo-card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .07);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
  }

  .combo-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
  }

  .combo-card-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 100%);
    padding: 18px 20px 14px;
    position: relative;
  }

  .combo-card-header .batch-badge {
    background: rgba(255, 255, 255, .18);
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
  }

  .combo-card-header .campus-badge {
    background: rgba(124, 58, 237, .35);
    color: #e9d5ff;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    letter-spacing: 0.4px;
  }

  .combo-card-header h6 {
    color: white;
    font-weight: 700;
    font-size: 15px;
    margin: 8px 0 0;
    line-height: 1.3;
  }

  .combo-card-header small {
    color: rgba(255, 255, 255, .6);
    font-size: 11px;
  }

  .combo-count-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255, 255, 255, .15);
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
  }

  .combo-list {
    padding: 16px 20px;
    background: white;
  }

  .combo-subject-label {
    font-size: 12px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 10px;
  }

  .combo-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    border: 1px solid #f0f0f5;
    border-radius: 10px;
    padding: 8px 12px;
    margin-bottom: 6px;
    transition: background .15s;
  }

  .combo-pill:hover {
    background: #f0eeff;
    border-color: #c4b5fd;
  }

  .combo-pill .pill-text {
    font-size: 13px;
    color: #374151;
    font-weight: 500;
  }

  .combo-pill .pill-code {
    font-size: 11px;
    color: #9ca3af;
    margin-left: 6px;
  }

  .combo-pill .remove-btn {
    color: #dc2626;
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 13px;
    transition: background .15s, color .15s;
    flex-shrink: 0;
    margin-left: 8px;
  }

  .combo-pill .remove-btn:hover {
    background: #fee2e2;
    color: #b91c1c;
  }

  .combo-card-footer {
    padding: 12px 20px;
    background: #fafafa;
    border-top: 1px solid #f0f0f5;
  }

  .btn-add-more {
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #5b4cdb;
    border: none;
    border-radius: 10px;
    padding: 8px 0;
    font-size: 13px;
    font-weight: 600;
    width: 100%;
    transition: opacity .2s;
  }

  .btn-add-more:hover {
    opacity: .85;
    color: #4c1d95;
  }

  /* ── Empty state ── */
  .empty-state {
    background: white;
    border-radius: 20px;
    padding: 64px 32px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
  }

  .empty-icon {
    width: 88px;
    height: 88px;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  /* ── Modals ── */
  .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
  }

  .modal-grad-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 100%);
    padding: 24px;
    border-radius: 0;
  }

  .form-label-modern {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }



  .form-select-modern:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
  }

  .btn-save-combo {
    background: linear-gradient(135deg, #5b4cdb, #7c3aed);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 28px;
    font-weight: 700;
    font-size: 14px;
    transition: opacity .2s;
  }

  .btn-save-combo:hover {
    opacity: .9;
    color: white;
  }
</style>

<div class="main-content combo-page">

  <!-- Hero Header -->
  <div class="combo-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <div style="font-size:12px; font-weight:700; color:rgba(255,255,255,.6); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
          <i class="fas fa-layer-group me-1"></i>Combination Master
        </div>
        <h2 style="font-size:26px; font-weight:800; margin:0 0 6px; color:white;">{{ $subject->title ?? '–' }}</h2>
        <span style="font-size:13px; color:rgba(255,255,255,.65);">
          <i class="fas fa-code me-1"></i>{{ $subject->code ?? '–' }}
          &nbsp;&bull;&nbsp; Subject Combination Manager
        </span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="stat-chip" style="background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.2); color:white;">
          <i class="fas fa-cubes me-1"></i>
          {{ $grouped->count() }} group{{ $grouped->count() != 1 ? 's' : '' }}
        </span>
        <a href="{{ route('department.dashboard') }}" class="btn" style="background:rgba(255,255,255,.15); color:white; border-radius:12px; padding:10px 20px; font-weight:600; font-size:13px; backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.2);">
          <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
        <button class="btn" style="background:white; color:#5b4cdb; border-radius:12px; padding:10px 20px; font-weight:700; font-size:13px;" data-bs-toggle="modal" data-bs-target="#addCombinationModal">
          <i class="fas fa-plus-circle me-2"></i>Add Combination
        </button>
      </div>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:14px; border:none; background:#d1fae5; color:#065f46;" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:14px; border:none; background:#fee2e2; color:#991b1b;" role="alert">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <!-- Filter Bar -->
  <form method="GET" action="{{ route('department.combo.master') }}" id="filterForm">
    <div class="filter-bar">
      <div style="font-size:14px; font-weight:700; color:#374151; white-space:nowrap;">
        <i class="fas fa-filter me-1" style="color:#7c3aed;"></i>Filter
      </div>
      <select name="batch_id" class="form-select" id="batchSelect" onchange="this.form.submit()">
        <option value="">All Batches</option>
        @foreach($batches as $batch)
        <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
          {{ $batch->batch_name }}
        </option>
        @endforeach
      </select>
      @if(request('batch_id'))
      <a href="{{ route('department.combo.master') }}" class="btn-clear">
        <i class="fas fa-times me-1"></i>Clear
      </a>
      <span class="stat-chip">
        Showing: {{ $batches->firstWhere('id', request('batch_id'))?->batch_name ?? '' }}
      </span>
      @endif
      <div class="ms-auto" style="font-size:13px; color:#9ca3af;">
        {{ $grouped->count() }} combination group{{ $grouped->count() != 1 ? 's' : '' }} found
      </div>
    </div>
  </form>

  <!-- Combination Cards -->
  @if($grouped->count() > 0)
  <div class="row g-4">
    @foreach($grouped as $groupKey => $groupRows)
    @php $first = $groupRows->first(); @endphp
    <div class="col-12 col-md-6 col-xl-4">
      <div class="combo-card card">

        <!-- Card Header -->
        <div class="combo-card-header">
          <span class="combo-count-badge">
            {{ $groupRows->count() }} combo{{ $groupRows->count() != 1 ? 's' : '' }}
          </span>
          <div class="d-flex gap-2 flex-wrap mb-2">
            <span class="batch-badge">{{ $first->batch->batch_name ?? '–' }}</span>
            <span class="campus-badge">
              <i class="fas fa-map-marker-alt me-1" style="font-size:10px;"></i>{{ $first->campus->name ?? '–' }}
            </span>
          </div>
          <h6>{{ $first->mainSubject->title ?? '–' }}</h6>
          @if($first->mainSubject && $first->mainSubject->code)
          <small>{{ $first->mainSubject->code }}</small>
          @endif
        </div>

        <!-- Combo List -->
        <div class="combo-list flex-grow-1">
          <div class="combo-subject-label">
            <i class="fas fa-link me-1"></i>Combo Subjects
          </div>
          @foreach($groupRows as $row)
          <div class="combo-pill">
            <div class="d-flex align-items-center flex-grow-1 min-w-0">
              <span class="pill-text text-truncate">{{ $row->comboSubject->title ?? '–' }}</span>
              @if($row->comboSubject && $row->comboSubject->code)
              <span class="pill-code">({{ $row->comboSubject->code }})</span>
              @endif
            </div>
            <button class="remove-btn"
              onclick="if(confirm('Remove this combo subject?')) window.location='{{ url('erp/admin/master/delete-subject-combination/'.$row->id) }}';"
              title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
          @endforeach
        </div>

        <!-- Card Footer -->
        <div class="combo-card-footer">
          <button class="btn btn-add-more"
            data-bs-toggle="modal"
            data-bs-target="#addMoreModal_{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}">
            <i class="fas fa-plus me-1"></i>Add More Combos
          </button>
        </div>
      </div>
    </div>

    <!-- Per-Card Add More Modal -->
    <div class="modal fade" id="addMoreModal_{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-grad-header">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div style="font-size:11px; color:rgba(255,255,255,.6); font-weight:700; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Add More Combos</div>
                <h5 style="color:white; font-weight:700; margin:0; font-size:16px;">{{ $first->mainSubject->title ?? '' }}</h5>
                <div class="d-flex gap-2 mt-2">
                  <span style="font-size:11px; background:rgba(255,255,255,.15); color:white; padding:2px 10px; border-radius:20px;">{{ $first->batch->batch_name ?? '' }}</span>
                  <span style="font-size:11px; background:rgba(255,255,255,.1); color:rgba(255,255,255,.8); padding:2px 10px; border-radius:20px;">{{ $first->campus->name ?? '' }}</span>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
            </div>
          </div>
          <form action="{{ url('erp/admin/master/subject-combination') }}" method="POST">
            @csrf
            <input type="hidden" name="batch_id" value="{{ $first->batch_id }}">
            <input type="hidden" name="campus_id" value="{{ $first->campus_id }}">
            <input type="hidden" name="main_subject_id" value="{{ $first->main_subject_id }}">
            <div class="modal-body p-4">
              <label class="form-label-modern">Select Combo Subject(s) <span class="text-danger">*</span></label>
              @php $existingIds = $groupRows->pluck('combo_subject_id')->toArray(); @endphp
              <select name="combo_subject_ids[]" class="form-select form-select-modern" multiple required style="min-height:150px;">
                @foreach($subjects as $subj)
                @if($subj->id != $first->main_subject_id)
                <option value="{{ $subj->id }}" {{ in_array($subj->id, $existingIds) ? 'disabled' : '' }}>
                  {{ $subj->title }}@if($subj->code) ({{ $subj->code }})@endif{{ in_array($subj->id, $existingIds) ? ' — already added' : '' }}
                </option>
                @endif
                @endforeach
              </select>
              <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle me-1"></i>Hold <kbd>Ctrl</kbd> / <kbd>Cmd</kbd> to select multiple. Disabled = already exists.
              </small>
            </div>
            <div class="modal-footer" style="padding:20px 24px; border-top:1px solid #f0f0f0;">
              <button type="button" class="btn" style="border-radius:10px; border:1.5px solid #e5e7eb; color:#6b7280; padding:10px 20px; font-weight:600;" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-save-combo">
                <i class="fas fa-save me-1"></i>Add
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  @else
  <div class="empty-state">
    <div class="empty-icon">
      <i class="fas fa-layer-group fa-2x" style="color:#7c3aed;"></i>
    </div>
    <h5 style="color:#1a1a1a; font-weight:700; margin-bottom:8px;">No Combinations Found</h5>
    <p style="color:#6b7280; margin-bottom:24px;">
      @if(request('batch_id'))
      No combinations for the selected batch. Try clearing the filter or add a new one.
      @else
      No subject combinations defined yet for this department.
      @endif
    </p>
    <button class="btn btn-save-combo" data-bs-toggle="modal" data-bs-target="#addCombinationModal">
      <i class="fas fa-plus-circle me-2"></i>Add First Combination
    </button>
  </div>
  @endif

</div>

<!-- ═══════════════════════════════════════════════════════════
     Add Combination Modal (global)
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addCombinationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-grad-header">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div style="font-size:11px; color:rgba(255,255,255,.6); font-weight:700; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">
              <i class="fas fa-layer-group me-1"></i>New Combination
            </div>
            <h5 style="color:white; font-weight:700; margin:0;">{{ $subject->title ?? '' }}</h5>
          </div>
          <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <form action="{{ url('erp/admin/master/subject-combination') }}" method="POST">
        @csrf
        <input type="hidden" name="main_subject_id" value="{{ $subject->id }}">
        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label-modern">Academic Batch <span class="text-danger">*</span></label>
              <select name="batch_id" class="form-select form-select-modern" required>
                <option value="">-- Select Batch --</option>
                @foreach($batches as $batch)
                <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <label class="form-label-modern mb-2">Combo Subject(s) <span class="text-danger">*</span></label>


          <div id="comboCheckboxList" style="max-height:220px; overflow-y:auto; border:1.5px solid #e5e7eb; border-radius:12px; padding:8px 4px;">
            @foreach($subjects as $subj)
            @if($subj->id != $subject->id)
            <label class="combo-checkbox-item d-flex align-items-center gap-2 px-3 py-2 rounded" data-label="{{ strtolower($subj->title . ' ' . ($subj->code ?? '')) }}" style="cursor:pointer; margin:2px 0; transition:background .15s;">
              <input type="checkbox" name="combo_subject_ids[]" value="{{ $subj->id }}" data-title="{{ $subj->code ?? $subj->title }}" class="combo-chk form-check-input m-0" style="width:17px;height:17px;cursor:pointer;accent-color:#7c3aed;">
              <span style="font-size:13px; color:#374151; font-weight:500;">{{ $subj->title }}</span>
              @if($subj->code)
              <span style="font-size:11px; color:#9ca3af; margin-left:2px;">({{ $subj->code }})</span>
              @endif
            </label>
            @endif
            @endforeach
          </div>
          <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Check one or more subjects to combine.</small>

          <!-- Live preview -->
          <div id="comboPreview" class="mt-3" style="display:none;">
            <div style="font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
              <i class="fas fa-eye me-1"></i>Preview
            </div>
            <div id="comboPreviewTags" class="d-flex flex-wrap gap-2"></div>
          </div>
        </div>
        <div class="modal-footer" style="padding:20px 24px; border-top:1px solid #f0f0f0;">
          <button type="button" class="btn" style="border-radius:10px; border:1.5px solid #e5e7eb; color:#6b7280; padding:10px 20px; font-weight:600;" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-save-combo">
            <i class="fas fa-save me-2"></i>Save Combination
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<span id="mainSubjectTitle" data-title="{{ $subject->code ?? $subject->title ?? '' }}" style="display:none;"></span>

<script>
  (function() {
    const mainTitle = document.getElementById('mainSubjectTitle').dataset.title;
    const preview = document.getElementById('comboPreview');
    const tagsEl = document.getElementById('comboPreviewTags');

    // Checkbox change → update preview
    document.querySelectorAll('.combo-chk').forEach(function(chk) {
      chk.addEventListener('change', updatePreview);
    });

    function updatePreview() {
      const checked = Array.from(document.querySelectorAll('.combo-chk:checked'));
      tagsEl.innerHTML = '';

      if (checked.length === 0) {
        preview.style.display = 'none';
        return;
      }

      preview.style.display = 'block';
      checked.forEach(function(chk) {
        const tag = document.createElement('span');
        tag.style.cssText = 'background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#4c1d95;border-radius:20px;padding:5px 14px;font-size:13px;font-weight:700;white-space:nowrap;';
        tag.textContent = mainTitle + ' + ' + chk.dataset.title;
        tagsEl.appendChild(tag);
      });
    }

    // Hover highlight
    document.querySelectorAll('.combo-checkbox-item').forEach(function(item) {
      item.addEventListener('mouseenter', function() {
        this.style.background = '#f0eeff';
      });
      item.addEventListener('mouseleave', function() {
        this.style.background = '';
      });
    });

    // Search filter
    document.getElementById('comboSearchInput').addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      document.querySelectorAll('.combo-checkbox-item').forEach(function(item) {
        const label = (item.dataset.label || '').toLowerCase();
        item.style.display = label.includes(q) ? '' : 'none';
      });
    });

  })();
</script>

@include('includes.footer')
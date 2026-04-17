@include('includes.header')
@include('admin.sidebar')

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h4 class="fw-bold mb-0"><i class="fa fa-object-group text-primary me-2"></i>Subject Combination Master</h4>
    <small class="text-muted">Define valid subject combinations per batch and campus</small>
  </div>

</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fa fa-times-circle me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif



<!-- Grouped Records -->
@if($grouped->count())
<div class="row g-3">
  @foreach($grouped as $groupKey => $groupRows)
  @php $first = $groupRows->first(); @endphp
  <div class="col-12 col-md-6 col-xl-4">
    <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;overflow:hidden">

      <!-- Card Header -->
      <div class="px-3 pt-3 pb-2" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%)">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-primary mb-1">{{ $first->batch->batch_name ?? '-' }}</span>
            <span class="badge bg-secondary ms-1 mb-1">{{ $first->campus->name ?? '-' }}</span>
            <h6 class="text-white fw-bold mb-0 mt-1" style="font-size:.95rem">{{ $first->mainSubject->title ?? '-' }}</h6>
            @if($first->mainSubject && $first->mainSubject->code)
            <small class="text-white-50 text-uppercase">{{ $first->mainSubject->code }}</small>
            @endif
          </div>
          <span class="badge" style="background:rgba(255,255,255,.15);font-size:.8rem">
            {{ $groupRows->count() }} combo{{ $groupRows->count() != 1 ? 's' : '' }}
          </span>
        </div>
      </div>

      <!-- Combo Subjects List -->
      <div class="card-body px-3 py-2">
        <div class="mb-2">
          <span class="fw-semibold text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">
            <i class="fa fa-link me-1"></i>Combo Subjects
          </span>
        </div>
        @foreach($groupRows as $row)
        <div class="d-flex align-items-center justify-content-between py-1" style="border-bottom:1px dashed #e9ecef">
          <div>
            <span class="fw-semibold" style="font-size:.875rem">{{ $row->comboSubject->title ?? '-' }}</span>
            @if($row->comboSubject && $row->comboSubject->code)
            <small class="text-muted text-uppercase ms-1">({{ $row->comboSubject->code }})</small>
            @endif
          </div>
          <a href="{{ url('erp/admin/master/delete-subject-combination/'.$row->id) }}"
            class="btn btn-sm btn-link text-danger p-0 ms-2"
            title="Remove this combo"
            onclick="return confirm('Remove this combo subject?')">
            <i class="fa fa-times-circle"></i>
          </a>
        </div>
        @endforeach
      </div>

      <!-- Add more combos footer -->
      <!-- <div class="card-footer bg-white border-top px-3 py-2">
        <button class="btn btn-sm btn-outline-primary w-100"
          data-bs-toggle="modal"
          data-bs-target="#addMoreModal{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}">
          <i class="fa fa-plus me-1"></i>Add More Combos
        </button>
      </div> -->
    </div>
  </div>

  <!-- Add More Combos Modal -->
  <div class="modal fade" id="addMoreModal{{ $first->batch_id }}_{{ $first->campus_id }}_{{ $first->main_subject_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="font-size:.95rem">
            <i class="fa fa-plus-circle text-primary me-2"></i>Add Combos &mdash;
            <strong>{{ $first->mainSubject->title ?? '-' }}</strong>
            <span class="badge bg-primary ms-1">{{ $first->batch->batch_name ?? '' }}</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ url('erp/admin/master/subject-combination') }}" method="POST">
          @csrf
          <input type="hidden" name="batch_id" value="{{ $first->batch_id }}">
          <input type="hidden" name="campus_id" value="{{ $first->campus_id }}">
          <input type="hidden" name="main_subject_id" value="{{ $first->main_subject_id }}">
          <div class="modal-body">
            <label class="form-label fw-semibold">Select Combo Subject(s) <span class="text-danger">*</span></label>
            @php $existingIds = $groupRows->pluck('combo_subject_id')->toArray(); @endphp
            <select name="combo_subject_ids[]" class="form-select select-multiple" multiple required style="min-height:130px">
              @foreach($subjects as $subject)
              @if($subject->id != $first->main_subject_id)
              <option value="{{ $subject->id }}" {{ in_array($subject->id, $existingIds) ? 'disabled' : '' }}>
                {{ $subject->title }}@if($subject->code) ({{ $subject->code }})@endif{{ in_array($subject->id, $existingIds) ? ' — already added' : '' }}
              </option>
              @endif
              @endforeach
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i>Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endforeach
</div>
@else
<div class="text-center py-5">
  <i class="fa fa-object-group fa-3x text-muted mb-3 d-block"></i>
  <p class="text-muted fs-5 mb-0">No subject combinations found</p>
  <small class="text-muted">Click "Add Combination" to get started</small>
</div>
@endif

@include('includes.footer')
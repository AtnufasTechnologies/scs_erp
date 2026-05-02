@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">
    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
  </ul>
</div>
@endif

<div class="row g-3">
  <div class="col-12">
    <label class="form-label fw-semibold">Event Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
      value="{{ old('title', $event->title ?? '') }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Description</label>
    <textarea name="description" rows="3"
      class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description ?? '') }}</textarea>
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
      value="{{ old('start_date', isset($event) ? $event->start_date->format('Y-m-d') : '') }}" required>
    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
      value="{{ old('end_date', isset($event) ? $event->end_date->format('Y-m-d') : '') }}" required>
    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-8">
    <label class="form-label fw-semibold">Venue</label>
    <input type="text" name="venue" class="form-control"
      value="{{ old('venue', $event->venue ?? '') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">Total Budget (₹) <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="total_budget" min="0"
      class="form-control @error('total_budget') is-invalid @enderror"
      value="{{ old('total_budget', $event->total_budget ?? 0) }}" required>
    @error('total_budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
      @foreach(['draft','active','completed','cancelled'] as $s)
      <option value="{{ $s }}" {{ old('status', $event->status ?? 'draft') === $s ? 'selected' : '' }}>
        {{ ucfirst($s) }}
      </option>
      @endforeach
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Banner Image</label>
    <input type="file" name="banner_image" accept="image/*" class="form-control">
    @if(!empty($event->banner_image))
    <small class="text-muted">Current: <a href="{{ Storage::url($event->banner_image) }}" target="_blank">View</a></small>
    @endif
  </div>
</div>
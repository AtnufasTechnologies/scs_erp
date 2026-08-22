@php
$isEdit = isset($category);
$selectedRoleIds = collect(old('role_ids', $isEdit ? $category->roles->pluck('id')->all() : []))->map(fn($id) => (int) $id)->all();
$componentsFromOld = old('components');
$seedComponents = is_array($componentsFromOld)
? array_values($componentsFromOld)
: ($isEdit ? $category->components->map(function ($component) {
return [
'title' => $component->title,
'score' => $component->score,
'verifier_role_master_id' => (int) ($component->verifier_role_master_id ?? 0),
'is_active' => (int) $component->is_active,
];
})->toArray() : []);

if (empty($seedComponents)) {
$seedComponents = [[
'title' => '',
'score' => '',
'verifier_role_master_id' => '',
'is_active' => 1,
]];
}
@endphp

<style>
  .api-metrix-form {
    --amx-accent: #0f766e;
    --amx-soft: #f0fdfa;
  }

  .api-metrix-form .section-block {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid #e8edf3;
    border-radius: 12px;
    padding: 14px;
  }

  .api-metrix-form .section-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .api-metrix-form .section-title .dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: var(--amx-accent);
    box-shadow: 0 0 0 4px var(--amx-soft);
  }

  .api-metrix-form .form-label {
    font-weight: 600;
    color: #374151;
  }

  .api-metrix-form .role-item {
    border: 1px solid #dbe3ec;
    border-radius: 10px;
    padding: 0;
    background: #fff;
    transition: all 0.2s ease;
    overflow: hidden;
    position: relative;
  }

  .api-metrix-form .role-item:hover {
    border-color: #b9ccd7;
    box-shadow: 0 2px 8px rgba(17, 24, 39, 0.06);
  }

  .api-metrix-form .role-check {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .api-metrix-form .role-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
    margin: 0;
    cursor: pointer;
    padding: 10px 12px;
    font-size: 0.88rem;
    color: #1f2937;
  }

  .api-metrix-form .role-label .tick {
    width: 18px;
    height: 18px;
    border-radius: 999px;
    border: 1px solid #c7d2fe;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    color: #fff;
    background: #fff;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  .api-metrix-form .role-check:checked+.role-label {
    background: #ecfeff;
    color: #0f172a;
    font-weight: 600;
  }

  .api-metrix-form .role-check:checked+.role-label .tick {
    background: var(--amx-accent);
    border-color: var(--amx-accent);
  }

  .api-metrix-form .role-check:focus-visible+.role-label {
    outline: 2px solid #7dd3fc;
    outline-offset: -2px;
  }

  .api-metrix-form .roles-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
  }

  .api-metrix-form .selected-role-count {
    font-size: 0.78rem;
    color: #334155;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    border-radius: 999px;
    padding: 2px 10px;
  }

  .api-metrix-form .component-row {
    background: #fff;
    border: 1px solid #dde6ee !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
  }

  .api-metrix-form .component-row .form-check-label {
    font-size: 0.82rem;
  }

  .api-metrix-form .component-tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
  }

  .api-metrix-form .component-count {
    font-size: 0.8rem;
    color: #475569;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 999px;
    padding: 2px 10px;
  }
</style>

<div class="row g-3 api-metrix-form">
  <div class="col-md-4">
    <label class="form-label">Category Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $isEdit ? $category->title : '') }}" placeholder="Example: Faculty Academic Contributions" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label">Slug</label>
    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $isEdit ? ($category->slug ?? '') : '') }}" placeholder="auto-from-title-if-empty">
    <div class="form-text">Unique URL key. Leave blank to auto-generate.</div>
    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-2">
    <label class="form-label">Status <span class="text-danger">*</span></label>
    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
      <option value="active" {{ old('status', $isEdit ? $category->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
      <option value="inactive" {{ old('status', $isEdit ? $category->status : 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-2">
    <label class="form-label">Visible In WorkDiary</label>
    <select name="show_in_workdiary" class="form-select @error('show_in_workdiary') is-invalid @enderror">
      <option value="1" {{ (string) old('show_in_workdiary', $isEdit ? (int) $category->show_in_workdiary : 1) === '1' ? 'selected' : '' }}>Yes</option>
      <option value="0" {{ (string) old('show_in_workdiary', $isEdit ? (int) $category->show_in_workdiary : 1) === '0' ? 'selected' : '' }}>No</option>
    </select>
    @error('show_in_workdiary')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3">
    <label class="form-label">Components</label>
    <input type="text" id="componentCountInput" class="form-control" value="{{ count($seedComponents) }} configured" readonly>
  </div>

  <div class="col-12 section-block">
    <div class="section-title"><span class="dot"></span>Category Notes</div>
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional notes about this category">{{ old('description', $isEdit ? $category->description : '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-12 section-block">
    <div class="section-title"><span class="dot"></span>Applicable To Role Types</div>
    <div class="roles-meta">
      <label class="form-label mb-0">Applicable Roles <span class="text-danger">*</span></label>
      <span class="selected-role-count" id="selectedRoleCount">0 selected</span>
    </div>
    <div class="row g-2">
      @forelse($roles as $role)
      <div class="col-md-3 col-sm-4 col-6">
        <div class="form-check role-item">
          <input class="form-check-input role-check" type="checkbox" name="role_ids[]" id="role_{{ $role->id }}" value="{{ $role->id }}" {{ in_array((int) $role->id, $selectedRoleIds, true) ? 'checked' : '' }}>
          <label class="form-check-label role-label" for="role_{{ $role->id }}">
            <span>{{ $role->role_name }}</span>
            <span class="tick"><i class="fas fa-check"></i></span>
          </label>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="alert alert-warning mb-0">No roles found in role_masters.</div>
      </div>
      @endforelse
    </div>
    @error('role_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('role_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
  </div>

  <div class="col-12 section-block">
    <div class="section-title"><span class="dot"></span>Scoring Components</div>
    <div class="component-tools">
      <label class="form-label mb-0">Components <span class="text-danger">*</span></label>
      <span class="component-count" id="componentCountBadge">{{ count($seedComponents) }} rows</span>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addComponentRow">
        <i class="fas fa-plus me-1"></i>Add Component
      </button>
    </div>

    <div id="componentRows" class="d-flex flex-column gap-2">
      @foreach($seedComponents as $index => $component)
      <div class="row g-2 align-items-center component-row border rounded p-2">
        <div class="col-md-4">
          <input type="text" name="components[{{ $index }}][title]" class="form-control" placeholder="Component title" value="{{ $component['title'] ?? '' }}" required>
        </div>
        <div class="col-md-2">
          <input type="number" step="0.01" min="0" name="components[{{ $index }}][score]" class="form-control" placeholder="Score" value="{{ $component['score'] ?? '' }}" required>
        </div>
        <div class="col-md-4">
          <select name="components[{{ $index }}][verifier_role_master_id]" class="form-select dselect-multiple" required>
            <option value="">Verified By Role</option>
            @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ (string) ($component['verifier_role_master_id'] ?? '') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1">
          <div class="form-check">
            <input type="hidden" name="components[{{ $index }}][is_active]" value="0">
            <input type="checkbox" class="form-check-input" name="components[{{ $index }}][is_active]" value="1" {{ (int) ($component['is_active'] ?? 0) === 1 ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
          </div>
        </div>
        <div class="col-md-1 text-end">
          <button type="button" class="btn btn-sm btn-outline-danger remove-component-row" title="Remove">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      @endforeach
    </div>

    @error('components')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('components.*.title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('components.*.score')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('components.*.verifier_role_master_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
  </div>
</div>

<template id="componentRowTemplate">
  <div class="row g-2 align-items-center component-row border rounded p-2">
    <div class="col-md-4">
      <input type="text" data-name="title" class="form-control" placeholder="Component title" required>
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" min="0" data-name="score" class="form-control" placeholder="Score" required>
    </div>
    <div class="col-md-4">
      <select data-name="verifier_role_master_id" class="form-select dselect-multiple" required>
        <option value="">Verification By Role</option>
        @foreach($roles as $role)
        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-1">
      <div class="form-check">
        <input type="hidden" data-name="is_active_hidden" value="0">
        <input type="checkbox" data-name="is_active" class="form-check-input" value="1" checked>
        <label class="form-check-label">Active</label>
      </div>
    </div>
    <div class="col-md-1 text-end">
      <button type="button" class="btn btn-sm btn-outline-danger remove-component-row" title="Remove">
        <i class="fas fa-trash"></i>
      </button>
    </div>
  </div>
</template>

<script>
  (function() {
    const rowsWrap = document.getElementById('componentRows');
    const addBtn = document.getElementById('addComponentRow');
    const template = document.getElementById('componentRowTemplate');
    const countInput = document.getElementById('componentCountInput');
    const countBadge = document.getElementById('componentCountBadge');
    const selectedRoleCount = document.getElementById('selectedRoleCount');

    function updateSelectedRoleCount() {
      if (!selectedRoleCount) return;
      const selected = document.querySelectorAll('input[name="role_ids[]"]:checked').length;
      selectedRoleCount.textContent = `${selected} selected`;
    }

    function updateComponentCount() {
      const count = rowsWrap.querySelectorAll('.component-row').length;
      if (countInput) {
        countInput.value = `${count} configured`;
      }
      if (countBadge) {
        countBadge.textContent = `${count} rows`;
      }
    }

    function initDselectMultiple(root) {
      if (typeof window.dselect !== 'function') {
        return false;
      }

      root.querySelectorAll('select.dselect-multiple').forEach(function(selectEl) {
        if (selectEl.dataset.dselectApplied === '1') {
          return;
        }

        window.dselect(selectEl, {
          search: true,
          creatable: false,
          clearable: true,
          maxHeight: '300px',
          size: 'sm',
        });

        selectEl.dataset.dselectApplied = '1';
      });

      return true;
    }

    function initDselectWhenReady(root, attempt) {
      const currentAttempt = Number.isInteger(attempt) ? attempt : 0;
      const initialized = initDselectMultiple(root);

      if (initialized || currentAttempt >= 40) {
        return;
      }

      setTimeout(function() {
        initDselectWhenReady(root, currentAttempt + 1);
      }, 100);
    }

    function reindexRows() {
      const rows = rowsWrap.querySelectorAll('.component-row');
      rows.forEach((row, idx) => {
        const titleInput = row.querySelector('[data-name="title"]') || row.querySelector('input[name*="[title]"]');
        const scoreInput = row.querySelector('[data-name="score"]') || row.querySelector('input[name*="[score]"]');
        const verifierRoleSelect = row.querySelector('[data-name="verifier_role_master_id"]') || row.querySelector('select[name*="[verifier_role_master_id]"]');
        const activeHidden = row.querySelector('[data-name="is_active_hidden"]') || row.querySelector('input[type="hidden"][name*="[is_active]"]');
        const activeCheckbox = row.querySelector('[data-name="is_active"]') || row.querySelector('input[type="checkbox"][name*="[is_active]"]');

        if (titleInput) titleInput.name = `components[${idx}][title]`;
        if (scoreInput) scoreInput.name = `components[${idx}][score]`;
        if (verifierRoleSelect) verifierRoleSelect.name = `components[${idx}][verifier_role_master_id]`;
        if (activeHidden) activeHidden.name = `components[${idx}][is_active]`;
        if (activeCheckbox) activeCheckbox.name = `components[${idx}][is_active]`;
      });
      updateComponentCount();
    }

    addBtn.addEventListener('click', function() {
      const node = template.content.firstElementChild.cloneNode(true);
      rowsWrap.appendChild(node);
      reindexRows();
      initDselectWhenReady(node, 0);
    });

    rowsWrap.addEventListener('click', function(e) {
      const removeBtn = e.target.closest('.remove-component-row');
      if (!removeBtn) return;
      const rows = rowsWrap.querySelectorAll('.component-row');
      if (rows.length <= 1) {
        return;
      }
      removeBtn.closest('.component-row').remove();
      reindexRows();
    });

    document.addEventListener('change', function(e) {
      if (e.target && e.target.matches('input[name="role_ids[]"]')) {
        updateSelectedRoleCount();
      }
    });

    reindexRows();
    updateSelectedRoleCount();

    document.addEventListener('DOMContentLoaded', function() {
      initDselectWhenReady(document, 0);
    });

    window.addEventListener('load', function() {
      initDselectWhenReady(document, 0);
    });

    initDselectWhenReady(document, 0);
  })();
</script>
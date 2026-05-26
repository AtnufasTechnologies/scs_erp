@include('includes.header')
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid">

    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1a1a1a;">
              <i class="fas fa-tags me-2" style="color: #5b4cdb;"></i>Leave Category Master
            </h3>
            <p class="text-muted mb-0">Manage leave types and their allotted days</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
              <i class="fas fa-plus me-1"></i>Add Category
            </button>
            <a href="{{ route('department.leave.index') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-1"></i>Back to Leave
            </a>
          </div>
        </div>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      @foreach($errors->all() as $error)
      {{ $error }}<br>
      @endforeach
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Categories Table -->
    <div class="card shadow-sm" style="border-radius: 16px; border: none;">
      <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
        <h5 class="mb-0 fw-bold">
          <i class="fas fa-list me-2" style="color: #5b4cdb;"></i>Leave Categories
          <span class="badge ms-2" style="background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); font-size: 13px; border-radius: 8px;">
            {{ $categories->count() }}
          </span>
        </h5>
      </div>
      <div class="card-body p-0">
        @if($categories->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead style="background: #f9fafb;">
              <tr>
                <th style="padding: 14px 16px; color: #6b7280; font-weight: 600; font-size: 13px;">#</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Leave Title</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Code</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Allotted Days/Year</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Attachment Required</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Status</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Description</th>

              </tr>
            </thead>
            <tbody>
              @foreach($categories as $index => $cat)
              <tr>
                <td style="padding: 14px 16px;">{{ $index + 1 }}</td>
                <td>
                  <span class="badge bg-{{ $cat->badge_color }} me-1" style="font-size: 13px;">{{ $cat->leave_type_name }}</span>
                </td>
                <td><code>{{ $cat->leave_type_code }}</code></td>
                <td>
                  @if($cat->allowed_days_per_year)
                  <span class="fw-bold">{{ $cat->allowed_days_per_year }}</span> days
                  @else
                  <span class="text-muted">Unlimited</span>
                  @endif
                </td>
                <td>
                  @if($cat->requires_attachment)
                  <span class="badge bg-warning text-dark"><i class="fas fa-paperclip me-1"></i>Yes</span>
                  @else
                  <span class="text-muted">No</span>
                  @endif
                </td>
                <td>
                  <form action="{{ route('department.leave.categories.toggle', $cat->id) }}" method="POST" class="d-inline">
                    @csrf
                    @if($cat->is_active)
                    <button type="submit" class="badge bg-success border-0" style="cursor: pointer;">Active</button>
                    @else
                    <button type="submit" class="badge bg-secondary border-0" style="cursor: pointer;">Inactive</button>
                    @endif
                  </form>
                </td>
                <td>
                  <small class="text-muted">{{ Str::limit($cat->description, 40) }}</small>
                </td>

              </tr>

              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-tags text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">No leave categories found. Add your first category.</p>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 16px;">
      <div class="modal-header border-0">
        <h5 class="modal-title"><i class="fas fa-plus-circle text-primary me-2"></i>Add Leave Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('department.leave.categories.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Leave Title <span class="text-danger">*</span></label>
            <input type="text" name="leave_type_name" class="form-control" required placeholder="e.g., Casual Leave">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
            <input type="text" name="leave_type_code" class="form-control" required placeholder="e.g., CL" style="text-transform: uppercase;">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Allotted Days Per Year</label>
            <input type="number" name="allowed_days_per_year" class="form-control" min="0" placeholder="Leave empty for unlimited">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this leave type"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Badge Color</label>
            <select name="badge_color" class="form-select">
              <option value="primary">Primary (Blue)</option>
              <option value="success">Success (Green)</option>
              <option value="danger">Danger (Red)</option>
              <option value="warning">Warning (Yellow)</option>
              <option value="info">Info (Cyan)</option>
              <option value="secondary">Secondary (Gray)</option>
              <option value="dark">Dark</option>
            </select>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="requires_attachment" value="1" id="addAttachment">
            <label class="form-check-label" for="addAttachment">Requires Attachment (e.g., Medical Certificate)</label>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')
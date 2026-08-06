@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Placement Management</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Placement</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Placement Management</h4>
              <p class="text-muted mb-0">Catalog internships, apprenticeships, placements, and project opportunities with academic applicability details.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-pie me-1"></i>Dashboard
              </a>
              <a href="{{ route('tpo.training-placement.index') }}" class="btn btn-primary">
                <i class="fas fa-chalkboard-teacher me-1"></i>Go to Training
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-5">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">Add Catalog Item</h6>
            </div>
            <div class="card-body">
              <form action="{{ route('tpo.training-placement.placement.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                  <label class="form-label fw-semibold">Title</label>
                  <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-2">
                  <label class="form-label fw-semibold">Category</label>
                  <select name="category" class="form-select" required>
                    <option value="" selected disabled>Select category</option>
                    @foreach($categoryOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Month</label>
                    <select name="month" class="form-select" required>
                      <option value="" selected disabled>Select month</option>
                      @foreach($monthOptions as $monthValue => $monthLabel)
                      <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Applicable Student Year</label>
                    <select name="student_year" class="form-select" required>
                      <option value="" selected disabled>Select year</option>
                      @foreach($yearOptions as $year)
                      <option value="{{ $year }}">{{ $year }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label fw-semibold">Description</label>
                  <textarea name="description" rows="3" class="form-control" required></textarea>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Location</label>
                    <input type="text" name="location" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Country (Optional)</label>
                    <input type="text" name="country" class="form-control">
                  </div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Department / Subject</label>
                    <select name="subject_id" class="form-select" required>
                      <option value="" selected disabled>Select department</option>
                      @foreach($subjects as $subject)
                      <option value="{{ $subject->id }}">{{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Logo (Optional)</label>
                    <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                  </div>
                </div>
                <div class="row g-2 mb-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Company (Optional)</label>
                    <input type="text" name="company_name" class="form-control">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Drive Date</label>
                    <input type="date" name="drive_date" class="form-control">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Apply Deadline</label>
                    <input type="date" name="apply_deadline" class="form-control">
                  </div>
                </div>
                <button class="btn btn-primary" type="submit">Create Catalog Item</button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-xl-7">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent">
              <h6 class="mb-0 fw-bold">Catalog Listings</h6>
            </div>
            <div class="card-body">
              @forelse($placements as $placement)
              <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                  <div class="w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                      <h6 class="mb-0 fw-bold">{{ $placement->title }}</h6>
                      <span class="badge bg-info text-dark">{{ $categoryOptions[$placement->category] ?? ucfirst($placement->category ?? 'N/A') }}</span>
                      <span class="badge bg-secondary">{{ $monthOptions[$placement->month] ?? 'Month N/A' }}</span>
                      @if($placement->is_active)
                      <span class="badge bg-success">Active</span>
                      @else
                      <span class="badge bg-secondary">Inactive</span>
                      @endif
                    </div>

                    @if($placement->logo_path)
                    <div class="mb-2">
                      <img src="{{ Storage::disk('s3')->url($placement->logo_path) }}" alt="logo" style="height: 42px; width: auto; border-radius: 6px;">
                    </div>
                    @endif

                    <p class="mb-1">{{ $placement->description }}</p>
                    <div class="small text-muted mb-1">Location: {{ $placement->location }}{{ $placement->country ? ', ' . $placement->country : '' }}</div>
                    <div class="small text-muted mb-1">Applicable Year: {{ $placement->student_year }}</div>
                    <div class="small text-muted mb-1">Department: {{ $placement->subject->title ?? $placement->subject->name ?? 'N/A' }}</div>
                    <div class="small text-muted mb-1">Company: {{ $placement->company_name ?: 'N/A' }}</div>
                    <div class="small text-muted mb-1">Drive: {{ $placement->drive_date ? $placement->drive_date->format('d M Y') : 'N/A' }} | Deadline: {{ $placement->apply_deadline ? $placement->apply_deadline->format('d M Y') : 'N/A' }}</div>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#placementManage{{ $placement->id }}">Edit</button>
                    <form action="{{ route('tpo.training-placement.placement.destroy', $placement->id) }}" method="POST" onsubmit="return confirm('Delete this item?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </div>
                </div>

                <div class="collapse mt-3" id="placementManage{{ $placement->id }}">
                  <form action="{{ route('tpo.training-placement.placement.update', $placement->id) }}" method="POST" enctype="multipart/form-data" class="border rounded p-3">
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Title</label>
                        <input class="form-control" name="title" value="{{ $placement->title }}" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select" required>
                          @foreach($categoryOptions as $value => $label)
                          <option value="{{ $value }}" {{ $placement->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Month</label>
                        <select name="month" class="form-select" required>
                          @foreach($monthOptions as $monthValue => $monthLabel)
                          <option value="{{ $monthValue }}" {{ (int) $placement->month === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Applicable Student Year</label>
                        <select name="student_year" class="form-select" required>
                          @foreach($yearOptions as $year)
                          <option value="{{ $year }}" {{ $placement->student_year === $year ? 'selected' : '' }}>{{ $year }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="2" required>{{ $placement->description }}</textarea>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input class="form-control" name="location" value="{{ $placement->location }}" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Country (Optional)</label>
                        <input class="form-control" name="country" value="{{ $placement->country }}">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Department / Subject</label>
                        <select name="subject_id" class="form-select" required>
                          @foreach($subjects as $subject)
                          <option value="{{ $subject->id }}" {{ (int) $placement->subject_id === (int) $subject->id ? 'selected' : '' }}>{{ $subject->title ?? $subject->name ?? ('Subject #' . $subject->id) }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Logo (Optional)</label>
                        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Company (Optional)</label>
                        <input class="form-control" name="company_name" value="{{ $placement->company_name }}">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold">Drive Date</label>
                        <input type="date" class="form-control" name="drive_date" value="{{ $placement->drive_date ? $placement->drive_date->format('Y-m-d') : '' }}">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label fw-semibold">Apply Deadline</label>
                        <input type="date" class="form-control" name="apply_deadline" value="{{ $placement->apply_deadline ? $placement->apply_deadline->format('Y-m-d') : '' }}">
                      </div>
                      <div class="col-12">
                        <div class="form-check">
                          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activePlacement{{ $placement->id }}" {{ $placement->is_active ? 'checked' : '' }}>
                          <label class="form-check-label" for="activePlacement{{ $placement->id }}">Active</label>
                        </div>
                      </div>
                      <div class="col-12">
                        <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No placement catalog items added.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
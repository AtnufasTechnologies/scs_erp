@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Metrix</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-metrix.index') }}">Category Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card mt-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
          <div>
            <h4 class="mb-1">{{ $category->title }}</h4>
            <div class="text-muted">{{ $category->description ?: 'No description provided' }}</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('hr.api-metrix.edit', $category->id) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('hr.api-metrix.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <div class="col-md-3">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">Status</div>
              @if($category->status === 'active')
              <span class="badge bg-success mt-1">Active</span>
              @else
              <span class="badge bg-secondary mt-1">Inactive</span>
              @endif
            </div>
          </div>
          <div class="col-md-3">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">WorkDiary Visibility</div>
              @if((int) $category->show_in_workdiary === 1)
              <span class="badge bg-success mt-1">Visible</span>
              @else
              <span class="badge bg-secondary mt-1">Hidden</span>
              @endif
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Applicable Roles</div>
              @forelse($category->roles as $role)
              <span class="badge bg-light text-dark border me-1 mb-1">{{ $role->role_name }}</span>
              @empty
              <span class="text-muted small">No role mapping available.</span>
              @endforelse
            </div>
          </div>
        </div>

        <div class="mt-4">
          <h5 class="mb-3">Components (Click a row to view subcomponents)</h5>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width: 80px;">#</th>
                  <th>Component Title</th>
                  <th style="width: 140px;">Score</th>
                  <th style="width: 220px;">Verified By Role</th>
                  <th style="width: 140px;">Status</th>
                  <th style="width: 120px;">Subcomponents</th>
                </tr>
              </thead>
              <tbody>
                @forelse($category->components as $component)
                <tr role="button" data-bs-toggle="collapse" data-bs-target="#subcomponents-{{ $component->id }}" aria-expanded="false" aria-controls="subcomponents-{{ $component->id }}">
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="fw-semibold">{{ $component->title }}</span>
                    <div class="small text-muted">Click to expand</div>
                  </td>
                  <td>{{ number_format((float) $component->score, 2) }}</td>
                  <td>{{ optional($component->verifierRole)->role_name ?? '-' }}</td>
                  <td>
                    @if($component->is_active)
                    <span class="badge bg-success">Active</span>
                    @else
                    <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td><span class="badge bg-info text-dark">{{ $component->subcomponents->count() }}</span></td>
                </tr>

                <tr class="collapse" id="subcomponents-{{ $component->id }}">
                  <td colspan="6" class="bg-light">
                    <div class="p-2">
                      <h6 class="mb-3">Subcomponents for: {{ $component->title }}</h6>

                      <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered align-middle mb-3">
                          <thead>
                            <tr>
                              <th style="width: 60px;">#</th>
                              <th>Title</th>
                              <th style="width: 120px;">Score</th>
                              <th style="width: 200px;">Verified By Role</th>
                              <th style="width: 120px;">Status</th>
                              <th style="width: 360px;">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse($component->subcomponents as $subcomponent)
                            <tr>
                              <td>{{ $loop->iteration }}</td>
                              <td>{{ $subcomponent->title }}</td>
                              <td>{{ number_format((float) $subcomponent->score, 2) }}</td>
                              <td>{{ optional($subcomponent->verifierRole)->role_name ?? 'IQAC' }}</td>
                              <td>
                                @if($subcomponent->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                              </td>
                              <td>
                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                  <form action="{{ route('hr.api-metrix.subcomponents.update', $subcomponent->id) }}" method="POST" class="d-flex gap-1 align-items-center flex-wrap">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="title" class="form-control form-control-sm" style="width: 140px;" value="{{ $subcomponent->title }}" required>
                                    <input type="number" step="0.01" min="0" name="score" class="form-control form-control-sm" style="width: 90px;" value="{{ $subcomponent->score }}" required>
                                    <select name="verifier_role_master_id" class="form-select form-select-sm" style="width: 150px;">
                                      @foreach($roles as $role)
                                      <option value="{{ $role->id }}" {{ (string) ($subcomponent->verifier_role_master_id ?? $iqacRoleId) === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                      @endforeach
                                    </select>
                                    <select name="is_active" class="form-select form-select-sm" style="width: 95px;">
                                      <option value="1" {{ $subcomponent->is_active ? 'selected' : '' }}>Active</option>
                                      <option value="0" {{ !$subcomponent->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                  </form>

                                  <form action="{{ route('hr.api-metrix.subcomponents.destroy', $subcomponent->id) }}" method="POST" onsubmit="return confirm('Delete this subcomponent?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                  </form>
                                </div>
                              </td>
                            </tr>
                            @empty
                            <tr>
                              <td colspan="6" class="text-center text-muted">No subcomponents configured.</td>
                            </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>

                      <form action="{{ route('hr.api-metrix.subcomponents.store', $component->id) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-5">
                          <label class="form-label mb-1">Subcomponent Title</label>
                          <input type="text" name="title" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label mb-1">Score</label>
                          <input type="number" step="0.01" min="0" name="score" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label mb-1">Verified By</label>
                          <select name="verifier_role_master_id" class="form-select form-select-sm">
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ (string) $iqacRoleId === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label mb-1">Status</label>
                          <select name="is_active" class="form-select form-select-sm">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                          </select>
                        </div>
                        <div class="col-md-1">
                          <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-plus-circle me-1"></i>Add
                          </button>
                        </div>
                      </form>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center text-muted">No components configured.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')
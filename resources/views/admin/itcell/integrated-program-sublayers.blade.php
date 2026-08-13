@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">ITCELL Integrated Program Sublayers</h4>
  </div>

  @if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
      <h5 class="mb-0">Add Integrated Program Setting</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('itcell.integrated-sublayer-settings.store') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-5">
          <label class="form-label">Enrolled Program</label>
          <select name="student_program_id" class="dselect-example" required>
            <option value="">Select program</option>
            @foreach($programs as $program)
            <option value="{{ $program->id }}" {{ (string) old('student_program_id') === (string) $program->id ? 'selected' : '' }}>
              {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">UG Max Year</label>
          <input type="number" min="1" max="10" name="ug_max_year" class="form-control" value="{{ old('ug_max_year', 2) }}" required>
        </div>

        <div class="col-md-2">
          <label class="form-label">UG Label</label>
          <input type="text" name="ug_label" class="form-control" value="{{ old('ug_label', 'UG Layer') }}" placeholder="UG Layer">
        </div>

        <div class="col-md-2">
          <label class="form-label">PG Label</label>
          <input type="text" name="pg_label" class="form-control" value="{{ old('pg_label', 'PG Layer') }}" placeholder="PG Layer">
        </div>

        <div class="col-md-1">
          <label class="form-label">Active</label>
          <select name="is_active" class="form-select">
            <option value="1" selected>Yes</option>
            <option value="0">No</option>
          </select>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save Setting</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-light">
      <h5 class="mb-0">Configured Integrated Program Sublayers</h5>
    </div>
    <div class="card-body">
      @if($settings->isEmpty())
      <div class="alert alert-info mb-0">No integrated program settings configured yet.</div>
      @else
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 70px;">#</th>
              <th>Program</th>
              <th style="width: 130px;">UG Max Year</th>
              <th style="width: 170px;">UG Label</th>
              <th style="width: 170px;">PG Label</th>
              <th style="width: 120px;">Status</th>
              <th style="width: 270px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($settings as $index => $setting)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                {{ $setting->studentProgram?->code ? $setting->studentProgram->code . ' - ' : '' }}{{ $setting->studentProgram?->name ?? 'Unknown Program' }}
              </td>
              <td>{{ (int) $setting->ug_max_year }}</td>
              <td>{{ $setting->ug_label ?: 'UG Layer' }}</td>
              <td>{{ $setting->pg_label ?: 'PG Layer' }}</td>
              <td>
                <span class="badge {{ (int) $setting->is_active === 1 ? 'bg-success' : 'bg-secondary' }}">
                  {{ (int) $setting->is_active === 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-2 flex-wrap">
                  <form method="POST" action="{{ route('itcell.integrated-sublayer-settings.toggle', $setting->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning">
                      {{ (int) $setting->is_active === 1 ? 'Disable' : 'Enable' }}
                    </button>
                  </form>

                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editSetting{{ $setting->id }}">
                    Edit
                  </button>
                </div>

                <div class="collapse mt-2" id="editSetting{{ $setting->id }}">
                  <form method="POST" action="{{ route('itcell.integrated-sublayer-settings.update', $setting->id) }}" class="row g-2">
                    @csrf
                    @method('PUT')
                    <div class="col-md-3">
                      <input type="number" min="1" max="10" name="ug_max_year" class="form-control form-control-sm" value="{{ (int) $setting->ug_max_year }}" required>
                    </div>
                    <div class="col-md-3">
                      <input type="text" name="ug_label" class="form-control form-control-sm" value="{{ $setting->ug_label ?: 'UG Layer' }}" placeholder="UG Layer">
                    </div>
                    <div class="col-md-3">
                      <input type="text" name="pg_label" class="form-control form-control-sm" value="{{ $setting->pg_label ?: 'PG Layer' }}" placeholder="PG Layer">
                    </div>
                    <div class="col-md-2">
                      <select name="is_active" class="form-select form-select-sm" required>
                        <option value="1" {{ (int) $setting->is_active === 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (int) $setting->is_active === 0 ? 'selected' : '' }}>Inactive</option>
                      </select>
                    </div>
                    <div class="col-md-1">
                      <button type="submit" class="btn btn-sm btn-success w-100">Save</button>
                    </div>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>
</div>

@include('includes.footer')
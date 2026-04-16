@include('includes.header')
@include('admin.accounts.sidebar')
<h3>Account Office | Assistant Access Management</h3>

<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#createAssistantModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Create New Assistant</span>
  </div>
</button>



{{-- Create Assistant Modal --}}
<div class="modal fade" id="createAssistantModal" tabindex="-1" aria-labelledby="createAssistantLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('account-office.create-assistant') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createAssistantLabel">Create Account Office Assistant</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control mb-3" placeholder="Password" required minlength="6">
            </div>
          </div>
          <hr>
          <label class="form-label fw-bold">Assign Account Modules</label>
          <div class="row">
            @foreach($accountModules as $mod)
            <div class="col-md-6">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $mod->id }}" id="mod_{{ $mod->id }}">
                <label class="form-check-label" for="mod_{{ $mod->id }}">{{ $mod->menu_name }}</label>
              </div>
            </div>
            @endforeach
          </div>
          @if($accountModules->isEmpty())
          <p class="text-muted">No account modules available. Please add modules in Menu Manager first.</p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Create Assistant</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Permission Modals --}}
@foreach($assistants as $asst)
<div class="modal fade" id="editPermModal{{ $asst->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('account-office.update-permissions', $asst->id) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Permissions - {{ $asst->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php
          $assignedMenuIds = $asst->menupermission->pluck('menu_master_id')->toArray();
          ?>
          <label class="form-label fw-bold">Account Modules</label>
          <div class="row">
            @foreach($accountModules as $mod)
            <div class="col-md-6">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $mod->id }}"
                  id="edit_mod_{{ $asst->id }}_{{ $mod->id }}"
                  {{ in_array($mod->id, $assignedMenuIds) ? 'checked' : '' }}>
                <label class="form-check-label" for="edit_mod_{{ $asst->id }}_{{ $mod->id }}">{{ $mod->menu_name }}</label>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Permissions</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endforeach

{{-- Assistants List --}}
@if($assistants->count())
<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle" id="exportTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Assigned Modules</th>
            <th>Status</th>
            <th>Actions</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assistants as $key => $asst)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $asst->name }}</td>
            <td>{{ $asst->email }}</td>
            <td>
              @foreach($asst->menupermission as $perm)
              @if($perm->menu_master)
              <span class="badge bg-info mb-1">
                {{ $perm->menu_master->menu_name }}
                <a href="{{ route('account-office.remove-permission', $perm->id) }}" class="text-white ms-1" onclick="return confirm('Remove this permission?')">
                  <i class="fa fa-times"></i>
                </a>
              </span>
              @endif
              @endforeach
            </td>
            <td>
              @if($asst->status == 'ACTIVE')
              <span class="badge bg-success">Active</span>
              @else
              <span class="badge bg-danger">Inactive</span>
              @endif
            </td>
            <td>
              <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPermModal{{ $asst->id }}">
                <i class="fa fa-edit"></i> Permissions
              </button>
              <a href="{{ route('account-office.toggle-status', $asst->id) }}" class="btn btn-sm {{ $asst->status == 'ACTIVE' ? 'btn-dark' : 'btn-success' }}">
                {{ $asst->status == 'ACTIVE' ? 'Block' : 'Allow' }}
              </a>
              <a href="{{ route('account-office.delete-assistant', $asst->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this assistant?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
            <td>{{ date('d-M-Y', strtotime($asst->created_at)) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@else
<p class="display-6 text-center mt-5">No account office assistants found.</p>
@endif

@include('includes.footer')
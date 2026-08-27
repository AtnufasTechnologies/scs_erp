@include('includes.header')
@include('admin.sidebar')

<div class="wrapper">
  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">ITCELL</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Mail Server Settings</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid mt-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      @endif

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <h5 class="mb-1 fw-bold">Module Mail Server Profile</h5>
          <div class="text-muted small">Configure SMTP profile used by module mail senders.</div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent fw-bold">TPO Mail Profile</div>
        <div class="card-body">
          <form method="POST" action="{{ route('itcell.mail-server-settings.update') }}">
            @csrf
            <input type="hidden" name="module_key" value="{{ $moduleKey }}">

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Mailer</label>
                <select name="mailer" class="form-select" required>
                  @php $mailerValue = old('mailer', $setting->mailer ?? 'smtp'); @endphp
                  <option value="smtp" {{ $mailerValue === 'smtp' ? 'selected' : '' }}>smtp</option>
                  <option value="sendmail" {{ $mailerValue === 'sendmail' ? 'selected' : '' }}>sendmail</option>
                  <option value="mailgun" {{ $mailerValue === 'mailgun' ? 'selected' : '' }}>mailgun</option>
                  <option value="ses" {{ $mailerValue === 'ses' ? 'selected' : '' }}>ses</option>
                  <option value="postmark" {{ $mailerValue === 'postmark' ? 'selected' : '' }}>postmark</option>
                  <option value="log" {{ $mailerValue === 'log' ? 'selected' : '' }}>log</option>
                  <option value="array" {{ $mailerValue === 'array' ? 'selected' : '' }}>array</option>
                  <option value="failover" {{ $mailerValue === 'failover' ? 'selected' : '' }}>failover</option>
                  <option value="roundrobin" {{ $mailerValue === 'roundrobin' ? 'selected' : '' }}>roundrobin</option>
                </select>
              </div>

              <div class="col-md-5">
                <label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" value="{{ old('smtp_host', $setting->smtp_host ?? '') }}" required>
              </div>

              <div class="col-md-2">
                <label class="form-label">Port</label>
                <input type="number" min="1" max="65535" name="smtp_port" class="form-control" value="{{ old('smtp_port', $setting->smtp_port ?? 587) }}" required>
              </div>

              <div class="col-md-2">
                <label class="form-label">Encryption</label>
                <select name="smtp_encryption" class="form-select">
                  @php $encryption = old('smtp_encryption', $setting->smtp_encryption ?? 'tls'); @endphp
                  <option value="">None</option>
                  <option value="tls" {{ $encryption === 'tls' ? 'selected' : '' }}>tls</option>
                  <option value="ssl" {{ $encryption === 'ssl' ? 'selected' : '' }}>ssl</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">SMTP Username</label>
                <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', $setting->smtp_username ?? '') }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">SMTP Password</label>
                <input type="password" name="smtp_password" class="form-control" placeholder="Leave blank to keep existing password">
              </div>

              <div class="col-md-6">
                <label class="form-label">From Address</label>
                <input type="email" name="from_address" class="form-control" value="{{ old('from_address', $setting->from_address ?? '') }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">From Name</label>
                <input type="text" name="from_name" class="form-control" value="{{ old('from_name', $setting->from_name ?? '') }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">EHLO Domain (optional)</label>
                <input type="text" name="smtp_ehlo_domain" class="form-control" value="{{ old('smtp_ehlo_domain', $setting->smtp_ehlo_domain ?? '') }}">
              </div>

              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="mailProfileActive" {{ old('is_active', ($setting->is_active ?? 1)) ? 'checked' : '' }}>
                  <label class="form-check-label" for="mailProfileActive">Profile Active</label>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <button class="btn btn-primary" type="submit">Save Mail Server Settings</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent fw-bold">Role Access for TPO Mail Usage</div>
        <div class="card-body">
          <form method="POST" action="{{ route('itcell.mail-server-settings.role-access.update') }}">
            @csrf
            <input type="hidden" name="module_key" value="{{ $moduleKey }}">

            <label class="form-label">Allowed Roles</label>
            <select name="allowed_roles[]" class="dselect-example" multiple>
              @foreach($roles as $role)
              @php
              $slug = (string) ($role->slug ?? '');
              $label = (string) ($role->role_name ?? $slug);
              @endphp
              <option value="{{ $slug }}" {{ $assignedRoles->contains($slug) ? 'selected' : '' }}>
                {{ $label }} ({{ $slug }})
              </option>
              @endforeach
            </select>
            <small class="text-muted d-block mt-2">Users must have at least one selected role to access TPO mailbox compose/inbox/sent/trash.</small>

            <div class="mt-3">
              <button class="btn btn-success" type="submit">Save Role Access</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')